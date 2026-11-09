<?php
/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Unified Inventory Management & Concurrency Locking Service (Q4 & Q8)
 *
 * Enforces pessimistic concurrency locking (SELECT ... FOR UPDATE) to eliminate
 * race conditions and ensure zero double-deduction across POS sales and online orders.
 */

declare(strict_types=1);

namespace App\Services;

use PDO;
use DomainException;

class InventoryService
{
    /**
     * Atomically locks a medicine batch and decrements its stock.
     */
    public static function deductBatchStock(PDO $pdo, int $batchId, int $quantity): array
    {
        if ($quantity <= 0) {
            throw new DomainException('Deduction quantity must be a positive integer.');
        }

        $stmt = $pdo->prepare(
            'SELECT id, name, generic_name, brand_name, batch_number, medicine_type, 
                    selling_price, purchase_price, quantity, minimum_stock_level, 
                    expiry_date, prescription_required, active 
             FROM medicines 
             WHERE id = ? 
             FOR UPDATE'
        );
        $stmt->execute([$batchId]);
        $batch = $stmt->fetch();

        if (!$batch) {
            throw new DomainException("Medicine batch #{$batchId} was not found.");
        }

        if (!(int)$batch['active']) {
            throw new DomainException("Medicine '{$batch['name']}' (Batch {$batch['batch_number']}) is inactive and cannot be dispensed.");
        }

        $today = date('Y-m-d');
        if ($batch['expiry_date'] < $today) {
            throw new DomainException("Medicine '{$batch['name']}' (Batch {$batch['batch_number']}) expired on {$batch['expiry_date']} and cannot be dispensed.");
        }

        $currentStock = (int)$batch['quantity'];
        if ($currentStock < $quantity) {
            throw new DomainException("Insufficient stock for '{$batch['name']}' (Batch {$batch['batch_number']}). Requested: {$quantity}, Available: {$currentStock}.");
        }

        $updateStmt = $pdo->prepare('UPDATE medicines SET quantity = quantity - ? WHERE id = ?');
        $updateStmt->execute([$quantity, $batchId]);

        $batch['previous_quantity'] = $currentStock;
        $batch['remaining_quantity'] = $currentStock - $quantity;
        assert($batch['remaining_quantity'] >= 0, 'Invariant violation: Medicine stock cannot be negative.');
        return $batch;
    }

    /**
     * Atomically restores stock to a medicine batch upon order cancellation.
     */
    public static function restoreBatchStock(PDO $pdo, int $batchId, int $quantity): int
    {
        if ($quantity <= 0) {
            throw new DomainException('Restoration quantity must be a positive integer.');
        }

        $stmt = $pdo->prepare('SELECT id, name, batch_number, quantity FROM medicines WHERE id = ? FOR UPDATE');
        $stmt->execute([$batchId]);
        $batch = $stmt->fetch();

        if (!$batch) {
            throw new DomainException("Medicine batch #{$batchId} was not found.");
        }

        $updateStmt = $pdo->prepare('UPDATE medicines SET quantity = quantity + ? WHERE id = ?');
        $updateStmt->execute([$quantity, $batchId]);

        return ((int)$batch['quantity']) + $quantity;
    }

    /**
     * Allocates stock across unexpired batches using First Expiry, First Out (FEFO) order.
     */
    public static function allocateFefoStock(PDO $pdo, int $medicineId, int $requestedQuantity): array
    {
        if ($requestedQuantity <= 0) {
            throw new DomainException('Requested allocation quantity must be a positive integer.');
        }

        $targetStmt = $pdo->prepare('SELECT id, name, generic_name, medicine_type, selling_price FROM medicines WHERE id = ?');
        $targetStmt->execute([$medicineId]);
        $target = $targetStmt->fetch();

        if (!$target) {
            throw new DomainException("Medicine batch #{$medicineId} was not found.");
        }

        $medName = (string)$target['name'];
        $genericName = (string)$target['generic_name'];
        $medType = (string)$target['medicine_type'];

        // Query all active, unexpired batches matching target medicine ID or formulation in strict earliest-expiry order
        $batchStmt = $pdo->prepare(
            'SELECT id, name, generic_name, batch_number, quantity, expiry_date, selling_price 
             FROM medicines 
             WHERE (id = ? OR (generic_name = ? AND medicine_type = ?)) 
               AND active = 1 
               AND expiry_date >= CURDATE() 
               AND quantity > 0 
             ORDER BY expiry_date ASC, id ASC 
             FOR UPDATE'
        );
        $batchStmt->execute([$medicineId, $genericName, $medType]);
        $batches = $batchStmt->fetchAll();

        $allocations = [];
        $remainingNeeded = $requestedQuantity;

        foreach ($batches as $b) {
            if ($remainingNeeded <= 0) {
                break;
            }
            $batchAvailable = (int)$b['quantity'];
            if ($batchAvailable <= 0) {
                continue;
            }
            $take = min($remainingNeeded, $batchAvailable);

            self::deductBatchStock($pdo, (int)$b['id'], $take);

            $allocations[] = [
                'batch_id'      => (int)$b['id'],
                'medicine_id'   => (int)$b['id'],
                'batch_number'  => (string)$b['batch_number'],
                'deducted'      => $take,
                'quantity'      => $take,
                'unit_price'    => (float)$b['selling_price'],
                'medicine_name' => (string)$b['name'],
                'generic_name'  => (string)$b['generic_name'],
                'expiry_date'   => (string)$b['expiry_date'],
            ];

            $remainingNeeded -= $take;
        }

        if ($remainingNeeded > 0) {
            throw new DomainException("Insufficient unexpired stock available to fulfill requested quantity for '{$medName}'. Requested: {$requestedQuantity}, Unfulfilled: {$remainingNeeded}.");
        }

        return $allocations;
    }
}

namespace App;

use PDO;

class_alias(\App\Services\InventoryService::class, 'InventoryService');
class_alias(\App\Services\InventoryService::class, \App\InventoryService::class);

// Procedural Helper Functions
function deduct_batch_stock(PDO $pdo, int $batch_id, int $quantity): array
{
    return \App\Services\InventoryService::deductBatchStock($pdo, $batch_id, $quantity);
}

function restore_batch_stock(PDO $pdo, int $batch_id, int $quantity): int
{
    return \App\Services\InventoryService::restoreBatchStock($pdo, $batch_id, $quantity);
}

function allocate_fefo_stock(PDO $pdo, int $medicine_id, int $requested_quantity): array
{
    return \App\Services\InventoryService::allocateFefoStock($pdo, $medicine_id, $requested_quantity);
}

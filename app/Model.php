<?php
/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Consolidated Domain Models: User, Medicine, Order (Q4 & Q8)
 */

declare(strict_types=1);

namespace App\Models;

use PDO;

/**
 * User Domain Model
 * Authentication, profile persistence, and customer registration.
 */
class User
{
    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT id, full_name, username, email, phone, address, role, enabled, created_at FROM app_users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUsernameOrEmail(string $identifier): ?array
    {
        $stmt = db()->prepare('SELECT id, full_name, username, email, phone, address, password, role, enabled FROM app_users WHERE username = ? OR email = ? LIMIT 1');
        $stmt->execute([$identifier, $identifier]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        return self::findByUsernameOrEmail($username);
    }

    public static function createCustomer(array $data): int
    {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT);
        $stmt = db()->prepare(
            'INSERT INTO app_users (full_name, username, email, phone, address, password, role, enabled) 
             VALUES (?, ?, ?, ?, ?, ?, "CUSTOMER", 1)'
        );
        $stmt->execute([
            $data['full_name'],
            $data['username'],
            $data['email'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $hash
        ]);
        return (int)db()->lastInsertId();
    }

    public static function updateProfile(int $id, array $data): void
    {
        $stmt = db()->prepare(
            'UPDATE app_users 
             SET full_name = ?, email = ?, phone = ?, address = ? 
             WHERE id = ?'
        );
        $stmt->execute([
            $data['full_name'],
            $data['email'],
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $id
        ]);
    }

    public static function existsByUsername(string $username, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM app_users WHERE username = ?';
        $params = [$username];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public static function existsByEmail(string $email, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM app_users WHERE email = ?';
        $params = [$email];
        if ($excludeId !== null) {
            $sql .= ' AND id != ?';
            $params[] = $excludeId;
        }
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return ((int)$stmt->fetchColumn()) > 0;
    }
}

/**
 * Medicine Domain Model
 * Inventory queries, batch retrieval, and stock helpers.
 */
class Medicine
{
    public static function baseQuery(): string
    {
        return medicine_query();
    }

    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare(self::baseQuery() . ' WHERE m.id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findActive(string $keyword = '', int $categoryId = 0): array
    {
        $sql = self::baseQuery() . ' WHERE m.active = 1 AND m.expiry_date >= CURDATE() AND m.quantity > 0';
        $params = [];

        if ($keyword !== '') {
            $sql .= ' AND (m.name LIKE ? OR m.generic_name LIKE ? OR m.brand_name LIKE ?)';
            $needle = '%' . $keyword . '%';
            array_push($params, $needle, $needle, $needle);
        }

        if ($categoryId > 0) {
            $sql .= ' AND m.category_id = ?';
            $params[] = $categoryId;
        }

        $sql .= ' ORDER BY m.name ASC, m.batch_number ASC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findActiveByName(PDO $pdo, string $name): array
    {
        $stmt = $pdo->prepare(
            'SELECT id, name, batch_number, quantity, selling_price, expiry_date 
             FROM medicines 
             WHERE name = ? AND active = 1 AND expiry_date >= CURDATE() AND quantity > 0 
             ORDER BY expiry_date ASC, id ASC 
             FOR UPDATE'
        );
        $stmt->execute([$name]);
        return $stmt->fetchAll();
    }

    public static function lockBatchForUpdate(PDO $pdo, int $batchId): ?array
    {
        $stmt = $pdo->prepare(
            'SELECT id, name, generic_name, brand_name, batch_number, medicine_type, 
                    selling_price, purchase_price, quantity, minimum_stock_level, 
                    expiry_date, prescription_required, active 
             FROM medicines 
             WHERE id = ? 
             FOR UPDATE'
        );
        $stmt->execute([$batchId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function decrementStock(PDO $pdo, int $batchId, int $quantity): void
    {
        $stmt = $pdo->prepare('UPDATE medicines SET quantity = quantity - ? WHERE id = ?');
        $stmt->execute([$quantity, $batchId]);
    }

    public static function incrementStock(PDO $pdo, int $batchId, int $quantity): void
    {
        $stmt = $pdo->prepare('UPDATE medicines SET quantity = quantity + ? WHERE id = ?');
        $stmt->execute([$quantity, $batchId]);
    }

    public static function getAllCategories(): array
    {
        $stmt = db()->query('SELECT id, name FROM categories ORDER BY name ASC');
        return $stmt->fetchAll();
    }
}

/**
 * Order Domain Model
 * Online customer orders, line items, and audit lifecycle progression.
 */
class Order
{
    public static function findById(int $id): ?array
    {
        $stmt = db()->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByOrderNumber(string $orderNumber): ?array
    {
        $stmt = db()->prepare('SELECT * FROM orders WHERE order_number = ? LIMIT 1');
        $stmt->execute([$orderNumber]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByCustomerId(int $customerId, string $statusFilter = ''): array
    {
        $sql = 'SELECT o.*, COUNT(oi.id) AS total_items, COUNT(oi.id) AS item_count 
                FROM orders o 
                LEFT JOIN order_items oi ON oi.order_id = o.id 
                WHERE o.customer_id = ?';
        $params = [$customerId];

        if ($statusFilter !== '') {
            $sql .= ' AND o.order_status = ?';
            $params[] = $statusFilter;
        }

        $sql .= ' GROUP BY o.id ORDER BY o.id DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function findAll(string $statusFilter = '', string $paymentFilter = '', string $search = ''): array
    {
        $sql = 'SELECT o.*, COUNT(oi.id) AS total_items, COUNT(oi.id) AS item_count 
                FROM orders o 
                LEFT JOIN order_items oi ON oi.order_id = o.id 
                WHERE 1=1';
        $params = [];

        if ($statusFilter !== '') {
            $sql .= ' AND o.order_status = ?';
            $params[] = $statusFilter;
        }

        if ($paymentFilter !== '') {
            $sql .= ' AND o.payment_status = ?';
            $params[] = $paymentFilter;
        }

        if ($search !== '') {
            $sql .= ' AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)';
            $needle = '%' . $search . '%';
            array_push($params, $needle, $needle, $needle);
        }

        $sql .= ' GROUP BY o.id ORDER BY o.id DESC';
        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getItems(int $orderId): array
    {
        $stmt = db()->prepare(
            'SELECT oi.*, m.name AS medicine_name, m.generic_name, m.batch_number, m.medicine_type, c.name AS category_name 
             FROM order_items oi 
             JOIN medicines m ON m.id = oi.medicine_id 
             LEFT JOIN categories c ON c.id = m.category_id 
             WHERE oi.order_id = ? 
             ORDER BY oi.id ASC'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function getPrescription(int $orderId): ?array
    {
        $stmt = db()->prepare(
            'SELECT p.*, u.full_name AS reviewer_name 
             FROM prescriptions p 
             LEFT JOIN app_users u ON u.id = p.reviewed_by_id 
             WHERE p.order_id = ? LIMIT 1'
        );
        $stmt->execute([$orderId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function getHistory(int $orderId): array
    {
        $stmt = db()->prepare(
            'SELECT h.*, u.full_name AS changed_by_name, u.role AS changed_by_role 
             FROM order_status_history h 
             LEFT JOIN app_users u ON u.id = h.changed_by_id 
             WHERE h.order_id = ? 
             ORDER BY h.id ASC'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    public static function countPending(): int
    {
        $stmt = db()->query("SELECT COUNT(*) FROM orders WHERE order_status IN ('PENDING', 'CONFIRMED', 'PROCESSING')");
        return (int)$stmt->fetchColumn();
    }

    public static function create(PDO $pdo, array $data): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO orders 
             (order_number, customer_id, customer_name, customer_phone, delivery_address, 
              subtotal, shipping_fee, total_amount, order_status, payment_method, payment_status, 
              prescription_required, prescription_status, notes, stock_deducted, stock_restored) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, "PENDING", ?, "UNPAID", ?, ?, ?, 1, 0)'
        );
        $stmt->execute([
            $data['order_number'],
            (int)$data['customer_id'],
            $data['customer_name'],
            $data['customer_phone'],
            $data['delivery_address'],
            number_format((float)$data['subtotal'], 2, '.', ''),
            number_format((float)($data['shipping_fee'] ?? 0), 2, '.', ''),
            number_format((float)$data['total_amount'], 2, '.', ''),
            $data['payment_method'],
            !empty($data['prescription_required']) ? 1 : 0,
            $data['prescription_status'] ?? 'NOT_REQUIRED',
            $data['notes'] ?? null
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function addItems(PDO $pdo, int $orderId, array $items): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO order_items (order_id, medicine_id, medicine_name, batch_number, quantity, unit_price, line_total) 
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        foreach ($items as $item) {
            $stmt->execute([
                $orderId,
                (int)$item['medicine_id'],
                (string)($item['medicine_name'] ?? 'Medicine'),
                (string)($item['batch_number'] ?? 'BATCH-001'),
                (int)$item['quantity'],
                number_format((float)$item['unit_price'], 2, '.', ''),
                number_format((float)$item['line_total'], 2, '.', '')
            ]);
        }
    }

    public static function addPrescription(PDO $pdo, int $orderId, int $customerId, array $rxData): int
    {
        $stmt = $pdo->prepare(
            'INSERT INTO prescriptions 
             (order_id, customer_id, file_path, original_filename, file_size, mime_type, verification_status) 
             VALUES (?, ?, ?, ?, ?, ?, "PENDING")'
        );
        $stmt->execute([
            $orderId,
            $customerId,
            $rxData['file_path'],
            $rxData['original_filename'],
            (int)$rxData['file_size'],
            $rxData['mime_type']
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function addStatusHistory(PDO $pdo, int $orderId, ?string $prevStatus, string $newStatus, ?int $userId, ?string $remarks): void
    {
        $stmt = $pdo->prepare(
            'INSERT INTO order_status_history (order_id, previous_status, new_status, changed_by_id, remarks) 
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $orderId,
            $prevStatus,
            $newStatus,
            $userId,
            $remarks
        ]);
    }

    public static function updateStatus(PDO $pdo, int $orderId, string $newStatus): void
    {
        $stmt = $pdo->prepare('UPDATE orders SET order_status = ? WHERE id = ?');
        $stmt->execute([$newStatus, $orderId]);
    }

    public static function markPaymentStatus(PDO $pdo, int $orderId, string $paymentStatus): void
    {
        $stmt = $pdo->prepare('UPDATE orders SET payment_status = ? WHERE id = ?');
        $stmt->execute([$paymentStatus, $orderId]);
    }

    public static function markStockRestored(PDO $pdo, int $orderId): void
    {
        $stmt = $pdo->prepare('UPDATE orders SET stock_restored = 1 WHERE id = ?');
        $stmt->execute([$orderId]);
    }

    public static function updatePrescriptionStatus(PDO $pdo, int $orderId, string $status, ?int $reviewerId, ?string $notes): void
    {
        $rxStmt = $pdo->prepare(
            'UPDATE prescriptions 
             SET verification_status = ?, reviewed_by_id = ?, review_notes = ?, reviewed_at = NOW() 
             WHERE order_id = ?'
        );
        $rxStmt->execute([$status, $reviewerId, $notes, $orderId]);

        $orderPrescriptionStatus = ($status === 'APPROVED') ? 'VERIFIED' : 'REJECTED';
        $orderStmt = $pdo->prepare('UPDATE orders SET prescription_status = ? WHERE id = ?');
        $orderStmt->execute([$orderPrescriptionStatus, $orderId]);
    }

    public static function getLinkedSaleId(PDO $pdo, int $orderId): ?int
    {
        $stmt = $pdo->prepare('SELECT id FROM sales WHERE order_id = ? LIMIT 1');
        $stmt->execute([$orderId]);
        $val = $stmt->fetchColumn();
        return $val !== false ? (int)$val : null;
    }
}

namespace App;

// Global Namespace Aliases for Convenience
class_alias(\App\Models\User::class, 'User');
class_alias(\App\Models\Medicine::class, 'Medicine');
class_alias(\App\Models\Order::class, 'Order');

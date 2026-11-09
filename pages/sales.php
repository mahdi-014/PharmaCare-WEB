<?php
/**
 * PharmaCare — Unified Sales Management & Point-of-Sale (POS) View
 * Combines:
 * 1. Interactive POS Billing Counter (dynamic multi-item dispensing with live calculation)
 * 2. Sales Ledger & Historical Invoices (channels, staff attribution, and receipts)
 */

declare(strict_types=1);

require_staff_or_admin();

$tab = trim((string)($_GET['tab'] ?? ''));
if ($tab === '' || !in_array($tab, ['pos', 'history'], true)) {
    $tab = isset($_GET['new']) ? 'pos' : 'history';
}

// Fetch saleable medicines for POS counter
$medicines = fetch_all(
    'SELECT id, name, brand_name, batch_number, quantity, selling_price, prescription_required 
     FROM medicines 
     WHERE active = 1 AND expiry_date >= CURDATE() AND quantity > 0 
     ORDER BY name, batch_number'
);

// Fetch completed sales transactions for ledger
$sales = fetch_all(
    'SELECT s.*, u.full_name AS sold_by_name, u.username AS sold_by_username, 
            COALESCE((SELECT SUM(si.quantity) FROM sale_items si WHERE si.sale_id = s.id), 0) AS total_quantity,
            COALESCE((
                SELECT GROUP_CONCAT(CONCAT(COALESCE(si.medicine_name, m.name), " (", si.quantity, ")") SEPARATOR ", ")
                FROM sale_items si
                LEFT JOIN medicines m ON m.id = si.medicine_id
                WHERE si.sale_id = s.id
            ), "") AS items_summary
     FROM sales s 
     JOIN app_users u ON u.id = s.sold_by_id 
     ORDER BY s.sale_date DESC, s.id DESC'
);
?>

<div class="page-intro mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h2>Point of Sale &amp; Sales Ledger</h2>
        <p>Dispense over-the-counter medicines and inspect historical sales invoices.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="index.php?page=sales&tab=pos" class="btn btn-primary btn-sm <?= $tab === 'pos' ? 'active' : '' ?>">
            <i class="bi bi-cart-plus me-1"></i>New POS Sale
        </a>
        <a href="index.php?page=sales&tab=history" class="btn btn-outline-secondary btn-sm <?= $tab === 'history' ? 'active' : '' ?>">
            <i class="bi bi-receipt me-1"></i>Sales Ledger
        </a>
    </div>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'pos' ? 'active fw-bold' : '' ?>" href="index.php?page=sales&tab=pos">
            <i class="bi bi-shop me-1"></i>POS Billing Counter
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'history' ? 'active fw-bold' : '' ?>" href="index.php?page=sales&tab=history">
            <i class="bi bi-clock-history me-1"></i>Sales Ledger (<?= count($sales) ?>)
        </a>
    </li>
</ul>

<?php if ($tab === 'pos'): ?>
    <!-- POS Billing Counter -->
    <form action="actions.php" method="post" id="saleForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="sale_create">

        <!-- Customer Coordinates -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <strong>Customer Information</strong> <span class="text-secondary small">(Optional for walk-in retail)</span>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" for="customer_name">Customer Name</label>
                        <input class="form-control" id="customer_name" name="customer_name" maxlength="100" placeholder="Walk-in Customer">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" for="customer_phone">Contact Phone</label>
                        <input class="form-control" id="customer_phone" name="customer_phone" maxlength="30" placeholder="01XXXXXXXXX">
                    </div>
                </div>
            </div>
        </div>

        <!-- Dispensed Medicine Items -->
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <div>
                    <strong>Dispensed Medicines</strong>
                    <div class="small text-secondary">Inventory quantities are locked atomically with FEFO enforcement on submit.</div>
                </div>
                <button type="button" id="addRow" class="btn btn-sm btn-outline-primary">
                    <i class="bi bi-plus-lg me-1"></i>Add Medicine Line
                </button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="saleTable">
                    <thead class="table-light">
                        <tr>
                            <th style="min-width: 285px">Medicine &amp; Batch</th>
                            <th>In Stock</th>
                            <th style="width: 120px">Quantity</th>
                            <th>Unit Price</th>
                            <th>Line Total</th>
                            <th style="width: 40px"></th>
                        </tr>
                    </thead>
                    <tbody id="saleRows"></tbody>
                </table>
            </div>
        </div>

        <!-- Financial Summary & Checkout -->
        <div class="row justify-content-end">
            <div class="col-lg-5 col-xl-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Subtotal</span>
                            <strong id="subtotalText">৳0.00</strong>
                        </div>

                        <div class="mb-3">
                            <label for="discountInput" class="form-label small fw-semibold">Discount (৳)</label>
                            <input type="number" min="0" step="0.01" name="discount" value="0" id="discountInput" class="form-control">
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="h5 mb-0">Grand Total</span>
                            <strong class="h4 text-success mb-0" id="grandTotalText">৳0.00</strong>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100" <?= !$medicines ? 'disabled' : '' ?>>
                            <i class="bi bi-check-circle me-1"></i>Complete Sale &amp; Print Invoice
                        </button>

                        <?php if (!$medicines): ?>
                            <div class="alert alert-danger small mt-2 mb-0">
                                No saleable medicines are currently available in stock.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <!-- Dynamic Sale Row Template -->
    <template id="saleRowTemplate">
        <tr class="sale-row">
            <td>
                <select class="form-select medicine-select" name="medicine_id[]" required>
                    <option value="">Select Medicine</option>
                    <?php foreach ($medicines as $m): ?>
                        <option value="<?= e((string)$m['id']) ?>"
                                data-price="<?= e((string)$m['selling_price']) ?>"
                                data-stock="<?= e((string)$m['quantity']) ?>">
                            <?= e($m['name']) ?> — <?= e($m['batch_number']) ?><?= $m['prescription_required'] ? ' (Rx)' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td class="stock-cell text-secondary">—</td>
            <td>
                <input name="quantity[]" type="number" min="1" step="1" value="1" class="form-control quantity-input" required>
            </td>
            <td class="price-cell">৳0.00</td>
            <td class="line-total fw-semibold text-success">৳0.00</td>
            <td>
                <button class="btn btn-sm btn-light border remove-row" type="button" aria-label="Remove item">
                    <i class="bi bi-x-lg"></i>
                </button>
            </td>
        </tr>
    </template>

<?php else: ?>
    <!-- Sales History Ledger -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Invoice #</th>
                        <th>Date &amp; Time</th>
                        <th>Channel</th>
                        <th>Customer</th>
                        <th>Sold By</th>
                        <th>Units</th>
                        <th>Total Amount</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sales as $s): ?>
                        <tr>
                            <td class="fw-semibold font-monospace">
                                <?= e($s['invoice_number']) ?>
                                <?php if (!empty($s['order_id'])): ?>
                                    <div class="small"><a href="index.php?page=order_detail&id=<?= (int)$s['order_id'] ?>" class="text-decoration-none text-muted">Online Order #<?= (int)$s['order_id'] ?></a></div>
                                <?php endif; ?>
                            </td>
                            <td><?= e(nice_date($s['sale_date'], true)) ?></td>
                            <td>
                                <?php if (($s['channel'] ?? 'POS') === 'ONLINE'): ?>
                                    <span class="badge text-bg-primary"><i class="bi bi-globe me-1"></i>Online</span>
                                <?php else: ?>
                                    <span class="badge text-bg-secondary"><i class="bi bi-shop me-1"></i>POS</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= e($s['customer_name'] ?: 'Walk-in Customer') ?></div>
                                <span class="subtext text-muted small"><?= e($s['customer_phone'] ?? '') ?></span>
                            </td>
                            <td><?= e($s['sold_by_name']) ?></td>
                            <td>
                                <div><strong class="text-dark"><?= (int)$s['total_quantity'] ?></strong> <small class="text-secondary">unit(s)</small></div>
                                <?php if (!empty($s['items_summary'])): ?>
                                    <div class="small text-muted text-truncate" style="max-width: 220px;" title="<?= e($s['items_summary']) ?>">
                                        <?= e($s['items_summary']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong class="text-success"><?= money($s['total_amount']) ?></strong>
                                <?php if ((float)$s['discount'] > 0): ?>
                                    <span class="subtext text-muted small d-block">Disc: <?= money($s['discount']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="index.php?page=invoice&id=<?= (int)$s['id'] ?>" class="btn btn-sm btn-outline-primary" title="View printable invoice">
                                    <i class="bi bi-receipt me-1"></i>Invoice
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$sales): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-receipt fs-1 d-block mb-2"></i>
                                No transactions have been recorded yet.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php
/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Unified Orders Management View (Customer Order History & Staff Fulfillment Queue)
 */

declare(strict_types=1);

require_login();

if (is_customer()) {
    // -------------------------------------------------------------
    // Customer Order History View
    // -------------------------------------------------------------
    $customerId = (int)user()['id'];
    $orders = \App\Models\Order::findByCustomerId($customerId);
    ?>
    <div class="page-intro mb-4">
        <div>
            <h2>My Medicine Orders</h2>
            <p>Track your real-time fulfillment status and review previous medication orders.</p>
        </div>
        <div>
            <a href="<?= e(url('catalog')) ?>" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-cart-plus me-1"></i>New Order
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <strong>Order History (<?= count($orders) ?>)</strong>
            <span class="text-secondary small">Sorted by newest order</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th>Order Reference</th>
                        <th>Date Placed</th>
                        <th>Items</th>
                        <th class="text-end">Total Amount</th>
                        <th>Payment</th>
                        <th>Prescription</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-secondary">
                                <i class="bi bi-box2 text-muted" style="font-size: 2rem;"></i>
                                <div class="mt-2">You haven't placed any orders yet.</div>
                                <a href="<?= e(url('catalog')) ?>" class="btn btn-primary btn-sm mt-3">Browse Medicines</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <a href="<?= e(url('order_detail', ['id' => $o['id']])) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= e($o['order_number']) ?>
                                    </a>
                                </td>
                                <td class="small text-secondary"><?= e(nice_date($o['created_at'], true)) ?></td>
                                <td class="small"><?= (int)$o['item_count'] ?> item(s)</td>
                                <td class="text-end fw-bold text-dark"><?= money($o['total_amount']) ?></td>
                                <td>
                                    <span class="badge text-bg-light border text-dark" style="font-size: 11px;">
                                        <?= $o['payment_method'] === 'COD' ? 'Cash on Delivery' : 'Store Pickup' ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if (!(int)$o['prescription_required']): ?>
                                        <span class="badge text-bg-light border text-muted" style="font-size: 11px;">OTC</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-<?= e(prescription_status_badge($o['prescription_status'])) ?>" style="font-size: 11px;">
                                            <i class="bi bi-prescription2 me-1"></i><?= e(prescription_status_label($o['prescription_status'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-<?= e(order_status_badge($o['order_status'])) ?>" style="font-size: 11px;">
                                        <?= e(order_status_label($o['order_status'])) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= e(url('order_detail', ['id' => $o['id']])) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
} else {
    // -------------------------------------------------------------
    // Staff & Administrator Order Fulfillment Queue
    // -------------------------------------------------------------
    require_staff_or_admin();

    $statusFilter = trim((string)($_GET['status'] ?? ''));
    $paymentFilter = trim((string)($_GET['payment'] ?? ''));
    $rxFilter = trim((string)($_GET['prescription'] ?? ''));
    $searchKeyword = trim((string)($_GET['search'] ?? ''));

    $counts = [
        'total'      => (int)scalar('SELECT COUNT(*) FROM orders'),
        'pending'    => (int)scalar("SELECT COUNT(*) FROM orders WHERE order_status = 'PENDING'"),
        'needs_rx'   => (int)scalar("SELECT COUNT(*) FROM orders WHERE prescription_status = 'PENDING_REVIEW'"),
        'completed'  => (int)scalar("SELECT COUNT(*) FROM orders WHERE order_status = 'COMPLETED'"),
    ];

    $sql = 'SELECT o.*, (SELECT COUNT(*) FROM order_items WHERE order_id = o.id) AS item_count FROM orders o WHERE 1=1';
    $params = [];

    if ($statusFilter !== '') {
        $sql .= ' AND o.order_status = ?';
        $params[] = $statusFilter;
    }
    if ($paymentFilter !== '') {
        $sql .= ' AND o.payment_status = ?';
        $params[] = $paymentFilter;
    }
    if ($rxFilter === 'needs_review') {
        $sql .= " AND o.prescription_status = 'PENDING_REVIEW'";
    } elseif ($rxFilter === 'required') {
        $sql .= ' AND o.prescription_required = 1';
    }
    if ($searchKeyword !== '') {
        $sql .= ' AND (o.order_number LIKE ? OR o.customer_name LIKE ? OR o.customer_phone LIKE ?)';
        $needle = '%' . $searchKeyword . '%';
        array_push($params, $needle, $needle, $needle);
    }

    $sql .= ' ORDER BY o.id DESC';
    $orders = fetch_all($sql, $params);
    ?>
    <div class="page-intro mb-4">
        <div>
            <h2>Staff Order Fulfillment Queue</h2>
            <p>Review incoming customer orders, verify medical prescriptions, and execute order fulfillment.</p>
        </div>
    </div>

    <!-- Quick Filter Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-warning">
                <div class="card-body">
                    <div class="text-secondary small fw-bold text-uppercase">Pending Orders</div>
                    <div class="h3 mb-0 mt-1 text-warning fw-bold"><?= $counts['pending'] ?></div>
                    <small class="text-muted">Awaiting fulfillment</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-danger">
                <div class="card-body">
                    <div class="text-secondary small fw-bold text-uppercase">Needs Rx Review</div>
                    <div class="h3 mb-0 mt-1 text-danger fw-bold"><?= $counts['needs_rx'] ?></div>
                    <small class="text-muted">Prescription approval</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-success">
                <div class="card-body">
                    <div class="text-secondary small fw-bold text-uppercase">Completed Orders</div>
                    <div class="h3 mb-0 mt-1 text-success fw-bold"><?= $counts['completed'] ?></div>
                    <small class="text-muted">Dispatched &amp; paid</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card h-100 shadow-sm border-0 border-start border-4 border-primary">
                <div class="card-body">
                    <div class="text-secondary small fw-bold text-uppercase">Total Orders</div>
                    <div class="h3 mb-0 mt-1 text-primary fw-bold"><?= $counts['total'] ?></div>
                    <small class="text-muted">Lifetime volume</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="get" action="index.php" class="row g-2 align-items-center">
                <input type="hidden" name="page" value="orders">

                <div class="col-md-4">
                    <input class="form-control" name="search" value="<?= e($searchKeyword) ?>" placeholder="Search Order #, customer, phone...">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Order Statuses</option>
                        <option value="PENDING" <?= $statusFilter === 'PENDING' ? 'selected' : '' ?>>Pending Review</option>
                        <option value="CONFIRMED" <?= $statusFilter === 'CONFIRMED' ? 'selected' : '' ?>>Confirmed</option>
                        <option value="COMPLETED" <?= $statusFilter === 'COMPLETED' ? 'selected' : '' ?>>Completed</option>
                        <option value="CANCELLED" <?= $statusFilter === 'CANCELLED' ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="prescription" class="form-select">
                        <option value="">All Prescriptions</option>
                        <option value="needs_review" <?= $rxFilter === 'needs_review' ? 'selected' : '' ?>>Needs Review</option>
                        <option value="required" <?= $rxFilter === 'required' ? 'selected' : '' ?>>Prescription Required</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1"><i class="bi bi-funnel me-1"></i>Filter</button>
                    <a href="<?= e(url('orders')) ?>" class="btn btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
                </div>
            </form>
        </div>
    </div>

    <!-- Orders Queue Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <strong>Active Order Queue (<?= count($orders) ?>)</strong>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-secondary">
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th class="text-end">Total</th>
                        <th>Prescription</th>
                        <th>Status</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr><td colspan="8" class="text-center py-4 text-secondary">No orders match the selected filters.</td></tr>
                    <?php else: ?>
                        <?php foreach ($orders as $o): ?>
                            <tr>
                                <td>
                                    <a href="<?= e(url('order_detail', ['id' => $o['id']])) ?>" class="fw-bold text-primary text-decoration-none">
                                        <?= e($o['order_number']) ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= e($o['customer_name']) ?></div>
                                    <div class="small text-secondary"><?= e($o['customer_phone']) ?></div>
                                </td>
                                <td class="small text-secondary"><?= e(nice_date($o['created_at'], true)) ?></td>
                                <td class="small"><?= (int)$o['item_count'] ?> item(s)</td>
                                <td class="text-end fw-bold text-dark"><?= money($o['total_amount']) ?></td>
                                <td>
                                    <?php if (!(int)$o['prescription_required']): ?>
                                        <span class="badge text-bg-light border text-muted">OTC</span>
                                    <?php else: ?>
                                        <span class="badge text-bg-<?= e(prescription_status_badge($o['prescription_status'])) ?>">
                                            <?= e(prescription_status_label($o['prescription_status'])) ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge text-bg-<?= e(order_status_badge($o['order_status'])) ?>">
                                        <?= e(order_status_label($o['order_status'])) ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="<?= e(url('order_detail', ['id' => $o['id']])) ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-eye me-1"></i>Inspect
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}
?>

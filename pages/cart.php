<?php
/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Unified Shopping Cart & Checkout View (Absorbs checkout.php)
 */

declare(strict_types=1);

require_customer();

$items = cart_get_items();
$itemCount = cart_get_count();
$lineCount = cart_get_line_count();
$cartTotal = cart_get_total();
$requiresPrescription = cart_requires_prescription();

$customerId = (int)user()['id'];
$u = fetch_one('SELECT full_name, phone, address FROM app_users WHERE id = ?', [$customerId]);
?>

<div class="page-intro mb-4">
    <div>
        <h2>Shopping Cart &amp; Checkout</h2>
        <p>Review selected medicines, verify delivery details, and place your order.</p>
    </div>
</div>

<?php if (empty($items)): ?>
    <div class="card shadow-sm border-0">
        <div class="card-body py-5 text-center">
            <div class="avatar avatar-xl mx-auto mb-3" style="width: 72px; height: 72px; font-size: 32px; background: rgba(13, 148, 136, 0.1); color: var(--brand); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-cart-x"></i>
            </div>
            <h4 class="mb-2">Your Shopping Cart is Empty</h4>
            <p class="text-secondary mb-4">You have not added any medicines to your cart yet.</p>
            <a href="<?= e(url('catalog')) ?>" class="btn btn-primary px-4">
                <i class="bi bi-capsule-pill me-1"></i>Browse Medicine Storefront
            </a>
        </div>
    </div>
<?php else: ?>

    <?php if ($requiresPrescription): ?>
        <div class="alert alert-warning border border-warning-subtle shadow-sm mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="bi bi-prescription2 fs-2 me-3 text-danger"></i>
                <div>
                    <h5 class="alert-heading mb-1 text-dark fw-bold">Prescription Required Notice</h5>
                    <p class="mb-0 text-secondary small">
                        Your cart contains prescription-required medications. A valid doctor's prescription document (JPG, PNG, or PDF) must be attached below before dispatch.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <!-- Left: Cart Items Table -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <strong>Cart Items (<?= $lineCount ?>)</strong>
                    <form action="actions.php" method="post" onsubmit="return confirm('Clear your entire cart?');" class="mb-0">
                        <input type="hidden" name="action" value="cart_clear">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash3 me-1"></i>Clear Cart
                        </button>
                    </form>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small text-secondary">
                            <tr>
                                <th>Medicine</th>
                                <th class="text-end">Price</th>
                                <th class="text-center" style="width: 150px;">Qty</th>
                                <th class="text-end">Total</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items as $medId => $item):
                                $lineTotal = ((float)$item['price']) * ((int)$item['quantity']);
                            ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($item['name']) ?></div>
                                        <div class="small text-secondary">
                                            <span class="badge text-bg-light border"><?= e($item['category_name']) ?></span>
                                            <?php if (!empty($item['batch_number'])): ?>
                                                &middot; Batch: <code><?= e($item['batch_number']) ?></code>
                                            <?php endif; ?>
                                        </div>
                                        <?php if (!empty($item['requires_prescription'])): ?>
                                            <span class="badge text-bg-danger-subtle text-danger border border-danger-subtle mt-1" style="font-size: 10px;">
                                                <i class="bi bi-prescription2 me-1"></i>Rx Required
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-semibold"><?= money($item['price']) ?></td>
                                    <td class="text-center">
                                        <form action="actions.php" method="post" class="d-flex align-items-center justify-content-center gap-1 mb-0">
                                            <input type="hidden" name="action" value="cart_update">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="medicine_id" value="<?= (int)$medId ?>">
                                            <input type="number" name="quantity" value="<?= (int)$item['quantity'] ?>" min="1" max="<?= (int)$item['max_stock'] ?>" class="form-control form-control-sm text-center" style="width: 65px;" onchange="this.form.submit()">
                                        </form>
                                    </td>
                                    <td class="text-end fw-bold text-dark"><?= money($lineTotal) ?></td>
                                    <td class="text-center">
                                        <form action="actions.php" method="post" class="mb-0">
                                            <input type="hidden" name="action" value="cart_remove">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="medicine_id" value="<?= (int)$medId ?>">
                                            <button type="submit" class="btn btn-sm btn-link text-danger p-0" title="Remove item">
                                                <i class="bi bi-x-circle fs-5"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <a href="<?= e(url('catalog')) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Continue Shopping
            </a>
        </div>

        <!-- Right: Checkout Shipping & Prescription Form -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <strong class="d-flex align-items-center">
                        <i class="bi bi-truck text-primary me-2 fs-5"></i>Shipping &amp; Order Placement
                    </strong>
                </div>
                <div class="card-body">
                    <form action="actions.php" method="post" enctype="multipart/form-data">
                        <input type="hidden" name="action" value="order_create">
                        <?= csrf_field() ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="co_name">Recipient Name *</label>
                            <input class="form-control" id="co_name" name="customer_name" value="<?= e($u['full_name'] ?? '') ?>" maxlength="100" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="co_phone">Mobile Number *</label>
                            <input class="form-control" id="co_phone" name="customer_phone" type="tel" value="<?= e($u['phone'] ?? '') ?>" maxlength="15" pattern="(01|\+8801)[3-9][0-9]{8}" placeholder="017XXXXXXXX" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="co_address">Delivery Address *</label>
                            <textarea class="form-control" id="co_address" name="delivery_address" rows="2" maxlength="500" required><?= e($u['address'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Payment Method</label>
                            <div class="form-check mb-1">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_cod" value="COD" checked>
                                <label class="form-check-label" for="pay_cod">Cash on Delivery (COD)</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_method" id="pay_pickup" value="STORE_PICKUP">
                                <label class="form-check-label" for="pay_pickup">Store Pickup (Pay at Counter)</label>
                            </div>
                        </div>

                        <?php if ($requiresPrescription): ?>
                            <div class="mb-3 p-3 bg-light rounded border border-danger-subtle">
                                <label class="form-label fw-semibold text-danger" for="co_prescription">
                                    <i class="bi bi-file-earmark-medical me-1"></i>Upload Doctor's Prescription *
                                </label>
                                <input type="file" class="form-control" id="co_prescription" name="prescription" accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="form-hint small text-muted mt-1">Accepted: JPG, PNG, PDF (Max 5MB).</div>
                            </div>
                        <?php endif; ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold" for="co_notes">Special Instructions (Optional)</label>
                            <input class="form-control" id="co_notes" name="notes" placeholder="e.g. Call before arrival">
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-secondary">Subtotal (<?= $itemCount ?> units):</span>
                            <span class="fw-semibold"><?= money($cartTotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-secondary">Delivery Fee:</span>
                            <span class="text-success fw-semibold">FREE</span>
                        </div>
                        <div class="d-flex justify-content-between mb-4 pt-2 border-top">
                            <span class="h5 fw-bold mb-0">Total Amount:</span>
                            <span class="h4 fw-bold text-primary mb-0"><?= money($cartTotal) ?></span>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 shadow-sm">
                            <i class="bi bi-shield-check me-1"></i>Place Order Now
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php
/**
 * PharmaCare — Dedicated Staff & System User Administration View
 * Strictly restricted to users with the ADMIN role (RBAC Enforcement).
 * Provides complete directory inspection, account creation, activation toggle,
 * and secure credential resetting.
 */

declare(strict_types=1);

require_admin();

// Fetch all system users
$allUsers = fetch_all('SELECT id, full_name, username, email, phone, role, enabled, created_at FROM app_users ORDER BY id ASC');

$totalCount = count($allUsers);
$adminCount = 0;
$staffCount = 0;
$customerCount = 0;
$activeCount = 0;

foreach ($allUsers as $u) {
    if ((int)$u['enabled'] === 1) $activeCount++;
    if ($u['role'] === 'ADMIN') $adminCount++;
    elseif ($u['role'] === 'STAFF') $staffCount++;
    else $customerCount++;
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <div class="text-secondary small fw-bold text-uppercase">Access Control &amp; Personnel</div>
        <h2 class="h4 mb-0 fw-bold">User &amp; Staff Management</h2>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="<?= e(url('dashboard')) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
        </a>
        <button class="btn btn-primary shadow-sm" type="button" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="bi bi-person-plus me-1"></i>Add New User
        </button>
    </div>
</div>

<!-- High-Level Personnel KPI Counters -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-people-fill fs-5"></i>
                </div>
                <div>
                    <span class="text-secondary small d-block">Total Accounts</span>
                    <strong class="fs-5 text-dark"><?= $totalCount ?></strong>
                    <span class="small text-muted d-block"><?= $activeCount ?> active</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info-subtle text-info p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-hospital fs-5"></i>
                </div>
                <div>
                    <span class="text-secondary small d-block">Dispensary Staff</span>
                    <strong class="fs-5 text-dark"><?= $staffCount ?></strong>
                    <span class="small text-muted d-block">Pharmacists</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-danger-subtle text-danger p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-lock-fill fs-5"></i>
                </div>
                <div>
                    <span class="text-secondary small d-block">Administrators</span>
                    <strong class="fs-5 text-dark"><?= $adminCount ?></strong>
                    <span class="small text-muted d-block">Full system access</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3 d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <i class="bi bi-cart-check-fill fs-5"></i>
                </div>
                <div>
                    <span class="text-secondary small d-block">Online Customers</span>
                    <strong class="fs-5 text-dark"><?= $customerCount ?></strong>
                    <span class="small text-muted d-block">Storefront buyers</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- User Directory Table Card -->
<div class="card shadow-sm mb-4" id="users-section">
    <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <strong class="d-flex align-items-center">
                <i class="bi bi-person-lines-fill me-2 text-primary"></i>System User Directory
            </strong>
            <div class="small text-secondary">Registered personnel, roles, account status, and credential management</div>
        </div>
        <div class="small text-muted">
            Displaying <?= $totalCount ?> accounts
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Username &amp; Full Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Account Status</th>
                    <th>Created At</th>
                    <th class="text-end" style="min-width: 200px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allUsers as $u): ?>
                    <tr>
                        <td class="text-secondary small font-monospace">#<?= (int)$u['id'] ?></td>
                        <td>
                            <strong class="text-dark d-block">@<?= e($u['username']) ?></strong>
                            <span class="small text-secondary"><?= e($u['full_name']) ?></span>
                            <?php if ((int)$u['id'] === (int)user()['id']): ?>
                                <span class="badge text-bg-light border text-muted ms-1">You</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-secondary small"><?= e($u['email'] ?: '—') ?></td>
                        <td>
                            <?php 
                                $rBadge = match($u['role']) {
                                    'ADMIN' => 'danger',
                                    'STAFF' => 'primary',
                                    'CUSTOMER' => 'info',
                                    default => 'secondary'
                                };
                            ?>
                            <span class="badge text-bg-<?= $rBadge ?>">
                                <?= e($u['role']) ?>
                            </span>
                        </td>
                        <td>
                            <span class="badge text-bg-<?= $u['enabled'] ? 'success' : 'secondary' ?>">
                                <?= $u['enabled'] ? 'Active' : 'Disabled' ?>
                            </span>
                        </td>
                        <td class="text-secondary small"><?= nice_date($u['created_at'], true) ?></td>
                        <td class="text-end">
                            <?php if ((int)$u['id'] !== (int)user()['id']): ?>
                                <div class="d-inline-flex gap-1">
                                    <form action="actions.php" method="post" class="d-inline m-0">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="toggle_user_status">
                                        <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                                        <button class="btn btn-sm btn-outline-<?= $u['enabled'] ? 'danger' : 'success' ?>"
                                                onclick="return confirm('<?= $u['enabled'] ? 'Disable' : 'Enable' ?> user account @<?= e($u['username']) ?>?');">
                                            <?= $u['enabled'] ? 'Disable' : 'Enable' ?>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-reset-password"
                                            data-bs-toggle="modal" data-bs-target="#resetPasswordModal"
                                            data-user-id="<?= (int)$u['id'] ?>" data-username="<?= e($u['username']) ?>" title="Reset Password">
                                        <i class="bi bi-key me-1"></i>Reset Password
                                    </button>
                                </div>
                            <?php else: ?>
                                <span class="badge text-bg-light border text-muted">Current Session</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add New User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow">
            <form action="actions.php" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="create_user">
                <div class="modal-header bg-light">
                    <h5 class="modal-title h6 mb-0" id="addUserModalLabel">
                        <i class="bi bi-person-plus text-primary me-2"></i>Add New System User
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="modal_fullname">Full Name *</label>
                        <input type="text" id="modal_fullname" name="full_name" class="form-control form-control-sm" maxlength="80" required placeholder="e.g. Dr. Mahdi Hasan">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="modal_username">Username *</label>
                        <input type="text" id="modal_username" name="username" class="form-control form-control-sm" minlength="3" maxlength="50" pattern="[A-Za-z0-9_.-]+" autocomplete="off" required placeholder="e.g. mahdi.staff">
                        <div class="form-text text-muted" style="font-size: 11px;">Alphanumeric characters, dots, underscores or hyphens (3–50 chars)</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="modal_email">Email Address</label>
                        <input type="email" id="modal_email" name="email" class="form-control form-control-sm" maxlength="100" placeholder="e.g. staff@pharmacare.local">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="modal_role">Role *</label>
                        <select class="form-select form-select-sm" id="modal_role" name="role" required>
                            <option value="STAFF" selected>STAFF (Dispensary Pharmacist)</option>
                            <option value="ADMIN">ADMIN (Full Administrator)</option>
                            <option value="CUSTOMER">CUSTOMER (Online Storefront)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="modal_password">Initial Password *</label>
                        <input type="password" id="modal_password" name="password" class="form-control form-control-sm" minlength="8" maxlength="72" autocomplete="new-password" required placeholder="Minimum 8 characters">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-circle me-1"></i>Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content shadow">
            <form action="actions.php" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="reset_user_password">
                <input type="hidden" name="id" id="reset_user_id" value="">
                <div class="modal-header bg-light">
                    <h5 class="modal-title h6 mb-0" id="resetPasswordModalLabel">
                        <i class="bi bi-key text-warning me-1"></i>Reset Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-3">
                    <p class="small text-muted mb-2">Setting new password for <strong id="reset_user_name_display" class="text-dark"></strong>:</p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="modal_new_password">New Password *</label>
                        <input type="password" id="modal_new_password" name="new_password" class="form-control form-control-sm" minlength="8" maxlength="72" autocomplete="new-password" required placeholder="Min 8 characters">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning btn-sm">Set Password</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-reset-password').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var userIdInput = document.getElementById('reset_user_id');
            var userNameDisplay = document.getElementById('reset_user_name_display');
            if (userIdInput) userIdInput.value = this.dataset.userId;
            if (userNameDisplay) userNameDisplay.textContent = '@' + this.dataset.username;
        });
    });
});
</script>

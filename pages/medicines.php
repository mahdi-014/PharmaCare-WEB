<?php
/**
 * PharmaCare — Medicine Inventory & Master Data Management View
 * Unified module combining:
 * 1. Inventory Catalog & Stock Inspection (with multi-attribute search and filters)
 * 2. Add / Edit Medicine Form
 * 3. Pharmaceutical Category Management
 * 4. Supplier / Manufacturer Directory
 */

declare(strict_types=1);

require_staff_or_admin();

// Determine active sub-tab
$tab = trim((string)($_GET['tab'] ?? ''));
if ($tab === '' || !in_array($tab, ['inventory', 'medicine_form', 'categories', 'suppliers'], true)) {
    if (isset($_GET['edit_med']) || isset($_GET['add_med']) || isset($_GET['id'])) {
        $tab = 'medicine_form';
    } elseif (isset($_GET['edit_cat'])) {
        $tab = 'categories';
    } elseif (isset($_GET['edit_sup'])) {
        $tab = 'suppliers';
    } else {
        $tab = 'inventory';
    }
}

// -------------------------------------------------------------
// TAB 1: Inventory Data
// -------------------------------------------------------------
$keyword = trim((string)($_GET['keyword'] ?? ''));
$categoryId = (int)($_GET['category_id'] ?? 0);
$selectedStatus = strtoupper(trim((string)($_GET['status'] ?? '')));
$categoriesList = fetch_all('SELECT id, name FROM categories ORDER BY name');
$suppliersList = fetch_all('SELECT id, name, company_name FROM suppliers ORDER BY name');

$params = [];
$sql = medicine_query() . ' WHERE 1=1';

if ($keyword !== '') {
    $sql .= ' AND (m.name LIKE ? OR m.generic_name LIKE ? OR m.brand_name LIKE ? OR m.batch_number LIKE ?)';
    $needle = '%' . $keyword . '%';
    array_push($params, $needle, $needle, $needle, $needle);
}

if ($categoryId) {
    $sql .= ' AND m.category_id = ?';
    $params[] = $categoryId;
}

$allMedicines = fetch_all($sql . ' ORDER BY m.name, m.batch_number', $params);
$medicines = $selectedStatus !== ''
    ? array_values(array_filter($allMedicines, fn($m) => medicine_status($m) === $selectedStatus))
    : $allMedicines;

// -------------------------------------------------------------
// TAB 2: Add / Edit Medicine Data
// -------------------------------------------------------------
$medId = (int)($_GET['id'] ?? $_GET['edit_med'] ?? 0);
$medEdit = $medId ? fetch_one('SELECT * FROM medicines WHERE id = ?', [$medId]) : null;
$m = $medEdit ?? [
    'id' => '',
    'name' => '',
    'generic_name' => '',
    'brand_name' => '',
    'batch_number' => '',
    'medicine_type' => 'TABLET',
    'purchase_price' => '',
    'selling_price' => '',
    'quantity' => 0,
    'minimum_stock_level' => 10,
    'manufacturing_date' => '',
    'expiry_date' => '',
    'prescription_required' => 0,
    'category_id' => '',
    'supplier_id' => ''
];

// -------------------------------------------------------------
// TAB 3: Category Data
// -------------------------------------------------------------
$catEditId = (int)($_GET['edit_cat'] ?? 0);
$catEdit = $catEditId ? fetch_one('SELECT * FROM categories WHERE id = ?', [$catEditId]) : null;
$categoriesAll = fetch_all('SELECT c.*, (SELECT COUNT(*) FROM medicines WHERE category_id = c.id) AS medicines_count FROM categories c ORDER BY c.name');

// -------------------------------------------------------------
// TAB 4: Supplier Data
// -------------------------------------------------------------
$supEditId = (int)($_GET['edit_sup'] ?? 0);
$supEdit = $supEditId ? fetch_one('SELECT * FROM suppliers WHERE id = ?', [$supEditId]) : null;
$suppliersAll = fetch_all('SELECT s.*, (SELECT COUNT(*) FROM medicines m WHERE m.supplier_id = s.id) AS medicines_count FROM suppliers s ORDER BY s.name');
?>

<div class="page-intro mb-3 d-flex justify-content-between align-items-center">
    <div>
        <h2>Medicine Inventory &amp; Master Catalog</h2>
        <p>Manage pharmaceutical stock, formulations, categories, and distributors.</p>
    </div>
    <?php if (is_admin()): ?>
        <div class="d-flex gap-2">
            <a href="index.php?page=medicines&tab=medicine_form" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i>Add Medicine
            </a>
            <a href="index.php?page=medicines&tab=categories" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-tags me-1"></i>Categories
            </a>
            <a href="index.php?page=medicines&tab=suppliers" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-truck me-1"></i>Suppliers
            </a>
        </div>
    <?php endif; ?>
</div>

<!-- Navigation Tabs -->
<ul class="nav nav-tabs mb-4">
    <li class="nav-item">
        <a class="nav-link <?= $tab === 'inventory' ? 'active fw-bold' : '' ?>" href="index.php?page=medicines&tab=inventory">
            <i class="bi bi-capsule me-1"></i>Medicine Catalog (<?= count($medicines) ?>)
        </a>
    </li>
    <?php if (is_admin()): ?>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'medicine_form' ? 'active fw-bold' : '' ?>" href="index.php?page=medicines&tab=medicine_form">
                <i class="bi bi-pencil-square me-1"></i><?= $medEdit ? 'Edit Medicine' : 'Add New Medicine' ?>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'categories' ? 'active fw-bold' : '' ?>" href="index.php?page=medicines&tab=categories">
                <i class="bi bi-tags me-1"></i>Categories (<?= count($categoriesAll) ?>)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $tab === 'suppliers' ? 'active fw-bold' : '' ?>" href="index.php?page=medicines&tab=suppliers">
                <i class="bi bi-truck me-1"></i>Suppliers (<?= count($suppliersAll) ?>)
            </a>
        </li>
    <?php endif; ?>
</ul>

<?php if ($tab === 'inventory'): ?>
    <!-- Search & Filter Controls -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="index.php" class="row g-3 align-items-end">
                <input type="hidden" name="page" value="medicines">
                <input type="hidden" name="tab" value="inventory">

                <div class="col-lg-5">
                    <label for="keyword" class="form-label small fw-semibold">Search Medicines</label>
                    <input id="keyword" name="keyword" class="form-control" value="<?= e($keyword) ?>" placeholder="Search name, generic, brand or batch...">
                </div>

                <div class="col-md-4 col-lg-3">
                    <label for="category" class="form-label small fw-semibold">Category</label>
                    <select name="category_id" id="category" class="form-select">
                        <option value="">All categories</option>
                        <?php foreach ($categoriesList as $c): ?>
                            <option value="<?= e((string)$c['id']) ?>" <?= $categoryId === (int)$c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2">
                    <label for="status" class="form-label small fw-semibold">Stock Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All statuses</option>
                        <?php foreach (['AVAILABLE', 'LOW_STOCK', 'OUT_OF_STOCK', 'EXPIRING_SOON', 'EXPIRED', 'INACTIVE'] as $s): ?>
                            <option value="<?= e($s) ?>" <?= $s === $selectedStatus ? 'selected' : '' ?>>
                                <?= e(status_label($s)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-4 col-lg-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1" title="Search" type="submit">
                        <i class="bi bi-search me-1"></i>Filter
                    </button>
                    <a class="btn btn-light border" title="Reset Filters" href="index.php?page=medicines&tab=inventory">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Medicine</th>
                        <th>Category &amp; Supplier</th>
                        <th>Batch</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Expiry Date</th>
                        <th>Status</th>
                        <?php if (is_admin()): ?>
                            <th class="text-end">Actions</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($medicines as $medItem):
                        $st = medicine_status($medItem);
                    ?>
                        <tr>
                            <td>
                                <strong><?= e($medItem['name']) ?></strong>
                                <span class="subtext d-block"><?= e($medItem['generic_name']) ?> &middot; <?= e(ucfirst(strtolower($medItem['medicine_type']))) ?></span>
                            </td>
                            <td>
                                <?= e($medItem['category_name']) ?>
                                <span class="subtext d-block"><?= e($medItem['supplier_company'] ?: $medItem['supplier_name']) ?></span>
                            </td>
                            <td><span class="fw-semibold font-monospace"><?= e($medItem['batch_number']) ?></span></td>
                            <td>
                                <strong class="text-success"><?= money($medItem['selling_price']) ?></strong>
                                <span class="subtext d-block">Cost <?= money($medItem['purchase_price']) ?></span>
                            </td>
                            <td>
                                <strong><?= (int)$medItem['quantity'] ?></strong>
                                <span class="subtext d-block">Min: <?= (int)$medItem['minimum_stock_level'] ?></span>
                            </td>
                            <td>
                                <?= e(nice_date($medItem['expiry_date'])) ?>
                                <?php if ($medItem['expiry_date'] < date('Y-m-d')): ?>
                                    <span class="badge text-bg-danger d-block mt-1">Expired</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge text-bg-<?= e(status_badge($st)) ?>">
                                    <?= e(status_label($st)) ?>
                                </span>
                                <?php if ($medItem['prescription_required']): ?>
                                    <span class="badge text-bg-danger-subtle text-danger border border-danger d-block mt-1">Rx Required</span>
                                <?php endif; ?>
                            </td>
                            <?php if (is_admin()): ?>
                                <td class="text-end text-nowrap">
                                    <a class="btn btn-sm btn-light border" title="Edit" href="index.php?page=medicines&tab=medicine_form&id=<?= (int)$medItem['id'] ?>">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form class="d-inline" action="actions.php" method="post">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="action" value="medicine_toggle">
                                        <input type="hidden" name="id" value="<?= (int)$medItem['id'] ?>">
                                        <button class="btn btn-sm btn-outline-<?= $medItem['active'] ? 'danger' : 'success' ?>"
                                                title="<?= $medItem['active'] ? 'Deactivate' : 'Activate' ?>"
                                                onclick="return confirm('Change active status for <?= e($medItem['name']) ?>?');">
                                            <i class="bi bi-<?= $medItem['active'] ? 'slash-circle' : 'check-circle' ?>"></i>
                                        </button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$medicines): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-search fs-1 d-block mb-2"></i>
                                No medicines matched your search criteria.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php elseif ($tab === 'medicine_form' && is_admin()): ?>
    <!-- Add / Edit Medicine Form -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <strong><?= $medEdit ? 'Edit Medicine Formulation' : 'Create New Medicine Record' ?></strong>
            <a href="index.php?page=medicines&tab=inventory" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to Inventory
            </a>
        </div>
        <div class="card-body">
            <form action="actions.php" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="medicine_save">
                <input type="hidden" name="id" value="<?= e((string)$m['id']) ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" for="m_name">Medicine Name *</label>
                        <input class="form-control" type="text" id="m_name" name="name" value="<?= e((string)$m['name']) ?>" required maxlength="120" placeholder="e.g. Napa Extra">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" for="m_generic">Generic Chemical Name *</label>
                        <input class="form-control" type="text" id="m_generic" name="generic_name" value="<?= e((string)$m['generic_name']) ?>" required maxlength="120" placeholder="e.g. Paracetamol + Caffeine">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" for="m_brand">Brand / Trade Name</label>
                        <input class="form-control" type="text" id="m_brand" name="brand_name" value="<?= e((string)$m['brand_name']) ?>" maxlength="120" placeholder="e.g. Beximco Pharma">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" for="m_batch">Batch Number *</label>
                        <input class="form-control font-monospace" type="text" id="m_batch" name="batch_number" value="<?= e((string)$m['batch_number']) ?>" required maxlength="60" placeholder="e.g. NP-2026-001">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" for="m_type">Dosage Form *</label>
                        <select id="m_type" name="medicine_type" class="form-select" required>
                            <?php foreach (medicine_types() as $val => $lbl): ?>
                                <option value="<?= e($val) ?>" <?= $val === $m['medicine_type'] ? 'selected' : '' ?>>
                                    <?= e($lbl) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" for="m_category">Category *</label>
                        <select id="m_category" class="form-select" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php foreach ($categoriesList as $c): ?>
                                <option value="<?= e((string)$c['id']) ?>" <?= (string)$m['category_id'] === (string)$c['id'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold" for="m_supplier">Supplier *</label>
                        <select id="m_supplier" class="form-select" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            <?php foreach ($suppliersList as $s): ?>
                                <option value="<?= e((string)$s['id']) ?>" <?= (string)$m['supplier_id'] === (string)$s['id'] ? 'selected' : '' ?>>
                                    <?= e($s['name']) ?> (<?= e($s['company_name'] ?: 'Distributor') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" for="m_purchase">Purchase Cost (৳) *</label>
                        <input class="form-control" type="number" step="0.01" min="0.01" id="m_purchase" name="purchase_price" value="<?= e((string)$m['purchase_price']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" for="m_selling">Selling Price (৳) *</label>
                        <input class="form-control" type="number" step="0.01" min="0.01" id="m_selling" name="selling_price" value="<?= e((string)$m['selling_price']) ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" for="m_qty">Current Stock Units *</label>
                        <input class="form-control" type="number" min="0" id="m_qty" name="quantity" value="<?= (int)$m['quantity'] ?>" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold" for="m_min">Reorder Threshold *</label>
                        <input class="form-control" type="number" min="1" id="m_min" name="minimum_stock_level" value="<?= (int)$m['minimum_stock_level'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" for="m_mfg">Manufacturing Date</label>
                        <input class="form-control" type="date" id="m_mfg" name="manufacturing_date" value="<?= e((string)$m['manufacturing_date']) ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold" for="m_exp">Expiry Date (FEFO) *</label>
                        <input class="form-control" type="date" id="m_exp" name="expiry_date" value="<?= e((string)$m['expiry_date']) ?>" required>
                    </div>
                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="m_rx" name="prescription_required" value="1" <?= !empty($m['prescription_required']) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold" for="m_rx">
                                Requires Doctor's Prescription (Rx) for Online Customer Purchase
                            </label>
                        </div>
                    </div>
                    <div class="col-12 mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check2-circle me-1"></i><?= $medEdit ? 'Update Medicine' : 'Save Medicine' ?>
                        </button>
                        <a href="index.php?page=medicines&tab=inventory" class="btn btn-outline-secondary px-4">Cancel</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

<?php elseif ($tab === 'categories' && is_admin()): ?>
    <!-- Category Management -->
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <strong><?= $catEdit ? 'Edit Category' : 'Add New Category' ?></strong>
                </div>
                <div class="card-body">
                    <form action="actions.php" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="category_save">
                        <input type="hidden" name="id" value="<?= e((string)($catEdit['id'] ?? '')) ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="cat_name">Category Name *</label>
                            <input class="form-control" id="cat_name" name="name" value="<?= e($catEdit['name'] ?? '') ?>" maxlength="80" required placeholder="e.g. Antibiotics">
                        </div>

                        <div class="mb-3">
                            <label for="cat_desc" class="form-label small fw-semibold">Description</label>
                            <textarea class="form-control" rows="3" id="cat_desc" name="description" maxlength="255" placeholder="Optional category scope details"><?= e($catEdit['description'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary flex-grow-1">
                                <i class="bi bi-check-lg me-1"></i>Save Category
                            </button>
                            <?php if ($catEdit): ?>
                                <a href="index.php?page=medicines&tab=categories" class="btn btn-light border">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <strong>Category Directory</strong>
                    <span class="badge text-bg-light border text-muted"><?= count($categoriesAll) ?> categories</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Category Name</th>
                                <th>Description</th>
                                <th class="text-center">Medicines</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categoriesAll as $cat): ?>
                                <tr>
                                    <td class="fw-semibold text-dark"><?= e($cat['name']) ?></td>
                                    <td class="small text-secondary"><?= e($cat['description'] ?: '—') ?></td>
                                    <td class="text-center">
                                        <span class="badge text-bg-secondary rounded-pill"><?= (int)$cat['medicines_count'] ?></span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="index.php?page=medicines&tab=categories&edit_cat=<?= (int)$cat['id'] ?>" class="btn btn-sm btn-light border" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="actions.php" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="category_delete">
                                            <input type="hidden" name="id" value="<?= (int)$cat['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete category &quot;<?= e($cat['name']) ?>&quot;?');" <?= (int)$cat['medicines_count'] > 0 ? 'disabled' : '' ?>>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<?php elseif ($tab === 'suppliers' && is_admin()): ?>
    <!-- Supplier Management -->
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <strong><?= $supEdit ? 'Edit Supplier' : 'Add New Supplier' ?></strong>
                </div>
                <div class="card-body">
                    <form action="actions.php" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="supplier_save">
                        <input type="hidden" name="id" value="<?= e((string)($supEdit['id'] ?? '')) ?>">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="sup_name">Contact Person *</label>
                            <input type="text" id="sup_name" name="name" maxlength="100" class="form-control" value="<?= e($supEdit['name'] ?? '') ?>" required placeholder="e.g. Dr. Rafiqul Islam">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="sup_company">Company / Manufacturer</label>
                            <input type="text" id="sup_company" name="company_name" maxlength="120" class="form-control" value="<?= e($supEdit['company_name'] ?? '') ?>" placeholder="e.g. Square Pharmaceuticals">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="sup_phone">Phone Number *</label>
                            <input type="tel" id="sup_phone" name="phone" maxlength="30" class="form-control" value="<?= e($supEdit['phone'] ?? '') ?>" required placeholder="e.g. 01711000000">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="sup_email">Email Address</label>
                            <input type="email" id="sup_email" name="email" maxlength="120" class="form-control" value="<?= e($supEdit['email'] ?? '') ?>" placeholder="e.g. sales@squarepharma.com">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="sup_address">Physical Address</label>
                            <textarea class="form-control" rows="2" id="sup_address" maxlength="255" name="address" placeholder="e.g. Mohakhali C/A, Dhaka"><?= e($supEdit['address'] ?? '') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1" type="submit">
                                <i class="bi bi-check-lg me-1"></i>Save Supplier
                            </button>
                            <?php if ($supEdit): ?>
                                <a class="btn btn-light border" href="index.php?page=medicines&tab=suppliers">Cancel</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <strong>Supplier &amp; Distributor Directory</strong>
                    <span class="badge text-bg-light border text-muted"><?= count($suppliersAll) ?> suppliers</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Contact &amp; Company</th>
                                <th>Contact Info</th>
                                <th class="text-center">Medicines</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($suppliersAll as $sup): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= e($sup['name']) ?></div>
                                        <div class="small text-secondary"><?= e($sup['company_name'] ?: 'Independent Distributor') ?></div>
                                    </td>
                                    <td>
                                        <div class="small"><i class="bi bi-telephone me-1 text-muted"></i><?= e($sup['phone']) ?></div>
                                        <?php if (!empty($sup['email'])): ?>
                                            <div class="small text-muted"><i class="bi bi-envelope me-1"></i><?= e($sup['email']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge text-bg-secondary rounded-pill"><?= (int)$sup['medicines_count'] ?></span>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="index.php?page=medicines&tab=suppliers&edit_sup=<?= (int)$sup['id'] ?>" class="btn btn-sm btn-light border" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <form action="actions.php" method="post" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="supplier_delete">
                                            <input type="hidden" name="id" value="<?= (int)$sup['id'] ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete" onclick="return confirm('Delete supplier &quot;<?= e($sup['name']) ?>&quot;?');" <?= (int)$sup['medicines_count'] > 0 ? 'disabled' : '' ?>>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

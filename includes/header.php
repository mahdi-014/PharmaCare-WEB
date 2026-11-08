<?php
$active = match($page) {
    'order_detail' => 'orders',
    'invoice'      => 'sales',
    default        => $page
};

$heading = page_title($page);
$subtitles = [
    'dashboard'    => 'Real-time sales revenue, inventory health, and operations overview',
    'catalog'      => 'Browse, search, and order available medicines online',
    'cart'         => 'Review selected medicines and complete your order',
    'orders'       => is_customer() ? 'Track your active and previous medicine orders' : 'Staff order fulfillment and prescription verification queue',
    'order_detail' => 'Review order items, prescription documents, and fulfillment timeline',
    'medicines'    => 'Medicine inventory, stock levels, categories, and suppliers',
    'sales'        => 'Point of Sale counter billing and historical sales receipts',
    'invoice'      => 'Printable receipt for completed transaction',
    'login'        => 'Sign in to access your PharmaCare account',
    'register'     => 'Create a customer account to place medicine orders online',
];

$cartCount = cart_get_count();

if (!user()) {
    $links = [
        ['catalog', 'capsule-pill', 'Browse Medicines', 'catalog'],
    ];
    $brandUrl = url('catalog');
    $brandSubtitle = 'Online Storefront';
} elseif (is_customer()) {
    $cartLabel = 'Shopping Cart' . ($cartCount > 0 ? ' (' . $cartCount . ')' : '');
    $links = [
        ['catalog', 'capsule-pill', 'Storefront', 'catalog'],
        ['cart', 'cart3', $cartLabel, 'cart'],
        ['orders', 'bag-check-fill', 'My Orders', 'orders'],
    ];
    $brandUrl = url('catalog');
    $brandSubtitle = 'Customer Portal';
} else {
    $pendingOrdersCount = 0;
    try {
        $pendingOrdersCount = (int)scalar("SELECT COUNT(*) FROM orders WHERE order_status IN ('PENDING', 'CONFIRMED')");
    } catch (Throwable $e) {}
    $ordersBadge = $pendingOrdersCount > 0 ? ' (' . $pendingOrdersCount . ')' : '';

    $links = [
        ['dashboard', 'grid-1x2-fill', 'Dashboard', 'dashboard'],
        ['orders', 'box-seam-fill', 'Orders Queue' . $ordersBadge, 'orders'],
        ['medicines', 'capsule', 'Inventory & Drugs', 'medicines'],
        ['sales', 'cart-plus-fill', 'POS & Sales', 'sales'],
        ['catalog', 'shop', 'Storefront View', 'catalog'],
    ];
    $brandUrl = url('dashboard');
    $brandSubtitle = 'Dispensary Portal';
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="PharmaCare Pharmacy Management System">
<meta name="csrf-token" content="<?= e(csrf()) ?>">
<title><?= e($heading) ?> | PharmaCare</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>
<div class="sidebar-backdrop"></div>
<aside class="app-sidebar">
 <a class="brand" href="<?= e($brandUrl) ?>">
   <span class="brand-mark"><i class="bi bi-capsule-pill"></i></span>
   <span><strong>PharmaCare</strong><small><?= e($brandSubtitle) ?></small></span>
 </a>
 <nav class="sidebar-nav">
 <?php foreach ($links as [$dest, $icon, $label, $selected]): ?>
   <a class="<?= ($active === $selected ? 'active' : '') ?>" href="<?= e(url($dest)) ?>">
     <i class="bi bi-<?= e($icon) ?>"></i>
     <span><?= e($label) ?></span>
   </a>
 <?php endforeach; ?>
 </nav>
 <?php if (user()): ?>
  <div class="sidebar-footer">
   <div class="small text-white-50">Signed in as</div>
   <div class="fw-semibold text-white text-truncate"><?= e(user()['username']) ?> <small>(<?= e(user()['role']) ?>)</small></div>
   <form method="post" action="actions.php">
     <input type="hidden" name="action" value="logout">
     <?= csrf_field() ?>
     <button class="btn btn-sm btn-outline-light w-100" type="submit">
       <i class="bi bi-box-arrow-left me-1"></i><span>Logout</span>
     </button>
   </form>
  </div>
 <?php else: ?>
  <div class="sidebar-footer">
   <div class="small text-white-50">Customer Access</div>
   <a class="btn btn-sm btn-outline-light w-100 mt-2" href="<?= e(url('login')) ?>">
     <i class="bi bi-box-arrow-in-right me-1"></i><span>Sign In</span>
   </a>
  </div>
 <?php endif; ?>
</aside>

<div class="app-main">
 <header class="app-topbar">
  <div class="d-flex align-items-center gap-3">
    <button type="button" class="btn btn-sm btn-light border d-lg-none" data-sidebar-toggle aria-label="Toggle Navigation">
      <i class="bi bi-list fs-5"></i>
    </button>
    <div>
      <h1><?= e($heading) ?></h1>
      <p><?= e($subtitles[$page] ?? 'Pharmacy Management & Online Ordering System') ?></p>
    </div>
  </div>
  <div class="topbar-user">
   <?php if (user()): ?>
    <div class="text-end">
     <strong><?= e(user()['full_name']) ?></strong>
     <small><?= e(user()['role']) ?></small>
    </div>
    <div class="avatar"><i class="bi bi-person-fill"></i></div>
   <?php else: ?>
    <a href="<?= e(url('login')) ?>" class="btn btn-outline-primary btn-sm">Sign In</a>
    <a href="<?= e(url('register')) ?>" class="btn btn-primary btn-sm">Register</a>
   <?php endif; ?>
  </div>
 </header>
 <main class="content">
  <?php show_flashes(); ?>

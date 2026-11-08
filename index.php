<?php
/**
 * PharmaCare — Single Front Controller & Routing Dispatcher
 * Dispatches HTTP requests to the 10 consolidated core views with
 * strict RBAC enforcement, session initialization, and layout templating.
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';

$page = (string)($_GET['page'] ?? (user() && is_customer() ? 'catalog' : 'dashboard'));

// Legacy route redirects to consolidated views
$legacyRedirects = [
    'checkout'              => fn() => redirect('cart'),
    'my_orders'             => fn() => redirect('orders'),
    'order_confirmation'    => fn() => redirect('order_detail', ['id' => $_GET['id'] ?? 0, 'confirmed' => 1]),
    'download_prescription' => fn() => redirect('order_detail', ['id' => $_GET['id'] ?? $_GET['order_id'] ?? 0, 'download' => 1]),
    'medicine_form'         => fn() => redirect('medicines', ['tab' => 'medicine_form', 'id' => $_GET['id'] ?? 0]),
    'categories'            => fn() => redirect('medicines', ['tab' => 'categories']),
    'suppliers'             => fn() => redirect('medicines', ['tab' => 'suppliers']),
    'sale_form'             => fn() => redirect('sales', ['tab' => 'pos']),
    'users'                 => fn() => redirect('dashboard'),
    'profile'               => fn() => redirect('dashboard'),
];

if (isset($legacyRedirects[$page])) {
    $legacyRedirects[$page]();
    exit;
}

// 10 Consolidated Core Views
$routes = [
    'login', 'register', 'dashboard', 'catalog',
    'cart', 'orders', 'order_detail', 'medicines', 'sales', 'invoice'
];

if (!in_array($page, $routes, true)) {
    http_response_code(404);
    require __DIR__ . '/includes/header.php';
    echo '<div class="alert alert-warning my-5 text-center shadow-sm">
            <h4><i class="bi bi-exclamation-circle me-2"></i>Page Not Found</h4>
            <p class="mb-3 text-muted">The requested page does not exist or has been moved.</p>
            <a href="index.php" class="btn btn-primary btn-sm"><i class="bi bi-house me-1"></i>Return Home</a>
          </div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// Public authentication views (standalone layout)
if ($page === 'login') {
    if (user()) redirect(is_customer() ? 'catalog' : 'dashboard');
    include __DIR__ . '/pages/login.php';
    exit;
}

if ($page === 'register') {
    if (user()) redirect(is_customer() ? 'catalog' : 'dashboard');
    include __DIR__ . '/pages/register.php';
    exit;
}

// Public catalog viewing for guest visitors
if ($page === 'catalog' && !user()) {
    require __DIR__ . '/includes/header.php';
    include __DIR__ . '/pages/catalog.php';
    require __DIR__ . '/includes/footer.php';
    exit;
}

// All remaining routes require authentication
require_login();

// Strict Role-Based Access Control (RBAC)
$staffOrAdminPages = ['dashboard', 'medicines', 'sales'];
$customerOnlyPages = ['cart'];

if (in_array($page, $staffOrAdminPages, true)) {
    require_staff_or_admin();
} elseif (in_array($page, $customerOnlyPages, true)) {
    require_customer();
}

// Render view wrapped in layout header and footer
require __DIR__ . '/includes/header.php';
include __DIR__ . '/pages/' . $page . '.php';
require __DIR__ . '/includes/footer.php';

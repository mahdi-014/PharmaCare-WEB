<?php
/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Unified Asynchronous JSON Web API Router (Q6 Vanilla JS Fetch)
 *
 * Dispatches JSON API requests based on action parameter:
 * - action=search      : Live medicine catalog search (GET)
 * - action=cart_add    : Asynchronous cart addition (POST)
 * - action=cart_update : Asynchronous cart quantity update / remove (POST)
 * - action=status      : Live order fulfillment tracking polling (GET)
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

$controller = new \App\Controllers\Controller();

$action = (string)($_GET['action'] ?? '');
if ($action === '') {
    $input = api_get_json_input();
    $action = (string)($input['action'] ?? '');
}

// Autodetection for query-based API calls
if ($action === '') {
    if (isset($_GET['q'])) {
        $action = 'search';
    } elseif (isset($_GET['id'])) {
        $action = 'status';
    } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = 'cart_add';
    }
}

match ($action) {
    'search'                 => $controller->searchApi(),
    'cart_add', 'add'        => $controller->cartAddApi(),
    'cart_update', 'update'  => $controller->cartUpdateApi(),
    'status', 'order_status' => $controller->orderStatusApi(),
    default                  => api_json_response(false, "Unknown API action '{$action}'.", null, 400),
};

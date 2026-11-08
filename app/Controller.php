<?php
/**
 * CIS324 Web Engineering - Topic 13: Pharmacy Management System
 * Consolidated Application Controller & Service Layer (Q8 Layered MVC)
 */

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Medicine;
use App\Models\Order;
use App\Models\User;
use App\Services\InventoryService;
use PDO;
use DomainException;
use Throwable;

class Controller
{
    // ==========================================
    // 1. Storefront & Catalog
    // ==========================================

    public function catalog(): void
    {
        $keyword = trim((string)($_GET['keyword'] ?? ''));
        $categoryId = (int)($_GET['category_id'] ?? 0);

        $categories = Medicine::getAllCategories();
        $medicines = Medicine::findActive($keyword, $categoryId);

        include dirname(__DIR__) . '/pages/catalog.php';
    }

    public function searchApi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            api_json_response(false, 'Method not allowed. Expected GET request.', null, 405);
        }

        try {
            $keyword = trim((string)($_GET['q'] ?? $_GET['keyword'] ?? ''));
            $categoryId = (int)($_GET['category_id'] ?? 0);

            $rows = Medicine::findActive($keyword, $categoryId);
            $cart = cart_get_items();

            $medicines = array_map(function (array $m) use ($cart): array {
                $status = medicine_status($m);
                $cartQty = isset($cart[$m['id']]) ? (int)$cart[$m['id']]['quantity'] : 0;
                return [
                    'id'                    => (int)$m['id'],
                    'name'                  => (string)$m['name'],
                    'generic_name'          => (string)$m['generic_name'],
                    'brand_name'            => (string)($m['brand_name'] ?? ''),
                    'batch_number'          => (string)$m['batch_number'],
                    'medicine_type'         => (string)$m['medicine_type'],
                    'dosage_form'           => ucfirst(strtolower((string)$m['medicine_type'])),
                    'selling_price'         => (float)$m['selling_price'],
                    'formatted_price'       => money($m['selling_price']),
                    'quantity'              => (int)$m['quantity'],
                    'prescription_required' => (bool)$m['prescription_required'],
                    'category_id'           => (int)$m['category_id'],
                    'category_name'         => (string)$m['category_name'],
                    'manufacturer'          => (string)($m['supplier_company'] ?: $m['supplier_name']),
                    'expiry_date'           => (string)$m['expiry_date'],
                    'formatted_expiry'      => nice_date($m['expiry_date']),
                    'status'                => $status,
                    'status_label'          => status_label($status),
                    'status_badge'          => status_badge($status),
                    'in_cart_quantity'      => $cartQty,
                ];
            }, $rows);

            api_json_response(true, 'Medicines retrieved successfully.', [
                'count'        => count($medicines),
                'medicines'    => $medicines,
                'cart_count'   => cart_get_count(),
                'is_logged_in' => user() !== null,
                'is_customer'  => is_customer(),
            ], 200);

        } catch (Throwable $e) {
            api_json_response(false, 'Failed to query medicines: ' . $e->getMessage(), null, 500);
        }
    }

    // ==========================================
    // 2. Shopping Cart & Form Handlers
    // ==========================================

    public function cart(): void
    {
        require_customer();
        include dirname(__DIR__) . '/pages/cart.php';
    }

    public function cartAdd(): void
    {
        require_customer();
        $medicineId = positive_int($_POST['medicine_id'] ?? 0, 'Medicine ID');
        $requestedQty = positive_int($_POST['quantity'] ?? 1, 'Quantity', false);

        $medicine = Medicine::findById($medicineId);
        if (!$medicine) {
            throw new DomainException('Selected medicine batch was not found.');
        }

        $currentCartQty = (int)($_SESSION['cart'][$medicineId]['quantity'] ?? 0);
        $maxStock = (int)$medicine['quantity'];

        $finalQty = cart_add_item($medicine, $requestedQty);

        if ($currentCartQty + $requestedQty > $maxStock) {
            flash('warning', 'Only ' . $maxStock . ' unit(s) available in stock. Cart updated to ' . $finalQty . ' unit(s).');
        } else {
            flash('success', 'Added ' . $requestedQty . ' × ' . $medicine['name'] . ' to your cart.');
        }

        $redirectTo = (string)($_POST['redirect_to'] ?? 'cart');
        redirect($redirectTo === 'catalog' ? 'catalog' : 'cart');
    }

    public function cartAddApi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            api_json_response(false, 'Method not allowed. Expected POST request.', null, 405);
        }

        if (!user()) {
            api_json_response(false, 'Authentication required. Please sign in to order medicines.', null, 401);
        }

        if (!is_customer()) {
            api_json_response(false, 'Access forbidden. Staff accounts cannot use customer cart.', null, 403);
        }

        api_csrf_verify();

        try {
            $input = api_get_json_input();
            $medicineId = (int)($input['medicine_id'] ?? 0);
            $quantity = (int)($input['quantity'] ?? 1);

            if ($medicineId <= 0 || $quantity <= 0) {
                api_json_response(false, 'Invalid medicine ID or quantity.', null, 400);
            }

            $medicine = Medicine::findById($medicineId);
            if (!$medicine) {
                api_json_response(false, 'Medicine batch not found.', null, 404);
            }

            $finalQty = cart_add_item($medicine, $quantity);

            api_json_response(true, "Added {$quantity} × {$medicine['name']} to your cart.", [
                'item' => [
                    'id'                   => $medicineId,
                    'name'                 => $medicine['name'],
                    'quantity'             => $finalQty,
                    'max_stock'            => (int)$medicine['quantity'],
                    'price'                => (float)$medicine['selling_price'],
                    'line_total'           => round(((float)$medicine['selling_price']) * $finalQty, 2),
                    'formatted_line_total' => money(((float)$medicine['selling_price']) * $finalQty),
                ],
                'cart_count'            => cart_get_count(),
                'line_count'            => cart_get_line_count(),
                'cart_total'            => cart_get_total(),
                'formatted_cart_total'  => money(cart_get_total()),
                'requires_prescription' => cart_requires_prescription(),
            ], 200);

        } catch (DomainException $e) {
            api_json_response(false, $e->getMessage(), null, 400);
        } catch (Throwable $e) {
            api_json_response(false, 'Failed to add item to cart: ' . $e->getMessage(), null, 500);
        }
    }

    public function cartUpdate(): void
    {
        require_customer();
        $medicineId = positive_int($_POST['medicine_id'] ?? 0, 'Medicine ID');
        $quantity = positive_int($_POST['quantity'] ?? 0, 'Quantity', true);

        $medicine = Medicine::findById($medicineId);
        if (!$medicine || !(int)$medicine['active'] || $medicine['expiry_date'] < date('Y-m-d')) {
            cart_remove_item($medicineId);
            throw new DomainException('This medicine is no longer available and has been removed from your cart.');
        }

        $maxStock = (int)$medicine['quantity'];
        if ($quantity <= 0) {
            cart_remove_item($medicineId);
            flash('success', 'Item removed from your cart.');
        } else {
            if ($quantity > $maxStock) {
                cart_update_item($medicineId, $maxStock, $maxStock);
                flash('warning', 'Requested quantity exceeds stock. Adjusted to ' . $maxStock . ' unit(s).');
            } else {
                cart_update_item($medicineId, $quantity, $maxStock);
                flash('success', 'Cart updated successfully.');
            }
        }
        redirect('cart');
    }

    public function cartUpdateApi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            api_json_response(false, 'Method not allowed. Expected POST request.', null, 405);
        }

        if (!user()) {
            api_json_response(false, 'Authentication required. Please sign in.', null, 401);
        }

        if (!is_customer()) {
            api_json_response(false, 'Access forbidden.', null, 403);
        }

        api_csrf_verify();

        try {
            $input = api_get_json_input();
            $medicineId = (int)($input['medicine_id'] ?? 0);
            $quantity = isset($input['quantity']) ? (int)$input['quantity'] : -1;

            if ($medicineId <= 0 || $quantity < 0) {
                api_json_response(false, 'Invalid medicine ID or quantity.', null, 400);
            }

            $cart = cart_get_items();
            if (!isset($cart[$medicineId])) {
                api_json_response(false, 'Item is not currently in your cart.', null, 404);
            }

            if ($quantity === 0) {
                $itemName = $cart[$medicineId]['name'];
                cart_remove_item($medicineId);
                api_json_response(true, "Removed {$itemName} from your cart.", [
                    'item_removed'          => true,
                    'medicine_id'           => $medicineId,
                    'cart_count'            => cart_get_count(),
                    'line_count'            => cart_get_line_count(),
                    'cart_total'            => cart_get_total(),
                    'formatted_cart_total'  => money(cart_get_total()),
                    'requires_prescription' => cart_requires_prescription(),
                ], 200);
            }

            $medicine = Medicine::findById($medicineId);
            if (!$medicine || !(int)$medicine['active'] || $medicine['expiry_date'] < date('Y-m-d')) {
                cart_remove_item($medicineId);
                api_json_response(false, 'This medicine is no longer available.', [
                    'item_removed' => true,
                    'medicine_id'  => $medicineId,
                    'cart_count'   => cart_get_count(),
                    'cart_total'   => cart_get_total(),
                ], 400);
            }

            $maxStock = (int)$medicine['quantity'];
            cart_update_item($medicineId, $quantity, $maxStock);
            $updatedCart = cart_get_items();
            $updatedItem = $updatedCart[$medicineId];

            $lineTotal = round(((float)$updatedItem['price']) * ((int)$updatedItem['quantity']), 2);

            api_json_response(true, 'Cart quantity updated.', [
                'item' => [
                    'id'                   => $medicineId,
                    'name'                 => $updatedItem['name'],
                    'quantity'             => (int)$updatedItem['quantity'],
                    'max_stock'            => $maxStock,
                    'price'                => (float)$updatedItem['price'],
                    'line_total'           => $lineTotal,
                    'formatted_line_total' => money($lineTotal),
                ],
                'cart_count'            => cart_get_count(),
                'line_count'            => cart_get_line_count(),
                'cart_total'            => cart_get_total(),
                'formatted_cart_total'  => money(cart_get_total()),
                'requires_prescription' => cart_requires_prescription(),
            ], 200);

        } catch (DomainException $e) {
            api_json_response(false, $e->getMessage(), null, 400);
        } catch (Throwable $e) {
            api_json_response(false, 'Failed to update cart: ' . $e->getMessage(), null, 500);
        }
    }

    public function cartRemove(): void
    {
        require_customer();
        $medicineId = positive_int($_POST['medicine_id'] ?? 0, 'Medicine ID');
        $itemName = (string)($_SESSION['cart'][$medicineId]['name'] ?? 'Item');
        cart_remove_item($medicineId);
        flash('success', 'Removed ' . $itemName . ' from your cart.');
        redirect('cart');
    }

    public function cartClear(): void
    {
        require_customer();
        cart_clear();
        flash('success', 'Your shopping cart has been cleared.');
        redirect('cart');
    }

    // ==========================================
    // 3. Orders & Fulfillment
    // ==========================================

    public function orders(): void
    {
        require_login();
        include dirname(__DIR__) . '/pages/orders.php';
    }

    public function orderDetail(): void
    {
        require_login();
        include dirname(__DIR__) . '/pages/order_detail.php';
    }

    public function store(): void
    {
        require_customer();
        $cart = cart_get_items();
        $prescriptionFile = $_FILES['prescription'] ?? null;

        $input = [
            'customer_id' => (int)user()['id'],
            'customer_name' => required_text('customer_name', 100),
            'customer_phone' => trim((string)($_POST['customer_phone'] ?? '')),
            'delivery_address' => required_text('delivery_address', 500),
            'payment_method' => strtoupper(trim((string)($_POST['payment_method'] ?? 'COD'))),
            'notes' => optional_text('notes', 1000),
        ];

        $result = self::createOrder($input, $cart, $prescriptionFile);

        flash('success', "Order #{$result['order_number']} placed successfully!");
        redirect('order_detail', ['id' => $result['order_id'], 'confirmed' => 1]);
    }

    public function updateStatus(): void
    {
        require_staff_or_admin();
        $orderId = positive_int($_POST['order_id'] ?? 0, 'Order ID');
        $newStatus = trim((string)($_POST['new_status'] ?? ''));
        $remarks = optional_text('remarks', 1000);
        $actorId = (int)user()['id'];

        self::updateOrderStatus($orderId, $newStatus, $remarks, $actorId);

        $order = Order::findById($orderId);
        $orderNum = $order ? $order['order_number'] : $orderId;

        flash('success', "Order #{$orderNum} status updated to {$newStatus}.");

        $referer = (string)($_SERVER['HTTP_REFERER'] ?? '');
        if (str_contains($referer, 'page=orders') && !str_contains($referer, 'page=order_detail')) {
            redirect('orders');
        }

        redirect('order_detail', ['id' => $orderId]);
    }

    public function cancelByCustomer(): void
    {
        require_customer();
        $orderId = positive_int($_POST['order_id'] ?? 0, 'Order ID');
        $remarks = optional_text('remarks', 1000);
        $customerId = (int)user()['id'];

        self::cancelOrderByCustomer($orderId, $customerId, $remarks);

        $order = Order::findById($orderId);
        $orderNum = $order ? $order['order_number'] : $orderId;

        flash('success', "Order #{$orderNum} has been cancelled.");
        redirect('order_detail', ['id' => $orderId]);
    }

    public function reviewPrescription(): void
    {
        require_staff_or_admin();
        $orderId = positive_int($_POST['order_id'] ?? 0, 'Order ID');
        $status = strtoupper(trim((string)($_POST['verification_status'] ?? '')));
        $notes = optional_text('review_notes', 1000);
        $reviewerId = (int)user()['id'];

        self::processPrescriptionReview($orderId, $status, $notes, $reviewerId);

        $decision = $status === 'APPROVED' ? 'approved' : 'rejected';
        flash('success', "Prescription has been {$decision}.");
        redirect('order_detail', ['id' => $orderId]);
    }

    public function statusApi(): void
    {
        $this->orderStatusApi();
    }

    public function orderStatusApi(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            api_json_response(false, 'Method not allowed. Expected GET request.', null, 405);
        }

        if (!user()) {
            api_json_response(false, 'Authentication required to check order status.', null, 401);
        }

        try {
            $orderId = positive_int($_GET['id'] ?? $_GET['order_id'] ?? 0, 'Order ID');
            $order = Order::findById($orderId);

            if (!$order) {
                api_json_response(false, 'Order record not found.', null, 404);
            }

            if (is_customer() && (int)$order['customer_id'] !== (int)user()['id']) {
                api_json_response(false, 'Access forbidden. You cannot view other customers\' orders.', null, 403);
            }

            $currentStatus = (string)$order['order_status'];
            $historyRows = Order::getHistory($orderId);

            $timeline = array_map(function (array $h): array {
                return [
                    'id'               => (int)$h['id'],
                    'previous_status'  => $h['previous_status'],
                    'new_status'       => (string)$h['new_status'],
                    'new_status_label' => order_status_label((string)$h['new_status']),
                    'new_status_badge' => order_status_badge((string)$h['new_status']),
                    'actor_name'       => (string)($h['changed_by_name'] ?? 'System'),
                    'actor_role'       => (string)($h['changed_by_role'] ?? ''),
                    'remarks'          => (string)($h['remarks'] ?? ''),
                    'created_at'       => (string)$h['created_at'],
                    'formatted_time'   => nice_date($h['created_at'], true),
                ];
            }, $historyRows);

            $isTerminal = in_array($currentStatus, ['COMPLETED', 'CANCELLED'], true);

            api_json_response(true, 'Order status retrieved successfully.', [
                'order_id'                  => $orderId,
                'order_number'              => (string)$order['order_number'],
                'order_status'              => $currentStatus,
                'order_status_label'        => order_status_label($currentStatus),
                'order_status_badge'        => order_status_badge($currentStatus),
                'payment_method'            => (string)$order['payment_method'],
                'payment_status'            => (string)$order['payment_status'],
                'prescription_required'     => (bool)$order['prescription_required'],
                'prescription_status'       => (string)$order['prescription_status'],
                'prescription_status_label' => prescription_status_label((string)$order['prescription_status']),
                'prescription_status_badge' => prescription_status_badge((string)$order['prescription_status']),
                'is_terminal'               => $isTerminal,
                'total_amount'              => (float)$order['total_amount'],
                'formatted_total'           => money($order['total_amount']),
                'recipient_name'            => (string)$order['customer_name'],
                'delivery_address'          => (string)$order['delivery_address'],
                'timeline'                  => $timeline,
                'last_updated'              => nice_date($order['updated_at'] ?? $order['created_at'], true),
            ], 200);

        } catch (Throwable $e) {
            api_json_response(false, 'Failed to retrieve order status: ' . $e->getMessage(), null, 500);
        }
    }

    public function downloadPrescription(): void
    {
        require_login();
        $orderId = positive_int($_GET['id'] ?? $_GET['order_id'] ?? 0, 'Order ID');
        $order = Order::findById($orderId);

        if (!$order) {
            http_response_code(404);
            exit('Order not found.');
        }

        if (is_customer() && (int)$order['customer_id'] !== (int)user()['id']) {
            http_response_code(403);
            exit('Access forbidden.');
        }

        $rx = Order::getPrescription($orderId);
        if (!$rx || empty($rx['file_path'])) {
            http_response_code(404);
            exit('No prescription file associated with this order.');
        }

        $baseDir = dirname(__DIR__);
        $cleanRelative = ltrim(str_replace(['\\', '/'], '/', (string)$rx['file_path']), '/');
        $filePath = $baseDir . '/' . $cleanRelative;

        $realPath = realpath($filePath);
        $storageDir = realpath($baseDir . '/storage/prescriptions');

        if (!$realPath || !$storageDir || !str_starts_with($realPath, $storageDir) || !is_file($realPath)) {
            http_response_code(404);
            exit('Prescription file is missing or invalid.');
        }

        // Clean all active output buffers to prevent corrupted binary image stream
        while (ob_get_level()) {
            ob_end_clean();
        }

        $mime = !empty($rx['mime_type']) ? (string)$rx['mime_type'] : '';
        if (empty($mime) || $mime === 'application/octet-stream') {
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $detected = finfo_file($finfo, $realPath);
                finfo_close($finfo);
                if ($detected) {
                    $mime = $detected;
                }
            } elseif (function_exists('mime_content_type')) {
                $detected = mime_content_type($realPath);
                if ($detected) {
                    $mime = $detected;
                }
            }
        }
        if (empty($mime)) {
            $ext = strtolower(pathinfo($realPath, PATHINFO_EXTENSION));
            $mime = match ($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'         => 'image/png',
                'pdf'         => 'application/pdf',
                default       => 'application/octet-stream',
            };
        }

        $filename = basename(!empty($rx['original_filename']) ? $rx['original_filename'] : $realPath);
        $safeFilename = str_replace(['"', "\r", "\n"], '', $filename);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: inline; filename="' . $safeFilename . '"');
        header('Content-Length: ' . (string)filesize($realPath));
        header('Cache-Control: private, max-age=3600, must-revalidate');
        header('X-Content-Type-Options: nosniff');

        readfile($realPath);
        exit;
    }

    // ==========================================
    // 4. Order Service Domain Logic
    // ==========================================

    public static function createOrder(array $input, array $cart, ?array $prescriptionFile = null): array
    {
        if (empty($cart)) {
            throw new DomainException('Your shopping cart is empty.');
        }

        $customerId = (int)($input['customer_id'] ?? 0);
        if ($customerId <= 0) {
            throw new DomainException('Valid customer authentication is required.');
        }

        $customerName = trim((string)($input['customer_name'] ?? ''));
        if ($customerName === '' || strlen($customerName) > 100) {
            throw new DomainException('Recipient Name is required (up to 100 characters).');
        }

        $customerPhone = trim((string)($input['customer_phone'] ?? ''));
        if (!preg_match('/^(?:\+?880|0)?1[3-9]\d{8}$/', $customerPhone)) {
            throw new DomainException('Please enter a valid 11-digit mobile number.');
        }

        $deliveryAddress = trim((string)($input['delivery_address'] ?? ''));
        if ($deliveryAddress === '' || strlen($deliveryAddress) > 500) {
            throw new DomainException('Shipping Address is required (up to 500 characters).');
        }

        $rawPayment = strtoupper(trim((string)($input['payment_method'] ?? 'COD')));
        $paymentMethod = in_array($rawPayment, ['COD', 'STORE_PICKUP'], true) ? $rawPayment : 'COD';
        $notes = trim((string)($input['notes'] ?? ''));

        $requiresRx = cart_requires_prescription();
        $prescriptionRecord = self::processPrescriptionUpload($prescriptionFile, $requiresRx);

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $sortedCart = $cart;
            ksort($sortedCart, SORT_NUMERIC);

            $lineItems = [];
            $subtotal = 0.0;

            foreach ($sortedCart as $medId => $cItem) {
                $qty = (int)$cItem['quantity'];
                $deducted = InventoryService::deductBatchStock($pdo, (int)$medId, $qty);
                $unitPrice = (float)$deducted['selling_price'];
                $lineTotal = round($unitPrice * $qty, 2);
                $subtotal += $lineTotal;

                $lineItems[] = [
                    'medicine_id'   => (int)$medId,
                    'medicine_name' => (string)$deducted['name'],
                    'batch_number'  => (string)$deducted['batch_number'],
                    'quantity'      => $qty,
                    'unit_price'    => $unitPrice,
                    'line_total'    => $lineTotal
                ];
            }

            $shippingFee = 0.00;
            $totalAmount = round($subtotal + $shippingFee, 2);
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(3)));
            $prescriptionStatus = $requiresRx ? 'PENDING_REVIEW' : 'NOT_REQUIRED';

            $orderId = Order::create($pdo, [
                'order_number'          => $orderNumber,
                'customer_id'           => $customerId,
                'customer_name'         => $customerName,
                'customer_phone'        => $customerPhone,
                'delivery_address'      => $deliveryAddress,
                'subtotal'              => $subtotal,
                'shipping_fee'          => $shippingFee,
                'total_amount'          => $totalAmount,
                'payment_method'        => $paymentMethod,
                'prescription_required' => $requiresRx ? 1 : 0,
                'prescription_status'   => $prescriptionStatus,
                'notes'                 => $notes !== '' ? $notes : null
            ]);

            Order::addItems($pdo, $orderId, $lineItems);

            if ($prescriptionRecord) {
                Order::addPrescription($pdo, $orderId, $customerId, $prescriptionRecord);
            }

            Order::addStatusHistory($pdo, $orderId, null, 'PENDING', $customerId, 'Order placed by customer');

            $pdo->commit();
            cart_clear();

            return [
                'order_id'     => $orderId,
                'order_number' => $orderNumber,
            ];

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    public static function processPrescriptionUpload(?array $file, bool $isRequired): ?array
    {
        $hasFile = $file !== null && isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE;

        if ($isRequired && !$hasFile) {
            throw new DomainException("Your cart contains prescription medications. A valid doctor's prescription file is required.");
        }

        if (!$hasFile) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new DomainException('Prescription upload failed. Please try again.');
        }

        $fileSize = (int)$file['size'];
        if ($fileSize <= 0 || $fileSize > 5 * 1024 * 1024) {
            throw new DomainException('Prescription file size exceeds 5MB limit.');
        }

        $origName = basename((string)$file['name']);
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
            throw new DomainException('Prescription must be an image (JPG, PNG) or PDF document.');
        }

        $tmpPath = (string)$file['tmp_name'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmpPath);
        finfo_close($finfo);

        if (!in_array($mime, ['image/jpeg', 'image/png', 'application/pdf'], true)) {
            throw new DomainException('Invalid file type. Only JPG, PNG, and PDF files are accepted.');
        }

        $storageDir = dirname(__DIR__) . '/storage/prescriptions';
        if (!is_dir($storageDir)) {
            mkdir($storageDir, 0755, true);
        }

        $randomName = bin2hex(random_bytes(16)) . '.' . $ext;
        $targetDiskPath = $storageDir . '/' . $randomName;

        if (!move_uploaded_file($tmpPath, $targetDiskPath)) {
            throw new DomainException('Failed to store the uploaded prescription. Please try again.');
        }

        return [
            'file_path'         => 'storage/prescriptions/' . $randomName,
            'original_filename' => $origName,
            'file_size'         => $fileSize,
            'mime_type'         => $mime,
        ];
    }

    public static function updateOrderStatus(int $orderId, string $newStatus, ?string $remarks = null, ?int $actorId = null): void
    {
        $validStatuses = ['PENDING', 'CONFIRMED', 'PROCESSING', 'READY', 'DISPATCHED', 'COMPLETED', 'CANCELLED'];
        if (!in_array($newStatus, $validStatuses, true)) {
            throw new DomainException('Invalid order status selected.');
        }

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new DomainException('Order not found.');
            }

            $currentStatus = (string)$order['order_status'];
            if ($currentStatus === $newStatus) {
                throw new DomainException("Order is already in '{$newStatus}' status.");
            }

            if (in_array($currentStatus, ['COMPLETED', 'CANCELLED'], true)) {
                throw new DomainException("Cannot modify terminal order (Current status: {$currentStatus}).");
            }

            $allowed = get_allowed_order_transitions($currentStatus);
            if (!in_array($newStatus, $allowed, true)) {
                throw new DomainException("Invalid status transition from {$currentStatus} to {$newStatus}.");
            }

            if ((int)$order['prescription_required'] === 1 && in_array($newStatus, ['CONFIRMED', 'PROCESSING', 'READY', 'DISPATCHED', 'COMPLETED'], true)) {
                if ($order['prescription_status'] !== 'VERIFIED') {
                    throw new DomainException("Cannot advance order: Prescription must be approved by a pharmacist first.");
                }
            }

            if ($newStatus === 'CANCELLED') {
                if ((int)$order['stock_deducted'] === 1 && (int)$order['stock_restored'] === 0) {
                    $itemsStmt = $pdo->prepare('SELECT medicine_id, quantity FROM order_items WHERE order_id = ?');
                    $itemsStmt->execute([$orderId]);
                    $orderItems = $itemsStmt->fetchAll();

                    foreach ($orderItems as $item) {
                        InventoryService::restoreBatchStock($pdo, (int)$item['medicine_id'], (int)$item['quantity']);
                    }
                    Order::markStockRestored($pdo, $orderId);
                }
            }

            if ($newStatus === 'COMPLETED') {
                Order::markPaymentStatus($pdo, $orderId, 'PAID');

                $checkSale = $pdo->prepare('SELECT id FROM sales WHERE order_id = ? FOR UPDATE');
                $checkSale->execute([$orderId]);
                $existingSale = $checkSale->fetch();

                if (!$existingSale) {
                    $orderNum = (string)$order['order_number'];
                    $invSuffix = str_starts_with($orderNum, 'ORD-') ? substr($orderNum, 4) : $orderNum;
                    $invoiceNumber = 'INV-ORD-' . $invSuffix;
                    $soldById = $actorId ?: (int)$order['customer_id'];

                    $saleStmt = $pdo->prepare(
                        'INSERT INTO sales 
                         (invoice_number, sale_date, customer_name, customer_phone, subtotal, discount, total_amount, sold_by_id, channel, order_id) 
                         VALUES (?, NOW(), ?, ?, ?, 0.00, ?, ?, "ONLINE", ?)'
                    );
                    $saleStmt->execute([
                        $invoiceNumber,
                        $order['customer_name'],
                        $order['customer_phone'],
                        $order['subtotal'],
                        $order['total_amount'],
                        $soldById,
                        $orderId
                    ]);
                    $saleId = (int)$pdo->lastInsertId();

                    $itemsStmt = $pdo->prepare('SELECT medicine_id, quantity, unit_price, line_total FROM order_items WHERE order_id = ?');
                    $itemsStmt->execute([$orderId]);
                    $orderItems = $itemsStmt->fetchAll();

                    $itemInsert = $pdo->prepare('INSERT INTO sale_items (sale_id, medicine_id, quantity, unit_price, line_total) VALUES (?, ?, ?, ?, ?)');
                    foreach ($orderItems as $oi) {
                        $itemInsert->execute([
                            $saleId,
                            (int)$oi['medicine_id'],
                            (int)$oi['quantity'],
                            $oi['unit_price'],
                            $oi['line_total']
                        ]);
                    }
                }
            }

            Order::updateStatus($pdo, $orderId, $newStatus);

            $histRemarks = $remarks ?: "Status transitioned from {$currentStatus} to {$newStatus}";
            Order::addStatusHistory($pdo, $orderId, $currentStatus, $newStatus, $actorId, $histRemarks);

            $pdo->commit();

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    public static function cancelOrderByCustomer(int $orderId, int $customerId, ?string $remarks = null): void
    {
        $pdo = db();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('SELECT * FROM orders WHERE id = ? FOR UPDATE');
            $stmt->execute([$orderId]);
            $order = $stmt->fetch();

            if (!$order) {
                throw new DomainException('Order not found.');
            }

            if ((int)$order['customer_id'] !== $customerId) {
                throw new DomainException('Access denied. You can only cancel your own orders.');
            }

            $currentStatus = (string)$order['order_status'];
            if ($currentStatus !== 'PENDING') {
                throw new DomainException("Order has already progressed to '{$currentStatus}' and cannot be self-cancelled.");
            }

            if ((int)$order['stock_deducted'] === 1 && (int)$order['stock_restored'] === 0) {
                $itemsStmt = $pdo->prepare('SELECT medicine_id, quantity FROM order_items WHERE order_id = ?');
                $itemsStmt->execute([$orderId]);
                $orderItems = $itemsStmt->fetchAll();

                foreach ($orderItems as $item) {
                    InventoryService::restoreBatchStock($pdo, (int)$item['medicine_id'], (int)$item['quantity']);
                }
                Order::markStockRestored($pdo, $orderId);
            }

            Order::updateStatus($pdo, $orderId, 'CANCELLED');
            $cancelRemarks = $remarks ?: 'Order cancelled by customer.';
            Order::addStatusHistory($pdo, $orderId, $currentStatus, 'CANCELLED', $customerId, $cancelRemarks);

            $pdo->commit();

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    /**
     * Pharmacist prescription verification (APPROVED or REJECTED).
     */
    public static function processPrescriptionReview(int $orderId, string $status, ?string $notes = null, ?int $reviewerId = null): void
    {
        $status = strtoupper(trim($status));
        if (!in_array($status, ['APPROVED', 'REJECTED'], true)) {
            throw new DomainException('Please select a valid review decision (Approved or Rejected).');
        }

        $pdo = db();
        $pdo->beginTransaction();

        try {
            $order = Order::findById($orderId);
            if (!$order) {
                throw new DomainException('Order not found.');
            }

            if (in_array($order['order_status'], ['COMPLETED', 'CANCELLED'], true)) {
                throw new DomainException('Cannot review prescription for a completed or cancelled order.');
            }

            Order::updatePrescriptionStatus($pdo, $orderId, $status, $reviewerId, $notes);

            $actionText = $status === 'APPROVED' ? 'approved' : 'rejected';
            $auditRemarks = "Prescription {$actionText} by pharmacist." . ($notes ? " Notes: {$notes}" : '');
            Order::addStatusHistory($pdo, $orderId, (string)$order['order_status'], (string)$order['order_status'], $reviewerId, $auditRemarks);

            $pdo->commit();

        } catch (Throwable $ex) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $ex;
        }
    }

    // ==========================================
    // 5. Dashboard Analytics Service
    // ==========================================

    public static function getFinancialKpis(?PDO $pdo = null): array
    {
        $pdo = $pdo ?? db();

        $row = $pdo->query("
            SELECT 
                COUNT(*) AS total_invoices_all,
                COALESCE(SUM(total_amount), 0.00) AS total_revenue,
                COUNT(CASE WHEN DATE(sale_date) = CURDATE() THEN 1 END) AS today_invoices_count,
                COALESCE(SUM(CASE WHEN DATE(sale_date) = CURDATE() THEN total_amount ELSE 0 END), 0.00) AS today_total_sales,
                COALESCE(SUM(CASE WHEN DATE(sale_date) = CURDATE() AND channel = 'POS' THEN total_amount ELSE 0 END), 0.00) AS today_pos_sales,
                COALESCE(SUM(CASE WHEN DATE(sale_date) = CURDATE() AND channel = 'ONLINE' THEN total_amount ELSE 0 END), 0.00) AS today_online_sales,
                COALESCE(SUM(CASE WHEN channel = 'POS' THEN total_amount ELSE 0 END), 0.00) AS all_pos_sales,
                COALESCE(SUM(CASE WHEN channel = 'ONLINE' THEN total_amount ELSE 0 END), 0.00) AS all_online_sales,
                COUNT(CASE WHEN channel = 'POS' THEN 1 END) AS all_pos_count,
                COUNT(CASE WHEN channel = 'ONLINE' THEN 1 END) AS all_online_count
            FROM sales
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        $totalRevenue = (float)($row['total_revenue'] ?? 0.0);
        $allPosSales = (float)($row['all_pos_sales'] ?? 0.0);
        $allOnlineSales = (float)($row['all_online_sales'] ?? 0.0);

        return [
            'today_total_sales'    => (float)($row['today_total_sales'] ?? 0.0),
            'today_pos_sales'      => (float)($row['today_pos_sales'] ?? 0.0),
            'today_online_sales'   => (float)($row['today_online_sales'] ?? 0.0),
            'today_invoices_count' => (int)($row['today_invoices_count'] ?? 0),
            'total_revenue'        => $totalRevenue,
            'all_pos_sales'        => $allPosSales,
            'all_online_sales'     => $allOnlineSales,
            'total_invoices_all'   => (int)($row['total_invoices_all'] ?? 0),
            'all_pos_count'        => (int)($row['all_pos_count'] ?? 0),
            'all_online_count'     => (int)($row['all_online_count'] ?? 0),
            'pos_percentage'       => $totalRevenue > 0.0 ? round(($allPosSales / $totalRevenue) * 100, 1) : 0.0,
            'online_percentage'    => $totalRevenue > 0.0 ? round(($allOnlineSales / $totalRevenue) * 100, 1) : 0.0,
        ];
    }

    public static function getInventoryKpis(?PDO $pdo = null): array
    {
        $pdo = $pdo ?? db();

        $row = $pdo->query("
            SELECT 
                COUNT(CASE WHEN active = 1 THEN 1 END) AS active_batches,
                COALESCE(SUM(CASE WHEN active = 1 THEN quantity ELSE 0 END), 0) AS active_stock_units,
                COUNT(CASE WHEN active = 1 AND expiry_date >= CURDATE() AND quantity <= 10 THEN 1 END) AS low_stock_count,
                COUNT(CASE WHEN expiry_date < CURDATE() THEN 1 END) AS expired_count,
                COUNT(CASE WHEN expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY) THEN 1 END) AS expiring_soon_count
            FROM medicines
        ")->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'active_batches'      => (int)($row['active_batches'] ?? 0),
            'active_stock_units'  => (int)($row['active_stock_units'] ?? 0),
            'low_stock_count'     => (int)($row['low_stock_count'] ?? 0),
            'expired_count'       => (int)($row['expired_count'] ?? 0),
            'expiring_soon_count' => (int)($row['expiring_soon_count'] ?? 0),
        ];
    }

    public static function getOperationsKpis(?PDO $pdo = null): array
    {
        $pdo = $pdo ?? db();

        $pendingOrders = (int)$pdo->query("
            SELECT COUNT(*) FROM orders WHERE order_status IN ('PENDING', 'CONFIRMED')
        ")->fetchColumn();

        $pendingPrescriptions = (int)$pdo->query("
            SELECT COUNT(*) FROM prescriptions WHERE verification_status = 'PENDING'
        ")->fetchColumn();

        return [
            'pending_orders'        => $pendingOrders,
            'pending_prescriptions' => $pendingPrescriptions,
        ];
    }

    public static function getTopSellingMedicines(int $limit = 5, ?PDO $pdo = null): array
    {
        $pdo = $pdo ?? db();
        $limit = max(1, min(50, $limit));

        $stmt = $pdo->prepare("
            SELECT 
                m.id AS medicine_id,
                m.name AS medicine_name,
                c.name AS category_name,
                COALESCE(SUM(si.quantity), 0) AS total_units_sold,
                COALESCE(SUM(si.line_total), 0.00) AS total_revenue_generated
            FROM sale_items si
            JOIN medicines m ON m.id = si.medicine_id
            JOIN categories c ON c.id = m.category_id
            GROUP BY m.id, m.name, c.name
            ORDER BY total_units_sold DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getRecentTransactions(int $limit = 10, ?PDO $pdo = null): array
    {
        $pdo = $pdo ?? db();
        $limit = max(1, min(50, $limit));

        $stmt = $pdo->prepare("
            SELECT 
                s.id,
                s.invoice_number,
                s.sale_date,
                s.customer_name,
                s.customer_phone,
                s.total_amount,
                s.channel,
                s.order_id,
                u.full_name AS cashier_name,
                COUNT(si.id) AS item_lines_count
            FROM sales s
            JOIN app_users u ON u.id = s.sold_by_id
            LEFT JOIN sale_items si ON si.sale_id = s.id
            GROUP BY s.id, s.invoice_number, s.sale_date, s.customer_name, s.customer_phone, s.total_amount, s.channel, s.order_id, u.full_name
            ORDER BY s.sale_date DESC, s.id DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getInventoryAlerts(int $limit = 8, ?PDO $pdo = null): array
    {
        $pdo = $pdo ?? db();
        $limit = max(1, min(50, $limit));

        $stmt = $pdo->prepare("
            SELECT 
                m.id,
                m.name,
                m.generic_name,
                m.batch_number,
                m.quantity,
                m.minimum_stock_level,
                m.expiry_date,
                c.name AS category_name,
                s.name AS supplier_name,
                CASE 
                    WHEN m.expiry_date < CURDATE() THEN 'EXPIRED'
                    WHEN m.quantity <= 0 THEN 'OUT_OF_STOCK'
                    WHEN m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 'EXPIRING_SOON'
                    WHEN m.quantity <= m.minimum_stock_level THEN 'LOW_STOCK'
                    ELSE 'OK'
                END AS alert_type
            FROM medicines m
            JOIN categories c ON c.id = m.category_id
            JOIN suppliers s ON s.id = m.supplier_id
            WHERE m.active = 1 AND (
                m.expiry_date < CURDATE() 
                OR m.quantity <= 0 
                OR m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) 
                OR m.quantity <= m.minimum_stock_level
            )
            ORDER BY 
                CASE 
                    WHEN m.expiry_date < CURDATE() THEN 1
                    WHEN m.quantity <= 0 THEN 2
                    WHEN m.quantity <= m.minimum_stock_level THEN 3
                    WHEN m.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY) THEN 4
                    ELSE 5
                END ASC,
                m.expiry_date ASC,
                m.quantity ASC
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function getSummary(?PDO $pdo = null): array
    {
        $pdo = $pdo ?? db();

        return [
            'financial'           => self::getFinancialKpis($pdo),
            'inventory'           => self::getInventoryKpis($pdo),
            'operations'          => self::getOperationsKpis($pdo),
            'top_selling'         => self::getTopSellingMedicines(5, $pdo),
            'recent_transactions' => self::getRecentTransactions(8, $pdo),
            'inventory_alerts'    => self::getInventoryAlerts(6, $pdo),
        ];
    }
}

namespace App;

// Global Namespace Aliases for Convenience & Full Backwards Compatibility
class_alias(\App\Controllers\Controller::class, 'Controller');
class_alias(\App\Controllers\Controller::class, 'App\Controller');
class_alias(\App\Controllers\Controller::class, 'App\Controllers\CatalogController');
class_alias(\App\Controllers\Controller::class, 'App\Controllers\CartController');
class_alias(\App\Controllers\Controller::class, 'App\Controllers\OrderController');
class_alias(\App\Controllers\Controller::class, 'App\Services\OrderService');
class_alias(\App\Controllers\Controller::class, 'App\Services\DashboardService');

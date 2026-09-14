<?php
require_once 'includes/db.php';

header('Content-Type: application/json');

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($action === 'validate_coupon') {
    $code = strtoupper(trim($_POST['code'] ?? $_GET['code'] ?? ''));
    $subtotal = (int)($_POST['subtotal'] ?? $_GET['subtotal'] ?? 0);

    if ($code === '') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Coupon code required']);
        exit;
    }

    $coupon = db_row('SELECT * FROM coupons WHERE code=? AND is_active=1', [$code]);

    if (!$coupon) {
        echo json_encode(['success' => false, 'message' => 'Invalid coupon code']);
        exit;
    }

    if (!empty($coupon['expires_at']) && $coupon['expires_at'] < date('Y-m-d')) {
        echo json_encode(['success' => false, 'message' => 'Coupon has expired']);
        exit;
    }

    if ($coupon['max_uses'] !== null && (int)$coupon['used_count'] >= (int)$coupon['max_uses']) {
        echo json_encode(['success' => false, 'message' => 'Coupon usage limit reached']);
        exit;
    }

    if ((int)$coupon['min_order'] > 0 && $subtotal < (int)$coupon['min_order']) {
        echo json_encode(['success' => false, 'message' => 'Minimum order is BDT '.number_format((int)$coupon['min_order'])]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'code' => $coupon['code'],
        'discount' => (int)$coupon['discount'],
        'type' => $coupon['type'],
    ]);
    exit;
}

if ($action === 'get_wishlist') {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $userId = current_user_id();
    $wishlistIds = array_column(db_rows('SELECT product_id FROM wishlist WHERE user_id=?', [$userId]), 'product_id');
    echo json_encode(['success' => true, 'wishlist' => $wishlistIds]);
    exit;
}

if ($action === 'toggle_wishlist') {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Not logged in']);
        exit;
    }

    $productId = $_POST['product_id'] ?? $_GET['product_id'] ?? '';
    $userId = current_user_id();

    if (!$productId) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Product ID required']);
        exit;
    }

    // Check if already in wishlist
    $existing = db_row('SELECT id FROM wishlist WHERE user_id=? AND product_id=?', [$userId, $productId]);

    if ($existing) {
        // Remove from wishlist
        db_run('DELETE FROM wishlist WHERE user_id=? AND product_id=?', [$userId, $productId]);
        echo json_encode(['success' => true, 'action' => 'removed']);
    } else {
        // Add to wishlist
        db_run('INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)', [$userId, $productId]);
        echo json_encode(['success' => true, 'action' => 'added']);
    }
    exit;
}

http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Invalid action']);
?>

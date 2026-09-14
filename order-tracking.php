<?php
require_once 'includes/db.php';
$pageTitle = 'Track Order';

$order      = null;
$orderItems = [];
$searchId   = trim($_GET['id'] ?? '');

if ($searchId) {
    $order = db_row(
        'SELECT * FROM orders WHERE order_key=? OR tracking_id=?',
        [$searchId, $searchId]
    );
    if ($order) {
        $orderItems = db_rows('SELECT * FROM order_items WHERE order_id=?', [$order['id']]);
    }
}

$statusSteps = ['confirmed'=>1,'shipped'=>2,'out_for_delivery'=>3,'delivered'=>4];
$stepDefs = [
    ['id'=>'confirmed',        'icon'=>'✓', 'label'=>'Confirmed'],
    ['id'=>'shipped',          'icon'=>'🚚','label'=>'Shipped'],
    ['id'=>'out_for_delivery', 'icon'=>'📍','label'=>'Out for Delivery'],
    ['id'=>'delivered',        'icon'=>'🎉','label'=>'Delivered'],
];
?>
<?php include 'includes/header.php'; ?><?php include 'includes/navbar.php'; ?>
<div class="page-hero"><div class="container"><h1>📦 Track Your Order</h1><p>Enter your order ID or tracking number</p></div></div>
<div class="container" style="padding:40px 0;max-width:720px;">

  <!-- Search Form -->
  <div style="background:#fff;border:1.5px solid #E2E8F0;border-radius:14px;padding:28px;margin-bottom:24px;">
    <form method="GET" style="display:flex;gap:10px;">
      <input type="text" name="id" class="form-input" placeholder="Enter Order ID (e.g. ORD-2024-123456)" value="<?php echo htmlspecialchars($searchId); ?>" style="flex:1;">
      <button type="submit" class="btn-primary" style="padding:12px 24px;">Track</button>
    </form>
    <div style="margin-top:12px;color:#94A3B8;font-size:13px;">Or check your <a href="dashboard.php" style="color:#F97316;">dashboard</a> for all orders.</div>
  </div>

  <?php if ($searchId && !$order): ?>
  <div style="text-align:center;padding:40px;background:#fff;border:1.5px solid #E2E8F0;border-radius:14px;">
    <div style="font-size:60px;">🔍</div>
    <div style="font-size:20px;font-weight:800;margin-top:12px;">Order not found</div>
    <p style="color:#64748B;margin-top:8px;">Check the order ID and try again.</p>
  </div>
  <?php endif; ?>

  <?php if ($order): ?>
  <?php
  $level  = $statusSteps[$order['status']] ?? 1;
  $statusClass = ['confirmed'=>'status-confirmed','out_for_delivery'=>'status-shipped','delivered'=>'status-delivered','cancelled'=>'status-cancelled'][$order['status']] ?? 'status-confirmed';
  ?>
  <div style="background:#fff;border:1.5px solid #E2E8F0;border-radius:14px;padding:28px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;margin-bottom:28px;">
      <div>
        <div style="font-size:13px;color:#64748B;">Order ID</div>
        <div style="font-size:22px;font-weight:800;color:#111827;"><?php echo htmlspecialchars($order['order_key']); ?></div>
        <div style="color:#64748B;font-size:13px;margin-top:4px;">Placed on <?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></div>
      </div>
      <span class="order-status <?php echo $statusClass; ?>" style="font-size:14px;padding:6px 18px;"><?php echo ucfirst(str_replace('_',' ',$order['status'])); ?></span>
    </div>

    <!-- Tracking Steps -->
    <?php if ($order['status'] !== 'cancelled'): ?>
    <div class="tracking-steps">
      <?php foreach ($stepDefs as $i => $step):
        $stepLevel = $i + 1;
        $cls = $stepLevel < $level ? 'done' : ($stepLevel === $level ? 'active' : '');
      ?>
      <div class="tracking-step <?php echo $cls; ?>">
        <div class="tracking-step-circle"><?php echo $step['icon']; ?></div>
        <div class="tracking-step-label"><?php echo $step['label']; ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Info -->
    <div style="background:#F8FAFC;border-radius:10px;padding:16px;margin-top:20px;display:grid;grid-template-columns:1fr 1fr;gap:16px;">
      <div><div style="font-size:12px;color:#64748B;margin-bottom:4px;">TRACKING ID</div><div style="font-weight:700;"><?php echo htmlspecialchars($order['tracking_id'] ?? '—'); ?></div></div>
      <div><div style="font-size:12px;color:#64748B;margin-bottom:4px;">PAYMENT</div><div style="font-weight:700;"><?php echo $order['payment_method'] === 'cod' ? '💵 Cash on Delivery' : '💳 Card'; ?></div></div>
      <div style="grid-column:span 2;"><div style="font-size:12px;color:#64748B;margin-bottom:4px;">DELIVERY ADDRESS</div><div style="font-weight:600;color:#374151;"><?php echo htmlspecialchars($order['address']); ?></div></div>
    </div>

    <!-- Items -->
    <div style="margin-top:24px;">
      <div style="font-weight:700;font-size:15px;margin-bottom:14px;">Order Items (<?php echo count($orderItems); ?>)</div>
      <?php foreach ($orderItems as $item): ?>
      <div class="checkout-item">
        <img src="<?php echo htmlspecialchars($item['product_image']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>">
        <div style="flex:1;">
          <div style="font-weight:600;font-size:14px;"><?php echo htmlspecialchars($item['product_name']); ?></div>
          <div style="color:#64748B;font-size:13px;"><?php echo htmlspecialchars($item['product_brand']); ?> · Qty: <?php echo $item['quantity']; ?></div>
        </div>
        <div style="font-weight:700;color:#111827;">BDT <?php echo number_format($item['price'] * $item['quantity']); ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Total -->
    <div style="border-top:2px solid #E2E8F0;margin-top:16px;padding-top:16px;display:flex;justify-content:space-between;align-items:center;">
      <span style="font-weight:700;font-size:16px;">Total Paid</span>
      <span style="color:#F97316;font-weight:900;font-size:22px;">BDT <?php echo number_format($order['total']); ?></span>
    </div>
  </div>
  <?php endif; ?>

  <!-- My Orders List (if logged in) -->
  <?php if (is_logged_in() && !$searchId):
    $myOrders = db_rows('SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC LIMIT 10', [current_user_id()]);
    if (!empty($myOrders)): ?>
  <div style="margin-top:28px;">
    <h3 style="font-weight:800;margin-bottom:16px;">My Recent Orders</h3>
    <?php foreach ($myOrders as $o):
      $orderItems = db_rows('SELECT * FROM order_items WHERE order_id=?', [$o['id']]);
      include 'includes/order_card.php';
    endforeach; ?>
  </div>
  <?php endif; endif; ?>
</div>
<?php include 'includes/footer.php'; ?>

<?php
require_once 'includes/db.php';
require_once 'includes/products_data.php';

if (!is_logged_in()) {
    header('Location: auth.php');
    exit;
}

$user    = session_user();
$userId  = current_user_id();
$orders  = db_rows('SELECT o.*, COUNT(oi.id) AS item_count FROM orders o LEFT JOIN order_items oi ON o.id=oi.order_id WHERE o.user_id=? GROUP BY o.id ORDER BY o.created_at DESC', [$userId]);
$wlIds   = array_column(db_rows('SELECT product_id FROM wishlist WHERE user_id=?', [$userId]), 'product_id');
$wlProducts = db_rows('SELECT * FROM products WHERE id IN (SELECT product_id FROM wishlist WHERE user_id=?) ORDER BY (SELECT created_at FROM wishlist WHERE product_id=products.id AND user_id=?) DESC', [$userId, $userId]);
$totalSpent = array_sum(array_column($orders, 'total'));

$pageTitle = 'My Dashboard';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    $name  = trim($_POST['name']  ?? $user['name']);
    $phone = trim($_POST['phone'] ?? $user['phone']);
    db_run('UPDATE users SET name=?, phone=? WHERE id=?', [$name, $phone, $userId]);
    $user['name'] = $name; $user['phone'] = $phone;
    session_login(array_merge($user, ['name'=>$name,'phone'=>$phone]));
    $profileSuccess = 'Profile updated successfully!';
}

// Handle wishlist removal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'remove_from_wishlist') {
    $productId = $_POST['product_id'] ?? '';
    if ($productId) {
        db_run('DELETE FROM wishlist WHERE user_id=? AND product_id=?', [$userId, $productId]);
        header('Location: dashboard.php?tab=wishlist');
        exit;
    }
}
?>
<?php include 'includes/header.php'; ?><?php include 'includes/navbar.php'; ?>
<div class="page-hero"><div class="container"><h1>My Dashboard</h1><p>Welcome back, <?php echo htmlspecialchars($user['name']); ?>!</p></div></div>
<div class="container">
  <div class="dashboard-layout">
    <!-- Sidebar -->
    <div class="dashboard-sidebar">
      <div class="dash-nav">
        <div class="dash-user">
          <img class="dash-avatar" src="<?php echo htmlspecialchars($user['avatar'] ?: 'https://i.pravatar.cc/80?u='.$user['email']); ?>" alt="Avatar">
          <div class="dash-username"><?php echo htmlspecialchars($user['name']); ?></div>
          <div class="dash-email"><?php echo htmlspecialchars($user['email']); ?></div>
        </div>
        <div class="dash-nav-item active" onclick="showDashSection('overview',this)">📊 Overview</div>
        <div class="dash-nav-item" onclick="showDashSection('orders',this)">📦 My Orders</div>
        <div class="dash-nav-item" onclick="showDashSection('wishlist',this)">❤️ Wishlist (<?php echo count($wlIds); ?>)</div>
        <div class="dash-nav-item" onclick="showDashSection('profile',this)">👤 Profile</div>
        <div class="dash-nav-item" style="margin-top:16px;border-top:1px solid #E2E8F0;padding-top:16px;">
          <a href="logout.php" style="color:#EF4444;display:flex;align-items:center;gap:8px;">🚪 Logout</a>
        </div>
      </div>
    </div>
    <!-- Content -->
    <div class="dash-content">
      <!-- Overview -->
      <div class="dash-section active" id="dash-overview">
        <div class="stats-grid">
          <div class="stat-card"><div class="stat-icon">📦</div><div class="stat-value"><?php echo count($orders); ?></div><div class="stat-label">Total Orders</div></div>
          <div class="stat-card"><div class="stat-icon">❤️</div><div class="stat-value"><?php echo count($wlIds); ?></div><div class="stat-label">Wishlist Items</div></div>
          <div class="stat-card"><div class="stat-icon">💰</div><div class="stat-value" style="font-size:18px;">BDT <?php echo number_format($totalSpent); ?></div><div class="stat-label">Total Spent</div></div>
          <div class="stat-card"><div class="stat-icon">🎯</div><div class="stat-value"><?php echo count(array_filter($orders, fn($o) => $o['status'] === 'delivered')); ?></div><div class="stat-label">Delivered</div></div>
        </div>
        <div style="background:#fff;border:1.5px solid #E2E8F0;border-radius:14px;padding:24px;">
          <h3 style="font-weight:800;margin-bottom:16px;">Recent Orders</h3>
          <?php if (empty($orders)): ?>
          <p style="color:#64748B;">No orders yet. <a href="products.php" style="color:#F97316;">Start shopping!</a></p>
          <?php else: foreach (array_slice($orders, 0, 3) as $o): ?>
          <?php include 'includes/order_card.php'; ?>
          <?php endforeach; endif; ?>
        </div>
      </div>

      <!-- Orders -->
      <div class="dash-section" id="dash-orders">
        <h2 style="font-size:22px;font-weight:800;margin-bottom:20px;">📦 My Orders</h2>
        <?php if (empty($orders)): ?>
        <div style="text-align:center;padding:40px;color:#64748B;"><div style="font-size:60px;">📦</div><div style="font-size:18px;font-weight:700;margin-top:12px;">No orders yet</div><a href="products.php" class="btn-primary" style="margin-top:20px;display:inline-flex;">Browse Products</a></div>
        <?php else: foreach ($orders as $o): ?>
        <?php include 'includes/order_card.php'; ?>
        <?php endforeach; endif; ?>
      </div>

      <!-- Wishlist -->
      <div class="dash-section" id="dash-wishlist">
        <h2 style="font-size:22px;font-weight:800;margin-bottom:20px;">❤️ My Wishlist</h2>
        <?php if (empty($wlProducts)): ?>
        <div style="text-align:center;padding:60px 0;"><div style="font-size:60px;">🤍</div><div style="font-size:18px;font-weight:700;margin-top:12px;">Your wishlist is empty</div><a href="products.php" class="btn-primary" style="margin-top:20px;display:inline-flex;">Browse Products</a></div>
        <?php else: ?>
        <div class="wishlist-table">
          <table style="width:100%;border-collapse:collapse;">
            <thead>
              <tr style="border-bottom:2px solid #E2E8F0;font-weight:700;text-align:left;">
                <th style="padding:16px;">Product</th>
                <th style="padding:16px;">Brand</th>
                <th style="padding:16px;">Price</th>
                <th style="padding:16px;">Stock</th>
                <th style="padding:16px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($wlProducts as $wp): ?>
              <tr style="border-bottom:1px solid #E2E8F0;align-items:center;">
                <td style="padding:16px;display:flex;align-items:center;gap:12px;">
                  <img src="<?php echo htmlspecialchars($wp['image']); ?>" alt="<?php echo htmlspecialchars($wp['name']); ?>" style="width:60px;height:60px;object-fit:cover;border-radius:8px;">
                  <div>
                    <div style="font-weight:600;color:#111827;"><?php echo htmlspecialchars($wp['name']); ?></div>
                    <div style="font-size:12px;color:#94A3B8;">ID: <?php echo htmlspecialchars($wp['id']); ?></div>
                  </div>
                </td>
                <td style="padding:16px;"><?php echo htmlspecialchars($wp['brand']); ?></td>
                <td style="padding:16px;font-weight:700;color:#F97316;">BDT <?php echo number_format($wp['price']); ?></td>
                <td style="padding:16px;"><?php echo $wp['stock'] > 0 ? '<span style="color:#10B981;font-weight:600;">'.$wp['stock'].' in stock</span>' : '<span style="color:#EF4444;font-weight:600;">Out of Stock</span>'; ?></td>
                <td style="padding:16px;">
                  <a href="product.php?id=<?php echo htmlspecialchars($wp['id']); ?>" class="btn-primary" style="padding:8px 16px;font-size:12px;display:inline-flex;text-decoration:none;">View Details</a>
                  <?php if ($wp['stock'] > 0): ?>
                  <button onclick="addToCart('<?php echo $wp['id']; ?>','<?php echo addslashes($wp['name']); ?>',<?php echo $wp['price']; ?>,'<?php echo $wp['image']; ?>','<?php echo addslashes($wp['brand']); ?>')" class="btn-secondary" style="padding:8px 16px;font-size:12px;margin-left:8px;display:inline-flex;">Add to Cart</button>
                  <?php endif; ?>
                  <button onclick="removeFromWishlist('<?php echo htmlspecialchars($wp['id']); ?>')" class="btn-danger" style="padding:8px 16px;font-size:12px;margin-left:8px;">Remove</button>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Profile -->
      <div class="dash-section" id="dash-profile">
        <h2 style="font-size:22px;font-weight:800;margin-bottom:20px;">👤 Edit Profile</h2>
        <?php if (!empty($profileSuccess)): ?>
        <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:10px;padding:12px 16px;color:#16A34A;font-size:14px;margin-bottom:16px;">✓ <?php echo $profileSuccess; ?></div>
        <?php endif; ?>
        <div style="background:#fff;border:1.5px solid #E2E8F0;border-radius:14px;padding:24px;max-width:500px;">
          <form method="POST">
            <input type="hidden" name="action" value="update_profile">
            <div class="form-group"><label class="form-label">Full Name</label><input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($user['name']); ?>"></div>
            <div class="form-group"><label class="form-label">Email <span style="color:#94A3B8;font-size:12px;">(cannot change)</span></label><input type="email" class="form-input" value="<?php echo htmlspecialchars($user['email']); ?>" disabled style="opacity:0.6;"></div>
            <div class="form-group"><label class="form-label">Phone</label><input type="tel" name="phone" class="form-input" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>" placeholder="+92 300 1234567"></div>
            <button type="submit" class="btn-primary">Save Changes</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include 'includes/footer.php'; ?>
<script>
function showDashSection(id,el){document.querySelectorAll('.dash-section').forEach(s=>s.classList.remove('active'));document.querySelectorAll('.dash-nav-item').forEach(n=>n.classList.remove('active'));document.getElementById('dash-'+id).classList.add('active');if(el)el.classList.add('active');}
</script>

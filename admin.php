<?php
require_once 'includes/db.php';
require_once 'includes/products_data.php';

// Protect admin
if (!is_logged_in() || !is_admin()) {
    header('Location: auth.php');
    exit;
}

// Handle product add/edit/delete via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add_product') {
        $id = 'p' . time();
        db_run('INSERT INTO products (id,name,brand,category,price,original_price,discount,image,rating,stock,description,is_cod,is_trending,is_best_seller,is_flash_sale,delivery_days) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [
            $id, $_POST['name'], $_POST['brand'], $_POST['category'],
            (int)$_POST['price'], (int)($_POST['original_price'] ?: $_POST['price']),
            (int)($_POST['discount'] ?? 0), $_POST['image'] ?: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&auto=format',
            (float)($_POST['rating'] ?? 4.5), (int)($_POST['stock'] ?? 0),
            $_POST['description'], isset($_POST['is_cod'])?1:0,
            isset($_POST['is_trending'])?1:0, isset($_POST['is_bestseller'])?1:0,
            isset($_POST['is_flashsale'])?1:0, (int)($_POST['delivery_days'] ?? 3)
        ]);
        $addSuccess = 'Product "'.$_POST['name'].'" added successfully!';
    }
    if ($action === 'edit_product') {
        db_run('UPDATE products SET name=?,brand=?,category=?,price=?,original_price=?,discount=?,image=?,stock=?,description=?,is_cod=?,is_trending=?,is_best_seller=?,is_flash_sale=?,delivery_days=? WHERE id=?', [
            $_POST['name'], $_POST['brand'], $_POST['category'],
            (int)$_POST['price'], (int)($_POST['original_price'] ?: $_POST['price']),
            (int)($_POST['discount'] ?? 0), $_POST['image'] ?: 'https://images.unsplash.com/photo-1496181133206-80ce9b88a853?w=400&auto=format',
            (int)($_POST['stock'] ?? 0), $_POST['description'],
            isset($_POST['is_cod'])?1:0, isset($_POST['is_trending'])?1:0,
            isset($_POST['is_bestseller'])?1:0, isset($_POST['is_flashsale'])?1:0,
            (int)($_POST['delivery_days'] ?? 3), $_POST['product_id']
        ]);
        $editSuccess = 'Product "'.$_POST['name'].'" updated successfully!';
    }
    if ($action === 'update_status') {
        db_run('UPDATE orders SET status=? WHERE id=?', [$_POST['status'], (int)$_POST['order_id']]);
        $statusSuccess = 'Order status updated!';
    }
    if ($action === 'delete_product') {
        db_run('DELETE FROM products WHERE id=?', [$_POST['product_id']]);
        $deleteSuccess = 'Product deleted.';
    }
}

// Fetch data
$dbProducts  = db_rows('SELECT * FROM products ORDER BY created_at DESC');
$allOrders   = db_rows('SELECT o.*, u.name AS customer_name, u.email AS customer_email, COUNT(oi.id) AS item_count FROM orders o LEFT JOIN users u ON o.user_id=u.id LEFT JOIN order_items oi ON o.id=oi.order_id GROUP BY o.id ORDER BY o.created_at DESC');
$totalRevenue = array_sum(array_column($allOrders, 'total'));
$totalUsers  = db_row('SELECT COUNT(*) AS c FROM users')['c'];

$pageTitle = 'Admin Panel';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Admin — DETECH</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
  <div class="admin-sidebar">
    <div class="admin-logo">⚡ DETECH Admin</div>
    <nav style="margin-top:20px;">
      <div class="admin-nav-item active" onclick="showAdminSection('dashboard',this)">📊 Dashboard</div>
      <div class="admin-nav-item" onclick="showAdminSection('products',this)">📦 Products (<?php echo count($dbProducts); ?>)</div>
      <div class="admin-nav-item" onclick="showAdminSection('orders',this)">🛒 Orders (<?php echo count($allOrders); ?>)</div>
      <div class="admin-nav-item" onclick="showAdminSection('addproduct',this)">➕ Add Product</div>
      <a href="category.php" class="admin-nav-item" style="text-decoration:none;">Categories</a>
      <a href="coupons.php" class="admin-nav-item" style="text-decoration:none;">Coupons</a>
      <div style="margin-top:auto;padding:20px;border-top:1px solid rgba(255,255,255,0.1);">
        <a href="index.php" style="color:#94A3B8;font-size:13px;">← Back to Store</a><br>
        <a href="logout.php" style="color:#EF4444;font-size:13px;margin-top:8px;display:block;">🚪 Logout</a>
      </div>
    </nav>
  </div>
  <div class="admin-main">

    <!-- Dashboard -->
    <div class="admin-section active" id="admin-dashboard">
      <div class="admin-header">📊 Dashboard Overview</div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
        <div style="background:#fff;border-radius:12px;padding:20px;border:1.5px solid #E2E8F0;"><div style="font-size:26px;margin-bottom:8px;">📦</div><div style="font-size:28px;font-weight:900;"><?php echo count($dbProducts); ?></div><div style="color:#64748B;font-size:13px;margin-top:4px;">Products</div></div>
        <div style="background:#fff;border-radius:12px;padding:20px;border:1.5px solid #E2E8F0;"><div style="font-size:26px;margin-bottom:8px;">🛒</div><div style="font-size:28px;font-weight:900;"><?php echo count($allOrders); ?></div><div style="color:#64748B;font-size:13px;margin-top:4px;">Orders</div></div>
        <div style="background:#fff;border-radius:12px;padding:20px;border:1.5px solid #E2E8F0;"><div style="font-size:26px;margin-bottom:8px;">💰</div><div style="font-size:16px;font-weight:900;margin-top:4px;">BDT <?php echo number_format($totalRevenue); ?></div><div style="color:#64748B;font-size:13px;margin-top:4px;">Revenue</div></div>
        <div style="background:#fff;border-radius:12px;padding:20px;border:1.5px solid #E2E8F0;"><div style="font-size:26px;margin-bottom:8px;">👥</div><div style="font-size:28px;font-weight:900;"><?php echo $totalUsers; ?></div><div style="color:#64748B;font-size:13px;margin-top:4px;">Users</div></div>
      </div>
      <div style="background:#fff;border-radius:12px;border:1.5px solid #E2E8F0;padding:24px;">
        <h3 style="font-weight:800;margin-bottom:16px;">Recent Orders</h3>
        <div style="overflow-x:auto;">
          <table class="data-table">
            <thead><tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Payment</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach (array_slice($allOrders, 0, 5) as $o): ?>
            <tr>
              <td style="font-weight:700;"><?php echo htmlspecialchars($o['order_key']); ?></td>
              <td><?php echo htmlspecialchars($o['customer_name'] ?? 'Guest'); ?></td>
              <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
              <td style="color:#F97316;font-weight:700;">BDT <?php echo number_format($o['total']); ?></td>
              <td><?php echo $o['payment_method'] === 'cod' ? '💵 COD' : '💳 Card'; ?></td>
              <td><span class="order-status status-<?php echo $o['status']; ?>"><?php echo ucfirst(str_replace('_',' ',$o['status'])); ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <!-- Products -->
    <div class="admin-section" id="admin-products">
      <?php if (!empty($addSuccess)): ?><div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:10px;padding:12px 16px;color:#16A34A;margin-bottom:16px;">✓ <?php echo $addSuccess; ?></div><?php endif; ?>
      <?php if (!empty($editSuccess)): ?><div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:10px;padding:12px 16px;color:#16A34A;margin-bottom:16px;">✓ <?php echo $editSuccess; ?></div><?php endif; ?>
      <?php if (!empty($deleteSuccess)): ?><div style="background:#FEF3C7;border:1.5px solid #FDE68A;border-radius:10px;padding:12px 16px;color:#D97706;margin-bottom:16px;">✓ <?php echo $deleteSuccess; ?></div><?php endif; ?>
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <div class="admin-header" style="margin-bottom:0;">📦 Products (<?php echo count($dbProducts); ?>)</div>
        <button onclick="showAdminSection('addproduct',null)" class="btn-primary" style="padding:10px 20px;font-size:13px;">+ Add Product</button>
      </div>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Image</th><th>Name</th><th>Brand</th><th>Category</th><th>Price</th><th>Stock</th><th>Badges</th><th>Action</th></tr></thead>
          <tbody>
          <?php foreach ($dbProducts as $prod): ?>
          <tr>
            <td><img src="<?php echo htmlspecialchars($prod['image']); ?>" alt=""></td>
            <td style="font-weight:600;max-width:180px;"><?php echo htmlspecialchars($prod['name']); ?></td>
            <td><?php echo htmlspecialchars($prod['brand']); ?></td>
            <td><?php echo ucfirst($prod['category']); ?></td>
            <td style="color:#F97316;font-weight:700;">BDT <?php echo number_format($prod['price']); ?></td>
            <td><?php echo $prod['stock']; ?></td>
            <td>
              <?php if ($prod['is_flash_sale']): ?><span class="badge-flash">⚡</span><?php endif; ?>
              <?php if ($prod['is_best_seller']): ?><span class="badge-new">🏆</span><?php endif; ?>
              <?php if ($prod['is_trending']): ?><span class="badge-cod">📈</span><?php endif; ?>
              <?php if ($prod['is_cod']): ?><span class="badge-cod">COD</span><?php endif; ?>
            </td>
            <td>
              <button class="btn-success" onclick="editProduct('<?php echo htmlspecialchars(json_encode($prod)); ?>')" style="padding:5px 12px;font-size:12px;margin-right:4px;">Edit</button>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this product?');">
                <input type="hidden" name="action" value="delete_product">
                <input type="hidden" name="product_id" value="<?php echo $prod['id']; ?>">
                <button type="submit" class="btn-danger" style="padding:5px 12px;font-size:12px;">Delete</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Orders -->
    <div class="admin-section" id="admin-orders">
      <?php if (!empty($statusSuccess)): ?><div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:10px;padding:12px 16px;color:#16A34A;margin-bottom:16px;">✓ <?php echo $statusSuccess; ?></div><?php endif; ?>
      <div class="admin-header">🛒 All Orders</div>
      <?php if (empty($allOrders)): ?>
      <div style="text-align:center;padding:40px;color:#64748B;">No orders yet.</div>
      <?php else: ?>
      <div style="overflow-x:auto;">
        <table class="data-table">
          <thead><tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th>Update</th></tr></thead>
          <tbody>
          <?php foreach ($allOrders as $o): ?>
          <tr>
            <td style="font-weight:700;"><?php echo htmlspecialchars($o['order_key']); ?></td>
            <td><?php echo htmlspecialchars($o['customer_name'] ?? 'Guest'); ?></td>
            <td><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
            <td><?php echo $o['item_count']; ?></td>
            <td style="color:#F97316;font-weight:700;">BDT <?php echo number_format($o['total']); ?></td>
            <td><?php echo $o['payment_method'] === 'cod' ? '💵 COD' : '💳 Card'; ?></td>
            <td><span class="order-status status-<?php echo $o['status']; ?>"><?php echo ucfirst(str_replace('_',' ',$o['status'])); ?></span></td>
            <td>
              <form method="POST" style="display:flex;gap:6px;">
                <input type="hidden" name="action" value="update_status">
                <input type="hidden" name="order_id" value="<?php echo $o['id']; ?>">
                <select name="status" class="sort-select" style="font-size:12px;padding:5px 8px;">
                  <?php foreach(['confirmed','out_for_delivery','delivered','cancelled'] as $s): ?>
                  <option value="<?php echo $s; ?>" <?php echo $o['status']===$s?'selected':''; ?>><?php echo ucfirst(str_replace('_',' ',$s)); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-primary" style="padding:5px 12px;font-size:12px;">Save</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>

    <!-- Add Product -->
    <div class="admin-section" id="admin-addproduct">
      <div class="admin-header">➕ Add New Product</div>
      <div style="background:#fff;border-radius:12px;border:1.5px solid #E2E8F0;padding:24px;max-width:700px;">
        <form method="POST">
          <input type="hidden" name="action" value="add_product">
          <div class="admin-form-grid">
            <div class="form-group"><label class="form-label">Product Name *</label><input type="text" name="name" class="form-input" placeholder="e.g. Samsung Galaxy S25" required></div>
            <div class="form-group"><label class="form-label">Brand *</label><select name="brand" class="form-input" required><?php foreach($BRANDS as $b): ?><option><?php echo $b;?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Category *</label><select name="category" class="form-input" required><?php foreach($CATEGORIES as $cat): ?><option value="<?php echo $cat['id'];?>"><?php echo $cat['name'];?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Price (BDT) *</label><input type="number" name="price" class="form-input" placeholder="e.g. 89999" required></div>
            <div class="form-group"><label class="form-label">Original Price</label><input type="number" name="original_price" class="form-input" placeholder="e.g. 99999"></div>
            <div class="form-group"><label class="form-label">Discount %</label><input type="number" name="discount" class="form-input" placeholder="e.g. 10"></div>
            <div class="form-group"><label class="form-label">Stock *</label><input type="number" name="stock" class="form-input" placeholder="e.g. 50" required></div>
            <div class="form-group"><label class="form-label">Delivery Days</label><input type="number" name="delivery_days" class="form-input" value="3"></div>
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Image URL</label><input type="url" name="image" class="form-input" placeholder="https://images.unsplash.com/..."></div>
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Description *</label><textarea name="description" class="form-input" rows="3" placeholder="Product description..." required></textarea></div>
          </div>
          <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;"><input type="checkbox" name="is_cod" style="accent-color:#F97316;width:16px;height:16px;"> COD Available</label>
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;"><input type="checkbox" name="is_trending" style="accent-color:#F97316;width:16px;height:16px;"> Trending</label>
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;"><input type="checkbox" name="is_bestseller" style="accent-color:#F97316;width:16px;height:16px;"> Best Seller</label>
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;"><input type="checkbox" name="is_flashsale" style="accent-color:#F97316;width:16px;height:16px;"> Flash Sale</label>
          </div>
          <button type="submit" class="btn-primary" style="margin-top:20px;">Add Product</button>
        </form>
      </div>
    </div>

    <!-- Edit Product -->
    <div class="admin-section" id="admin-editproduct">
      <div class="admin-header">✏️ Edit Product</div>
      <div style="background:#fff;border-radius:12px;border:1.5px solid #E2E8F0;padding:24px;max-width:700px;">
        <form method="POST" id="edit-product-form">
          <input type="hidden" name="action" value="edit_product">
          <input type="hidden" name="product_id" id="edit-product-id" value="">
          <div class="admin-form-grid">
            <div class="form-group"><label class="form-label">Product Name *</label><input type="text" name="name" id="edit-name" class="form-input" placeholder="e.g. Samsung Galaxy S25" required></div>
            <div class="form-group"><label class="form-label">Brand *</label><select name="brand" id="edit-brand" class="form-input" required><?php foreach($BRANDS as $b): ?><option><?php echo $b;?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Category *</label><select name="category" id="edit-category" class="form-input" required><?php foreach($CATEGORIES as $cat): ?><option value="<?php echo $cat['id'];?>"><?php echo $cat['name'];?></option><?php endforeach; ?></select></div>
            <div class="form-group"><label class="form-label">Price (BDT) *</label><input type="number" name="price" id="edit-price" class="form-input" placeholder="e.g. 89999" required></div>
            <div class="form-group"><label class="form-label">Original Price</label><input type="number" name="original_price" id="edit-original_price" class="form-input" placeholder="e.g. 99999"></div>
            <div class="form-group"><label class="form-label">Discount %</label><input type="number" name="discount" id="edit-discount" class="form-input" placeholder="e.g. 10"></div>
            <div class="form-group"><label class="form-label">Stock *</label><input type="number" name="stock" id="edit-stock" class="form-input" placeholder="e.g. 50" required></div>
            <div class="form-group"><label class="form-label">Delivery Days</label><input type="number" name="delivery_days" id="edit-delivery_days" class="form-input" value="3"></div>
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Image URL</label><input type="url" name="image" id="edit-image" class="form-input" placeholder="https://images.unsplash.com/..."></div>
            <div class="form-group" style="grid-column:span 2;"><label class="form-label">Description *</label><textarea name="description" id="edit-description" class="form-input" rows="3" placeholder="Product description..." required></textarea></div>
          </div>
          <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:8px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;"><input type="checkbox" name="is_cod" id="edit-is_cod" style="accent-color:#F97316;width:16px;height:16px;"> COD Available</label>
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;"><input type="checkbox" name="is_trending" id="edit-is_trending" style="accent-color:#F97316;width:16px;height:16px;"> Trending</label>
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;"><input type="checkbox" name="is_bestseller" id="edit-is_bestseller" style="accent-color:#F97316;width:16px;height:16px;"> Best Seller</label>
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;"><input type="checkbox" name="is_flashsale" id="edit-is_flashsale" style="accent-color:#F97316;width:16px;height:16px;"> Flash Sale</label>
          </div>
          <div style="display:flex;gap:10px;margin-top:20px;">
            <button type="submit" class="btn-primary">Update Product</button>
            <button type="button" class="btn-secondary" onclick="showAdminSection('products',document.querySelector('[onclick=\"showAdminSection(\\\'products\\\',this)\"]'))">Cancel</button>
          </div>
        </form>
      </div>
    </div>

  </div>
</div>
<div id="toast" class="toast" style="display:none;"></div>
<script src="js/app.js"></script>
<script>
function showAdminSection(id,el){document.querySelectorAll('.admin-section').forEach(s=>s.classList.remove('active'));document.querySelectorAll('.admin-nav-item').forEach(n=>n.classList.remove('active'));document.getElementById('admin-'+id).classList.add('active');if(el)el.classList.add('active');}
function editProduct(productJson){let product=JSON.parse(productJson);document.getElementById('edit-product-id').value=product.id;document.getElementById('edit-name').value=product.name;document.getElementById('edit-brand').value=product.brand;document.getElementById('edit-category').value=product.category;document.getElementById('edit-price').value=product.price;document.getElementById('edit-original_price').value=product.original_price;document.getElementById('edit-discount').value=product.discount;document.getElementById('edit-stock').value=product.stock;document.getElementById('edit-delivery_days').value=product.delivery_days;document.getElementById('edit-image').value=product.image;document.getElementById('edit-description').value=product.description;document.getElementById('edit-is_cod').checked=product.is_cod==1;document.getElementById('edit-is_trending').checked=product.is_trending==1;document.getElementById('edit-is_bestseller').checked=product.is_best_seller==1;document.getElementById('edit-is_flashsale').checked=product.is_flash_sale==1;showAdminSection('editproduct',null);}
</script>
</body></html>

<?php
require_once 'includes/db.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: auth.php');
    exit;
}

db_run('CREATE TABLE IF NOT EXISTS categories (
    id VARCHAR(40) PRIMARY KEY,
    name VARCHAR(80) NOT NULL,
    icon VARCHAR(10) NOT NULL,
    sort_order TINYINT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

require_once 'includes/products_data.php';

$categoryTotal = db_row('SELECT COUNT(*) AS c FROM categories');
if ((int)($categoryTotal['c'] ?? 0) === 0 && !empty($CATEGORIES)) {
    foreach (array_values($CATEGORIES) as $index => $category) {
        db_run('INSERT INTO categories (id, name, icon, sort_order) VALUES (?, ?, ?, ?)', [
            $category['id'],
            $category['name'],
            $category['icon'] ?: '#',
            $index + 1,
        ]);
    }
}

function category_slug(string $value): string {
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
    return trim($slug, '-');
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_category') {
        $name = trim($_POST['name'] ?? '');
        $categoryIdInput = trim($_POST['category_id'] ?? '');
        $id = category_slug($categoryIdInput !== '' ? $categoryIdInput : $name);
        $icon = trim($_POST['icon'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if ($name === '') {
            $error = 'Category name is required.';
        } elseif ($id === '') {
            $error = 'Category ID must contain at least one letter or number.';
        } elseif (db_row('SELECT id FROM categories WHERE id=?', [$id])) {
            $error = 'That category ID already exists.';
        } else {
            if ($icon === '') {
                $icon = '#';
            }
            if ($sortOrder <= 0) {
                $maxSort = db_row('SELECT COALESCE(MAX(sort_order), 0) AS max_sort FROM categories');
                $sortOrder = ((int)($maxSort['max_sort'] ?? 0)) + 1;
            }

            db_run('INSERT INTO categories (id, name, icon, sort_order) VALUES (?, ?, ?, ?)', [
                $id,
                $name,
                $icon,
                $sortOrder,
            ]);
            $success = 'Category "'.$name.'" added successfully.';
        }
    }

    if ($action === 'edit_category') {
        $originalId = $_POST['original_category_id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $categoryIdInput = trim($_POST['category_id'] ?? '');
        $id = category_slug($categoryIdInput !== '' ? $categoryIdInput : $name);
        $icon = trim($_POST['icon'] ?? '');
        $sortOrder = max(0, (int)($_POST['sort_order'] ?? 0));
        $category = db_row('SELECT * FROM categories WHERE id=?', [$originalId]);

        if (!$category) {
            $error = 'Category not found.';
        } elseif ($name === '') {
            $error = 'Category name is required.';
        } elseif ($id === '') {
            $error = 'Category ID must contain at least one letter or number.';
        } elseif ($id !== $originalId && db_row('SELECT id FROM categories WHERE id=?', [$id])) {
            $error = 'That category ID already exists.';
        } else {
            if ($icon === '') {
                $icon = '#';
            }

            if ($id !== $originalId) {
                db_run('UPDATE categories SET id=?, name=?, icon=?, sort_order=? WHERE id=?', [
                    $id,
                    $name,
                    $icon,
                    $sortOrder,
                    $originalId,
                ]);
                db_run('UPDATE products SET category=? WHERE category=?', [$id, $originalId]);
            } else {
                db_run('UPDATE categories SET name=?, icon=?, sort_order=? WHERE id=?', [
                    $name,
                    $icon,
                    $sortOrder,
                    $originalId,
                ]);
            }
            $success = 'Category "'.$name.'" updated successfully.';
        }
    }

    if ($action === 'delete_category') {
        $id = $_POST['category_id'] ?? '';
        $category = db_row('SELECT * FROM categories WHERE id=?', [$id]);
        $productCount = db_row('SELECT COUNT(*) AS c FROM products WHERE category=?', [$id]);
        $usedBy = (int)($productCount['c'] ?? 0);

        if (!$category) {
            $error = 'Category not found.';
        } elseif ($usedBy > 0) {
            $error = 'Cannot remove "'.$category['name'].'" because '.$usedBy.' product(s) use it.';
        } else {
            db_run('DELETE FROM categories WHERE id=?', [$id]);
            $success = 'Category "'.$category['name'].'" removed.';
        }
    }
}

$editId = $_GET['edit'] ?? '';
$editCategory = $editId !== '' ? db_row('SELECT * FROM categories WHERE id=?', [$editId]) : null;
if ($editId !== '' && !$editCategory) {
    $error = $error ?: 'Category not found.';
}

$categories = db_rows('
    SELECT c.id, c.name, c.icon, c.sort_order, COUNT(p.id) AS product_count
    FROM categories c
    LEFT JOIN products p ON p.category = c.id
    GROUP BY c.id, c.name, c.icon, c.sort_order
    ORDER BY c.sort_order ASC, c.name ASC
');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Categories - DETECH Admin</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
<div class="admin-layout">
  <div class="admin-sidebar">
    <div class="admin-logo">DETECH Admin</div>
    <nav style="margin-top:20px;">
      <a href="admin.php" class="admin-nav-item" style="text-decoration:none;">Dashboard</a>
      <a href="admin.php" class="admin-nav-item" style="text-decoration:none;">Products</a>
      <a href="admin.php" class="admin-nav-item" style="text-decoration:none;">Orders</a>
      <a href="category.php" class="admin-nav-item active" style="text-decoration:none;">Categories</a>
      <a href="coupons.php" class="admin-nav-item" style="text-decoration:none;">Coupons</a>
      <div style="margin-top:auto;padding:20px;border-top:1px solid rgba(255,255,255,0.1);">
        <a href="index.php" style="color:#94A3B8;font-size:13px;">Back to Store</a><br>
        <a href="logout.php" style="color:#EF4444;font-size:13px;margin-top:8px;display:block;">Logout</a>
      </div>
    </nav>
  </div>
  <main class="admin-main">
    <div class="admin-header">Categories</div>

    <?php if ($success): ?>
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:10px;padding:12px 16px;color:#16A34A;margin-bottom:16px;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div style="background:#FEF2F2;border:1.5px solid #FCA5A5;border-radius:10px;padding:12px 16px;color:#DC2626;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div style="background:#fff;border-radius:12px;border:1.5px solid #E2E8F0;padding:24px;margin-bottom:24px;max-width:860px;">
      <h2 style="font-size:18px;font-weight:800;margin-bottom:16px;">Add Category</h2>
      <form method="POST">
        <input type="hidden" name="action" value="add_category">
        <div class="admin-form-grid">
          <div class="form-group">
            <label class="form-label">Category Name *</label>
            <input type="text" name="name" class="form-input" placeholder="e.g. Monitors" required>
          </div>
          <div class="form-group">
            <label class="form-label">Category ID</label>
            <input type="text" name="category_id" class="form-input" placeholder="auto from name">
          </div>
          <div class="form-group">
            <label class="form-label">Icon</label>
            <input type="text" name="icon" class="form-input" maxlength="10" placeholder="#">
          </div>
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input" min="0" placeholder="auto">
          </div>
        </div>
        <button type="submit" class="btn-primary" style="margin-top:20px;">Add Category</button>
      </form>
    </div>

    <?php if ($editCategory): ?>
    <div style="background:#fff;border-radius:12px;border:1.5px solid #F97316;padding:24px;margin-bottom:24px;max-width:860px;">
      <h2 style="font-size:18px;font-weight:800;margin-bottom:16px;">Edit Category</h2>
      <form method="POST">
        <input type="hidden" name="action" value="edit_category">
        <input type="hidden" name="original_category_id" value="<?php echo htmlspecialchars($editCategory['id']); ?>">
        <div class="admin-form-grid">
          <div class="form-group">
            <label class="form-label">Category Name *</label>
            <input type="text" name="name" class="form-input" value="<?php echo htmlspecialchars($editCategory['name']); ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Category ID</label>
            <input type="text" name="category_id" class="form-input" value="<?php echo htmlspecialchars($editCategory['id']); ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Icon</label>
            <input type="text" name="icon" class="form-input" maxlength="10" value="<?php echo htmlspecialchars($editCategory['icon']); ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Sort Order</label>
            <input type="number" name="sort_order" class="form-input" min="0" value="<?php echo (int)$editCategory['sort_order']; ?>">
          </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px;">
          <button type="submit" class="btn-primary">Update Category</button>
          <a href="category.php" class="btn-secondary" style="text-decoration:none;">Cancel</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Icon</th>
            <th>Name</th>
            <th>ID</th>
            <th>Sort</th>
            <th>Products</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($categories)): ?>
          <tr><td colspan="6" style="text-align:center;color:#64748B;padding:32px;">No categories found.</td></tr>
          <?php endif; ?>
          <?php foreach ($categories as $category): ?>
          <?php $cannotRemove = (int)$category['product_count'] > 0; ?>
          <tr>
            <td style="font-size:20px;"><?php echo htmlspecialchars($category['icon']); ?></td>
            <td style="font-weight:700;"><?php echo htmlspecialchars($category['name']); ?></td>
            <td><?php echo htmlspecialchars($category['id']); ?></td>
            <td><?php echo (int)$category['sort_order']; ?></td>
            <td><?php echo (int)$category['product_count']; ?></td>
            <td>
              <a href="category.php?edit=<?php echo urlencode($category['id']); ?>" class="btn-success" style="padding:6px 12px;font-size:12px;text-decoration:none;margin-right:6px;">Edit</a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this category?');">
                <input type="hidden" name="action" value="delete_category">
                <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category['id']); ?>">
                <button type="submit" class="btn-danger" style="padding:6px 12px;font-size:12px;<?php echo $cannotRemove ? 'opacity:0.5;cursor:not-allowed;' : ''; ?>" <?php echo $cannotRemove ? 'disabled title="Remove products from this category first"' : ''; ?>>Remove</button>
              </form>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</div>
</body>
</html>

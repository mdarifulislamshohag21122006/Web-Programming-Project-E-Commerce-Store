<?php
require_once 'includes/db.php';

if (!is_logged_in() || !is_admin()) {
    header('Location: auth.php');
    exit;
}

db_run('CREATE TABLE IF NOT EXISTS coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(30) NOT NULL UNIQUE,
    discount TINYINT NOT NULL,
    type ENUM("percent","fixed") DEFAULT "percent",
    min_order INT DEFAULT 0,
    max_uses INT DEFAULT NULL,
    used_count INT DEFAULT 0,
    expires_at DATE DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

$couponTotal = db_row('SELECT COUNT(*) AS c FROM coupons');
if ((int)($couponTotal['c'] ?? 0) === 0) {
    foreach ([['DETECH10', 10], ['SAVE20', 20], ['FLASH15', 15], ['WELCOME5', 5]] as $coupon) {
        db_run('INSERT INTO coupons (code, discount, type) VALUES (?, ?, "percent")', $coupon);
    }
}

function coupon_code(string $value): string {
    $code = strtoupper(trim($value));
    return preg_replace('/[^A-Z0-9_-]/', '', $code);
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_coupon') {
        $code = coupon_code($_POST['code'] ?? '');
        $discount = (int)($_POST['discount'] ?? 0);
        $minOrder = max(0, (int)($_POST['min_order'] ?? 0));
        $maxUses = trim($_POST['max_uses'] ?? '') === '' ? null : max(1, (int)$_POST['max_uses']);
        $expiresAt = trim($_POST['expires_at'] ?? '');
        $expiresAt = $expiresAt === '' ? null : $expiresAt;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($code === '') {
            $error = 'Coupon code is required.';
        } elseif ($discount < 1 || $discount > 100) {
            $error = 'Discount must be between 1 and 100 percent.';
        } elseif (db_row('SELECT id FROM coupons WHERE code=?', [$code])) {
            $error = 'That coupon code already exists.';
        } else {
            db_run('INSERT INTO coupons (code, discount, type, min_order, max_uses, expires_at, is_active) VALUES (?, ?, "percent", ?, ?, ?, ?)', [
                $code,
                $discount,
                $minOrder,
                $maxUses,
                $expiresAt,
                $isActive,
            ]);
            $success = 'Coupon "'.$code.'" added successfully.';
        }
    }

    if ($action === 'edit_coupon') {
        $id = (int)($_POST['coupon_id'] ?? 0);
        $code = coupon_code($_POST['code'] ?? '');
        $discount = (int)($_POST['discount'] ?? 0);
        $minOrder = max(0, (int)($_POST['min_order'] ?? 0));
        $maxUses = trim($_POST['max_uses'] ?? '') === '' ? null : max(1, (int)$_POST['max_uses']);
        $expiresAt = trim($_POST['expires_at'] ?? '');
        $expiresAt = $expiresAt === '' ? null : $expiresAt;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $coupon = db_row('SELECT * FROM coupons WHERE id=?', [$id]);

        if (!$coupon) {
            $error = 'Coupon not found.';
        } elseif ($code === '') {
            $error = 'Coupon code is required.';
        } elseif ($discount < 1 || $discount > 100) {
            $error = 'Discount must be between 1 and 100 percent.';
        } elseif (db_row('SELECT id FROM coupons WHERE code=? AND id<>?', [$code, $id])) {
            $error = 'That coupon code already exists.';
        } else {
            db_run('UPDATE coupons SET code=?, discount=?, type="percent", min_order=?, max_uses=?, expires_at=?, is_active=? WHERE id=?', [
                $code,
                $discount,
                $minOrder,
                $maxUses,
                $expiresAt,
                $isActive,
                $id,
            ]);
            $success = 'Coupon "'.$code.'" updated successfully.';
        }
    }

    if ($action === 'delete_coupon') {
        $id = (int)($_POST['coupon_id'] ?? 0);
        $coupon = db_row('SELECT * FROM coupons WHERE id=?', [$id]);

        if (!$coupon) {
            $error = 'Coupon not found.';
        } else {
            db_run('DELETE FROM coupons WHERE id=?', [$id]);
            $success = 'Coupon "'.$coupon['code'].'" removed.';
        }
    }
}

$editId = (int)($_GET['edit'] ?? 0);
$editCoupon = $editId > 0 ? db_row('SELECT * FROM coupons WHERE id=?', [$editId]) : null;
if ($editId > 0 && !$editCoupon) {
    $error = $error ?: 'Coupon not found.';
}

$coupons = db_rows('SELECT * FROM coupons ORDER BY created_at DESC, id DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Coupons - DETECH Admin</title>
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
      <a href="category.php" class="admin-nav-item" style="text-decoration:none;">Categories</a>
      <a href="coupons.php" class="admin-nav-item active" style="text-decoration:none;">Coupons</a>
      <div style="margin-top:auto;padding:20px;border-top:1px solid rgba(255,255,255,0.1);">
        <a href="index.php" style="color:#94A3B8;font-size:13px;">Back to Store</a><br>
        <a href="logout.php" style="color:#EF4444;font-size:13px;margin-top:8px;display:block;">Logout</a>
      </div>
    </nav>
  </div>
  <main class="admin-main">
    <div class="admin-header">Coupons</div>

    <?php if ($success): ?>
    <div style="background:#F0FDF4;border:1.5px solid #86EFAC;border-radius:10px;padding:12px 16px;color:#16A34A;margin-bottom:16px;"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div style="background:#FEF2F2;border:1.5px solid #FCA5A5;border-radius:10px;padding:12px 16px;color:#DC2626;margin-bottom:16px;"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div style="background:#fff;border-radius:12px;border:1.5px solid #E2E8F0;padding:24px;margin-bottom:24px;max-width:920px;">
      <h2 style="font-size:18px;font-weight:800;margin-bottom:16px;">Add Coupon</h2>
      <form method="POST">
        <input type="hidden" name="action" value="add_coupon">
        <div class="admin-form-grid">
          <div class="form-group">
            <label class="form-label">Coupon Code *</label>
            <input type="text" name="code" class="form-input" placeholder="e.g. SUMMER25" maxlength="30" required>
          </div>
          <div class="form-group">
            <label class="form-label">Discount % *</label>
            <input type="number" name="discount" class="form-input" min="1" max="100" placeholder="10" required>
          </div>
          <div class="form-group">
            <label class="form-label">Minimum Order</label>
            <input type="number" name="min_order" class="form-input" min="0" placeholder="0">
          </div>
          <div class="form-group">
            <label class="form-label">Maximum Uses</label>
            <input type="number" name="max_uses" class="form-input" min="1" placeholder="unlimited">
          </div>
          <div class="form-group">
            <label class="form-label">Expires At</label>
            <input type="date" name="expires_at" class="form-input">
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:12px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;">
              <input type="checkbox" name="is_active" checked style="accent-color:#F97316;width:16px;height:16px;"> Active
            </label>
          </div>
        </div>
        <button type="submit" class="btn-primary" style="margin-top:20px;">Add Coupon</button>
      </form>
    </div>

    <?php if ($editCoupon): ?>
    <div style="background:#fff;border-radius:12px;border:1.5px solid #F97316;padding:24px;margin-bottom:24px;max-width:920px;">
      <h2 style="font-size:18px;font-weight:800;margin-bottom:16px;">Edit Coupon</h2>
      <form method="POST">
        <input type="hidden" name="action" value="edit_coupon">
        <input type="hidden" name="coupon_id" value="<?php echo (int)$editCoupon['id']; ?>">
        <div class="admin-form-grid">
          <div class="form-group">
            <label class="form-label">Coupon Code *</label>
            <input type="text" name="code" class="form-input" value="<?php echo htmlspecialchars($editCoupon['code']); ?>" maxlength="30" required>
          </div>
          <div class="form-group">
            <label class="form-label">Discount % *</label>
            <input type="number" name="discount" class="form-input" min="1" max="100" value="<?php echo (int)$editCoupon['discount']; ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label">Minimum Order</label>
            <input type="number" name="min_order" class="form-input" min="0" value="<?php echo (int)$editCoupon['min_order']; ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Maximum Uses</label>
            <input type="number" name="max_uses" class="form-input" min="1" value="<?php echo $editCoupon['max_uses'] !== null ? (int)$editCoupon['max_uses'] : ''; ?>" placeholder="unlimited">
          </div>
          <div class="form-group">
            <label class="form-label">Expires At</label>
            <input type="date" name="expires_at" class="form-input" value="<?php echo htmlspecialchars($editCoupon['expires_at'] ?? ''); ?>">
          </div>
          <div class="form-group" style="display:flex;align-items:flex-end;padding-bottom:12px;">
            <label style="display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer;">
              <input type="checkbox" name="is_active" <?php echo (int)$editCoupon['is_active'] ? 'checked' : ''; ?> style="accent-color:#F97316;width:16px;height:16px;"> Active
            </label>
          </div>
        </div>
        <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px;">
          <button type="submit" class="btn-primary">Update Coupon</button>
          <a href="coupons.php" class="btn-secondary" style="text-decoration:none;">Cancel</a>
        </div>
      </form>
    </div>
    <?php endif; ?>

    <div style="overflow-x:auto;">
      <table class="data-table">
        <thead>
          <tr>
            <th>Code</th>
            <th>Discount</th>
            <th>Min Order</th>
            <th>Uses</th>
            <th>Expires</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (empty($coupons)): ?>
          <tr><td colspan="7" style="text-align:center;color:#64748B;padding:32px;">No coupons found.</td></tr>
          <?php endif; ?>
          <?php foreach ($coupons as $coupon): ?>
          <tr>
            <td style="font-weight:800;"><?php echo htmlspecialchars($coupon['code']); ?></td>
            <td><?php echo (int)$coupon['discount']; ?>%</td>
            <td><?php echo (int)$coupon['min_order'] > 0 ? 'BDT '.number_format($coupon['min_order']) : 'None'; ?></td>
            <td><?php echo (int)$coupon['used_count']; ?><?php echo $coupon['max_uses'] !== null ? ' / '.(int)$coupon['max_uses'] : ' / unlimited'; ?></td>
            <td><?php echo $coupon['expires_at'] ? htmlspecialchars($coupon['expires_at']) : 'Never'; ?></td>
            <td>
              <span style="display:inline-flex;border-radius:999px;padding:4px 10px;font-size:12px;font-weight:700;background:<?php echo (int)$coupon['is_active'] ? '#DCFCE7;color:#15803D' : '#FEE2E2;color:#DC2626'; ?>;">
                <?php echo (int)$coupon['is_active'] ? 'Active' : 'Inactive'; ?>
              </span>
            </td>
            <td>
              <a href="coupons.php?edit=<?php echo (int)$coupon['id']; ?>" class="btn-success" style="padding:6px 12px;font-size:12px;text-decoration:none;margin-right:6px;">Edit</a>
              <form method="POST" style="display:inline;" onsubmit="return confirm('Remove this coupon?');">
                <input type="hidden" name="action" value="delete_coupon">
                <input type="hidden" name="coupon_id" value="<?php echo (int)$coupon['id']; ?>">
                <button type="submit" class="btn-danger" style="padding:6px 12px;font-size:12px;">Remove</button>
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

<?php
require_once 'includes/db.php';

$error = '';
$success = '';
$tab = $_GET['tab'] ?? 'login';

// Redirect if already logged in
if (is_logged_in()) {
    header('Location: dashboard.php');
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ---- LOGIN ----
    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $pass  = $_POST['password'] ?? '';
        if (!$email || !$pass) {
            $error = 'Please fill all fields.';
        } else {
            $user = db_row('SELECT * FROM users WHERE email = ?', [$email]);
            if ($user && password_verify($pass, $user['password'])) {
                session_login($user);
                header('Location: ' . (is_admin() ? 'admin.php' : 'dashboard.php'));
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        }
        $tab = 'login';
    }

    // ---- REGISTER ----
    if ($action === 'register') {
        $name  = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $pass  = $_POST['password'] ?? '';
        $pass2 = $_POST['password2'] ?? '';

        if (!$name || !$email || !$pass) {
            $error = 'Please fill all required fields.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($pass) < 6) {
            $error = 'Password must be at least 6 characters.';
        } elseif ($pass !== $pass2) {
            $error = 'Passwords do not match.';
        } elseif (db_row('SELECT id FROM users WHERE email = ?', [$email])) {
            $error = 'An account with this email already exists.';
        } else {
            $hash   = password_hash($pass, PASSWORD_DEFAULT);
            $avatar = 'https://i.pravatar.cc/80?u=' . urlencode($email);
            $id = db_run(
                'INSERT INTO users (name, email, password, phone, avatar) VALUES (?,?,?,?,?)',
                [$name, $email, $hash, $phone, $avatar]
            );
            $user = db_row('SELECT * FROM users WHERE id = ?', [$id]);
            session_login($user);
            header('Location: dashboard.php');
            exit;
        }
        $tab = 'register';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Login — DETECH</title>
<link rel="stylesheet" href="css/style.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>
<div class="auth-page">
  <div class="auth-card">
    <div class="auth-logo">⚡ DETECH</div>
    <div style="font-size:14px;color:#64748B;text-align:center;margin-bottom:24px;">Bangladesh's #1 Electronics Store</div>

    <?php if ($error): ?>
    <div style="background:#FEE2E2;border:1.5px solid #FCA5A5;border-radius:10px;padding:12px 16px;color:#DC2626;font-size:14px;margin-bottom:16px;">⚠️ <?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="auth-tabs">
      <div class="auth-tab <?php echo $tab==='login'?'active':''; ?>" onclick="showTab('login')">Login</div>
      <div class="auth-tab <?php echo $tab==='register'?'active':''; ?>" onclick="showTab('register')">Sign Up</div>
    </div>

    <!-- LOGIN -->
    <div id="loginForm" style="display:<?php echo $tab==='login'?'block':'none'; ?>;">
      <form method="POST">
        <input type="hidden" name="action" value="login">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-input" placeholder="you@example.com" value="<?php echo htmlspecialchars($_POST['email']??''); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-input" placeholder="Enter your password" required>
        </div>
        <div style="display:flex;justify-content:flex-end;margin-bottom:20px;">
          <a href="#" style="color:#F97316;font-size:13px;">Forgot password?</a>
        </div>
        <button type="submit" class="btn-primary form-submit">Login to DETECH</button>
      </form>
      <div style="text-align:center;margin-top:16px;color:#64748B;font-size:14px;">
        Don't have an account? <a href="#" onclick="showTab('register')" style="color:#F97316;font-weight:700;">Sign Up</a>
      </div>
      <div style="margin-top:16px;padding:12px;background:#F8FAFC;border-radius:8px;font-size:13px;color:#64748B;">
        <strong>Demo login:</strong> admin@detech.pk / password
      </div>
    </div>

    <!-- REGISTER -->
    <div id="registerForm" style="display:<?php echo $tab==='register'?'block':'none'; ?>;">
      <form method="POST">
        <input type="hidden" name="action" value="register">
        <div class="form-group">
          <label class="form-label">Full Name *</label>
          <input type="text" name="name" class="form-input" placeholder="Enter your full name" value="<?php echo htmlspecialchars($_POST['name']??''); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address *</label>
          <input type="email" name="email" class="form-input" placeholder="***@example.com" value="<?php echo htmlspecialchars($_POST['email']??''); ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" name="phone" class="form-input" placeholder="+880 " value="<?php echo htmlspecialchars($_POST['phone']??''); ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Password *</label>
          <input type="password" name="password" class="form-input" placeholder="Minimum 6 characters" required>
        </div>
        <div class="form-group">
          <label class="form-label">Confirm Password *</label>
          <input type="password" name="password2" class="form-input" placeholder="Repeat your password" required>
        </div>
        <button type="submit" class="btn-primary form-submit">Create Account</button>
      </form>
      <div style="text-align:center;margin-top:16px;color:#64748B;font-size:14px;">
        Have an account? <a href="#" onclick="showTab('login')" style="color:#F97316;font-weight:700;">Login</a>
      </div>
    </div>

    <div style="text-align:center;margin-top:24px;padding-top:20px;border-top:1px solid #E2E8F0;">
      <a href="index.php" style="color:#F97316;font-size:13px;font-weight:600;">← Back to Home</a>
    </div>
  </div>
</div>
<div id="toast" class="toast" style="display:none;"></div>
<script src="js/app.js"></script>
<script>
function showTab(tab) {
  document.getElementById('loginForm').style.display    = tab==='login'    ? 'block' : 'none';
  document.getElementById('registerForm').style.display = tab==='register' ? 'block' : 'none';
  document.querySelectorAll('.auth-tab').forEach((t,i) => t.classList.toggle('active', (i===0&&tab==='login')||(i===1&&tab==='register')));
}
</script>
</body>
</html>

<?php
require_once 'includes/config.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin/dashboard.php' : 'student/profile.php'));
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['role']     = $user['role'];
        $_SESSION['name']     = $user['first_name'] . ' ' . $user['last_name'];
        $_SESSION['photo']    = $user['photo'];
        $_SESSION['reg_no']   = $user['registration_no'];

        if ($user['must_change_password'] && $user['role'] === 'student') {
            header("Location: student/change_password.php");
        } elseif ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: student/profile.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login — Student Registration System</title>
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg); }
body::before {
  content: '';
  position: fixed; inset: 0;
  background: radial-gradient(ellipse 60% 60% at 50% 50%, rgba(255,191,0,0.08) 0%, transparent 70%);
  pointer-events: none;
}
.login-box {
  width: 100%; max-width: 420px;
  background: #ffffff;
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 40px 36px;
  position: relative;
  box-shadow: 0 8px 40px rgba(0,0,0,0.08);
}
.login-box::before {
  content: '';
  position: absolute; top: 0; left: 50%; transform: translateX(-50%);
  width: 60%; height: 1px;
  background: linear-gradient(90deg, transparent, var(--gold), transparent);
}
.login-logo {
  text-align: center; margin-bottom: 28px;
}
.login-logo .icon-wrap {
  width: 60px; height: 60px; border-radius: 16px;
  background: linear-gradient(135deg, var(--gold), var(--orange));
  display: inline-flex; align-items: center; justify-content: center;
  font-size: 1.5rem; color: #000; margin-bottom: 12px;
}
.login-logo h1 { font-size: 1.3rem; font-weight: 800; color: var(--gold); }
.login-logo p  { font-size: 0.75rem; color: var(--muted); }
.input-wrap { position: relative; }
.input-wrap i {
  position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
  color: var(--muted); font-size: 0.9rem;
}
.input-wrap .form-control { padding-left: 38px; }
.login-box .btn-primary { width: 100%; justify-content: center; padding: 11px; font-size: 0.9rem; margin-top: 6px; }
.login-footer { text-align: center; margin-top: 18px; font-size: 0.78rem; color: var(--muted); }
</style>
</head>
<body>
<div class="login-box">
  <div class="login-logo">
    <img src="assets/img/logo.png" width="220px">
    <h1>DB-KIITEC</h1>
    <p>Student Registration System</p>
  </div>

  <?php if ($error): ?>
    <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= $error ?></div>
  <?php endif; ?>

  <form method="POST">
    <div style="display:flex;flex-direction:column;gap:14px;">
      <div class="form-group">
        <label>Email Address</label>
        <div class="input-wrap">
          <i class="fas fa-envelope"></i>
          <input type="email" name="email" class="form-control" placeholder="you@kiitec.tz" required autocomplete="email">
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fas fa-lock"></i>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
      </div>
      <button type="submit" class="btn btn-primary"><i class="fas fa-arrow-right-to-bracket"></i> Sign In</button>
    </div>
  </form>

  <div class="login-footer">
    <p>2026 &copy; DB-KIITEC <strong style="color:var(--orange)">NTA 5</strong></p>
  </div>
</div>
</body>
</html>

<?php
require_once '../includes/config.php';
requireStudent();

$success = $error = '';
$forced = isset($_GET['forced']) || (function() use ($conn) {
    $stmt = $conn->prepare("SELECT must_change_password FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row['must_change_password'];
})();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current  = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    // Fetch current hash
    $stmt = $conn->prepare("SELECT password FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!password_verify($current, $row['password'])) {
        $error = "Current password is incorrect.";
    } elseif (strlen($new_pass) < 8) {
        $error = "New password must be at least 8 characters.";
    } elseif ($new_pass !== $confirm) {
        $error = "Passwords do not match.";
    } else {
        $hash = password_hash($new_pass, PASSWORD_DEFAULT);
        $stmt2 = $conn->prepare("UPDATE users SET password=?, must_change_password=0 WHERE id=?");
        $stmt2->bind_param("si", $hash, $_SESSION['user_id']);
        $stmt2->execute();
        $success = "Password changed successfully!";
        if ($forced) {
            header("Location: profile.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Change Password — Student Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Change <span>Password</span></div>
    </div>

    <div class="page-content">
      <div class="page-header">
        <h2>Change Your Password</h2>
        <p><?= $forced ? 'You must set a new password before continuing.' : 'Update your account password.' ?></p>
      </div>

      <?php if ($forced): ?>
        <div class="alert alert-warning"><i class="fas fa-shield-halved"></i> For your security, please change your default password now.</div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= $success ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= $error ?></div>
      <?php endif; ?>

      <div class="card" style="max-width:460px;">
        <div class="card-header">
          <h3><i class="fas fa-lock" style="color:var(--gold);margin-right:8px;"></i>Set New Password</h3>
        </div>
        <div class="card-body">
          <form method="POST">
            <div style="display:flex;flex-direction:column;gap:16px;">
              <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" class="form-control" required placeholder="Your current password">
              </div>
              <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required placeholder="At least 8 characters" minlength="8">
              </div>
              <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" class="form-control" required placeholder="Repeat new password">
              </div>
              <button type="submit" class="btn btn-primary" style="align-self:flex-start;">
                <i class="fas fa-check"></i> Update Password
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>

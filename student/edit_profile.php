<?php
require_once '../includes/config.php';
requireStudent();

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($conn, $_POST['email']);
    $phone = sanitize($conn, $_POST['phone']);

    $photo = $user['photo'];
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $photo = 'student_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/photos/' . $photo);
        }
    }

    $stmt2 = $conn->prepare("UPDATE users SET email=?, phone=?, photo=? WHERE id=?");
    $stmt2->bind_param("sssi", $email, $phone, $photo, $_SESSION['user_id']);
    if ($stmt2->execute()) {
        $_SESSION['photo'] = $photo;
        $user['email'] = $email;
        $user['phone'] = $phone;
        $user['photo'] = $photo;
        $success = "Profile updated successfully!";
    } else {
        $error = "Error updating profile.";
    }
}

$ph = file_exists("../uploads/photos/".$user['photo']) ? "../uploads/photos/".$user['photo'] : "../assets/img/default.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Profile — Student Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Edit <span>Profile</span></div>
      <div class="topbar-right">
        <a href="profile.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
    </div>

    <div class="page-content">
      <div class="page-header">
        <h2>Edit Your Profile</h2>
        <p>You can update your contact information and profile photo.</p>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= $success ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= $error ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-pen-to-square" style="color:var(--gold);margin-right:8px;"></i>Update Details</h3>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">
            <div style="text-align:center;margin-bottom:24px;">
              <img src="<?= $ph ?>" id="photoPreview" class="photo-preview">
              <div>
                <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                  <i class="fas fa-camera"></i> Change Photo
                  <input type="file" name="photo" id="photoInput" style="display:none;" accept="image/*">
                </label>
              </div>
            </div>

            <!-- Read-only info -->
            <div class="alert alert-warning" style="margin-bottom:18px;">
              <i class="fas fa-lock"></i>
              Name, Registration No., Course, and Date of Birth can only be changed by an administrator.
            </div>

            <div class="form-grid" style="margin-bottom:20px;">
              <div class="form-group">
                <label>First Name</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['first_name']) ?>" disabled style="opacity:0.5;cursor:not-allowed;">
              </div>
              <div class="form-group">
                <label>Last Name</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['last_name']) ?>" disabled style="opacity:0.5;cursor:not-allowed;">
              </div>
              <div class="form-group">
                <label>Registration No.</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($user['registration_no']) ?>" disabled style="opacity:0.5;cursor:not-allowed;">
              </div>
              <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
              </div>
              <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" value="<?= htmlspecialchars($user['phone']) ?>">
              </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;">
              <a href="profile.php" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('photoInput').addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('photoPreview').src = e.target.result;
    reader.readAsDataURL(file);
  }
});
</script>
</body>
</html>

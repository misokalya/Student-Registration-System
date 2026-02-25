<?php
require_once '../includes/config.php';
requireStudent();

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$ph = file_exists("../uploads/photos/".$user['photo']) ? "../uploads/photos/".$user['photo'] : "../assets/img/default.png";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile — Student Registration System</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">My <span>Profile</span></div>
      <div class="topbar-right">
        <a href="edit_profile.php" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Edit Profile</a>
      </div>
    </div>

    <div class="page-content">
      <?php if ($user['must_change_password']): ?>
        <div class="alert alert-warning">
          <i class="fas fa-triangle-exclamation"></i>
          You are using a default password. <a href="change_password.php" style="color:var(--gold);font-weight:700;">Please change it now.</a>
        </div>
      <?php endif; ?>

      <!-- HERO -->
      <div class="profile-hero">
        <img src="<?= $ph ?>" alt="Profile Photo">
        <div class="info">
          <h2><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></h2>
          <div class="reg"><?= htmlspecialchars($user['registration_no']) ?></div>
          <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap;">
            <?php if($user['course_type']==='fulltime'): ?>
              <span class="badge badge-gold"><i class="fas fa-user-graduate"></i> Full-Time</span>
              <span class="badge badge-yellow">Year <?= $user['year_of_study'] ?></span>
            <?php else: ?>
              <span class="badge badge-orange"><i class="fas fa-clock"></i> Short Course</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <!-- DETAILS GRID -->
      <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
          <h3><i class="fas fa-circle-info" style="color:var(--gold);margin-right:8px;"></i>Personal Information</h3>
        </div>
        <div class="card-body">
          <div class="profile-detail-grid">
            <div class="detail-item">
              <label><i class="fas fa-user"></i> First Name</label>
              <span><?= htmlspecialchars($user['first_name']) ?></span>
            </div>
            <div class="detail-item">
              <label><i class="fas fa-user"></i> Last Name</label>
              <span><?= htmlspecialchars($user['last_name']) ?></span>
            </div>
            <div class="detail-item">
              <label><i class="fas fa-cake-candles"></i> Date of Birth</label>
              <span><?= $user['date_of_birth'] ? date('F d, Y', strtotime($user['date_of_birth'])) : '—' ?></span>
            </div>
            <div class="detail-item">
              <label><i class="fas fa-envelope"></i> Email</label>
              <span><?= htmlspecialchars($user['email']) ?></span>
            </div>
            <div class="detail-item">
              <label><i class="fas fa-phone"></i> Phone</label>
              <span><?= htmlspecialchars($user['phone'] ?: '—') ?></span>
            </div>
            <div class="detail-item">
              <label><i class="fas fa-book-open"></i> Course</label>
              <span><?= htmlspecialchars($user['course']) ?></span>
            </div>
            <div class="detail-item">
              <label><i class="fas fa-layer-group"></i> Course Type</label>
              <span><?= ucfirst($user['course_type'] === 'shortcourse' ? 'Short Course' : 'Full-Time') ?></span>
            </div>
            <?php if($user['course_type'] === 'fulltime'): ?>
            <div class="detail-item">
              <label><i class="fas fa-list-ol"></i> Year of Study</label>
              <span>Year <?= $user['year_of_study'] ?></span>
            </div>
            <?php endif; ?>
            <div class="detail-item">
              <label><i class="fas fa-calendar-plus"></i> Registered</label>
              <span><?= date('F d, Y', strtotime($user['created_at'])) ?></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>

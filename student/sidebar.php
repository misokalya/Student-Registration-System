<?php
$current = basename($_SERVER['PHP_SELF']);
$photo = $_SESSION['photo'] ?? 'default.png';
$photoPath = file_exists("../uploads/photos/" . $photo) ? "../uploads/photos/" . $photo : "../assets/img/default.png";
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <img src="../assets/img/logo.png" width="65px">
    <div class="brand-text">
      DB-KIITEC - SRS
      <span>Student Panel</span>
    </div>
  </div>

  <div style="padding:12px;">
    <!-- Student Avatar -->
    <div style="text-align:center;padding:20px 0 16px;border-bottom:1px solid var(--border);margin-bottom:8px;">
      <img src="<?= $photoPath ?>" style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:3px solid var(--gold);margin-bottom:8px;">
      <div style="font-weight:700;font-size:0.88rem;"><?= htmlspecialchars($_SESSION['name']) ?></div>
      <div style="font-size:0.68rem;color:var(--muted);font-family:monospace;"><?= htmlspecialchars($_SESSION['reg_no']) ?></div>
    </div>

    <div class="sidebar-section">My Account</div>
    <a href="profile.php" class="nav-item <?= $current==='profile.php'?'active':'' ?>">
      <i class="fas fa-id-card"></i> My Profile
    </a>
    <a href="results.php" class="nav-item <?= $current==='results.php'?'active':'' ?>">
      <i class="fas fa-chart-bar"></i> My Results
    </a>
    <a href="transcript.php" class="nav-item <?= $current==='transcript.php'?'active':'' ?>" target="_blank">
      <i class="fas fa-file-pdf"></i> Download Transcript
    </a>
    <a href="edit_profile.php" class="nav-item <?= $current==='edit_profile.php'?'active':'' ?>">
      <i class="fas fa-pen-to-square"></i> Edit Profile
    </a>
    <a href="change_password.php" class="nav-item <?= $current==='change_password.php'?'active':'' ?>">
      <i class="fas fa-lock"></i> Change Password
    </a>
  </div>

  <div class="sidebar-footer">
    <a href="../logout.php" class="nav-item" style="margin:0;color:var(--danger);">
      <i class="fas fa-right-from-bracket" style="color:var(--danger);"></i> Logout
    </a>
  </div>
</aside>

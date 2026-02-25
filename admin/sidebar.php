<?php
$current = basename($_SERVER['PHP_SELF']);
$photo = $_SESSION['photo'] ?? 'default.png';
$photoPath = (file_exists("../uploads/photos/" . $photo)) ? "../uploads/photos/" . $photo : "../assets/img/default.png";
?>
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <div class="brand-icon"><i class="fas fa-graduation-cap"></i></div>
    <div class="brand-text">
      College Portal
      <span>Admin Panel</span>
    </div>
  </div>

  <div style="padding:12px;">
    <div class="sidebar-section">Main</div>
    <a href="dashboard.php" class="nav-item <?= $current==='dashboard.php'?'active':'' ?>">
      <i class="fas fa-gauge-high"></i> Dashboard
    </a>

    <div class="sidebar-section">Students</div>
    <a href="students.php?type=fulltime" class="nav-item <?= ($current==='students.php' && ($_GET['type']??'')==='fulltime')?'active':'' ?>">
      <i class="fas fa-user-graduate"></i> Full-Time Students
    </a>
    <a href="students.php?type=shortcourse" class="nav-item <?= ($current==='students.php' && ($_GET['type']??'')==='shortcourse')?'active':'' ?>">
      <i class="fas fa-clock"></i> Short Course Students
    </a>
    <a href="students.php" class="nav-item <?= ($current==='students.php' && !isset($_GET['type']))?'active':'' ?>">
      <i class="fas fa-users"></i> All Students
    </a>
    <a href="add_student.php" class="nav-item <?= $current==='add_student.php'?'active':'' ?>">
      <i class="fas fa-user-plus"></i> Add Student
    </a>

    <div class="sidebar-section">Results</div>
    <a href="upload_results.php" class="nav-item <?= $current==='upload_results.php'?'active':'' ?>">
      <i class="fas fa-upload"></i> Upload Results CSV
    </a>
    <a href="view_results.php" class="nav-item <?= $current==='view_results.php'?'active':'' ?>">
      <i class="fas fa-chart-bar"></i> View Results
    </a>
    <a href="subjects.php" class="nav-item <?= $current==='subjects.php'?'active':'' ?>">
      <i class="fas fa-book-bookmark"></i> Manage Subjects
    </a>
  </div>

  <div class="sidebar-footer">
    <div class="nav-item" style="margin:0;">
      <img src="<?= $photoPath ?>" style="width:30px;height:30px;border-radius:50%;object-fit:cover;border:2px solid var(--gold);">
      <div style="flex:1;overflow:hidden;">
        <div style="font-size:0.8rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($_SESSION['name']) ?></div>
        <div style="font-size:0.65rem;color:var(--muted);">Administrator</div>
      </div>
    </div>
    <a href="../logout.php" class="nav-item" style="margin:4px 0 0; color:var(--danger);">
      <i class="fas fa-right-from-bracket" style="color:var(--danger);"></i> Logout
    </a>
  </div>
</aside>

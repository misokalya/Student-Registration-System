<?php
require_once '../includes/config.php';
requireAdmin();

// Stats
$stats = [];

// Total fulltime
$r = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role='student' AND course_type='fulltime'");
$stats['fulltime_total'] = $r->fetch_assoc()['cnt'];

// Total shortcourse
$r = $conn->query("SELECT COUNT(*) AS cnt FROM users WHERE role='student' AND course_type='shortcourse'");
$stats['short_total'] = $r->fetch_assoc()['cnt'];

// Total students
$stats['total'] = $stats['fulltime_total'] + $stats['short_total'];

// Distinct courses fulltime
$r = $conn->query("SELECT COUNT(DISTINCT course) AS cnt FROM users WHERE role='student' AND course_type='fulltime'");
$stats['fulltime_courses'] = $r->fetch_assoc()['cnt'];

// Distinct courses shortcourse
$r = $conn->query("SELECT COUNT(DISTINCT course) AS cnt FROM users WHERE role='student' AND course_type='shortcourse'");
$stats['short_courses'] = $r->fetch_assoc()['cnt'];

// Students per fulltime course
$ft_courses = $conn->query("SELECT course, COUNT(*) AS cnt FROM users WHERE role='student' AND course_type='fulltime' GROUP BY course ORDER BY cnt DESC");

// Students per short course
$sc_courses = $conn->query("SELECT course, COUNT(*) AS cnt FROM users WHERE role='student' AND course_type='shortcourse' GROUP BY course ORDER BY cnt DESC");

// Recent students
$recent = $conn->query("SELECT * FROM users WHERE role='student' ORDER BY created_at DESC LIMIT 5");

// Year breakdown
$years = $conn->query("SELECT year_of_study, COUNT(*) AS cnt FROM users WHERE role='student' AND course_type='fulltime' GROUP BY year_of_study ORDER BY year_of_study");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Admin <span>Dashboard</span></div>
      <div class="topbar-right">
        <div class="user-info">
          <div class="name"><?= htmlspecialchars($_SESSION['name']) ?></div>
          <div class="role">Administrator</div>
        </div>
      </div>
    </div>

    <div class="page-content">
      <div class="page-header">
        <h2>Welcome back 👋</h2>
        <p>Here's an overview of the registration system.</p>
      </div>

      <!-- STAT CARDS -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-icon gold"><i class="fas fa-users"></i></div>
          <div class="stat-info">
            <div class="val"><?= $stats['total'] ?></div>
            <div class="lbl">Total Students</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon orange"><i class="fas fa-user-graduate"></i></div>
          <div class="stat-info">
            <div class="val"><?= $stats['fulltime_total'] ?></div>
            <div class="lbl">Full-Time Students</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon yellow"><i class="fas fa-clock"></i></div>
          <div class="stat-info">
            <div class="val"><?= $stats['short_total'] ?></div>
            <div class="lbl">Short Course Students</div>
          </div>
        </div>
        <div class="stat-card">
          <div class="stat-icon lt"><i class="fas fa-book-open"></i></div>
          <div class="stat-info">
            <div class="val"><?= $stats['fulltime_courses'] + $stats['short_courses'] ?></div>
            <div class="lbl">Active Courses</div>
          </div>
        </div>
      </div>

      <!-- TWO COLUMN BREAKDOWN -->
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;" class="dash-grid">

        <!-- FULLTIME COURSES -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-user-graduate" style="color:var(--gold);margin-right:8px;"></i>Full-Time Courses</h3>
            <span class="badge badge-gold"><?= $stats['fulltime_courses'] ?> courses</span>
          </div>
          <div class="card-body" style="padding:0;">
            <table>
              <thead><tr><th>Course</th><th>Enrolled</th></tr></thead>
              <tbody>
              <?php while($row = $ft_courses->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['course']) ?></td>
                  <td>
                    <span class="badge badge-gold"><?= $row['cnt'] ?></span>
                  </td>
                </tr>
              <?php endwhile; ?>
              <?php if($ft_courses->num_rows === 0): ?>
                <tr><td colspan="2" style="text-align:center;color:var(--muted);padding:20px;">No students yet</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- SHORT COURSES -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-clock" style="color:var(--orange);margin-right:8px;"></i>Short Courses</h3>
            <span class="badge badge-orange"><?= $stats['short_courses'] ?> courses</span>
          </div>
          <div class="card-body" style="padding:0;">
            <table>
              <thead><tr><th>Course</th><th>Enrolled</th></tr></thead>
              <tbody>
              <?php while($row = $sc_courses->fetch_assoc()): ?>
                <tr>
                  <td><?= htmlspecialchars($row['course']) ?></td>
                  <td><span class="badge badge-orange"><?= $row['cnt'] ?></span></td>
                </tr>
              <?php endwhile; ?>
              <?php if($sc_courses->num_rows === 0): ?>
                <tr><td colspan="2" style="text-align:center;color:var(--muted);padding:20px;">No students yet</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- YEAR BREAKDOWN (fulltime) -->
      <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
          <h3><i class="fas fa-layer-group" style="color:var(--yellow);margin-right:8px;"></i>Full-Time Year Distribution</h3>
        </div>
        <div class="card-body">
          <div style="display:flex;gap:16px;flex-wrap:wrap;">
          <?php
          $year_data = [1=>0, 2=>0, 3=>0];
          while($y = $years->fetch_assoc()) { $year_data[$y['year_of_study']] = $y['cnt']; }
          foreach($year_data as $yr => $cnt):
          ?>
            <div style="flex:1;min-width:120px;background:var(--surface2);border-radius:12px;padding:16px;text-align:center;border:1px solid var(--border);">
              <div style="font-size:1.8rem;font-weight:800;color:var(--gold);"><?= $cnt ?></div>
              <div style="font-size:0.72rem;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Year <?= $yr ?></div>
            </div>
          <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- RECENT STUDENTS -->
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-clock-rotate-left" style="color:var(--gold-lt);margin-right:8px;"></i>Recently Added Students</h3>
          <a href="students.php" class="btn btn-secondary btn-sm">View All</a>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-wrap">
            <table>
              <thead><tr><th>Student</th><th>Reg No.</th><th>Course</th><th>Type</th><th>Added</th></tr></thead>
              <tbody>
              <?php while($s = $recent->fetch_assoc()): 
                $ph = file_exists("../uploads/photos/".$s['photo']) ? "../uploads/photos/".$s['photo'] : "../assets/img/default.png";
              ?>
                <tr>
                  <td>
                    <img src="<?= $ph ?>" class="student-avatar">
                    <?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?>
                  </td>
                  <td style="font-family:monospace;font-size:0.78rem;color:var(--muted);"><?= htmlspecialchars($s['registration_no']) ?></td>
                  <td><?= htmlspecialchars($s['course']) ?></td>
                  <td>
                    <?php if($s['course_type']==='fulltime'): ?>
                      <span class="badge badge-gold">Full-Time</span>
                    <?php else: ?>
                      <span class="badge badge-orange">Short Course</span>
                    <?php endif; ?>
                  </td>
                  <td style="color:var(--muted);font-size:0.78rem;"><?= date('M d, Y', strtotime($s['created_at'])) ?></td>
                </tr>
              <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<style>
@media(max-width:700px){ .dash-grid{ grid-template-columns:1fr !important; } }
</style>
</body>
</html>

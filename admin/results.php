<?php
require_once '../includes/config.php';
requireStudent();

$stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

$res = $conn->prepare("SELECT r.*, COALESCE(s.subject_name, r.subject_name, '') AS resolved_name
    FROM results r LEFT JOIN subjects s ON s.subject_code = r.subject_code
    WHERE r.registration_no=? ORDER BY r.subject_code");
$res->bind_param("s", $user['registration_no']);
$res->execute();
$results = $res->get_result()->fetch_all(MYSQLI_ASSOC);

$grade_counts = ['A'=>0,'B'=>0,'C'=>0,'D'=>0,'F'=>0];
$total_avg = 0;
if (!empty($results)) {
    foreach ($results as $r) {
        $g = strtoupper($r['grade']);
        if (isset($grade_counts[$g])) $grade_counts[$g]++;
        $total_avg += $r['total_score'];
    }
    $total_avg = round($total_avg / count($results), 1);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Results — Student Portal</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.grade-A{color:#16a34a;font-weight:700;font-size:1rem;}
.grade-B{color:#2563eb;font-weight:700;font-size:1rem;}
.grade-C{color:#d97706;font-weight:700;font-size:1rem;}
.grade-D,.grade-F{color:#dc2626;font-weight:700;font-size:1rem;}
.score-bar-wrap{width:100px;height:7px;background:var(--border);border-radius:99px;display:inline-block;vertical-align:middle;}
.score-bar{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--gold),var(--orange));}
.grade-pill{display:flex;flex-direction:column;align-items:center;justify-content:center;
  width:56px;height:56px;border-radius:14px;font-size:1.3rem;font-weight:800;}
.grade-pill-A{background:rgba(22,163,74,0.1);color:#16a34a;}
.grade-pill-B{background:rgba(37,99,235,0.1);color:#2563eb;}
.grade-pill-C{background:rgba(217,119,6,0.1);color:#d97706;}
.grade-pill-D,.grade-pill-F{background:rgba(220,38,38,0.1);color:#dc2626;}
</style>
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">My <span>Results</span></div>
      <div class="topbar-right">
        <?php if (!empty($results)): ?>
        <a href="transcript.php" class="btn btn-primary btn-sm" target="_blank">
          <i class="fas fa-file-pdf"></i> Download Transcript
        </a>
        <?php endif; ?>
      </div>
    </div>

    <div class="page-content">
      <div class="page-header">
        <h2>Academic Results</h2>
        <p><?= htmlspecialchars($user['course']) ?> &nbsp;·&nbsp; <?= $user['course_type']==='fulltime' ? 'Year '.$user['year_of_study'].' · Full-Time' : 'Short Course' ?></p>
      </div>

      <?php if (empty($results)): ?>
        <div class="card">
          <div class="card-body" style="text-align:center;padding:60px;">
            <i class="fas fa-hourglass-half" style="font-size:3rem;color:var(--gold);display:block;margin-bottom:16px;"></i>
            <h3 style="margin-bottom:8px;">No results available yet</h3>
            <p style="color:var(--muted);">Your results will appear here once they have been uploaded by the administration.</p>
          </div>
        </div>
      <?php else: ?>

        <!-- SUMMARY STATS -->
        <div class="stats-grid" style="margin-bottom:24px;">
          <div class="stat-card">
            <div class="stat-icon gold"><i class="fas fa-book-open"></i></div>
            <div class="stat-info">
              <div class="val"><?= count($results) ?></div>
              <div class="lbl">Subjects</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon orange"><i class="fas fa-chart-line"></i></div>
            <div class="stat-info">
              <div class="val"><?= $total_avg ?></div>
              <div class="lbl">Average Score</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon yellow"><i class="fas fa-star"></i></div>
            <div class="stat-info">
              <div class="val"><?= $grade_counts['A'] ?></div>
              <div class="lbl">A Grades</div>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon lt"><i class="fas fa-award"></i></div>
            <div class="stat-info">
              <div class="val"><?= $grade_counts['A'] + $grade_counts['B'] ?></div>
              <div class="lbl">A + B Grades</div>
            </div>
          </div>
        </div>

        <!-- RESULTS TABLE -->
        <div class="card" style="margin-bottom:24px;">
          <div class="card-header">
            <h3><i class="fas fa-table-list" style="color:var(--gold);margin-right:8px;"></i>Subject Results</h3>
            <a href="transcript.php" target="_blank" class="btn btn-primary btn-sm">
              <i class="fas fa-file-pdf"></i> PDF Transcript
            </a>
          </div>
          <div class="card-body" style="padding:0;">
            <div class="table-wrap">
              <table>
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Subject Code</th>
                    <th>Subject Name</th>
                    <th>CA <small style="font-weight:400;">(60)</small></th>
                    <th>SE <small style="font-weight:400;">(40)</small></th>
                    <th>Total <small style="font-weight:400;">(100)</small></th>
                    <th>Grade</th>
                    <th>Progress</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach ($results as $i => $r): ?>
                  <tr>
                    <td style="color:var(--muted);font-size:0.78rem;"><?= $i+1 ?></td>
                    <td><code style="background:var(--surface2);padding:2px 8px;border-radius:6px;font-size:0.82rem;"><?= htmlspecialchars($r['subject_code']) ?></code></td>
                    <td style="font-size:0.82rem;color:var(--muted);"><?= $r['resolved_name'] !== '' ? htmlspecialchars($r['resolved_name']) : '<em>—</em>' ?></td>
                    <td><?= $r['ca_score'] ?></td>
                    <td><?= $r['se_score'] ?></td>
                    <td><strong style="font-size:0.95rem;"><?= $r['total_score'] ?></strong></td>
                    <td><span class="grade-<?= htmlspecialchars($r['grade']) ?>"><?= htmlspecialchars($r['grade']) ?></span></td>
                    <td>
                      <div class="score-bar-wrap">
                        <div class="score-bar" style="width:<?= $r['total_score'] ?>%;"></div>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr style="background:rgba(255,191,0,0.05);border-top:2px solid var(--border);">
                    <td colspan="4" style="text-align:right;font-weight:700;padding:10px 14px;">Overall Average:</td>
                    <td style="font-weight:800;color:var(--gold);font-size:1rem;"><?= $total_avg ?></td>
                    <td colspan="2"></td>
                  </tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <!-- GRADE DISTRIBUTION -->
        <div class="card">
          <div class="card-header"><h3><i class="fas fa-chart-bar" style="color:var(--gold);margin-right:8px;"></i>Grade Distribution</h3></div>
          <div class="card-body">
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
            <?php foreach(['A','B','C','D','F'] as $g): if($grade_counts[$g]===0) continue; ?>
              <div style="flex:1;min-width:80px;background:var(--surface2);border:1px solid var(--border);border-radius:12px;padding:16px;text-align:center;">
                <div class="grade-pill grade-pill-<?= $g ?>" style="margin:0 auto 8px;"><?= $g ?></div>
                <div style="font-size:1.6rem;font-weight:800;"><?= $grade_counts[$g] ?></div>
                <div style="font-size:0.68rem;color:var(--muted);text-transform:uppercase;">subject<?= $grade_counts[$g]!==1?'s':'' ?></div>
              </div>
            <?php endforeach; ?>
            </div>
          </div>
        </div>

      <?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>

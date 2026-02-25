<?php
require_once '../includes/config.php';
requireAdmin();

// Get all students who have results
$students_with_results = $conn->query("
    SELECT DISTINCT u.registration_no, u.first_name, u.last_name, u.course, u.course_type, u.year_of_study,
        COUNT(r.id) AS subject_count,
        ROUND(AVG(r.total_score),1) AS avg_score
    FROM results r
    JOIN users u ON u.registration_no = r.registration_no
    WHERE u.role='student'
    GROUP BY u.registration_no
    ORDER BY u.course_type, u.course, u.last_name
");

// Filter
$filter_reg = $_GET['reg'] ?? '';
$results_data = [];
if ($filter_reg) {
    $fr = sanitize($conn, $filter_reg);
    $res = $conn->query("SELECT r.*, COALESCE(s.subject_name, r.subject_name, '') AS resolved_name FROM results r LEFT JOIN subjects s ON s.subject_code = r.subject_code WHERE r.registration_no='$fr' ORDER BY r.subject_code");
    while ($r = $res->fetch_assoc()) { $results_data[] = $r; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Results — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.grade-A{color:#16a34a;font-weight:700;} .grade-B{color:#2563eb;font-weight:700;}
.grade-C{color:#d97706;font-weight:700;} .grade-D,.grade-F{color:#dc2626;font-weight:700;}
.score-bar-wrap{width:80px;height:6px;background:var(--border);border-radius:99px;display:inline-block;vertical-align:middle;margin-left:8px;}
.score-bar{height:100%;border-radius:99px;background:linear-gradient(90deg,var(--gold),var(--orange));}
</style>
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Student <span>Results</span></div>
      <div class="topbar-right">
        <a href="upload_results.php" class="btn btn-primary btn-sm"><i class="fas fa-upload"></i> Upload CSV</a>
      </div>
    </div>

    <div class="page-content">
      <div style="display:grid;grid-template-columns:300px 1fr;gap:20px;align-items:start;">

        <!-- STUDENT LIST PANEL -->
        <div class="card" style="position:sticky;top:80px;">
          <div class="card-header"><h3><i class="fas fa-users" style="color:var(--gold);margin-right:8px;"></i>Students</h3></div>
          <div style="padding:12px;">
            <input type="text" id="stuSearch" placeholder="Search..." class="form-control" style="margin-bottom:10px;">
            <div id="stuList" style="max-height:65vh;overflow-y:auto;display:flex;flex-direction:column;gap:4px;">
            <?php while($s = $students_with_results->fetch_assoc()):
              $active = ($filter_reg === $s['registration_no']) ? 'style="background:rgba(255,191,0,0.1);border-color:var(--gold);"' : '';
            ?>
              <a href="view_results.php?reg=<?= urlencode($s['registration_no']) ?>"
                 style="display:block;padding:10px 12px;border-radius:10px;border:1px solid var(--border);text-decoration:none;color:inherit;transition:all .2s;<?= ($filter_reg===$s['registration_no'])?'background:rgba(255,191,0,0.1);border-color:var(--gold);':'' ?>"
                 class="stu-item">
                <div style="font-weight:700;font-size:0.85rem;"><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></div>
                <div style="font-size:0.7rem;color:var(--muted);font-family:monospace;"><?= htmlspecialchars($s['registration_no']) ?></div>
                <div style="display:flex;justify-content:space-between;margin-top:4px;">
                  <span style="font-size:0.68rem;color:var(--muted);"><?= $s['subject_count'] ?> subjects</span>
                  <span style="font-size:0.72rem;font-weight:700;color:var(--gold);">Avg: <?= $s['avg_score'] ?></span>
                </div>
              </a>
            <?php endwhile; ?>
            </div>
          </div>
        </div>

        <!-- RESULTS PANEL -->
        <div>
          <?php if ($filter_reg && !empty($results_data)):
            $reg_stmt = $conn->prepare("SELECT * FROM users WHERE registration_no=?");
            $reg_stmt->bind_param("s", $filter_reg);
            $reg_stmt->execute();
            $stu = $reg_stmt->get_result()->fetch_assoc();
            $total_avg = round(array_sum(array_column($results_data,'total_score')) / count($results_data), 1);
          ?>
          <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
              <div>
                <strong style="font-size:1rem;"><?= htmlspecialchars($stu['first_name'].' '.$stu['last_name']) ?></strong>
                <span style="color:var(--muted);margin-left:8px;font-size:0.8rem;"><?= htmlspecialchars($filter_reg) ?></span>
              </div>
              <div style="display:flex;gap:8px;">
                <a href="../student/transcript.php?reg=<?= urlencode($filter_reg) ?>&admin=1" target="_blank" class="btn btn-primary btn-sm">
                  <i class="fas fa-file-pdf"></i> View Transcript
                </a>
              </div>
            </div>
            <div class="card-body">
              <div style="display:flex;gap:16px;flex-wrap:wrap;margin-bottom:20px;">
                <div style="background:var(--surface2);border-radius:10px;padding:12px 20px;text-align:center;border:1px solid var(--border);">
                  <div style="font-size:1.5rem;font-weight:800;color:var(--gold);"><?= count($results_data) ?></div>
                  <div style="font-size:0.68rem;color:var(--muted);text-transform:uppercase;">Subjects</div>
                </div>
                <div style="background:var(--surface2);border-radius:10px;padding:12px 20px;text-align:center;border:1px solid var(--border);">
                  <div style="font-size:1.5rem;font-weight:800;color:var(--orange);"><?= $total_avg ?></div>
                  <div style="font-size:0.68rem;color:var(--muted);text-transform:uppercase;">Average Score</div>
                </div>
                <div style="background:var(--surface2);border-radius:10px;padding:12px 20px;text-align:center;border:1px solid var(--border);">
                  <div style="font-size:1.5rem;font-weight:800;color:var(--gold);"><?= $stu['course_type']==='fulltime'?'Year '.$stu['year_of_study']:'Short Course' ?></div>
                  <div style="font-size:0.68rem;color:var(--muted);text-transform:uppercase;">Level</div>
                </div>
              </div>
              <div class="table-wrap">
                <table>
                  <thead>
                    <tr><th>Subject Code</th><th>Subject Name</th><th>CA (60)</th><th>SE (40)</th><th>Total (100)</th><th>Grade</th><th>Progress</th></tr>
                  </thead>
                  <tbody>
                  <?php foreach ($results_data as $r): ?>
                    <tr>
                      <td><code><?= htmlspecialchars($r['subject_code']) ?></code></td>
                      <td style="font-size:0.8rem;color:var(--muted);"><?= $r['resolved_name'] !== '' ? htmlspecialchars($r['resolved_name']) : '<em>—</em>' ?></td>
                      <td><?= $r['ca_score'] ?></td>
                      <td><?= $r['se_score'] ?></td>
                      <td><strong><?= $r['total_score'] ?></strong></td>
                      <td><span class="grade-<?= $r['grade'] ?>"><?= $r['grade'] ?></span></td>
                      <td>
                        <div class="score-bar-wrap">
                          <div class="score-bar" style="width:<?= $r['total_score'] ?>%"></div>
                        </div>
                        <span style="font-size:0.72rem;color:var(--muted);margin-left:4px;"><?= $r['total_score'] ?>%</span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
          <?php elseif($filter_reg): ?>
            <div class="card"><div class="card-body" style="text-align:center;padding:40px;color:var(--muted);">
              <i class="fas fa-folder-open" style="font-size:2rem;display:block;margin-bottom:10px;"></i>
              No results found for this student.
            </div></div>
          <?php else: ?>
            <div class="card"><div class="card-body" style="text-align:center;padding:60px;color:var(--muted);">
              <i class="fas fa-arrow-left" style="font-size:2rem;display:block;margin-bottom:10px;color:var(--gold);"></i>
              Select a student from the list to view their results.
            </div></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>
<script>
document.getElementById('stuSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.stu-item').forEach(el => {
    el.style.display = el.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>

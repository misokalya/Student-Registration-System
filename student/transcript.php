<?php
require_once '../includes/config.php';

// Determine who we're generating for
if (isset($_GET['admin']) && isAdmin()) {
    $reg_no = sanitize($conn, $_GET['reg'] ?? '');
    if (!$reg_no) { die("No registration number provided."); }
    $stmt = $conn->prepare("SELECT * FROM users WHERE registration_no=? AND role='student'");
    $stmt->bind_param("s", $reg_no);
} else {
    requireStudent();
    $stmt = $conn->prepare("SELECT * FROM users WHERE id=?");
    $stmt->bind_param("i", $_SESSION['user_id']);
}
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
if (!$user) { die("Student not found."); }

// Fetch results with subject names
$res = $conn->prepare("SELECT r.*, COALESCE(s.subject_name, r.subject_name, '') AS resolved_name
    FROM results r LEFT JOIN subjects s ON s.subject_code = r.subject_code
    WHERE r.registration_no=? ORDER BY r.subject_code");
$res->bind_param("s", $user['registration_no']);
$res->execute();
$results = $res->get_result()->fetch_all(MYSQLI_ASSOC);
if (empty($results)) { die("No results available for this student yet."); }

// Compute stats
$total_sum = 0;
$grade_points = ['A' => 4, 'B' => 3, 'C' => 2, 'D' => 1, 'F' => 0];
$gpa_sum = 0;
$grade_counts = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'F' => 0];
foreach ($results as $r) {
    $total_sum += $r['total_score'];
    $g = strtoupper(trim($r['grade']));
    $gpa_sum += $grade_points[$g] ?? 0;
    if (isset($grade_counts[$g])) $grade_counts[$g]++;
}
$avg_score     = round($total_sum / count($results), 2);
$gpa           = round($gpa_sum / count($results), 2);
$subject_count = count($results);

function overallRemark($avg) {
    if ($avg >= 80) return ['Distinction', '#15803d'];
    if ($avg >= 65) return ['Credit',      '#1d4ed8'];
    if ($avg >= 50) return ['Pass',        '#b45309'];
    return            ['Fail',             '#b91c1c'];
}
[$remark, $remark_color] = overallRemark($avg_score);

$remarks_map = ['A' => 'Excellent', 'B' => 'Very Good', 'C' => 'Good', 'D' => 'Pass', 'F' => 'Fail'];
$course_type_label = $user['course_type'] === 'fulltime'
    ? 'Full-Time &mdash; Year ' . $user['year_of_study']
    : 'Short Course';
$gen_date = date('F d, Y');
$gen_time = date('H:i');

// Embed photo as base64 so it survives printing
$photo_b64 = '';
$photo_path = '../uploads/photos/' . $user['photo'];
if (!empty($user['photo']) && $user['photo'] !== 'default.png' && file_exists($photo_path)) {
    $ext  = strtolower(pathinfo($user['photo'], PATHINFO_EXTENSION));
    $mime = in_array($ext, ['jpg','jpeg']) ? 'image/jpeg' : 'image/png';
    $photo_b64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($photo_path));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Transcript &mdash; <?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
/* ── RESET ─────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
  font-family: 'Open Sans', Arial, sans-serif;
  background: #dde1ea;
  color: #1a1d2e;
  font-size: 13px;
  line-height: 1.5;
}

/* ── ACTION BAR (screen only) ───────────────────── */
.action-bar {
  background: #1a1d2e;
  padding: 11px 28px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  position: sticky;
  top: 0;
  z-index: 200;
}
.action-bar .doc-info { color: #94a3b8; font-size: 0.78rem; }
.action-bar .doc-info strong { color: #fff; }
.btn-print {
  background: #ffbf00; color: #000; border: none;
  border-radius: 8px; padding: 9px 22px;
  font-family: 'Open Sans', sans-serif; font-size: 0.82rem; font-weight: 700;
  cursor: pointer; display: inline-flex; align-items: center; gap: 8px;
  transition: background 0.18s;
}
.btn-print:hover { background: #ffe642; }
.btn-back {
  background: transparent; color: #94a3b8;
  border: 1px solid #2d3748; border-radius: 8px;
  padding: 9px 16px; font-family: 'Open Sans', sans-serif; font-size: 0.82rem;
  cursor: pointer; margin-right: 8px; transition: all 0.18s;
}
.btn-back:hover { color: #fff; border-color: #4a5568; }

/* ── A4 SHEET ───────────────────────────────────── */
.page-wrap {
  width: 210mm;
  margin: 28px auto 40px;
  background: #fff;
  box-shadow: 0 8px 48px rgba(0,0,0,0.2);
}
.stripe-top    { height: 7px; background: linear-gradient(90deg, #ffbf00, #ff7900 60%, #ffe642); }
.stripe-bottom { height: 4px; background: linear-gradient(90deg, #ff7900, #ffbf00 50%, #ffe642); }
.page-inner { padding: 28px 32px 36px; }

/* ── LETTERHEAD ─────────────────────────────────── */
.letterhead {
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
      padding-bottom: 12px;
      border-bottom: 2.5px solid #1a1d2e;
      margin-bottom: 18px;
    }
.lh-name  { font-size: 1.4rem; font-weight: 800; color: #1a1d2e; letter-spacing: -0.02em; }
.lh-sub   { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.1em; color: #94a3b8; margin-top: 2px; }
.lh-right { text-align: right; }
.lh-doc   { font-size: 0.95rem; font-weight: 800; color: #ffbf00; text-transform: uppercase; letter-spacing: 0.07em; }
.lh-date  { font-size: 0.68rem; color: #94a3b8; margin-top: 2px; }
.lh-title {
      font-size: 0.95rem;
      font-weight: 800;
      color: #000000;
      text-transform: uppercase;
      letter-spacing: 0.07em;
    }
.lh-sub-title {
      font-size: 0.8rem;
      font-weight: 600;
      color: #1a1d2e;
      letter-spacing: -0.02em;
    }

/* ── STUDENT BLOCK ──────────────────────────────── */
.student-block {
  display: flex;
  gap: 18px;
  align-items: flex-start;
  background: #f7f8fc;
  border: 1px solid #e2e6f0;
  border-left: 5px solid #ffbf00;
  border-radius: 0 10px 10px 0;
  padding: 16px 20px;
  margin-bottom: 18px;
}
.stu-photo {
  width: 70px; height: 70px;
  border-radius: 50%; object-fit: cover;
  border: 3px solid #ffbf00; flex-shrink: 0;
}
.stu-photo-placeholder {
  width: 70px; height: 70px;
  border-radius: 50%; background: #e8eaf0;
  border: 3px solid #ffbf00; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 1.8rem; color: #b0b8cc;
}
.stu-info { flex: 1; min-width: 0; }
.stu-name { font-size: 1.12rem; font-weight: 800; color: #1a1d2e; }
.stu-reg  { font-family: 'Courier New', monospace; font-size: 0.72rem; color: #94a3b8; margin: 3px 0 10px; }
.stu-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 5px 20px;
}
.stu-grid .sf label { font-size: 0.58rem; text-transform: uppercase; letter-spacing: 0.07em; color: #b0b8cc; display: block; }
.stu-grid .sf span  { font-size: 0.8rem; font-weight: 600; color: #1a1d2e; }

/* ── SUMMARY ROW ────────────────────────────────── */
.summary-row {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
  margin-bottom: 20px;
}
.sum-box { border: 1px solid #e2e6f0; border-radius: 8px; overflow: hidden; text-align: center; }
.sum-accent { height: 4px; }
.sum-body { padding: 10px 8px 12px; }
.sum-val { font-size: 1.25rem; font-weight: 800; color: #1a1d2e; line-height: 1; }
.sum-lbl { font-size: 0.58rem; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; margin-top: 4px; }

/* ── SECTION LABEL ──────────────────────────────── */
.sec-label {
  font-size: 0.62rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: 0.1em; color: #94a3b8;
  display: flex; align-items: center; gap: 8px; margin-bottom: 8px;
}
.sec-label::after { content: ''; flex: 1; height: 1px; background: #e2e6f0; }

/* ── RESULTS TABLE ──────────────────────────────── */
table.rt { width: 100%; border-collapse: collapse; font-size: 0.8rem; margin-bottom: 16px; }
table.rt thead tr { background: #1a1d2e; color: #fff; }
table.rt thead th {
  padding: 8px 10px;
  font-size: 0.63rem; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 700;
  text-align: center; white-space: nowrap;
}
table.rt thead th.al { text-align: left; }
table.rt tbody tr:nth-child(odd)  { background: #fff; }
table.rt tbody tr:nth-child(even) { background: #f7f8fc; }
table.rt tbody td { padding: 7px 10px; border-bottom: 1px solid #e8eaf0; text-align: center; vertical-align: middle; }
table.rt tbody td.al { text-align: left; }
table.rt tbody tr:last-child td { border-bottom: 2px solid #1a1d2e; }
table.rt tfoot tr { background: #fffbea; }
table.rt tfoot td { padding: 8px 10px; font-weight: 800; border-top: 2px solid #ffbf00; text-align: center; }
table.rt tfoot td.al { text-align: right; }

.code-chip {
  font-family: 'Courier New', monospace; font-size: 0.78rem;
  background: #f0f2f8; padding: 2px 8px; border-radius: 5px; display: inline-block;
}
.sn   { color: #b0b8cc; font-size: 0.7rem; }
.rmk  { color: #94a3b8; font-style: italic; font-size: 0.73rem; }

/* Grade colours */
.gA { color: #15803d; font-weight: 800; }
.gB { color: #1d4ed8; font-weight: 800; }
.gC { color: #b45309; font-weight: 800; }
.gD { color: #dc2626; font-weight: 800; }
.gF { color: #7f1d1d; font-weight: 800; }

/* ── GRADE KEY ──────────────────────────────────── */
.gkey { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 18px; }
.gkey span {
  background: #f7f8fc; border: 1px solid #e2e6f0;
  border-radius: 6px; padding: 4px 10px; font-size: 0.7rem;
}

/* ── DECLARATION ────────────────────────────────── */
.declaration {
  background: #f7f8fc; border: 1px solid #e2e6f0; border-radius: 8px;
  padding: 12px 16px; font-size: 0.72rem; color: #7a8299; font-style: italic;
  line-height: 1.7; margin-bottom: 20px;
}

/* ── SIGNATURES ─────────────────────────────────── */
.sig-row { display: flex; justify-content: space-between; gap: 14px; margin-bottom: 22px; }
.sig-box { flex: 1; text-align: center; }
.sig-box .sig-space { height: 36px; border-bottom: 1px solid #b0b8cc; margin-bottom: 5px; }
.sig-box .sig-lbl { font-size: 0.63rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; }

/* ── DOCUMENT FOOTER ────────────────────────────── */
.doc-footer {
  display: flex; justify-content: space-between; align-items: center;
  padding-top: 12px; border-top: 1px solid #e2e6f0;
  font-size: 0.63rem; color: #b0b8cc; line-height: 1.7;
}
.doc-stamp {
  width: 62px; height: 62px; border-radius: 50%;
  border: 2px dashed #d1d5e0; flex-shrink: 0;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.5rem; font-weight: 800; text-transform: uppercase;
  letter-spacing: 0.05em; color: #c8cdd8; text-align: center; line-height: 1.4;
}

/* ── PRINT ──────────────────────────────────────── */
@media print {
  @page { size: A4 portrait; margin: 0; }
  html, body {
    background: #fff !important;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  .action-bar { display: none !important; }
  .page-wrap  { width: 100%; margin: 0; box-shadow: none; }
}
</style>
</head>
<body>

<!-- ACTION BAR -->
<div class="action-bar">
  <div class="doc-info">
    <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
    &nbsp;&bull;&nbsp; <?= htmlspecialchars($user['registration_no']) ?>
    &nbsp;&bull;&nbsp; Academic Transcript
  </div>
  <div>
    <button class="btn-back" onclick="window.close()">&#x2715; Close</button>
    <button class="btn-print" onclick="window.print()">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 6 2 18 2 18 9"/>
        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
        <rect x="6" y="14" width="12" height="8"/>
      </svg>
      Print / Save as PDF
    </button>
  </div>
</div>

<!-- A4 PAGE -->
<div class="page-wrap">
  <div class="stripe-top"></div>
  <div class="page-inner">

    <!-- LETTERHEAD -->
    <div class="letterhead">
      <div>
        <img src="../assets/img/logo.png" width="85px">
          <div class="lh-title">Kilimanjaro International Institute for Telecommunications, Electronics and Computers</div>
          <div class="lh-sub-title">P.O. Box 3172 Arusha | Tel: +255757 845 118</div>
          <div class="lh-sub-title">Email: info@kiitec.ac.tz | Website: www.kiitec.ac.tz</div>
          <br>
          <div class="lh-title">STUDENT ACADEMIC REPORT</div>
      </div>
    </div>

    <!-- STUDENT -->
    <div class="student-block">
      <?php if ($photo_b64): ?>
        <img src="<?= $photo_b64 ?>" class="stu-photo" alt="Photo">
      <?php else: ?>
        <div class="stu-photo-placeholder">&#128100;</div>
      <?php endif; ?>
      <div class="stu-info">
        <div class="stu-name"><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></div>
        <div class="stu-reg">REG. NO: <?= htmlspecialchars($user['registration_no']) ?></div>
        <div class="stu-grid">
          <div class="sf"><label>Course</label><span><?= htmlspecialchars($user['course']) ?></span></div>
          <div class="sf"><label>Level</label><span><?= $course_type_label ?></span></div>
          <div class="sf"><label>Date of Birth</label><span><?= $user['date_of_birth'] ? date('M d, Y', strtotime($user['date_of_birth'])) : 'N/A' ?></span></div>
          <div class="sf"><label>Email</label><span><?= htmlspecialchars($user['email']) ?></span></div>
          <div class="sf"><label>Phone</label><span><?= htmlspecialchars($user['phone'] ?: 'N/A') ?></span></div>
          <div class="sf"><label>Date Issued</label><span><?= $gen_date ?></span></div>
        </div>
      </div>
    </div>

    <!-- RESULTS TABLE -->
    <div class="sec-label">Subject Results</div>
    <table class="rt">
      <thead>
        <tr>
          <th style="width:24px;">#</th>
          <th class="al" style="width:80px;">Code</th>
          <th class="al">Subject Name</th>
          <th style="width:38px;">CA <span style="font-weight:400;opacity:0.6;">(60)</span></th>
          <th style="width:38px;">SE <span style="font-weight:400;opacity:0.6;">(40)</span></th>
          <th style="width:52px;">Total <span style="font-weight:400;opacity:0.6;">(100)</span></th>
          <th style="width:36px;">Grade</th>
          <th style="width:60px;">Remarks</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($results as $i => $r):
        $g  = strtoupper(trim($r['grade']));
        $gc = match($g) { 'A'=>'gA','B'=>'gB','C'=>'gC','D'=>'gD', default=>'gF' };
        $pct = min(100, max(0, (float)$r['total_score']));
        $has_name = isset($r['resolved_name']) && $r['resolved_name'] !== '';
      ?>
        <tr>
          <td class="sn"><?= $i + 1 ?></td>
          <td class="al"><span class="code-chip"><?= htmlspecialchars($r['subject_code']) ?></span></td>
          <td class="al">
            <?php if ($has_name): ?>
              <span style="font-size:0.8rem;font-weight:600;color:#1a1d2e;"><?= htmlspecialchars($r['resolved_name']) ?></span>
            <?php else: ?>
              <span style="color:#b0b8cc;font-style:italic;font-size:0.75rem;">Not specified</span>
            <?php endif; ?>
          </td>
          <td><?= number_format($r['ca_score'], 0) ?></td>
          <td><?= number_format($r['se_score'], 0) ?></td>
          <td><strong><?= number_format($r['total_score'], 0) ?></strong></td>
          <td><span class="<?= $gc ?>"><?= $g ?></span></td>
          <td class="rmk"><?= $remarks_map[$g] ?? '' ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td class="al" colspan="5" style="font-size:0.7rem;color:#94a3b8;font-weight:600;letter-spacing:.04em;">GPA</td>
          <td style="color:#ff7900;"><?= $gpa ?>/4</td>
          <td colspan="2"></td>
        </tr>
      </tfoot>
    </table>

    <!-- GRADE KEY -->
    <div class="sec-label">Grading Key</div>
    <div class="gkey">
      <span><strong class="gA">A</strong> &nbsp;80–100&nbsp; Excellent</span>
      <span><strong class="gB">B</strong> &nbsp;65–79&nbsp; Very Good</span>
      <span><strong class="gC">C</strong> &nbsp;50–64&nbsp; Good</span>
      <span><strong class="gD">D</strong> &nbsp;40–49&nbsp; Pass</span>
      <span><strong class="gF">F</strong> &nbsp;0–39&nbsp;&nbsp; Fail</span>
    </div>

    <!-- DECLARATION -->
    <div class="declaration">
      This is to certify that the above results are a true and accurate record of the academic performance of
      <strong><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></strong>
      (<?= htmlspecialchars($user['registration_no']) ?>) as recorded in the official academic registry of DB-KIITEC.
      This transcript is issued on <?= $gen_date ?> and is valid only when accompanied by an official institutional stamp.
    </div>

    <!-- SIGNATURES -->
    <div class="sec-label">Authorisation</div>
    <div class="sig-row">
      <div class="sig-box">
        <div class="sig-space"></div>
        <div class="sig-lbl">Student's Signature</div>
      </div>
      <div class="sig-box">
        <div class="sig-space"></div>
        <div class="sig-lbl">Date</div>
      </div>
      <div class="sig-box">
        <div class="sig-space"></div>
        <div class="sig-lbl">Head of Department</div>
      </div>
      <div class="sig-box">
        <div class="sig-space"></div>
        <div class="sig-lbl">Registrar &amp; Stamp</div>
      </div>
    </div>

    <!-- FOOTER -->
    <div class="doc-footer">
      <div>
        <div><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?> &bull; <?= htmlspecialchars($user['registration_no']) ?> &bull; <?= htmlspecialchars($user['course']) ?></div>
        <div>Generated <?= $gen_date ?> at <?= $gen_time ?> &bull; DB-KIITEC Academic Records System</div>
        <div>This document is computer-generated. Verify authenticity with the college registrar.</div>
      </div>
      <div class="doc-stamp">Official<br>Stamp</div>
    </div>

  </div>
  <div class="stripe-bottom"></div>
</div>

</body>
</html>
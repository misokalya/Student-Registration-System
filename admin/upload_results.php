<?php
require_once '../includes/config.php';
requireAdmin();

$success = $error = '';
$preview = [];
$import_summary = null;

// Handle CSV import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_import'])) {
    $batch_id = intval($_POST['batch_id']);
    $rows     = json_decode($_POST['rows_json'], true);
    $filename = sanitize($conn, $_POST['filename']);

    $imported = 0; $skipped = 0;
    foreach ($rows as $row) {
        $reg      = sanitize($conn, $row['registration_no']);
        $code     = sanitize($conn, $row['subject_code']);
        $sname    = sanitize($conn, $row['subject_name'] ?? '');
        $ca       = floatval($row['CA']);
        $se       = floatval($row['SE']);
        $total    = floatval($row['Total']);
        $grade    = sanitize($conn, $row['Grade']);

        // Upsert subject name into subjects table if provided
        if ($sname !== '') {
            $ss = $conn->prepare("INSERT INTO subjects (subject_code, subject_name)
                VALUES (?,?) ON DUPLICATE KEY UPDATE subject_name=VALUES(subject_name)");
            $ss->bind_param("ss", $code, $sname);
            $ss->execute();
        }

        $stmt = $conn->prepare("INSERT INTO results (registration_no, subject_code, subject_name, ca_score, se_score, total_score, grade, upload_batch)
            VALUES (?,?,?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE subject_name=IF(VALUES(subject_name)!='',VALUES(subject_name),subject_name),
            ca_score=VALUES(ca_score), se_score=VALUES(se_score),
            total_score=VALUES(total_score), grade=VALUES(grade)");
        $stmt->bind_param("sssdddsi", $reg, $code, $sname, $ca, $se, $total, $grade, $batch_id);
        if ($stmt->execute()) { $imported++; } else { $skipped++; }
    }

    // Log the upload
    $admin_id = $_SESSION['user_id'];
    $total_rows = count($rows);
    $conn->query("INSERT INTO result_uploads (batch_id, filename, total_rows, imported_rows, skipped_rows, uploaded_by)
        VALUES ($batch_id, '$filename', $total_rows, $imported, $skipped, $admin_id)");

    $import_summary = compact('imported', 'skipped', 'total_rows', 'filename');
}

// Handle file upload & preview
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    $file = $_FILES['csv_file'];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $error = "File upload error. Please try again.";
    } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'csv') {
        $error = "Only CSV files are accepted.";
    } else {
        $handle = fopen($file['tmp_name'], 'r');
        $headers = fgetcsv($handle); // skip header row
        // Normalise headers
        $headers = array_map('trim', $headers);
        $required = ['registration_no','subject_code','CA','SE','Total','Grade'];
        $missing = array_diff($required, $headers);
        if (!empty($missing)) {
            $error = "CSV is missing columns: " . implode(', ', $missing);
        } else {
            while (($line = fgetcsv($handle)) !== false) {
                if (count($line) < count($headers)) continue;
                $row = array_combine($headers, array_map('trim', $line));
                $preview[] = $row;
            }
            fclose($handle);
            if (empty($preview)) {
                $error = "CSV file is empty or has no data rows.";
            }
        }
    }
}

// Fetch past uploads
$uploads = $conn->query("SELECT ru.*, u.first_name, u.last_name FROM result_uploads ru
    LEFT JOIN users u ON ru.uploaded_by = u.id
    ORDER BY ru.uploaded_at DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Upload Results — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
.grade-A { color:#16a34a; font-weight:700; }
.grade-B { color:#2563eb; font-weight:700; }
.grade-C { color:#d97706; font-weight:700; }
.grade-D { color:#dc2626; font-weight:700; }
.grade-F { color:#991b1b; font-weight:700; }
.upload-zone {
  border: 2px dashed var(--border);
  border-radius: 14px;
  padding: 40px;
  text-align: center;
  transition: all 0.2s;
  cursor: pointer;
  background: var(--surface2);
}
.upload-zone:hover, .upload-zone.drag { border-color: var(--gold); background: rgba(255,191,0,0.04); }
.upload-zone i { font-size: 2.5rem; color: var(--gold); margin-bottom: 12px; display: block; }
.upload-zone p { color: var(--muted); font-size: 0.85rem; }
</style>
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Upload <span>Student Results</span></div>
      <div class="topbar-right">
        <a href="dashboard.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Dashboard</a>
      </div>
    </div>

    <div class="page-content">
      <div class="page-header">
        <h2>Upload Results CSV</h2>
        <p>Import student results from a CSV file. Required columns: <code>registration_no, subject_code, CA, SE, Total, Grade</code> &mdash; Optional: <code>subject_name</code></p>
      </div>

      <?php if ($import_summary): ?>
        <div class="alert alert-success">
          <i class="fas fa-circle-check"></i>
          <div>
            <strong>Import complete!</strong> File: <em><?= htmlspecialchars($import_summary['filename']) ?></em><br>
            <?= $import_summary['imported'] ?> records imported &nbsp;·&nbsp;
            <?= $import_summary['skipped'] ?> skipped
          </div>
        </div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= $error ?></div>
      <?php endif; ?>

      <!-- UPLOAD FORM -->
      <?php if (empty($preview) && !$import_summary): ?>
      <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
          <h3><i class="fas fa-file-csv" style="color:var(--gold);margin-right:8px;"></i>Select CSV File</h3>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data" id="uploadForm">
            <label class="upload-zone" for="csvFileInput" id="uploadZone">
              <i class="fas fa-cloud-arrow-up"></i>
              <strong style="font-size:1rem;display:block;margin-bottom:6px;">Click to browse or drag & drop</strong>
              <p>Accepts .csv files only &nbsp;·&nbsp; Max 5MB</p>
              <p style="margin-top:8px;font-size:0.75rem;color:var(--gold);" id="fileNameDisplay">No file selected</p>
              <input type="file" name="csv_file" id="csvFileInput" accept=".csv" style="display:none;" required>
            </label>
            <div style="margin-top:16px;text-align:right;">
              <button type="submit" class="btn btn-primary"><i class="fas fa-eye"></i> Preview Import</button>
            </div>
          </form>
        </div>
      </div>

      <!-- CSV FORMAT GUIDE -->
      <div class="card" style="margin-bottom:24px;">
        <div class="card-header">
          <h3><i class="fas fa-circle-info" style="color:var(--blue,#3b82f6);margin-right:8px;"></i>CSV Format Guide</h3>
        </div>
        <div class="card-body">
          <p style="margin-bottom:12px;color:var(--muted);font-size:0.83rem;">Your CSV must have a header row with exactly these column names:</p>
          <div class="table-wrap">
            <table>
              <thead><tr><th>Column</th><th>Description</th><th>Example</th></tr></thead>
              <tbody>
                <tr><td><code>registration_no</code></td><td>Student registration number (must match system)</td><td>KEC-009</td></tr>
                <tr><td><code>subject_code</code></td><td>Subject / module code</td><td>ECT04201</td></tr>
                <tr><td><code>CA</code></td><td>Continuous Assessment score</td><td>35</td></tr>
                <tr><td><code>SE</code></td><td>Sit-in Exam score</td><td>30</td></tr>
                <tr><td><code>Total</code></td><td>Total score (CA + SE)</td><td>65</td></tr>
                <tr><td><code>Grade</code></td><td>Grade letter</td><td>C</td></tr>
                <tr style="background:rgba(255,191,0,0.05);"><td><code>subject_name</code> <span style="color:var(--gold);font-size:0.7rem;">optional</span></td><td>Full subject name — saved to subjects table</td><td>Digital Electronics</td></tr>
              </tbody>
            </table>
          </div>
          <p style="margin-top:10px;font-size:0.78rem;color:var(--muted);">Optional columns: <code>first_name</code>, <code>last_name</code> (ignored), and <code>subject_name</code> (stored if provided). Student data is matched by <code>registration_no</code>.</p>
        </div>
      </div>

      <?php endif; ?>

      <!-- PREVIEW TABLE -->
      <?php if (!empty($preview)): 
        $batch_id = time();
        $grouped = [];
        foreach ($preview as $row) { $grouped[$row['registration_no']][] = $row; }
      ?>
      <div class="alert alert-warning">
        <i class="fas fa-triangle-exclamation"></i>
        <div><strong>Review before importing.</strong> <?= count($preview) ?> result rows found for <?= count($grouped) ?> student(s). Existing records for the same subject will be overwritten.</div>
      </div>

      <form method="POST">
        <input type="hidden" name="confirm_import" value="1">
        <input type="hidden" name="batch_id" value="<?= $batch_id ?>">
        <input type="hidden" name="filename" value="<?= htmlspecialchars($_FILES['csv_file']['name'] ?? 'upload.csv') ?>">
        <input type="hidden" name="rows_json" value="<?= htmlspecialchars(json_encode($preview)) ?>">

        <?php foreach ($grouped as $reg => $rows): 
          // Try to find matching student
          $s_stmt = $conn->prepare("SELECT first_name, last_name, course FROM users WHERE registration_no=?");
          $s_stmt->bind_param("s", $reg);
          $s_stmt->execute();
          $student = $s_stmt->get_result()->fetch_assoc();
          $matched = $student !== null;
        ?>
        <div class="card" style="margin-bottom:16px;">
          <div class="card-header">
            <div>
              <strong><?= htmlspecialchars($reg) ?></strong>
              <?php if ($matched): ?>
                <span style="color:var(--muted);margin-left:8px;font-size:0.82rem;">
                  <?= htmlspecialchars($student['first_name'].' '.$student['last_name']) ?>
                  &nbsp;·&nbsp; <?= htmlspecialchars($student['course']) ?>
                </span>
                <span class="badge badge-gold" style="margin-left:8px;"><i class="fas fa-check"></i> Matched</span>
              <?php else: ?>
                <span class="badge badge-orange" style="margin-left:8px;"><i class="fas fa-triangle-exclamation"></i> No matching student found</span>
              <?php endif; ?>
            </div>
            <span class="badge badge-yellow"><?= count($rows) ?> subjects</span>
          </div>
          <div class="card-body" style="padding:0;">
            <div class="table-wrap">
              <table>
                <thead><tr><th>Subject Code</th><th>Subject Name</th><th>CA</th><th>SE</th><th>Total</th><th>Grade</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $r): ?>
                  <tr>
                    <td><code><?= htmlspecialchars($r['subject_code']) ?></code></td>
                    <td style="color:var(--muted);font-size:0.8rem;"><?= !empty($r['subject_name']) ? htmlspecialchars($r['subject_name']) : '<em style="color:var(--muted);">—</em>' ?></td>
                    <td><?= $r['CA'] ?></td>
                    <td><?= $r['SE'] ?></td>
                    <td><strong><?= $r['Total'] ?></strong></td>
                    <td><span class="grade-<?= $r['Grade'] ?>"><?= $r['Grade'] ?></span></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <?php endforeach; ?>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-bottom:32px;">
          <a href="upload_results.php" class="btn btn-secondary"><i class="fas fa-xmark"></i> Cancel</a>
          <button type="submit" class="btn btn-primary"><i class="fas fa-database"></i> Confirm & Import <?= count($preview) ?> Records</button>
        </div>
      </form>
      <?php endif; ?>

      <!-- UPLOAD HISTORY -->
      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-clock-rotate-left" style="color:var(--gold-lt);margin-right:8px;"></i>Upload History</h3>
        </div>
        <div class="card-body" style="padding:0;">
          <div class="table-wrap">
            <table>
              <thead><tr><th>File</th><th>Total Rows</th><th>Imported</th><th>Skipped</th><th>Uploaded By</th><th>Date</th></tr></thead>
              <tbody>
              <?php while($u = $uploads->fetch_assoc()): ?>
                <tr>
                  <td><i class="fas fa-file-csv" style="color:var(--gold);margin-right:6px;"></i><?= htmlspecialchars($u['filename']) ?></td>
                  <td><?= $u['total_rows'] ?></td>
                  <td><span class="badge badge-gold"><?= $u['imported_rows'] ?></span></td>
                  <td><?= $u['skipped_rows'] > 0 ? '<span class="badge badge-orange">'.$u['skipped_rows'].'</span>' : '—' ?></td>
                  <td><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></td>
                  <td style="color:var(--muted);font-size:0.78rem;"><?= date('M d, Y H:i', strtotime($u['uploaded_at'])) ?></td>
                </tr>
              <?php endwhile; ?>
              <?php if ($uploads->num_rows === 0): ?>
                <tr><td colspan="6" style="text-align:center;color:var(--muted);padding:24px;">No uploads yet.</td></tr>
              <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>

<script>
const input = document.getElementById('csvFileInput');
const display = document.getElementById('fileNameDisplay');
if (input) {
  input.addEventListener('change', function() {
    display.textContent = this.files[0] ? this.files[0].name : 'No file selected';
  });
}
const zone = document.getElementById('uploadZone');
if (zone) {
  zone.addEventListener('dragover', e => { e.preventDefault(); zone.classList.add('drag'); });
  zone.addEventListener('dragleave', () => zone.classList.remove('drag'));
  zone.addEventListener('drop', e => {
    e.preventDefault(); zone.classList.remove('drag');
    if (e.dataTransfer.files[0]) {
      input.files = e.dataTransfer.files;
      display.textContent = e.dataTransfer.files[0].name;
    }
  });
}
</script>
</body>
</html>

<?php
require_once '../includes/config.php';
requireAdmin();

$success = $error = '';

// Handle add / edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code  = strtoupper(trim(sanitize($conn, $_POST['subject_code'])));
    $name  = sanitize($conn, $_POST['subject_name']);
    $edit_id = intval($_POST['edit_id'] ?? 0);

    if ($code === '' || $name === '') {
        $error = "Both Subject Code and Subject Name are required.";
    } elseif ($edit_id > 0) {
        $stmt = $conn->prepare("UPDATE subjects SET subject_code=?, subject_name=? WHERE id=?");
        $stmt->bind_param("ssi", $code, $name, $edit_id);
        $stmt->execute() ? $success = "Subject updated." : $error = "Update failed: " . $conn->error;
    } else {
        $stmt = $conn->prepare("INSERT INTO subjects (subject_code, subject_name) VALUES (?,?)
            ON DUPLICATE KEY UPDATE subject_name=VALUES(subject_name)");
        $stmt->bind_param("ss", $code, $name);
        $stmt->execute() ? $success = "Subject <strong>$code</strong> saved." : $error = "Error: " . $conn->error;
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $del = intval($_GET['delete']);
    $conn->query("DELETE FROM subjects WHERE id=$del");
    header("Location: subjects.php"); exit;
}

// Fetch edit target
$edit_row = null;
if (isset($_GET['edit'])) {
    $eid = intval($_GET['edit']);
    $edit_row = $conn->query("SELECT * FROM subjects WHERE id=$eid")->fetch_assoc();
}

// Fetch all subjects with usage count
$subjects = $conn->query("
    SELECT s.*, COUNT(r.id) AS usage_count
    FROM subjects s
    LEFT JOIN results r ON r.subject_code = s.subject_code
    GROUP BY s.id
    ORDER BY s.subject_code
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Manage Subjects — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Manage <span>Subjects</span></div>
      <div class="topbar-right">
        <a href="upload_results.php" class="btn btn-secondary btn-sm"><i class="fas fa-upload"></i> Upload Results</a>
      </div>
    </div>

    <div class="page-content">
      <div class="page-header">
        <h2>Subject Directory</h2>
        <p>Map subject codes to full names. These names appear on results pages and transcripts. Names can also be imported automatically via CSV.</p>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= $success ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= $error ?></div>
      <?php endif; ?>

      <div style="display:grid;grid-template-columns:340px 1fr;gap:20px;align-items:start;">

        <!-- ADD / EDIT FORM -->
        <div class="card" style="position:sticky;top:80px;">
          <div class="card-header">
            <h3>
              <i class="fas fa-<?= $edit_row ? 'pen' : 'plus' ?>" style="color:var(--gold);margin-right:8px;"></i>
              <?= $edit_row ? 'Edit Subject' : 'Add Subject' ?>
            </h3>
            <?php if ($edit_row): ?>
              <a href="subjects.php" class="btn btn-secondary btn-sm"><i class="fas fa-xmark"></i> Cancel</a>
            <?php endif; ?>
          </div>
          <div class="card-body">
            <form method="POST">
              <input type="hidden" name="edit_id" value="<?= $edit_row['id'] ?? 0 ?>">
              <div style="display:flex;flex-direction:column;gap:14px;">
                <div class="form-group">
                  <label>Subject Code *</label>
                  <input type="text" name="subject_code" class="form-control"
                    placeholder="e.g. ECT04201" required
                    value="<?= htmlspecialchars($edit_row['subject_code'] ?? '') ?>"
                    style="font-family:monospace;text-transform:uppercase;">
                  <small style="color:var(--muted);font-size:0.72rem;">Must match exactly what's in the results CSV.</small>
                </div>
                <div class="form-group">
                  <label>Subject Name *</label>
                  <input type="text" name="subject_name" class="form-control"
                    placeholder="e.g. Digital Electronics"
                    required
                    value="<?= htmlspecialchars($edit_row['subject_name'] ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary">
                  <i class="fas fa-<?= $edit_row ? 'save' : 'plus' ?>"></i>
                  <?= $edit_row ? 'Save Changes' : 'Add Subject' ?>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- SUBJECTS TABLE -->
        <div class="card">
          <div class="card-header">
            <h3><i class="fas fa-list" style="color:var(--gold-lt);margin-right:8px;"></i>All Subjects</h3>
            <div style="display:flex;align-items:center;gap:10px;">
              <input type="text" id="subjectSearch" placeholder="Search..." class="form-control" style="width:180px;padding:6px 12px;font-size:0.82rem;">
              <span class="badge badge-gold"><?= $subjects->num_rows ?> total</span>
            </div>
          </div>
          <div class="card-body" style="padding:0;">
            <div class="table-wrap">
              <table id="subjectsTable">
                <thead>
                  <tr>
                    <th>Code</th>
                    <th>Subject Name</th>
                    <th style="text-align:center;">Used In</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                <?php if ($subjects->num_rows === 0): ?>
                  <tr>
                    <td colspan="4" style="text-align:center;padding:32px;color:var(--muted);">
                      <i class="fas fa-folder-open" style="font-size:1.8rem;display:block;margin-bottom:8px;"></i>
                      No subjects yet. Add one using the form, or upload a CSV with a <code>subject_name</code> column.
                    </td>
                  </tr>
                <?php endif; ?>
                <?php while ($s = $subjects->fetch_assoc()): ?>
                  <tr <?= ($edit_row && $edit_row['id'] == $s['id']) ? 'style="background:rgba(255,191,0,0.07);"' : '' ?>>
                    <td>
                      <code style="background:var(--surface2);padding:2px 9px;border-radius:6px;font-size:0.82rem;font-weight:700;">
                        <?= htmlspecialchars($s['subject_code']) ?>
                      </code>
                    </td>
                    <td style="font-weight:600;"><?= htmlspecialchars($s['subject_name']) ?></td>
                    <td style="text-align:center;">
                      <?php if ($s['usage_count'] > 0): ?>
                        <span class="badge badge-gold"><?= $s['usage_count'] ?> result<?= $s['usage_count'] != 1 ? 's' : '' ?></span>
                      <?php else: ?>
                        <span style="color:var(--muted);font-size:0.75rem;">unused</span>
                      <?php endif; ?>
                    </td>
                    <td>
                      <div style="display:flex;gap:6px;">
                        <a href="subjects.php?edit=<?= $s['id'] ?>" class="btn btn-secondary btn-sm btn-icon" title="Edit">
                          <i class="fas fa-pen"></i>
                        </a>
                        <a href="subjects.php?delete=<?= $s['id'] ?>"
                           class="btn btn-danger btn-sm btn-icon" title="Delete"
                           onclick="return confirm('Delete subject <?= htmlspecialchars(addslashes($s['subject_code'])) ?>? Results using this code will show code only.')">
                          <i class="fas fa-trash"></i>
                        </a>
                      </div>
                    </td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

      </div>

      <!-- INFO CARD -->
      <div class="card" style="margin-top:20px;">
        <div class="card-header">
          <h3><i class="fas fa-circle-info" style="color:var(--gold);margin-right:8px;"></i>How Subject Names Work</h3>
        </div>
        <div class="card-body">
          <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;">
            <div style="display:flex;gap:12px;">
              <div style="width:36px;height:36px;background:rgba(255,191,0,0.12);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-upload" style="color:var(--gold);"></i>
              </div>
              <div>
                <div style="font-weight:700;font-size:0.82rem;margin-bottom:2px;">Via CSV Upload</div>
                <div style="font-size:0.75rem;color:var(--muted);">Add a <code>subject_name</code> column to your CSV. Names are saved automatically on import.</div>
              </div>
            </div>
            <div style="display:flex;gap:12px;">
              <div style="width:36px;height:36px;background:rgba(255,121,0,0.12);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-keyboard" style="color:var(--orange);"></i>
              </div>
              <div>
                <div style="font-weight:700;font-size:0.82rem;margin-bottom:2px;">Manually Here</div>
                <div style="font-size:0.75rem;color:var(--muted);">Use the form on the left to add or edit subject codes and names at any time.</div>
              </div>
            </div>
            <div style="display:flex;gap:12px;">
              <div style="width:36px;height:36px;background:rgba(255,230,66,0.15);border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-file-pdf" style="color:var(--yellow);"></i>
              </div>
              <div>
                <div style="font-weight:700;font-size:0.82rem;margin-bottom:2px;">Shown Everywhere</div>
                <div style="font-size:0.75rem;color:var(--muted);">Names appear on the student results page, admin view, and PDF transcripts.</div>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
<script>
document.getElementById('subjectSearch').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('#subjectsTable tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
// Auto-uppercase subject code input
document.querySelector('input[name="subject_code"]').addEventListener('input', function() {
  const pos = this.selectionStart;
  this.value = this.value.toUpperCase();
  this.setSelectionRange(pos, pos);
});
</script>
</body>
</html>

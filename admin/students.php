<?php
require_once '../includes/config.php';
requireAdmin();

// Handle delete
if (isset($_GET['delete'])) {
    $del_id = intval($_GET['delete']);
    // Don't delete admin
    $stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='student'");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    header("Location: students.php" . (isset($_GET['type']) ? "?type=".$_GET['type'] : ""));
    exit;
}

// Handle edit
$edit_success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_id'])) {
    $id          = intval($_POST['edit_id']);
    $first_name  = sanitize($conn, $_POST['first_name']);
    $last_name   = sanitize($conn, $_POST['last_name']);
    $reg_no      = sanitize($conn, $_POST['registration_no']);
    $dob         = sanitize($conn, $_POST['date_of_birth']);
    $email       = sanitize($conn, $_POST['email']);
    $phone       = sanitize($conn, $_POST['phone']);
    $course      = sanitize($conn, $_POST['course']);
    $course_type = sanitize($conn, $_POST['course_type']);
    $year        = ($course_type === 'fulltime') ? intval($_POST['year_of_study']) : 0;

    // Handle photo update
    $photo_update = "";
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $photo = 'student_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/photos/' . $photo);
            $photo_update = ", photo='$photo'";
        }
    }

    $stmt = $conn->prepare("UPDATE users SET first_name=?,last_name=?,registration_no=?,date_of_birth=?,email=?,phone=?,course=?,course_type=?,year_of_study=?$photo_update WHERE id=?");
    $stmt->bind_param("sssssssssi", $first_name,$last_name,$reg_no,$dob,$email,$phone,$course,$course_type,$year,$id);
    $stmt->execute();
    $edit_success = "Student updated successfully.";
}

// Build query
$type_filter = '';
$where = "WHERE role='student'";
if (isset($_GET['type']) && in_array($_GET['type'], ['fulltime','shortcourse'])) {
    $type_filter = $_GET['type'];
    $t = $conn->real_escape_string($type_filter);
    $where .= " AND course_type='$t'";
}

$students = $conn->query("SELECT * FROM users $where ORDER BY course_type, course, last_name");
$active_tab = $type_filter ?: 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Student <span>Management</span></div>
      <div class="topbar-right">
        <a href="add_student.php" class="btn btn-primary btn-sm"><i class="fas fa-user-plus"></i> Add Student</a>
      </div>
    </div>

    <div class="page-content">
      <?php if ($edit_success): ?>
        <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= $edit_success ?></div>
      <?php endif; ?>

      <!-- TABS -->
      <div class="tab-bar">
        <a href="students.php" style="text-decoration:none;">
          <button class="tab <?= $active_tab==='all'?'active':'' ?>"><i class="fas fa-users"></i> All Students</button>
        </a>
        <a href="students.php?type=fulltime" style="text-decoration:none;">
          <button class="tab <?= $active_tab==='fulltime'?'active':'' ?>"><i class="fas fa-user-graduate"></i> Full-Time</button>
        </a>
        <a href="students.php?type=shortcourse" style="text-decoration:none;">
          <button class="tab <?= $active_tab==='shortcourse'?'active':'' ?>"><i class="fas fa-clock"></i> Short Course</button>
        </a>
      </div>

      <!-- Search bar -->
      <div style="margin-bottom:16px;">
        <div style="position:relative;max-width:320px;">
          <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--muted);"></i>
          <input type="text" id="searchInput" placeholder="Search students..." class="form-control" style="padding-left:36px;">
        </div>
      </div>

      <?php
      // Group by course type and course
      $grouped = [];
      while ($s = $students->fetch_assoc()) {
          $grouped[$s['course_type']][$s['course']][] = $s;
      }

      if (empty($grouped)):
      ?>
        <div class="card">
          <div class="card-body" style="text-align:center;padding:48px;color:var(--muted);">
            <i class="fas fa-user-slash" style="font-size:2.5rem;margin-bottom:12px;display:block;"></i>
            No students found.
          </div>
        </div>
      <?php else:
        foreach ($grouped as $ctype => $courses):
          $type_label = $ctype === 'fulltime' ? 'Full-Time' : 'Short Course';
          $badge_class = $ctype === 'fulltime' ? 'badge-gold' : 'badge-orange';
          $icon = $ctype === 'fulltime' ? 'fa-user-graduate' : 'fa-clock';
      ?>
        <div class="section-label">
          <i class="fas <?= $icon ?>"></i> <?= $type_label ?>
        </div>

        <?php foreach ($courses as $course_name => $students_list): ?>
          <div class="card" style="margin-bottom:16px;">
            <div class="card-header">
              <h3><i class="fas fa-book" style="color:var(--gold-lt);margin-right:8px;"></i><?= htmlspecialchars($course_name) ?></h3>
              <span class="badge <?= $badge_class ?>"><?= count($students_list) ?> student<?= count($students_list)!==1?'s':'' ?></span>
            </div>
            <div class="card-body" style="padding:0;">
              <div class="table-wrap">
                <table class="searchable-table">
                  <thead>
                    <tr>
                      <th>Student</th>
                      <th>Reg No.</th>
                      <th>Email</th>
                      <th>Phone</th>
                      <?php if($ctype==='fulltime'): ?><th>Year</th><?php endif; ?>
                      <th>D.O.B</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  <?php foreach ($students_list as $s):
                    $ph = file_exists("../uploads/photos/".$s['photo']) ? "../uploads/photos/".$s['photo'] : "../assets/img/default.png";
                  ?>
                    <tr>
                      <td>
                        <img src="<?= $ph ?>" class="student-avatar">
                        <strong><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></strong>
                      </td>
                      <td style="font-family:monospace;font-size:0.78rem;color:var(--muted);"><?= htmlspecialchars($s['registration_no']) ?></td>
                      <td><?= htmlspecialchars($s['email']) ?></td>
                      <td><?= htmlspecialchars($s['phone']) ?></td>
                      <?php if($ctype==='fulltime'): ?>
                        <td><span class="badge badge-yellow">Year <?= $s['year_of_study'] ?></span></td>
                      <?php endif; ?>
                      <td style="color:var(--muted);font-size:0.78rem;"><?= $s['date_of_birth'] ? date('M d, Y', strtotime($s['date_of_birth'])) : '—' ?></td>
                      <td>
                        <div style="display:flex;gap:6px;">
                          <button class="btn btn-secondary btn-sm btn-icon" title="Edit"
                            onclick='openEdit(<?= json_encode($s) ?>)'>
                            <i class="fas fa-pen"></i>
                          </button>
                          <a href="students.php?delete=<?= $s['id'] ?><?= $type_filter?"&type=$type_filter":"" ?>"
                             class="btn btn-danger btn-sm btn-icon" title="Delete"
                             onclick="return confirm('Delete <?= htmlspecialchars(addslashes($s['first_name'])) ?>? This cannot be undone.')">
                            <i class="fas fa-trash"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endforeach; endif; ?>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div class="modal-overlay" id="editModal">
  <div class="modal">
    <div class="modal-header">
      <h3><i class="fas fa-pen" style="color:var(--gold);margin-right:8px;"></i>Edit Student</h3>
      <button class="close-btn" onclick="closeEdit()"><i class="fas fa-xmark"></i></button>
    </div>
    <form method="POST" enctype="multipart/form-data">
      <div class="modal-body">
        <input type="hidden" name="edit_id" id="edit_id">

        <div style="text-align:center;margin-bottom:20px;">
          <img src="" id="editPhotoPreview" class="photo-preview">
          <div>
            <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
              <i class="fas fa-camera"></i> Change Photo
              <input type="file" name="photo" id="editPhotoInput" style="display:none;" accept="image/*">
            </label>
          </div>
        </div>

        <div class="form-grid" style="margin-bottom:14px;">
          <div class="form-group">
            <label>First Name</label>
            <input type="text" name="first_name" id="edit_first" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Last Name</label>
            <input type="text" name="last_name" id="edit_last" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Registration No.</label>
            <input type="text" name="registration_no" id="edit_reg" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Date of Birth</label>
            <input type="date" name="date_of_birth" id="edit_dob" class="form-control">
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" id="edit_email" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Phone</label>
            <input type="tel" name="phone" id="edit_phone" class="form-control">
          </div>
          <div class="form-group">
            <label>Course</label>
            <input type="text" name="course" id="edit_course" class="form-control" required>
          </div>
          <div class="form-group">
            <label>Course Type</label>
            <select name="course_type" id="edit_ctype" class="form-control" onchange="toggleEditYear()">
              <option value="fulltime">Full-Time</option>
              <option value="shortcourse">Short Course</option>
            </select>
          </div>
          <div class="form-group" id="editYearGroup">
            <label>Year of Study</label>
            <select name="year_of_study" id="edit_year" class="form-control">
              <option value="1">Year 1</option>
              <option value="2">Year 2</option>
              <option value="3">Year 3</option>
            </select>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="closeEdit()">Cancel</button>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(s) {
  document.getElementById('edit_id').value    = s.id;
  document.getElementById('edit_first').value = s.first_name;
  document.getElementById('edit_last').value  = s.last_name;
  document.getElementById('edit_reg').value   = s.registration_no;
  document.getElementById('edit_dob').value   = s.date_of_birth || '';
  document.getElementById('edit_email').value = s.email;
  document.getElementById('edit_phone').value = s.phone || '';
  document.getElementById('edit_course').value= s.course;
  document.getElementById('edit_ctype').value = s.course_type;
  document.getElementById('edit_year').value  = s.year_of_study || 1;

  const ph = s.photo && s.photo !== 'default.png'
    ? '../uploads/photos/' + s.photo
    : '../assets/img/default.png';
  document.getElementById('editPhotoPreview').src = ph;

  toggleEditYear();
  document.getElementById('editModal').classList.add('open');
}
function closeEdit() { document.getElementById('editModal').classList.remove('open'); }
function toggleEditYear() {
  document.getElementById('editYearGroup').style.display =
    document.getElementById('edit_ctype').value === 'fulltime' ? 'block' : 'none';
}

document.getElementById('editPhotoInput').addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('editPhotoPreview').src = e.target.result;
    reader.readAsDataURL(file);
  }
});

// Search
document.getElementById('searchInput').addEventListener('input', function() {
  const q = this.value.toLowerCase();
  document.querySelectorAll('.searchable-table tbody tr').forEach(row => {
    row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
  });
});
</script>
</body>
</html>

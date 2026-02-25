<?php
require_once '../includes/config.php';
requireAdmin();

$success = $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name      = sanitize($conn, $_POST['first_name']);
    $last_name       = sanitize($conn, $_POST['last_name']);
    $reg_no          = sanitize($conn, $_POST['registration_no']);
    $dob             = sanitize($conn, $_POST['date_of_birth']);
    $email           = sanitize($conn, $_POST['email']);
    $phone           = sanitize($conn, $_POST['phone']);
    $course          = sanitize($conn, $_POST['course']);
    $course_type     = sanitize($conn, $_POST['course_type']);
    $year_of_study   = ($course_type === 'fulltime') ? intval($_POST['year_of_study']) : 0;
    $default_pass    = password_hash('Student@123', PASSWORD_DEFAULT);

    // Handle photo upload
    $photo = 'default.png';
    if (!empty($_FILES['photo']['name'])) {
        $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','gif','webp'];
        if (in_array($ext, $allowed)) {
            $photo = 'student_' . time() . '_' . rand(1000,9999) . '.' . $ext;
            move_uploaded_file($_FILES['photo']['tmp_name'], '../uploads/photos/' . $photo);
        }
    }

    $stmt = $conn->prepare("INSERT INTO users (first_name,last_name,registration_no,date_of_birth,email,phone,photo,course,course_type,year_of_study,password,role,must_change_password) VALUES (?,?,?,?,?,?,?,?,?,?,?,'student',1)");
    $stmt->bind_param("sssssssssss", $first_name, $last_name, $reg_no, $dob, $email, $phone, $photo, $course, $course_type, $year_of_study, $default_pass);

    if ($stmt->execute()) {
        $success = "Student <strong>$first_name $last_name</strong> added successfully! Default password: <code>Student@123</code>";
    } else {
        if ($conn->errno == 1062) {
            $error = "A student with that Registration No. or Email already exists.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Student — Admin</title>
<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="wrapper">
  <?php include 'sidebar.php'; ?>
  <div class="main">
    <div class="topbar">
      <div class="topbar-title">Add <span>New Student</span></div>
      <div class="topbar-right">
        <a href="students.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
      </div>
    </div>

    <div class="page-content">
      <div class="page-header">
        <h2>Register New Student</h2>
        <p>Fill in all required fields. The student will use a default password on first login.</p>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><i class="fas fa-circle-check"></i> <?= $success ?></div>
      <?php endif; ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-circle-exclamation"></i> <?= $error ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="card-header">
          <h3><i class="fas fa-user-plus" style="color:var(--gold);margin-right:8px;"></i>Student Information</h3>
        </div>
        <div class="card-body">
          <form method="POST" enctype="multipart/form-data">

            <!-- Photo -->
            <div style="text-align:center;margin-bottom:24px;">
              <img src="../assets/img/default.png" id="photoPreview" class="photo-preview">
              <div>
                <label class="btn btn-secondary btn-sm" style="cursor:pointer;">
                  <i class="fas fa-camera"></i> Upload Photo
                  <input type="file" name="photo" id="photoInput" style="display:none;" accept="image/*">
                </label>
              </div>
            </div>

            <div class="section-label"><i class="fas fa-id-card"></i> Personal Details</div>
            <div class="form-grid" style="margin-bottom:16px;">
              <div class="form-group">
                <label>First Name *</label>
                <input type="text" name="first_name" class="form-control" required placeholder="John">
              </div>
              <div class="form-group">
                <label>Last Name *</label>
                <input type="text" name="last_name" class="form-control" required placeholder="Doe">
              </div>
              <div class="form-group">
                <label>Registration No. *</label>
                <input type="text" name="registration_no" class="form-control" required placeholder="e.g. STU-2024-001">
              </div>
              <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control">
              </div>
            </div>

            <div class="section-label"><i class="fas fa-address-book"></i> Contact Details</div>
            <div class="form-grid" style="margin-bottom:16px;">
              <div class="form-group">
                <label>Email Address *</label>
                <input type="email" name="email" class="form-control" required placeholder="student@email.com">
              </div>
              <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="0754000000">
              </div>
            </div>

            <div class="section-label"><i class="fas fa-book-open"></i> Course Details</div>
            <div class="form-grid" style="margin-bottom:16px;">
              <div class="form-group">
                <label>Course *</label>
                <input type="text" name="course" class="form-control" required placeholder="e.g. Computer Science">
              </div>
              <div class="form-group">
                <label>Course Type *</label>
                <select name="course_type" id="courseType" class="form-control" required>
                  <option value="">— Select Type —</option>
                  <option value="fulltime">Full-Time</option>
                  <option value="shortcourse">Short Course</option>
                </select>
              </div>
              <div class="form-group" id="yearGroup" style="display:none;">
                <label>Year of Study</label>
                <select name="year_of_study" class="form-control">
                  <option value="1">Year 1</option>
                  <option value="2">Year 2</option>
                  <option value="3">Year 3</option>
                </select>
              </div>
            </div>

            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:8px;">
              <a href="students.php" class="btn btn-secondary">Cancel</a>
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Student</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
document.getElementById('photoInput').addEventListener('change', function() {
  const file = this.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('photoPreview').src = e.target.result;
    reader.readAsDataURL(file);
  }
});

document.getElementById('courseType').addEventListener('change', function() {
  document.getElementById('yearGroup').style.display = this.value === 'fulltime' ? 'block' : 'none';
});
</script>
</body>
</html>

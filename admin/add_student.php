<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

require '../config/db.php';

/* Fetch courses */
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

$success = $error = "";

if (isset($_POST['save'])) {

    $fname   = trim($_POST['fname']);
    $lname   = trim($_POST['lname']);
    $gender  = $_POST['gender'];
    $course  = $_POST['course'];
    $reg_no  = trim($_POST['reg_no']);
    $dob     = $_POST['dob'];

    /* Check duplicate reg no */
    $check = $pdo->prepare("SELECT id FROM students WHERE reg_no=?");
    $check->execute([$reg_no]);

    if ($check->rowCount() > 0) {
        $error = "Registration number already exists!";
    } else {

        /* Upload photo */
        $photo = "default.png";
        if (!empty($_FILES['photo']['name'])) {
            $photo = time() . "_" . $_FILES['photo']['name'];
            move_uploaded_file(
                $_FILES['photo']['tmp_name'],
                "../assets/uploads/" . $photo
            );
        }

        /* Create login account for student
           Username = Reg No
           Password = Reg No (hashed) */
        $password = password_hash($reg_no, PASSWORD_DEFAULT);

        $pdo->prepare("
            INSERT INTO users (username, password, role)
            VALUES (?, ?, 'student')
        ")->execute([$reg_no, $password]);

        $user_id = $pdo->lastInsertId();

        /* Insert student profile */
        $pdo->prepare("
            INSERT INTO students
            (user_id, first_name, last_name, gender, course_id, reg_no, dob, photo)
            VALUES (?,?,?,?,?,?,?,?)
        ")->execute([
            $user_id,
            $fname,
            $lname,
            $gender,
            $course,
            $reg_no,
            $dob,
            $photo
        ]);

        $success = "Student added successfully!";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Add Student</title>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.form-box {
    max-width: 600px;
    background: #fff;
    padding: 30px;
    margin: 40px auto;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,.1);
}
.form-box h2 {
    text-align: center;
    margin-bottom: 20px;
}
.form-box input, .form-box select {
    width: 100%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.form-box button {
    width: 100%;
    padding: 12px;
    background: #4e54c8;
    color: white;
    border: none;
    border-radius: 6px;
    cursor: pointer;
}
.success {
    background: #e6ffed;
    color: #046c4e;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    text-align: center;
}
.error {
    background: #ffe5e5;
    color: #c00;
    padding: 10px;
    margin-bottom: 15px;
    border-radius: 6px;
    text-align: center;
}
</style>
</head>

<body>

<div class="form-box">
    <h2><i class="fa fa-user-plus"></i> Add Student</h2>

    <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

    <form method="POST" enctype="multipart/form-data">

        <input type="text" name="fname" placeholder="First Name" required>
        <input type="text" name="lname" placeholder="Last Name" required>

        <select name="gender" required>
            <option value="">Select Gender</option>
            <option>Male</option>
            <option>Female</option>
        </select>

        <select name="course" required>
            <option value="">Select Course</option>
            <?php foreach ($courses as $c): ?>
                <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['course_name']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="text" name="reg_no" placeholder="Registration Number" required>
        <input type="date" name="dob" required>

        <input type="file" name="photo" accept="image/*">

        <button type="submit" name="save">
            <i class="fa fa-save"></i> Save Student
        </button>
    </form>
</div>
<div align="center"><a href="dashboard.php">< Admin Dashboard</a></div>

</body>
</html>

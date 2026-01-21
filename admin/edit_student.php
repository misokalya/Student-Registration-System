<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

require '../config/db.php';

$id = $_GET['id'] ?? null;
if (!$id) die("Invalid request");

/* Fetch student */
$stmt = $pdo->prepare("SELECT * FROM students WHERE id=?");
$stmt->execute([$id]);
$student = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$student) die("Student not found");

/* Fetch courses */
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);

$success = "";

/* Update student */
if (isset($_POST['update'])) {

    $photo = $student['photo'];

    if (!empty($_FILES['photo']['name'])) {
        $photo = time() . "_" . $_FILES['photo']['name'];
        move_uploaded_file(
            $_FILES['photo']['tmp_name'],
            "../assets/uploads/" . $photo
        );
    }

    $pdo->prepare("
        UPDATE students SET
        first_name=?, last_name=?, gender=?, course_id=?, dob=?, photo=?
        WHERE id=?
    ")->execute([
        $_POST['fname'],
        $_POST['lname'],
        $_POST['gender'],
        $_POST['course'],
        $_POST['dob'],
        $photo,
        $id
    ]);

    $success = "Student updated successfully";
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .form-box {
            max-width: 500px;
            background: #fff;
            padding: 30px;
            margin: 10px auto;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1);
        }

        .form-box input,
        .form-box select {
            width: 95%;
            padding: 12px;
            margin-bottom: 15px;
        }

        button {
            background: #28a745;
            color: white;
            padding: 12px;
            border: none;
            width: 99%;
            border-radius: 6px;
        }

        .back {
            text-align: center;
            margin-top: 15px;
        }

        .back a {
            text-decoration: none;
            color: #4e54c8;
        }

        .success {
            background: #e6ffed;
            color: #046c4e;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 6px;
            text-align: center;
        }
    </style>
</head>

<body>

    <div class="form-box">
        <h2><i class="fa fa-user-edit"></i> Edit Student</h2>

        <?php if ($success): ?>
            <div class="success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">

            <input type="text" name="fname" value="<?= $student['first_name'] ?>" required>
            <input type="text" name="lname" value="<?= $student['last_name'] ?>" required>

            <select name="gender">
                <option <?= $student['gender'] == "Male" ? "selected" : "" ?>>Male</option>
                <option <?= $student['gender'] == "Female" ? "selected" : "" ?>>Female</option>
            </select>

            <select name="course">
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>"
                        <?= $student['course_id'] == $c['id'] ? "selected" : "" ?>>
                        <?= $c['course_name'] ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="date" name="dob" value="<?= $student['dob'] ?>">

            <p>Current Photo:</p>
            <img src="../assets/uploads/<?= $student['photo'] ?>" width="80">

            <input type="file" name="photo">

            <button type="submit" name="update">
                <i class="fa fa-save"></i> Update Student
            </button>
        </form>
    </div>
    <div class="back">
        <a href="dashboard.php">
            <i class="fa fa-arrow-left"></i> Back to Dashboard
        </a>
    </div>

</body>

</html>
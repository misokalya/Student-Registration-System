<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

require '../config/db.php';

/* Fetch all courses */
$courses = $pdo->query("SELECT * FROM courses")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>View Students</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .content {
            margin-left: 260px;
            padding: 30px;
        }

        .course-card {
            background: #fff;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .08);
        }

        .course-card h2 {
            margin-bottom: 15px;
            color: #4e54c8;
        }

        .course-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .course-card th {
            background: #4e54c8;
            color: white;
            padding: 10px;
        }

        .course-card td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }

        .course-card tr:hover {
            background: #f4f6f9;
        }

        .action a {
            text-decoration: none;
            padding: 6px 10px;
            border-radius: 5px;
            color: white;
            font-size: 0.9rem;
        }

        .edit {
            background: #28a745;
        }

        .delete {
            background: #dc3545;
        }

        img.photo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2><i class="fa fa-user-shield"></i> Admin</h2>
        <a href="dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
        <a href="add_student.php"><i class="fa fa-user-plus"></i> Add Student</a>
        <a href="view_students.php" class="active">
            <i class="fa fa-users"></i> View Students
        </a>
        <a href="../logout.php"><i class="fa fa-sign-out"></i> Logout</a>
    </div>

    <!-- CONTENT -->
    <div class="content">

        <h1><i class="fa fa-users"></i> Students by Course</h1>

        <?php foreach ($courses as $course): ?>

            <div class="course-card">
                <h2>
                    <i class="fa fa-layer-group"></i>
                    <?= htmlspecialchars($course['course_name']) ?>
                </h2>

                <table>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Reg No</th>
                        <th>Gender</th>
                        <th>Date Registered</th>
                        <th>Action</th>
                    </tr>

                    <?php
                    $stmt = $pdo->prepare("
            SELECT * FROM students
            WHERE course_id = ?
        ");
                    $stmt->execute([$course['id']]);
                    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (!$students):
                    ?>
                        <tr>
                            <td colspan="6" style="text-align:center;color:#999;">
                                No students in this course
                            </td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($students as $s): ?>
                        <tr>
                            <td>
                                <img src="../assets/uploads/<?= $s['photo'] ?>"
                                    class="photo">
                            </td>
                            <td><?= $s['first_name'] . ' ' . $s['last_name'] ?></td>
                            <td><?= $s['reg_no'] ?></td>
                            <td><?= $s['gender'] ?></td>
                            <td><?= date('d M Y', strtotime($s['date_registered'])) ?></td>
                            <td class="action">
                                <a class="edit" href="edit_student.php?id=<?= $s['id'] ?>">
                                    <i class="fa fa-edit"></i>
                                </a>
                                &nbsp;
                                <a class="delete"
                                    href="delete_student.php?id=<?= $s['id'] ?>"
                                    onclick="return confirm('Delete this student?')">
                                    <i class="fa fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                </table>
            </div>

        <?php endforeach; ?>

    </div>

</body>

</html>
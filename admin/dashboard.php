<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    die("Access denied");
}

require '../config/db.php';

/* Dashboard statistics */
$totalStudents = $pdo->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalCourses  = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.content {
    margin-left: 260px;
    padding: 30px;
}

.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
    gap: 25px;
    margin-top: 30px;
}

.card {
    background: white;
    padding: 30px;
    border-radius: 18px;
    box-shadow: 0 10px 25px rgba(0,0,0,.1);
    display: flex;
    align-items: center;
    gap: 20px;
    transition: 0.3s;
}

.card:hover {
    transform: translateY(-5px);
}

.card i {
    font-size: 45px;
    padding: 18px;
    border-radius: 50%;
    color: white;
}

.card.students i {
    background: linear-gradient(135deg,#36d1dc,#5b86e5);
}

.card.courses i {
    background: linear-gradient(135deg,#ff9966,#ff5e62);
}

.card h2 {
    margin: 0;
    font-size: 2.2rem;
}

.card span {
    color: #666;
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2><i class="fa fa-user-shield"></i> Admin</h2>
    <a href="dashboard.php" class="active">
        <i class="fa fa-home"></i> Dashboard
    </a>
    <a href="add_student.php"><i class="fa fa-user-plus"></i> Add Student</a>
    <a href="view_students.php"><i class="fa fa-users"></i> View Students</a>
    <a href="../logout.php"><i class="fa fa-sign-out"></i> Logout</a>
</div>

<!-- CONTENT -->
<div class="content">
    <h1>Dashboard Overview</h1>

    <div class="cards">

        <div class="card students">
            <i class="fa fa-user-graduate"></i>
            <div>
                <h2><?= $totalStudents ?></h2>
                <span>Total Students</span>
            </div>
        </div>

        <div class="card courses">
            <i class="fa fa-layer-group"></i>
            <div>
                <h2><?= $totalCourses ?></h2>
                <span>Courses</span>
            </div>
        </div>

    </div>
</div>

</body>
</html>

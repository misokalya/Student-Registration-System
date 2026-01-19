<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    die("Access denied");
}

require '../config/db.php';

$stmt = $pdo->prepare("
    SELECT s.*, c.course_name
    FROM students s
    JOIN courses c ON s.course_id = c.id
    WHERE s.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>My Profile</title>

<link rel="stylesheet" href="../assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.profile-container {
    max-width: 900px;
    margin: 40px auto;
    background: white;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(0,0,0,.1);
    overflow: hidden;
}

.profile-header {
    background: linear-gradient(135deg,#4e54c8,#8f94fb);
    color: white;
    padding: 40px;
    display: flex;
    align-items: center;
    gap: 30px;
}

.profile-header img {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    object-fit: cover;
    border: 5px solid rgba(255,255,255,.5);
}

.profile-header h2 {
    margin: 0;
}

.profile-body {
    padding: 40px;
    display: grid;
    grid-template-columns: repeat(auto-fit,minmax(250px,1fr));
    gap: 25px;
}

.info {
    background: #f4f6f9;
    padding: 20px;
    border-radius: 12px;
}

.info i {
    color: #4e54c8;
    margin-right: 8px;
}

.logout {
    text-align: center;
    padding-bottom: 30px;
}
.logout a {
    background: #dc3545;
    color: white;
    padding: 12px 25px;
    border-radius: 30px;
    text-decoration: none;
}
</style>
</head>

<body>

<div class="profile-container">

    <div class="profile-header">
        <img src="../assets/uploads/<?= htmlspecialchars($s['photo']) ?>">
        <div>
            <h2><?= htmlspecialchars($s['first_name'].' '.$s['last_name']) ?></h2>
            <p><?= htmlspecialchars($s['course_name']) ?></p>
        </div>
    </div>

    <div class="profile-body">

        <div class="info">
            <p><i class="fa fa-id-card"></i> <b>Registration No:</b><br>
            <?= htmlspecialchars($s['reg_no']) ?></p>
        </div>

        <div class="info">
            <p><i class="fa fa-venus-mars"></i> <b>Gender:</b><br>
            <?= htmlspecialchars($s['gender']) ?></p>
        </div>

        <div class="info">
            <p><i class="fa fa-cake-candles"></i> <b>Date of Birth:</b><br>
            <?= date('d M Y', strtotime($s['dob'])) ?></p>
        </div>

        <div class="info">
            <p><i class="fa fa-calendar-check"></i> <b>Date Registered:</b><br>
            <?= date('d M Y', strtotime($s['date_registered'])) ?></p>
        </div>

    </div>

    <div class="logout">
        <a href="../logout.php">
            <i class="fa fa-sign-out"></i> Logout
        </a>
    </div>

</div>

</body>
</html>

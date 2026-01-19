<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require 'config/db.php';

if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin/dashboard.php");
        exit;
    } else {
        header("Location: student/profile.php");
        exit;
    }
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin/dashboard.php");
        } else {
            header("Location: student/profile.php");
        }
        exit;
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Login | Student System</title>

<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<style>
.login-box {
    width: 360px;
    margin: 120px auto;
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
}
.login-box h2 {
    text-align: center;
    margin-bottom: 20px;
}
.login-box input {
    width: 93%;
    padding: 12px;
    margin-bottom: 15px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.login-box button {
    width: 100%;
    padding: 12px;
    background: #4e54c8;
    border: none;
    color: white;
    border-radius: 6px;
    cursor: pointer;
}
.error {
    background: #ffe5e5;
    color: #c00;
    padding: 10px;
    margin-bottom: 10px;
    border-radius: 6px;
    text-align: center;
}
</style>
</head>

<body>

<div class="login-box">
    <h2><i class="fa fa-lock"></i> Login</h2>

    <?php if ($error): ?>
        <div class="error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username / Reg No" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login">
            <i class="fa fa-sign-in-alt"></i> Login
        </button>
    </form>
</div>
<div align="center"><a href="index.php">< Home</a></div>

</body>
</html>

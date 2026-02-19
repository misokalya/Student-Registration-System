<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'college_registration');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

session_start();

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireAdmin() {
    if (!isLoggedIn() || !isAdmin()) {
        header("Location: ../index.php");
        exit;
    }
}

function requireStudent() {
    if (!isLoggedIn() || isAdmin()) {
        header("Location: ../index.php");
        exit;
    }
}

function sanitize($conn, $val) {
    return $conn->real_escape_string(trim($val));
}
?>

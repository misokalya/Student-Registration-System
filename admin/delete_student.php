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

/* Get student to delete user + photo */
$stmt = $pdo->prepare("SELECT user_id, photo FROM students WHERE id=?");
$stmt->execute([$id]);
$s = $stmt->fetch(PDO::FETCH_ASSOC);

if ($s) {

    /* Delete photo */
    if ($s['photo'] && file_exists("../assets/uploads/" . $s['photo'])) {
        unlink("../assets/uploads/" . $s['photo']);
    }

    /* Delete student record */
    $pdo->prepare("DELETE FROM students WHERE id=?")->execute([$id]);

    /* Delete login account */
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$s['user_id']]);
}

header("Location: view_students.php");
exit;

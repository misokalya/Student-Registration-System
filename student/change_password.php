<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
    die("Access denied");
}

require '../config/db.php';

$success = $error = "";

/* Handle password change */
if (isset($_POST['change'])) {

    $current = $_POST['current_password'];
    $new     = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    if ($new !== $confirm) {
        $error = "New passwords do not match";
    } elseif (strlen($new) < 6) {
        $error = "Password must be at least 6 characters";
    } else {

        /* Fetch current password */
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id=?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($current, $user['password'])) {
            $error = "Current password is incorrect";
        } else {

            /* Update password */
            $hashed = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")
                ->execute([$hashed, $_SESSION['user_id']]);

            $success = "Password changed successfully";
        }
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Change Password</title>

    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        .password-box {
            max-width: 500px;
            background: white;
            padding: 30px;
            margin: 60px auto;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, .1);
        }

        .password-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .password-box input {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 6px;
            border: 1px solid #ccc;
        }

        .password-box button {
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

        .back {
            text-align: center;
            margin-top: 15px;
        }

        .back a {
            text-decoration: none;
            color: #4e54c8;
        }
    </style>
</head>

<body>

    <div class="password-box">
        <h2><i class="fa fa-key"></i> Change Password</h2>

        <?php if ($success): ?><div class="success"><?= $success ?></div><?php endif; ?>
        <?php if ($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <form method="POST">
            <input type="password" name="current_password" placeholder="Current Password" required>
            <input type="password" name="new_password" placeholder="New Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm New Password" required>

            <button type="submit" name="change">
                <i class="fa fa-save"></i> Update Password
            </button>
        </form>

        <div class="back">
            <a href="profile.php">
                <i class="fa fa-arrow-left"></i> Back to Profile
            </a>
        </div>
    </div>

</body>

</html>
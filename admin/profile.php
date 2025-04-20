<?php
session_start();
include '../connection/connect.php';

//avoid user coming back
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache"); 
header("Expires: 0"); 

//avoiding navigation button
if (isset($_SESSION['user_id'])) {
    echo '<script>history.pushState(null, null, window.location.href); window.onpopstate = function () { history.go(1); }</script>';
}

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT author_name, nickname_updated_at, password_updated_at FROM authors WHERE author_id = ?");
$stmt->execute([$user_id]);
$author = $stmt->fetch(PDO::FETCH_ASSOC);
$authorName = $author['author_name'];

$today = new DateTime();

$success_message = "";
$error_message = "";

if (isset($_POST['author_name']) && !empty(trim($_POST['author_name']))) {
    $new_author_name = trim($_POST['author_name']);
    $stmt = $pdo->prepare("UPDATE authors SET author_name = ? WHERE author_id = ?");
    $stmt->execute([$new_author_name, $user_id]);
    $success_message = "Successfully updated your name.";
}

if (isset($_POST['nickname']) && !empty(trim($_POST['nickname']))) {
    $new_nickname = trim($_POST['nickname']);
    $canUpdateNickname = false;

    if (empty($author['nickname_updated_at'])) {
        $canUpdateNickname = true;
    } else {
        $lastUpdate = new DateTime($author['nickname_updated_at']);
        $interval = $lastUpdate->diff($today);
        if ($interval->days >= 31) {
            $canUpdateNickname = true;
        }
    }

    if ($canUpdateNickname) {
        $stmt = $pdo->prepare("UPDATE authors SET author_display_name = ?, nickname_updated_at = NOW() WHERE author_id = ?");
        $stmt->execute([$new_nickname, $user_id]);
        $success_message = "Successfully updated your nickname.";
    } else {
        $error_message = "You can only change your nickname once every 31 days.";
    }
}

if (isset($_POST['new_password']) && !empty(trim($_POST['new_password']))) {
    $new_password = trim($_POST['new_password']);
    $canUpdatePassword = false;

    if (empty($author['password_updated_at'])) {
        $canUpdatePassword = true;
    } else {
        $lastUpdate = new DateTime($author['password_updated_at']);
        $interval = $lastUpdate->diff($today);
        if ($interval->days >= 31) {
            $canUpdatePassword = true;
        }
    }

    if ($canUpdatePassword) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE authors SET password = ?, password_updated_at = NOW() WHERE author_id = ?");
        $stmt->execute([$hashed_password, $user_id]);
        $success_message = "Successfully updated your password.";
    } else {
        $error_message = "You can only change your password once every 31 days.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile Update - Admin</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color:rgb(36, 39, 37);
        }
        .container {
            text-align: center;
            background: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            width: 90%;
            max-width: 500px;
        }
        .message {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
            font-size: 18px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(to right, #28a745, #000000);
            color: #ffffff;
            font-weight: bold;
            text-decoration: none;
            border-radius: 30px;
            transition: background 0.3s;
        }
        .back-link:hover {
            background: linear-gradient(to right, #218838, #000000);
        }
    </style>
    <?php if (!empty($success_message) || !empty($error_message)) : ?>
    <meta http-equiv="refresh" content="5;url=admin.php">
    <?php endif; ?>
</head>
<body>

<div class="container">
    <?php if (!empty($success_message)): ?>
        <div class="message success"><?php echo $success_message; ?><br>Redirecting back to dashboard...</div>
    <?php elseif (!empty($error_message)): ?>
        <div class="message error"><?php echo $error_message; ?><br>Redirecting back to dashboard...</div>
    <?php endif; ?>

    <a href="admin.php" class="back-link">Go back to Admin Dashboard</a>
</div>

</body>
</html>

<?php
session_start();
include '../connection/connect.php';

if(!isset($_SESSION['user_id'])){
    header("Location: admin_login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT author_name, nickname_updated_at, password_updated_at FROM authors WHERE author_id = ?");
$stmt->execute([$user_id]);
$author  = $stmt->fetch(PDO::FETCH_ASSOC);
$authorName = $author['author_name'];

$today  = new Datetime();


if (isset($_POST['author_name']) && !empty(trim($_POST['author_name']))) {
    $new_author_name = trim($_POST['author_name']);
    $stmt = $pdo->prepare("UPDATE authors SET author_name = ? WHERE author_id = ?");
    $stmt->execute([$new_author_name, $user_id]);
} else {
    $_SESSION['error'] = "Please enter a valid name.";
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
    } else {
        
        $_SESSION['error'] = "You can only change your nickname once every 31 days.";
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
    } else {
        
        $_SESSION['error'] = "You can only change your password once every 31 days.";
    }
}

header("Location: admin.php");
exit();
?>
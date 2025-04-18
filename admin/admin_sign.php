<?php
session_start(); 

include '../connection/connect.php';


if (!isset($_SESSION['email'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
        $email = trim($_POST['email']);
        
        $stmt = $pdo->prepare("SELECT * FROM authors WHERE email_address = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['email'] = $email; 
        } else {
            echo "<p style='color:red;'>Email not found!</p>";
        }
    }
}


if (isset($_SESSION['email']) && !isset($_SESSION['secret_code_verified'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['secret_code'])) {
        $secret_code_input = trim($_POST['secret_code']);

        $stmt = $pdo->prepare("SELECT * FROM authors WHERE email_address = ?");
        $stmt->execute([$_SESSION['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['secret_code'] === $secret_code_input) {
            $_SESSION['secret_code_verified'] = true; 
        } else {
            echo "<p style='color:red;'>Incorrect Secret Code!</p>";
        }
    }
}


if (isset($_SESSION['secret_code_verified']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_password'])) {
    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE authors SET password = ? WHERE email_address = ?");
    $stmt->execute([$new_password, $_SESSION['email']]);

    session_destroy();
    header("Location: admin_login.php"); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup</title>
    <link rel="stylesheet" href="admin_sign.css">
</head>
<body>
    <h1>Admin Setup</h1>

    <?php if (!isset($_SESSION['email'])): ?>
        <div class="email_part">
        <form method="POST">
            <input type="email" name="email" placeholder="Enter your email..." required>
            <button type="submit">Continue</button>
        </form>
    </div>
    <?php elseif (!isset($_SESSION['secret_code_verified'])): ?>
        <div class="secret_code">
        <form method="POST">
            <input type="text" name="secret_code" placeholder="Enter your secret code..." required>
            <button type="submit">Continue</button>
        </form>
    </div>
    <?php elseif (isset($_SESSION['secret_code_verified'])): ?>
        <div class="pass">
        <form method="POST">
            <input type="password" name="new_password" placeholder="Set your new password..." required>
            <button type="submit">Set Password</button>
        </form>
    </div>
    <?php endif; ?>

</body>
</html>

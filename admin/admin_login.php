<?php 
session_start();
include '../connection/connect.php';


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM authors WHERE email_address = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['author_id']; 
            $_SESSION['email'] = $user['email_address'];
            $_SESSION['role'] = $user['role'];

            echo "<p style='color:green;'>Login successful! Redirecting...</p>";
            header("refresh:2; url=admin.php"); 
            exit;
        } else {
            echo "<p style='color:red;'>Incorrect password!</p>";
        }
    } else {
        echo "<p style='color:red;'>Email not found!</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_login.css">
</head>
<body>
    
    <div class="login-form">
    <?php if (!empty($message)): ?>
                <div class="message <?= $message_type; ?>">
                    <?= htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

    <fieldset>
    <h2 style="text-align: center; margin-top: 0.2em;">Admin Login</h2>
    <form action="" method="POST">
        <input type="email" name="email" placeholder="Enter your email..." required><br>
        <input type="password" name="password" placeholder="Enter your password..." required><br>
        <button type="submit">Login</button>
    </form>
</fieldset>
</div>
</body>
</html>

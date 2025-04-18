<?php 
session_start();
include '../connection/connect.php';

$message = '';
$message_type = '';

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

           $message = 'Login successful! Redirecting...';
           $message_type = 'success';
            header("refresh:2; url=admin.php"); 
            exit;
        } else {
           $message = 'Incorrect password!';
            $message_type = 'error';
        }
    } else {
        $message = 'Email not found!';
        $message_type = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="admin_login.css?v=1.0">
</head>
<body>
    
    <div class="login-form">
    <fieldset>
    <h2 style="text-align: center; margin-top: 0.2em;">Admin Login</h2>
    <?php if (!empty($message)): ?>
            <div class="<?= htmlspecialchars($message_type); ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
    <form action="" method="POST">
        <input type="email" name="email" placeholder="Enter your email..." required><br>
        <input type="password" name="password" placeholder="Enter your password..." required><br>
        <button type="submit">Login</button>
    </form>
</fieldset>
</div>
</body>
</html>

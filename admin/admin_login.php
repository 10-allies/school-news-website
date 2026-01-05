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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin_login.css">
</head>
<body>
    <div class="container">
        <div class="login-form">
            <fieldset>
                <h2>Admin Login</h2>
                <p class="subtitle">Welcome back! Please login to your account</p>
                <?php if (!empty($message)): ?>
            <div class="<?= htmlspecialchars($message_type); ?>">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
                
                <form action="" method="POST">
                    <div class="input-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" placeholder="Enter your email..." required>
                    </div>
                    
                    <div class="input-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" placeholder="Enter your password..." required>
                    </div>
                    
                    <button type="submit">Login</button>
                    
                    <div class="forgot-password">
                        <a href="#">Forgot password?</a>
                    </div>
                </form>
            </fieldset>
        </div>
        
        <div class="greetings">
            <spline-viewer url="https://prod.spline.design/qI-4U129558MHQ-X/scene.splinecode"></spline-viewer>
            <div class="hide"></div>
        </div>
        
    </div>
    
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.12.28/build/spline-viewer.js"></script>
</body>
</html>

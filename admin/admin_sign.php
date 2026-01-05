<?php
session_start(); // VERY IMPORTANT - Start session at the top

include '../connection/connect.php'; // $pdo is already defined there

$error_message = '';
$success_message = '';

// Step 1: Enter email
if (!isset($_SESSION['email'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
        $email = trim($_POST['email']);
        
        $stmt = $pdo->prepare("SELECT * FROM authors WHERE email_address = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $_SESSION['email'] = $email; 
        } else {
            $error_message = "Email not found! Please check and try again.";
        }
    }
}

// Step 2: Enter secret code
if (isset($_SESSION['email']) && !isset($_SESSION['secret_code_verified'])) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['secret_code'])) {
        $secret_code_input = trim($_POST['secret_code']);

        $stmt = $pdo->prepare("SELECT * FROM authors WHERE email_address = ?");
        $stmt->execute([$_SESSION['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $user['secret_code'] === $secret_code_input) {
            $_SESSION['secret_code_verified'] = true; 
        } else {
            $error_message = "Incorrect Secret Code! Please try again.";
        }
    }
}

// Step 3: Set new password
if (isset($_SESSION['secret_code_verified']) && $_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['new_password'])) {
    $new_password = password_hash(trim($_POST['new_password']), PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("UPDATE authors SET password = ? WHERE email_address = ?");
    $stmt->execute([$new_password, $_SESSION['email']]);

    session_destroy();
    header("Location: admin_login.php"); // Redirect to login page
    exit;
}

// Determine current step
$current_step = 1;
if (isset($_SESSION['email']) && !isset($_SESSION['secret_code_verified'])) {
    $current_step = 2;
} elseif (isset($_SESSION['secret_code_verified'])) {
    $current_step = 3;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Setup - Alliance</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #004d00 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Animated Background Pattern */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                repeating-linear-gradient(
                    45deg,
                    transparent,
                    transparent 10px,
                    rgba(108, 250, 58, 0.03) 10px,
                    rgba(108, 250, 58, 0.03) 20px
                );
            animation: movePattern 20s linear infinite;
            pointer-events: none;
        }

        @keyframes movePattern {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(40px) translateY(40px); }
        }

        .container {
            background: rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            padding: 50px 40px;
            width: 100%;
            max-width: 500px;
            box-shadow: 
                0 8px 32px rgba(0, 0, 0, 0.5),
                0 0 0 1px rgba(108, 250, 58, 0.1),
                0 0 80px rgba(108, 250, 58, 0.2);
            position: relative;
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Logo Section */
        .logo-section {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #6cfa3a, #4dd624);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 0 30px rgba(108, 250, 58, 0.4);
        }

        .logo i {
            font-size: 40px;
            color: #000;
        }

        h1 {
            color: #6cfa3a;
            font-size: 2em;
            font-weight: 800;
            text-align: center;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .subtitle {
            color: #b0b0b0;
            text-align: center;
            font-size: 0.95em;
            margin-bottom: 30px;
        }

        /* Progress Steps */
        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 40px;
            position: relative;
        }

        .progress-steps::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 0;
            width: 100%;
            height: 2px;
            background: rgba(108, 250, 58, 0.2);
            z-index: 0;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 1;
            flex: 1;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: rgba(108, 250, 58, 0.2);
            border: 2px solid rgba(108, 250, 58, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #6cfa3a;
            font-weight: 700;
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .step.active .step-circle {
            background: linear-gradient(135deg, #6cfa3a, #4dd624);
            border-color: #6cfa3a;
            color: #000;
            box-shadow: 0 0 20px rgba(108, 250, 58, 0.6);
        }

        .step.completed .step-circle {
            background: #6cfa3a;
            border-color: #6cfa3a;
            color: #000;
        }

        .step-label {
            font-size: 0.75em;
            color: #808080;
            text-align: center;
        }

        .step.active .step-label {
            color: #6cfa3a;
            font-weight: 600;
        }

        /* Form Styles */
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6cfa3a;
            font-size: 18px;
            pointer-events: none;
        }

        input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(255, 255, 255, 0.05);
            border: 2px solid rgba(108, 250, 58, 0.2);
            border-radius: 10px;
            color: #ffffff;
            font-size: 1em;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        input::placeholder {
            color: #808080;
        }

        input:focus {
            outline: none;
            border-color: #6cfa3a;
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 20px rgba(108, 250, 58, 0.2);
        }

        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #6cfa3a, #4dd624);
            border: none;
            border-radius: 10px;
            color: #000;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 4px 15px rgba(108, 250, 58, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(108, 250, 58, 0.5);
        }

        button:active {
            transform: translateY(0);
        }

        /* Messages */
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.95em;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.3s ease-out;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-message {
            background: rgba(231, 76, 60, 0.2);
            border: 1px solid rgba(231, 76, 60, 0.4);
            color: #ff6b6b;
        }

        .success-message {
            background: rgba(108, 250, 58, 0.2);
            border: 1px solid rgba(108, 250, 58, 0.4);
            color: #6cfa3a;
        }

        .info-box {
            background: rgba(108, 250, 58, 0.1);
            border: 1px solid rgba(108, 250, 58, 0.3);
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
            color: #b0b0b0;
            font-size: 0.9em;
            line-height: 1.6;
        }

        .info-box i {
            color: #6cfa3a;
            margin-right: 8px;
        }

        /* Responsive Design */
        @media (max-width: 600px) {
            .container {
                padding: 40px 25px;
            }

            h1 {
                font-size: 1.6em;
            }

            .step-label {
                font-size: 0.7em;
            }

            .step-circle {
                width: 35px;
                height: 35px;
                font-size: 0.9em;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo-section">
            <div class="logo">
                <i class="bi bi-shield-lock-fill"></i>
            </div>
            <h1>Admin Setup</h1>
            <p class="subtitle">Secure access to Alliance Admin Panel</p>
        </div>

        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step <?= $current_step >= 1 ? 'active' : '' ?> <?= $current_step > 1 ? 'completed' : '' ?>">
                <div class="step-circle"><?= $current_step > 1 ? '✓' : '1' ?></div>
                <span class="step-label">Email</span>
            </div>
            <div class="step <?= $current_step >= 2 ? 'active' : '' ?> <?= $current_step > 2 ? 'completed' : '' ?>">
                <div class="step-circle"><?= $current_step > 2 ? '✓' : '2' ?></div>
                <span class="step-label">Secret Code</span>
            </div>
            <div class="step <?= $current_step >= 3 ? 'active' : '' ?>">
                <div class="step-circle">3</div>
                <span class="step-label">New Password</span>
            </div>
        </div>

        <?php if ($error_message): ?>
            <div class="message error-message">
                <i class="bi bi-exclamation-circle-fill"></i>
                <?= htmlspecialchars($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="message success-message">
                <i class="bi bi-check-circle-fill"></i>
                <?= htmlspecialchars($success_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!isset($_SESSION['email'])): ?>
            <div class="info-box">
                <i class="bi bi-info-circle-fill"></i>
                Enter your registered email address to begin the password setup process.
            </div>
            <form method="POST">
                <div class="input-group">
                    <i class="bi bi-envelope-fill input-icon"></i>
                    <input type="email" name="email" placeholder="Enter your email address" required autofocus>
                </div>
                <button type="submit">
                    Continue <i class="bi bi-arrow-right"></i>
                </button>
            </form>

        <?php elseif (!isset($_SESSION['secret_code_verified'])): ?>
            <div class="info-box">
                <i class="bi bi-key-fill"></i>
                Enter the secret code that was provided to you during account creation.
            </div>
            <form method="POST">
                <div class="input-group">
                    <i class="bi bi-shield-lock-fill input-icon"></i>
                    <input type="text" name="secret_code" placeholder="Enter your secret code" required autofocus>
                </div>
                <button type="submit">
                    Verify Code <i class="bi bi-arrow-right"></i>
                </button>
            </form>

        <?php elseif (isset($_SESSION['secret_code_verified'])): ?>
            <div class="info-box">
                <i class="bi bi-lock-fill"></i>
                Create a strong password for your account. Use a combination of letters, numbers, and symbols.
            </div>
            <form method="POST">
                <div class="input-group">
                    <i class="bi bi-key-fill input-icon"></i>
                    <input type="password" name="new_password" placeholder="Enter your new password" required minlength="8" autofocus>
                </div>
                <button type="submit">
                    Set Password <i class="bi bi-check-circle-fill"></i>
                </button>
            </form>

        <?php endif; ?>
    </div>
</body>
</html>
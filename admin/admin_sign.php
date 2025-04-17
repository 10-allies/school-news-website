<?php
session_start();
include '../connection/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $role = trim($_POST['role']);

}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="POST">
        <input type="email" name="email" id="email" placeholder="Enter your email.."><br>
        <input type="password" name="password" id="password" placeholder="Enter your password.."><br>
        <button type="submit">Login</button>
    </form>
    <?php
  
    ?>
</body>
</html>
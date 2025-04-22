<?php  
$host = 'localhost'; // Change if you need to use a different host  
$db = 'sports_news';  
$user = 'root'; // Change if your username is different  
$password = ''; // Change if you have a different password  

try {  
    // Create a PDO instance  
    $conn = new PDO("mysql:host=$host;dbname=$db", $user, $password);  
    // Set the PDO error mode to exception  
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  
} catch (PDOException $e) {  
    die("Connection failed: " . $e->getMessage());  
}  
?>  
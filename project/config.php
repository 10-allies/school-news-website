<?php  
$host = 'localhost'; // Change if you need to use a different host  
$db = 'sports_news';  
$user = 'root'; // Change if your username is different  
$password = ''; // Change if you have a different password  

// Create connection  
$conn = new mysqli($host, $user, $password, $db);  

// Check connection  
if ($conn->connect_error) {  
    die("Connection failed: " . $conn->connect_error);  
}  
?>  
<?php   
session_start();
// Your database credentials   
$servername = "localhost";
$username = "root";  
$password = "";  
$dbname = "newsdb";  

// Create connection  
$conn = new mysqli($servername, $username, $password, $dbname);  

// Check connection  
if ($conn->connect_error) {  
    die("Connection failed: " . $conn->connect_error);  
}  

// Check whether the data from the form is submitted   
if ($_SERVER["REQUEST_METHOD"] == "POST") {  
    $name = $conn->real_escape_string($_POST['name']);  
    $password = $conn->real_escape_string($_POST['password']);  
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
   
   
    // Prepare and bind  
    $stmt = $conn->prepare("INSERT INTO user (name, password) VALUES (?, ?)");  
     
    $stmt->bind_param("ss", $name, $hashed_password);  

    if ($stmt->execute()) {  
        header("Location: Allyai.php"); 
        $_SESSION['name']=$name;//'name' is the key, $name is the value
        exit(); 
    } else {  
        echo "Error: " . $stmt->error;  
    }  

    // Close the statement   
    $stmt->close();  
}  

// Close connection  
$conn->close();  
?>  
<?php  
include 'config.php'; // Include the database connection  

try {  
    // Create a PDO instance  
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);  
    // Set the PDO error mode to exception  
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);  

    // Prepare the SQL statement  
    $sql = "SELECT * FROM articles ORDER BY date_published DESC"; // Latest articles first  
    $stmt = $conn->prepare($sql);  
    $stmt->execute();  

    // Fetch articles  
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);  
} catch (PDOException $e) {  
    echo "Connection failed: " . $e->getMessage();  
}  

// Close the connection  
$conn = null;  

// Return articles as JSON  
header('Content-Type: application/json');  
echo json_encode($articles);  
?>  
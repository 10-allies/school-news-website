<?php  
include 'foot_config.php'; // Include the database connection  

// Fetch articles from the database  
$sql = "SELECT * FROM articles ORDER BY date_published DESC"; // Latest articles first  
$result = $conn->query($sql);  

$articles = [];  
if ($result->num_rows > 0) {  
    // Fetch each article  
    while($row = $result->fetch_assoc()) {  
        $articles[] = $row;  
    }  
}  

$conn->close();  

// Return articles as JSON  
header('Content-Type: application/json');  
echo json_encode($articles);  
?>  
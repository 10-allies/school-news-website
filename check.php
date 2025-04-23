<?php
// database connection
$conn = new mysqli("localhost", "root", "", "newsdb");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch all sections for Sports
$sports_sections = [];
$sql = "SELECT * FROM sections WHERE category_id = (SELECT category_id FROM news_category WHERE category_name = 'Sports')";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $sports_sections[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>School News Portal</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        nav { margin-bottom: 20px; }
        nav a { margin-right: 15px; text-decoration: none; }
        .dropdown { position: relative; display: inline-block; }
        .dropdown-content { display: none; position: absolute; background-color: #f9f9f9; min-width: 160px; box-shadow: 0px 8px 16px 0px rgba(0,0,0,0.2); }
        .dropdown-content a { color: black; padding: 12px 16px; text-decoration: none; display: block; }
        .dropdown:hover .dropdown-content { display: block; }
    </style>
</head>
<body>

<nav>
    <a href="sport.php">Sports</a>
    <a href="#">Entertainment</a>
    <a href="#">Announcement</a>
</nav>


<h1>Welcome to the School News Portal!</h1>
<p>Select a category above to view news.</p>

</body>
</html>

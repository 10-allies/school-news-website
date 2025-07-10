<?php
// PDO connection to the database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=newsdb;charset=utf8', 'root', '');
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Get the news ID from URL parameter
$news_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($news_id <= 0) {
    die("Invalid news ID specified.");
}

// Get the category_id for 'Local news' dynamically
$local_news_category_id = 0;
try {
    $stmt = $pdo->prepare("SELECT category_id FROM news_category WHERE category_name = 'Local news' LIMIT 1");
    $stmt->execute();
    $category_row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($category_row) {
        $local_news_category_id = (int)$category_row['category_id'];
    } else {
        die("Category 'Local news' not found in the database. Please create it or adjust the category name in the code.");
    }
} catch (PDOException $e) {
    die("Query failed to get category ID: " . $e->getMessage());
}


// Fetch the news item with the provided ID, only if it belongs to 'Local news'
$news_sql = "SELECT n.news_id, n.news_title, n.news_content, n.created_at, a.author_display_name AS posted_by, m.media_url
             FROM news n
             LEFT JOIN authors a ON n.author_id = a.author_id
             LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type = 'image'
             WHERE n.news_id = :news_id AND n.category_id = :category_id";

try {
    $stmt = $pdo->prepare($news_sql);
    $stmt->bindParam(':news_id', $news_id, PDO::PARAM_INT);
    $stmt->bindParam(':category_id', $local_news_category_id, PDO::PARAM_INT);
    $stmt->execute();
    $news = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Query failed: " . $e->getMessage());
}

// Close connection (PDO auto-closes when script ends)
$pdo = null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title><?= $news ? htmlspecialchars($news['news_title']) : "News Not Found" ?></title>
<link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
<style>
body {
  font-family: 'Roboto', sans-serif;
  background-color: #f0f2f5;
  margin: 0;
  padding: 0;
  line-height: 1.6;
  color: #333;
}
.header {
  background-color: #222;
  padding: 20px;
  color: white;
  text-align: center;
}
.header h1 {
  margin: 0;
  font-size: 2em;
}
.container {
  max-width: 900px;
  margin: 30px auto;
  background: #fff;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}
h2 {
  margin-top: 0;
  color: #333;
}
.post-meta {
  color: #777;
  font-size: 0.9em;
  margin-bottom: 20px;
}
img {
  width: 100%;
  height: auto;
  border-radius: 8px;
  margin-bottom: 20px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  transition: transform 0.2s ease;
}
img:hover {
  transform: scale(1.02);
}
p.content {
  font-size: 1.1em;
  line-height: 1.6;
  color: #444;
}
.back-btn {
  display: inline-block;
  margin-top: 30px;
  padding: 12px 25px;
  background-color: #007bff;
  color: #fff;
  text-decoration: none;
  border-radius: 6px;
  font-weight: 600;
  transition: background-color 0.3s ease;
}
.back-btn:hover {
  background-color: #0056b3;
}
</style>
</head>
<body>
<div class="header">
  <h1>Local News</h1>
</div>
<div class="container">
<?php if ($news): ?>
  <h2><?= htmlspecialchars($news['news_title']) ?></h2>
  <div class="post-meta">
    Posted on <?= date("F j, Y", strtotime($news['created_at'])) ?>
    <?php if ($news['posted_by']): ?>
      by <?= htmlspecialchars($news['posted_by']) ?>
    <?php endif; ?>
  </div>
  <?php 
  $image_path = (!empty($news['media_url']) && $news['media_url'] !== 'uploads/default.jpg') 
                  ? '../' . htmlspecialchars($news['media_url']) // Prepend ../
                  : '../images/placeholder.jpg'; // Path to placeholder
  ?>
  <img src="<?= $image_path ?>" alt="News Image" />
  <p class="content"><?= nl2br(htmlspecialchars($news['news_content'])) ?></p>
  <a href="local.php" class="back-btn">? Back to Local News</a>
<?php else: ?>
  <h2>News Not Found</h2>
  <p>The news item with ID <?= htmlspecialchars($news_id) ?> in 'Local news' category does not exist or is not published.</p>
  <a href="local.php" class="back-btn">? Back to Local News</a>
<?php endif; ?>
</div>
</body>
</html>
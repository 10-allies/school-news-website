<?php
$conn = new mysqli("localhost", "root", "", "newsdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sections = [];
$sql = "SELECT * FROM sections WHERE category_id = (SELECT category_id FROM news_category WHERE category_name = 'Sports')";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}


$selected_section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : $sections[0]['section_id'];
$selected_section_name = "";

foreach ($sections as $section) {
    if ($section['section_id'] == $selected_section_id) {
        $selected_section_name = $section['section_name'];
        break;
    }
}


$news = [];
$news_sql = "SELECT n.news_id, n.news_title, n.news_content, n.created_at, a.author_display_name AS posted_by, m.media_url
             FROM news n
             JOIN authors a ON n.author_id = a.author_id
             LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type = 'image'
             WHERE n.section_id = $selected_section_id AND n.status = 'published' 
             ORDER BY n.created_at DESC";
$news_result = $conn->query($news_sql);
while ($row = $news_result->fetch_assoc()) {
    $news[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $image = $_FILES['image'];
    $upload_dir = "uploads/";
    $image_path = $upload_dir . basename($image['name']);
    

    if (getimagesize($image['tmp_name']) !== false) {
        if (move_uploaded_file($image['tmp_name'], $image_path)) {
            $news_id = (int)$_POST['news_id'];
        
            $media_sql = "INSERT INTO media (news_id, media_url) VALUES ($news_id, '$image_path')";
            if ($conn->query($media_sql)) {
                echo "Image uploaded successfully.";
            } else {
                echo "Error uploading image.";
            }
        } else {
            echo "Error moving the uploaded file.";
        }
    } else {
        echo "The file is not a valid image.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sports News</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        nav { background-color: #333; padding: 10px; }
        nav a { color: white; margin-right: 15px; text-decoration: none; }
        .subnav {
            
            background-color: white; 
    padding: 10px 0;
    text-align: center;
    border-bottom: 1px solid #e0e0e0;
        }
        .subnav a { 
            text-decoration: none;
    color: black; /* make text black */
    font-weight: bold;
    font-size: 16px;
    transition: color 0.3s;
    margin: 0 15px;
        }
        .container { 
        padding: 20px; 
        background-image: url('../images/kk1.jpg');
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        height: 100vh;
        }
        .main-info { 
            background-color: rgba(255, 255, 255, 0.8); /* white with some transparency */
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); 
        }
        .news-card { border: 1px solid #ddd; padding: 15px; margin-bottom: 20px; }
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        body {
          font-family: Arial, sans-serif;
          background-color: #fff;
        }

        .wrapper {
          width: 100%;
          max-width: 1250px;
          margin: 0 auto;
        }

        /* Top Nav */
        .top-nav {
          margin-top: 100px;
          display: flex;
          align-items: center;
          padding: 0 20px;
          height: 65px;
          border-bottom: 1px solid #e0e0e0;
        }

        .logo-box {
          background-color:greenyellow;
          height: 100%;
          padding: 0 12px;
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .logo-box img {
          height: 46px;
          width: auto;
        }

        .site-title {
          font-size: 18px;
          font-weight: bold;
          color:6cfa3a;
          margin-left: 15px;
        }

        .nav-links {
          display: flex;
          gap: 25px;
          margin-left: 50px;
          font-weight: bold;
        }

        .nav-links a {
          text-decoration: none;
          color: black;
          font-size: 16px;
          position: relative;
          padding-bottom: 5px;
          transition: color 0.2s ease-in-out;
        }

        .nav-links a:hover {
          color: #1a73e8;
        }

        .nav-links a:hover::after {
          content: "";
          position: absolute;
          bottom: 0;
          left: 0;
          height: 2px;
          width: 100%;
          background-color: #1a73e8;
        }

        .nav-links a::after {
          content: ' ▾';
          font-size: 12px;
          display: inline-block;
        }

        .nav-links a:last-child::after {
          content: '';
        }
        .page-heading {
          text-align: center;
          margin-top: 20px;
          font-size: 2.5em;
          color: #333;
        }
  
    </style>
</head>
<body>


<div class="wrapper">
        <div class="top-nav">
            <div class="logo-box">
                <img src="../images/claudia (1).gif" alt="Alliance Logo">
            </div>
            <div class="site-title">ALLIANCE</div>
            <div class="nav-links">
                <a href="#">All</a>
                <a href="#">Local news</a>
                <a href="./pages/sport.php">Sports</a>
                <a href="#">Entertainment</a>
                <li><a href="#" onclick="showContent(event, 'anounce')">School Announcement</a></li>  
            </div>
        </div>

        
        </div>
    </div>
    
    <h1 class="page-heading">SPORT</h1>  

    <div class="subnav">
    <?php foreach ($sections as $section): ?>
        <?php if ($section['section_name'] === 'Hope Premier League'): ?>
            <a href="premier_league.php">
                <?= htmlspecialchars($section['section_name']) ?>
            </a>
        <?php else: ?>
            <a href="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>?section_id=<?= $section['section_id'] ?>">
                <?= htmlspecialchars($section['section_name']) ?>
            </a>
        <?php endif; ?>
    <?php endforeach; ?>
</div>



<div class="container">
    <div class="main-info">
    <h2><?= htmlspecialchars($selected_section_name) ?> News</h2>

    <?php if (count($news) > 0): ?>
        <?php foreach ($news as $item): ?>
            <div class="news-card">
                <h3><?= htmlspecialchars($item['news_title']) ?></h3>
                <p><?= nl2br(htmlspecialchars($item['news_content'])) ?></p>
                <?php if ($item['media_url']): ?>
                    <img src="<?= $item['media_url'] ?>" alt="News Image" style="max-width: 100%; height: auto;">
                <?php endif; ?>
                <p><strong>Posted by:</strong> <?= htmlspecialchars($item['posted_by']) ?></p>
                <small>Posted on <?= $item['created_at'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No news in <?= htmlspecialchars($selected_section_name) ?> yet.</p>
    <?php endif; ?>
</div>
    </div>
</body>
</html>

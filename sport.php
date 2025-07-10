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
/* ========== CSS Variables ========== */
:root {
  --primary-color: #333;
  --accent-color: #1a73e8;
  --site-title-color: #6cfa3a;
  --hamburger-color: #fff;
  --nav-link-color: #fff;
  --nav-background: #333;
  --nav-hover-color: #1a73e8;
  --nav-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}

/* ========== Global Reset ========== */
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: Arial, sans-serif;
  background-color: #fff;
}

/* ========== Responsive Navigation Bar (Powerful Nav) ========== */
.responsive-nav {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: var(--nav-background);
  padding: 10px 20px;
  position: relative;
  box-shadow: var(--nav-shadow);
  z-index: 100;
}

/* Logo & Site Title */
.logo-box {
  display: flex;
  align-items: center;
}

.logo-box img {
  height: 46px;
  width: auto;
}

.site-title {
  font-size: 20px;
  font-weight: bold;
  color: var(--site-title-color);
  margin-left: 15px;
}

/* Navigation Links (Desktop Layout) */
.nav-links {
  display: flex;
  list-style: none;
}

.nav-links li a {
  text-decoration: none;
  color: var(--nav-link-color);
  font-size: 16px;
  padding: 10px 15px;
  transition: all 0.3s ease-in-out;
}

.nav-links li a:hover,
.nav-links li a:focus {
  color: var(--nav-hover-color);
  transform: translateY(-3px);
}

/* ================= Hamburger Menu for Mobile ================= */
/* Hidden checkbox to control toggle */
#menu-toggle {
  display: none;
}

/* Hamburger Icon using span elements */
.hamburger {
  display: none;
  cursor: pointer;
  width: 30px;
  height: 25px;
  position: relative;
  z-index: 200;
}

.hamburger span {
  display: block;
  width: 100%;
  height: 3px;
  background: var(--hamburger-color);
  margin: 5px 0;
  transition: all 0.3s ease;
}

/* Transform hamburger to "X" when toggled */
#menu-toggle:checked + .hamburger span:nth-child(1) {
  transform: translateY(8px) rotate(45deg);
}

#menu-toggle:checked + .hamburger span:nth-child(2) {
  opacity: 0;
}

#menu-toggle:checked + .hamburger span:nth-child(3) {
  transform: translateY(-8px) rotate(-45deg);
}

/* ================= Media Queries for Navigation ================= */
/* For devices up to 768px (tablets and below) */
@media (max-width: 768px) {
  /* Hide the horizontal nav links and use vertical layout */
  .nav-links {
    flex-direction: column;
    position: absolute;
    top: 65px; /* Adjust based on nav height */
    left: 0;
    width: 100%;
    background-color: var(--nav-background);
    display: none;
  }
  
  .nav-links li {
    text-align: center;
    width: 100%;
    border-bottom: 1px solid #444;
  }

  /* Show hamburger icon */
  .hamburger {
    display: block;
  }
  
  /* When toggle is active, display mobile menu */
  #menu-toggle:checked + .hamburger + .nav-links {
    display: flex;
  }
}

/* For mobile devices up to 480px */
@media (max-width: 480px) {
  .site-title {
    font-size: 18px;
  }

  .nav-links li a {
    font-size: 14px;
    padding: 8px 10px;
  }
}

/* ========== Additional Site Styles ========== */

/* Subnavigation (if needed in addition to the main nav) */
.subnav {
  background-color: #fff;
  padding: 10px 0;
  text-align: center;
  border-bottom: 1px solid #e0e0e0;
}

.subnav a {
  text-decoration: none;
  color: black;
  font-weight: bold;
  font-size: 16px;
  transition: color 0.3s;
  margin: 0 15px;
}

/* Main Content Container with Background Image */
.container {
  padding: 20px;
  background-image: url('./images/kk1.jpg');
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  min-height: 100vh;
}

/* Main Information Box */
.main-info {
  background-color: rgba(255, 255, 255, 0.8); /* White with transparency */
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

/* News Card Styling */
.news-card {
  border: 1px solid #ddd;
  padding: 15px;
  margin-bottom: 20px;
}

/* Wrapper to constrain content width */
.wrapper {
  width: 100%;
  max-width: 1250px;
  margin: 0 auto;
}

/* Page Heading */
.page-heading {
  text-align: center;
  margin-top: 20px;
  font-size: 2.5em;
  color: #333;
}

/* Legacy Top Navigation (if needed for alternate layouts) */
.top-nav {
  margin-top: 100px;
  display: flex;
  align-items: center;
  padding: 0 20px;
  height: 65px;
  border-bottom: 1px solid #e0e0e0;
}


    </style>
</head>
<body>


<nav class="responsive-nav">
  <div class="logo-box">
    <img src="./images/logo.png" alt="Site Logo" />
    <span class="site-title">MySite</span>
  </div>
  <!-- Hidden checkbox to toggle menu visibility -->
  <input type="checkbox" id="menu-toggle" />
  <!-- Hamburger icon -->
  <label for="menu-toggle" class="hamburger">&#9776;</label>
  <!-- Navigation Links -->
  <ul class="nav-links">
    <li><a href="#home">Home</a></li>
    <li><a href="#news">News</a></li>
    <li><a href="#entertainment">Entertainment</a></li>
    <li><a href="#sports">Sports</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>
</nav>

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

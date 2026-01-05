<?php
// PDO connection to the database
try {
    $pdo = new PDO('mysql:host=localhost;dbname=newsdb;charset=utf8', 'root', '');
    // Set error mode to exceptions
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch the category_id for 'Local news' dynamically
$local_news_category_id = 0;
try {
    $stmt = $pdo->prepare("SELECT category_id FROM news_category WHERE category_name = 'Local news' LIMIT 1");
    $stmt->execute();
    $category_row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($category_row) {
        $local_news_category_id = (int)$category_row['category_id'];
    } else {
        // If 'Local news' category doesn't exist, handle it gracefully
        echo "<p style='color:red; text-align:center;'>Warning: 'Local news' category not found in the database. Please create it.</p>";
        $category_news = [];
    }
} catch (PDOException $e) {
    die("Error fetching category ID: " . $e->getMessage());
}

// Fetch news only from the determined local_news_category_id
$category_news = [];
if ($local_news_category_id > 0) {
    $category_news_sql = "SELECT n.news_id, n.news_title, n.news_content, n.created_at, 
                                 a.author_display_name AS posted_by, m.media_url, m.media_caption
                          FROM news n
                          LEFT JOIN authors a ON n.author_id = a.author_id
                          LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type='image'
                          WHERE n.status='published' AND n.category_id = :category_id
                          ORDER BY n.created_at DESC";

    try {
        $stmt = $pdo->prepare($category_news_sql);
        $stmt->bindParam(':category_id', $local_news_category_id, PDO::PARAM_INT);
        $stmt->execute();
        $category_news = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        die("Query failed: " . $e->getMessage());
    }
}

// Prepare sections
$slider_news = array_slice($category_news, 0, 3); // Top 3 for slider
$top_stories = array_slice($category_news, 0, 3); // Top 3 for top stories
$other_news = array_slice($category_news, 3, 4); // Next 4 for "Other Updates"

// Function to get correct image path
function getImagePath($media_url) {
    if (empty($media_url) || $media_url === 'uploads/default.jpg') {
        return '../images/placeholder.jpg';
    }
    
    // Handle both /uploads/ and uploads/ formats
    if (strpos($media_url, '/') === 0) {
        // If starts with /, prepend ..
        return '..' . htmlspecialchars($media_url);
    } else {
        // If starts with uploads/, prepend ../
        return  htmlspecialchars($media_url);
    }
}

// Close connection
$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Local News - APPEAL Newspaper</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="local.css">
    
    <style>
        /* --- CSS Variables for Theme Colors --- */
        :root {
            --bg-primary: #f0f2f5;
            --bg-secondary: #ffffff;
            --bg-header: #e6e6e6;
            --text-primary: #333;
            --text-secondary: #555;
            --text-tertiary: #777;
            --border-color: #e0e0e0;
            --shadow-light: rgba(0,0,0,0.1);
            --shadow-medium: rgba(0,0,0,0.08);
            --accent-green: #6cfa3a;
            --accent-blue: #0056b3;
            --footer-bg: #004d00;
            --footer-dark: #003300;
            --slide-overlay: rgba(0,0,0,0.8);
            --slide-filter: brightness(70%);
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-header: #252525;
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --text-tertiary: #909090;
            --border-color: #404040;
            --shadow-light: rgba(0,0,0,0.3);
            --shadow-medium: rgba(0,0,0,0.2);
            --accent-green: #6cfa3a;
            --accent-blue: #4a9eff;
            --footer-bg: #1a3a1a;
            --footer-dark: #0d1f0d;
            --slide-overlay: rgba(0,0,0,0.9);
            --slide-filter: brightness(60%);
        }

        /* --- General Reset & Body Layout --- */
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
        }

        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: var(--bg-primary);
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .wrapper {
            flex: 1;
            width: 100%;
            max-width: 1250px;
            margin: 0 auto;
            margin-top: 40px;
            position: relative;
            padding: 0 15px;
        }

        /* --- Theme Toggle Button --- */
        .theme-toggle {
            background: var(--bg-secondary);
            border: 2px solid var(--border-color);
            border-radius: 50px;
            width: 60px;
            height: 32px;
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            margin-left: 20px;
        }

        .theme-toggle::before {
            content: '☀️';
            position: absolute;
            left: 4px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            transition: all 0.3s ease;
        }

        [data-theme="dark"] .theme-toggle::before {
            content: '🌙';
            left: calc(100% - 28px);
        }

        /* --- Top Navigation Bar (Header) --- */
        .top-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            height: 100px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--bg-secondary);
            box-shadow: 0 2px 5px var(--shadow-light);
            position: sticky;
            top: 5px;
            z-index: 1000;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .nav-left {
            display: flex;
            align-items: center;
        }

        .logo-box {
            background-color: var(--accent-green);
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
            font-size: 1.2em;
            font-weight: 800;
            color: var(--accent-green);
            margin-left: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            font-weight: 600;
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-primary);
            font-size: 1.0em;
            position: relative;
            padding-bottom: 5px;
            transition: color 0.2s ease-in-out;
        }

        .nav-links a:hover,
        .nav-links a.active {
            color: var(--accent-blue);
        }

        .nav-links a:hover::after,
        .nav-links a.active::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
            background-color: var(--accent-blue);
        }

        /* Toggle Button (Hamburger) for mobile */
        .nav-toggle {
            display: none;
            flex-direction: column;
            justify-content: space-between;
            width: 30px;
            height: 20px;
            cursor: pointer;
            z-index: 1001;
        }

        .nav-toggle span {
            display: block;
            width: 100%;
            height: 3px;
            background-color: var(--text-primary);
            border-radius: 2px;
            transition: all 0.3s ease-in-out;
        }

        /* Responsive Mobile Navigation */
        @media (max-width: 768px) {
            .nav-toggle {
                display: flex;
            }

            .nav-right {
                position: absolute;
                top: 100px;
                left: 0;
                width: 100%;
                flex-direction: column;
                background-color: var(--bg-secondary);
                box-shadow: 0 5px 10px var(--shadow-light);
                padding: 15px 0;
                border-top: 1px solid var(--border-color);
                display: none;
                gap: 0;
            }

            .nav-right.active {
                display: flex;
            }

            .nav-links {
                flex-direction: column;
                width: 100%;
                gap: 0;
            }

            .nav-links li {
                width: 100%;
                text-align: center;
            }

            .nav-links a {
                padding: 10px 0;
                display: block;
            }

            .theme-toggle {
                margin: 15px auto 0;
            }

            .site-title {
                margin-left: 10px;
            }
        }

        /* --- Page Header / Heading --- */
        .page-heading {
            text-align: center;
            padding: 40px 20px;
            background-color: var(--bg-header);
            margin-bottom: 30px;
            box-shadow: inset 0 -2px 5px var(--shadow-light);
            position: relative;
            transition: background-color 0.3s ease;
        }

        .page-heading::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(108, 250, 58, 0.1), rgba(0, 86, 179, 0.05));
            pointer-events: none;
        }

        .page-heading h1 {
            font-size: 3.2em;
            color: var(--accent-blue);
            margin-bottom: 10px;
            font-weight: 800;
            text-shadow: 2px 2px 4px var(--shadow-light);
        }

        .page-heading p {
            font-size: 1.1em;
            color: var(--text-secondary);
            max-width: 700px;
            margin: 0 auto;
        }

        /* --- Slider Section --- */
        .slider {
            position: relative;
            width: 100%;
            height: 450px;
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 8px 20px var(--shadow-light);
            margin-bottom: 40px;
            background-color: var(--bg-secondary);
        }

        .slide {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 1s ease-in-out;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 1;
        }

        .slide.active {
            opacity: 1;
            z-index: 2;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: var(--slide-filter);
        }

        .slide-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to top, var(--slide-overlay), rgba(0,0,0,0));
            color: white;
            padding: 30px 40px;
            font-size: 2em;
            font-weight: 700;
            text-align: center;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
            z-index: 3;
            box-sizing: border-box;
        }

        /* --- Content Container (Main Grid Layout) --- */
        .content-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            margin-bottom: 50px;
        }

        main {
            background-color: var(--bg-secondary);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-medium);
            transition: background-color 0.3s ease;
        }

        aside {
            background-color: var(--bg-secondary);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-medium);
            transition: background-color 0.3s ease;
        }

        main h2, aside h2 {
            font-size: 2em;
            color: var(--accent-blue);
            margin-bottom: 25px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
            font-weight: 700;
        }

        /* --- News Item Styling --- */
        .news-item {
            display: flex;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--border-color);
            align-items: flex-start;
        }

        .news-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .news-item img {
            width: 180px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 20px;
            flex-shrink: 0;
            box-shadow: 0 2px 8px var(--shadow-light);
        }

        .news-item h2, .news-item h3 {
            font-size: 1.5em;
            color: var(--text-primary);
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        aside .news-item h3 {
            font-size: 1.2em;
            margin-bottom: 5px;
        }

        .news-item p {
            font-size: 0.95em;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .news-item p.date-author {
            font-size: 0.8em;
            color: var(--text-tertiary);
            margin-top: 5px;
        }

        .read-more {
            display: inline-block;
            background-color: var(--accent-green);
            color: #fff;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .read-more:hover {
            background-color: var(--accent-blue);
        }
        
        /* Specific styling for sidebar news items */
        aside .news-item {
            flex-direction: column;
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 15px;
        }
        
        aside .news-item img {
            width: 100%;
            height: 150px;
            margin-right: 0;
            margin-bottom: 10px;
        }

        /* --- Footer Styles --- */
        .site-footer {
            background-color: #1a1a1a;
            color: white;
            padding: 40px 20px 0;
            font-family: 'Inter', Arial, sans-serif;
            margin-top: auto;
            flex-shrink: 0;
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            max-width: 1200px;
            margin: 0 auto;
            gap: 30px;
            padding-bottom: 30px;
        }

        .footer-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 20px;
        }

        .footer-section h3 {
            color: var(--accent-green);
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid var(--accent-green);
            padding-bottom: 5px;
            display: inline-block;
        }
        
        .footer-section h4 {
            color: var(--accent-green);
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .footer-section p {
            font-size: 0.95em;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .footer-section ul {
            list-style: none;
            padding: 0;
        }

        .footer-section ul li a {
            color: white;
            text-decoration: none;
            margin-bottom: 8px;
            display: block;
            font-size: 0.9em;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: var(--accent-green);
        }

        .social-icons a {
            color: white;
            font-size: 24px;
            margin-right: 15px;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: var(--accent-green);
        }

        /* Journalist Team Section in Footer */
        .footer-section .team-section {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
        }

        .footer-section .member {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            width: 100%;
            max-width: none;
            text-align: left;
        }

        .footer-section .member:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .footer-section .member img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-green);
            flex-shrink: 0;
            margin-bottom: 0;
        }

        .footer-section .member h4 {
            font-size: 1em;
            margin-bottom: 5px;
            color: var(--accent-green);
        }

        .footer-section .member p {
            font-size: 0.8em;
            margin-bottom: 0;
            line-height: 1.4;
        }

        /* Alliance Section in Footer */
        .footer-section .alliance h4 {
            color: var(--accent-green);
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        
        .footer-section .alliance img {
            max-width: 100px;
            height: auto;
            margin-top: 0;
        }

        /* Footer Bottom Bar */
        .footer-bottom {
            background-color: #0d0d0d;
            padding: 15px 20px;
            font-size: 0.85em;
            text-align: center;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom .wrapper {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer-bottom p {
            margin: 0;
        }

        /* --- Responsive Design for Content --- */
        @media (max-width: 992px) {
            .content-container {
                grid-template-columns: 1fr;
            }
            aside {
                margin-top: 30px;
            }
        }

        @media (max-width: 576px) {
            .page-heading h1 {
                font-size: 2.5em;
            }
            .page-heading p {
                font-size: 0.9em;
            }
            .slider {
                height: 300px;
            }
            .slide-caption {
                font-size: 1.5em;
                padding: 20px;
            }
            .news-item {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }
            .news-item img {
                margin-right: 0;
                margin-bottom: 15px;
            }
            .news-item h2, .news-item h3 {
                font-size: 1.3em;
            }
            .read-more {
                width: 100%;
                text-align: center;
            }
            .footer-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
            }

            .footer-section {
                min-width: unset;
                width: 90%;
            }

            .footer-section h3 {
                margin-top: 20px;
            }

            .social-icons {
                margin-bottom: 20px;
            }
            .footer-section ul {
                text-align: center;
            }
            .footer-section ul li a {
                display: inline-block;
                margin: 0 8px;
            }

            .footer-section .member {
                flex-direction: column;
                text-align: center;
            }
            .footer-section .member img {
                margin-bottom: 10px;
            }
            .footer-bottom .wrapper {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <div class="nav-left">
            <div class="logo-box">
                <img src="../images/claudia (1).gif" alt="Alliance Logo">
            </div>
            <div class="site-title">ALLIANCE</div>
        </div>
        <div class="nav-toggle" id="navToggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="nav-right" id="navRight">
            <ul class="nav-links">
                <li><a href="../index.php">All</a></li>
                <li><a href="./local.php" class="active">Local News</a></li>
                <li><a href="../pages/sport.php">Sports</a></li>
                <li><a href="../pages/entertain.php">Entertainment</a></li>
                <li><a href="../pages/announcement.php">Announcements</a></li>
            </ul>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
        </div>
    </div>
    
    <div class="wrapper">
        <div class="slider" id="update-slider">
            <?php if (!empty($slider_news)): ?>
                <?php foreach ($slider_news as $index => $slide): ?>
                    <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= getImagePath($slide['media_url']) ?>" alt="<?= htmlspecialchars($slide['news_title']) ?>" />
                        <div class="slide-caption"><?= htmlspecialchars($slide['news_title']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="slide active">
                    <img src="../images/placeholder.jpg" alt="No slider news available." style="filter: none;">
                    <div class="slide-caption">No Recent Local News Available for Slider</div>
                </div>
            <?php endif; ?>
        </div>

        <div class="content-container">
            <main>
                <h2>Top Stories</h2>
                <?php if (!empty($top_stories)): ?>
                    <?php foreach ($top_stories as $news): ?>
                        <article class="news-item">
                            <img src="<?= getImagePath($news['media_url']) ?>" alt="<?= htmlspecialchars($news['news_title']) ?>" />
                            <div>
                                <h2><?= htmlspecialchars($news['news_title']) ?></h2>
                                <p><?= htmlspecialchars(substr($news['news_content'], 0, 150)) ?><?= strlen($news['news_content']) > 150 ? '...' : '' ?></p>
                                <p class="date-author">Posted on <?= date("F j, Y", strtotime($news['created_at'])) ?><?php if ($news['posted_by']) { echo ' by ' . htmlspecialchars($news['posted_by']); } ?></p>
                                <a class="read-more" href="../pages/news.php?id=<?= $news['news_id'] ?>">Read More</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No top stories available for this category.</p>
                <?php endif; ?>
            </main>

            <aside>
                <h2>Other Updates</h2>
                <?php if (!empty($other_news)): ?>
                    <?php foreach ($other_news as $news): ?>
                        <article class="news-item">
                            <img src="<?= getImagePath($news['media_url']) ?>" alt="<?= htmlspecialchars($news['news_title']) ?>" />
                            <div>
                                <h3><?= htmlspecialchars($news['news_title']) ?></h3>
                                <p><?= htmlspecialchars(substr($news['news_content'], 0, 100)) ?><?= strlen($news['news_content']) > 100 ? '...' : '' ?></p>
                                <p class="date-author">Posted on <?= date("F j, Y", strtotime($news['created_at'])) ?></p>
                                <a class="read-more" href="../pages/news.php?id=<?= $news['news_id'] ?>">Read More</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No other news available for this category.</p>
                <?php endif; ?>
            </aside>
        </div>
    </div>

    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>APPEAL Newspaper</h3>
                <p>NEWS THAT MATTERS, DELIEVERED FRESH, straight from the heart of HHS.</p>
                <p>Stay informed, stay engaged, and join our community of informed citizens.</p>
                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                    <a href="#" aria-label="Twitter"><i class="bi bi-twitter"></i></a>
                    <a href="https://www.instagram.com/appealnews" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>

            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="local.php">Local News</a></li>
                    <li><a href="sport.php">Sports</a></li>
                    <li><a href="entertain.php">Entertainment</a></li>
                    <li><a href="announcement.php">Announcement</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>Our Journalists</h3>
                <div class="team-section" id="team">
                    <div class="member">
                        <img src="../images/eric.jpg" alt="Eric Bombo Chocola">
                        <div>
                            <h4>ERIC BOMBO CHOCOLA</h4>
                            <p>HOPE HAVEN RWANDA, YES! TOGETHER WE MAKE A BETTER WORLD</p>
                        </div>
                    </div>
                    <div class="member">
                        <img src="../images/journal.jpg" alt="Nicole">
                        <div>
                            <h4>Nicole</h4>
                            <p>World of information, World of life</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="footer-section">
                <h3>Partners & Community</h3>
                <div class="alliance">
                    <h4>HAND IN HAND WITH THE ALLIANCE</h4>
                    <img src="../images/logo.png" alt="Alliance Logo">
                </div>
            </div>
        </div>

        <div class="footer-bottom">
            <div class="wrapper">
                <p>&copy; 2025 APPEAL Newspaper. All Rights Reserved.</p>
                <p>Designed and Developed by Alliance Dev Team</p>
            </div>
        </div>
    </footer>

    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;

        // Check for saved theme preference or default to 'light'
        const currentTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-theme', currentTheme);

        themeToggle.addEventListener('click', () => {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';
            
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
        });

        // Slider Functionality
        const slides = document.querySelectorAll('.slider .slide');
        let currentSlide = 0;
        const slideInterval = 5000; // 5 seconds

        function showSlide(index) {
            slides.forEach((slide, i) => {
                slide.classList.toggle('active', i === index);
            });
        }

        function nextSlide() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }

        if (slides.length > 0) {
            showSlide(currentSlide);
            setInterval(nextSlide, slideInterval);
        }

        // Navigation Toggle (Hamburger menu)
        const navToggle = document.getElementById('navToggle');
        const navRight = document.getElementById('navRight');

        if (navToggle && navRight) {
            navToggle.addEventListener('click', () => {
                navRight.classList.toggle('active');
            });

            // Close nav menu when a link is clicked (for mobile)
            const navLinks = navRight.querySelectorAll('a');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    navRight.classList.remove('active');
                });
            });
        }
    </script>
</body>
</html>
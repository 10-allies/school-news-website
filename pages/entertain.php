<?php

include 'db2.php';

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$mainNews = null;
$mainSql = "SELECT n.news_id, n.news_title, n.news_content, n.created_at, m.media_url AS image_url, a.author_display_name AS author
            FROM news n
            LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type = 'image'
            LEFT JOIN authors a ON n.author_id = a.author_id
            WHERE n.category_id = (SELECT category_id FROM news_category WHERE category_name = 'Entertainment')
            ORDER BY n.created_at DESC LIMIT 1";
$mainResult = $conn->query($mainSql);
if ($mainResult && $mainResult->num_rows > 0) {
    $mainNews = $mainResult->fetch_assoc();
}

$sideNews = [];
$sideSql = "SELECT n.news_id, n.news_title, n.news_content, n.created_at, m.media_url AS image_url, a.author_display_name AS author
            FROM news n
            LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type = 'image'
            LEFT JOIN authors a ON n.author_id = a.author_id
            WHERE n.category_id = (SELECT category_id FROM news_category WHERE category_name = 'Entertainment')
            ORDER BY n.created_at DESC LIMIT 1, 4";
$sideResult = $conn->query($sideSql);
if ($sideResult && $sideResult->num_rows > 0) {
    while ($row = $sideResult->fetch_assoc()) {
        $sideNews[] = $row;
    }
}

$gridNews = [];
$gridSql = "SELECT n.news_id, n.news_title, n.news_content, n.created_at, m.media_url AS image_url
            FROM news n
            LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type = 'image'
            WHERE n.category_id = (SELECT category_id FROM news_category WHERE category_name = 'Entertainment')
            ORDER BY n.created_at DESC LIMIT 5, 12"; 
$gridResult = $conn->query($gridSql);
if ($gridResult && $gridResult->num_rows > 0) {
    while ($row = $gridResult->fetch_assoc()) {
        $gridNews[] = $row;
    }
}

$sliderNews = [];
$sliderSql = "SELECT n.news_id, n.news_title, n.news_content, m.media_url AS image_url, a.author_display_name AS author
              FROM news n
              LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type = 'image'
              LEFT JOIN authors a ON n.author_id = a.author_id
              WHERE n.category_id = (SELECT category_id FROM news_category WHERE category_name = 'Entertainment')
              AND m.media_url IS NOT NULL 
              ORDER BY n.created_at DESC LIMIT 10";
$sliderResult = $conn->query($sliderSql);
if ($sliderResult && $sliderResult->num_rows > 0) {
    while ($row = $sliderResult->fetch_assoc()) {
        $sliderNews[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entertainment | APPEAL</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* --- CSS Variables for Theme Colors --- */
        :root {
            --bg-primary: #f0f2f5;
            --bg-secondary: #ffffff;
            --bg-overlay: rgba(255, 255, 255, 0.95);
            --text-primary: #333;
            --text-secondary: #555;
            --text-tertiary: #666;
            --text-quaternary: #777;
            --border-color: #e0e0e0;
            --shadow-light: rgba(0,0,0,0.1);
            --shadow-medium: rgba(0,0,0,0.08);
            --accent-green: #6cfa3a;
            --accent-blue: #0056b3;
            --accent-pink: #e91e63;
            --hover-bg: #f8f9fa;
            --grid-bg: #fafafa;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-overlay: rgba(29, 29, 29, 0.95);
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --text-tertiary: #909090;
            --text-quaternary: #808080;
            --border-color: #404040;
            --shadow-light: rgba(0,0,0,0.3);
            --shadow-medium: rgba(0,0,0,0.2);
            --accent-green: #6cfa3a;
            --accent-blue: #4a9eff;
            --accent-pink: #f06292;
            --hover-bg: #3a3a3a;
            --grid-bg: #252525;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', Arial, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .background-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--bg-overlay);
            z-index: -1;
            transition: background 0.3s ease;
        }

        /* --- Top Navigation Bar --- */
        .top-nav {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 5px var(--shadow-light);
            padding: 0 20px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-box {
            background-color: var(--accent-green);
            height: 100px;
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

        .nav-links {
            display: flex;
            gap: 25px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-primary);
            font-weight: 600;
            font-size: 1em;
            position: relative;
            padding-bottom: 5px;
            transition: color 0.2s ease-in-out;
        }

        .nav-links a:hover {
            color: var(--accent-pink);
        }

        .nav-links a:hover::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
            background-color: var(--accent-pink);
        }

        /* Theme Toggle Button */
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

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 28px;
            color: var(--text-primary);
            cursor: pointer;
        }

        /* --- Wrapper --- */
        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* --- Page Header --- */
        .page-header {
            text-align: center;
            padding: 40px 20px;
            background: linear-gradient(135deg, var(--accent-pink), var(--accent-blue));
            color: white;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px var(--shadow-light);
        }

        .page-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .page-header p {
            font-size: 1.1em;
            max-width: 700px;
            margin: 0 auto;
        }

        /* --- Main Content Area --- */
        .main-content-area {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
            margin-bottom: 40px;
        }

        .main-news {
            background-color: var(--bg-secondary);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-medium);
            transition: background-color 0.3s ease;
        }

        .featured-news img {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px var(--shadow-light);
        }

        .featured-content h2 {
            font-size: 2em;
            margin-bottom: 10px;
        }

        .featured-content h2 a {
            color: var(--text-primary);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .featured-content h2 a:hover {
            color: var(--accent-pink);
        }

        .article-meta {
            color: var(--text-quaternary);
            font-size: 0.9em;
            margin-bottom: 15px;
        }

        .featured-content p {
            font-size: 1.05em;
            line-height: 1.7;
            color: var(--text-secondary);
        }

        .featured-content a {
            color: var(--accent-pink);
            font-weight: 600;
        }

        /* --- Sidebar --- */
        .sidebar {
            background-color: var(--bg-secondary);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-medium);
            transition: background-color 0.3s ease;
        }

        .sidebar h2 {
            font-size: 1.8em;
            color: var(--accent-blue);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
        }

        .sidebar-item {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--border-color);
        }

        .sidebar-item:last-child {
            border-bottom: none;
        }

        .sidebar-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            flex-shrink: 0;
        }

        .sidebar-text a {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95em;
            line-height: 1.3;
            display: block;
            margin-bottom: 5px;
            transition: color 0.3s ease;
        }

        .sidebar-text a:hover {
            color: var(--accent-pink);
        }

        .sidebar-text p {
            font-size: 0.85em;
            color: var(--text-tertiary);
        }

        /* --- Slider --- */
        .slider {
            position: relative;
            overflow: hidden;
            border-radius: 12px;
            margin-bottom: 40px;
            box-shadow: 0 4px 15px var(--shadow-light);
        }

        .slides {
            display: flex;
            transition: transform 0.5s ease-in-out;
        }

        .slide-item {
            flex-shrink: 0;
            position: relative;
        }

        .slide-item img {
            width: 100%;
            height: 450px;
            object-fit: cover;
            filter: brightness(70%);
        }

        .slider-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0));
            color: white;
            padding: 30px;
            font-size: 1.1em;
        }

        .slider-caption a {
            color: var(--accent-green);
            font-weight: 600;
        }

        .prev, .next {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background-color: rgba(0,0,0,0.5);
            color: white;
            border: none;
            padding: 15px 20px;
            cursor: pointer;
            font-size: 24px;
            z-index: 10;
            transition: background-color 0.3s ease;
        }

        .prev:hover, .next:hover {
            background-color: rgba(0,0,0,0.8);
        }

        .prev {
            left: 10px;
        }

        .next {
            right: 10px;
        }

        /* --- Grid Container --- */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .grid-item {
            background-color: var(--grid-bg);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px var(--shadow-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        }

        .grid-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 20px var(--shadow-medium);
        }

        .grid-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .grid-item a {
            display: block;
            padding: 15px;
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 600;
            font-size: 1em;
            line-height: 1.4;
            transition: color 0.3s ease;
        }

        .grid-item:hover a {
            color: var(--accent-pink);
        }

        .no-news {
            text-align: center;
            color: var(--text-tertiary);
            padding: 40px;
            font-size: 1.1em;
        }

        /* --- Footer Styles --- */
        .site-footer {
            background-color: #1a1a1a;
            color: white;
            padding: 40px 20px 0;
            margin-top: 60px;
            font-family: 'Poppins', Arial, sans-serif;
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

        .team-section {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease, transform 0.6s ease;
        }

        .member {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: rgba(255, 255, 255, 0.1);
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }

        .member:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .member img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--accent-green);
        }

        .member h4 {
            font-size: 1em;
            margin-bottom: 5px;
            color: var(--accent-green);
        }

        .member p {
            font-size: 0.8em;
            margin-bottom: 0;
            line-height: 1.4;
        }

        .alliance h4 {
            color: var(--accent-green);
            margin-bottom: 10px;
            font-size: 1.1em;
        }

        .alliance img {
            max-width: 100px;
            height: auto;
            margin-top: 0;
        }

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

        /* --- Responsive Design --- */
        @media (max-width: 992px) {
            .main-content-area {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 2.5em;
            }
        }

        @media (max-width: 768px) {
            .menu-toggle {
                display: block;
            }

            .nav-links {
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

            .nav-links.active {
                display: flex;
            }

            .nav-links a {
                padding: 10px 20px;
                width: 100%;
                text-align: center;
            }

            .theme-toggle {
                margin: 15px auto 0;
            }

            .page-header h1 {
                font-size: 2em;
            }

            .page-header p {
                font-size: 0.95em;
            }

            .featured-news img {
                height: 300px;
            }

            .slide-item img {
                height: 300px;
            }

            .grid-container {
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 15px;
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

            .member {
                flex-direction: column;
                text-align: center;
            }

            .footer-bottom .wrapper {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            .page-header h1 {
                font-size: 1.8em;
            }

            .grid-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="background-overlay"></div>

    <div class="top-nav">
        <div class="logo-container">
            <div class="logo-box">
                <img src="../images/claudia (1).gif" alt="Alliance Logo">
            </div>
            <span class="site-title">APPEAL</span>
        </div>
        <nav class="nav-links" id="navLinks">
            <a href="../index.php">All</a>
            <a href="../local/local.php">Local news</a>
            <a href="sport.php">Sports</a>
            <a href="./entertain.php">Entertainment</a>
            <a href="announcement.php">Announcement</a>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
        </nav>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu">☰</button>
    </div>

    <div class="wrapper">
        <header class="page-header">
            <h1>Entertainment Hub</h1>
            <p>Your daily dose of celebrity gossip, movie reviews, music updates, and trending entertainment news.</p>
        </header>

        <div class="main-content-area">
            <main class="main-news">
                <?php if ($mainNews): ?>
                    <article class="featured-news">
                        <img class="main1" src="<?= $mainNews['image_url'] ? htmlspecialchars($mainNews['image_url']) : '../uploads/default.png' ?>" alt="<?= htmlspecialchars($mainNews['news_title']) ?>">
                        <div class="featured-content">
                            <h2><a href="news.php?id=<?= $mainNews['news_id'] ?>"><?= htmlspecialchars($mainNews['news_title']) ?></a></h2>
                            <p class="article-meta">By: <?= htmlspecialchars($mainNews['author']) ?> | <?= date('F j, Y', strtotime($mainNews['created_at'])) ?></p>
                            <p><?= nl2br(htmlspecialchars(substr($mainNews['news_content'], 0, 250))) ?>... <a href='news.php?id=<?= $mainNews['news_id'] ?>'>Read more</a></p>
                        </div>
                    </article>
                <?php else: ?>
                    <p class="no-news">No main entertainment news found.</p>
                <?php endif; ?>
            </main>

            <aside class="sidebar">
                <h2>Latest Updates</h2>
                <div class="sidebar-news">
                    <?php if (!empty($sideNews)): ?>
                        <?php foreach ($sideNews as $row): ?>
                            <div class="sidebar-item">
                                <img src="<?= $row['image_url'] ? htmlspecialchars($row['image_url']) : '../images/placeholder.jpg' ?>" alt="News Thumbnail">
                                <div class="sidebar-text">
                                    <a href="news.php?id=<?= $row['news_id'] ?>"><?= htmlspecialchars($row['news_title']) ?></a>
                                    <p><?= nl2br(htmlspecialchars(substr($row['news_content'], 0, 80))) ?>...</p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p class="no-news">No other entertainment news available.</p>
                    <?php endif; ?>
                </div>
            </aside>
        </div>

        <?php if (!empty($sliderNews)): ?>
            <section class="slider">
                <button class="prev" aria-label="Previous slide">&#10094;</button>
                <div class="slides">
                    <?php 
                    if (count($sliderNews) > 1) {
                        $lastActualItem = $sliderNews[count($sliderNews) - 1];
                        ?>
                        <div class="slide-item">
                            <img src="<?= $lastActualItem['image_url'] ? htmlspecialchars($lastActualItem['image_url']) : '../images/placeholder.jpg' ?>"
                                 data-news-id="<?= $lastActualItem['news_id'] ?>"
                                 id="lastClone">
                            <div class="slider-caption">
                                <p><?= nl2br(htmlspecialchars(substr($lastActualItem['news_content'], 0, 100))) ?>... <a href='news.php?id=<?= $lastActualItem['news_id'] ?>'>Read more</a></p>
                            </div>
                        </div>
                    <?php } ?>

                    <?php foreach ($sliderNews as $index => $item): ?>
                        <div class="slide-item">
                            <img src="<?= $item['image_url'] ? htmlspecialchars($item['image_url']) : '../images/placeholder.jpg' ?>"
                                 data-news-id="<?= $item['news_id'] ?>"
                                 <?php if ($index === 0) echo 'id="firstActual"'; ?>
                                 <?php if ($index === count($sliderNews) - 1) echo 'id="lastActual"'; ?>>
                            <div class="slider-caption">
                                <p><?= nl2br(htmlspecialchars(substr($item['news_content'], 0, 150))) ?>... <a href='news.php?id=<?= $item['news_id'] ?>'>Read more</a></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php 
                    if (count($sliderNews) > 1) {
                        $firstActualItem = $sliderNews[0];
                        ?>
                        <div class="slide-item">
                            <img src="<?= $firstActualItem['image_url'] ? htmlspecialchars($firstActualItem['image_url']) : '../images/placeholder.jpg' ?>"
                                 data-news-id="<?= $firstActualItem['news_id'] ?>"
                                 id="firstClone">
                            <div class="slider-caption">
                                <p><?= nl2br(htmlspecialchars(substr($firstActualItem['news_content'], 0, 150))) ?>... <a href='news.php?id=<?= $firstActualItem['news_id'] ?>'>Read more</a></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <button class="next" aria-label="Next slide">&#10095;</button>
            </section>
        <?php else: ?>
            <p class="no-news" style="text-align: center; margin: 40px 0;">No entertainment images for the slider.</p>
        <?php endif; ?>

        <?php if (!empty($gridNews)): ?>
            <section class="grid-container">
                <?php foreach ($gridNews as $row): ?>
                    <div class="grid-item">
                        <img src="<?= $row['image_url'] ? htmlspecialchars($row['image_url']) : '../images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($row['news_title']) ?>">
                        <a href='news.php?id=<?= $row['news_id'] ?>'><?= htmlspecialchars($row['news_title']) ?></a>
                    </div>
                <?php endforeach; ?>
            </section>
        <?php else: ?>
            <p class="no-news" style="text-align: center; margin: 40px 0;">No additional entertainment news to display in the grid.</p>
        <?php endif; ?>

    </div>

    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>APPEAL Newspaper</h3>
                <p>NEWS THAT MATTERS, DELIVERED FRESH, straight from the heart of HHS.</p>
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
                    <li><a href="#">Local News</a></li>
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

        document.addEventListener('DOMContentLoaded', function() {
            // --- Mobile Menu Toggle Logic ---
            const menuToggle = document.getElementById('menuToggle');
            const navLinks = document.getElementById('navLinks');

            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                    if (navLinks.classList.contains('active')) {
                        menuToggle.innerHTML = '✖';
                        document.body.style.overflowY = 'hidden';
                    } else {
                        menuToggle.innerHTML = '☰';
                        document.body.style.overflowY = 'auto';
                    }
                });

                navLinks.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (navLinks.classList.contains('active')) {
                            navLinks.classList.remove('active');
                            menuToggle.innerHTML = '☰'; 
                            document.body.style.overflowY = 'auto';
                        }
                    });
                });

                window.addEventListener('resize', function() {
                    if (window.innerWidth > 768 && navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        menuToggle.innerHTML = '☰';
                        document.body.style.overflowY = 'auto';
                    }
                });
            }

            // --- Slider Logic ---
            const slidesContainer = document.querySelector('.slider .slides');
            const prevBtn = document.querySelector('.slider .prev');
            const nextBtn = document.querySelector('.slider .next');
            
            if (slidesContainer && prevBtn && nextBtn) { 
                let slides = Array.from(document.querySelectorAll('.slider .slides .slide-item'));
                const numOriginalSlides = slides.length - 2;

                if (numOriginalSlides > 0) {
                    slides = Array.from(document.querySelectorAll('.slider .slides .slide-item'));
                }
               
                let currentIndex = 1; 
                slidesContainer.style.width = (slides.length * 100) + '%';
                slides.forEach(slide => {
                    slide.style.width = (100 / slides.length) + '%';
                });
                slidesContainer.style.transform = `translateX(${-currentIndex * (100 / slides.length)}%)`;

                function updateSlider() {
                    slidesContainer.style.transition = "transform 0.5s ease-in-out";
                    slidesContainer.style.transform = `translateX(${-currentIndex * (100 / slides.length)}%)`;
                }

                slidesContainer.addEventListener('transitionend', function() {
                    if (slides[currentIndex].id === 'firstClone') {
                        slidesContainer.style.transition = "none";
                        currentIndex = 1;
                        slidesContainer.style.transform = `translateX(${-currentIndex * (100 / slides.length)}%)`;
                    }
                    else if (slides[currentIndex].id === 'lastClone') {
                        slidesContainer.style.transition = "none";
                        currentIndex = numOriginalSlides;
                        slidesContainer.style.transform = `translateX(${-currentIndex * (100 / slides.length)}%)`;
                    }
                });

                nextBtn.addEventListener('click', function() {
                    if (currentIndex >= slides.length - 1) {
                        currentIndex = numOriginalSlides + 1;
                    }
                    currentIndex++;
                    updateSlider();
                });

                prevBtn.addEventListener('click', function() {
                    if (currentIndex <= 0) { 
                        currentIndex = 0; 
                    }
                    currentIndex--;
                    updateSlider();
                });

                let slideInterval = setInterval(function() {
                    if (currentIndex >= slides.length - 1) {
                        currentIndex = 0; 
                    }
                    currentIndex++;
                    updateSlider();
                }, 5000);

                slidesContainer.addEventListener('mouseenter', () => clearInterval(slideInterval));
                slidesContainer.addEventListener('mouseleave', () => {
                    slideInterval = setInterval(function() {
                        if (currentIndex >= slides.length - 1) {
                            currentIndex = 0;
                        }
                        currentIndex++;
                        updateSlider();
                    }, 5000);
                });
            }

            // Animate team section on load
            const teamSection = document.getElementById("team");
            if (teamSection) {
                setTimeout(() => {
                    teamSection.style.opacity = "1";
                    teamSection.style.transform = "translateY(0)";
                }, 100);
            }
        });

        function showContent(event, contentId) {
            event.preventDefault();
            alert("This section is under construction for: " + contentId);
        }
    </script>
</body>
</html>
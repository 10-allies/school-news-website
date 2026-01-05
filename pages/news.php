<?php
include 'db2.php'; 

$id = intval($_GET['id']);


$sql_news = "SELECT 
                n.news_id, 
                n.news_title, 
                n.news_content,
                n.created_at,
                a.author_display_name,
                nc.category_name,
                s.section_name
            FROM 
                news n
            LEFT JOIN 
                authors a ON n.author_id = a.author_id
            LEFT JOIN
                news_category nc ON n.category_id = nc.category_id
            LEFT JOIN
                sections s ON n.section_id = s.section_id
            WHERE 
                n.news_id = ?";

$stmt_news = $conn->prepare($sql_news);
if (!$stmt_news) {
    die("Prepare failed for news details: " . $conn->error);
}
$stmt_news->bind_param("i", $id);
$stmt_news->execute();
$result_news = $stmt_news->get_result();

if ($result_news && $result_news->num_rows > 0) {
    $news = $result_news->fetch_assoc();
} else {
    header("Location: ../index.php?error=newsnotfound");
    exit();
}
$stmt_news->close();

$sql_media = "SELECT media_type, media_url, media_caption FROM media WHERE news_id = ?";
$stmt_media = $conn->prepare($sql_media);
if (!$stmt_media) {
    die("Prepare failed for media: " . $conn->error);
}
$stmt_media->bind_param("i", $id);
$stmt_media->execute();
$result_media = $stmt_media->get_result();

$media_items = [];
if ($result_media) {
    while($row = $result_media->fetch_assoc()) {
        $media_items[] = $row;
    }
}
$stmt_media->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($news['news_title']); ?> - APPEAL News</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* --- CSS Variables for Theme Colors --- */
        :root {
            --bg-primary: #f0f2f5;
            --bg-secondary: #ffffff;
            --text-primary: #333;
            --text-secondary: #555;
            --text-tertiary: #777;
            --border-color: #e0e0e0;
            --shadow-light: rgba(0,0,0,0.1);
            --shadow-medium: rgba(0,0,0,0.08);
            --accent-green: #6cfa3a;
            --accent-blue: #0056b3;
            --hover-bg: #f8f9fa;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --text-tertiary: #909090;
            --border-color: #404040;
            --shadow-light: rgba(0,0,0,0.3);
            --shadow-medium: rgba(0,0,0,0.2);
            --accent-green: #6cfa3a;
            --accent-blue: #4a9eff;
            --hover-bg: #3a3a3a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
            line-height: 1.6;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* --- Top Navigation Bar --- */
        .top-nav {
            background-color: var(--bg-secondary);
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 2px 5px var(--shadow-light);
            position: sticky;
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .top-nav .wrapper {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            height: 100px;
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
            list-style: none;
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
            color: var(--accent-blue);
        }

        .nav-links a:hover::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
            background-color: var(--accent-blue);
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

        /* --- Article Container --- */
        .article-container {
            max-width: 900px;
            margin: 40px auto;
            background-color: var(--bg-secondary);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-medium);
            transition: background-color 0.3s ease;
        }

        .article-header h1 {
            font-size: 2.5em;
            color: var(--text-primary);
            margin-bottom: 20px;
            line-height: 1.3;
            font-weight: 800;
        }

        .article-meta {
            color: var(--text-tertiary);
            font-size: 0.95em;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .category-tag {
            background-color: var(--accent-green);
            color: white;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 0.9em;
        }

        .article-media-figure {
            margin: 30px 0;
            text-align: center;
        }

        .article-image {
            width: 100%;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 4px 12px var(--shadow-light);
        }

        .article-video {
            width: 100%;
            max-height: 500px;
            border-radius: 8px;
            box-shadow: 0 4px 12px var(--shadow-light);
        }

        figcaption {
            margin-top: 10px;
            font-size: 0.9em;
            color: var(--text-tertiary);
            font-style: italic;
        }

        .article-content {
            font-size: 1.1em;
            line-height: 1.8;
            color: var(--text-secondary);
            margin: 30px 0;
        }

        .article-content p {
            margin-bottom: 20px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background-color: var(--accent-green);
            color: white;
            padding: 12px 24px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
            margin-top: 30px;
        }

        .back-link:hover {
            background-color: var(--accent-blue);
        }

        /* --- Footer Styles --- */
        .site-footer {
            background-color: #1a1a1a;
            color: white;
            padding: 40px 20px 0;
            margin-top: 60px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 30px;
            margin-bottom: 30px;
        }

        .footer-section h3 {
            color: var(--accent-green);
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid var(--accent-green);
            padding-bottom: 5px;
            display: inline-block;
        }

        .footer-section p,
        .footer-section ul {
            font-size: 0.95em;
            line-height: 1.6;
        }

        .footer-section ul {
            list-style: none;
        }

        .footer-section ul li {
            margin-bottom: 8px;
        }

        .footer-section ul li a {
            color: white;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .footer-section ul li a:hover {
            color: var(--accent-green);
        }

        .social-icons {
            display: flex;
            gap: 15px;
            margin-top: 15px;
        }

        .social-icons a {
            color: white;
            font-size: 24px;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: var(--accent-green);
        }

        .alliance {
            text-align: center;
            margin-top: 30px;
        }

        .alliance img {
            max-width: 100px;
            height: auto;
        }

        .footer-bottom {
            background-color: #0d0d0d;
            padding: 15px 20px;
            text-align: center;
            font-size: 0.85em;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        .footer-bottom .wrapper {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        /* --- Responsive Design --- */
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

            .nav-links li {
                width: 100%;
                text-align: center;
                padding: 10px 0;
            }

            .theme-toggle {
                margin: 15px auto 0;
            }

            .article-container {
                padding: 20px;
                margin: 20px 15px;
            }

            .article-header h1 {
                font-size: 1.8em;
            }

            .footer-content {
                grid-template-columns: 1fr;
                text-align: center;
            }

            .footer-bottom .wrapper {
                flex-direction: column;
                text-align: center;
            }
        }

        @media (max-width: 480px) {
            .article-header h1 {
                font-size: 1.5em;
            }

            .article-content {
                font-size: 1em;
            }
        }
    </style>
</head>
<body>
    <div class="top-nav">
        <div class="wrapper">
            <div class="logo-container">
                <div class="logo-box">
                    <img src="../images/claudia (1).gif" alt="Alliance Logo">
                </div>
                <div class="site-title">ALLIANCE</div>
            </div>
            <button class="menu-toggle" aria-label="Toggle navigation">&#9776;</button>
            <ul class="nav-links">
                <li><a href="../index.php">All</a></li>
                <li><a href="../index.php?category=Local">Local news</a></li>
                <li><a href="sport.php">Sports</a></li>
                <li><a href="entertain.php">Entertainment</a></li>
                <li><a href="#" onclick="showContent(event, 'anounce')">School Announcement</a></li>
                <li><button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button></li>
            </ul>
        </div>
    </div>

    <div class="article-container">
        <div class="article-header">
            <h1><?php echo htmlspecialchars($news['news_title']); ?></h1>
            <p class="article-meta">
                Published on: <?php echo date("F j, Y", strtotime($news['created_at'])); ?> 
                <?php if ($news['author_display_name']): ?>
                    by <span class="category-tag"><?php echo htmlspecialchars($news['author_display_name']); ?></span>
                <?php endif; ?>
            </p>
        </div>

        <?php 
        if (!empty($media_items)): 
            foreach ($media_items as $media): 
                if ($media['media_type'] == 'image'): ?>
                    <figure class="article-media-figure">
                        <img src="../uploads/<?php echo htmlspecialchars($media['media_url']); ?>" 
                             alt="<?php echo htmlspecialchars($media['media_caption'] ?: $news['news_title']); ?>" 
                             class="article-image">
                        <?php if (!empty($media['media_caption'])): ?>
                            <figcaption><?php echo htmlspecialchars($media['media_caption']); ?></figcaption>
                        <?php endif; ?>
                    </figure>
                <?php elseif ($media['media_type'] == 'video'): ?>
                    <figure class="article-media-figure">
                        <video controls class="article-video">
                            <source src="../uploads/<?php echo htmlspecialchars($media['media_url']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                        <?php if (!empty($media['media_caption'])): ?>
                            <figcaption><?php echo htmlspecialchars($media['media_caption']); ?></figcaption>
                        <?php endif; ?>
                    </figure>
                <?php 
                endif;
            endforeach;
        endif;
        ?>

        <div class="article-content">
            <p><?php echo nl2br(htmlspecialchars($news['news_content'])); ?></p>
        </div>

        <a href="javascript:history.back()" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to News
        </a>
    </div>

    <footer class="site-footer">
        <div class="wrapper">
            <div class="footer-content">
                <div class="footer-section about">
                    <h3>About APPEAL News</h3>
                    <p>NEWS THAT MATTERS, DELIVERED FRESH</p>
                    <div class="social-icons">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-linkedin"></i></a>
                    </div>
                </div>

                <div class="footer-section links">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="../index.php">Home</a></li>
                        <li><a href="sport.php">Sports News</a></li>
                        <li><a href="entertain.php">Entertainment News</a></li>
                        <li><a href="announcement.php">School Announcements</a></li>
                    </ul>
                </div>

                <div class="footer-section instagram-feed">
                    <h3>Stay Connected</h3>
                    <p style="font-size: 0.85em; color: #aaa; margin-top: 15px;">Stay connected for daily updates!</p>
                </div>

                <div class="footer-section contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i> Kigali, Rwanda</p>
                    <p><i class="fas fa-phone-alt"></i> +000 000 000</p>
                    <p><i class="fas fa-envelope"></i> alliance@gmail.com</p>
                </div>
            </div>

            <div class="alliance">
                <img src="../images/claudia (1).gif" alt="Alliance Logo">
            </div>

            <div class="footer-bottom">
                <div class="wrapper">
                    <p>&copy; <?php echo date("Y"); ?> ALLIANCE News. All rights reserved.</p>
                    <p>Designed with <i class="fas fa-heart" style="color: #e74c3c;"></i> by Alliance Dev Team</p>
                </div>
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

        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const navLinks = document.querySelector('.nav-links');

            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });

                // Close menu when a link is clicked
                navLinks.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        if (navLinks.classList.contains('active')) {
                            navLinks.classList.remove('active');
                        }
                    });
                });
            }
        });

        function showContent(event, contentId) {
            event.preventDefault();
            alert("This section is under construction for: " + contentId);
        }
    </script>
</body>
</html>
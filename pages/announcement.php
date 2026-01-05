<?php
include '../connection/connect.php';
session_start();

$announcements = [];
$error_message = '';

try {
    $stmt = $pdo->prepare("
        SELECT
            n.news_id,
            n.news_title,
            n.news_content,
            n.created_at,
            m.media_type,
            m.media_url
        FROM news n
        LEFT JOIN media m ON n.news_id = m.news_id
        WHERE n.category_id = 4
        ORDER BY n.created_at DESC
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "⚠ Error fetching announcements: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Announcements</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* --- CSS Variables for Theme Colors --- */
        :root {
            --bg-primary: #f4f4f4;
            --bg-secondary: #ffffff;
            --bg-overlay: rgba(255, 255, 255, 0.95);
            --bg-announcement: rgba(255, 255, 255, 0.9);
            --bg-container: rgba(255, 255, 255, 0.2);
            --text-primary: #333;
            --text-secondary: #555;
            --text-tertiary: #777;
            --border-color: #e0e0e0;
            --shadow-light: rgba(0,0,0,0.1);
            --shadow-medium: rgba(0,0,0,0.2);
            --shadow-hover: rgba(0,0,0,0.3);
            --accent-green: #6cfa3a;
            --accent-blue: #0056b3;
            --accent-hover: #1a73e8;
            --hover-bg: #f8f9fa;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-overlay: rgba(29, 29, 29, 0.95);
            --bg-announcement: rgba(45, 45, 45, 0.95);
            --bg-container: rgba(45, 45, 45, 0.3);
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --text-tertiary: #909090;
            --border-color: #404040;
            --shadow-light: rgba(0,0,0,0.3);
            --shadow-medium: rgba(0,0,0,0.4);
            --shadow-hover: rgba(0,0,0,0.5);
            --accent-green: #6cfa3a;
            --accent-blue: #4a9eff;
            --accent-hover: #5a9eff;
            --hover-bg: #3a3a3a;
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
            color: var(--text-primary);
            display: flex;
            flex-direction: column;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .wrapper {
            flex: 1;
            width: 100%;
            max-width: 1250px;
            margin: 0 auto;
            position: relative;
        }

        /* --- Top Navigation Bar --- */
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
            top: 0;
            z-index: 1000;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        .nav-left {
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
            font-size: 1em;
            position: relative;
            padding-bottom: 5px;
            transition: color 0.2s ease-in-out;
        }

        .nav-links a:hover {
            color: var(--accent-hover);
        }

        .nav-links a:hover::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
            background-color: var(--accent-hover);
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

        /* --- Content Container --- */
        .content-container {
            width: 90%;
            margin: 30px auto;
            background: var(--bg-container);
            padding: 40px 30px;
            border-radius: 15px;
            box-shadow: 0 4px 15px var(--shadow-light);
            backdrop-filter: blur(10px);
            text-align: center;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            padding-bottom: 50px;
            align-items: start;
            transition: background 0.3s ease;
        }
        
        h2 {
            grid-column: 1 / -1;
            text-align: center;
            color: var(--text-primary);
            margin-bottom: 25px;
            font-size: 2.5em;
            text-shadow: 1px 1px 2px var(--shadow-light);
            font-weight: 800;
        }
        
        .announcement {
            position: relative;
            background: var(--bg-announcement);
            padding: 25px;
            color: var(--text-primary);
            font-weight: 600;
            border-radius: 12px;
            box-shadow: 2px 2px 10px var(--shadow-medium);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            margin-top: 20px;
            text-align: left;
            display: flex;
            flex-direction: column;
            min-height: 280px;
            justify-content: space-between;
            border: 1px solid var(--border-color);
        }

        .announcement:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 4px 4px 20px var(--shadow-hover);
        }

        .announcement h3 {
            color: var(--accent-blue);
            margin-bottom: 15px;
            font-size: 1.5em;
            word-wrap: break-word;
            line-height: 1.3;
            font-weight: 700;
        }

        .announcement p {
            line-height: 1.7;
            margin-bottom: 15px;
            flex-grow: 1;
            color: var(--text-secondary);
            font-weight: 400;
        }

        .announcement small {
            color: var(--text-tertiary);
            font-size: 0.85em;
            display: block;
            text-align: right;
            margin-top: 15px;
            font-weight: 500;
        }

        .announcement img {
            max-width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: contain;
            display: block;
            margin: 15px auto 10px auto;
            border-radius: 8px;
            box-shadow: 0 2px 8px var(--shadow-light);
        }

        .announcement a {
            color: var(--accent-blue);
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .announcement a:hover {
            text-decoration: underline;
            color: var(--accent-hover);
        }

        .announcement video {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 15px 0;
            box-shadow: 0 2px 8px var(--shadow-light);
        }

        .document-link {
            background: var(--hover-bg);
            padding: 12px;
            border-radius: 6px;
            margin: 15px 0;
            border: 1px dashed var(--border-color);
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            font-weight: 700;
            grid-column: 1 / -1;
            background: rgba(231, 76, 60, 0.1);
            padding: 20px;
            border-radius: 8px;
        }

        .no-announcements {
            text-align: center;
            color: var(--text-secondary);
            padding: 40px 20px;
            grid-column: 1 / -1;
            font-size: 1.2em;
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

        .team-section {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 10px;
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

            .top-nav {
                justify-content: space-between;
            }

            .site-title {
                margin-left: 10px;
            }

            .content-container {
                width: 95%;
                padding: 30px 20px;
                grid-template-columns: 1fr;
            }

            h2 {
                font-size: 2em;
            }

            .announcement {
                min-height: 200px;
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

            .member {
                flex-direction: column;
                text-align: center;
            }

            .member img {
                margin-bottom: 10px;
            }

            .footer-bottom .wrapper {
                flex-direction: column;
            }
        }

        @media (max-width: 480px) {
            h2 {
                font-size: 1.8em;
            }

            .content-container {
                padding: 20px 15px;
            }

            .announcement h3 {
                font-size: 1.3em;
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
                <li><a href="../local/local.php">Local news</a></li>
                <li><a href="sport.php">Sports</a></li>
                <li><a href="entertain.php">Entertainment</a></li>
                <li><a href="./announcement.php">Announcement</a></li>
            </ul>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
        </div>
    </div>

    <div class="wrapper">
        <div class="content-container">
            <h2>📢 School Announcements</h2>

            <?php if (isset($error_message) && !empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php else: ?>
                <?php if ($announcements): ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement">
                            <h3><?php echo htmlspecialchars($announcement['news_title']); ?></h3>
                            
                            <?php
                            if (!empty(trim($announcement['news_content']))) {
                                echo '<p>' . nl2br(htmlspecialchars($announcement['news_content'])) . '</p>';
                            }
                            ?>

                            <?php
                            if (!empty($announcement['media_url']) && $announcement['media_url'] !== 'uploads/default.jpg') {
                                $media_url_clean = htmlspecialchars($announcement['media_url']);
                                $display_media_url = $media_url_clean;

                                if ($announcement['media_type'] === 'image') {
                                    echo '<img src="' . $display_media_url . '" alt="Announcement Image">';
                                } elseif ($announcement['media_type'] === 'document') { 
                                    echo '<p class="document-link">📎 Attached Document: <a href="' . $display_media_url . '" target="_blank">' . basename($display_media_url) . '</a></p>';
                                } elseif ($announcement['media_type'] === 'video') {
                                    echo '<video controls>';
                                    echo '<source src="' . $display_media_url . '" type="video/mp4">'; 
                                    echo 'Your browser does not support the video tag.';
                                    echo '</video>';
                                }
                            }
                            ?>

                            <small>📅 Posted on: <?php echo htmlspecialchars($announcement['created_at']); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-announcements">📭 No announcements available at this time.</p>
                <?php endif; ?>
            <?php endif; ?>
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

        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });

        // Navigation Toggle (Hamburger menu)
        const navToggle = document.getElementById('navToggle');
        const navRight = document.getElementById('navRight');

        navToggle.addEventListener('click', () => {
            navRight.classList.toggle('active');
        });

        // Close nav menu when a link is clicked
        document.querySelectorAll('.nav-links a').forEach(link => {
            link.addEventListener('click', () => {
                navRight.classList.remove('active');
            });
        });
    </script>
</body>
</html>
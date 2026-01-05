<?php
// Assuming db2.php contains your database connection logic
$conn = new mysqli("localhost", "root", "", "newsdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$article = null; // Initialize $article to null

// Check if news_id is provided in the URL and is a valid number
if (isset($_GET['news_id']) && is_numeric($_GET['news_id'])) {
    $news_id = (int)$_GET['news_id'];

    // Prepare and execute the SQL query to fetch the specific news article
    $stmt = $conn->prepare("SELECT n.news_title, n.news_content, n.created_at, m.media_url, a.author_display_name AS author
                            FROM news n
                            LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type = 'image'
                            LEFT JOIN authors a ON n.author_id = a.author_id
                            WHERE n.news_id = ? AND n.status = 'published'");
    $stmt->bind_param("i", $news_id); // 'i' for integer
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $article = $result->fetch_assoc();
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $article ? htmlspecialchars($article['news_title']) . ' | APPEAL' : 'News Article Not Found'; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* --- CSS Variables for Theme Colors --- */
        :root {
            --bg-primary: #f0f2f5;
            --bg-secondary: #ffffff;
            --bg-overlay: rgba(255, 255, 255, 0.95);
            --text-primary: #333;
            --text-secondary: #444;
            --text-tertiary: #666;
            --text-quaternary: #777;
            --border-color: #eee;
            --shadow-light: rgba(0,0,0,0.1);
            --shadow-medium: rgba(0,0,0,0.08);
            --accent-green: #6cfa3a;
            --accent-pink: #e91e63;
            --accent-pink-hover: #d81b60;
            --hover-bg: #f8f9fa;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-overlay: rgba(29, 29, 29, 0.95);
            --text-primary: #e0e0e0;
            --text-secondary: #d0d0d0;
            --text-tertiary: #b0b0b0;
            --text-quaternary: #909090;
            --border-color: #404040;
            --shadow-light: rgba(0,0,0,0.3);
            --shadow-medium: rgba(0,0,0,0.2);
            --accent-green: #6cfa3a;
            --accent-pink: #e91e63;
            --accent-pink-hover: #f06292;
            --hover-bg: #3a3a3a;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        /* --- Article Wrapper --- */
        .wrapper {
            max-width: 900px;
            margin: 20px auto;
            padding: 30px;
            background-color: var(--bg-secondary);
            border-radius: 12px;
            box-shadow: 0 4px 15px var(--shadow-light);
            flex-grow: 1;
            margin-top: 120px;
            transition: background-color 0.3s ease;
        }

        .article-image {
            width: 100%;
            max-height: 450px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px var(--shadow-light);
        }

        .article-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.8em;
            color: var(--text-primary);
            margin-bottom: 15px;
            line-height: 1.2;
        }

        .article-meta {
            font-size: 0.95em;
            color: var(--text-quaternary);
            margin-bottom: 25px;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 15px;
        }

        .article-content {
            font-size: 1.1em;
            line-height: 1.8;
            color: var(--text-secondary);
        }

        .article-content p {
            margin-bottom: 1em;
        }

        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: var(--accent-pink);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
            font-weight: 600;
        }

        .back-link:hover {
            background-color: var(--accent-pink-hover);
        }

        .not-found {
            text-align: center;
            padding: 50px;
            font-size: 1.5em;
            color: var(--text-tertiary);
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
        @media (max-width: 768px) {
            .wrapper {
                margin-top: 90px;
                padding: 15px;
            }

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

            .article-title {
                font-size: 2em;
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
            .article-title {
                font-size: 1.6em;
            }

            .article-content {
                font-size: 1em;
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
            <a href="#">Local news</a>
            <a href="./sport.php">Sports</a>
            <a href="entertain.php">Entertainment</a>
            <a href="announcement.php">Announcement</a>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
        </nav>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu">☰</button>
    </div>

    <div class="wrapper">
        <?php if ($article): ?>
            <article>
                <?php if ($article['media_url']): ?>
                    <img src="<?= htmlspecialchars($article['media_url']) ?>" alt="<?= htmlspecialchars($article['news_title']) ?>" class="article-image">
                <?php endif; ?>
                <h1 class="article-title"><?= htmlspecialchars($article['news_title']) ?></h1>
                <p class="article-meta">
                    <strong>By:</strong> <?= htmlspecialchars($article['author']) ?> |
                    <?= date('F j, Y', strtotime($article['created_at'])) ?>
                </p>
                <div class="article-content">
                    <?= nl2br(htmlspecialchars($article['news_content'])) ?>
                </div>
                <a href="javascript:history.back()" class="back-link">← Back to previous page</a>
            </article>
        <?php else: ?>
            <div class="not-found">
                <p>Sorry, the news article you are looking for could not be found.</p>
                <a href="javascript:history.back()" class="back-link">← Go back</a>
            </div>
        <?php endif; ?>
    </div>

    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>APPEAL Newspaper</h3>
                <p>Your daily dose of reliable news and captivating stories, straight from the heart of Rwanda.</p>
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

        // Mobile Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.getElementById('menuToggle');
            const navLinks = document.getElementById('navLinks');

            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                    if (navLinks.classList.contains('active')) {
                        menuToggle.innerHTML = '✖';
                    } else {
                        menuToggle.innerHTML = '☰';
                    }
                });

                // Close the menu when a link is clicked
                navLinks.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', () => {
                        if (navLinks.classList.contains('active')) {
                            navLinks.classList.remove('active');
                            menuToggle.innerHTML = '☰';
                        }
                    });
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
    </script>
</body>
</html>
<?php
$conn = new mysqli("localhost", "root", "", "newsdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get all sports sections
$sections = [];
$sql = "SELECT * FROM sections WHERE category_id = (SELECT category_id FROM news_category WHERE category_name = 'Sports')";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $sections[] = $row;
}

// Selected section logic
$selected_section_id = isset($_GET['section_id']) ? (int)$_GET['section_id'] : (isset($sections[0]['section_id']) ? $sections[0]['section_id'] : null);
$selected_section_name = "";
foreach ($sections as $section) {
    if ($section['section_id'] == $selected_section_id) {
        $selected_section_name = $section['section_name'];
        break;
    }
}

// News retrieval
$news = [];
if ($selected_section_id !== null) {
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
}

$main_news = count($news) > 0 ? $news[0] : null;
$side_news = array_slice($news, 1);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sports News - Alliance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* --- CSS Variables for Theme Colors --- */
        :root {
            --bg-primary: #f0f2f5;
            --bg-secondary: #ffffff;
            --bg-overlay: rgba(255, 255, 255, 0.95);
            --text-primary: #333;
            --text-secondary: #555;
            --text-tertiary: #777;
            --border-color: #e0e0e0;
            --shadow-light: rgba(0,0,0,0.1);
            --shadow-medium: rgba(0,0,0,0.08);
            --accent-green: #6cfa3a;
            --accent-blue: #0056b3;
            --hover-bg: #f8f9fa;
            --subnav-bg: #ffffff;
            --subnav-active: #e8f5e9;
        }

        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-overlay: rgba(29, 29, 29, 0.95);
            --text-primary: #e0e0e0;
            --text-secondary: #b0b0b0;
            --text-tertiary: #909090;
            --border-color: #404040;
            --shadow-light: rgba(0,0,0,0.3);
            --shadow-medium: rgba(0,0,0,0.2);
            --accent-green: #6cfa3a;
            --accent-blue: #4a9eff;
            --hover-bg: #3a3a3a;
            --subnav-bg: #252525;
            --subnav-active: #2a4a2a;
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
            position: relative;
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

        /* --- Wrapper & Sub-Navigation --- */
        .wrapper {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .subnav {
            background-color: var(--subnav-bg);
            padding: 15px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px var(--shadow-light);
            margin-bottom: 30px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            transition: background-color 0.3s ease;
        }

        .subnav a {
            text-decoration: none;
            color: var(--text-primary);
            padding: 8px 16px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .subnav a:hover {
            background-color: var(--hover-bg);
            color: var(--accent-blue);
        }

        .subnav a.active-section {
            background-color: var(--accent-green);
            color: white;
        }

        /* --- Container & Grid --- */
        .container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
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

        aside h2 {
            font-size: 1.8em;
            color: var(--accent-blue);
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
            padding-bottom: 10px;
        }

        /* --- News Items --- */
        .news-item {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px dashed var(--border-color);
        }

        .news-item:last-child {
            border-bottom: none;
        }

        .news-item img {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px var(--shadow-light);
        }

        aside .news-item img {
            max-height: 200px;
        }

        .news-item h2,
        .news-item h3 {
            font-size: 1.8em;
            color: var(--text-primary);
            margin-bottom: 10px;
            line-height: 1.3;
        }

        aside .news-item h3 {
            font-size: 1.3em;
        }

        .news-item p {
            font-size: 1em;
            color: var(--text-secondary);
            margin-bottom: 10px;
            line-height: 1.6;
        }

        .read-more-link {
            display: inline-block;
            background-color: var(--accent-green);
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        .read-more-link:hover {
            background-color: var(--accent-blue);
        }

        /* --- News Slider --- */
        .news-slider {
            position: relative;
            width: 100%;
            height: 400px;
            overflow: hidden;
            border-radius: 12px;
            margin-top: 30px;
            box-shadow: 0 4px 15px var(--shadow-light);
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
        }

        .slide.active {
            opacity: 1;
            z-index: 2;
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            filter: brightness(70%);
        }

        .slide-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.9), rgba(0,0,0,0));
            color: white;
            padding: 30px;
            font-size: 1.5em;
            font-weight: 700;
            text-align: center;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
            z-index: 3;
        }

        /* --- Footer Styles --- */
        .site-footer {
            background-color: #1a1a1a;
            color: white;
            padding: 40px 20px 0;
            margin-top: 60px;
            font-family: 'Inter', Arial, sans-serif;
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

            .container {
                grid-template-columns: 1fr;
            }

            .subnav {
                justify-content: center;
            }

            .news-slider {
                height: 300px;
            }

            .slide-caption {
                font-size: 1.2em;
                padding: 20px;
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
            .news-item h2 {
                font-size: 1.5em;
            }

            .news-slider {
                height: 250px;
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
            <a href="./sport.php">Sports</a>
            <a href="entertain.php">Entertainment</a>
            <a href="announcement.php">Announcement</a>
            <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark mode"></button>
        </nav>
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle navigation menu">☰</button>
    </div>

    <div class="wrapper">
        <div class="subnav">
            <?php foreach ($sections as $section): ?>
                <?php
                $link = 'sport.php?section_id=' . $section['section_id'];
                $active_class = ($section['section_id'] == $selected_section_id) ? ' active-section' : '';
                ?>
                <a href="<?= $link ?>" class="<?= htmlspecialchars($active_class) ?>"><?= htmlspecialchars($section['section_name']) ?></a>
            <?php endforeach; ?>
        </div>

        <div class="container">
            <main>
                <section id="main-news">
                    <?php if ($main_news): ?>
                        <article class="news-item">
                            <img src="<?= $main_news['media_url'] ? htmlspecialchars($main_news['media_url']) : '../images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($main_news['news_title']) ?>">
                            <h2><?= htmlspecialchars($main_news['news_title']) ?></h2>
                            <p><?= nl2br(htmlspecialchars(substr($main_news['news_content'], 0, 200))) ?>...</p>
                            <p><strong>Posted by:</strong> <?= htmlspecialchars($main_news['posted_by']) ?> | <?= date('F j, Y', strtotime($main_news['created_at'])) ?></p>
                            <a class="read-more-link" href="view_news.php?news_id=<?= $main_news['news_id'] ?>">Read full article →</a>
                        </article>
                    <?php else: ?>
                        <p>No main news available for "<?= htmlspecialchars($selected_section_name) ?>".</p>
                    <?php endif; ?>

                    <?php if (!empty($news) && count($news) > 1):?>
                        <div class="news-slider">
                            <?php foreach ($news as $index => $item): ?>
                                <?php if (!empty($item['media_url'])): ?>
                                    <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                                        <img src="<?= htmlspecialchars($item['media_url']) ?>" alt="<?= htmlspecialchars($item['news_title']) ?>">
                                        <div class="slide-caption"><?= htmlspecialchars($item['news_title']) ?></div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </main>

            <aside id="side-news">
                <h2>Other News</h2>
                <?php if (count($side_news) > 0): ?>
                    <?php foreach ($side_news as $item): ?>
                        <article class="news-item">
                            <img src="<?= $item['media_url'] ? htmlspecialchars($item['media_url']) : '../images/placeholder.jpg' ?>" alt="<?= htmlspecialchars($item['news_title']) ?>">
                            <h3><?= htmlspecialchars($item['news_title']) ?></h3>
                            <p><?= nl2br(htmlspecialchars(substr($item['news_content'], 0, 100))) ?>...</p>
                            <p><strong>Posted by:</strong> <?= htmlspecialchars($item['posted_by']) ?> | <?= date('F j, Y', strtotime($item['created_at'])) ?></p>
                            <a class="read-more-link" href="view_news.php?news_id=<?= $item['news_id'] ?>">Read full article →</a>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>No other news available for "<?= htmlspecialchars($selected_section_name) ?>".</p>
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
                            <h4>ERIC</h4>
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

        // News Slider Functionality
        document.addEventListener('DOMContentLoaded', function() {
            let slides = document.querySelectorAll('.news-slider .slide');
            let currentIndex = 0;

            function showSlide(index) {
                slides.forEach((slide, i) => {
                    slide.classList.remove('active');
                    if (i === index) {
                        slide.classList.add('active');
                    }
                });
            }

            function nextSlide() {
                currentIndex = (currentIndex + 1) % slides.length;
                showSlide(currentIndex);
            }

            if (slides.length > 0) {
                showSlide(currentIndex);
                setInterval(nextSlide, 4000);
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

        // Mobile Menu Toggle
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

            // Close menu when a link is clicked
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        menuToggle.innerHTML = '☰';
                    }
                });
            });
        }
    </script>
</body>
</html>
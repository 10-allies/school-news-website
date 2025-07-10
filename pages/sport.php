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
if ($selected_section_id !== null) { // Only fetch news if a section is selected
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
    <link rel="stylesheet" href="sport.css?v=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        <nav class="nav-links">
            <a href="../index.php">All</a>
            <a href="../local/local.php">Local news</a>
            <a href="./sport.php">Sports</a>
            <a href="entertain.php">Entertainment</a>
            <a href="announcement.php">Announcement</a>
        </nav>
        <button class="menu-toggle" aria-label="Toggle navigation menu">
            ☰
        </button>
    </div>
<div class="background-overlay"></div>
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
                        <h4>ERIC</h4>
                        <p>HOPE HAVEN RWANDA, YES! TOGETHER WE MAKE A BETTER WORLD</p>
                    </div>
                    <div class="member">
                        <img src="../images/journal.jpg" alt="Nicole">
                        <h4>Nicole</h4>
                        <p>World of information, World of life</p>
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
    <script src="script.js"></script>

    <script>
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
                showSlide(currentIndex); // Show the first slide immediately
                setInterval(nextSlide, 4000); // Change every 4 seconds
            }

            // Animate team section on load
            const teamSection = document.getElementById("team");
            if (teamSection) {
                 // Trigger animation after a slight delay to ensure CSS is rendered
                setTimeout(() => {
                    teamSection.style.opacity = "1";
                    teamSection.style.transform = "translateY(0)";
                }, 100);
            }
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            // Change the hamburger icon to 'X' and vice-versa
            if (navLinks.classList.contains('active')) {
                menuToggle.innerHTML = '✖'; // Unicode 'X' character
            } else {
                menuToggle.innerHTML = '☰'; // Unicode hamburger icon
            }
        });

        // Optional: Close the menu when a link is clicked (for single-page navigation or if desired)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                if (navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                    menuToggle.innerHTML = '☰'; // Reset icon
                }
            });
        });
    }
});
    </script>
       <script>
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
                showSlide(currentIndex); // Show the first slide immediately
                setInterval(nextSlide, 4000); // Change every 4 seconds
            }

            // Animate team section on load
            const teamSection = document.getElementById("team");
            if (teamSection) {
                 // Trigger animation after a slight delay to ensure CSS is rendered
                setTimeout(() => {
                    teamSection.style.opacity = "1";
                    teamSection.style.transform = "translateY(0)";
                }, 100);
            }
        });
    </script>
</body>
</html>

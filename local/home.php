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
    
    <link rel="stylesheet" href="style.css"> 
    
    <link rel="stylesheet" href="entertain.css?v=<?php echo time(); ?>"> 
</head>
<body>
    <div class="background-overlay"></div>

    <div class="top-nav">
        <div class="logo-container">
            <div class="logo-box">
                <img src="../images/claudia (1).gif" alt="Alliance Logo">
            </div>
            <span class="site-title">APPEAL</span> </div>
        <nav class="nav-links">
            <a href="../index.php">All</a>
            <a href="#">Local news</a>
            <a href="sport.php">Sports</a>
            <a href="./entertain.php">Entertainment</a>
            <a href="announcement.php">Announcement</a>
        </nav>
        <button class="menu-toggle" aria-label="Toggle navigation menu">
            â˜°
        </button>
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
                    // Add clone of last slide before the first for infinite loop effect
                    if (count($sliderNews) > 1) {
                        $lastActualItem = $sliderNews[count($sliderNews) - 1];
                        ?>
                        <div class="slide-item">
                            <img src="<?= $lastActualItem['image_url'] ? htmlspecialchars($lastActualItem['image_url']) : '../images/placeholder.jpg' ?>"
                                 alt="<?= htmlspecialchars($lastActualItem['news_title']) ?>"
                                 data-news-id="<?= $lastActualItem['news_id'] ?>"
                                 id="lastClone">
                            <div class="slider-caption">
                                <h2><a href="news.php?id=<?= $lastActualItem['news_id'] ?>"><?= htmlspecialchars($lastActualItem['news_title']) ?></a></h2>
                                <p><?= nl2br(htmlspecialchars(substr($lastActualItem['news_content'], 0, 100))) ?>... <a href='news.php?id=<?= $lastActualItem['news_id'] ?>'>Read more</a></p>
                            </div>
                        </div>
                    <?php } ?>

                    <?php foreach ($sliderNews as $index => $item): ?>
                        <div class="slide-item">
                            <img src="<?= $item['image_url'] ? htmlspecialchars($item['image_url']) : '../images/placeholder.jpg' ?>"
                                 alt="<?= htmlspecialchars($item['news_title']) ?>"
                                 data-news-id="<?= $item['news_id'] ?>"
                                 <?php if ($index === 0) echo 'id="firstActual"'; ?>
                                 <?php if ($index === count($sliderNews) - 1) echo 'id="lastActual"'; ?>>
                            <div class="slider-caption">
                                <h2><a href="news.php?id=<?= $item['news_id'] ?>"><?= htmlspecialchars($item['news_title']) ?></a></h2>
                                <p><?= nl2br(htmlspecialchars(substr($item['news_content'], 0, 150))) ?>... <a href='news.php?id=<?= $item['news_id'] ?>'>Read more</a></p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php 
                    // Add clone of first slide after the last for infinite loop effect
                    if (count($sliderNews) > 1) {
                        $firstActualItem = $sliderNews[0];
                        ?>
                        <div class="slide-item">
                            <img src="<?= $firstActualItem['image_url'] ? htmlspecialchars($firstActualItem['image_url']) : '../images/placeholder.jpg' ?>"
                                 alt="<?= htmlspecialchars($firstActualItem['news_title']) ?>"
                                 data-news-id="<?= $firstActualItem['news_id'] ?>"
                                 id="firstClone">
                            <div class="slider-caption">
                                <h2><a href="news.php?id=<?= $firstActualItem['news_id'] ?>"><?= htmlspecialchars($firstActualItem['news_title']) ?></a></h2>
                                <p><?= nl2br(htmlspecialchars(substr($firstActualItem['news_content'], 0, 150))) ?>... <a href='news.php?id=<?= $firstActualItem['news_id'] ?>'>Read more</a></p>
                            </div>
                        </div>
                    <?php } ?>
                </div>
                <button class="next" aria-label="Next slide">&#10095;</button>
            </section>
        <?php else: ?>
            <p class="no-news" style="text-align: center; margin: 40px 0; color: #666;">No entertainment images for the slider.</p>
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
            <p class="no-news" style="text-align: center; margin: 40px 0; color: #666;">No additional entertainment news to display in the grid.</p>
        <?php endif; ?>

    </div> 
    <footer class="site-footer">
        <div class="footer-content">
            <div class="footer-section">
                <h3>APPEAL Newspaper</h3>
                <p>NEWSTHTA MATTERS, DELIVERED FRESH, straight from the heart of HHS.</p>
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
                        <h4>ERIC BOMBO CHOCOLA</h4>
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
                  <!--  <p>JOIN THE ALLIANCE COMMUNITY:</p>
                    <p>
                        <a href="https://www.instagram.com/appealnews" class="instagram-link">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" alt="Instagram">
                            Appeal News Instagram
                        </a>
                    </p>-->
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
        // --- Mobile Menu Toggle Logic ---
        const menuToggle = document.querySelector('.menu-toggle');
        const navLinks = document.querySelector('.nav-links');

        if (menuToggle && navLinks) {
            menuToggle.addEventListener('click', function() {
                navLinks.classList.toggle('active');
                if (navLinks.classList.contains('active')) {
                    menuToggle.innerHTML = 'âœ–'; // Unicode 'X' character
                    document.body.style.overflowY = 'hidden'; // Prevent scrolling when menu is open
                } else {
                    menuToggle.innerHTML = 'â˜°'; // Unicode hamburger icon
                    document.body.style.overflowY = 'auto'; // Re-enable scrolling
                }
            });


            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
       
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                        menuToggle.innerHTML = 'â˜°'; 
                        document.body.style.overflowY = 'auto';
                    }
                });
            });


            window.addEventListener('resize', function() {
                if (window.innerWidth > 768 && navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                    menuToggle.innerHTML = 'â˜°';
                    document.body.style.overflowY = 'auto';
                }
            });
        }


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
                // If it's the cloned first slide (at end of slides array), jump to actual first
                if (slides[currentIndex].id === 'firstClone') {
                    slidesContainer.style.transition = "none";
                    currentIndex = 1; // Jump to the real first slide (index 1)
                    slidesContainer.style.transform = `translateX(${-currentIndex * (100 / slides.length)}%)`;
                }
                // If it's the cloned last slide (at beginning of slides array), jump to actual last
                else if (slides[currentIndex].id === 'lastClone') {
                    slidesContainer.style.transition = "none";
                    currentIndex = numOriginalSlides; // Jump to the real last slide
                    slidesContainer.style.transform = `translateX(${-currentIndex * (100 / slides.length)}%)`;
                }
            });

            nextBtn.addEventListener('click', function() {
                if (currentIndex >= slides.length - 1) { // If at the cloned first slide (effectively at the end)
                    currentIndex = numOriginalSlides + 1; // Set index to the last clone
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
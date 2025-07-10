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
    <link rel="stylesheet" href="news.css?v=1.0">
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

        <a href="javascript:history.back()" class="back-link"><i class="fas fa-arrow-left"></i> Back to News</a>
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
                   <!-- <h3>Follow Us on Instagram</h3>
                     <a href="https://instagram.com/your_school_instagram" target="_blank" class="instagram-link">
                        <img src="../images/instagram_logo.png" alt="Instagram Logo">
                        <span>@alliance_school_news</span>
                    </a>-->
                    <p style="font-size: 0.85em; color: #aaa; margin-top: 15px;">Stay connected for daily updates!</p>
                </div>

                <div class="footer-section contact">
                    <h3>Contact Us</h3>
                    <p><i class="fas fa-map-marker-alt"></i>Kigali, Rwanda</p>
                    <p><i class="fas fa-phone-alt"></i>+000 000 000</p>
                    <p><i class="fas fa-envelope"></i>alliance@gmail.com</p>
                </div>
            </div>

            <div class="alliance">
              <!--
                <h3>Our Team</h3>
                <div class="team-section">
                    <div class="member">
                        <img src="../im" alt="Team Member 1">
                        <h4>Mugisha Alex</h4>
                        <p>Editor-in-Chief</p>
                    </div>
                    <div class="member">
                        <img src="../images/team/member2.jpg" alt="Team Member 2">
                        <h4>Uwase Grace</h4>
                        <p>Sports Reporter</p>
                    </div>
                    <div class="member">
                        <img src="../images/team/member3.jpg" alt="Team Member 3">
                        <h4>Habimana David</h4>
                        <p>Tech Writer</p>
                    </div>
                    <div class="member">
                        <img src="../images/team/member4.jpg" alt="Team Member 4">
                        <h4>Kwizera Sarah</h4>
                        <p>Entertainment Editor</p>
                    </div>
                </div>
      -->
                <img src="../images/claudia (1).gif" alt="Alliance Logo" style="margin-top: 30px;">
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
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const navLinks = document.querySelector('.nav-links');

            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });
            }

            // Optional: Close menu when a link is clicked (for mobile)
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', function() {
                    // Check if the menu is currently active (i.e., on mobile)
                    if (navLinks.classList.contains('active')) {
                        navLinks.classList.remove('active');
                    }
                });
            });
        });

        function showContent(event, contentId) {
            event.preventDefault();
            alert("This section is under construction for: " + contentId);
            // In a real application, you'd load content here or navigate to a dedicated page
        }
    </script>
</body>
</html>
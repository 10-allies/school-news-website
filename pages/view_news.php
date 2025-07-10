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
    <link rel="stylesheet" href="sport.css?v=1.1"> <style>
        /* Specific styles for view_news.php, will override sport.css if conflicting */
        .wrapper {
            max-width: 900px;
            margin: 20px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            flex-grow: 1; /* Allows the wrapper to grow and push the footer down */
            margin-top: 120px; /* Adjust based on your top-nav height */
        }

        @media (max-width: 768px) {
            .wrapper {
                margin-top: 90px; /* Adjust for smaller screens */
                padding: 15px;
            }
        }

        .article-image {
            width: 100%;
            max-height: 450px; /* Capped height for consistency */
            object-fit: cover;
            border-radius: 8px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .article-title {
            font-family: 'Montserrat', sans-serif;
            font-size: 2.8em; /* Adjusted for better readability on single page */
            color: #333;
            margin-bottom: 15px;
            line-height: 1.2;
        }
        .article-meta {
            font-size: 0.95em;
            color: #777;
            margin-bottom: 25px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
        }
        .article-content {
            font-size: 1.1em;
            line-height: 1.8;
            color: #444;
        }
        .article-content p {
            margin-bottom: 1em;
        }
        .back-link {
            display: inline-block;
            margin-top: 30px;
            padding: 10px 20px;
            background-color: #e91e63; /* Your brand color */
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }
        .back-link:hover {
            background-color: #d81b60; /* Darker shade on hover */
        }
        .not-found {
            text-align: center;
            padding: 50px;
            font-size: 1.5em;
            color: #666;
        }

        /* Ensure body is flex container for footer to stick to bottom */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
        <nav class="nav-links">
            <a href="../index.php">All</a>
            <a href="#">Local news</a>
            <a href="./sport.php">Sports</a>
            <a href="entertain.php">Entertainment</a>
            <a href="announcement.php">Announcement</a>
        </nav>
        <button class="menu-toggle" aria-label="Toggle navigation menu">
            ☰
        </button>
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

            // Animate team section on load (same as your sport.php)
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
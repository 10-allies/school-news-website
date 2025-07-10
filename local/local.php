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
        // Or create it if that's part of the setup
        echo "<p style='color:red; text-align:center;'>Warning: 'Local news' category not found in the database. Please create it.</p>";
        // You might want to exit or set $category_news to empty array
        $category_news = []; // Ensure this is empty if category not found
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
                          LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type='image' -- Ensure we only get image media for display
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
$top_stories = array_slice($category_news, 0, 3); // Top 3 for top stories (can overlap with slider or fetch separately)
$other_news = array_slice($category_news, 3, 4); // Next 4 for "Other Updates"

// Close connection (PDO auto-closes when script ends, but explicit null helps)
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
    
    <style>
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
            background-color: #f0f2f5; /* Lighter background */
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            color: #333;
        }

        .wrapper {
            flex: 1; /* Allow wrapper to grow and push footer to bottom */
            width: 100%;
            max-width: 1250px; /* Consistent max width */
            margin: 0 auto;
            margin-top: 40px;
            position: relative;
            padding: 0 15px; /* Add some horizontal padding */
        }

        /* --- Top Navigation Bar (Header) --- */
  .top-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    height: 100px; /* Changed from 105px to 100px */
    border-bottom: 1px solid #e0e0e0;
    background-color: #ffffff;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    position: sticky;
    top: 5px;
    z-index: 1000;
}}

        .nav-left {
            display: flex;
            align-items: center;
        }

        .logo-box {
            background-color: #6cfa3a; /* Greenish-yellow from other headers */
            height: 100%;
            padding: 0 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-box img {
            height: 46px; /* Adjusted to fit the height of the nav */
            width: auto;
        }

        .site-title {
            font-size: 1.2em; /* Slightly larger */
            font-weight: 800; /* Bold */
            color: #6cfa3a; /* Greenish-yellow */
            margin-left: 15px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            margin-left: 50px;
            font-weight: 600; /* Semi-bold */
            list-style: none;
            padding: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: #333; /* Darker text */
            font-size: 1.0em;
            position: relative;
            padding-bottom: 5px;
            transition: color 0.2s ease-in-out;
        }

        .nav-links a:hover,
        .nav-links a.active { /* Added active state */
            color: #0056b3; /* Blue for hover/active */
        }

        .nav-links a:hover::after,
        .nav-links a.active::after { /* Underline for hover/active */
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
            background-color: #0056b3;
        }

        /* Toggle Button (Hamburger) for mobile */
        .nav-toggle {
            display: none; /* Hidden by default for desktop */
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
            background-color: #333;
            border-radius: 2px;
            transition: all 0.3s ease-in-out;
        }

        /* Responsive Mobile Navigation */
        @media (max-width: 768px) {
            .nav-toggle {
                display: flex; /* Show hamburger on mobile */
            }

            .nav-links {
                position: absolute;
                top: 65px; /* Below the header */
                left: 0;
                width: 100%;
                flex-direction: column;
                background-color: #ffffff;
                box-shadow: 0 5px 10px rgba(0,0,0,0.1);
                padding: 15px 0;
                border-top: 1px solid #e0e0e0;
                display: none; /* Hide by default */
                margin-left: 0;
            }

            .nav-links.active {
                display: flex; /* Show when active */
            }

            .nav-links li {
                width: 100%;
                text-align: center;
            }

            .nav-links a {
                padding: 10px 0;
            }

            .site-title {
                margin-left: 10px;
            }
        }

        /* --- Page Header / Heading --- */
        .page-heading {
            text-align: center;
            padding: 40px 20px;
            background-color: #e6e6e6; /* Light gray background */
            margin-bottom: 30px;
            box-shadow: inset 0 -2px 5px rgba(0,0,0,0.05);
            position: relative;
        }

        .page-heading::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(108, 250, 58, 0.1), rgba(0, 86, 179, 0.05)); /* Subtle gradient overlay */
            pointer-events: none;
        }

        .page-heading h1 {
            font-size: 3.2em; /* Larger, more impactful heading */
            color: #0056b3; /* Blue color */
            margin-bottom: 10px;
            font-weight: 800;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.05);
        }

        .page-heading p {
            font-size: 1.1em;
            color: #555;
            max-width: 700px;
            margin: 0 auto;
        }

        /* --- Slider Section --- */
        .slider {
            position: relative;
            width: 100%;
            height: 450px; /* Fixed height for consistency */
            overflow: hidden;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            margin-bottom: 40px;
            background-color: #fff; /* Fallback background */
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
            align-items: flex-end; /* Align caption to bottom */
            justify-content: center;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: 1; /* Ensure slides stack correctly */
        }

        .slide.active {
            opacity: 1;
            z-index: 2; /* Active slide on top */
        }

        .slide img {
            width: 100%;
            height: 100%;
            object-fit: cover; /* Cover the entire slide area */
            filter: brightness(70%); /* Slightly darken image for better text contrast */
        }

        .slide-caption {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            background: linear-gradient(to top, rgba(0,0,0,0.8), rgba(0,0,0,0)); /* Gradient background for caption */
            color: white;
            padding: 30px 40px;
            font-size: 2em; /* Larger caption text */
            font-weight: 700;
            text-align: center;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.6);
            z-index: 3; /* Above image */
            box-sizing: border-box; /* Include padding in width calculation */
        }

        /* --- Content Container (Main Grid Layout) --- */
        .content-container {
            display: grid;
            grid-template-columns: 2fr 1fr; /* Main content takes 2/3, sidebar 1/3 */
            gap: 40px; /* Space between columns */
            margin-bottom: 50px;
        }

        main {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        aside {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        main h2, aside h2 {
            font-size: 2em;
            color: #0056b3;
            margin-bottom: 25px;
            border-bottom: 2px solid #e0e0e0;
            padding-bottom: 10px;
            font-weight: 700;
        }

        /* --- News Item Styling --- */
        .news-item {
            display: flex;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #eee;
            align-items: flex-start; /* Align image and text at the top */
        }

        .news-item:last-child {
            border-bottom: none; /* No border for the last item */
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .news-item img {
            width: 180px; /* Fixed width for consistent image size */
            height: 120px; /* Fixed height */
            object-fit: cover; /* Crop to fit */
            border-radius: 8px;
            margin-right: 20px;
            flex-shrink: 0; /* Prevent image from shrinking */
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .news-item h2, .news-item h3 {
            font-size: 1.5em; /* Main news title */
            color: #222;
            margin-bottom: 8px;
            line-height: 1.3;
        }
        
        aside .news-item h3 {
             font-size: 1.2em; /* Smaller title for sidebar news */
             margin-bottom: 5px;
        }

        .news-item p {
            font-size: 0.95em;
            color: #555;
            margin-bottom: 10px;
        }

        .news-item p.date-author { /* Added a class for consistent date/author styling */
            font-size: 0.8em;
            color: #777;
            margin-top: 5px;
        }

        .read-more {
            display: inline-block;
            background-color: #6cfa3a; /* Greenish-yellow button */
            color: #fff;
            padding: 8px 15px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: background-color 0.3s ease;
        }

        .read-more:hover {
            background-color: #0056b3; /* Blue on hover */
        }
        
        /* Specific styling for sidebar news items */
        aside .news-item {
            flex-direction: column; /* Stack image and text vertically in sidebar */
            align-items: flex-start;
            margin-bottom: 25px;
            padding-bottom: 15px;
        }
        aside .news-item img {
            width: 100%; /* Image takes full width in sidebar */
            height: 150px;
            margin-right: 0;
            margin-bottom: 10px; /* Space below image */
        }

        /* --- Footer Styles --- */
        .site-footer {
            background-color: #004d00; /* Dark green background */
            color: white;
            padding: 40px 20px 0; /* Padding top, sides, no bottom padding for now */
            font-family: 'Inter', Arial, sans-serif;
            margin-top: auto; /* Pushes the footer to the bottom */
            flex-shrink: 0; /* Prevent footer from shrinking */
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            max-width: 1200px;
            margin: 0 auto;
            gap: 30px;
            padding-bottom: 30px; /* Padding above the bottom bar */
        }

        .footer-section {
            flex: 1;
            min-width: 250px;
            margin-bottom: 20px; /* Space between sections on wrap */
        }

        .footer-section h3 {
            color: #6cfa3a; /* Green for headings */
            margin-bottom: 15px;
            font-size: 1.3em;
            border-bottom: 2px solid #6cfa3a; /* Underline for headings */
            padding-bottom: 5px;
            display: inline-block; /* Make border-bottom fit content */
        }
         .footer-section h4 { /* For "Our Journalists" sub-heading */
            color: #6cfa3a; /* Green for headings */
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
            color: #6cfa3a; /* Hover color for links */
        }

        .social-icons a {
            color: white;
            font-size: 24px;
            margin-right: 15px;
            transition: color 0.3s ease;
        }

        .social-icons a:hover {
            color: #6cfa3a; /* Hover color for social icons */
        }

        /* Journalist Team Section in Footer */
        .footer-section .team-section {
            display: flex;
            flex-direction: column; /* Stack members vertically in footer */
            gap: 15px;
            margin-top: 10px;
        }

        .footer-section .member {
            display: flex;
            align-items: center;
            gap: 15px;
            background-color: rgba(255, 255, 255, 0.1); /* Slightly transparent background for members */
            padding: 10px;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            width: 100%; /* Take full width of its container */
            max-width: none; /* Override previous max-width */
            text-align: left; /* Align text left within member box */
        }

        .footer-section .member:hover {
            background-color: rgba(255, 255, 255, 0.2);
            transform: none; /* No lift effect in footer */
        }

        .footer-section .member img {
            width: 60px; /* Smaller circular image in footer */
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #6cfa3a;
            flex-shrink: 0; /* Prevent image from shrinking */
            margin-bottom: 0; /* Reset margin */
        }

        .footer-section .member h4 {
            font-size: 1em;
            margin-bottom: 5px;
            color: #6cfa3a;
        }

        .footer-section .member p {
            font-size: 0.8em;
            margin-bottom: 0;
            line-height: 1.4;
        }

        /* Alliance Section in Footer */
        .footer-section .alliance h4 {
            color: #6cfa3a;
            margin-bottom: 10px;
            font-size: 1.1em;
        }
        .footer-section .alliance img {
            max-width: 100px; /* Smaller alliance logo in footer */
            height: auto;
            margin-top: 0;
        }

        /* Footer Bottom Bar */
        .footer-bottom {
            background-color: #003300; /* Even darker green for bottom bar */
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
                grid-template-columns: 1fr; /* Stack columns on smaller screens */
            }
            aside {
                margin-top: 30px; /* Add space when stacked */
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
                flex-direction: column; /* Stack image and text in main content */
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
                min-width: unset; /* Remove min-width on small screens */
                width: 90%; /* Take more width on small screens */
            }

            .footer-section h3 {
                margin-top: 20px; /* Add space between stacked headings */
            }

            .social-icons {
                margin-bottom: 20px;
            }
            .footer-section ul {
                text-align: center; /* Center quick links */
            }
            .footer-section ul li a {
                display: inline-block; /* Allow wrapping for inline display */
                margin: 0 8px; /* Space between inline links */
            }

            .footer-section .member {
                flex-direction: column; /* Stack image and text in member on small screens */
                text-align: center;
            }
            .footer-section .member img {
                margin-bottom: 10px; /* Space below image */
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
        <ul class="nav-links" id="navLinks">
            <li><a href="../index.php">All</a></li>
            <li><a href="./local.php" class="active">Local News</a></li>
            <li><a href="../pages/sport.php">Sports</a></li>
            <li><a href="../pages/entertain.php">Entertainment</a></li>
            <li><a href="../pages/announcement.php">Announcements</a></li>
        </ul>
    </div>
    <div class="wrapper">
        <div class="slider" id="update-slider">
            <?php if (!empty($slider_news)): ?>
                <?php foreach ($slider_news as $index => $slide): ?>
                    <div class="slide <?= $index === 0 ? 'active' : '' ?>">
                        <?php 
                        $image_path = (!empty($slide['media_url']) && $slide['media_url'] !== 'uploads/default.jpg') 
                                        ? '../' . htmlspecialchars($slide['media_url']) // Prepend ../ if stored as uploads/filename.jpg
                                        : '../images/placeholder.jpg'; // Path to placeholder
                        ?>
                        <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($slide['news_title']) ?>" />
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
                            <?php 
                            $image_path = (!empty($news['media_url']) && $news['media_url'] !== 'uploads/default.jpg') 
                                            ? '../' . htmlspecialchars($news['media_url']) // Prepend ../
                                            : '../images/placeholder.jpg'; // Path to placeholder
                            ?>
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($news['news_title']) ?>" />
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
                            <?php 
                            $image_path = (!empty($news['media_url']) && $news['media_url'] !== 'uploads/default.jpg') 
                                            ? '../' . htmlspecialchars($news['media_url']) // Prepend ../
                                            : '../images/placeholder.jpg'; // Path to placeholder
                            ?>
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($news['news_title']) ?>" />
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
        const slides = document.querySelectorAll('.slider .slide'); // Corrected selector
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
            showSlide(currentSlide); // Show initial slide
            setInterval(nextSlide, slideInterval);
        }

        // Navigation Toggle (Hamburger menu)
        const navToggle = document.getElementById('navToggle');
        const navLinks = document.getElementById('navLinks');

        if (navToggle && navLinks) {
            navToggle.addEventListener('click', () => {
                navLinks.classList.toggle('active');
            });

            // Close nav menu when a link is clicked (for mobile)
            navLinks.querySelectorAll('a').forEach(link => {
                link.addEventListener('click', () => {
                    navLinks.classList.remove('active');
                });
            });
        }
    </script>
</body>
</html>

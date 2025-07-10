<?php
include '../connection/connect.php'; // Ensure your connection file is included
session_start(); // If you need session data on this page

$announcements = [];
$error_message = '';

try {
    // Select news items with category_id = 4 (Announcements)
    // LEFT JOIN with media to get associated files.
    // Order by created_at in descending order to show latest first.
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
    $error_message = "? Error fetching announcements: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Announcements</title>
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
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            display: flex;
            flex-direction: column;
        }

        /* Wrapper for main content to push footer down */
        .wrapper {
            flex: 1; /* Allow wrapper to grow and push footer to bottom */
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
            height: 65px;
            border-bottom: 1px solid #e0e0e0;
            background-color: #ffffff;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .nav-left {
            display: flex;
            align-items: center;
        }

        .logo-box {
            background-color: greenyellow;
            height: 100%;
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
            font-size: 18px;
            font-weight: bold;
            color: #6cfa3a;
            margin-left: 15px;
        }

        .nav-links {
            display: flex;
            gap: 25px;
            margin-left: 50px;
            font-weight: bold;
            list-style: none;
            padding: 0;
        }

        .nav-links a {
            text-decoration: none;
            color: black;
            font-size: 16px;
            position: relative;
            padding-bottom: 5px;
            transition: color 0.2s ease-in-out;
        }

        .nav-links a:hover {
            color: #1a73e8;
        }

        .nav-links a:hover::after {
            content: "";
            position: absolute;
            bottom: 0;
            left: 0;
            height: 2px;
            width: 100%;
            background-color: #1a73e8;
        }

        /* Toggle Button (Hamburger) */
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
            background-color: black;
            border-radius: 2px;
            transition: all 0.3s ease-in-out;
        }

        /* Responsive Mobile Navigation */
        @media (max-width: 768px) {
            .nav-toggle {
                display: flex;
            }

            .nav-links {
                position: absolute;
                top: 65px;
                left: 0;
                width: 100%;
                flex-direction: column;
                background-color: #ffffff;
                box-shadow: 0 5px 10px rgba(0,0,0,0.1);
                padding: 15px 0;
                border-top: 1px solid #e0e0e0;
                display: none;
                margin-left: 0;
            }

            .nav-links.active {
                display: flex;
            }

            .nav-links li {
                width: 100%;
                text-align: center;
            }

            .nav-links a {
                padding: 10px 0;
            }

            .top-nav {
                justify-content: space-between;
            }

            .site-title {
                margin-left: 10px;
            }
        }

        /* --- Custom Styling for Announcements Page Content --- */
        .content-container {
            width: 90%;
            margin: 20px auto;
            background: rgba(255, 255, 255, 0.2);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            backdrop-filter: blur(10px);
            text-align: center;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            padding-bottom: 70px;
            align-items: start;
        }
        
        h2 {
            grid-column: 1 / -1;
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 2.2em;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        
        .announcement {
            position: relative;
            background: rgba(255, 255, 255, 0.9);
            padding: 25px;
            color: #333;
            font-weight: bold;
            border-radius: 8px;
            box-shadow: 2px 2px 8px rgba(0,0,0,0.2);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top: 20px;
            text-align: left;
            display: flex;
            flex-direction: column;
            min-height: 250px;
            justify-content: space-between;
        }

        .announcement:hover {
            transform: translateY(-5px);
            box-shadow: 4px 4px 12px rgba(0,0,0,0.3);
        }

        /* Remove any old rope/hole styles */
       

        .announcement h3 {
            color: #0056b3;
            margin-bottom: 10px;
            font-size: 1.5em;
            word-wrap: break-word;
        }
        .announcement p {
            line-height: 1.6;
            margin-bottom: 15px;
            flex-grow: 1;
        }
        .announcement small {
            color: #777;
            font-size: 0.8em;
            display: block;
            text-align: right;
            margin-top: 10px;
        }
        .announcement img {
            max-width: 100%;
            height: auto;
            max-height: 250px;
            object-fit: contain;
            display: block;
            margin: 15px auto 10px auto;
            border-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        .announcement a {
            color: #007bff;
            text-decoration: none;
            font-weight: normal;
        }
        .announcement a:hover {
            text-decoration: underline;
        }
        .error-message {
            color: red;
            text-align: center;
            font-weight: bold;
            grid-column: 1 / -1;
        }
        .no-announcements {
            text-align: center;
            color: #555;
            padding: 20px;
            grid-column: 1 / -1;
        }

        /* --- Footer Styles --- */
        .site-footer {
            background-color: #004d00; /* Dark green background */
            color: white;
            padding: 40px 20px 0; /* Padding top, sides, no bottom padding for now */
            font-family: Arial, sans-serif;
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
            opacity: 1; /* Already visible in footer */
            transform: translateY(0); /* No initial transform needed here */
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

        /* Responsive adjustments for Footer */
        @media (max-width: 768px) {
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
            <li><a href="../local/local.php">Local news</a></li>
            <li><a href="sport.php">Sports</a></li>
            <li><a href="entertain.php">Entertainment</a></li>
            <li><a href="./announcement.php">Announcement</a></li>
        </ul>
    </div>

    <div class="wrapper">
        <div class="content-container">
            <h2>School Announcements</h2>

            <?php if (isset($error_message) && !empty($error_message)): ?>
                <p class="error-message"><?php echo $error_message; ?></p>
            <?php else: ?>
                <?php if ($announcements): ?>
                    <?php foreach ($announcements as $announcement): ?>
                        <div class="announcement">
                            <h3><?php echo htmlspecialchars($announcement['news_title']); ?></h3>
                            
                            <?php
                            // Display news_content only if it's not empty or just whitespace
                            if (!empty(trim($announcement['news_content']))) {
                                echo '<p>' . nl2br(htmlspecialchars($announcement['news_content'])) . '</p>';
                            }
                            ?>

                            <?php
                            // Check if there's a media URL and display based on type
                            if (!empty($announcement['media_url']) && $announcement['media_url'] !== 'uploads/default.jpg') {
                                $media_url_clean = htmlspecialchars($announcement['media_url']);
                                
                                // Ensure the path for displaying images is correct relative to this page.
                                // If announcement.php is in 'pages/' and uploads is in the root,
                                // '../uploads/' is correct.
                                $display_media_url = $media_url_clean;

                                if ($announcement['media_type'] === 'image') {
                                    echo '<img src="' . $display_media_url . '" alt="Announcement Image">';
                                } elseif ($announcement['media_type'] === 'document') { 
                                    echo '<p class="document-link">Attached Document: <a href="' . $display_media_url . '" target="_blank">' . basename($display_media_url) . '</a></p>';
                                } elseif ($announcement['media_type'] === 'video') {
                                    echo '<video controls style="max-width:100%; height:auto;">';
                                    echo '<source src="' . $display_media_url . '" type="video/mp4">'; 
                                    echo 'Your browser does not support the video tag.';
                                    echo '</video>';
                                }
                            }
                            ?>

                            <small>Posted on: <?php echo htmlspecialchars($announcement['created_at']); ?></small>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-announcements">No announcements available.</p>
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
        // Smooth scrolling for anchor links (optional)
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Team section animation (for the main content team section if any, but now also in footer)
        // Note: The footer's team section might not need this animation if it's always visible.
        // I've kept it as it was in your previous script.
        document.addEventListener('DOMContentLoaded', animateSections);

        function animateSections() {
            const teamSection = document.getElementById("team");
            if (teamSection) {
                teamSection.style.opacity = "1";
                teamSection.style.transform = "translateY(0)";
            }
        }

        // Navigation Toggle (Hamburger menu)
        const navToggle = document.getElementById('navToggle');
        const navLinks = document.getElementById('navLinks');

        navToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // Close nav menu when a link is clicked (for mobile)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinks.classList.remove('active');
            });
        });
    </script>
</body>
</html>

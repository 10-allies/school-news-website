<?php

if (!isset($_GET['from_splash'])) {
    header("Location: splash.html");
    exit();
}

include './connection/connect.php';
include './weather/weather_fetch.php'; 
session_start();

$database_error_message = '';
$announcements = [];

try {
    $stmt = $pdo->prepare("
        SELECT news_title, news_content, created_at, media.media_url, media.media_type
        FROM news
        LEFT JOIN media ON news.news_id = media.news_id
        WHERE category_id = 4 AND status = 'published'
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $database_error_message = "❌ Database Error: " . htmlspecialchars($e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>THE APPEAL - NEWS THAT MATTERS</title>
    <link rel="stylesheet" href="index.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="../images/10.png" type="image/x-icon">
<style>
 
        
        .img{
            width:30px;
            height:30px;
        }

        /* Theme Toggle Styles */
        .theme-toggle {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 30px;
            margin-left: 20px;
        }

        .theme-toggle input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .theme-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 30px;
        }

        .theme-slider:before {
            position: absolute;
            content: "";
            height: 22px;
            width: 22px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .theme-slider {
            background-color: #008a12;
        }

        input:checked + .theme-slider:before {
            transform: translateX(30px);
        }

        .theme-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 14px;
            transition: opacity 0.3s;
        }

        .theme-icon.sun {
            left: 8px;
            color: #ffd700;
        }

        .theme-icon.moon {
            right: 8px;
            color: #f0f0f0;
        }

        /* Dark Theme Styles */
        body.dark-theme {
            background-color: #1a1a1a;
            color: #e0e0e0;
        }

        body.dark-theme .wrapper {
            background-color: #2a2a2a;
        }

        body.dark-theme .top-nav {
            background-color: #1f1f1f;
            border-bottom-color: #333;
        }

        body.dark-theme .nav-links a {
            color: #e0e0e0;
        }

        body.dark-theme .nav-links a:hover {
            color: #008a12;
        }

        body.dark-theme .extra-content {
            background-color: #2a2a2a;
            color: #e0e0e0;
        }

        body.dark-theme .extra-content span {
            color: #00b318;
        }

        body.dark-theme .weather-container {
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            border: 1px solid #008a12;
            box-shadow: 0 8px 32px rgba(0, 138, 18, 0.2);
        }

        body.dark-theme .current-time {
            color: #00ff1a;
            text-shadow: 0 0 10px rgba(0, 255, 26, 0.5);
        }

        body.dark-theme .report-button {
            background: linear-gradient(135deg, #008a12 0%, #00b318 100%);
            color: #fff;
            border: 1px solid #00ff1a;
            box-shadow: 0 4px 15px rgba(0, 138, 18, 0.3);
            transition: all 0.3s ease;
        }

        body.dark-theme .report-button:hover {
            background: linear-gradient(135deg, #00b318 0%, #00d420 100%);
            box-shadow: 0 6px 20px rgba(0, 255, 26, 0.4);
            transform: translateY(-2px);
        }

        body.dark-theme .temperature {
            color: #00ff1a;
            text-shadow: 0 0 20px rgba(0, 255, 26, 0.6);
        }

        body.dark-theme .condition {
            color: #b0b0b0;
        }

        body.dark-theme .detail-item {
            background: rgba(0, 138, 18, 0.1);
            border: 1px solid rgba(0, 138, 18, 0.3);
            border-radius: 8px;
            padding: 10px;
        }

        body.dark-theme .detail-label {
            color: #00b318;
            font-weight: 600;
        }

        body.dark-theme .detail-item span:not(.detail-label) {
            color: #e0e0e0;
        }

        body.dark-theme .site-footer {
            background-color: #1f1f1f;
            color: #e0e0e0;
        }

        body.dark-theme .footer-section h3 {
            color: #00b318;
        }

        body.dark-theme .footer-section a {
            color: #b0b0b0;
        }

        body.dark-theme .footer-section a:hover {
            color: #00b318;
        }

        body.dark-theme .footer-bottom {
            background-color: #151515;
            border-top-color: #333;
        }
        body.dark-theme .the-body{
            background-color: #1a1a1a;
        }



</style>   
</head>
<body>

 <div class="background-overlay"></div>

    <div class="wrapper">
        <div class="top-nav">
            <div class="logo-container">
                <div class="logo-box">
                    <img src="./images/claudia (1).gif" alt="Alliance Logo">
                </div>
                <div class="site-title">APPEAL</div>
            </div>
            
            <!-- Theme Toggle Button -->
            <label class="theme-toggle">
                <input type="checkbox" id="theme-toggle-checkbox">
                <span class="theme-slider">
                    <i class="bi bi-sun-fill theme-icon sun"></i>
                    <i class="bi bi-moon-fill theme-icon moon"></i>
                </span>
            </label>
            
            <button class="menu-toggle" aria-label="Toggle navigation bar">
                &#9776;
            </button>
            
            <div class="nav-links">
                <a href="index.php">All</a>
                <a href="./local/local.php">Local news</a>
                <a href="./pages/sport.php">Sports</a>
                <a href="./pages/entertain.php">Entertainment</a>
                <a href="./pages/announcement.php">Announcement</a>
                <a href="./Ally ai/index.php"><img src="./images/icon.png"class="img" alt="Ally ai "></a>
            </div>
        </div>
        
        <section class="extra-content">
            <h2>Welcome to the controlling news hub. <span>THE ALLIANCE APPEAL</span></h2>
            <p>Stay updated with the latest global news in sports, entertainment, local news + Politics and also announcements from the staff and administration + student cabinet.</p>
        </section>

        <div class="the-body">
            <div class="weather-container" id="weatherContainer">
                <?php if (!empty($weather_data) && empty($weather_error_message)): ?>
                    <div class="current-weather">
                        <div class="current-time" id="current-time">
                            </div>
                        <button class="report-button" onclick="reportWeather()">KIGALI - RWANDA</button>
                    </div>
                    <div class="temperature">
                        <?php
                        
                            $weather_icon_code = isset($weather_data['weather'][0]['icon']) ? $weather_data['weather'][0]['icon'] : '01d';
                            $weather_icon_url = "http://openweathermap.org/img/wn/{$weather_icon_code}@2x.png";
                            $weather_description = isset($weather_data['weather'][0]['description']) ? $weather_data['weather'][0]['description'] : 'N/A';
                        ?>
                        <span id="temperature"><?php echo round($weather_data['main']['temp']); ?></span> <span style="font-size: 0.8em;">°C</span>
                    </div>
                    <div class="condition" id="condition">
                        <?php echo htmlspecialchars(ucwords($weather_description)); ?>
                    </div>
                    <div class="details">
                        <div class="detail-item"><span class="detail-label">Wind</span><br><span id="wind"><?php echo round($weather_data['wind']['speed'] * 3.6); ?> km/h</span></div>
                        <div class="detail-item"><span class="detail-label">Humidity</span><br><span id="humidity"><?php echo $weather_data['main']['humidity']; ?>%</span></div>
                        <div class="detail-item"><span class="detail-label">Feels like</span><br><span id="feels-like"><?php echo round($weather_data['main']['feels_like']); ?>°C</span></div>
                        <?php if (isset($weather_data['visibility'])): ?>
                            <div class="detail-item"><span class="detail-label">Visibility</span><br><span id="visibility"><?php echo ($weather_data['visibility'] / 1000); ?> km</span></div>
                        <?php endif; ?>
                        <div class="detail-item"><span class="detail-label">Pressure</span><br><span id="pressure"><?php echo $weather_data['main']['pressure']; ?> hPa</span></div>
                        <?php if (isset($weather_data['sys']['sunrise']) && isset($weather_data['sys']['sunset'])): ?>
                            <div class="detail-item"><span class="detail-label">Sunrise</span><br><span id="sunrise"><?php echo date('H:i A', $weather_data['sys']['sunrise']); ?></span></div>
                            <div class="detail-item"><span class="detail-label">Sunset</span><br><span id="sunset"><?php echo date('H:i A', $weather_data['sys']['sunset']); ?></span></div>
                        <?php endif; ?>
                    </div>
                <?php elseif (!empty($weather_error_message)): ?>
                    <p style="color: red; text-align: center; width: 100%;">Error loading weather: <?php echo htmlspecialchars($weather_error_message); ?></p>
                <?php else: ?>
                    <p style="text-align: center; width: 100%;">Loading weather data...</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

<footer class="site-footer">
    <div class="wrapper footer-content">
        <div class="footer-section footer-about">
            <div class="footer-logo">
                <img src="./images/claudia (1).gif" alt="Alliance Logo">
                <span class="site-title">APPEAL</span>
            </div>
            <p class="tagline">News that matters, Delivered Fresh</p>
            <p class="description">
              We bring you trusted and useful news to transform and advance your minds. "AMAKURU ATARIHO IVUMBI"
            </p>
            <div class="social-links">
                <a href="http://10.12.0.9" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                <a href="http://10.12.0.9/netgram" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a>
                <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
                <a href="#" aria-label="YouTube"><i class="bi bi-youtube"></i></a>
            </div>
        </div>

        <div class="footer-section footer-links">
            <h3>Quick Links</h3>
            <ul>
                <li><a href="../index.php">Home</a></li>
               <li><a href="./pages/announcement.php">Announcements</a></li>
                <li><a href="#">local news</a></li>
                <li><a href="./pages/sport.php">Sports</a></li>
            </ul>
        </div>

        <div class="footer-section footer-categories">
            <h3>Categories</h3>
            <ul>
                <li><a href="pages/category.php?id=1">Local News</a></li>
                <li><a href="pages/category.php?id=2">Sports</a></li>
                <li><a href="pages/category.php?id=3">Entertainment</a></li>
                <li><a href="./pages/announcement.php">Announcement</a></li>
            </ul>
        </div>

        <div class="footer-section footer-contact">
            <h3>Get In Touch</h3>
            <p><i class="icon-map-marker"></i> Kigali, Rwanda</p>
            <p><i class="icon-envelope"></i> <a href="mailto:info@alliancenews.com">alliance@gmail.com</a></p>
            <p><i class="icon-phone"></i> +000 000 000</p>
            <div class="newsletter-signup">
                <h4>Newsletter</h4>
                <p>Subscribe to our newsletter for daily updates!</p>
                <form action="#" method="POST">
                    <input type="email" placeholder="Your email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="wrapper">
            <p>&copy; <?php echo date('Y'); ?> Alliance News. All rights reserved.</p>
            <p><a href="#">Privacy Policy</a> | <a href="#">Terms of Service</a></p>
        </div>
    </div>
</footer>

    <script>
        // Theme Toggle Functionality
        const themeToggle = document.getElementById('theme-toggle-checkbox');
        const body = document.body;

        // Check for saved theme preference or default to light mode
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            body.classList.add('dark-theme');
            themeToggle.checked = true;
        }

        themeToggle.addEventListener('change', function() {
            if (this.checked) {
                body.classList.add('dark-theme');
                localStorage.setItem('theme', 'dark');
            } else {
                body.classList.remove('dark-theme');
                localStorage.setItem('theme', 'light');
            }
        });

        // Dummy function for 'Report weather' button
        function reportWeather() {
            alert('This button is a placeholder. You could implement functionality here to allow users to report current weather conditions, perhaps by sending data to your server.');
        }

        // Update current time every second (client-side)
        function updateCurrentTime() {
            const timeElement = document.getElementById('current-time');
            if (timeElement) {
                const now = new Date();
                const options = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
                timeElement.textContent = now.toLocaleTimeString('en-US', options);
            }
        }
        setInterval(updateCurrentTime, 1000);
        updateCurrentTime();
    </script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuToggle = document.querySelector('.menu-toggle');
            const navLinks = document.querySelector('.nav-links');

            if (menuToggle && navLinks) {
                menuToggle.addEventListener('click', function() {
                    navLinks.classList.toggle('active');
                });

                navLinks.querySelectorAll('a').forEach(link => {
                    link.addEventListener('click', function() {
                        if (navLinks.classList.contains('active')) {
                            navLinks.classList.remove('active');
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>
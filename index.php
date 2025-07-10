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
    <link rel="stylesheet" href="index.css?v=6.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="shortcut icon" href="../images/10.png" type="image/x-icon">
<style> .countdown-title {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 20px;
            color:#008a12;
            margin-top:1em;
        }

        #countdown {
            font-size: 2em;
            font-weight: bold;
            padding: 5px;
            border-radius: 10px;
            background-color: rgba(255, 255, 255, 0.2);
            display: inline-block;
            color:#555;
            
        }
</style>   
</head>
<body>

 <div class="background-overlay"></div>

    <div class="wrapper">
        <div class="top-nav">
                        <div class="logo-container"> <div class="logo-box">
                    <img src="./images/claudia (1).gif" alt="Alliance Logo">
                </div>
                <div class="site-title">APPEAL</div>
            </div> <button class="menu-toggle" aria-label="Toggle navigation bar">
                &#9776;
            </button>
                        <div class="nav-links">
                <a href="index.php">All</a>
                <a href="./local/local.php">Local news</a>
                <a href="./pages/sport.php">Sports</a>
                <a href="./pages/entertain.php">Entertainment</a>
                <a href="./pages/announcement.php">Announcement</a>
            </div>
        </div>
        <section class="extra-content">
            <h2>Welcome to the controlling news hub. <span>THE ALLIANCE APPEAL</span></h2>
            <p>Stay updated with the latest global news in sports, entertainment, local news + Politics and also announcements from the staff and administration + student cabinet.</p>
               <div class="countdown-title"> Mission: Going Home </div>
        <div id="countdown">Loading...</div>

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
                        
                            $weather_icon_code = isset($weather_data['weather'][0]['icon']) ? $weather_data['weather'][0]['icon'] : '01d'; // Default icon
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
    <a href="http://10.12.0.9/netgram" aria-label="Twitter"><i class="bi bi-twitter-x"></i></a> <a href="#" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
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
        setInterval(updateCurrentTime, 1000); // Update every second
        updateCurrentTime(); // Initial call to display time immediately on load
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');

    if (menuToggle && navLinks) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
        });

        // Optional: Close menu when a link is clicked (useful for single-page navigation)
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                if (navLinks.classList.contains('active')) {
                    navLinks.classList.remove('active');
                }
            });
        });
    }
});

 const endDate = new Date("june 27 , 2025  07:00:00").getTime();

  // Update countdown every second
  const timer = setInterval(function() {
    const now = new Date().getTime();
    const distance = endDate - now;

    // Time calculations
    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    // Display the result
    document.getElementById("countdown").innerHTML =
      days + "d " + hours + "h " + minutes + "m " + seconds + "s ";

    // If the countdown is finished
    if (distance < 0) {
      clearInterval(timer);
      document.getElementById("countdown").innerHTML = "Term Ended!";
    }
  }, 1000);
    </script>
</body>
</html>

<?php  


if (!isset($_GET['from_splash'])) {
    header("Location: splash.html");
    exit();
}




include 'connection/connect.php';   
session_start();  

try {  
    $stmt = $pdo->prepare("
        SELECT news_title, news_content, created_at
        FROM news
        WHERE category_id = 4
        ORDER BY created_at DESC
    ");  
    $stmt->execute();  
    $announcements = $stmt->fetchAll(PDO::FETCH_ASSOC);  
} catch (PDOException $e) {  
    $error_message = "❌ Error: " . htmlspecialchars($e->getMessage());  
}  
?>  

<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <title>Home Page</title>  
    <link rel="stylesheet" href="index.css">
    <style>
        * {
          margin: 0;
          padding: 0;
          box-sizing: border-box;
        }

        body {
          font-family: Arial, sans-serif;
          background-color: #fff;
        }

        .wrapper {
          width: 100%;
          max-width: 1250px;
          margin: 0 auto;
        }

        /* Top Nav */
        .top-nav {
          margin-top: 100px;
          display: flex;
          align-items: center;
          padding: 0 20px;
          height: 65px;
          border-bottom: 1px solid #e0e0e0;
        }

        .logo-box {
          background-color:greenyellow;
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
          color:6cfa3a;
          margin-left: 15px;
        }

        .nav-links {
          display: flex;
          gap: 25px;
          margin-left: 50px;
          font-weight: bold;
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

        .nav-links a::after {
          content: ' ▾';
          font-size: 12px;
          display: inline-block;
        }

        .nav-links a:last-child::after {
          content: '';
        }

        
    </style>
</head>  
<body>  
    <!-- NEW NAVBAR START -->
    <div class="wrapper">
        <div class="top-nav">
            <div class="logo-box">
                <img src="./images/claudia (1).gif" alt="Alliance Logo">
            </div>
            <div class="site-title">ALLIANCE</div>
            <div class="nav-links">
                <a href="#">All</a>
                <a href="#">Local news</a>
                <a href="sport.php">Sports</a>
                <a href="#">Entertainment</a>
                <li><a href="#" onclick="showContent(event, 'anounce')">School Announcement</a></li>  
            </div>
        </div>

        
        </div>
    </div>
    <!-- NEW NAVBAR END -->

    <div class="sliding-news">  
        <div class="slider">  
            <div class="slides" id="slides">  
                <div class="slide">  
                    <img src="./Images/10.png" alt="">  
                    <div class="content">  
                        <p><a href="#">Sample headline for your sliding news</a></p>  
                    </div>  
                </div>  
                <div class="slide">  
                    <img src="./Images/10.png" alt="">  
                    <p><a href="#">Another important news headline here</a></p>  
                </div>  
            </div>  
            <button class="nav right" onclick="moveSlide(1)">&#10095;</button>  
        </div>  
    </div>  

    <div class="main-content" id="anounce">  
        <h2>Recent Announcements</h2>  
        <?php if (isset($error_message)): ?>  
            <p><?php echo $error_message; ?></p>  
        <?php else: ?>  
            <?php if ($announcements): ?>  
                <?php foreach ($announcements as $announcement): ?>  
                    <div class="announcement">  
                        <h3><?php echo htmlspecialchars($announcement['news_title']); ?></h3>  
                        <p><?php echo nl2br(htmlspecialchars($announcement['news_content'])); ?></p>  
                        <small>Posted on: <?php echo htmlspecialchars($announcement['created_at']); ?></small>  
                    </div>  
                <?php endforeach; ?>  
            <?php else: ?>  
                <p>No announcements available.</p>  
            <?php endif; ?>  
        <?php endif; ?>  
    </div>

    <div class="the-body">
        <div class="weather-container" id="weatherContainer">
            <div class="current-weather">
                <div class="current-time" id="current-time"></div>
                <button class="report-button" onclick="reportWeather()">Report weather</button>
            </div>
            <div class="temperature">
                <span id="weather-symbol" style="color: orange">&#x2600;</span>
                <span id="temperature"></span> <span style="font-size: 0.8em;">°C</span>
            </div>
            <div class="condition" id="condition"></div>
            <div class="details">
                <div class="detail-item"><span class="detail-label">Wind</span><br><span id="wind"></span></div>
                <div class="detail-item"><span class="detail-label">Humidity</span><br><span id="humidity"></span></div>
                <div class="detail-item"><span class="detail-label">Feels like</span><br><span id="feels-like"></span></div>
                <div class="detail-item"><span class="detail-label">Visibility</span><br><span id="visibility"></span></div>
                <div class="detail-item"><span class="detail-label">UV index</span><br><span id="uv-index"></span></div>
                <div class="detail-item"><span class="detail-label">Pressure</span><br><span id="pressure"></span></div>
                <div class="detail-item"><span class="detail-label">Dew point</span><br><span id="dew-point"></span></div>
            </div>
        </div>
    </div>  

    <script src="index.js"></script>
</body>  
</html>  

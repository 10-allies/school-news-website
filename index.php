<?php  
include 'connection/connect.php';   
session_start();  

// Fetch announcements from the database  
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
    <style>  
        /*Header css*/  
        * {  
            padding: 0;  
            margin: 0;  
        }  
        .header {  
            width: 100%;  
            display: flex;  
            height: 9.5em;  
            background-color: #ffffff;  
        }  
        .header-right ul {  
            display: flex;  
            margin: 1em;  
            margin-left: 23em;  
        }  
        .header-right ul li, .header-right ul li a {  
            text-decoration: none;  
            margin: 1em;  
            list-style-type: none;  
            padding-top: 1.6em;  
            font-size: 1.15em;  
            color: rgb(52, 216, 52);  
        }  
        .header-right ul li:hover, .header-right ul li a:hover {  
            color: rgb(4, 70, 4);  
        }  
        .logo img {  
            width: 70%;  
            height: 7em;  
            margin-left: 2em;  
            padding-top: 1em;  
        }  
        /*End of header css*/  
        /*sliding news*/  
        .sliding-news {  
            height: 100em;  
            background-image: url("./Images/kk1.jpg");  
            background-size: cover;  
            background-position: center;  
            background-repeat: no-repeat;  
        }  
        .main-content {  
            height: auto; /* Adjust your height based on content */  
            margin-left: 2em;  
            width: 95%;  
            background-color: rgba(255, 255, 255, 0.7);  
            padding: 1em; /* Adding padding for aesthetics */  
        }  
        .announcement {  
            border: 1px solid #ccc;  
            margin-bottom: 1em;  
            padding: 1em;  
            border-radius: 5px;  
            background-color: #f9f9f9;  
        }  
        .slider {  
            position: relative;  
            margin-left: 3em;  
            width: 100%;   
            background-color: rgb(255, 255, 255, 0.9);  
            margin: auto;  
            overflow: hidden;  
        }  
        .slides {  
            display: flex;  
            transition: transform 0.5s ease;  
            margin-top: 2em;  
        }  
        .slide {  
            display: flex;  
            min-width: 100%;  
            box-sizing: border-box;  
            text-align: center;  
        }  
        .slide p {  
            font-size: 1.4em;  
        }  
        .slide img {  
            margin-left: 2em;  
            width: 42%;  
            height: auto;  
        }  
        .nav {  
            position: absolute;  
            top: 50%;  
            transform: translateY(-50%);  
            background-color: rgba(12, 238, 42, 0.9);  
            color: white;  
            border: none;  
            font-size: 2em;  
            border-radius: 2em;  
            cursor: pointer;  
            padding: 15px;  
            z-index: 1000;  
        }  
        .left {  
            left: 10px;  
        }  
        .right {  
            right: 10px;  
        }  
        /*End of sliding news*/  
    </style>  
</head>  
<body>  
    <div class="header">  
        <div class="logo">  
            <img src="./Images/10.png" alt="School news logo">  
        </div>  
        <div class="header-right">  
            <ul>  
                <li><a href="">ALL</a></li>  
                <li><a href="">Local news</a></li>  
                <li><a href="">Sports</a></li>  
                <li><a href="">Entertainment</a></li>  
                <li><a href="" onclick="showContent('anounce')">School Announcement</a></li>  
            </ul>  
        </div>  
    </div>  
    <div class="sliding-news">  
        <div class="slider">  
            <div class="slides" id="slides">  
                <div class="slide">  
                    <img src="./Images/10.png" alt="">  
                    <div class="content">  
                        <p><a href=""> ipsum dolor sit amet consectetur adipisicing <br> elit. Dicta fugiat eaque <br>laboriosam aliquam in quos libero animi possimus,<br> temporibus, ipsum ad magni quibusdam! <br> Fugit debitis necessitatibus expedita magni repellat doloremque.</a></p>  
                    </div>  
                </div>  
                <div class="slide">  
                    <img src="./Images/10.png" alt="">  
                    <p><a href="">Lorem ipsum dolor sit amet consectetur adipisicing <br> elit. Dicta fugiat eaque <br>laboriosam aliquam in quos libero animi possimus,<br> temporibus, ipsum ad magni quibusdam! <br> Fugit debitis necessitatibus expedita magni repellat doloremque.</a></p>  
                </div>  
            </div>  
            <button class="nav right" onclick="moveSlide(1)">&#10095; </button>  
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
    
    <script>  
        let currentIndexs = 0;  
        function moveSlide(direction) {  
            const slides = document.getElementById('slides');  
            const totalSlides = slides.children.length;  

            currentIndexs += direction;  

            if (currentIndexs < 0) {  
                currentIndexs = totalSlides - 1;  
            }  
            if (currentIndexs >= totalSlides) {  
                currentIndexs = 0;   
            }  

            slides.style.transform = `translateX(-${currentIndexs * 100}%)`;  
        }  
       
        setInterval(() => {  
            moveSlide(1);  
        }, 5000);
        function showContent(id) {
        // Hide the slider div
        const slider = document.querySelector('.sliding-news');
        if (slider) {
            slider.style.display = 'none';
        }

        // Show the selected section
        const target = document.getElementById(id);
        if (target) {
            target.style.display = 'block';
        }
    }

    // Ensure the announcement div is hidden by default
    document.addEventListener('DOMContentLoaded', () => {
        const anounce = document.getElementById('anounce');
        if (anounce) {
            anounce.style.display = 'none';
        }
    });
    </script>  
</body>  
</html>  
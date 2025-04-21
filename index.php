<?php  
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
</head>  
<body>  
    <div class="header">  
        <div class="logo">  
            <img src="./Images/10.png" alt="School news logo">  
        </div>  
        <div class="header-right">  
            <ul>  
                <li><a href="#">ALL</a></li>  
                <li><a href="#">Local news</a></li>  
                <li><a href="#">Sports</a></li>  
                <li><a href="#">Entertainment</a></li>  
                <li><a href="#" onclick="showContent(event, 'anounce')">School Announcement</a></li>  
            </ul>  
        </div>  
    </div>  

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

        setInterval(() => { moveSlide(1); }, 5000);  

        function showContent(event, id) {
            event.preventDefault();

            
            const slider = document.querySelector('.sliding-news');
            if (slider) {
                slider.style.display = 'none';
            }

        
            const target = document.getElementById(id);
            if (target) {
                target.style.display = 'block';
            }
        }

    
        document.addEventListener('DOMContentLoaded', () => {
            const anounce = document.getElementById('anounce');
            if (anounce) {
                anounce.style.display = 'none';
            }
        });
    </script>  
</body>  
</html>  

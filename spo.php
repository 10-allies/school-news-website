<!DOCTYPE html>  
<html lang="en">  
<head>  
    <meta charset="UTF-8">  
    <meta name="viewport" content="width=device-width, initial-scale=1.0">  
    <link rel="stylesheet" href="styles.css">  
    <title>Sports News Page</title>  
    <style>  
        * {  
            margin: 0;  
            padding: 0;  
            box-sizing: border-box;  
        }  

        body {  
            font-family: Arial, sans-serif;  
            background-color: #fff;  
            display: flex;  
            justify-content: center;  
        }  

        .wrapper {  
            width: 100%;  
            max-width: 1250px;  
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
            background-color: #f39800;  
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
            color: #f39800;  
            margin-left: 10px;  
        }  

        .nav-links {  
            display: flex;  
            gap: 25px;  
            margin-left: 40px;  
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

        .nav-links a:nth-child(2),  
        .nav-links a:nth-child(3),  
        .nav-links a:nth-child(4),  
        .nav-links a:nth-child(5),  
        .nav-links a:nth-child(6),  
        .nav-links a:nth-child(7) {  
            padding-right: 5px;  
        }  

        .nav-links a:last-child::after {  
            content: '';  
        }  

        /* Container Styles */  
        .container {  
            display: flex;  
            margin-top: 20px;  
        }  

        .main-news {  
            flex: 2; /* Main news takes twice the space of the sidebar */  
            margin-right: 20px;  
        }  

        .sidebar {  
            flex: 1; /* Sidebar takes one part space */  
        }  

        .sidebar div {  
            margin-bottom: 20px;  
        }  

        .sidebar img {  
            width: 100%; /* Make images responsive */  
            height: auto;  
        }  

        .sidebar .date {  
            font-size: 12px;  
            color: gray;  
        }  

        h1.page-heading {  
            text-align: center;  
            margin: 20px 0;  
        }  
      
    </style>  
</head>  
<body>  
    <div class="wrapper">  
        <!-- Top Navigation -->  
        <div class="top-nav">  
            <div class="logo-box">  
                <img src="image/claudia (1).gif" alt="Alliance Logo">  
            </div>  
            <div class="site-title">ALLIANCE</div>  
            <div class="nav-links">  
                <a href="#">News</a>  
                <a href="#">HOPE-News</a>  
                <a href="#">Explained</a>  
                <a href="#">Opinion</a>  
                <a href="sport.html">Sport</a>  
                <a href="#">Video</a>  
                <a href="#">More</a>  
            </div>  
        </div>  

        <!-- Main Heading -->  
        <h1 class="page-heading">SPORT</h1>  

        <!-- Navigation Bar for Sports Categories -->  
        <nav class="navbar">  
            <ul>  
                <li><a href="#">Cricket</a></li>  
                <li><a href="#">Football</a></li>  
                <li><a href="#">Basketball</a></li>  
                <li><a href="#">Motorsports</a></li>  
                <li><a href="#">Boxing</a></li>  
                <li><a href="#">MMA</a></li>  
                <li><a href="#">HOPE HAVEN LEAGUE</a></li>  
            </ul>  
        </nav>  

        <!-- Container for Main News and Sidebar -->  
        <div class="container">  
            <div class="main-news">  
                <!-- Articles will be dynamically inserted here -->  
            </div>  
            <div class="sidebar">  
                <div>  
                    <img src="https://www.aljazeera.com/wp-content/uploads/2025/04/AP25103546510206-1744557806.jpg?resize=270%2C180&quality=80" alt="Small News 1">  
                    <div>  
                        <a href="small-news-url-1.html">Marc Marquez survives brotherly collision to win Qatar MotoGP</a>  
                        <div class="date">14 Apr 2025</div>  
                    </div>  
                </div>  
                <div>  
                    <img src="https://www.aljazeera.com/wp-content/uploads/2025/04/2025-04-13T124338Z_46102764_UP1EL4D0ZCPN2_RTRMADP_3_TENNIS-MONTECARLO-1744549024.jpg?resize=270%2C180&quality=80" alt="Small News 2">  
                    <div>  
                        <a href="small-news-url-2.html">‘Pretty damn fun’: Piastri powers to dominant F1 victory at Bahrain GP</a>  
                        <div class="date">14 Apr 2025</div>  
                    </div>  
                </div>  
                <div>  
                    <img src="https://www.aljazeera.com/wp-content/uploads/2025/04/2025-04-13T145114Z_785157437_UP1EL4D159DZ4_RTRMADP_3_SOCCER-ENGLAND-LIV-WHU-REPORT-1744556264.jpg?resize=270%2C180&quality=80" alt="Small News 3">  
                    <div>  
                        <a href="small-news-url-3.html">Golf: Rory McIlroy wins Masters, completes career Grand Slam</a>  
                        <div class="date">14 Apr 2025</div>  
                    </div>  
                </div>  
            </div>  
        </div>  
    </div>  

    <script>  
        document.addEventListener('DOMContentLoaded', function() {  
            fetch('fetch_articles.php')  
                .then(response => response.json())  
                .then(data => {  
                    const container = document.querySelector('.main-news');  
                    // Clear the current content  
                    container.innerHTML = '';  
                    // Loop through articles and create HTML elements  
                    data.forEach(article => {  
                        const articleDiv = document.createElement('div');  
                        articleDiv.innerHTML = `  
                            <img src="${article.image_url}" alt="${article.title}">  
                            <p>${article.description} <a href="#">Read more...</a></p>  
                        `;  
                        container.appendChild(articleDiv);  
                    });  
                })  
                .catch(error => console.error('Error fetching articles:', error));  
        });  
    </script>  
</body>  
</html>  
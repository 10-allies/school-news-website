<?php
// Include database connection file
include 'db2.php';

// Retrieve the 'id' parameter from the URL and sanitize it
$id = intval($_GET['id']);

// Perform the query to get the news article by ID
$sql = "SELECT * FROM news WHERE id = $id";
$result = $conn->query($sql);

// Check if there are results and fetch the data
if ($result) {
    $news = $result->fetch_assoc();
} else {
    die("Error: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($news['title']); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        h1 {
            font-size: 2em;
            margin-bottom: 20px;
            color: #222;
        }
        img, video {
            max-width: 100%;
            border-radius: 6px;
            margin-bottom: 20px;
        }
        p {
            line-height: 1.6;
            font-size: 1.1em;
        }
        a {
            display: inline-block;
            margin-top: 20px;
            text-decoration: none;
            color: #0077cc;
            transition: color 0.3s ease;
        }
        a:hover {
            color: #005fa3;
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
                <img src="../images/claudia (1).gif" alt="Alliance Logo">
            </div>
            <div class="site-title">ALLIANCE</div>
            <div class="nav-links">
                <a href="#">All</a>
                <a href="#">Local news</a>
                <a href="./pages/sport.php">Sports</a>
                <a href="./pages/entertain.php">Entertainment</a>
                <li><a href="#" onclick="showContent(event, 'anounce')">School Announcement</a></li>  
            </div>
        </div>

        
        </div>
    </div>
    <!-- NEW NAVBAR END -->
    <div class="container">
        <h1><?php echo htmlspecialchars($news['title']); ?></h1>
        <?php if (!empty($news['image'])): ?>
            <img src="../uploads/<?php echo htmlspecialchars($news['image']); ?>" alt="News Image">
        <?php endif; ?>

        <p><?php echo nl2br(htmlspecialchars($news['content'])); ?></p>

        <?php if (!empty($news['video'])): ?>
            <video controls>
                <source src="../uploads/<?php echo htmlspecialchars($news['video']); ?>">
                Your browser does not support the video tag.
            </video>
        <?php endif; ?>

        <a href="entertain.php">← Back to News</a>
    </div>
</body>
</html>

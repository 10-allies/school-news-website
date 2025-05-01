<?php include 'db2.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Entertainment | APPEAL</title>
  <link rel="stylesheet" href="entertain.css">
</head>
<body>

  <!-- NAVIGATION BAR -->
  <header class="top-nav">
    <div class="logo-box">
      <img src="../images/claudia (1).gif" alt="Alliance Logo">
    </div>
    <div class="site-title">APPEAL</div>
    <nav class="nav-links">
      <a href="../index.php">All</a>
      <a href="#">Local News</a>
      <a href="../pages/sport.php">Sports</a>
      <a href="../pages/entertain.php" class="active">Entertainment</a>
      <a href="#" onclick="showContent(event, 'anounce')">School Announcement</a>
    </nav>
  </header>

  <!-- PAGE CONTENT -->
  <main class="content-wrapper">
    
    <!-- MAIN FEATURED NEWS -->
    <section class="main-news">
   
      <?php
      $mainSql = "SELECT * FROM news ORDER BY created_at DESC LIMIT 1";
      $mainResult = $conn->query($mainSql);
      if ($mainResult && $mainResult->num_rows > 0) {
          $main = $mainResult->fetch_assoc();
          echo "<article class='featured-news'>";
          echo "<img src='../uploads/{$main['image']}' alt='Main News'>";
          echo "<div class='featured-content'>";
          echo "<h2><a href='news.php?id={$main['id']}'>" . htmlspecialchars($main['title']) . "</a></h2>";
          echo "<p>" . substr(strip_tags($main['content']), 0, 200) . "... <br><a href='news.php?id={$main['id']}'>Read more</a></p>";
          echo "</div></article>";
      } else {
          echo "<p>No news found.</p>";
      }
      ?>
    </section>

    <!-- SIDEBAR NEWS -->
    <aside class="sidebar">
      <h2>More Entertainment</h2>
      <div class="sidebar-news">
        <?php
        // Fetch next 5 recent articles excluding the main one
        $sideSql = "SELECT * FROM news ORDER BY created_at DESC LIMIT  5";
        $sideResult = $conn->query($sideSql);
        if ($sideResult) {
          while ($row = $sideResult->fetch_assoc()) {
            echo "<div class='sidebar-item'>";
            echo "<img src='../uploads/{$row['image']}' alt='News Thumbnail'>";
            echo "<div class='sidebar-text'>";
            echo "<a href='news.php?id={$row['id']}'>" . htmlspecialchars($row['title']) . "</a>";
            echo "</div></div>";
          }
        }
        ?>
      </div>
    </aside>
    
  </main>

</body>
</html>

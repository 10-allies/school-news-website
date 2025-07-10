<?php
// Database connection using PDO
$dsn = "mysql:host=localhost;dbname=newsdb;charset=utf8mb4";
$username = "root";
$password = " ";

try {
    $pdo = new PDO($dsn, $username, $password);
    // Set error mode
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Fetch sports news (adjust category ID or name as needed)
$sql = "
    SELECT n.news_title, n.news_content, n.created_at, a.author_display_name, m.media_url
    FROM news n
    JOIN authors a ON n.author_id = a.author_id
    JOIN categories c ON n.category_id = c.category_id
    LEFT JOIN media m ON n.news_id = m.news_id AND m.media_type = 'image'
    WHERE n.status = 'published' AND c.category_name = 'Sports'
    ORDER BY n.created_at DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$news = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Sports News</title>
    <style>
        .news-card {
            border: 1px solid #ccc;
            padding: 15px;
            margin: 15px auto;
            width: 80%;
            background: #f9f9f9;
            border-radius: 8px;
        }
        .news-card img {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
        }
        .news-card h2 { margin-top: 10px; }
        .news-card p { font-size: 16px; }
    </style>
</head>
<body>
    <h1 style="text-align:center;">Latest Sports News</h1>

    <?php if (count($news) === 0): ?>
        <p style="text-align:center;">No sports news available at the moment.</p>
    <?php endif; ?>

    <?php foreach ($news as $item): ?>
        <div class="news-card">
            <?php if (!empty($item['media_url'])): ?>
                <img src="<?= htmlspecialchars($item['media_url']) ?>" alt="News Image">
            <?php endif; ?>
            <h2><?= htmlspecialchars($item['news_title']) ?></h2>
            <p><?= nl2br(htmlspecialchars($item['news_content'])) ?></p>
            <small>Posted by <?= htmlspecialchars($item['author_display_name']) ?> on <?= $item['created_at'] ?></small>
        </div>
    <?php endforeach; ?>
</body>
</html>

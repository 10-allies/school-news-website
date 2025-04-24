<?php
header('Content-Type: application/json');
require_once './connection/connect.php';

try {
    $sql = "SELECT news.*, media.media_url 
            FROM news 
            LEFT JOIN media ON news.news_id = media.news_id 
            JOIN sections ON news.section_id = sections.section_id 
            WHERE sections.section_name = 'Hope Haven League' 
            AND news.status = 'published'
            ORDER BY news.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $news = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $news[] = [
            "title" => $row['news_title'],
            "content" => $row['news_content'],
            "image" => $row['media_url'] ?? 'uploads/default.jpg',
            "published_at" => $row['created_at']
        ];
    }

    echo json_encode($news);

} catch (PDOException $e) {
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>

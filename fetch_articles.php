<?php
header('Content-Type: application/json');
require_once '../connection/connect.php'; // your correct path

try {
    // Mapping section names to section IDs
    $sectionMap = [
        "Football" => 1,
        "Basketball" => 2,
        "Volleyball" => 3,
        "Hope_haven_league" => 4
    ];

    // Mapping category names to category IDs
    $categoryMap = [
        "All" => null,
        "Local news" => 1,
        "Sports" => 2,
        "Entertainment" => 3,
        "School Announcement" => 4
    ];

    $section = isset($_GET['section']) ? $_GET['section'] : null;
    $category = isset($_GET['category']) ? $_GET['category'] : null;

    $sql = "SELECT news.*, media.media_url 
            FROM news 
            LEFT JOIN media ON news.news_id = media.news_id 
            WHERE news.status = 'published'";

    $params = [];

    if ($section !== null && isset($sectionMap[$section])) {
        $sql .= " AND news.section_id = :section_id";
        $params[':section_id'] = $sectionMap[$section];
    } elseif ($category !== null && isset($categoryMap[$category])) {
        if ($categoryMap[$category] !== null) { // Don't filter if "All"
            $sql .= " AND news.category_id = :category_id";
            $params[':category_id'] = $categoryMap[$category];
        }
    }

    $sql .= " ORDER BY news.created_at DESC";

    $stmt = $conn->prepare($sql);

    foreach ($params as $param => $value) {
        $stmt->bindValue($param, $value, PDO::PARAM_INT);
    }

    $stmt->execute();

    $articles = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $articles[] = [
            "news_title" => $row['news_title'],
            "news_content" => $row['news_content'],
            "media_url" => $row['media_url'] ?? 'https://via.placeholder.com/600x400',
            "created_at" => $row['created_at']
        ];
    }

    echo json_encode($articles);

} catch (PDOException $e) {
    echo json_encode(["error" => "Query failed: " . $e->getMessage()]);
}
?>

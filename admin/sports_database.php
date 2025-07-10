<?php
include '../connection/connect.php';

// Ensure this file is only accessed via POST request
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: admin.php?status=error&msg=Invalid request method.");
    exit;
}

$title = $_POST["title"] ?? '';
$content = $_POST["content"] ?? '';
$section_id = (int)($_POST["section_id"] ?? 0);
$author_id = (int)($_POST["author_id"] ?? 0);
$status = "published";
$created_at = date("Y-m-d H:i:s");

// --- Determine the category_id for 'Sports' ---
$sportsCategoryId = null;
try {
    $stmtCategory = $pdo->prepare("SELECT category_id FROM news_category WHERE LOWER(category_name) = 'sports'");
    $stmtCategory->execute();
    $categoryResult = $stmtCategory->fetch(PDO::FETCH_ASSOC);
    if ($categoryResult) {
        $sportsCategoryId = $categoryResult['category_id'];
    } else {
        header("Location: admin.php?status=error&msg=Sports category not found in database.");
        exit;
    }
} catch (PDOException $e) {
    header("Location: admin.php?status=error&msg=Database error fetching category: " . urlencode($e->getMessage()));
    exit;
}

// --- Insert news into the database ---
try {
    $stmt = $pdo->prepare("INSERT INTO news (news_title, news_content, section_id, author_id, status, created_at, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $content, $section_id, $author_id, $status, $created_at, $sportsCategoryId]);
    $news_id = $pdo->lastInsertId();

    // --- Handle image upload ---
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $image_name = basename($_FILES['image']['name']);
        $file_extension = pathinfo($image_name, PATHINFO_EXTENSION);
        $unique_name = 'news_image_' . uniqid() . '.' . $file_extension;

        // Define the relative upload directory from the web root
        // This is crucial for storing the correct path that can be used in HTML img tags
        $upload_dir = "../uploads/"; // This path assumes 'uploads' is a sibling of 'Admin' or at the web root
        
        // Adjust the server-side file system path for moving the file
        // This path is relative to the current script (sports_db.php)
        $server_upload_path = "../uploads/"; 

        if (!is_dir($server_upload_path)) {
            mkdir($server_upload_path, 0777, true); // Create directory if it doesn't exist
        }
        
        $full_server_path = $server_upload_path . $unique_name;
        $web_access_path = $upload_dir . $unique_name; // This is the path to store in DB

        if (move_uploaded_file($_FILES['image']['tmp_name'], $full_server_path)) {
            $stmt2 = $pdo->prepare("INSERT INTO media (news_id, media_url, media_type, media_caption) VALUES (?, ?, 'image', ?)");
            // Store the web-accessible path in the database
            $stmt2->execute([$news_id, $web_access_path, $title]);
        } else {
            header("Location: admin.php?status=file_error");
            exit;
        }
    }

    // If everything is successful, redirect to admin.php with a success status
    header("Location: admin.php?status=success");
    exit;

} catch (PDOException $e) {
    header("Location: admin.php?status=error&msg=" . urlencode($e->getMessage()));
    exit;
}
?>
<?php
include '../connection/connect.php'; 


$sections = $pdo->query("SELECT section_id, section_name FROM sections ORDER BY section_name ASC")->fetchAll(PDO::FETCH_ASSOC); // Keep if sections are used elsewhere
$authors = $pdo->query("SELECT author_id, author_display_name FROM authors ORDER BY author_display_name ASC")->fetchAll(PDO::FETCH_ASSOC);

$entertainmentCategoryId = null;
$entertainmentSectionId = null; 

$stmtCategory = $pdo->prepare("SELECT category_id FROM news_category WHERE LOWER(category_name) = 'entertainment'");
$stmtCategory->execute();
$resultCategory = $stmtCategory->fetch(PDO::FETCH_ASSOC);
if ($resultCategory) {
    $entertainmentCategoryId = $resultCategory['category_id'];


    $stmtSection = $pdo->prepare("SELECT section_id FROM sections WHERE category_id = ? LIMIT 1"); 
    $stmtSection->execute([$entertainmentCategoryId]);
    $resultSection = $stmtSection->fetch(PDO::FETCH_ASSOC);
    if ($resultSection) {
        $entertainmentSectionId = $resultSection['section_id'];
    }

} else {

    $message = "<div class='error'>❌ 'Entertainment' category not found in the database! Please add it.</div>";
}



$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST["title"];
    $content = $_POST["content"];
    $author_id = (int)$_POST["author_id"];
    $status = "published";
    $created_at = date("Y-m-d H:i:s");


    if ($entertainmentCategoryId === null) {
        $message = "<div class='error'>Cannot add news: Entertainment category not found.</div>";
    } else {

        $stmt = $pdo->prepare("INSERT INTO news (news_title, news_content, section_id, author_id, status, created_at, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        
        $success = $stmt->execute([$title, $content, $entertainmentSectionId, $author_id, $status, $created_at, $entertainmentCategoryId]);

        if ($success) {
            $news_id = $pdo->lastInsertId();

            if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
                $original_image_name = basename($_FILES['image']['name']);
                $file_extension = pathinfo($original_image_name, PATHINFO_EXTENSION);
                

                $unique_filename_on_server = 'news_image_' . uniqid() . '.' . $file_extension;
                
                $upload_dir = "../uploads/"; 
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true); 
                }
                $upload_path_on_server = $upload_dir . $unique_filename_on_server;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path_on_server)) {
                  
                    $media_url_to_store = "../uploads/" . $unique_filename_on_server; 
                    
                    $stmt_media = $pdo->prepare("INSERT INTO media (news_id, media_url, media_type, media_caption) VALUES (?, ?, 'image', ?)");
                    $stmt_media->execute([$news_id, $media_url_to_store, $title]); 
                } else {
                    $message = "<div class='error'>❌ Error moving uploaded image file. Check permissions for " . $upload_dir . "</div>";
                }
            } else {
   
                $message = "<div class='error'>❌ No image uploaded or an image upload error occurred.</div>";
            }
            

            if (empty($message) || strpos($message, 'No image uploaded') !== false) { 
                header("Location: admin.php"); 
                exit;
            }
        } else {
            $errorInfo = $stmt->errorInfo();
            $message = "<div class='error'>❌ Error uploading news: " . htmlspecialchars($errorInfo[2]) . "</div>";
        }
    }
}
?>
<?php
include '../connection/connect.php'; 
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $announcement = $_POST['announcement'] ?? '';
    $announcer_name = $_POST['announcer_name'] ?? '';
    $created_at = date("Y-m-d H:i:s");
    $category_id = 4; 
    $section_id = null; 
    $author_id = null;
    $news_title = "Announcement from $announcer_name";
    $file_path = null;

    
    if (isset($_FILES['announce_file']) && $_FILES['announce_file']['error'] === 0) {
        $file = $_FILES['announce_file'];
        $file_name = basename($file['name']);
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];

        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        if (in_array($file_ext, $allowed_types)) {
            if ($file_size <= 2 * 1024 * 1024) { 
                $new_name = uniqid("announcement_", true) . '.' . $file_ext;
                $upload_dir = '../uploads/';
                $file_path = $upload_dir . $new_name;

                if (move_uploaded_file($file_tmp, $file_path)) {
                
                } else {
                    echo "❌ Failed to upload file.";
                    exit;
                }
            } else {
                echo "❌ File too large. Max 2MB.";
                exit;
            }
        } else {
            echo "❌ Invalid file type.";
            exit;
        }
    }

    try {
        $stmt = $pdo->prepare("
            INSERT INTO news 
            (category_id, section_id, author_id, news_title, news_content, created_at) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $category_id,
            $section_id,
            $author_id,
            $news_title,
            $announcement,
            $created_at
        ]);

        $news_id = $pdo->lastInsertId();

        
        if ($file_path) {
            $media_stmt = $pdo->prepare("
                INSERT INTO media (news_id, media_type, media_url, media_caption) 
                VALUES (?, ?, ?, ?)
            ");
            $media_type = 'image'; 
            if (in_array($file_ext, ['pdf', 'doc', 'docx'])) {
                $media_type = 'document';
            }

            $media_stmt->execute([
                $news_id,
                $media_type,
                $file_path,
                "Uploaded with announcement"
            ]);
        }

        echo "✅ Announcement posted successfully!";
    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>

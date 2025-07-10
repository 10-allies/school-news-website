<?php
// Ensure this path is correct relative to announce.php
// If announce.php is in Admin/ and connect.php is in connection/,
// then it should be correct: '../connection/connect.php'
include '../connection/connect.php'; 

// The $pdo variable is now available from connect.php (it's a PDO object)

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Sanitize and retrieve form data
    $announcement_text = isset($_POST['announcement']) ? trim($_POST['announcement']) : '';
    $announcer_name = isset($_POST['announcer_name']) ? trim($_POST['announcer_name']) : 'Unknown Author';

    // Set default values for news insertion
    $created_at = date("Y-m-d H:i:s");
    $category_id = 4; // Assuming category_id 4 is for Announcements
    $section_id = null; // Announcements might not have a specific section
    $author_id = null; // You might want to link this to a logged-in author's ID if available
    $news_title = "Announcement from " . htmlspecialchars($announcer_name);
    $media_url = null;
    $media_type = null;

    // File upload configuration
    // The path here is relative to announce.php
    // If announce.php is in Admin/ and uploads/ is parallel to Admin/,
    // then it should be '../uploads/'. If uploads/ is inside Admin/, then 'uploads/'.
    // Your error message "✅ File uploaded successfully: ../uploads/announcement_..." suggests it should be '../uploads/'.
    $upload_dir = '../uploads/'; // Corrected based on your error message output
    $max_file_size = 100 * 1024 * 1024; // Increased to 100MB as requested

    $allowed_types = [
        'pdf', 'doc', 'docx', 'txt', 'xls', 'xlsx', 'ppt', 'pptx', // Document formats
        'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg' // Image formats
    ];

    $has_file = false;
    if (isset($_FILES['announce_file']) && $_FILES['announce_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['announce_file'];
        $file_name = basename($file['name']);
        $file_tmp = $file['tmp_name'];
        $file_size = $file['size'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validate file type and size
        if (in_array($file_ext, $allowed_types)) {
            if ($file_size <= $max_file_size) {
                $new_name = uniqid("announcement_", true) . '.' . $file_ext;
                $target_file_path = $upload_dir . $new_name;

                // Create the uploads directory if it doesn't exist
                if (!is_dir($upload_dir)) {
                    // 0777 grants full permissions - consider 0755 for production
                    mkdir($upload_dir, 0777, true); 
                }

                if (move_uploaded_file($file_tmp, $target_file_path)) {
                    $media_url = $target_file_path;
                    // Note: Your media table ENUM should include 'document' if you use it.
                    // Otherwise, 'image' might be the only valid option for files.
                    $media_type = (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'])) ? 'image' : 'document'; 
                    $has_file = true;
                    echo "✅ File uploaded successfully: " . htmlspecialchars($media_url) . "<br>";
                } else {
                    echo "❌ Failed to move uploaded file. Check directory permissions.<br>";
                    // Consider logging this error for debugging
                }
            } else {
                echo "❌ File too large. Max " . ($max_file_size / (1024 * 1024)) . "MB.<br>";
            }
        } else {
            echo "❌ Invalid file type. Allowed types: " . implode(', ', $allowed_types) . "<br>";
        }
    } else if (isset($_FILES['announce_file']) && $_FILES['announce_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other upload errors (e.g., UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE)
        echo "❌ File upload error: " . $_FILES['announce_file']['error'] . "<br>";
    }

    // Determine the content for news_content
    $news_content = $announcement_text;
    if ($has_file && empty($announcement_text)) {
        // If only a file is uploaded, set content to a default message
        $news_content = "Please see the attached file for the announcement.";
    } elseif (!$has_file && empty($announcement_text)) {
        echo "Please provide either text content or upload a file for the announcement.<br>";
        // You might want to redirect back to the form or display an error
        exit;
    }

    // Insert into 'news' table using PDO
    // We now check for $pdo, as that's the variable from your connect.php
    if (isset($pdo)) { 
        try {
            $stmt = $pdo->prepare("INSERT INTO news (category_id, section_id, author_id, news_title, news_content, created_at) VALUES (:category_id, :section_id, :author_id, :news_title, :news_content, :created_at)");
            
            $stmt->bindParam(':category_id', $category_id, PDO::PARAM_INT);
            $stmt->bindParam(':section_id', $section_id, PDO::PARAM_INT); // Can be NULL
            $stmt->bindParam(':author_id', $author_id, PDO::PARAM_INT); // Can be NULL
            $stmt->bindParam(':news_title', $news_title, PDO::PARAM_STR);
            $stmt->bindParam(':news_content', $news_content, PDO::PARAM_STR);
            $stmt->bindParam(':created_at', $created_at, PDO::PARAM_STR);

            if ($stmt->execute()) {
                $news_id = $pdo->lastInsertId(); // Get the ID of the newly inserted news item using $pdo
                echo "✅ Announcement text saved successfully with News ID: " . $news_id . "<br>";

                // If a file was uploaded, insert into 'media' table
                if ($has_file && $media_url) {
                    $stmt_media = $pdo->prepare("INSERT INTO media (news_id, media_type, media_url, media_caption) VALUES (:news_id, :media_type, :media_url, :media_caption)");
                    $media_caption = "Announcement attachment: " . htmlspecialchars($file_name);
                    
                    $stmt_media->bindParam(':news_id', $news_id, PDO::PARAM_INT);
                    $stmt_media->bindParam(':media_type', $media_type, PDO::PARAM_STR);
                    $stmt_media->bindParam(':media_url', $media_url, PDO::PARAM_STR);
                    $stmt_media->bindParam(':media_caption', $media_caption, PDO::PARAM_STR);

                    if ($stmt_media->execute()) {
                        echo "✅ Media file path saved successfully to database.<br>";
                    } else {
                        echo "❌ Error saving media file path to database. PDO Error: " . $stmt_media->errorInfo()[2] . "<br>";
                    }
                }

                // Redirect or show success message
                echo "<p>Announcement successfully posted!</p>";
                // header("Location: admin.php?success=announcement"); // Example redirect
                // exit();

            } else {
                echo "❌ Error saving announcement to news table. PDO Error: " . $stmt->errorInfo()[2] . "<br>";
            }
        } catch (PDOException $e) {
            echo "❌ Database error during insertion: " . $e->getMessage() . "<br>";
        }
    } else {
        // This error should ideally not be reached if connect.php exits on failure
        echo "❌ Database connection not established. Please check 'connection/connect.php'.<br>";
    }
} else {
    // If not a POST request, perhaps redirect or show an error
    echo "This page should only be accessed via POST request.";
}
?>
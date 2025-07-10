<?php
// --- Configuration ---
$host = 'localhost'; // your DB host
$db_user = 'root'; // your DB username
$db_password = ''; // your DB password
$db_name = 'newsdb';

// Initialize
$errors = [];
$messages = [];

// Database connection (using PDO for consistency)
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $db_user, $db_password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); // For security with prepared statements
} catch (PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}

// Fetch categories for dropdown
$categories = [];
try {
    $stmt = $pdo->query("SELECT category_id, category_name FROM news_category ORDER BY category_name ASC");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Error fetching categories: " . $e->getMessage();
}

// Fetch sections (initially empty or all, will be filtered by JS)
$sections = [];
try {
    $stmt = $pdo->query("SELECT section_id, category_id, section_name FROM sections ORDER BY section_name ASC");
    $sections = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Error fetching sections: " . $e->getMessage();
}

// Fetch authors for dropdown
$authors = [];
try {
    $stmt = $pdo->query("SELECT author_id, author_display_name FROM authors ORDER BY author_display_name ASC");
    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $errors[] = "Error fetching authors: " . $e->getMessage();
}

// Initialize form variables for sticky form
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$category_id = intval($_POST['category_id'] ?? 0);
$section_id = intval($_POST['section_id'] ?? 0);
$author_id = intval($_POST['author_id'] ?? 0);
$media_caption = $_POST['media_caption'] ?? '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data (already initialized above)
    $title = trim($title);
    $content = trim($content);
    $media_caption = trim($media_caption);

    // Basic validation
    if (empty($title)) {
        $errors[] = "Title is required.";
    }
    if (empty($content)) {
        $errors[] = "Content is required.";
    }
    if ($category_id === 0) {
        $errors[] = "Category is required.";
    }
    if ($author_id === 0) {
        $errors[] = "Author is required.";
    }

    // --- Image Upload Logic ---
    // IMPORTANT: Adjust this path based on your exact folder structure.
    // If add_news.php is in news_web/admin/, and uploads folder is in news_web/,
    // the relative path from add_news.php to uploads/ is '../uploads/'.
    // realpath(__DIR__ . '/../uploads/') provides the absolute path which is safer for move_uploaded_file.
    $target_dir_absolute = realpath(__DIR__ . '/../uploads/'); // Absolute path for move_uploaded_file
    $media_url_db_path = '../uploads/'; // Path to store in DB (relative to web root)

    // Default image if no file is uploaded or if upload fails
    $media_url_to_save = $media_url_db_path . '../uploads/default.jpg'; // Path saved in DB

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $allowed_exts = ['jpg', 'jpeg', 'png', 'gif'];
        $file_tmp = $_FILES['image']['tmp_name'];
        $file_name_original = $_FILES['image']['name'];
        $file_ext = strtolower(pathinfo($file_name_original, PATHINFO_EXTENSION));

        // Generate a more robust unique file name
        $new_file_name = 'news_image_' . uniqid() . '.' . $file_ext;
        $dest_path_absolute = $target_dir_absolute . DIRECTORY_SEPARATOR . $new_file_name; // Absolute path for saving

        if (in_array($file_ext, $allowed_exts)) {
            // Check if absolute upload directory exists and is writable
            if (!is_dir($target_dir_absolute)) {
                // Attempt to create the directory with permissions
                if (!mkdir($target_dir_absolute, 0755, true)) { // 0755 is generally safe, adjust if needed
                    $errors[] = "Server: Failed to create upload directory. Check server permissions for parent folders like: " . dirname($target_dir_absolute);
                }
            }

            // Only attempt upload if directory is valid and writable
            if (empty($errors) && is_writable($target_dir_absolute)) {
                if (move_uploaded_file($file_tmp, $dest_path_absolute)) {
                    // Save relative path for URL access in the database
                    $media_url_to_save = $media_url_db_path . $new_file_name;
                    $messages[] = "Image uploaded successfully.";
                } else {
                    // Detailed error for move_uploaded_file failure
                    $errors[] = "Failed to move uploaded file. Check directory permissions and disk space. Destination: " . $dest_path_absolute;
                }
            } else {
                if (!is_writable($target_dir_absolute)) {
                    $errors[] = "Server: Upload directory is not writable. Please set correct permissions (e.g., chmod 755 or 777 temporarily for testing) on " . $target_dir_absolute;
                }
            }
        } else {
            $errors[] = "Invalid file type. Only JPG, JPEG, PNG, and GIF images are allowed.";
        }
    } elseif (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle other PHP upload errors (size, partial, no tmp dir, etc.)
        $php_upload_errors = [
            UPLOAD_ERR_INI_SIZE => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
            UPLOAD_ERR_FORM_SIZE => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
            UPLOAD_ERR_PARTIAL => "The uploaded file was only partially uploaded.",
            UPLOAD_ERR_NO_FILE => "No file was uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder for uploads.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk. Check server disk space or permissions.",
            UPLOAD_ERR_EXTENSION => "A PHP extension stopped the file upload. Check your php.ini."
        ];
        $upload_error_code = $_FILES['image']['error'];
        $error_message = $php_upload_errors[$upload_error_code] ?? "Unknown upload error (Code: {$upload_error_code}).";
        $errors[] = "Image upload problem: " . $error_message;
    }

    // If no errors so far, proceed with database insertion
    if (empty($errors)) {
        try {
            $pdo->beginTransaction(); // Start transaction for atomicity

            // Insert news
            // Ensure section_id is null if 0 (optional field)
            $stmt_news = $pdo->prepare("INSERT INTO news (category_id, section_id, author_id, news_title, news_content, status, created_at) VALUES (?, ?, ?, ?, ?, 'published', NOW())");
            $stmt_news->execute([
                $category_id,
                ($section_id !== 0 ? $section_id : null), // Pass null if section_id is 0
                $author_id,
                $title,
                $content
            ]);
            $news_id = $pdo->lastInsertId();

            // Insert media record
            $stmt_media = $pdo->prepare("INSERT INTO media (news_id, media_type, media_url, media_caption, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
            $stmt_media->execute([$news_id, 'image', $media_url_to_save, $media_caption]);

            $pdo->commit(); // Commit transaction
            $messages[] = "News article and image/media added successfully!";

            // Clear form fields after successful submission
            $title = $content = $media_caption = '';
            $category_id = $section_id = $author_id = 0;

        } catch (PDOException $e) {
            $pdo->rollBack(); // Rollback on error
            $errors[] = "Database insertion failed: " . $e->getMessage();
        }
    }
}
?>
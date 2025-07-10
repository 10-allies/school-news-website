<?php
// Ensure this path is correct relative to add_entertainment_news.php
include './pages/db2.php';

$message = '';
$authors = [];
$entertainment_category_id = null; // To store the ID of the 'Entertainment' category

// Check database connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Get the category_id for 'Entertainment'
$category_sql = "SELECT category_id FROM news_category WHERE category_name = 'Entertainment'";
$category_result = $conn->query($category_sql);
if ($category_result && $category_result->num_rows > 0) {
    $row = $category_result->fetch_assoc();
    $entertainment_category_id = $row['category_id'];
} else {
    // Handle case where 'Entertainment' category doesn't exist
    $message = "Error: 'Entertainment' category not found in the database. Please add it first.";
}

// 2. Fetch authors for dropdown (only if category found)
if ($entertainment_category_id !== null) {
    $author_sql = "SELECT author_id, author_display_name FROM authors ORDER BY author_display_name";
    $author_result = $conn->query($author_sql);
    if ($author_result && $author_result->num_rows > 0) {
        while ($row = $author_result->fetch_assoc()) {
            $authors[] = $row;
        }
    } else {
        $message .= " No authors found in the database. Please add authors first.";
    }
}


// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && $entertainment_category_id !== null) {
    // Sanitize and validate inputs
    $news_title = $conn->real_escape_string($_POST['news_title']);
    $news_content = $conn->real_escape_string($_POST['news_content']);
    $author_id = (int)$_POST['author_id']; // Cast to int for safety
    $section_id = null; // You might want to add a dropdown for sections later if needed for entertainment

    // Default status to 'published'
    $status = 'published';
    $media_type = 'image';
    $media_caption = $conn->real_escape_string($_POST['media_caption'] ?? ''); // Optional caption

    $target_dir = "./uploads/"; // Adjust path if needed. Ensure this directory exists and is writable.
    $media_url = './uploads/default.jpg'; // Default image if none uploaded or upload fails

    // Check if an image file was uploaded
    if (isset($_FILES['news_image']) && $_FILES['news_image']['error'] == UPLOAD_ERR_OK) {
        $file_extension = pathinfo($_FILES['news_image']['name'], PATHINFO_EXTENSION);
        $unique_file_name = uniqid('news_image_') . '.' . strtolower($file_extension);
        $target_file = $target_dir . $unique_file_name;

        // Basic validation for image file
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array(strtolower($file_extension), $allowed_types)) {
            $message = "Error: Only JPG, JPEG, PNG, and GIF files are allowed for images.";
        } elseif ($_FILES['news_image']['size'] > 5000000) { // 5MB limit
            $message = "Error: Your image file is too large (max 5MB).";
        } else {
            if (move_uploaded_file($_FILES['news_image']['tmp_name'], $target_file)) {
                $media_url = '../uploads/' . $unique_file_name; // Store relative path for DB
            } else {
                $message = "Error: There was a problem uploading your image.";
            }
        }
    } else if ($_FILES['news_image']['error'] != UPLOAD_ERR_NO_FILE) {
        $message = "Error uploading image: " . $_FILES['news_image']['error'];
    }


    if (empty($message)) { // Only proceed if no previous errors
        // Start transaction for atomicity
        $conn->begin_transaction();

        try {
            // Insert into 'news' table
            $stmt_news = $conn->prepare("INSERT INTO news (category_id, section_id, author_id, news_title, news_content, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
            if (!$stmt_news) {
                throw new Exception("Prepare statement for news failed: " . $conn->error);
            }
            $stmt_news->bind_param("iiisss", $entertainment_category_id, $section_id, $author_id, $news_title, $news_content, $status);
            $stmt_news->execute();

            $news_id = $conn->insert_id; // Get the ID of the newly inserted news item

            // Insert into 'media' table
            $stmt_media = $conn->prepare("INSERT INTO media (news_id, media_type, media_url, media_caption, uploaded_at) VALUES (?, ?, ?, ?, NOW())");
            if (!$stmt_media) {
                throw new Exception("Prepare statement for media failed: " . $conn->error);
            }
            $stmt_media->bind_param("isss", $news_id, $media_type, $media_url, $media_caption);
            $stmt_media->execute();

            $conn->commit();
            $message = "Entertainment news uploaded successfully!";
            // Clear form fields after successful submission (optional)
            $_POST = array(); // Clears all POST data
            // You might want to redirect here: header("Location: some_success_page.php");

        } catch (Exception $e) {
            $conn->rollback(); // Rollback changes if anything went wrong
            $message = "Database Error: " . $e->getMessage();
            // If image was uploaded but DB insertion failed, delete the file
            if ($media_url !== './uploads/default.jpg' && file_exists($target_dir . basename($media_url))) {
                unlink($target_dir . basename($media_url));
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Entertainment News | APPEAL Admin</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align to top */
            min-height: 100vh;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 700px;
            box-sizing: border-box;
            margin-top: 20px;
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 2em;
            border-bottom: 2px solid #e91e63; /* Entertainment color */
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
            color: #555;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            font-size: 1em;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }
        input[type="text"]:focus,
        textarea:focus,
        select:focus {
            border-color: #e91e63;
            box-shadow: 0 0 5px rgba(233, 30, 99, 0.3);
            outline: none;
        }
        textarea {
            resize: vertical;
            min-height: 180px;
        }
        input[type="file"] {
            padding: 8px 0;
            display: block;
            width: 100%;
        }
        button {
            background-color: #e91e63; /* Entertainment color */
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            width: 100%;
        }
        button:hover {
            background-color: #c2185b; /* Darker shade */
            transform: translateY(-2px);
        }
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-weight: bold;
            text-align: center;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Upload Entertainment News</h1>
        <?php if ($message): ?>
            <div class="message <?= strpos($message, 'Error') !== false ? 'error' : 'success' ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($entertainment_category_id === null): ?>
            <div class="message error">
                The 'Entertainment' category was not found. Please ensure it exists in your `news_category` table.
            </div>
        <?php elseif (empty($authors)): ?>
            <div class="message error">
                No authors found. Please ensure authors are added to your `authors` table.
            </div>
        <?php else: ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="news_title">News Title:</label>
                    <input type="text" id="news_title" name="news_title" required
                           value="<?= htmlspecialchars($_POST['news_title'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="news_content">News Content:</label>
                    <textarea id="news_content" name="news_content" required><?= htmlspecialchars($_POST['news_content'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label for="author_id">Author:</label>
                    <select id="author_id" name="author_id" required>
                        <option value="">Select an Author</option>
                        <?php foreach ($authors as $author): ?>
                            <option value="<?= $author['author_id'] ?>"
                                <?= (isset($_POST['author_id']) && $_POST['author_id'] == $author['author_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($author['author_display_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="news_image">News Image:</label>
                    <input type="file" id="news_image" name="news_image" accept="image/*" required>
                </div>

                <div class="form-group">
                    <label for="media_caption">Image Caption (Optional):</label>
                    <input type="text" id="media_caption" name="media_caption"
                           value="<?= htmlspecialchars($_POST['media_caption'] ?? '') ?>">
                </div>

                <button type="submit">Upload Entertainment News</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
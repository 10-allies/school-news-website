<?php
$conn = new mysqli("localhost", "root", "", "newsdb");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sections = $conn->query("SELECT * FROM sections");
$authors = $conn->query("SELECT * FROM authors");

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $conn->real_escape_string($_POST["title"]);
    $content = $conn->real_escape_string($_POST["content"]);
    $section_id = (int)$_POST["section_id"];
    $author_id = (int)$_POST["author_id"];
    $status = "published";
    $created_at = date("Y-m-d H:i:s");

    $insert_news = "INSERT INTO news (news_title, news_content, section_id, author_id, status, created_at) 
                    VALUES ('$title', '$content', $section_id, $author_id, '$status', '$created_at')";

    if ($conn->query($insert_news)) {
        $news_id = $conn->insert_id;

        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $image_name = basename($_FILES['image']['name']);
            $upload_dir = "uploads/";
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Ensure upload dir exists
            }
            $upload_path = $upload_dir . $image_name;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $conn->query("INSERT INTO media (news_id, media_url, media_type) VALUES ($news_id, '$upload_path', 'image')");
            }
        }

        $message = "<div class='success'>✅ News uploaded successfully!</div>";
    } else {
        $message = "<div class='error'>❌ Error uploading news: " . $conn->error . "</div>";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Upload News</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 60%;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 25px;
        }
        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
            margin-top: 15px;
            color: #34495e;
        }
        input[type="text"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            margin-top: 5px;
            font-size: 16px;
        }
        input[type="file"] {
            margin-top: 10px;
        }
        button {
            margin-top: 25px;
            padding: 12px 20px;
            background-color: #2ecc71;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            width: 100%;
        }
        button:hover {
            background-color: #27ae60;
        }
        .success, .error {
            text-align: center;
            padding: 15px;
            margin-top: 20px;
            border-radius: 6px;
            font-weight: bold;
        }
        .success {
            background-color: #dff0d8;
            color: #2e7d32;
        }
        .error {
            background-color: #f8d7da;
            color: #c0392b;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Upload News</h2>
        <form action="" method="post" enctype="multipart/form-data">
            <label for="title">Title:</label>
            <input type="text" name="title" id="title" required>

            <label for="content">Content:</label>
            <textarea name="content" id="content" rows="6" required></textarea>

            <label for="section_id">Section:</label>
            <select name="section_id" id="section_id" required>
                <?php while ($row = $sections->fetch_assoc()): ?>
                    <option value="<?= $row['section_id'] ?>"><?= $row['section_name'] ?></option>
                <?php endwhile; ?>
            </select>

            <label for="author_id">Author:</label>
            <select name="author_id" id="author_id" required>
                <?php while ($row = $authors->fetch_assoc()): ?>
                    <option value="<?= $row['author_id'] ?>"><?= $row['author_display_name'] ?></option>
                <?php endwhile; ?>
            </select>

            <label for="image">Upload Image:</label>
            <input type="file" name="image" id="image" accept="image/*">

            <button type="submit">Upload News</button>
        </form>
        <?= $message ?>
    </div>

</body>
</html>

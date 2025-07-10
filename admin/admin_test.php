<?php
// index.php - News Upload Admin Page
session_start();
include '../connection/connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Fetch categories
$categoryStmt = $pdo->query("SELECT * FROM news_category ORDER BY category_name");
$categories = $categoryStmt->fetchAll(PDO::FETCH_ASSOC);

// Get user nickname for author field
$nickname = 'Unknown';
if ($_SESSION['role'] === 'author') {
    $stmt = $pdo->prepare("SELECT author_display_name FROM authors WHERE author_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch();
    $nickname = $row['author_display_name'] ?? 'Unknown';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>News Upload</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="container">
    <div class="sideArea">
        <div class="avatar">
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSCNOdyoIXDDBztO_GC8MFLmG_p6lZ2lTDh1ZnxSDawl1TZY_Zw" alt="Avatar">
            <div class="avatarName">Welcome,<br><span style="color: yellow; font-family: sans-serif;">
                <?php echo htmlspecialchars($nickname); ?></span></div>
        </div>
        <ul class="sideMenu">
            <h2>Upload</h2>
            <li><a href="#" onclick="loadUploadForm()"><i class="fa fa-upload"></i> Upload News</a></li>
            <li><a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a></li>
        </ul>
    </div>

    <div id="uploadFormContent" class="main-content" style="display:block; padding: 2em;">
        <h2>Upload News</h2>
        <form action="upload_handler.php" method="POST" enctype="multipart/form-data" class="announce-all">
            <div>
                <p>Category *</p>
                <select name="category_id" id="category" required onchange="loadSections(this.value)">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['category_id']; ?>"><?= htmlspecialchars($cat['category_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <p>Section *</p>
                <select name="section_id" id="section" required>
                    <option value="">-- Select Section --</option>
                </select>
            </div>

            <div>
                <p>Title *</p>
                <input type="text" name="news_title" required>
            </div>

            <div>
                <p>Content *</p>
                <textarea name="news_content" rows="6" required></textarea>
            </div>

            <div>
                <p>Media</p>
                <input type="file" name="media">
            </div>

            <input type="hidden" name="author_id" value="<?php echo $_SESSION['user_id']; ?>">

            <button type="submit" class="announce-btn">Submit News</button>
        </form>
    </div>
</div>

<script>
function loadSections(categoryId) {
    fetch(`load_sections.php?category_id=${categoryId}`)
    .then(response => response.text())
    .then(html => {
        document.getElementById('section').innerHTML = html;
    });
}
</script>
</body>
</html>

<?php
session_start();
include '../connection/connect.php'; // Ensure this path is correct for your 'connect.php' file

// --- Session and User Authentication ---
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    header('Location: admin_login.php');
    exit;
}

$nickname_set = false;
$nickname = 'Media club'; // Default nickname

$author = null; // Initialize $author to prevent undefined variable notice later

if ($_SESSION['role'] == 'author') {
    $stmt = $pdo->prepare("SELECT author_name, author_display_name FROM authors WHERE author_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($author && !empty($author['author_display_name'])) {
        $nickname_set = true;
        $nickname = $author['author_display_name'];
    }
}


$sections = [];
$authors_for_dropdown = []; 

try {
    $sections_stmt = $pdo->query("SELECT section_id, section_name FROM sections ORDER BY section_name ASC");
    $sections = $sections_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {

    $message = "<div class='error'>⚠ Error fetching sections: Database error.</div>";
}

try {
    $authors_stmt = $pdo->query("SELECT author_id, author_display_name FROM authors ORDER BY author_display_name ASC");
    $authors_for_dropdown = $authors_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
   
    $message = "<div class='error'>⚠ Error fetching authors for dropdown: Database error.</div>";
}


// --- Handle Nickname Submission ---
if (isset($_POST['set_nickname']) && !empty($_POST['nickname'])) {
    $new_nickname = trim($_POST['nickname']);

    // Update the author_display_name in the database
    $stmt_update_nickname = $pdo->prepare("UPDATE authors SET author_display_name = ? WHERE author_id = ?");
    $stmt_update_nickname->execute([$new_nickname, $_SESSION['user_id']]);

    // Update the flag and nickname variable
    $nickname_set = true;
    $nickname = $new_nickname;

    // Redirect to prevent form resubmission
    header("Location: admin.php");
    exit();
}

$authorName = $author['author_name'] ?? ''; // For displaying the current author's name if needed elsewhere

// --- Handle messages from sports_db.php redirect ---
$message = ""; // Initialize message variable here to ensure it's always defined
if (isset($_GET['status'])) {
    if ($_GET['status'] === 'success') {
        $message = "<div class='success'>✓ News uploaded successfully!</div>";
    } elseif ($_GET['status'] === 'error') {
        $error_msg = $_GET['msg'] ?? 'Unknown error.';
        $message = "<div class='error'>⚠ Error uploading news: " . htmlspecialchars($error_msg) . "</div>";
    } elseif ($_GET['status'] === 'file_error') {
        $message = "<div class='error'>⚠ Error moving uploaded file. Check permissions for the uploads folder.</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <title>THE APPEAL - ADMIN PANEL</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <?php if ($_SESSION['role'] == 'author' && !$nickname_set): ?>
    <div class="modal">
        <div class="modal-content">
            <h2>Set your nickname/Journalist name:</h2>
            <form method="POST">
                <input type="text" name="nickname" placeholder="Enter your nickname..." required>
                <br><br>
                <button type="submit" name="set_nickname">Set</button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <div class="container">
        <div class="sideArea">
            <div class="avatar">
                <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSCNOdyoIXDDBztO_GC8MFLmG_p6lZ2lTDh1ZnxSDawl1TZY_Zw" alt="">
                <div class="avatarName">Welcome, <br><span><?php echo htmlspecialchars($nickname); ?></span></div>
            </div>
            <ul class="sideMenu">
                <h2>Upload</h2>
                <li><a href="#" onclick="showContent('content4')"><i class="fa fa-newspaper"></i> Local news</a></li>
                <li><a href="#" onclick="showContent('content3')"><i class="fa fa-futbol"></i> Sports news</a></li>
                <li><a href="#" onclick="showContent('content2')"><i class="fa fa-film"></i> Entertainment</a></li>
                <li><a href="#" onclick="showContent('content1')"><i class="fa fa-bullhorn"></i> Announcement</a></li>
                <button class="settings-dropdown"><i class="fa fa-cog"></i> Settings</button>
                <div class="settings-dropdown-content">
                    <a href="#" onclick="loadProfileContent()"><i class="fa fa-user-circle"></i> Profile</a>
                    <a href="logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                </div>
            </ul>
        </div>
        
        <div id="welcomeContent" class="main-content">
            <div class="welcome-card">
                <h1>👋 Welcome, <?php echo htmlspecialchars($nickname); ?>!</h1>
                <p>We're happy to have you managing your content today.<br>Select a section from the left to get started.</p>
                <img src="../images/reporter.png" alt="Welcome Image" class="welcome-image">
            </div>
        </div>

        <div id="content1" class="main-content">
            <div class="announce-header">
                <h3>Give an announcement</h3>
            </div>
            <form action="announce.php" method="POST" enctype="multipart/form-data">
                <div class="announce-all">
                    <div class="announce-content">
                        <p>Write to your students what you want to share today *</p>
                        <textarea rows="6" placeholder="Write your announcement here..." name="announcement" required></textarea>
                    </div>

                    <div class="announce-file">
                        <p>Upload a file</p>
                        <input type="file" id="fileInput" name="announce_file">
                        <div class="file-preview" id="filePreview">No file uploaded yet.</div>
                    </div>
                    <div class="by">
                        <p>Announcement by:</p>
                        <div class="by-name">
                            <input type="text" placeholder="Enter your name" name="announcer_name">
                        </div>
                    </div>
                    <button class="announce-btn" type="submit">Announce</button>
                </div>
            </form>
        </div>

        <div id="content2" class="main-content" style="display:none;">
            <h2 class="section-title">🎬 Submit New Entertainment News</h2>
            <?php if (!empty($message)) echo $message; ?>
            <form id="entertainmentForm" action="entertain2.php" method="POST" enctype="multipart/form-data">
                <label for="entertainment_title">News title:</label>
                <input type="text" name="title" id="entertainment_title" required>

                <div class="entertainment-group">
                    <label for="entertainment_content">Content:</label>
                    <textarea name="content" id="entertainment_content" rows="8" required></textarea>
                    
                    <label for="author_id">Author:</label>
                    <select id="author_id" name="author_id" required>
                        <?php foreach ($authors_for_dropdown as $auth): ?>
                            <option value="<?php echo htmlspecialchars($auth['author_id']); ?>">
                                <?php echo htmlspecialchars($auth['author_display_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label for="entertainment_image">Image:</label>
                    <input type="file" name="image" id="entertainment_image" accept="image/*" required>
                </div>

                <input type="submit" class="entertainment-btn" value="POST NEWS">
            </form>
        </div>

        <div id="content3" class="main-content">
            <h2 class="section-title">⚽ Submit New Sports News</h2>

            <?php echo $message; ?>

            <form action="sports_database.php" method="post" enctype="multipart/form-data">
                <label for="title">Title:</label>
                <input type="text" name="title" id="title" required>

                <label for="content">Content:</label>
                <textarea name="content" id="content" rows="6" required></textarea>

                <label for="section_id">Section:</label>
                <select name="section_id" id="section_id" required>
                    <?php if (!empty($sections)): ?>
                        <?php foreach ($sections as $section): ?>
                            <option value="<?= htmlspecialchars($section['section_id']) ?>"><?= htmlspecialchars($section['section_name']) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No sections available</option>
                    <?php endif; ?>
                </select>

                <label for="author_id">Author:</label>
                <select id="author_id" name="author_id" required>
                    <?php if (!empty($authors_for_dropdown)): ?>
                        <?php foreach ($authors_for_dropdown as $auth): ?>
                            <option value="<?php echo htmlspecialchars($auth['author_id']); ?>">
                                <?php echo htmlspecialchars($auth['author_display_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No authors available</option>
                    <?php endif; ?>
                </select>

                <label for="image">Upload Image:</label>
                <input type="file" name="image" id="image" accept="image/*">

                <button type="submit">Upload News</button>
            </form>
        </div>

        <div id="content4" class="main-content" style="display:none;">
            <h2 class="section-title">📰 Submit New Local News</h2>
            <?php if (!empty($message)) echo $message; ?>
            <form id="localNewsForm" action="local_update.php" method="POST" enctype="multipart/form-data">
                <label for="local_title">News Title:</label>
                <input type="text" name="title" id="local_title" required>

                <label for="local_content">Content:</label>
                <textarea name="content" id="local_content" rows="8" required></textarea>

                <label for="local_author_id">Author:</label>
                <select id="local_author_id" name="author_id" required>
                    <?php if (!empty($authors_for_dropdown)): ?>
                        <?php foreach ($authors_for_dropdown as $auth): ?>
                            <option value="<?php echo htmlspecialchars($auth['author_id']); ?>">
                                <?php echo htmlspecialchars($auth['author_display_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="">No authors available</option>
                    <?php endif; ?>
                </select>

                <label for="local_image">Upload Image:</label>
                <input type="file" name="image" id="local_image" accept="image/*" required>

                <button type="submit" class="local-btn">POST LOCAL NEWS</button>
            </form>
        </div>

        <div id="profileContent" class="main-content" style="display:none;">
            <div class="profile-header">
                <h3>Profile Settings</h3>
            </div>
            <form id="profileForm" method="POST" action="profile.php">
                <p id="danger">
                    <i class="fa fa-warning" style="font-size: 1.5em; color: #ff0000;"></i>
                    <span>Remember that changing your profile and modifying anything is done <strong style="color: red; font-size: 1.2em;">once in 31 days</strong>.</span>
                </p>
                <div class="profile-form-group">
                    <label>Full name:</label>
                    <div>
                        <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($authorName); ?>" readonly>
                        <button type="button" class="edit-btn" onclick="enableEditing('full_name')">Edit</button>
                    </div>
                </div>
                <div class="profile-form-group">
                    <label>Nickname:</label>
                    <div>
                        <input type="text" name="nickname" id="nickname" value="<?php echo htmlspecialchars($nickname); ?>" readonly>
                        <button type="button" class="edit-btn" onclick="enableEditing('nickname')">Edit</button>
                    </div>
                </div>

                <div class="profile-form-group">
                    <label>New Password:</label>
                    <input type="password" name="new_password" id="new_password" placeholder="Enter new password...">
                </div>

                <div class="profile-form-group">
                    <label>Secret Code:</label>
                    <p id="secret_code">
                        <i class="fa fa-exclamation-triangle" style="font-size: 1.8em; color: #ff9800;"></i>
                        <span>To change the secret code, you must contact the super admin. <strong style="color: #d32f2f;">Contact the super admin physically.</strong></span>
                    </p>
                </div>

                <button type="submit" class="profile-btn">Save Changes</button>
            </form>
        </div>
    </div>

    <script>
        window.onload = function() {
            showContent('welcomeContent');
        };

        function loadProfileContent() {
            var contents = document.querySelectorAll('.main-content');
            contents.forEach(function(content) {
                content.style.display = 'none';
            });

            var profileSection = document.getElementById('profileContent');
            profileSection.style.display = 'block';
        }

        function enableEditing(fieldId) {
            document.getElementById(fieldId).removeAttribute('readonly');
            document.getElementById(fieldId).focus();
        }

        var settingsDropdown = document.querySelector(".settings-dropdown");
        settingsDropdown.addEventListener("click", function() {
            this.classList.toggle("active");
            var dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
                dropdownContent.style.display = "none";
            } else {
                dropdownContent.style.display = "block";
            }
        });

        function showContent(contentId) {
            var contents = document.querySelectorAll('.main-content');
            contents.forEach(function(content) {
                content.style.display = 'none';
            });
            
            var selectedContent = document.getElementById(contentId);
            selectedContent.style.display = 'block';
        }

        const fileInput = document.getElementById('fileInput');
        const filePreview = document.getElementById('filePreview');
      
        if (fileInput && filePreview) {
            fileInput.addEventListener('change', function () {
                const file = this.files[0];
          
                if (file) {
                    let html = `<strong>Selected File:</strong> ${file.name}`;
          
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            html += `<br><img src="${e.target.result}" alt="Image Preview">`;
                            filePreview.innerHTML = html;
                        };
                        reader.readAsDataURL(file);
                    } else {
                        filePreview.innerHTML = html;
                    }
                } else {
                    filePreview.innerHTML = "No file uploaded yet.";
                }
            });
        }
    </script>
</body>
</html>
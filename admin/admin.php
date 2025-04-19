<?php
session_start();
include '../connection/connect.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: admin_login.php');
    exit;
}

$nickname_set = false;
$nickname = 'Media club';

if ($_SESSION['role'] == 'author') {
    
    $stmt = $pdo->prepare("SELECT author_display_name FROM authors WHERE author_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($author && !empty($author['author_display_name'])) {
        $nickname_set = true;
        $nickname = $author['author_display_name'];
    }
}

if (isset($_POST['set_nickname']) && !empty($_POST['nickname'])) {
    $new_nickname = trim($_POST['nickname']);

   
    $stmt = $pdo->prepare("UPDATE authors SET author_display_name = ? WHERE author_id = ?");
    $stmt->execute([$new_nickname, $_SESSION['user_id']]);

    
    $nickname_set = true;

    
    header("Location: admin.php");
    exit();
}
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.cdnfonts.com/css/tilt-prism" rel="stylesheet">
        <link href="https://fonts.cdnfonts.com/css/equine" rel="stylesheet">
        <link rel="stylesheet" href="admin.css?v=9.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <title>Admin panel</title>
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
                    <div class="avatarName">Welcome, <br><span style="color: yellow;"><?php echo htmlspecialchars($nickname); ?></span> </div>

                </div>
                <ul class="sideMenu">
                    <h2>Uplaod</h2>
                    <li><a href=""><i class="fa fa-newspaper-o"></i> Local news</a></li>
                    <button class="dropdown-btn"><i class="fa fa-futbol-o"></i> Sports 
                        <i class="fa fa-caret-down"></i>
                    </button>
                    <div class="dropdown-container">
                        <a href="#"><i class="fa fa-soccer-ball-o"></i> Football</a>
                        <a href="#"><i class="fa fa-dribbble"></i>  Basketball</a>
                        <a href="#"><i class="fa fa-trophy"></i> Volleyball</a>
                        <a href="#"><i class="fa fa-trophy"></i> Hope premier league</a>
                    </div>
                    <li><a href="#"><i class="fa fa-film"></i> entertainment</a></li>
                    <li><a href="#" onclick="showContent('content1')"><i class="fa fa-bullhorn"></i> Announcement</a></li>
                    <button class="settings-dropdown"><i class="fa fa-cog"></i> Settings</button>
                    <div class="settings-dropdown-content">
                        <a href="#" onclick="loadProfileContent()"><i class="fa fa-user-circle"></i> Profile</a>
                        <a href="admin_logout.php"><i class="fa fa-sign-out"></i> Logout</a>
                    </div>

                </ul>
            </div>
            <div id="content1" class="main-content">
                <div class="announce-header">
                <h3>Give an announcement</h3>
                </div>
                <form action="annunce.php" method="POST" enctype="multipart/form-data">
                <div class="announce-all">
                    <div class="announce-content">
                      <p>Write to your students what you want to share today *</p>
                      <textarea rows="6" placeholder="Write your announcement here..." name="announcement"></textarea>
                    </div>
                  
                    <div class="announce-file">
                      <p>Upload a file</p>
                      <input type="file" id="fileInput" name ="announce_file">
                      <div class="file-preview" id="filePreview">No file uploaded yet.</div>
                    </div>
                    <div class="by">
                        <p>Announcement by:</p>
                        <div class="by-name">
                            <input type="text" placeholder="Enter your name" name="announcer_name" style="width: 100%; padding: 10px; border-radius: 5px; background-color: #2c2c2c; color: white; border: 1px solid #444;">
                    </div>
                  </div>
                  <button  class="announce-btn" type="submit">Announce</button>
                </div>
          </form>
            </div>
            <div id="profileContent" class="main-content" style="display:none;">
    <div class="profile-header">
        <h3>Profile Settings</h3>
    </div>
    <form id="profileForm" method="POST" action="profile.php">
    <div class="profile-form-group">
            <label>Full name:</label>
            <div style="display: flex; align-items: center;">
                <input type="text" name="full_name" id="full_name" value="<?php echo htmlspecialchars($authorName); ?>" readonly>
                <button type="button" class="edit-btn" onclick="enableEditing('full_name')">Edit</button>
            </div>
        </div>    
    <div class="profile-form-group">
            <label>Nickname:</label>
            <div style="display: flex; align-items: center;">
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
            <input type="text" name="secret_code" id="secret_code" value="<?php echo htmlspecialchars($secret_code); ?>" readonly>
            <small style="color: orange;">Contact Super Admin to change your Secret Code.</small>
        </div>

        <button type="submit" class="profile-btn">Save Changes</button>
    </form>
</div>

            <script>
function loadProfileContent() {
    var contents = document.querySelectorAll('.main-content');
    contents.forEach(function(content) {
        content.style.display = 'none';
    });

    var profileSection = document.getElementById('profileContent');
    profileSection.style.display = 'block';
}
</script>
<script>
function enableEditing(fieldId) {
    document.getElementById(fieldId).removeAttribute('readonly');
    document.getElementById(fieldId).focus();
}
</script>


            <script>
                /* Loop through all dropdown buttons to toggle between hiding and showing its dropdown content - This allows the user to have multiple dropdowns without any conflict */
                var dropdown = document.getElementsByClassName("dropdown-btn");
                var i;
                
                for (i = 0; i < dropdown.length; i++) {
                dropdown[i].addEventListener("click", function() {
                    this.classList.toggle("active");
                    var dropdownContent = this.nextElementSibling;
                    if (dropdownContent.style.display === "block") {
                    dropdownContent.style.display = "none";
                    } else {
                    dropdownContent.style.display = "block";
                    }
                });
                }
                </script>
                <script>
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
</script>


                <script>
                    function showContent(contentId) {
            
                        var contents = document.querySelectorAll('.main-content');
                        contents.forEach(function(content) {
                            content.style.display = 'none';
                        });
                        
                    
                        var selectedContent = document.getElementById(contentId);
                        selectedContent.style.display = 'block';
                    }
                </script>
                <script>
                    const fileInput = document.getElementById('fileInput');
                    const filePreview = document.getElementById('filePreview');
                  
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
                  </script>
                  <script>
function toggleDropdown() {
    var dropdown = document.getElementById("avatarDropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
}


window.onclick = function(event) {
    if (!event.target.closest('.avatar')) {
        document.getElementById('avatarDropdown').style.display = 'none';
    }
}
</script>

    </body>
    </html>
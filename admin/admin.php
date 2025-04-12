    <?php
    include 'announce.php';
    include '../connection/connect.php';
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.cdnfonts.com/css/tilt-prism" rel="stylesheet">
        <link href="https://fonts.cdnfonts.com/css/equine" rel="stylesheet">
        <link rel="stylesheet" href="admin.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <title>Admin panel</title>
    </head>
    <body>
    <div class="container">
            <div class="sideArea">
                <div class="avatar">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSCNOdyoIXDDBztO_GC8MFLmG_p6lZ2lTDh1ZnxSDawl1TZY_Zw" alt="">
                    <div class="avatarName">Welcome,<br>Media club</div>
                </div>
                <ul class="sideMenu">
                    <h2>Uplaod</h2>
                    <li><a href="">Local news</a></li>
                    <button class="dropdown-btn">Sports 
                        <i class="fa fa-caret-down"></i>
                    </button>
                    <div class="dropdown-container">
                        <a href="#">Football</a>
                        <a href="#">Basketball</a>
                        <a href="#">Volleyball</a>
                        <a href="#">Hope premier league</a>
                    </div>
                    <li><a href="#">entertainment</a></li>
                    <li><a href="#" onclick="showContent('content1')">Announcement</a></li>
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
                  
                        // If it's an image, preview it
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
    </body>
    </html>
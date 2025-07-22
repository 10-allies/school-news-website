<?php 
session_start();
// Check if the session is not null , if it is , set a default value
$name=isset($_SESSION['name'])? $_SESSION['name']:"Friend";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="ally.css">
     <link rel="stylesheet" href="bootstrap-5.3.7-dist/css/bootstrap.css">
</head>
<body>
    
    <div class="second-content">
       
 </div>
 <div class="first-content">
      <h1>Hello, <?php echo htmlspecialchars($name);  ?></h1>
      
<h2>Ally</h2>
 <i onclick="togglePopup()">
        ☰
 <div id="showpop"class="showpop"> <a href="../index.php"> Return Home </a> </div>
</i>
     
      
      <div class="search-bar">
        <textarea name="" id="user-input" placeholder="Ask Ally"></textarea>
        <label for="upload"  id="add">
            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-plus" viewBox="0 0 16 16">
  <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4"/>
</svg>
            <input type="file" id="upload" style="display: none;">
        </label>
        <button onclick="askAlly()" id="button"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-send" viewBox="0 0 16 16">
  <path d="M15.854.146a.5.5 0 0 1 .11.54l-5.819 14.547a.75.75 0 0 1-1.329.124l-3.178-4.995L.643 7.184a.75.75 0 0 1 .124-1.33L15.314.037a.5.5 0 0 1 .54.11ZM6.636 10.07l2.761 4.338L14.13 2.576zm6.787-8.201L1.591 6.602l4.339 2.76z"/>
</svg></button>
      </div>
 </div>
 <div id="response"></div>
 
</body>
<script>
    function togglePopup() {
         const pop = document.getElementById('showpop');
         pop.classList.toggle('visible');
    }
    const textarea =document.getElementById('user-input');
const button = document.getElementById('button');
const add=document.getElementById('add');
textarea.addEventListener('input',()=>{
    textarea.style.height= 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
    if (textarea.value.trim()!== ''){
        button.style.display = 'inline-block';
        
    } else{
        button.style.display ='none';
        add.style.display='none';
    }
})
 
function askAlly() {
    const query = textarea.value.trim();
    if (query !== '') {
        // Redirect to response.php, passing question via GET
        window.location.href = "response.php?question=" + encodeURIComponent(query);
    }
}
</script>
</html>

<?php


$question = $_GET['question'] ?? '';


// Call the Gemini API
$api_key = "AIzaSyBjFtaHem1lbLt7o7tX1-pf4spnD6j7nAo";
$api_url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash-001:generateContent?key=" . $api_key;

$data = [
    "contents" => [
        ["parts" => [["text" => $question]]]
    ]
];

$ch = curl_init($api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

$response = curl_exec($ch);

if (curl_errno($ch)) {
    $answer = "Sorry, there was an error contacting Ally AI.";
} else {
    $result = json_decode($response, true);
    $answer = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, no answer returned.";
}
curl_close($ch);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="response.css">
     <link rel="stylesheet" href="bootstrap-5.3.7-dist/css/bootstrap.css">
</head>
<body>
    
    <div class="second-content">
       
 </div>
 <div class="first-content">
      <p class="lead"> <?php echo nl2br(htmlspecialchars($answer)); ?></p>
      
<h2>Ally</h2>
 <i>
        <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16">
  <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
</svg>
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
    const textarea =document.getElementById('user-input');
const button = document.getElementById('button');
const add=document.getElementById('add');
textarea.addEventListener('input',()=>{
    textarea.style.height= 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
    if (textarea.value.trim()!== ''){
        button.style.display = 'inline-block';
        add.style.display='inline-block';
    } else{
        button.style.display ='none';
        add.style.display='none';
    }
})
   window.onload = () => {
        scrollToBottom();
    };

    function scrollToBottom() {
        window.scrollTo({
            top: document.body.scrollHeight,
            behavior: "smooth"
        });
    }

 
function askAlly() {
    const query = textarea.value.trim();
    if (query !== '') {
        // Redirect to response.php, passing question via GET
        window.location.href = "response.php?question=" + encodeURIComponent(query);
    }
}
</script>
</html>

<?php 
if (!isset($_SESSION['name']) && isset($_COOKIE['user_name'])) {
    header("Location: Allyai.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Ally AI</title>
    <link rel="stylesheet" href="bootstrap-5.3.7-dist/css/bootstrap.css">
    <style>
        body {
    margin: 0;
    padding: 0;
    overflow: hidden;
    background-color: black; 
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh; 
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: white;
}

#welcome-container {
    text-align: center;
     
}

#welcome-text {
    font-size: 3em; 
    font-weight: bold;
    color: white;

    overflow: hidden; 
    border-right: .10em solid rgba(255, 255, 255, 0.7); 
    margin: 0 auto; 
    display: inline-block; 
    padding-right: 0px ; 
    animation: none; 
    
}


@keyframes typing {
  from { width: 0 }
  to { width: 100% }
}


@keyframes blink-caret {
  from, to { border-color: transparent }
  50% { border-color: rgba(255, 255, 255, 0.7) }
}

.sign-in-button {
    background-color: #007bff;
    color: white;
    padding: 12px;
    border: none;
    border-radius: 5px;
    font-size: 1em;
    cursor: pointer;
    
    opacity: 0;
    
    transition: opacity 0.8s ease-in-out, transform 0.8s ease-in-out;
}


.sign-in-button.show {
    opacity: 1; 
    transform: translateY(20%); 
    margin-left: 45%;
}

.sign-in-button:hover {
    background-color: #0056b3;
}
.main-content {
    display: flex;
    flex-direction: column;
}
a{
    color:white;
    text-decoration: none;
}
    </style>
</head>
<body>
  <div class="main-content">
    <div id="welcome-container" >
        <p id="welcome-text" class="text-wrap"></p>
        
    </div>
    <div class="page"> <button id="signInButton" class="sign-in-button btn btn-block btn-primary "><a href="form.html">Sign in</a> </button></div>
 
</div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    const welcomeTextElement = document.getElementById('welcome-text');
    const signInButton = document.getElementById('signInButton');
    const textToType = "Welcome to  Ally AI";
    const typingSpeed = 100; 
    const delayBeforeSignIn = 1000; 

    let i = 0;

    function typeWriter() {
        if (i < textToType.length) {
            welcomeTextElement.innerHTML += textToType.charAt(i);
            i++;
            setTimeout(typeWriter, typingSpeed);
        } else {
            
            welcomeTextElement.style.borderRight = 'none'; 
            setTimeout(() => {
                signInButton.classList.add('show');
            }, delayBeforeSignIn);
        }
    }

    
    welcomeTextElement.style.animation = `typing ${textToType.length * typingSpeed / 1000}s steps(${textToType.length}, end), blink-caret .75s step-end infinite`;

    
    typeWriter();

});
    </script>
</body>
</html>
const textarea =document.getElementById('userinput');
const button = document.getElementById('button');
textarea.addEventListener('input',()=>{
    textarea.style.height= 'auto';
    textarea.style.height = `${textarea.scrollHeight}px`;
    if (textarea.trim()!== ''){
        button.style.display = 'inline-block';
    } else{
        button.style.display ='none';
    }
})
let currentIndexs = 0;

function moveSlide(direction) {
    const slides = document.getElementById('slides');
    const totalSlides = slides.children.length;

    currentIndexs += direction;

    if (currentIndexs < 0) {
        currentIndexs = totalSlides - 1;
    }
    if (currentIndexs >= totalSlides) {
        currentIndexs = 0;
    }

    slides.style.transform = `translateX(-${currentIndexs * 100}%)`;
}

setInterval(() => { 
    moveSlide(1); 
}, 5000);

function showContent(event, id) {
    event.preventDefault();

    const slider = document.querySelector('.sliding-news');
    if (slider) {
        slider.style.display = 'none';
    }

    const target = document.getElementById(id);
    if (target) {
        target.style.display = 'block';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const anounce = document.getElementById('anounce');
    if (anounce) {
        anounce.style.display = 'none';
    }
});

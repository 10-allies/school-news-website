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
const apiKey = 'f59b33991db34a529fb115953252104'; // Insert your API key here
        const latitude = -1.9746; // Latitude for Kigali
        const longitude = 30.0616; // Longitude for Kigali
        const weatherSymbolElement = document.getElementById('weather-symbol');
        const weatherContainer = document.getElementById('weatherContainer');
        const displayDuration = 3000; // 5 seconds in milliseconds

        function updateTime() {
            const now = new Date();
            const hours = now.getHours().toString().padStart(2, '0');
            const minutes = now.getMinutes().toString().padStart(2, '0');
            document.getElementById('current-time').textContent = `${hours}:${minutes} PM`; // Assuming the image time was PM
        }

        async function fetchWeather() {
            const url = `http://api.weatherapi.com/v1/current.json?key=${apiKey}&q=${latitude},${longitude}`;

            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error('Network response was not ok ' + response.statusText);
                }
                const data = await response.json();
                displayWeather(data);
                showWeatherPopup(); // Show the pop-up after fetching data
                setTimeout(hideWeatherPopup, displayDuration); // Hide after 5 seconds
            } catch (error) {
                console.error('Error fetching weather:', error);
                // Optionally display an error message in the app
            }
        }

        function displayWeather(data) {
            const temperature = Math.round(data.current.temp_c);
            const humidity = data.current.humidity;
            document.getElementById('temperature').textContent = temperature;
            document.getElementById('condition').textContent = data.current.condition.text;
            document.getElementById('wind').textContent = `${data.current.wind_kph} km/h `; // Added the arrow
            document.getElementById('humidity').textContent = `${data.current.humidity}%`;
            document.getElementById('feels-like').textContent = `${Math.round(data.current.feelslike_c)}°`;
            document.getElementById('visibility').textContent = `${data.current.vis_km} km`;
            document.getElementById('uv-index').textContent = `${data.current.uv}`;
            document.getElementById('pressure').textContent = `${data.current.pressure_mb} mb`;
            document.getElementById('dew-point').textContent = `${Math.round(data.current.dewpoint_c)}°`;

            if (temperature <= 5 || humidity>= 80) {
                weatherSymbolElement.textContent = '\u2614'; // Umbrella emoji for cold/rainy
                weatherSymbolElement.style.color = 'lightblue'; // Change color to indicate cold
            } else {
                weatherSymbolElement.textContent = '\u2600'; // Sun emoji
                weatherSymbolElement.style.color = 'orange';
            }
            if (temperature <= 25 || temperature>=10 || humidity<= 70) {
                weatherSymbolElement.textContent = '\u{1F319}'; // Umbrella emoji for cold/rainy
                weatherSymbolElement.style.color = 'lightblue'; // Change color to indicate cold
            }
        }

        function showWeatherPopup() {
            weatherContainer.classList.add('show');
        }

        function hideWeatherPopup() {
            weatherContainer.classList.remove('show');
        }

        function reportWeather() {
            alert("Reporting weather feature is currently not implemented.");
        }

        updateTime(); // Initial time update
        setInterval(updateTime, 60000); // Update time every minute
        fetchWeather(); // Fetch weather and trigger the pop-up

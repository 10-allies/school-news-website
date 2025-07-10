<?php

define('OWM_API_KEY', '224fc6179867ff9652efc34a02dc58e2');
define('OWM_API_URL', 'http://api.openweathermap.org/data/2.5/weather');


$city_name = 'Kigali';
$country_code = 'RW'; 
$units = 'metric';


$cache_dir = __DIR__ . '/cache/'; 
$cache_file = $cache_dir . 'weather_cache.json';
$cache_lifetime = 60 * 15; 

$weather_data = null;
$weather_error_message = null;


if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0775, true);
}
if (!is_writable($cache_dir)) {
    $weather_error_message = "Cache directory is not writable. Please check permissions for: " . htmlspecialchars($cache_dir);
}


// Check cache first
if (empty($weather_error_message) && file_exists($cache_file) && (filemtime($cache_file) + $cache_lifetime) > time()) {
    $cached_content = file_get_contents($cache_file);
    $weather_data = json_decode($cached_content, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        $weather_error_message = "Error decoding cached weather data.";
        $weather_data = null;
        unlink($cache_file);
    }
}

// If no cached data or cache expired, fetch from API
if (empty($weather_data) && empty($weather_error_message)) {
    if (OWM_API_KEY === '224fc6179867ff9652efc34a02dc58e2' && !is_string(OWM_API_KEY)) { 
         $weather_error_message = "Please replace 'YOUR_OPENWEATHERMAP_API_KEY' with your actual OpenWeatherMap API key.";
    } else {
        // Build the API request URL
        $api_url = OWM_API_URL . "?q=" . urlencode($city_name) . "," . urlencode($country_code) . "&units=" . $units . "&appid=" . OWM_API_KEY;

        // Initialize cURL session
        $ch = curl_init();

        // Set cURL options
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        // Execute cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $weather_error_message = 'cURL Error: ' . curl_error($ch);
        } else {
            // Decode JSON response
            $decoded_response = json_decode($response, true);

            // Check if API returned an error
            if ($decoded_response && isset($decoded_response['cod']) && $decoded_response['cod'] != 200) {
                $weather_error_message = 'Weather API Error: ' . (isset($decoded_response['message']) ? $decoded_response['message'] : 'Unknown error');
            } elseif ($decoded_response) {
                $weather_data = $decoded_response;
                // Save to cache
                if (is_writable($cache_dir)) {
                    file_put_contents($cache_file, json_encode($weather_data));
                } else {
                    $weather_error_message = "Cache directory is not writable, weather data not cached.";
                }
            } else {
                $weather_error_message = 'Failed to decode weather data or empty response.';
            }
        }

        // Close cURL session
        curl_close($ch);
    }
}


if (empty($weather_data) && !empty($weather_error_message) && file_exists($cache_file)) {
    $cached_content = file_get_contents($cache_file);
    $temp_weather_data = json_decode($cached_content, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $weather_data = $temp_weather_data;
        $weather_error_message .= " (Displaying potentially stale cached data due to recent API fetch failure)";
    }
}

?>
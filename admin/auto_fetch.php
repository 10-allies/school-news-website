<?php
$api_key = "dcd61f08ed474d42ba324b6dcfd1db5a"; 

// Database connection
include '../connection/connect.php';

// Log file for tracking execution
$log_file = "../logs/auto_news.log";
$log_dir = dirname($log_file);
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0755, true);
}

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] $message\n", FILE_APPEND);
    echo "$message\n";
}


$last_run_file = "../logs/last_run.txt";
if (file_exists($last_run_file)) {
    $last_run = file_get_contents($last_run_file);
    $time_diff = time() - (int)$last_run;
    
    
    if ($time_diff < 86400) {
        $hours_left = round((86400 - $time_diff) / 3600, 1);
        writeLog("⏰ Script ran recently. Next run in $hours_left hours.");
        exit;
    }
}

writeLog("========================================");
writeLog("🚀 Starting automatic news fetch");
writeLog("========================================");

// Set author_id to 8 (system/automatic)
$author_id = 8;
writeLog("✅ Using author ID: $author_id");


$categories = [
    'local' => ['api_cat' => 'general', 'cat_id' => 5, 'country' => 'us'],
    'entertainment' => ['api_cat' => 'entertainment', 'cat_id' => 3, 'country' => 'us'],
    'sports' => ['api_cat' => 'sports', 'cat_id' => 2, 'country' => 'us']
];

// Sports section mapping
$sports_sections = [
    'football' => 1,
    'basketball' => 2,
    'volleyball' => 3
];

// Ensure uploads folder exists
$uploads_dir = "../uploads/";
if (!file_exists($uploads_dir)) {
    mkdir($uploads_dir, 0755, true);
}

// Function to fetch news with error handling
function fetchNews($url) {
    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "User-Agent: Mozilla/5.0\r\n",
            'ignore_errors' => true,
            'timeout' => 15
        ]
    ]);
    
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        return ['error' => 'Failed to fetch data', 'details' => error_get_last()];
    }
    
    if (isset($http_response_header[0])) {
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $http_response_header[0], $matches);
        $status_code = isset($matches[1]) ? (int)$matches[1] : 0;
        
        if ($status_code === 401) {
            return ['error' => 'API Key Invalid or Expired', 'status' => 401];
        } elseif ($status_code === 429) {
            return ['error' => 'Rate Limit Exceeded', 'status' => 429];
        } elseif ($status_code !== 200) {
            return ['error' => "HTTP Error: $status_code", 'status' => $status_code];
        }
    }
    
    return ['success' => true, 'data' => $response];
}

// Function to determine sports section based on content
function getSportsSection($title, $description, $sports_sections) {
    $text = strtolower($title . ' ' . $description);
    
    foreach ($sports_sections as $sport => $section_id) {
        if (strpos($text, $sport) !== false) {
            return $section_id;
        }
    }
    
    return null; // Other sports
}

$total_inserted = 0;
$total_skipped = 0;
$total_errors = 0;

foreach ($categories as $user_cat => $cat_info) {
    writeLog("\n--- Processing $user_cat ---");
    
    
    $url = 'https://newsapi.org/v2/top-headlines?'
         . 'apiKey=' . $api_key
         . '&category=' . $cat_info['api_cat']
         . '&country=' . $cat_info['country']
         . '&pageSize=50'; 
    
    writeLog("Fetching from NewsAPI.org...");
    
    // Fetch data with error handling
    $result = fetchNews($url);
    
    if (isset($result['error'])) {
        writeLog("❌ Error: " . $result['error']);
        if (isset($result['status']) && $result['status'] === 401) {
            writeLog("⚠️  Your API key is invalid. Please:");
            writeLog("   1. Visit https://newsapi.org/register");
            writeLog("   2. Get your free API key");
            writeLog("   3. Replace it in this script");
        }
        $total_errors++;
        continue;
    }
    
    $data = json_decode($result['data'], true);
    
    if (!isset($data['status']) || $data['status'] !== 'ok') {
        writeLog("API Error: " . ($data['message'] ?? 'Unknown error'));
        $total_errors++;
        continue;
    }
    
    if (!isset($data['articles']) || empty($data['articles'])) {
        writeLog("No articles found for $user_cat");
        continue;
    }
    
    // Process articles - fetch exactly 10 per category
    $articles = $data['articles'];
    writeLog("Found " . count($articles) . " articles from API");
    
    $inserted_count = 0;
    $target_count = 10;
    
    foreach ($articles as $article) {
        
        if ($inserted_count >= $target_count) {
            writeLog("✓ Reached target of $target_count articles for $user_cat");
            break;
        }
        $title = $article['title'] ?? '';
        $description = $article['description'] ?? '';
        $content = $article['content'] ?? $description;
        $source_url = $article['url'] ?? '';
        $image_url = $article['urlToImage'] ?? '';
        $published = $article['publishedAt'] ?? date('Y-m-d H:i:s');
        $source_name = $article['source']['name'] ?? 'Unknown';
        
        // Skip if no title or content
        if (empty($title) || empty($content)) {
            writeLog("⏭️  Skipping article with no title or content");
            $total_skipped++;
            continue;
        }
        
        // Remove [+X chars] suffix that NewsAPI adds
        $content = preg_replace('/\s*\[\+\d+\s+chars\]$/', '', $content);
        
        // Duplicate check
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM news WHERE news_title = :title");
        $stmt->execute(['title' => $title]);
        if ($stmt->fetchColumn() > 0) {
            writeLog("⏭️  Skipping duplicate: " . substr($title, 0, 50) . "...");
            $total_skipped++;
            continue;
        }
        
        // Determine section_id (for sports only, null for local and entertainment)
        $section_id = null;
        if ($user_cat === 'sports') {
            $section_id = getSportsSection($title, $content, $sports_sections);
            if ($section_id) {
                $sport_name = array_search($section_id, $sports_sections);
                writeLog("🏆 Sport detected: " . ucfirst($sport_name) . " (section_id: $section_id)");
            } else {
                writeLog("🏃 Other sport (section_id: NULL)");
            }
        }
        
        // Prepare news content with source attribution
        $news_content = $content . "\n\n";
        $news_content .= "Source: " . $source_name;
        if (!empty($source_url)) {
            $news_content .= " - Read more at: " . $source_url;
        }
        
        // Insert news into database
        $status = 'published';
        $created_at = date('Y-m-d H:i:s', strtotime($published));
        $updated_at = date('Y-m-d H:i:s');
        
        $sql = "INSERT INTO news 
                (category_id, section_id, author_id, news_title, news_content, status, created_at)
                VALUES 
                (:cat_id, :section_id, :author_id, :news_title, :news_content, :status, :created_at)";
        
        $params = [
            'cat_id' => $cat_info['cat_id'],
            'section_id' => $section_id,
            'author_id' => $author_id,
            'news_title' => $title,
            'news_content' => $news_content,
            'status' => $status,
            'created_at' => $created_at
        ];
        
        $stmt = $pdo->prepare($sql);
        
        try {
            $stmt->execute($params);
            $news_id = $pdo->lastInsertId();
            writeLog("✅ Inserted news: " . substr($title, 0, 50) . "... (ID: $news_id)");
            $total_inserted++;
            $inserted_count++; // Increment counter for this category
            
            // Download and save image to media table
            if (!empty($image_url)) {
                $image_data = @file_get_contents($image_url);
                
                if ($image_data !== false) {
                    // Get image extension from URL or default to jpg
                    $path_info = pathinfo(parse_url($image_url, PHP_URL_PATH));
                    $extension = isset($path_info['extension']) ? $path_info['extension'] : 'jpg';
                    
                    // Validate extension
                    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (!in_array(strtolower($extension), $allowed_ext)) {
                        $extension = 'jpg';
                    }
                    
                    $filename = 'news_img_' . uniqid() . '.' . $extension;
                    $file_path = $uploads_dir . $filename;
                    
                    if (file_put_contents($file_path, $image_data)) {
                        $media_url = '../uploads/' . $filename;
                        
                        // Insert into media table
                        $media_sql = "INSERT INTO media 
                                      (news_id, media_url, media_type, media_caption)
                                      VALUES 
                                      (:news_id, :media_url, :media_type, :media_caption)";
                        
                        $media_params = [
                            'news_id' => $news_id,
                            'media_url' => $media_url,
                            'media_type' => 'image',
                            'media_caption' => $title
                        ];
                        
                        $media_stmt = $pdo->prepare($media_sql);
                        
                        try {
                            $media_stmt->execute($media_params);
                            writeLog("📷 Image saved to media table (media_id: " . $pdo->lastInsertId() . ")");
                        } catch (PDOException $e) {
                            writeLog("⚠️  Error inserting media: " . $e->getMessage());
                        }
                    } else {
                        writeLog("⚠️  Failed to save image to disk");
                    }
                } else {
                    writeLog("⚠️  Failed to download image from URL");
                }
            } else {
                writeLog("ℹ️  No image available for this article");
            }
            
        } catch (PDOException $e) {
            writeLog("❌ Error inserting news: " . $e->getMessage());
            $total_errors++;
        }
        
        sleep(1); 
    }
    
    sleep(2); 
}

// Update last run timestamp
file_put_contents($last_run_file, time());

writeLog("\n=== News Import Summary ===");
writeLog("✅ Successfully inserted: $total_inserted articles");
writeLog("⏭️  Skipped (duplicates/invalid): $total_skipped articles");
writeLog("❌ Errors: $total_errors articles");
writeLog("===========================");
writeLog("✅ Script completed successfully");
writeLog("⏰ Next run scheduled in 24 hours");
writeLog("========================================\n");
?>
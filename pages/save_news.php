<?php

include 'db2.php';


$upload_dir = "../uploads/";
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}


$title = $_POST['title'];
$content = $_POST['content'];


$image_name = "";
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $image = $_FILES['image'];
    $image_name = time() . "_" . basename($image['name']);
    $image_path = $upload_dir . $image_name;

    if (!move_uploaded_file($image['tmp_name'], $image_path)) {
        die("Failed to upload image.");
    }
}


$video_name = "";
if (isset($_FILES['video']) && $_FILES['video']['error'] === UPLOAD_ERR_OK) {
    $video = $_FILES['video'];
    $video_name = time() . "_" . basename($video['name']);
    $video_path = $upload_dir . $video_name;

    if (!move_uploaded_file($video['tmp_name'], $video_path)) {
        die("Failed to upload video.");
    }
}


if (isset($conn)) {
    $stmt = $conn->prepare("INSERT INTO news (title, content, image, video, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $title, $content, $image_name, $video_name);
    $stmt->execute();
    $stmt->close();

    
    header("Location: upload.php");
    exit;
} else {
    die("Database connection not found.");
}
?>

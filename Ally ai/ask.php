<?php
header('Content-Type: application/json');

// Get the POST body
$input = json_decode(file_get_contents('php://input'), true);
$question = isset($input['question']) ? $input['question'] : "";

$api_key = getenv('api_key'); // Use environment variable
$api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-pro:generateContent?key=" . $api_key;

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
    $answer = "Sorry, there was an error contacting Ally AI: " . curl_error($ch);
} else {
    $result = json_decode($response, true);
    $answer = $result['candidates'][0]['content']['parts'][0]['text'] ?? "Sorry, no answer returned.";
}
curl_close($ch);

// Return JSON
echo json_encode(["Answer" => $answer]);
?>

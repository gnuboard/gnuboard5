<?php
include_once('./_common.php');

// Check if the request is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['error' => 'Invalid request method']));
}

// Get the subject from POST data
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';

if (empty($subject)) {
    die(json_encode(['error' => 'Subject is required']));
}

// Gemini AI API endpoint
$api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';

// Prepare the request data
$data = [
    'contents' => [
        [
            'parts' => [
                ['text' => "다음 제목에 대한 블로그 게시글 내용을 작성해주세요: '$subject'. 제목은 제외하고 내용만 작성해 주세요."]
            ]
        ]
    ]
];

// Initialize cURL session
$ch = curl_init($api_url . '?key=' . G5_GEMINI_API_KEY);

// Set cURL options
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);

// Execute cURL request
$response = curl_exec($ch);

// Check for cURL errors
if (curl_errno($ch)) {
    die(json_encode(['error' => 'cURL error: ' . curl_error($ch)]));
}

// Close cURL session
curl_close($ch);

// Decode the JSON response
$result = json_decode($response, true);

// Check if the API request was successful
if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
    $generated_content = $result['candidates'][0]['content']['parts'][0]['text'];
    
    echo json_encode([
        'content' => $generated_content
    ]);
} else {
    echo json_encode(['error' => 'Failed to generate content']);
}
<?php
include_once('./_common.php');
include_once(G5_PLUGIN_PATH.'/chatbot/_config.php');

// POST 데이터 받기
$postData = json_decode(file_get_contents('php://input'), true);

// API 요청 URL
$apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $chatbot_api_key;

// cURL 초기화
$ch = curl_init($apiUrl);

// cURL 옵션 설정
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

// API 요청 실행
$response = curl_exec($ch);

// 오류 확인
if (curl_errno($ch)) {
    echo json_encode(array('error' => 'API 요청 오류: ' . curl_error($ch)));
} else {
    echo $response;
}

// cURL 세션 종료
curl_close($ch);
?>
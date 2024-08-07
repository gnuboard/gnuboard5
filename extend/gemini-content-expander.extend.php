<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// Gemini API를 사용하여 텍스트를 확장하는 함수
function extend_text_with_gemini($text) {
    if (!defined('G5_GEMINI_API_KEY') || empty(G5_GEMINI_API_KEY)) {
        return $text; // API 키가 설정되지 않은 경우 원본 텍스트 반환
    }

    $api_key = G5_GEMINI_API_KEY;
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=' . $api_key;

    $data = [
        'contents' => [
            ['parts' => [['text' => "Please expand on the following text, maintaining the original context and tone: $text"]]],
        ],
        'generationConfig' => [
            'temperature' => 0.7,
            'topK' => 40,
            'topP' => 0.95,
            'maxOutputTokens' => 1024,
        ],
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-Type: application/json',
            'content' => json_encode($data)
        ]
    ];

    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        return $text; // API 호출 실패 시 원본 텍스트 반환
    }

    $response = json_decode($result, true);
    
    if (isset($response['candidates'][0]['content']['parts'][0]['text'])) {
        return $response['candidates'][0]['content']['parts'][0]['text'];
    }

    return $text; // 응답에서 텍스트를 찾을 수 없는 경우 원본 텍스트 반환
}

// 게시글 작성 시 내용 확장
add_event('write_update_before', 'extend_content_before_update', 10);
function extend_content_before_update($board = null, $wr_id = null, $w = '', $qstr = '', $redirect_url = '') {
    global $wr_content;

    // 내용이 짧은 경우에만 확장
    if (isset($wr_content) && mb_strlen($wr_content) < 100) {
        $wr_content = extend_text_with_gemini($wr_content);
    }
}
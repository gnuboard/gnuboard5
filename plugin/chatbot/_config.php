<?php
if (!defined('_GNUBOARD_')) exit;
define('G5_CHATBOT_DIR', G5_PLUGIN_PATH.'/chatbot');
define('G5_CHATBOT_URL', G5_PLUGIN_URL.'/chatbot');

// 메인 config.php에서 정의된 API 키 상수를 사용
$chatbot_api_key = G5_GOOGLE_GEMINI_CHATBOT_API_KEY;

// API 키가 설정되어 있지 않으면 오류 메시지 출력
if (empty($chatbot_api_key)) {
    die('Google Gemini Chatbot API 키가 설정되지 않았습니다. 메인 config.php 파일에서 G5_GOOGLE_GEMINI_CHATBOT_API_KEY를 설정해주세요.');
}
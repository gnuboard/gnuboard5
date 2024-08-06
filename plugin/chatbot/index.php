<?php
include_once('../../common.php');
include_once(G5_PLUGIN_PATH.'/chatbot/_config.php');

if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 헤더 파일 include
include_once(G5_PATH.'/head.sub.php');

// API 키를 config.php에서 가져옵니다.
$api_key = $chatbot_api_key;

// 챗봇 뷰 파일을 include
include(G5_PLUGIN_PATH.'/chatbot/view.php');

// 푸터 파일 include
include_once(G5_PATH.'/tail.sub.php');
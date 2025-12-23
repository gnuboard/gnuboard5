<?php
include_once($_SERVER['DOCUMENT_ROOT'].'/common.php');

header('Content-Type: application/json; charset=utf-8');

// POST로 언어 코드 받기
$lang = isset($_POST['lang']) ? preg_replace('/[^a-z]/', '', $_POST['lang']) : '';

// 유효한 언어인지 확인
$valid_langs = array('ko', 'en', 'zh', 'ja');
if (!in_array($lang, $valid_langs)) {
    echo json_encode(array('error' => true, 'message' => '유효하지 않은 언어 코드입니다.'));
    exit;
}

// 활성화된 언어인지 확인
if (empty($config['cf_lang_type'])) {
    $lang_types = array('ko');
} else {
    $lang_types = explode(',', $config['cf_lang_type']);
    $lang_types = array_map('trim', $lang_types);
    $lang_types = array_filter($lang_types); // 빈 값 제거
}

if (!in_array($lang, $lang_types)) {
    echo json_encode(array('error' => true, 'message' => '활성화되지 않은 언어입니다.'));
    exit;
}

// 세션에 언어 저장
set_session('ss_lang', $lang);

// 세션이 제대로 저장되었는지 확인
$saved_lang = get_session('ss_lang');

// 성공 응답
echo json_encode(array('error' => false, 'message' => '언어가 변경되었습니다.', 'lang' => $lang, 'saved_lang' => $saved_lang));


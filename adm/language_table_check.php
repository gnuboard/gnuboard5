<?php
$sub_menu = "100100";
require_once './_common.php';

header('Content-Type: application/json');

// AJAX용 토큰 체크 (토큰은 삭제하지 않음 - 폼 제출 시 사용해야 함)
$token = get_session('ss_admin_token');

if (!$token || !isset($_REQUEST['token']) || $token != $_REQUEST['token']) {
    die(json_encode(array('error' => true, 'message' => '토큰 오류')));
}

if ($is_admin != 'super') {
    die(json_encode(array('error' => true, 'message' => '권한 오류')));
}

// languages가 배열로 전달되지 않을 수 있으므로 처리
$selected_languages = array();
if (isset($_POST['languages'])) {
    if (is_array($_POST['languages'])) {
        $selected_languages = $_POST['languages'];
    } else {
        $selected_languages = array($_POST['languages']);
    }
}

$previous_languages = array();
if (isset($_POST['previous_languages']) && trim($_POST['previous_languages']) !== '') {
    $previous_languages = explode(',', trim($_POST['previous_languages']));
    $previous_languages = array_map('trim', $previous_languages);
    $previous_languages = array_filter($previous_languages, function($lang) {
        return $lang !== '';
    });
}

// 새로 추가된 언어만 찾기 (한국어 제외)
$new_languages = array_diff($selected_languages, $previous_languages);
$new_languages = array_filter($new_languages, function($lang) {
    return $lang !== 'ko'; // 한국어는 기본 언어이므로 제외
});

if (empty($new_languages)) {
    die(json_encode(array('has_new' => false, 'message' => '새로 추가된 언어가 없습니다.')));
}

$result = array(
    'has_new' => true,
    'new_languages' => array(),
    'tables_exist' => array()
);

$lang_names = array('ko' => '한국어', 'en' => '영어', 'zh' => '중국어', 'ja' => '일본어');

foreach ($new_languages as $lang) {
    if ($lang == 'ko') {
        // 한국어는 기본 언어이므로 테이블 체크 불필요
        continue;
    }
    
    $lang_name = isset($lang_names[$lang]) ? $lang_names[$lang] : $lang;
    
    // 메뉴 테이블만 체크 (단순화)
    $menu_table = $g5['menu_table'] . '_' . $lang;
    $menu_exists = sql_query("SHOW TABLES LIKE '{$menu_table}'", false);
    $menu_table_exists = ($menu_exists && sql_num_rows($menu_exists) > 0);
    
    $result['new_languages'][] = array(
        'code' => $lang,
        'name' => $lang_name,
        'menu_table_exists' => $menu_table_exists
    );
    
    $result['tables_exist'][$lang] = $menu_table_exists;
}

echo json_encode($result);


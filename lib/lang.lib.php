<?php
if (!defined('_GNUBOARD_')) exit;

/**
 * 현재 세션 언어 반환
 * @return string 언어 코드 ('ko', 'en', 'zh', 'ja')
 */
function get_current_lang() {
    global $config;
    
    // 세션에서 언어 가져오기 (항상 최신 값 가져오기, static 캐싱 없음)
    // $_SESSION을 직접 확인하여 항상 최신 값 가져옴
    $lang = isset($_SESSION['ss_lang']) ? $_SESSION['ss_lang'] : '';
    
    // 빈 문자열도 체크
    if ($lang === '' || $lang === false || $lang === null) {
        // 세션에 없으면 config의 첫 번째 언어 또는 'ko'
        $lang_types = !empty($config['cf_lang_type']) ? explode(',', $config['cf_lang_type']) : array('ko');
        $lang = isset($lang_types[0]) ? trim($lang_types[0]) : 'ko';
        
        // 유효한 언어인지 확인
        $valid_langs = array('ko', 'en', 'zh', 'ja');
        if (!in_array($lang, $valid_langs)) {
            $lang = 'ko';
        }
        
        set_session('ss_lang', $lang);
    }
    
    return $lang;
}

/**
 * 언어에 맞는 메뉴 테이블명 반환
 * @param string $lang 언어 코드
 * @return string 메뉴 테이블명
 */
function get_menu_table_by_lang($lang) {
    global $g5;
    
    if ($lang == 'ko') {
        return $g5['menu_table'];
    }
    
    return $g5['menu_table'] . '_' . $lang;
}

/**
 * bo_table을 언어에 맞게 변환
 * @param string $bo_table 게시판 테이블명
 * @param string $lang 언어 코드
 * @return string 변환된 게시판 테이블명
 */
function get_bo_table_by_lang($bo_table, $lang) {
    if (!$bo_table) {
        return $bo_table;
    }
    
    if ($lang == 'ko') {
        // 한국어: 언어 접미사 제거
        return preg_replace('/_(en|zh|ja)$/', '', $bo_table);
    } else {
        // 다른 언어: 접미사 추가 (이미 있으면 그대로)
        if (preg_match('/_(en|zh|ja)$/', $bo_table)) {
            // 이미 접미사가 있으면 현재 언어와 일치하는지 확인
            $current_suffix = '_' . $lang;
            if (substr($bo_table, -strlen($current_suffix)) === $current_suffix) {
                return $bo_table; // 이미 올바른 접미사
            }
            // 다른 언어 접미사가 있으면 제거 후 현재 언어 접미사 추가
            $bo_table = preg_replace('/_(en|zh|ja)$/', '', $bo_table);
        }
        return $bo_table . '_' . $lang;
    }
}


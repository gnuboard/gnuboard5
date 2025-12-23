<?php
$sub_menu = "100100";
require_once './_common.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'w');

if ($is_admin != 'super') {
    die(json_encode(array('error' => true, 'message' => '권한 오류')));
}

check_admin_token();

// 언어 배열 받기
$selected_languages = array();
if (isset($_POST['languages'])) {
    if (is_array($_POST['languages'])) {
        $selected_languages = $_POST['languages'];
    } else {
        $selected_languages = array($_POST['languages']);
    }
}

$previous_languages = isset($_POST['previous_languages']) ? explode(',', trim($_POST['previous_languages'])) : array();

// 새로 추가된 언어만 찾기
$new_languages = array_diff($selected_languages, $previous_languages);

if (empty($new_languages)) {
    die(json_encode(array('success' => true, 'message' => '새로 추가된 언어가 없습니다.')));
}

$lang_names = array('ko' => '한국어', 'en' => '영어', 'zh' => '중국어', 'ja' => '일본어');
$result = array(
    'success' => true,
    'copied_tables' => array()
);

foreach ($new_languages as $lang) {
    if ($lang == 'ko') {
        // 한국어는 기본 언어이므로 테이블 복사 불필요
        continue;
    }
    
    $lang_name = isset($lang_names[$lang]) ? $lang_names[$lang] : $lang;
    
    // 1. 메뉴 테이블 복사 (g5_menu -> g5_menu_{lang})
    $menu_table = $g5['menu_table'] . '_' . $lang;
    $menu_exists = sql_query("SHOW TABLES LIKE '{$menu_table}'", false);
    $menu_table_exists = ($menu_exists && sql_num_rows($menu_exists) > 0);
    
    if (!$menu_table_exists) {
        // 테이블 구조 복사
        $sql = get_table_define($g5['menu_table']);
        $sql = str_replace($g5['menu_table'], $menu_table, $sql);
        sql_query($sql, false);
        
        // 데이터 복사
        $sql = "INSERT INTO `{$menu_table}` SELECT * FROM `{$g5['menu_table']}`";
        sql_query($sql, false);
        
        $result['copied_tables'][] = $menu_table;
    }
    
    // 2. g5_board 테이블에 기존 게시판들을 새 언어로 복사 (bo_table에 _{lang} 붙여서)
    $sql = "SELECT * FROM {$g5['board_table']}";
    $res = sql_query($sql);
    while ($row = sql_fetch_array($res)) {
        $bo_table = $row['bo_table'];
        // 원본 bo_table (언어 접미사 없는 것)만 처리
        if (preg_match('/_(en|zh|ja)$/', $bo_table)) {
            continue; // 이미 언어 접미사가 있으면 스킵
        }
        $bo_table_lang = $bo_table . '_' . $lang;
        
        // 해당 언어로 이미 존재하는지 확인
        $check_sql = "SELECT COUNT(*) as cnt FROM {$g5['board_table']} WHERE bo_table = '{$bo_table_lang}'";
        $check_row = sql_fetch($check_sql);
        
        if ($check_row['cnt'] == 0) {
            // 존재하지 않으면 bo_table에 _{lang} 붙여서 복사
            $row['bo_table'] = $bo_table_lang;
            $row_values = array();
            foreach ($row as $key => $value) {
                if ($key == 'bo_table') {
                    $row_values[] = "`{$key}` = '{$bo_table_lang}'";
                } else {
                    $row_values[] = "`{$key}` = '" . sql_real_escape_string($value) . "'";
                }
            }
            $sql_insert = "INSERT INTO `{$g5['board_table']}` SET " . implode(', ', $row_values);
            sql_query($sql_insert, false);
        }
    }
    
    // 3. 게시판 글 테이블 복사 (g5_write_{bo_table} -> g5_write_{bo_table}_{lang})
    $sql = "SELECT bo_table FROM {$g5['board_table']}";
    $res = sql_query($sql);
    while ($row = sql_fetch_array($res)) {
        $bo_table = $row['bo_table'];
        // 원본 bo_table (언어 접미사 없는 것)만 처리
        if (preg_match('/_(en|zh|ja)$/', $bo_table)) {
            continue; // 이미 언어 접미사가 있으면 스킵
        }
        $write_table = $g5['write_prefix'] . $bo_table;
        $write_table_lang = $g5['write_prefix'] . $bo_table . '_' . $lang;
        
        // 테이블 존재 여부 확인
        $write_exists = sql_query("SHOW TABLES LIKE '{$write_table_lang}'", false);
        $write_table_exists = ($write_exists && sql_num_rows($write_exists) > 0);
        
        if (!$write_table_exists) {
            // 원본 테이블 존재 여부 확인
            $source_exists = sql_query("SHOW TABLES LIKE '{$write_table}'", false);
            if ($source_exists && sql_num_rows($source_exists) > 0) {
                // 테이블 구조 복사
                $sql = get_table_define($write_table);
                $sql = str_replace($write_table, $write_table_lang, $sql);
                sql_query($sql, false);
                
                // 데이터 복사
                $sql = "INSERT INTO `{$write_table_lang}` SELECT * FROM `{$write_table}`";
                sql_query($sql, false);
                
                $result['copied_tables'][] = $write_table_lang;
            }
        }
    }
}

header('Content-Type: application/json');
echo json_encode($result);


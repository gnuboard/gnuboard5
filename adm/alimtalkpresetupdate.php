<?php
$sub_menu = "100320";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

check_demo();

check_admin_token();

$g5['title'] = "알림톡 프리셋 관리";

// 알림톡 프리셋 일괄 업데이트
$kp_active = isset($_REQUEST['kp_active']) ? $_REQUEST['kp_active'] : array();
$kp_template_name = isset($_REQUEST['kp_template_name']) ? $_REQUEST['kp_template_name'] : array();
$kp_alt_send = isset($_REQUEST['kp_alt_send']) ? $_REQUEST['kp_alt_send'] : array();

// DB에서 전체 프리셋 목록 조회
$presets = array();
$sql = "SELECT kp_id, kp_active, kp_template_name, kp_alt_send FROM {$g5['kakao5_preset_table']}";
$result = sql_query($sql);

for($i=0; $row=sql_fetch_array($result); $i++) {
    $kp_id = $row['kp_id'];

    $active = isset($kp_active[$kp_id]) ? (int)$kp_active[$kp_id] : 0;
    $template_name = isset($kp_template_name[$kp_id]) ? sql_escape_string($kp_template_name[$kp_id]) : '';
    $alt_send = isset($kp_alt_send[$kp_id]) ? (int)$kp_alt_send[$kp_id] : 0;

    // 값이 하나라도 다르면 UPDATE
    if ($row['kp_active'] != $active || $row['kp_template_name'] != $template_name || $row['kp_alt_send'] != $alt_send) {
        $sql = "UPDATE {$g5['kakao5_preset_table']} SET kp_active = '{$active}', kp_template_name = '{$template_name}', kp_alt_send = '{$alt_send}' WHERE kp_id = '{$kp_id}'";
        sql_query($sql);
    }
}

goto_url(G5_ADMIN_URL.'/alimtalkpreset.php?' . $qstr);
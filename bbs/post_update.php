<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/naver_syndi.lib.php');
include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

// print_r2($_POST); exit;

foreach ($_POST as $key => $value) {
    $$key = htmlspecialchars($_POST[$key]);
}

// $w = htmlspecialchars($_POST['w']);
// $bo_table = htmlspecialchars($_POST['bo_table']);
// $po_id = htmlspecialchars($_POST['po_id']);
// $sca = htmlspecialchars($_POST['sca']);
// $sfl = htmlspecialchars($_POST['sfl']);
// $stx = htmlspecialchars($_POST['stx']);
// $spt = htmlspecialchars($_POST['spt']);
// $sst = htmlspecialchars($_POST['sst']);

if (!($w == '' || $w == 'u')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

if ($w == '') {
    $sql = " insert into {$g5['post_table']} ";
    $sql .= " set bo_table = '{$bo_table}', ";
    $sql .= " po_subject = '{$po_subject}', ";
    $sql .= " po_content = '{$po_content}', ";
    $sql .= " mb_id = '{$member['mb_id']}', ";
    $sql .= " po_name = '{$member['mb_nick']}', ";
    $sql .= " po_hit = 0, ";
    $sql .= " po_datetime = '".G5_TIME_YMDHIS."', ";
    $sql .= " po_ip = '{$_SERVER['REMOTE_ADDR']}' ";
    // die($sql);
    $result = sql_query($sql);
    // if (!$result) {
    //     alert('글을 입력하는 중 오류가 발생했습니다.');
    // }
    $last_po_id = sql_insert_id();

    $sql = " update {$g5['post_table']} ";
    $sql .= " set po_parent = 0, ";
    $sql .= " po_group = '{$last_po_id}', ";
    $sql .= " po_order = 0, ";
    $sql .= " po_depth = 0 ";
    $sql .= " where po_id = '{$last_po_id}' ";
    sql_query($sql);
}

goto_url("./post.php?bo_table={$bo_table}&".$qstr);
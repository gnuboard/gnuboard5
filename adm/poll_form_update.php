<?php
$sub_menu = "200900";
require_once './_common.php';

$w = $_POST['w'];
if ($w == 'u' || $w == 'd') {
    check_demo();
}

auth_check_menu($auth, $sub_menu, 'w');

check_admin_token();

$check_keys = array(
    'po_subject',
    'po_poll1',
    'po_poll2',
    'po_poll3',
    'po_poll4',
    'po_poll5',
    'po_poll6',
    'po_poll7',
    'po_poll8',
    'po_poll9',
    'po_cnt1',
    'po_cnt2',
    'po_cnt3',
    'po_cnt4',
    'po_cnt5',
    'po_cnt6',
    'po_cnt7',
    'po_cnt8',
    'po_cnt9',
    'po_etc',
    'po_level',
    'po_point',
    'po_id'
);

foreach ($_POST as $key => $value) {
    if (empty($value)) {
        continue;
    }

    if (in_array($key, $check_keys)) {
        $_POST[$key] = strip_tags(clean_xss_attributes($value));
    }
}

$po_id = isset($_POST['po_id']) ? $_POST['po_id'] : '';

if ($w == '') {
    $sql = " insert {$g5['poll_table']}
                    ( po_subject, po_poll1, po_poll2, po_poll3, po_poll4, po_poll5, po_poll6, po_poll7, po_poll8, po_poll9, po_cnt1, po_cnt2, po_cnt3, po_cnt4, po_cnt5, po_cnt6, po_cnt7, po_cnt8, po_cnt9, po_etc, po_level, po_point, po_date, po_use )
             values ( '{$_POST['po_subject']}', '{$_POST['po_poll1']}', '{$_POST['po_poll2']}', '{$_POST['po_poll3']}', '{$_POST['po_poll4']}', '{$_POST['po_poll5']}', '{$_POST['po_poll6']}', '{$_POST['po_poll7']}', '{$_POST['po_poll8']}', '{$_POST['po_poll9']}', '{$_POST['po_cnt1']}', '{$_POST['po_cnt2']}', '{$_POST['po_cnt3']}', '{$_POST['po_cnt4']}', '{$_POST['po_cnt5']}', '{$_POST['po_cnt6']}', '{$_POST['po_cnt7']}', '{$_POST['po_cnt8']}', '{$_POST['po_cnt9']}', '{$_POST['po_etc']}', '{$_POST['po_level']}', '{$_POST['po_point']}', '" . G5_TIME_YMD . "', 1 ) ";
    sql_query($sql);

    $po_id = sql_insert_id();
} elseif ($w == 'u') {
    $sql = " update {$g5['poll_table']}
                set po_subject = '{$_POST['po_subject']}',
                     po_poll1 = '{$_POST['po_poll1']}',
                     po_poll2 = '{$_POST['po_poll2']}',
                     po_poll3 = '{$_POST['po_poll3']}',
                     po_poll4 = '{$_POST['po_poll4']}',
                     po_poll5 = '{$_POST['po_poll5']}',
                     po_poll6 = '{$_POST['po_poll6']}',
                     po_poll7 = '{$_POST['po_poll7']}',
                     po_poll8 = '{$_POST['po_poll8']}',
                     po_poll9 = '{$_POST['po_poll9']}',
                     po_cnt1 = '{$_POST['po_cnt1']}',
                     po_cnt2 = '{$_POST['po_cnt2']}',
                     po_cnt3 = '{$_POST['po_cnt3']}',
                     po_cnt4 = '{$_POST['po_cnt4']}',
                     po_cnt5 = '{$_POST['po_cnt5']}',
                     po_cnt6 = '{$_POST['po_cnt6']}',
                     po_cnt7 = '{$_POST['po_cnt7']}',
                     po_cnt8 = '{$_POST['po_cnt8']}',
                     po_cnt9 = '{$_POST['po_cnt9']}',
                     po_etc = '{$_POST['po_etc']}',
                     po_level = '{$_POST['po_level']}',
                     po_point = '{$_POST['po_point']}',
                     po_use = '{$_POST['po_use']}'
                where po_id = '{$_POST['po_id']}' ";
    sql_query($sql);
} elseif ($w == 'd') {
    $sql = " delete from {$g5['poll_table']} where po_id = '{$_POST['po_id']}' ";
    sql_query($sql);

    $sql = " delete from {$g5['poll_etc_table']} where po_id = '{$_POST['po_id']}' ";
    sql_query($sql);
}

// 가장 큰 투표번호를 기본환경설정에 저장하여
// 투표번호를 넘겨주지 않았을 경우
// 가장 큰 투표번호를 구해야 하는 쿼리를 대체한다
$row = sql_fetch(" select max(po_id) as max_po_id from {$g5['poll_table']} ");
sql_query(" update {$g5['config_table']} set cf_max_po_id = '{$row['max_po_id']}' ");

if ($w == 'd') {
    goto_url('./poll_list.php?' . $qstr);
} else {
    goto_url('./poll_form.php?w=u&po_id=' . $po_id . '&amp;' . $qstr);
}

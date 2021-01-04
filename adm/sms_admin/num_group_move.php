<?php
// 휴대폰 그룹 이동
$sub_menu = "900700";
include_once("./_common.php");

$bk_no = isset($_REQUEST['bk_no']) ? (int) $_REQUEST['bk_no'] : 0;
$bg_no = isset($_REQUEST['bg_no']) ? (int) $_REQUEST['bg_no'] : 0;
$move_no = isset($_REQUEST['move_no']) ? (int) $_REQUEST['move_no'] : 0;

auth_check_menu($auth, $sub_menu, "w");

$res = sql_fetch("select * from {$g5['sms5_book_group_table']} where bg_no='$bg_no'");

if( $res ) {
    sql_query("update {$g5['sms5_book_group_table']} set bg_count = bg_count + {$res['bg_count']}, bg_member = bg_member + {$res['bg_member']}, bg_nomember = bg_nomember + {$res['bg_nomember']}, bg_receipt = bg_receipt + {$res['bg_receipt']}, bg_reject = bg_reject + {$res['bg_reject']} where bg_no='$move_no'");
    sql_query("update {$g5['sms5_book_group_table']} set bg_count = 0, bg_member = 0, bg_nomember = 0, bg_receipt = 0, bg_reject = 0 where bg_no='$bg_no'");
    sql_query("update {$g5['sms5_book_table']} set bg_no='$move_no' where bg_no='$bg_no'");
}

goto_url('./num_group.php');
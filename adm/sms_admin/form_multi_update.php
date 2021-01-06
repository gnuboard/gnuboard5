<?php
$sub_menu = "900600";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$post_fo_no = (isset($_POST['fo_no']) && is_array($_POST['fo_no'])) ? $_POST['fo_no'] : array();
$atype = isset($_POST['atype']) ? clean_xss_tags($_POST['atype'], 1, 1) : '';
$fg_no = isset($_POST['fg_no']) ? (int) $_POST['fg_no'] : 0;

if($atype == "del"){
    $count = count($post_fo_no);
    if(!$count)
        alert('선택삭제 하실 항목을 하나이상 선택해 주세요.');

    for ($i=0; $i<$count; $i++)
    {
        // 실제 번호를 넘김
        $fo_no = (int) $post_fo_no[$i];
        if (!trim($fo_no)) continue;

        $res = sql_fetch("select * from {$g5['sms5_form_table']} where fo_no='$fo_no'");
        if (!$res) continue;

        sql_query("delete from {$g5['sms5_form_table']} where fo_no='$fo_no'");
        sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count - 1 where fg_no='{$res['fg_no']}'");
    }
}
goto_url('./form_list.php?fg_no='.$fg_no);
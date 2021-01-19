<?php
$sub_menu = "900600";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, "w");

$fo_name = isset($_REQUEST['fo_name']) ? strip_tags(clean_xss_attributes($_REQUEST['fo_name'])) : '';
$fo_content = isset($_REQUEST['fo_content']) ? strip_tags(clean_xss_attributes($_REQUEST['fo_content'])) : '';
$fo_receipt = isset($_REQUEST['fo_receipt']) ? clean_xss_tags($_REQUEST['fo_receipt'], 1, 1) : '';
$get_fg_no = '';

$g5['title'] = "이모티콘 업데이트";

if ($w == 'u') // 업데이트
{
    if (!$fg_no) $fg_no = 0;

    if (!$fo_receipt) $fo_receipt = 0; else $fo_receipt = 1;

    if (!strlen(trim($fo_name)))
        alert('이름을 입력해주세요');

    if (!strlen(trim($fo_content)))
        alert('이모티콘을 입력해주세요');
/*
    $res = sql_fetch("select * from {$g5['sms5_form_table']} where fo_no<>'$fo_no' and fo_content='$fo_content'");
    if ($res)
        alert('같은 이모티콘이 존재합니다.');
*/
    $res = sql_fetch("select * from {$g5['sms5_form_table']} where fo_no='$fo_no'");
    if (!$res)
        alert('존재하지 않는 데이터 입니다.');

    if ($fg_no != $res['fg_no']) {
        if ($res['fg_no'])
            sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count - 1 where fg_no='{$res['fg_no']}'");

        sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count + 1 where fg_no='$fg_no'");
    }

    $group = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no = '$fg_no'");

    sql_query("update {$g5['sms5_form_table']} set fg_no='$fg_no', fg_member='{$group['fg_member']}', fo_name='$fo_name', fo_content='$fo_content', fo_datetime='".G5_TIME_YMDHIS."' where fo_no='$fo_no'");
}
else if ($w == 'd') // 삭제
{
    if (!is_numeric($fo_no))
        alert('고유번호가 없습니다.');

    $res = sql_fetch("select * from {$g5['sms5_form_table']} where fo_no='$fo_no'");
    if (!$res)
        alert('존재하지 않는 데이터 입니다.');

    sql_query("delete from {$g5['sms5_form_table']} where fo_no='$fo_no'");
    sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count - 1 where fg_no = '{$res['fg_no']}'");

    $get_fg_no = $fg_no;
}
else // 등록
{
    if (!$fg_no) $fg_no = 0;

    if (!strlen(trim($fo_name)))
        alert('이름을 입력해주세요');

    if (!strlen(trim($fo_content)))
        alert('이모티콘을 입력해주세요');

    $res = sql_fetch("select * from {$g5['sms5_form_table']} where fo_content='$fo_content'");
    if ($res)
        alert('같은 이모티콘이 존재합니다.');

    $group = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no = '$fg_no'");

    sql_query("insert into {$g5['sms5_form_table']} set fg_no='$fg_no', fg_member='{$group['fg_member']}', fo_name='$fo_name', fo_content='$fo_content', fo_datetime='".G5_TIME_YMDHIS."'");
    sql_query("update {$g5['sms5_form_group_table']} set fg_count = fg_count + 1 where fg_no = '$fg_no'");

    $get_fg_no = $fg_no;
}

$go_url = './form_list.php?page='.$page.'&amp;fg_no='.$get_fg_no;
goto_url($go_url);
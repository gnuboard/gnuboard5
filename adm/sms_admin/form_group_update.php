<?php
$sub_menu = "900500";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, "w");

$post_cnk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? $_POST['chk'] : array();

if ($w == 'u') // 업데이트
{
    for ($i=0; $i<count($post_cnk); $i++)
    {
        // 실제 번호를 넘김
        $k = $post_cnk[$i];
        $fg_no = isset($_POST['fg_no'][$k]) ? (int) $_POST['fg_no'][$k] : 0;
        $fg_name = isset($_POST['fg_name'][$k]) ? addslashes(strip_tags(clean_xss_attributes($_POST['fg_name'][$k]))) : '';
        $fg_member = isset($_POST['fg_member'][$k]) ? addslashes(strip_tags($_POST['fg_member'][$k])) : '';

        if (!is_numeric($fg_no))
            alert('그룹 고유번호가 없습니다.');

        $res = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no='$fg_no'");
        if (!$res)
            alert('존재하지 않는 그룹입니다.');

        if (!strlen(trim($fg_name)))
            alert('그룹명을 입력해주세요');

        $res = sql_fetch("select fg_name from {$g5['sms5_form_group_table']} where fg_no<>'$fg_no' and fg_name='$fg_name'");
        if ($res)
            alert('같은 그룹명이 존재합니다.');

        sql_query("update {$g5['sms5_form_group_table']} set fg_name='$fg_name', fg_member='$fg_member' where fg_no='$fg_no'");
        sql_query("update {$g5['sms5_form_table']} set fg_member = '$fg_member' where fg_no = '$fg_no'");
    }
}
else if ($w == 'de') // 그룹삭제
{
    for ($i=0; $i<count($post_cnk); $i++)
    {
        // 실제 번호를 넘김
        $k = $post_cnk[$i];
        $fg_no = isset($_POST['fg_no'][$k]) ? (int) $_POST['fg_no'][$k] : 0;

        if (!is_numeric($fg_no))
            alert('그룹 고유번호가 없습니다.');

        $res = sql_fetch("select * from {$g5['sms5_form_group_table']} where fg_no='$fg_no'");
        if (!$res)
            alert('존재하지 않는 그룹입니다.');

        sql_query("delete from {$g5['sms5_form_group_table']} where fg_no='$fg_no'");
        sql_query("update {$g5['sms5_form_table']} set fg_no = 0, fg_member = 0 where fg_no='$fg_no'");
    }
}
else if ($w == 'em') 
{
    for ($i=0; $i<count($post_cnk); $i++)
    {
        // 실제 번호를 넘김
        $k = $post_cnk[$i];
        $fg_no = isset($_POST['fg_no'][$k]) ? (int) $_POST['fg_no'][$k] : 0;

        if ($fg_no == 'no') $fg_no = 0;

        if ($fg_no)
            sql_query("update {$g5['sms5_form_group_table']} set fg_count = 0 where fg_no = '$fg_no'");

        sql_query("delete from {$g5['sms5_form_table']} where fg_no = '$fg_no'");
    }
}
else if ($w == 'no') 
{
    if ($fg_no == 'no') $fg_no = 0;

    $fg_no = (int) $fg_no;

    if ($fg_no)
        sql_query("update {$g5['sms5_form_group_table']} set fg_count = 0 where fg_no = '$fg_no'");

    sql_query("delete from {$g5['sms5_form_table']} where fg_no = '$fg_no'");
}
else // 등록
{
    $fg_name = isset($_POST['fg_name']) ? addslashes(strip_tags(clean_xss_attributes($_POST['fg_name']))) : '';

    if (!strlen(trim($fg_name)))
        alert('그룹명을 입력해주세요');

    $res = sql_fetch("select fg_name from {$g5['sms5_form_group_table']} where fg_name = '$fg_name'");
    if ($res)
        alert('같은 그룹명이 존재합니다.');

    sql_query("insert into {$g5['sms5_form_group_table']} set fg_name = '$fg_name'");
}

goto_url('./form_group.php');
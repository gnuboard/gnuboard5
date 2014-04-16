<?php
$sub_menu = "900700";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

if ($w == 'u') // 업데이트
{
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $bg_no = $_POST['bg_no'][$k];
        $bg_name = $_POST['bg_name'][$k];

        if (!is_numeric($bg_no))
            alert('그룹 고유번호가 없습니다.');

        $res = sql_fetch("select * from {$g5['sms5_book_group_table']} where bg_no='$bg_no'");
        if (!$res)
            alert('존재하지 않는 그룹입니다.');

        if (!strlen(trim($bg_name)))
            alert('그룹명을 입력해주세요');

        $res = sql_fetch("select bg_name from {$g5['sms5_book_group_table']} where bg_no<>'$bg_no' and bg_name='$bg_name'");
        if ($res)
            alert('같은 그룹명이 존재합니다.');

        sql_query("update {$g5['sms5_book_group_table']} set bg_name='".addslashes($bg_name)."' where bg_no='$bg_no'");
    }
}
else if ($w == 'de') // 그룹삭제
{
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $bg_no = $_POST['bg_no'][$k];

        if (!is_numeric($bg_no))
            alert('그룹 고유번호가 없습니다.');

        $res = sql_fetch("select * from {$g5['sms5_book_group_table']} where bg_no='$bg_no'");
        if (!$res)
            alert('존재하지 않는 그룹입니다.');

        sql_query("delete from {$g5['sms5_book_group_table']} where bg_no='$bg_no'");
        sql_query("update {$g5['sms5_book_table']} set bg_no=1 where bg_no='$bg_no'");
    }
}
else if ($w == 'em') // 비우기
{
    for ($i=0; $i<count($_POST['chk']); $i++)
    {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];
        $bg_no = $_POST['bg_no'][$k];

        sql_query("update {$g5['sms5_book_group_table']} set bg_count = 0, bg_member = 0, bg_nomember = 0, bg_receipt = 0, bg_reject = 0 where bg_no='$bg_no'");
        sql_query("delete from {$g5['sms5_book_table']} where bg_no='$bg_no'");
    }
}
else // 등록
{
    if (!strlen(trim($bg_name)))
        alert('그룹명을 입력해주세요');

    $res = sql_fetch("select bg_name from {$g5['sms5_book_group_table']} where bg_name='$bg_name'");
    if ($res)
        alert('같은 그룹명이 존재합니다.');

    sql_query("insert into {$g5['sms5_book_group_table']} set bg_name='".addslashes($bg_name)."'");
}

goto_url('./num_group.php');
?>
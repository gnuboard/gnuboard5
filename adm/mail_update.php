<?php
$sub_menu = "200300";
include_once('./_common.php');

if ($w == 'u' || $w == 'd')
    check_demo();

auth_check($auth[$sub_menu], 'w');

check_token();

if ($w == '')
{
    $sql = " insert {$g5['mail_table']}
                set ma_id = '{$_POST['ma_id']}',
                     ma_subject = '{$_POST['ma_subject']}',
                     ma_content = '{$_POST['ma_content']}',
                     ma_time = '".G5_TIME_YMDHIS."',
                     ma_ip = '{$_SERVER['REMOTE_ADDR']}' ";
    sql_query($sql);
}
else if ($w == 'u')
{
    $sql = " update {$g5['mail_table']}
                set ma_subject = '{$_POST['ma_subject']}',
                     ma_content = '{$_POST['ma_content']}',
                     ma_time = '".G5_TIME_YMDHIS."',
                     ma_ip = '{$_SERVER['REMOTE_ADDR']}'
                where ma_id = '{$_POST['ma_id']}' ";
    sql_query($sql);
}
else if ($w == 'd')
{
	$sql = " delete from {$g5['mail_table']} where ma_id = '{$_POST['ma_id']}' ";
    sql_query($sql);
}

goto_url('./mail_list.php');
?>

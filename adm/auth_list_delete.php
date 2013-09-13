<?php
$sub_menu = "100200";
include_once('./_common.php');

check_demo();

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

check_token();

$count = count($_POST['chk']);

if (!$count)
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");

for ($i=0; $i<$count; $i++)
{
    // 실제 번호를 넘김
    $k = $chk[$i];

    $sql = " delete from {$g5['auth_table']} where mb_id = '{$_POST['mb_id'][$k]}' and au_menu = '{$_POST['au_menu'][$k]}' ";
    sql_query($sql);
}

goto_url('./auth_list.php?'.$qstr);
?>

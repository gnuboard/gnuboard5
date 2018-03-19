<?php
$sub_menu = "100200";
include_once('./_common.php');

check_demo();

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

check_admin_token();

$count = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

if (!$count)
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");

if ( (isset($_POST['mb_id']) && ! is_array($_POST['mb_id'])) || (isset($_POST['au_menu']) && ! is_array($_POST['au_menu'])) ){
    alert("잘못된 요청입니다.");
}

for ($i=0; $i<$count; $i++)
{
    // 실제 번호를 넘김
    $k = $chk[$i];
    
    $mb_id = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['mb_id'][$k]);
    $au_menu = preg_replace('/[^a-zA-Z0-9_]/', '', $_POST['au_menu'][$k]);

    $sql = " delete from {$g5['auth_table']} where mb_id = '".$mb_id."' and au_menu = '".$au_menu."' ";
    sql_query($sql);
}

goto_url('./auth_list.php?'.$qstr);
?>

<?php
$sub_menu = '200300';
require_once './_common.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'd');

check_admin_token();

$post_count_chk = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

if (!$post_count_chk) {
    alert('삭제할 메일목록을 1개이상 선택해 주세요.');
}

for ($i = 0; $i < $post_count_chk; $i++) {
    $ma_id = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

    $sql = " delete from {$g5['mail_table']} where ma_id = '$ma_id' ";
    sql_query($sql);
}

goto_url('./mail_list.php');

<?php
$sub_menu = "200900";
require_once './_common.php';

check_demo();

auth_check_menu($auth, $sub_menu, 'd');

check_admin_token();

$count = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;

if (!$count) {
    alert('삭제할 투표목록을 1개이상 선택해 주세요.');
}

for ($i = 0; $i < $count; $i++) {
    $po_id = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

    $sql = " delete from {$g5['poll_table']} where po_id = '$po_id' ";
    sql_query($sql);

    $sql = " delete from {$g5['poll_etc_table']} where po_id = '$po_id' ";
    sql_query($sql);
}

goto_url('./poll_list.php?' . $qstr);

<?php
$sub_menu = '400650';
include_once('./_common.php');

check_demo();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] == "선택수정") {

    auth_check($auth[$sub_menu], 'w');

    for ($i=0; $i<count($_POST['chk']); $i++) {

        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = "update {$g4['shop_item_use_table']}
                   set is_confirm = '{$_POST['is_confirm'][$k]}'
                 where is_id      = '{$_POST['is_id'][$k]}' ";
        sql_query($sql);
    }
} else if ($_POST['act_button'] == "선택삭제") {

    auth_check($auth[$sub_menu], 'd');

    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = "delete from {$g4['shop_item_use_table']} where is_id = '{$_POST['is_id'][$k]}' ";
        sql_query($sql);
    }
}

goto_url("./itemuselist.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");
?>

<?php
$sub_menu = '400300';
include_once('./_common.php');

check_demo();

check_admin_token();

if (!count($_POST['chk'])) {
    alert($_POST['act_button']." 하실 항목을 하나 이상 체크하세요.");
}

if ($_POST['act_button'] == "선택수정") {

    auth_check($auth[$sub_menu], 'w');

    for ($i=0; $i<count($_POST['chk']); $i++) {

        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        $sql = "update {$g5['g5_shop_item_table']}
                   set ca_id          = '{$_POST['ca_id'][$k]}',
                       ca_id2         = '{$_POST['ca_id2'][$k]}',
                       ca_id3         = '{$_POST['ca_id3'][$k]}',
                       it_name        = '{$_POST['it_name'][$k]}',
                       it_cust_price  = '{$_POST['it_cust_price'][$k]}',
                       it_price       = '{$_POST['it_price'][$k]}',
                       it_stock_qty   = '{$_POST['it_stock_qty'][$k]}',
                       it_skin        = '{$_POST['it_skin'][$k]}',
                       it_mobile_skin = '{$_POST['it_mobile_skin'][$k]}',
                       it_use         = '{$_POST['it_use'][$k]}',
                       it_soldout     = '{$_POST['it_soldout'][$k]}',
                       it_order       = '{$_POST['it_order'][$k]}',
                       it_update_time = '".G5_TIME_YMDHIS."'
                 where it_id   = '{$_POST['it_id'][$k]}' ";
        sql_query($sql);
    }
} else if ($_POST['act_button'] == "선택삭제") {

    if ($is_admin != 'super')
        alert('상품 삭제는 최고관리자만 가능합니다.');

    auth_check($auth[$sub_menu], 'd');

    // _ITEM_DELETE_ 상수를 선언해야 itemdelete.inc.php 가 정상 작동함
    define('_ITEM_DELETE_', true);

    for ($i=0; $i<count($_POST['chk']); $i++) {
        // 실제 번호를 넘김
        $k = $_POST['chk'][$i];

        // include 전에 $it_id 값을 반드시 넘겨야 함
        $it_id = $_POST['it_id'][$k];
        include ('./itemdelete.inc.php');
    }
}

goto_url("./itemlist.php?sca=$sca&amp;sst=$sst&amp;sod=$sod&amp;sfl=$sfl&amp;stx=$stx&amp;page=$page");
?>

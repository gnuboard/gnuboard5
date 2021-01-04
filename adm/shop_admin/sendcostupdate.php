<?php
$sub_menu = '400750';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$w = isset($_POST['w']) ? $_POST['w'] : '';

if($w == 'd') {
    $count = (isset($_POST['chk']) && is_array($_POST['chk'])) ? count($_POST['chk']) : 0;
    if(!$count)
        alert('삭제하실 항목을 하나이상 선택해 주십시오.');

    for($i=0; $i<$count; $i++) {
        $k = isset($_POST['chk'][$i]) ? (int) $_POST['chk'][$i] : 0;

        $sc_id = isset($_POST['sc_id'][$i]) ? (int) $_POST['sc_id'][$k] : 0;
        sql_query(" delete from {$g5['g5_shop_sendcost_table']} where sc_id = '$sc_id' ");
    }
} else {
    $sc_name = isset($_POST['sc_name']) ? trim(strip_tags(clean_xss_attributes($_POST['sc_name']))) : '';
    $sc_zip1 = isset($_POST['sc_zip1']) ? preg_replace('/[^0-9]/', '', $_POST['sc_zip1']) : '';
    $sc_zip2 = isset($_POST['sc_zip2']) ? preg_replace('/[^0-9]/', '', $_POST['sc_zip2']) : '';
    $sc_price = isset($_POST['sc_price']) ? preg_replace('/[^0-9]/', '', $_POST['sc_price']) : '';

    if(!$sc_name)
        alert('지역명을 입력해 주십시오.');
    if(!$sc_zip1)
        alert('우편번호 시작을 입력해 주십시오.');
    if(!$sc_zip2)
        alert('우편번호 끝을 입력해 주십시오.');
    if(!$sc_price)
        alert('추가배송비를 입력해 주십시오.');

    $sql = " insert into {$g5['g5_shop_sendcost_table']}
                  ( sc_name, sc_zip1, sc_zip2, sc_price )
                values
                  ( '$sc_name', '$sc_zip1', '$sc_zip2', '$sc_price' ) ";
    sql_query($sql);
}

goto_url('./sendcostlist.php?page='.$page);
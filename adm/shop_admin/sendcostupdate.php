<?php
$sub_menu = '400750';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

check_admin_token();

$w = $_POST['w'];

if($w == 'd') {
    $count = count($_POST['chk']);
    if(!$count)
        alert('삭제하실 항목을 하나이상 선택해 주십시오.');

    for($i=0; $i<$count; $i++) {
        $k = $_POST['chk'][$i];

        $sc_id = $_POST['sc_id'][$k];
        sql_query(" delete from {$g5['g5_shop_sendcost_table']} where sc_id = '$sc_id' ");
    }
} else {
    $sc_name = trim($_POST['sc_name']);
    $sc_zip1 = preg_replace('/[^0-9]/', '', $_POST['sc_zip1']);
    $sc_zip2 = preg_replace('/[^0-9]/', '', $_POST['sc_zip2']);
    $sc_price = preg_replace('/[^0-9]/', '', $_POST['sc_price']);

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
?>
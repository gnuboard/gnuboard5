<?php
$sub_menu = "400750";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$sc_name = trim($sc_name);
$sc_zip1 = preg_replace("/[^0-9]/", "", $sc_zip1);
$sc_zip2 = preg_replace("/[^0-9]/", "", $sc_zip2);
$sc_amount = preg_replace("/[^-0-9]/", "", $sc_amount);

if(!$sc_name) {
    alert("지역명을 입력해 주세요.");
}
if(!$sc_zip1 || !$sc_zip2) {
    alert("우편번호 범위를 입력해 주세요.");
}
if($sc_zip1 >= $sc_zip2) {
    alert("우편번호 범위가 올바른지 확인해 주세요.");
}
if(!$sc_amount) {
    alert("추가배송료를 입력해 주세요.");
}

$sql = " insert into {$g4['yc4_sendcost_table']}
            set sc_name     = '$sc_name',
                sc_zip1     = '$sc_zip1',
                sc_zip2     = '$sc_zip2',
                sc_amount   = '$sc_amount' ";
sql_query($sql);

goto_url("./sendcostlist.php");
?>
<?php
$sub_menu = '400740';
include_once('./_common.php');

if ($w == "u" || $w == "d")
    check_demo();

if ($W == 'd')
    auth_check($auth[$sub_menu], "d");
else
    auth_check($auth[$sub_menu], "w");

// 기본 배송업체가 선택 되었다면 추가나 수정시 기본 배송업체를 모두 삭제한다.
if ($dl_default && ($w == "" || $w == "u")) {
    sql_query(" update {$g5['g5_shop_delivery_table']} set dl_default = 0 ");
}

$sql_common .= "set dl_company = '$dl_company',
                    dl_url = '$dl_url',
                    dl_tel = '$dl_tel',
                    dl_order = '$dl_order',
                    dl_default = '$dl_default' ";

if ($w == "") {
    $sql = " alter table {$g5['g5_shop_delivery_table']} auto_increment=1 ";
    sql_query($sql);

    $sql = " insert {$g5['g5_shop_delivery_table']} $sql_common ";
    sql_query($sql);

    $dl_id = mysql_insert_id();
} else if ($w == "u") {
    $sql = " update {$g5['g5_shop_delivery_table']} $sql_common where dl_id = '$dl_id' ";
    sql_query($sql);
} else if ($w == "d") {
	// Master 삭제
	$sql = " delete from {$g5['g5_shop_delivery_table']} where dl_id = '$dl_id' ";
    sql_query($sql);
}

if ($w == 'd') {
    goto_url("./deliverycodelist.php");
} else {
    goto_url("./deliverycodeform.php?w=u&amp;dl_id=$dl_id");
}
?>

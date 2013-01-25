<?
$sub_menu = "400400";
include_once("./_common.php");

$sql = " update $g4[yc4_order_table]
            set od_shop_memo = '$od_shop_memo',
                od_name = '$od_name',
                od_tel = '$od_tel',
                od_hp = '$od_hp',
                od_zip1 = '$od_zip1',
                od_zip2 = '$od_zip2',
                od_addr1 = '$od_addr1',
                od_addr2 = '$od_addr2',
                od_email = '$od_email',
                od_b_name = '$od_b_name',
                od_b_tel = '$od_b_tel',
                od_b_hp = '$od_b_hp',
                od_b_zip1 = '$od_b_zip1',
                od_b_zip2 = '$od_b_zip2',
                od_b_addr1 = '$od_b_addr1',
                od_b_addr2 = '$od_b_addr2' ";
if ($default[de_hope_date_use])
    $sql .= " , od_hope_date = '$od_hope_date' ";
$sql .= " where od_id = '$od_id' ";
sql_query($sql);

$qstr = "sort1=$sort1&sort2=$sort2&sel_field=$sel_field&search=$search&page=$page";

goto_url("./orderform.php?od_id=$od_id&$qstr");
?>

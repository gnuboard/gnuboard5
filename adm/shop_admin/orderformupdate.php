<?php
$sub_menu = '400400';
include_once('./_common.php');

check_admin_token();

if($_POST['mod_type'] == 'info') {
    $od_zip1   = substr($_POST['od_zip'], 0, 3);
    $od_zip2   = substr($_POST['od_zip'], 3);
    $od_b_zip1 = substr($_POST['od_b_zip'], 0, 3);
    $od_b_zip2 = substr($_POST['od_b_zip'], 3);

    $sql = " update {$g5['g5_shop_order_table']}
                set od_name = '$od_name',
                    od_tel = '$od_tel',
                    od_hp = '$od_hp',
                    od_zip1 = '$od_zip1',
                    od_zip2 = '$od_zip2',
                    od_addr1 = '$od_addr1',
                    od_addr2 = '$od_addr2',
                    od_addr3 = '$od_addr3',
                    od_addr_jibeon = '$od_addr_jibeon',
                    od_email = '$od_email',
                    od_b_name = '$od_b_name',
                    od_b_tel = '$od_b_tel',
                    od_b_hp = '$od_b_hp',
                    od_b_zip1 = '$od_b_zip1',
                    od_b_zip2 = '$od_b_zip2',
                    od_b_addr1 = '$od_b_addr1',
                    od_b_addr2 = '$od_b_addr2',
                    od_b_addr3 = '$od_b_addr3',
                    od_b_addr_jibeon = '$od_b_addr_jibeon' ";
    if ($default['de_hope_date_use'])
        $sql .= " , od_hope_date = '$od_hope_date' ";
} else {
    $sql = "update {$g5['g5_shop_order_table']}
                set od_shop_memo = '$od_shop_memo' ";
}
$sql .= " where od_id = '$od_id' ";
sql_query($sql);

$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

goto_url("./orderform.php?od_id=$od_id&amp;$qstr");
?>

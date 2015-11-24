<?php
$sub_menu = '400500';
include_once('./_common.php');

check_demo();

auth_check($auth[$sub_menu], "w");

check_admin_token();

// 재고 일괄수정
for ($i=0; $i<count($_POST['it_id']); $i++)
{
    $sql = "update {$g5['g5_shop_item_option_table']}
               set io_stock_qty    = '{$_POST['io_stock_qty'][$i]}',
                   io_noti_qty     = '{$_POST['io_noti_qty'][$i]}',
                   io_use = '{$_POST['io_use'][$i]}'
             where it_id = '{$_POST['it_id'][$i]}'
               and io_id = '{$_POST['io_id'][$i]}'
               and io_type = '{$_POST['io_type'][$i]}' ";
    sql_query($sql);
}

goto_url("./optionstocklist.php?sort1=$sort1&amp;sort2=$sort2&amp;sel_ca_id=$sel_ca_id&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page");
?>

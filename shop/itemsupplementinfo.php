<?php
include_once('./_common.php');

$sql = " select sp_amount, sp_qty from `{$g4['shop_supplement_table']}` where it_id = '$it_id' and sp_id = '$sp_id' and sp_use = '1' ";
$row = sql_fetch($sql);

if(!$row['sp_amount']) {
    $row['sp_amount'] = 0;
}

$sp_qty = get_option_stock_qty($it_id, $sp_id, 2);


// json 포맷으로 데이터 전달
echo '{ "amount": "' . $row['sp_amount'] . '", "qty": "' . $sp_qty . '" }';
?>
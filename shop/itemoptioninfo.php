<?php
include_once('./_common.php');

$sql = " select opt_amount, opt_qty from `{$g4['yc4_option_table']}` where it_id = '$it_id' and opt_id = '$opt_id' and opt_use = '1' ";
$row = sql_fetch($sql);

if(!$row['opt_amount']) {
    $row['opt_amount'] = 0;
}

$opt_qty = get_option_stock_qty($it_id, $opt_id, 1);


// json 포맷으로 데이터 전달
echo '{ "amount": "' . $row['opt_amount'] . '", "qty": "' . $opt_qty . '" }';
?>
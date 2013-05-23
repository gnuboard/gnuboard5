<?php
include_once('./_common.php');

$sql = " select * from {$g4['shop_item_option_table']}
                where io_type = '0'
                  and it_id = '$it_id'
                  and io_use = '1'
                  and io_id like '$opt_id%'
                order by io_no asc ";
$result = sql_query($sql);

$str = '<option value="">선택</option>';
$opt = array();

for($i=0; $row=sql_fetch_array($result); $i++) {
    $val = explode(chr(30), $row['io_id']);
    $key = $idx + 1;

    if(!$val[$key])
        continue;

    if(in_array($val[$key], $opt))
        continue;

    $opt[] = $val[$key];

    if($key + 1 < $sel_count) {
        $str .= PHP_EOL.'<option value="'.$val[$key].'">'.$val[$key].'</option>';
    } else {
        if($row['io_price'] >= 0)
            $price = '&nbsp;&nbsp;+ '.number_format($row['io_price']).'원';
        else
            $price = '&nbsp;&nbsp; '.number_format($row['io_price']).'원';

        $str .= PHP_EOL.'<option value="'.$val[$key].','.$row['io_price'].','.$row['io_stock_qty'].'">'.$val[$key].$price.'</otpion>';
    }
}

echo $str;
?>
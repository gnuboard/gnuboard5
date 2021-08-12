<?php
include_once('./_common.php');

$code = isset($_POST['zipcode']) ? preg_replace('#[^0-9]#', '', $_POST['zipcode']) : '';

if(!$code)
    die('0');

$sql = " select sc_id, sc_price
            from {$g5['g5_shop_sendcost_table']}
            where sc_zip1 <= '$code'
              and sc_zip2 >= '$code' ";
$row = sql_fetch($sql);

if(! (isset($row['sc_id']) && $row['sc_id']))
    die('0');

die($row['sc_price']);
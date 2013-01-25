<?php
include_once('./_common.php');

$zipcode = preg_replace("/[^0-9]/", "", $zip);

if(!$zipcode) exit;

$sql = " select sc_amount from {$g4['yc4_sendcost_table']} where sc_zip1 <= '$zipcode' and sc_zip2 >= '$zipcode' ";
$row = sql_fetch($sql);

if($row['sc_amount']) {
    echo $row['sc_amount'];
}
?>
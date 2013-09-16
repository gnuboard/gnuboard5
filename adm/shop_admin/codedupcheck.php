<?php
include_once('./_common.php');

$name = '';

if ($it_id)
{
    $sql = " select it_name from {$g5['g5_shop_item_table']} where it_id = '$it_id' ";
    $row = sql_fetch($sql);
    $code = $it_id;
    $name = $row['it_name'];
}
else if ($ca_id)
{
    $sql = " select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$ca_id' ";
    $row = sql_fetch($sql);
    $code = $ca_id;
    $name = $row['ca_name'];
}

echo '{ "code": "' . $code . '", "name": "' . $name . '" }';
?>
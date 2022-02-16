<?php
include_once('./_common.php');

$it_id = isset($it_id) ? clean_xss_tags(trim($it_id)) : '';
$ca_id = isset($ca_id) ? clean_xss_tags(trim($ca_id)) : '';

$code = '';
$name = '';

if ($it_id)
{
    $row = get_shop_item($it_id, true);
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
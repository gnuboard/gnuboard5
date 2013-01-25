<?
include_once("./_common.php");

$name = "";
if ($it_id)
{
    $sql = " select it_name from {$g4['yc4_item_table']} where it_id = '$it_id' ";
    $row = sql_fetch($sql);
    $code = $it_id;
    $name = $row['it_name'];
}
else if ($ca_id)
{
    $sql = " select ca_name from {$g4['yc4_category_table']} where ca_id = '$ca_id' ";
    $row = sql_fetch($sql);
    $code = $ca_id;
    $name = $row['ca_name'];
}

echo $name;

// json 포맷으로 데이터 전달
//echo '{ "name": "' . $name . '", "code": "' . $code . '" }';
?>

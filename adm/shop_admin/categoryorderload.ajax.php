<?
include_once("./_common.php");

$ca_id = $_REQUEST['ca_id'];
$length = strlen($ca_id) + 2;

$sql = " SELECT ca_id, ca_name from {$g4['yc4_category_table']} where ca_id like '$ca_id%' and length(ca_id) = $length order by ca_order, ca_id ";
$result = sql_query($sql);
$list = array();
while ($row=sql_fetch_array($result)) {
    $id   = $row['ca_id'];
    $name = $row['ca_name'];

    $cnt  = 0;
    if ($length < 10) {
        $sql2 = " select count(*) as cnt from {$g4['yc4_category_table']} where ca_id like '{$row['ca_id']}%' and length(ca_id) = $length + 2 ";
        $row2 = sql_fetch($sql2);
        $cnt = $row2['cnt'];
    }

    $list[] = "{\"ca_id\":\"$id\", \"ca_name\":\"$name\", \"low_category_count\":$cnt}";
    //break;
}
echo "{\"list\":[ ".implode(",", $list)." ]}";
?>
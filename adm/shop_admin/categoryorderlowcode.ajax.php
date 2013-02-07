<?
die('--'); // 같은걸 두개나 만들었네 ㅡㅡ;;;
include_once("./_common.php");

$ca_id = trim($_REQUEST['ca_id']);

$len = strlen($ca_id) + 2;
if ($len > 10)
    die('{"error":"마지막 레벨은 하위레벨이 없습니다."}');

$sql = " select ca_id, ca_name  from {$g4['shop_category_table']} where ca_id like '$ca_id%' and length(ca_id) = $len ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result)) {
    $id   = $row['ca_id'];
    $name = $row['ca_name'];
    $list[] = "{\"ca_id\":\"$id\", \"ca_name\":\"$name\"}";
}

if ($list)
    echo "{\"list\":[ ".implode(",", $list)." ]}";
?>
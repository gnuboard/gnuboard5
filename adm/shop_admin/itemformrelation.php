<?
$sub_menu = "400300";
include_once("./_common.php");

$sql = " select ca_id, it_id, it_name, it_amount
           from {$g4['yc4_item_table']}
          where ca_id like '$ca_id%'
            and it_id <> '$it_id'
          order by ca_id, it_name ";
$result = sql_query($sql);
$num = @mysql_num_rows($result);

$options = "";
for($i=0;$row=sql_fetch_array($result);$i++) {
    // 관련상품으로 등록된 상품은 제외
    $sql2 = " select count(*) as cnt from {$g4['yc4_item_relation_table']} where it_id = '$it_id' and it_id2 = '{$row['it_id']}' ";
    $row2 = sql_fetch($sql2);
    if ($row2['cnt'])
        continue;

    $sql2 = " select ca_name from {$g4['yc4_category_table']} where ca_id = '{$row['ca_id']}' ";
    $row2 = sql_fetch($sql2);
    $ca_name = addslashes($row2['ca_name']);

    $it_name = addslashes($row['it_name']);

    $options .= "<option value=\"".$row['it_id']."/".$row['it_amount']."\">$ca_name : $it_name</option>\n";
}

echo $options;
?>
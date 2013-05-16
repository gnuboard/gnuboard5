<?php
/*
**  가격비교사이트 다나와 엔진페이지
*/
include_once('./_common.php');
?>
<?php
echo $_SERVER['HTTP_HOST'];

// \n상품코드#대분류#소분류#상품명#상품URL#가격
$str = "";
$sql = " select * from {$g4['shop_item_table']}
          where it_use = '1'
          order by ca_id ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $row2 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '".substr($row['ca_id'],0,2)."' ");

    if (strlen($row['ca_id']) >= 4)
        $row3 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '".substr($row['ca_id'],0,4)."' ");

    $str .= "\n";
    $str .= "{$row['it_id']}#{$row2['ca_name']}#{$row3['ca_name']}#{$row['it_name']}#".G4_SHOP_URL."/item.php?it_id={$row['it_id']}#{$row['it_price']}";
}
echo $str;
?>
<?php
/*
**  가격비교사이트 비비(베스트바이어) 엔진페이지
*/
function it_image($img)
{
    global $g4;

    $tmp = "$g4[path]/data/item/$img";
    if (file_exists($tmp) && $img) {
        $str = "$g4[url]/data/item/$img";
    } else {
        $str = "$g4[shop_url]/img/no_image.gif";
    }
    return $str;
}

include_once("./_common.php");
?>
<html>
<title>비비 엔진페이지</title>
<head>
<meta http-equiv="Cache-Control" content="no-cache"/> 
<meta http-equiv="Expires" content="0"/> 
<meta http-equiv="Pragma" content="no-cache"/> 
</head>
<body>
<?
// <p>상품번호^대분류^중분류^소분류^제조사^모델명^상품Url^이미지Url^가격
$str = "";
$sql = " select * from $g4[yc4_item_table]
          where it_use = '1'
          order by ca_id ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $image = it_image("$row[it_id]_m");

    $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,2)."' ");

    if (strlen($row[ca_id]) >= 4) 
        $row3 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,4)."' ");

    if (strlen($row[ca_id]) >= 6) 
        $row4 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,6)."' ");

    // 재고검사해서 없으면 상품가격을 0 으로 설정
    $stock = get_it_stock_qty($row[it_id]);
    if ($stock <= 0)
        $row[it_amount] = 0;

    $str .= "<p>$row[it_id]^$row2[ca_name]^$row3[ca_name]^$row4[ca_name]^$row[it_maker]^$row[it_name]^$g4[shop_url]/item.php?it_id=$row[it_id]^$image^$row[it_amount]";
    $str .= "\n";
}

echo "<p>" . $config[cf_title] . " 입니다. 총 (".$i.") 건 입니다.\n";
echo $str;
?>
</body>
</html>
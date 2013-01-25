<?php
/*
**  가격비교사이트 다나와 엔진페이지
*/
include_once("./_common.php");

$nl = ""; // new line \n

// 배송비
if ($default[de_send_cost_case] == '없음')
    $delivery = 0;
else
{
    // 배송비 상한일 경우 제일 앞에 배송비
    $tmp = explode(';', $default[de_send_cost_list]);
    $delivery = (int)$tmp[0];
}
?>
<?
// 상품ID^카테고리^상품명^제조사^이미지URL^상품URL^가격^적립금^할인쿠폰^무이자할부^사은품^모델명^추가정보^출시일^배송료
$str = "";
$sql = " select * from $g4[yc4_item_table]
          where it_use = '1'
          order by ca_id ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++) {
    $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,2)."' ");
    $ca_name = $row2[ca_name];

    if (strlen($row[ca_id]) >= 4) {
        $row3 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,4)."' ");
        $ca_name .= "|" . $row3[ca_name];
    }

    $str .= $nl;
    $str .= "$row[it_id]";    // 상품ID
    $str .= "^$ca_name";        // 카테고리
    $str .= "^$row[it_name]";   // 상품명
    $str .= "^$row[it_maker]";  // 제조사
    $str .= "^$g4[url]/data/item/{$row[it_id]}_m"; // 이미지URL
    $str .= "^$g4[shop_url]/item.php?it_id=$row[it_id]"; // 상품URL
    $str .= "^$row[it_amount]"; // 가격
    $str .= "^$row[it_point]";  // 적립금
    $str .= "^";  // 할인쿠폰
    $str .= "^";  // 무이자할부
    $str .= "^";  // 사은품
    $str .= "^$row[it_model]";  // 모델명
    $str .= "^";  // 추가정보
    $str .= "^";  // 출시일
    $str .= "^$delivery";       // 배송료

    $nl = "\n";
}

echo $str;
?>
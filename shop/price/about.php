<?php
include_once('./_common.php');

ob_start();

$lt = "";
$gt = "<!>";

// 배송비
if ($default['de_send_cost_case'] == '없음')
    $delivery = 0;
else if($default['de_send_cost_case'] == '상한')
{
    // 배송비 상한일 경우 제일 앞에 배송비 얼마 금액 이하
    $tmp = explode(';', $default['de_send_cost_limit']);
    $delivery_limit = (int)$tmp[0];

    // 배송비 상한일 경우 제일 앞에 배송비
    $tmp = explode(';', $default['de_send_cost_list']);
    $delivery = (int)$tmp[0];
}

$sql =" select * from {$g4['shop_item_table']} where it_use = '1' order by ca_id";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $ca_id1 = "";
    $ca_id2 = "";
    $ca_id3 = "";
    $ca_id4 = "";
    $ca_name1 = "";
    $ca_name2 = "";
    $ca_name3 = "";
    $ca_name4 = "";

    $ca_id1 = substr($row['ca_id'],0,2);
    $row2 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '$ca_id1' ");
    $ca_name1 = $row2['ca_name'];

    if (strlen($row['ca_id']) >= 4) {
        $ca_id2 = substr($row['ca_id'],0,4);
        $row2 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '$ca_id2' ");
        $ca_name2 = $row2['ca_name'];
    }

    if (strlen($row['ca_id']) >= 6) {
        $ca_id3 = substr($row['ca_id'],0,6);
        $row2 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '$ca_id3' ");
        $ca_name3 = $row2['ca_name'];
    }

    if (strlen($row['ca_id']) >= 8) {
        $ca_id4 = substr($row['ca_id'],0,8);
        $row2 = sql_fetch(" select ca_name from {$g4['shop_category_table']} where ca_id = '$ca_id4' ");
        $ca_name4 = $row2['ca_name'];
    }

    $PRDATE = substr($row['it_time'], 0, 10);

    // 개별배송비계산
    if($default['de_send_cost_case'] == '개별') {
        $delivery = get_item_sendcost($row['it_id'], $row['it_price'], 1);
    }

    // 상품이미지
    $img_url = get_it_imageurl($row['it_id']);

echo "{$lt}{$row['it_id']}{$gt}"; // 쇼핑몰 상품ID
echo "{$lt}C{$gt}"; // 상품구분 C/U/D 전체EP는 일괄적으로 C
echo "{$lt}{$row['it_name']}{$gt}"; // 상품명
echo "{$lt}{$row['it_price']}{$gt}"; // 판매가격
echo "{$lt}".G4_SHOP_URL."/item.php?it_id={$row['it_id']}{$gt}"; // 상품의 상세페이지 주소
echo "{$lt}".$img_url."{$gt}"; // 이미지 URL
echo "{$lt}$ca_id1{$gt}"; // 대분류 카테고리 코드
echo "{$lt}$ca_id2{$gt}"; // 중분류 카테고리 코드
echo "{$lt}$ca_id3{$gt}"; // 소분류 카테고리 코드
echo "{$lt}$ca_id4{$gt}"; // 세분류 카테고리 코드
echo "{$lt}$ca_name1{$gt}"; // 대 카테고리명
echo "{$lt}$ca_name2{$gt}"; // 중 카테고리명
echo "{$lt}$ca_name3{$gt}"; // 소 카테고리명
echo "{$lt}$ca_name4{$gt}"; // 세 카테고리명
echo "{$lt}{$gt}"; // 모델명
echo "{$lt}{$gt}"; // 브랜드
echo "{$lt}{$row['it_maker']}{$gt}"; // 메이커
echo "{$lt}{$row['it_origin']}{$gt}"; // 원산지
echo "{$lt}$PRDATE{$gt}"; // 상품등록일자
echo "{$lt}$delivery{$gt}"; // 배송비
echo "{$lt}{$gt}"; // 이벤트
echo "{$lt}{$gt}"; // 쿠폰금액
echo "{$lt}{$gt}"; // 무이자
echo "{$lt}{$row['it_point']}{$gt}"; // 적립금
echo "{$lt}Y{$gt}"; // 이미지변경여부
echo "{$lt}{$gt}"; // 물품특성정보
echo "{$lt}{$gt}"; // 상점내 매출비율
echo "{$lt}"; // 상품정보 변경시간
echo "\r\n";
}

$content = ob_get_contents();
ob_end_clean();

// 100124 : 옥션에서는 아직 utf-8 을 지원하지 않고 있음
$content = iconv('utf-8', 'euc-kr', $content);

echo $content;
?>
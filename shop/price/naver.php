<?php
include_once('./_common.php');

// clean the output buffer
ob_end_clean();

/*
EP 버전 3.0

네이버지식쇼핑상품EP (Engine Page) 제작및연동가이드 (제휴사제공용)
http://join.shopping.naver.com/misc/download/ep_guide.nhn

Field                   Status  Notes
id                      필수    판매하는 상품의 유니크한 상품ID
title                   필수    실제 서비스에 반영될 상품명(Title)
price_pc                필수    상품가격
link                    필수    상품URL
image_link              필수    해당 상품의 이미지URL
category_name1          필수    카테고리명(대분류)
category_name2          권장    카테고리명(중분류)
category_name3          권장    카테고리명(소분류)
category_name4          권장    카테고리명(세분류)
model_number            권장    모델명
brand                   권장    브랜드
maker                   권장    제조사
origin                  권장    원산지
event_words             권장    이벤트
coupon                  권장    쿠폰
interest_free_event     권장    무이자
point                   권장    포인트
shipping                필수    배송료
seller_id               권장    셀러 ID (오픈마켓에 한함)
class                   필수(요약)  I (신규상품) / U (업데이트 상품) / D (품절상품)
update_time             필수(요약)  상품정보 생성 시각
*/

$tab = "\t";

ob_start();

echo "id{$tab}title{$tab}price_pc{$tab}link{$tab}image_link{$tab}category_name1{$tab}category_name2{$tab}category_name3{$tab}category_name4{$tab}brand{$tab}maker{$tab}origin{$tab}point{$tab}review_count{$tab}shipping{$tab}class{$tab}update_time";

$sql =" select * from {$g5['g5_shop_item_table']} where it_use = '1' and it_soldout = '0' and it_tel_inq = '0' and it_price > '0' order by ca_id";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $cate1 = $cate2 = $cate3 = $cate4 = '';
    $caid1 = $caid2 = $caid3 = $caid4 = '';

    $caid1 = substr($row['ca_id'],0,2);
    $row2  = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$caid1' ");
    $cate1 = $row2['ca_name'];

    if (strlen($row['ca_id']) >= 8) {
        $caid4 = substr($row['ca_id'],0,8);
        $row2  = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$caid4' ");
        $cate4 = $row2['ca_name'];
    }

    if (strlen($row['ca_id']) >= 6) {
        $caid3 = substr($row['ca_id'],0,6);
        $row2  = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$caid3' ");
        $cate3 = $row2['ca_name'];
    }

    if (strlen($row['ca_id']) >= 4) {
        $caid2 = substr($row['ca_id'],0,4);
        $row2  = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$caid2' ");
        $cate2 = $row2['ca_name'];
    }

    // 배송비계산
    $delivery = get_item_sendcost2($row['it_id'], $row['it_price'], 1);

    // 상품이미지
    $img_url = get_it_imageurl($row['it_id']);

    // 포인트
    $it_point = get_item_point($row);

    $item_link = shop_item_url($row['it_id']);

    // 상태
    $class = 'U';
    $stock_qty = get_it_stock_qty($row['it_id']);

    if(substr($row['it_time'], 0, 10) == G5_TIME_YMD && $row['it_update_time'] >= $row['it_time'])
    $class = 'I';

    if($row['it_soldout'] || $stock_qty < 0)
    $class = 'D';

    // 리뷰 카운트
    $review_count = (int) $row['it_use_cnt'];

    echo "\n{$row['it_id']}{$tab}{$row['it_name']}{$tab}{$row['it_price']}{$tab}{$item_link}{$tab}{$img_url}{$tab}{$cate1}{$tab}{$cate2}{$tab}{$cate3}{$tab}{$cate4}{$tab}{$row['it_brand']}{$tab}{$row['it_maker']}{$tab}{$row['it_origin']}{$tab}{$it_point}{$tab}{$review_count}{$tab}{$delivery}{$tab}{$class}{$tab}{$row['it_update_time']}";
}

$content = ob_get_contents();
ob_end_clean();

echo $content;
<?php
include_once('./_common.php');



ob_start();

header("Content-Type: text/html; charset=utf-8");

/*
구분 태그명 내용    설명    크기
<<<tocnt>>>     전체상품수
<<<begin>>>     시작    상품시작 알림   필수
<<<mapid>>>     상품ID  해당사 상품 ID  필수
<<<lprice>>>    원판매가(할인전가격)    선택적필수
<<<price>>>     할인적용가  할인후가격  필수
<<<mprice>>>    모바일 할인적용가  할인후가격  선택적필수
<<<pname>>>     상품명  상품명  필수,varchar(500)
<<<pgurl>>>     상품링크    해당 상품으로 갈 상품URL    필수
<<<igurl>>>     이미지링크  상품이미지 링크
                (상품이미지 중 제일 큰이미지링크)   필수,varchar(255)
<<<cate1>>>     카테고리명  대분류명 필수
<<<caid1>>>     카테고리 ID(대분류)  필수
<<<cate2>>>     카테고리명  중분류명
<<<caid2>>>     카테고리 ID(중분류)
<<<cate3>>>     카테고리명  소분류명
<<<caid3>>>     카테고리 ID(소분류)
<<<cate4>>>     카테고리명  세분류명
<<<caid4>>>     카테고리 ID(세분류)

<<<model>>>     모델명
<<<brand>>>     브랜드명
<<<maker>>>     제조사

<<<coupo>>>     쿠폰/제휴쿠폰
<<<mcoupo>>>    모바일 쿠폰/제휴쿠폰
<<<pcard>>>     무이자할부
<<<point>>>     적립금/포인트
<<<deliv>>>     배송비  무료일 때는 0, 유료일 때는 배송금액, 착불은 -1
<<<event>>>     이벤트
<<<weight>>>    가중치값

<<<selid>>>     셀러 ID   선택
<<<insco>>>     별도설치비
<<<ftend>>>     끝알림 필수
*/

$lt = "<<<";
$gt = ">>>";
$shop_url = G5_SHOP_URL;
$data_url = G5_DATA_URL;

$sql =" select * from {$g5['g5_shop_item_table']} where it_use = '1' and it_soldout = '0' order by ca_id";
$result = sql_query($sql);
$totcnt = sql_num_rows($result);

echo $lt.'tocnt'.$gt.$totcnt.PHP_EOL;

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $cate1 = $cate2 = $cate3 = $cate4 = "";

    $row2 = sql_fetch(" select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '".substr($row['ca_id'],0,2)."' ");
    $cate1 = $row2['ca_name'];
    $caid1 = $row2['ca_id'];

    $cate2 = $cate3 = $cate4 = "";
    $caid2 = $caid3 = $caid4 = "";

    if (strlen($row['ca_id']) >= 8) {
        $row2 = sql_fetch(" select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '".substr($row['ca_id'],0,8)."' ");
        $cate4 = $row2['ca_name'];
        $caid4 = $row2['ca_id'];
    }

    if (strlen($row['ca_id']) >= 6) {
        $row2 = sql_fetch(" select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '".substr($row['ca_id'],0,6)."' ");
        $cate3 = $row2['ca_name'];
        $caid3 = $row2['ca_id'];
    }

    if (strlen($row['ca_id']) >= 4) {
        $row2 = sql_fetch(" select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '".substr($row['ca_id'],0,4)."' ");
        $cate2 = $row2['ca_name'];
        $caid2 = $row2['ca_id'];
    }

    $point = get_item_point($row);
    if( $point ){
        $point .= '원';
    }

    // 배송비계산
    $deliv = get_item_sendcost2($row['it_id'], $row['it_price'], 1);

    // 상품이미지
    $img_url = get_it_imageurl($row['it_id']);

    $str = "{$lt}begin{$gt}".PHP_EOL;
    $str .= "{$lt}mapid{$gt}{$row['it_id']}".PHP_EOL;
    $str .= "{$lt}price{$gt}{$row['it_price']}".PHP_EOL;
    $str .= "{$lt}pname{$gt}{$row['it_name']}".PHP_EOL;
    $str .= "{$lt}pgurl{$gt}".shop_item_url($row['it_id']).PHP_EOL;
    $str .= "{$lt}igurl{$gt}$img_url".PHP_EOL;
    $str .= "{$lt}cate1{$gt}$cate1".PHP_EOL;
    $str .= "{$lt}caid1{$gt}$caid1".PHP_EOL;
    if( $cate2 ){
        $str .= "{$lt}cate2{$gt}$cate2".PHP_EOL;
    }
    if( $caid2 ){
        $str .= "{$lt}caid2{$gt}$caid2".PHP_EOL;
    }
    if( $cate3 ){
        $str .= "{$lt}cate3{$gt}$cate3".PHP_EOL;
    }
    if( $caid3 ){
        $str .= "{$lt}caid3{$gt}$caid3".PHP_EOL;
    }
    if( $cate4 ){
        $str .= "{$lt}cate4{$gt}$cate4".PHP_EOL;
    }
    if( $caid4 ){
        $str .= "{$lt}caid4{$gt}$caid4".PHP_EOL;
    }
    if( $row['it_model'] ){
        $str .= "{$lt}model{$gt}{$row['it_model']}".PHP_EOL;
    }
    if( $row['it_brand'] ){
        $str .= "{$lt}brand{$gt}{$row['it_brand']}".PHP_EOL;
    }
    if( $row['it_maker'] ){
        $str .= "{$lt}maker{$gt}{$row['it_maker']}".PHP_EOL;
    }
    $str .= "{$lt}point{$gt}$point".PHP_EOL;
    $str .= "{$lt}deliv{$gt}$deliv".PHP_EOL;
    $str .= "{$lt}ftend{$gt}".PHP_EOL;

echo iconv('utf-8', 'euc-kr', $str);
}



$content = ob_get_contents();
ob_end_clean();

echo $content;
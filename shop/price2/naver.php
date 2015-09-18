<?php
include_once('./_common.php');

ob_start();

/*
네이버지식쇼핑상품EP (Engine Page) 제작및연동가이드 (제휴사제공용)
http://imgshopping.naver.net/admin/join/download/db_url/db_url_guide_20101205.pdf

Field   Status  Notes
<<<begin>>> 필수    상품의 시작을 알리는 필드
<<<mapid>>> 필수    판매하는 상품의 유니크한 상품ID
<<<pname>>> 필수    실제 서비스에 반영될 상품명(Title)
<<<price>>> 필수    해당 상품의 판매가격
<<<pgurl>>> 필수    해당 상품을 구매할 수 있는 상품URL
<<<igurl>>> 필수    해당 상품의 이미지URL
<<<cate1>>> 필수    판매하는 상품의 카테고리명(대분류)
<<<cate2>>> 선택    판매하는 상품의 카테고리명(중분류)
<<<cate3>>> 선택    판매하는 상품의 카테고리명(소분류)
<<<cate4>>> 선택    판매하는 상품의 카테고리명(세분류)
<<<caid1>>> 필수    판매하는 상품의 카테고리명(대분류)
<<<caid2>>> 선택    판매하는 상품의 카테고리명(중분류)
<<<caid3>>> 선택    판매하는 상품의 카테고리명(소분류)
<<<caid4>>> 선택    판매하는 상품의 카테고리명(세분류)
<<<model>>> 선택    모델명
<<<brand>>> 선택    브랜드
<<<maker>>> 선택    제조사
<<<origi>>> 선택    원산지
<<<deliv>>> 필수    배송료
<<<event>>> 선택    이벤트
<<<coupo>>> 선택    쿠폰
<<<pcard>>> 선택    무이자
<<<point>>> 선택    포인트
<<<mvurl>>> 선택    동영상 상품 여부
<<<selid>>> 선택    셀러 ID (오픈마켓에 한함)
<<<barcode>>> 선택  바코드 (전체EP만 사용)
<<<ftend>>> 필수    상품의 마지막을 알리는 필드
*/

$lt = "<<<";
$gt = ">>>";

$sql =" select * from {$g5['g5_shop_item_table']} where it_use = '1' order by ca_id";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $cate1 = $cate2 = $cate3 = $cate4 = "";
    $caid1 = $caid2 = $caid3 = $caid4 = "";

    $caid1 = substr($row['ca_id'],0,2);
    $row2 = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$caid1' ");
    $cate1 = $row2['ca_name'];

    $caid2 = $caid3 = $caid4 = "";
    $cate2 = $cate3 = $cate4 = "";

    if (strlen($row['ca_id']) >= 8) {
        $caid4 = substr($row['ca_id'],0,8);
        $row2 = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$caid4' ");
        $cate4 = $row2['ca_name'];
    }

    if (strlen($row['ca_id']) >= 6) {
        $caid3 = substr($row['ca_id'],0,6);
        $row2 = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$caid3' ");
        $cate3 = $row2['ca_name'];
    }

    if (strlen($row['ca_id']) >= 4) {
        $caid2 = substr($row['ca_id'],0,4);
        $row2 = sql_fetch(" select ca_name from {$g5['g5_shop_category_table']} where ca_id = '$caid2' ");
        $cate2 = $row2['ca_name'];
    }

    // 상품이미지
    $img_url = get_it_imageurl($row['it_id']);

    // 상품별옵션
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' and io_use = '1' order by io_no asc ";
    $result2 = sql_query($sql);
    $opt_count = @mysql_num_rows($result2);

    if(!$opt_count) {
        $it_name = $row['it_name'];
        $buy_url = G5_SHOP_URL.'/itembuy.php?it_id='.$row['it_id'];
        $it_price = $row['it_price'];
        $delivery = get_item_sendcost2($row['it_id'], $it_price, 1);
        $it_point = get_item_point($row);

        echo <<< HEREDOC
{$lt}begin{$gt}
{$lt}mapid{$gt}{$row['it_id']}
{$lt}pname{$gt}$it_name
{$lt}price{$gt}$it_price
{$lt}pgurl{$gt}$buy_url
{$lt}igurl{$gt}$img_url
{$lt}cate1{$gt}$cate1
{$lt}cate2{$gt}$cate2
{$lt}cate3{$gt}$cate3
{$lt}cate4{$gt}$cate4
{$lt}caid1{$gt}$caid1
{$lt}caid2{$gt}$caid2
{$lt}caid3{$gt}$caid3
{$lt}caid4{$gt}$caid4
{$lt}model{$gt}
{$lt}brand{$gt}
{$lt}maker{$gt}{$row['it_maker']}
{$lt}origi{$gt}{$row['it_origin']}
{$lt}deliv{$gt}$delivery
{$lt}event{$gt}
{$lt}coupo{$gt}
{$lt}pcard{$gt}
{$lt}point{$gt}$it_point
{$lt}mvurl{$gt}
{$lt}selid{$gt}
{$lt}barcode{$gt}
{$lt}ftend{$gt}

HEREDOC;

    } else {
        $subj = explode(',', $row['it_option_subject']);
        for($k=0; $row2=sql_fetch_array($result2); $k++) {
            $it_name = $row['it_name'].' ';
            $opt = explode(chr(30), $row2['io_id']);
            $sep = '';
            for($j=0; $j<count($subj); $j++) {
                $it_name .= $sep.$subj[$j].':'.$opt[$j];
                $sep = ' ';
            }
            $buy_url = G5_SHOP_URL.'/itembuy.php?it_id='.$row['it_id'].'&amp;opt='.$row2['io_id'];
            $it_price = $row['it_price'] + $row2['io_price'];
            $delivery = get_item_sendcost2($row['it_id'], $it_price, 1);
            $it_point = get_item_point($row, $row2['io_id']);

    echo <<< HEREDOC
{$lt}begin{$gt}
{$lt}mapid{$gt}{$row['it_id']}
{$lt}pname{$gt}$it_name
{$lt}price{$gt}$it_price
{$lt}pgurl{$gt}$buy_url
{$lt}igurl{$gt}$img_url
{$lt}cate1{$gt}$cate1
{$lt}cate2{$gt}$cate2
{$lt}cate3{$gt}$cate3
{$lt}cate4{$gt}$cate4
{$lt}caid1{$gt}$caid1
{$lt}caid2{$gt}$caid2
{$lt}caid3{$gt}$caid3
{$lt}caid4{$gt}$caid4
{$lt}model{$gt}
{$lt}brand{$gt}
{$lt}maker{$gt}{$row['it_maker']}
{$lt}origi{$gt}{$row['it_origin']}
{$lt}deliv{$gt}$delivery
{$lt}event{$gt}
{$lt}coupo{$gt}
{$lt}pcard{$gt}
{$lt}point{$gt}$it_point
{$lt}mvurl{$gt}
{$lt}selid{$gt}
{$lt}barcode{$gt}
{$lt}ftend{$gt}

HEREDOC;
        }
    }
}

$content = ob_get_contents();
ob_end_clean();

// 091223 : 네이버에서는 아직 utf-8 을 지원하지 않고 있음
$content = iconv('utf-8', 'euc-kr', $content);

echo $content;
?>
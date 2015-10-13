<?php
include_once('./_common.php');

ob_start();

header("Content-Type: text/html; charset=utf-8");

/*
	 구분 태그명	내용	설명	크기
1	<<<begin>>>	    시작	상품시작 알림	필수
2	<<<pid>>>	    상품ID	해당사 상품 ID	필수,varchar(50)
3	<<<price>>>	    가격	상품 가격 	필수,number
4	<<<pname>>>	    상품명	상품명	필수,varchar(500)
5	<<<pgurl>>>	    상품링크	해당 상품으로 갈 상품URL	필수,varchar(255)
6	<<<igurl>>>	    이미지링크	상품이미지 링크
                    (상품이미지 중 제일 큰이미지링크)	필수,varchar(255)
7	<<<cate1>>>     대분류ID	대분류 코드	필수,varchar(20)
8	<<<cate2>>>     중분류ID	중분류 코드	varchar(20)
9	<<<cate3>>>     소분류ID	소분류 코드	varchar(20)
10	<<<cate4>>>     세분류ID	세분류 코드	varchar(20)
11	<<<catename1>>>	대분류명		필수,varchar(50)
12	<<<catename2>>>	중분류명		varchar(50)
13	<<<catename3>>>	소분류명		varchar(50)
14	<<<catename4>>>	세분류명		varchar(50)
15	<<<model>>>	    모델명		varchar(255)
16	<<<brand>>>	    브랜드명		varchar(255)
17	<<<maker>>>	    제조사		varchar(255)
18	<<<pdate>>>	    출시일	예) 20070101	varchar(8)
19	<<<weight>>>	가중치값	숫자 ( 0  ~ )
                    쇼핑몰대분류카테고리 기준으로
                    쇼핑몰내부에서 책정되는 상품에 대한
                    인기점수	Numer(14)
20	<<<sales>>>	    판매량	해당 상품이 팔린 누적판매량	number(14)
21	<<<coupon>>>	쿠폰정보	퍼센트 쿠폰인 경우
                    ex) 5%할인쿠폰 -> 5%
                    일정가격할인 쿠폰인 경우
                    ex) 3000원할인쿠폰 -> 3000원
                    만 표기
                    0%, 0원은 값을 제거	varchar(255)

22	<<<pcard>>>     무이자/할부	카드이름개월수 형식으로 표시
                    ex) 삼성2~3/롯데3/현대6
                    0개월 일 때에는 값을 제거	varchar(255)
23	<<<point>>>	    적립금/포인트	텍스트정보
                    0일 때에는 값을 제거	varchar(255)
24	<<<deliv>>>	    배송비	무료일 때는 0
                    유료일 때는 1
                    조건부무료일 때는 2 로 표기	number
25	<<<deliv2>>>	배송비 조건	유료(deliv필드 코드1번) or
                    조건부무료(deliv필드 코드2번)
                    인 경우에 상세 조건 표기
                    ex)3만원미만무료 or 2500원	varchar(20)
26	<<<review>>>	상품평수	상품의 상품평개수가 몇 개인지 숫자만 표기	number
27	<<<event>>>	    이벤트	해당 상품의 이벤트 내용을 표기
                    ex) 새봄맞이 행복이벤트! 새출발 아이템 50%SALE
                    신규회원 5%+전상품 3%할인쿠폰	varchar(255)
28	<<<eventurl>>>	이벤트url	event 페이지 URL	varchar(255)
29	<<<sellername>>>	실판매자샵명	실제로 상품을 판매하고있는 판매자샵 이름 표기 (판매샵의 대표자명이 아니라 판매샵명)
                        판매자샵명이 없는 경우에는 판매자아이디로 표기 (자체판매하는 경우에는 표기X)	varchar(20)
30	<<<sellershop>>>	실판매자샵주소	판매자의 미니샵 주소 or 판매자샵주소
                        (자체판매하는 경우에는 표기X)	varchar(50)
31	<<<sellergrade>>>	실판매자등급	판매자등급을 5점 만점기준으로
                        (자체판매하는 경우에는 표기X)	number
32	<<<end>>>	    끝알림	끝알림 태그	필수
*/

$lt = "<<<";
$gt = ">>>";

$sql =" select * from {$g5['g5_shop_item_table']} where it_use = '1' order by ca_id";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $cate1 = $cate2 = $cate3 = $cate4 = "";

    $row2 = sql_fetch(" select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '".substr($row['ca_id'],0,2)."' ");
    $cate1     = $row2['ca_id'];
    $catename1 = $row2['ca_name'];

    $cate2 = $cate3 = $cate4 = "";
    $catename2 = $catename3 = $catename4 = "";

    if (strlen($row['ca_id']) >= 8) {
        $row2 = sql_fetch(" select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '".substr($row['ca_id'],0,8)."' ");
        $cate4     = $row2['ca_id'];
        $catename4 = $row2['ca_name'];
    }

    if (strlen($row['ca_id']) >= 6) {
        $row2 = sql_fetch(" select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '".substr($row['ca_id'],0,6)."' ");
        $cate3     = $row2['ca_id'];
        $catename3 = $row2['ca_name'];
    }

    if (strlen($row['ca_id']) >= 4) {
        $row2 = sql_fetch(" select ca_id, ca_name from {$g5['g5_shop_category_table']} where ca_id = '".substr($row['ca_id'],0,4)."' ");
        $cate2     = $row2['ca_id'];
        $catename2 = $row2['ca_name'];
    }

    $pdate = date("Ymd", strtotime($row['it_time']));

    // 상품이미지
    $img_url = get_it_imageurl($row['it_id']);

    // 상품별옵션
    $sql = " select * from {$g5['g5_shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' and io_use = '1' order by io_no asc ";
    $result2 = sql_query($sql);
    $opt_count = @sql_num_rows($result2);

    if(!$opt_count) {
        $it_name = $row['it_name'];
        $buy_url = G5_SHOP_URL.'/itembuy.php?it_id='.$row['it_id'];
        $it_price = $row['it_price'];
        $delivery = get_item_sendcost2($row['it_id'], $it_price, 1);
        $point = get_item_point($row);

        if($delivery) {
            $deliv  = 1;
            $deliv2 = $delivery.'원';
        } else {
            $deliv  = 0;
            $deliv2 = "";
        }

    $str = <<< HEREDOC
{$lt}begin{$gt}
{$lt}pid{$gt}{$row['it_id']}
{$lt}price{$gt}$it_price
{$lt}pname{$gt}$it_name
{$lt}pgurl{$gt}$buy_url
{$lt}igurl{$gt}$img_url
{$lt}cate1{$gt}$cate1
{$lt}cate2{$gt}$cate2
{$lt}cate3{$gt}$cate3
{$lt}cate4{$gt}$cate4
{$lt}catename1{$gt}$catename1
{$lt}catename2{$gt}$catename2
{$lt}catename3{$gt}$catename3
{$lt}catename4{$gt}$catename4
{$lt}maker{$gt}{$row['it_maker']}
{$lt}pdate{$gt}$pdate
{$lt}point{$gt}$point
{$lt}deliv{$gt}$deliv
{$lt}deliv2{$gt}$deliv2
{$lt}end{$gt}

HEREDOC;

// 131227 : 쇼핑하우에서는 아직 utf-8 을 지원하지 않고 있음
echo iconv('utf-8', 'euc-kr', $str);

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
            $point = get_item_point($row, $row2['io_id']);

            if($delivery) {
                $deliv  = 1;
                $deliv2 = $delivery.'원';
            } else {
                $deliv  = 0;
                $deliv2 = "";
            }

    $str = <<< HEREDOC
{$lt}begin{$gt}
{$lt}pid{$gt}{$row['it_id']}
{$lt}price{$gt}$it_price
{$lt}pname{$gt}$it_name
{$lt}pgurl{$gt}$buy_url
{$lt}igurl{$gt}$img_url
{$lt}cate1{$gt}$cate1
{$lt}cate2{$gt}$cate2
{$lt}cate3{$gt}$cate3
{$lt}cate4{$gt}$cate4
{$lt}catename1{$gt}$catename1
{$lt}catename2{$gt}$catename2
{$lt}catename3{$gt}$catename3
{$lt}catename4{$gt}$catename4
{$lt}maker{$gt}{$row['it_maker']}
{$lt}pdate{$gt}$pdate
{$lt}point{$gt}$point
{$lt}deliv{$gt}$deliv
{$lt}deliv2{$gt}$deliv2
{$lt}end{$gt}

HEREDOC;

// 131227 : 쇼핑하우에서는 아직 utf-8 을 지원하지 않고 있음
echo iconv('utf-8', 'euc-kr', $str);

        }
    }
}

$content = ob_get_contents();
ob_end_clean();

echo $content;
?>
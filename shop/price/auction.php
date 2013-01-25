<?
include_once("./_common.php");

ob_start();

/*
    옥션 오픈 쇼핑
    DB 엔진페이지 제작가이드 다운받기
    http://openshopping.auction.co.kr/customer/pds/openShoppingGuide1.1.zip

[[_BEGIN]]
[[PRODID]]a111
[[PRNAME]]테스트상품1
[[_PRICE]]84000
[[PRDURL]]http://www.auction.co.kr/pinfo/pdetail.asp?pid=a111
[[IMGURL]]http://www.auction.co.kr/images/aa_1.jpg
[[CATE_1]]의류
[[CATE_2]]여성의류
[[CATE_3]]나시
[[CATE_4]]
[[_MODEL]]쉬폰
[[_BRAND]]
[[_MAKER]]
[[ORIGIN]]중국
[[PRDATE]]2008-01-11
[[DELIVR]]0/50000/3000
[[_EVENT]]
[[COUPON]]
[[PRCARD]]신한5개월
[[_POINT]]
[[MODIMG]]
[[SRATIO]]3.5
[[___END]]

필수
[[_BEGIN]]  // 상품시작을 알림
[[PRODID]]  // 상품아이디
[[PRNAME]]  // 상품명
[[_PRICE]]  // 가격 (숫자로만 표시, 컴마 제외)
[[PRDURL]]  // 상품 상세페이지 URL
[[IMGURL]]  // 상품 대 이미지
[[CATE_1]]  // 쇼핑몰 대 카테고리
[[___END]]  // 상품종료를 알림
*/

$lt = "[[";
$gt = "]]";

// 배송비
if ($default[de_send_cost_case] == '없음')
    $delivery = 0;
else
{
    // 배송비 상한일 경우 제일 앞에 배송비 얼마 금액 이하
    $tmp = explode(';', $default[de_send_cost_limit]);
    $delivery_limit = (int)$tmp[0];

    // 배송비 상한일 경우 제일 앞에 배송비
    $tmp = explode(';', $default[de_send_cost_list]);
    $delivery = (int)$tmp[0];
}

$sql =" select * from $g4[yc4_item_table] where it_use = '1' order by ca_id";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,2)."' ");
    $ca_name1 = $row2[ca_name];

    if (strlen($row[ca_id]) >= 4) {
        $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,4)."' ");
        $ca_name2 = $row2[ca_name];
    }

    if (strlen($row[ca_id]) >= 6) {
        $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,6)."' ");
        $ca_name3 = $row2[ca_name];
    }

    if (strlen($row[ca_id]) >= 8) {
        $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,8)."' ");
        $ca_name4 = $row2[ca_name];
    }

    $PRDATE = substr($row[it_time], 0, 10);

echo <<< HEREDOC
{$lt}_BEGIN{$gt}
{$lt}PRODID{$gt}$row[it_id]
{$lt}PRNAME{$gt}$row[it_name]
{$lt}_PRICE{$gt}$row[it_amount]
{$lt}PRDURL{$gt}$g4[shop_url]/item.php?it_id=$row[it_id]
{$lt}IMGURL{$gt}$g4[url]/data/item/{$row[it_id]}_l1
{$lt}CATE_1{$gt}$ca_name1
{$lt}CATE_2{$gt}$ca_name2
{$lt}CATE_3{$gt}$ca_name3
{$lt}CATE_4{$gt}$ca_name4
{$lt}_MODEL{$gt}
{$lt}_BRAND{$gt}
{$lt}_MAKER{$gt}$row[it_maker]
{$lt}ORIGIN{$gt}$row[it_origin]
{$lt}PRDATE{$gt}$PRDATE
{$lt}DELIVR{$gt}0/$delivery_limit/$delivery
{$lt}_EVENT{$gt}
{$lt}COUPON{$gt}
{$lt}PRCARD{$gt}
{$lt}_POINT{$gt}$row[it_point]
{$lt}MODIMG{$gt}Y
{$lt}SRATIO{$gt}
{$lt}___END{$gt}

HEREDOC;
}

$content = ob_get_contents();
ob_end_clean();

// 100124 : 옥션에서는 아직 utf-8 을 지원하지 않고 있음
if (strtolower($g4[charset]) == 'utf-8') {
    $content = iconv('utf-8', 'euc-kr', $content);
}

echo $content;
?>
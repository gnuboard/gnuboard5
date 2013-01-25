<?
include_once("./_common.php");

ob_start();

/*
    옥션 오픈 쇼핑
    DB 엔진페이지 제작가이드 다운받기
    http://openshopping.auction.co.kr/customer/pds/openShoppingGuide1.1.zip

// 요약상품 URL

[[_BEGIN]]  // 상품시작을 알림
[[PRODID]]  // 상품아이디
[[PRNAME]]  // 상품명
[[_PRICE]]  // 가격 (숫자로만 표시, 컴마 제외)
[[___END]]  // 상품종료를 알림
*/

$lt = "[[";
$gt = "]]";

$sql =" select * from $g4[yc4_item_table] where it_use = '1' order by ca_id";
$result = sql_query($sql);

for ($i=0; $row=sql_fetch_array($result); $i++)
{

echo <<< HEREDOC
{$lt}_BEGIN{$gt}
{$lt}PRODID{$gt}$row[it_id]
{$lt}PRNAME{$gt}$row[it_name]
{$lt}_PRICE{$gt}$row[it_amount]
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
<?php
/*
**  가격비교사이트 샵바인더 엔진페이지
*/
@extract($_GET);

function paging($write_pages, $cur_page, $total_page, $url)
{
    global $cfg;

    $str = "";

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1)
        $str .= "<a href='" . $url . ($start_page-1) . "'>...</a>";

    if ($total_page > 1)
        for ($k=$start_page;$k<=$end_page;$k++)
            $str .= "<a href='$url$k'>$k</a>";

    if ($total_page > $end_page)
        $str .= "<a href='" . $url . ($end_page+1) . "'>...</a>";

    return $str;
}

include_once("./_common.php");

// 페이지당 행수
$page_rows = 500;

$sql = " select count(*) as cnt from $g4[yc4_item_table] where it_use = '1' ";
$row = sql_fetch($sql);
$total_count = $row[cnt];
?>
<html>
<title>샵바인더 엔진페이지</title>
<head>
<meta http-equiv="Cache-Control" content="no-cache"/> 
<meta http-equiv="Expires" content="0"/> 
<meta http-equiv="Pragma" content="no-cache"/> 
<style type="text/css">
<!--
body, td {font-family:굴림; font-size:9pt;}

//-->
</style>
</head>
<body>
<table border="1" width="90%" align="center" cellspacing="2" cellpadding="3">
<tr> 
    <td width="30">번호</td>
    <td width="65">분류1</td>
    <td width="65">분류2</td>
    <td width="45">분류3</td>
    <td width="70">분류4</td>
    <td width="70">제조회사</td>
    <td width="100">상품명</td>
    <td width="100">상품코드</td>
    <td width="80">가격</td>
    <td width="80">이벤트</td>
    <td width="80">이미지URL</td>
    <td width="80">배송료</td>
    <td width="80">할인쿠폰</td>
    <td width="80">제조년월</td>
</tr>
<?
// 전체 페이지 계산
$total_page  = ceil($total_count / $page_rows);
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page == "") $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $page_rows;

$sql = " select * from $g4[yc4_item_table] where it_use = '1' order by ca_id limit $from_record, $page_rows ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $row2 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,2)."' ");

    if (strlen($row[ca_id]) >= 4) 
        $row3 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,4)."' ");
    else
        $row3[ca_name] = "&nbsp;";

    if (strlen($row[ca_id]) >= 6) 
        $row4 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,6)."' ");
    else
        $row4[ca_name] = "&nbsp;";

    if (strlen($row[ca_id]) >= 8) 
        $row5 = sql_fetch(" select ca_name from $g4[yc4_category_table] where ca_id = '".substr($row[ca_id],0,8)."' ");
    else
        $row5[ca_name] = "&nbsp;";

    $num = (($page - 1) * $page_rows) + $i + 1;
    $delivery = 0;      // 배송료

    $qty = (int)get_it_stock_qty($row[it_id]);
    if ($qty <= 0) $row[it_amount] = 0;

    echo "<tr>
		<td width=\"30\">$num&nbsp;</td>
		<td width=\"65\">$row2[ca_name]</td>
		<td width=\"65\">$row3[ca_name]</td>
		<td width=\"45\">$row4[ca_name]</td>
		<td width=\"70\">$row5[ca_name]</td>
		<td width=\"70\">$row[it_maker]&nbsp;</td>
		<td width=\"100\"><a href='{$g4[shop_url]}/item.php?it_id=$row[it_id]'>$row[it_name]&nbsp;</a></td>
		<td width=\"100\">$row[it_id]&nbsp;</td>
		<td width=\"80\">".number_format($row[it_amount])."&nbsp;</td>
		<td width=\"80\">&nbsp;</td>
		<td width=\"80\">{$g4[url]}/data/item/{$row[it_id]}_l1&nbsp;</td>
		<td width=\"80\">$delivery</td>
		<td width=\"80\">&nbsp;</td>
		<td width=\"80\">&nbsp;</td>
</tr>\n";
}
?>
</table>
<DIV>
<p align=center>
<?=paging($page_rows, $page, $total_page, "./shopbinder.php?page=");?>
</DIV>
</body>
</html>
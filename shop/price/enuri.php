<?php
/*
**  가격비교사이트 에누리 엔진페이지
*/
@extract($_GET);

function paging($write_pages, $cur_page, $total_page, $url )
{
    global $cfg;

    $str = "";

    if ($cur_page > 1)
    {
        $str .= "<a href='" . $url . ($cur_page-1) . "'>◀</a>";
    }

    $start_page = ( ( (int)( ($cur_page - 1 ) / $write_pages ) ) * $write_pages ) + 1;
    $end_page = $start_page + $write_pages - 1;

    if ($end_page >= $total_page) $end_page = $total_page;

    if ($start_page > 1)
        $str .= "<a href='" . $url . ($start_page-1) . "'>...</a>";

    if ($total_page > 1)
        for ($k=$start_page;$k<=$end_page;$k++)
            if ($cur_page != $k)
                $str .= "[<a href='$url$k'>$k</a>]";
            else
                $str .= " <b>$k</b> ";

    if ($total_page > $end_page)
        $str .= "<a href='" . $url . ($end_page+1) . "'>...</a>";

    if ($cur_page < $total_page)
    {
        $str .= "<a href='$url" . ($cur_page+1) . "'>▶</a>";
    }
    $str .= "";

    return $str;
}


include_once("./_common.php");

// 페이지당 행수
$page_rows = 1000;

$sql = " select count(*) as cnt from $g4[yc4_item_table] where it_use = '1' and ca_id LIKE '$ca_id%'";
$row = sql_fetch($sql);
$total_count = $row[cnt];
?>
<html>
<title>에누리 엔진페이지</title>
<head>
<meta http-equiv="Cache-Control" content="no-cache"/> 
<meta http-equiv="Expires" content="0"/> 
<meta http-equiv="Pragma" content="no-cache"/> 
<style type="text/css">
<!--
A:link		{text-decoration: underline; color:steelblue}
A:visited	{text-decoration: none; color:steelblue}
A:hover		{text-decoration: underline; color:RoyalBlue}   
font		{font-family:굴림; font-size:10pt}
th,td		{font-family:굴림; font-size:10pt ; height:15pt}

//-->
</style>
</head>
<body>

<p align=center>상품수 : <?=number_format($total_count)?> 개

<table border="0" cellspacing="1" cellpadding="5" bgcolor="black" width="90%" align='center'>
<tr bgcolor="#ededed" align=center>
    <td>번호</td>
    <td>제품명</td>
    <td>가격</td>
    <td>재고유무</td>
    <td>배송</td>
    <td>웹상품이미지</td>
    <td>할인쿠폰</td>
    <td>계산서</td>
    <td>제조사</td>
    <td>상품코드</td>
</tr>
<?
// 전체 페이지 계산
$total_page  = ceil($total_count / $page_rows);
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page == "") $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $page_rows;

$caid = addslashes($ca_id);
$sql = " select * from $g4[yc4_item_table]
          where it_use = '1' 
          and ca_id LIKE '$caid%'
          order by ca_id    
          limit $from_record, $page_rows ";

$result = sql_query($sql);

for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $stock = get_it_stock_qty($row[it_id]);

    if ($stock)
        $stock = "재고있음";
    else
        $stock = "재고없음";

    $num = (($page - 1) * $page_rows) + $i + 1;

    if ($default[de_send_cost_case] == '없음')
        $send_cost = '무료';
    else
        $send_cost = '유료';

    echo "
	<tr bgcolor='white'>
		<td align='center'>$num</td>
		<td><a href='{$g4[shop_url]}/item.php?it_id=$row[it_id]'>$row[it_name]</a></td>
		<td align='center'>".number_format($row[it_amount])."</td>
		<td align='center'>$stock</td>
		<td align='center'>$send_cost</td>
		<td align='center'>$g4[url]/data/item/{$row[it_id]}_m</td>
		<td align='center'>1</td>
		<td align='center'>N</td>
		<td align='center'>".get_text($row[it_maker])."</td>
		<td align='center'>$row[it_id]</td>
	</tr>
        ";
}

?>
</table>

<p align=center>
<?=paging(1000, $page, $total_page, "./enuri.php?ca_id=$caid&page=");?>

</body>
</html>
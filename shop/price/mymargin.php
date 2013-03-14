<?php
/*
**  가격비교사이트 마이마진 엔진페이지
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
            if ($cur_page != $k)
                $str .= "[<a href='$url$k'>$k</a>]";
            else
                $str .= " <b>$k</b> ";

    if ($total_page > $end_page)
        $str .= "<a href='" . $url . ($end_page+1) . "'>...</a>";

    return $str;
}

function it_image($img)
{
    global $g4;

    $tmp = "$g4[path]/data/item/$img";
    if (file_exists($tmp) && $img) {
        $str = "$g4[url]/data/item/$img";
    } else {
        $str = "$g4[shop_url]/img/no_image.gif";
    }
    return $str;
}

include_once("./_common.php");

// 페이지당 행수
$page_rows = 100;

$sql = " select count(*) as cnt from $g4[yc4_item_table] where it_use = '1' ";
$row = sql_fetch($sql);
$total_count = $row[cnt];
?>
<html>
<title>마이마진 엔진페이지</title>
<head>
<meta http-equiv="Cache-Control" content="no-cache"/> 
<meta http-equiv="Expires" content="0"/> 
<meta http-equiv="Pragma" content="no-cache"/> 
<style type="text/css">
<!--
body, td {font-family:굴림; font-size:10pt;}

//-->
</style>
</head>
<body>

<table border=1>
<tr>
    <td>일련번호</td>
    <td>제품코드</td>
    <td>제품명</td>
    <td>제품가격</td>
    <td>상품분류</td>
    <td>제조사</td>
    <td>이미지</td>
</tr>
<?
// 전체 페이지 계산
$total_page  = ceil($total_count / $page_rows);
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page == "") $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $page_rows;

$sql = " select * from $g4[yc4_item_table]
          where it_use = '1'
          order by ca_id 
          limit $from_record, $page_rows ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++) 
{
    $image = it_image("$row[it_id]_m");

    $num = (($page - 1) * $page_rows) + $i;

    $category = $bar = "";
    $len = strlen($row[ca_id]) / 2;
    for ($k=1; $k<=$len; $k++) 
    {
        $code = substr($row[ca_id],0,$k*2);

        $sql3 = " select ca_name from $g4[yc4_category_table] where ca_id = '$code' ";
        $row3 = sql_fetch($sql3);

        $category .= $bar . $row3[ca_name];
        $bar = "/";
    }

    echo "
	<tr>
		<td>$num</td>
		<td>$row[it_id]</td>
		<td><a href='{$g4[shop_url]}/item.php?it_id=$row[it_id]'>$row[it_name]</a></td>
		<td>$row[it_amount]</td>
		<td>$category</td>
		<td>$row[it_maker]</td>
		<td>$image</td>
	</tr>
        ";
}
?>
<tr>
    <td colspan=7><?=paging(1000, $page, $total_page, "./mymargin.php?page=");?> &nbsp;</td>
</tr>
</table>

</body>
</html>
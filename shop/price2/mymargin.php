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

    $tmp = G4_DATA_PATH.'/item/'.$img;
    if (file_exists($tmp) && $img) {
        $str = G4_DATA_URL.'/item/'.$img;
    } else {
        $str = G4_SHOP_URL.'/img/no_image.gif';
    }
    return $str;
}

include_once('./_common.php');

// 페이지당 행수
$page_rows = 100;

$sql = " select count(*) as cnt from {$g4['shop_item_table']} where it_use = '1' ";
$row = sql_fetch($sql);
$total_count = $row['cnt'];
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
<?php
// 전체 페이지 계산
$total_page  = ceil($total_count / $page_rows);
// 페이지가 없으면 첫 페이지 (1 페이지)
if ($page == "") $page = 1;
// 시작 레코드 구함
$from_record = ($page - 1) * $page_rows;

$sql = " select * from {$g4['shop_item_table']}
          where it_use = '1'
          order by ca_id
          limit $from_record, $page_rows ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $num = (($page - 1) * $page_rows) + $i;

    $category = $bar = "";
    $len = strlen($row['ca_id']) / 2;
    for ($k=1; $k<=$len; $k++)
    {
        $code = substr($row['ca_id'],0,$k*2);

        $sql3 = " select ca_name from {$g4['shop_category_table']} where ca_id = '$code' ";
        $row3 = sql_fetch($sql3);

        $category .= $bar . $row3['ca_name'];
        $bar = "/";
    }

    // 상품이미지
    $image = get_it_imageurl($row['it_id']);

    // 상품별옵션
    $sql = " select * from {$g4['shop_item_option_table']} where it_id = '{$row['it_id']}' and io_type = '0' and io_use = '1' order by io_no asc ";
    $result2 = sql_query($sql);
    $opt_count = @mysql_num_rows($result2);

    if(!$opt_count) {
        $it_name = $row['it_name'];
        $buy_url = G4_SHOP_URL.'/itembuy.php?it_id='.$row['it_id'];
        if($default['de_send_cost_case'] == '개별' && $row['it_sc_method'] != 1)
            $delivery = get_item_sendcost($row['it_id'], $row['it_price'], 1);
        $it_price = $row['it_price'];

        echo "
        <tr>
            <td>$num</td>
            <td>{$row['it_id']}</td>
            <td><a href='".$buy_url."'>{$it_name}</a></td>
            <td>{$it_price}</td>
            <td>$category</td>
            <td>{$row['it_maker']}</td>
            <td>$image</td>
        </tr>
            ";

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
            $buy_url = G4_SHOP_URL.'/itembuy.php?it_id='.$row['it_id'].'&amp;opt='.$row2['io_id'];
            $it_price = $row['it_price'] + $row2['io_price'];
            if($default['de_send_cost_case'] == '개별' && $row['it_sc_method'] != 1)
                $delivery = get_item_sendcost($row['it_id'], ($row['it_price'] + $row2['io_price']), 1);

            echo "
            <tr>
                <td>$num</td>
                <td>{$row['it_id']}</td>
                <td><a href='".$buy_url."'>{$it_name}</a></td>
                <td>{$it_price}</td>
                <td>$category</td>
                <td>{$row['it_maker']}</td>
                <td>$image</td>
            </tr>
                ";

        }
    }
}
?>
<tr>
    <td colspan=7><?php echo paging(1000, $page, $total_page, "./mymargin.php?page="); ?> &nbsp;</td>
</tr>
</table>

</body>
</html>
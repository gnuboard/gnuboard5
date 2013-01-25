<?
$sub_menu = "500120";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

//print_r2($_GET); exit;

/*
function multibyte_digit($source)
{
    $search  = array("0", "1", "2", "3", "4", "5", "6", "7", "8", "9");
    $replace = array("０","１","２","３","４","５","６","７","８","９");
    return str_replace($search, $replace, (string)$source);
}
*/

function conv_telno($t)
{
    // 숫자만 있고 0으로 시작하는 전화번호
    if (!preg_match("/[^0-9]/", $t) && preg_match("/^0/", $t))  {
        if (preg_match("/^01/", $t)) {
            $t = preg_replace("/([0-9]{3})(.*)([0-9]{4})/", "\\1-\\2-\\3", $t);
        } else if (preg_match("/^02/", $t)) {
            $t = preg_replace("/([0-9]{2})(.*)([0-9]{4})/", "\\1-\\2-\\3", $t);
        } else {
            $t = preg_replace("/([0-9]{3})(.*)([0-9]{4})/", "\\1-\\2-\\3", $t);
        }
    }

    return $t;
}

// 1.04.01
// MS엑셀 CSV 데이터로 다운로드 받음
if ($csv == 'csv') 
{
    $fr_date = date_conv($fr_date);
    $to_date = date_conv($to_date);


    $sql = " SELECT od_b_zip1, od_b_zip2, od_b_addr1, od_b_addr2, od_b_name, od_b_tel, od_b_hp, it_name, ct_qty, b.it_id, a.od_id, od_memo, od_invoice
               FROM $g4[yc4_order_table] a, $g4[yc4_cart_table] b, $g4[yc4_item_table] c
              where a.on_uid = b.on_uid
                and b.it_id = c.it_id ";
    if ($case == 1) // 출력기간
        $sql .= " and a.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
    else // 주문번호구간 
        $sql .= " and a.od_id between '$fr_od_id' and '$to_od_id' ";
    if ($ct_status)
        $sql .= " and b.ct_status = '$ct_status' ";
    $sql .="  order by od_time asc ";
    $result = sql_query($sql);
    $cnt = @mysql_num_rows($result);
    if (!$cnt)
        alert("출력할 내역이 없습니다.");

    //header('Content-Type: text/x-csv');
    header("Content-charset=$g4[charset]");
    header('Content-Type: doesn/matter');
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Content-Disposition: attachment; filename="' . date("ymd", time()) . '.csv"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    //echo "우편번호,주소,이름,전화1,전화2,상품명,수량,비고,전하실말씀\n";
    echo "우편번호,주소,이름,전화1,전화2,상품명,수량,상품코드,주문번호,운송장번호,전하실말씀\n";
    for ($i=0; $row=mysql_fetch_array($result); $i++) 
    {
        echo '"' . $row[od_b_zip1] . '-' . $row[od_b_zip2] . '"' . ',';
        echo '"' . $row[od_b_addr1] . ' ' . $row[od_b_addr2] . '"' . ',';
        echo '"' . $row[od_b_name] . '"' . ',';
        //echo '"' . multibyte_digit((string)$row[od_b_tel]) . '"' . ',';
        //echo '"' . multibyte_digit((string)$row[od_b_hp]) . '"' . ',';
        echo '"' . conv_telno($row[od_b_tel]) . '"' . ',';
        echo '"' . conv_telno($row[od_b_hp]) . '"' . ',';
        echo '"' . preg_replace("/\"/", "&#034;", $row[it_name]) . '"' . ',';
        echo '"' . $row[ct_qty] . '"' . ',';
        echo '"\'' . $row[it_id] . '\'"' . ',';
        echo '"\'' . $row[od_id] . '\'"' . ',';
        echo '"' . $row[od_invoice] . '"' . ',';
        //echo '"' . preg_replace("/\"/", "&#034;", preg_replace("/\n/", "", $row[od_memo])) . '"';
        echo '"' . preg_replace("/\"/", "&#034;", $row[od_memo]) . '"';
        echo "\n";
    }
    if ($i == 0)
        echo "자료가 없습니다.\n";

    exit;
}

// MS엑셀 XLS 데이터로 다운로드 받음
if ($csv == 'xls') 
{
    $fr_date = date_conv($fr_date);
    $to_date = date_conv($to_date);


    $sql = " SELECT od_b_zip1, od_b_zip2, od_b_addr1, od_b_addr2, od_b_name, od_b_tel, od_b_hp, it_name, ct_qty, b.it_id, a.od_id, od_memo, od_invoice, b.it_opt1, b.it_opt2, b.it_opt3, b.it_opt4, b.it_opt5, b.it_opt6
               FROM $g4[yc4_order_table] a, $g4[yc4_cart_table] b, $g4[yc4_item_table] c
              where a.on_uid = b.on_uid
                and b.it_id = c.it_id ";
    if ($case == 1) // 출력기간
        $sql .= " and a.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
    else // 주문번호구간 
        $sql .= " and a.od_id between '$fr_od_id' and '$to_od_id' ";
    if ($ct_status)
        $sql .= " and b.ct_status = '$ct_status' ";
    $sql .="  order by od_time asc ";
    $result = sql_query($sql);
    $cnt = @mysql_num_rows($result);
    if (!$cnt)
        alert("출력할 내역이 없습니다.");

    header("Content-charset=$g4[charset]");
    header('Content-Type: application/vnd.ms-excel');
    header('Expires: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Content-Disposition: attachment; filename="' . date("ymd", time()) . '.xls"');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    echo "<html>";
    echo "<head>";
    echo "<title>xls</title>";
    echo "<style>.txt {mso-number-format:'\\@';}</style>"; 
    echo "</head>";
    echo "<body>";
    echo "<table border='1'>";
    echo "<tr>";
    echo "<td>우편번호</td>";
    echo "<td>주소</td>";
    echo "<td>이름</td>";
    echo "<td>전화1</td>";
    echo "<td>전화2</td>";
    echo "<td>상품명</td>";
    echo "<td>수량</td>";
    echo "<td>상품코드</td>";
    echo "<td>주문번호</td>";
    echo "<td>운송장번호</td>";
    echo "<td>전하실말씀</td>";
    echo "</tr>";
    for ($i=0; $row=mysql_fetch_array($result); $i++) 
    {
        $it_name = stripslashes($row[it_name]) . "<br />";
        $it_name .= print_item_options($row[it_id], $row[it_opt1], $row[it_opt2], $row[it_opt3], $row[it_opt4], $row[it_opt5], $row[it_opt6]);

        echo "<tr>";
        echo "<td>" . $row[od_b_zip1] . '-' . $row[od_b_zip2] . "</td>";
        echo "<td>" . $row[od_b_addr1] . ' ' . $row[od_b_addr2] . "</td>";
        echo "<td>" . $row[od_b_name] . "</td>";
        echo "<td class='txt'>" . $row[od_b_tel] . "</td>";
        echo "<td class='txt'>" . $row[od_b_hp] . "</td>";
        echo "<td>" . $it_name . "</td>";
        echo "<td>" . $row[ct_qty] . "</td>";
        echo "<td class='txt'>" . $row[it_id] . "</td>";
        echo "<td class='txt'>'" . urlencode($row[od_id]) . "'</td>";
        echo "<td class='txt'>" . $row[od_invoice] . "</td>";
        echo "<td>" . $row[od_memo] . "</td>";
        echo "</tr>";
    }
    if ($i == 0)
        echo "<tr><td colspan='11'>자료가 없습니다.</td></tr>";
    echo "</table>";
    echo "</body>";
    echo "</html>";

    exit;
}

function get_order($on_uid)
{
	global $g4;

	$sql = " select * from $g4[yc4_order_table] where on_uid = '$on_uid' ";
    return sql_fetch($sql);
}

$g4[title] = "주문내역";
include_once("$g4[path]/head.sub.php");

if ($case == 1) 
{
    $fr_date = date_conv($fr_date);
    $to_date = date_conv($to_date);
    $sql = " SELECT DISTINCT a.on_uid FROM $g4[yc4_order_table] a, $g4[yc4_cart_table] b
              where a.on_uid = b.on_uid
                and a.od_time between '$fr_date 00:00:00' and '$to_date 23:59:59' ";
}
else
{
    $sql = " SELECT DISTINCT a.on_uid FROM $g4[yc4_order_table] a, $g4[yc4_cart_table] b
              where a.on_uid = b.on_uid
                and a.od_id between '$fr_od_id' and '$to_od_id' ";
}
if ($ct_status)
    $sql .= " and b.ct_status = '$ct_status' ";
$sql .= " order by a.od_id ";
$result = sql_query($sql);
if (mysql_num_rows($result) == 0) 
{
    echo "<script>alert('출력할 내역이 없습니다.'); window.close();</script>";
    exit;
}
?>
<html>
<head>
<meta http-equiv="Content-type" content="text/html; charset=<?=$g4['charset']?>">
<title>주문내역</title>
<style>
    body, table, tr, td, p { font-size:9pt; }
</style>
</head>
<body bgcolor=ffffff leftmargin=0 topmargin=0 marginheight=0 marginwidth=0>

<? 
if ($case == 1)
    echo "<p><b>[ $fr_date - $to_date $ct_status 내역 ]</b>";
else
    echo "<p><b>[ $fr_od_id - $to_od_id $ct_status 내역 ]</b>";
?>
<table width=650 cellpadding=2 cellspacing=0 border=0 bordercolordark="white" bordercolorlight="gray">
<tr><td colspan=5><hr></td></tr>
<tr>
    <td rowspan=2 width=70 valign=top align=center>주문번호</td>
    <td width=60>보낸분</td>
    <td>주소</td>
    <td width=100>전화번호</td>
    <td width=100>핸드폰</td>
</tr>
<tr>
    <td>받는분</td>
    <td>주소</td>
    <td>전화번호</td>
    <td>핸드폰</td>
</tr>
<tr><td colspan=5><hr></td></tr>
<?
$mod = 10;
$tot_total_amount = 0;
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    $sql1 = " select * from $g4[yc4_order_table] where on_uid = '$row[on_uid]' ";
    $row1 = sql_fetch($sql1);

    // 1.03.02
    $row1[od_addr] = "(".$row1[od_zip1]."-".$row1[od_zip2].") ".$row1[od_addr1]." ".$row1[od_addr2];
    $row1[od_b_addr] = "(".$row1[od_b_zip1]."-".$row1[od_b_zip2].") ".$row1[od_b_addr1]." ".$row1[od_b_addr2];

    $row1[od_addr] = ($row1[od_addr]) ? $row1[od_addr] : "&nbsp;";
    $row1[od_tel] = ($row1[od_tel]) ? $row1[od_tel] : "&nbsp;";
    $row1[od_hp]  = ($row1[od_hp]) ? $row1[od_hp] : "&nbsp;";
    $row1[od_b_tel] = ($row1[od_b_tel]) ? $row1[od_b_tel] : "&nbsp;";
    $row1[od_b_hp]  = ($row1[od_b_hp]) ? $row1[od_b_hp] : "&nbsp;";

    if ($row1[od_name] == $row1[od_b_name]) $row1[od_b_name] = '"';
    if ($row1[od_addr] == $row1[od_b_addr]) $row1[od_b_addr] = '"';
    if ($row1[od_tel] == $row1[od_b_tel]) $row1[od_b_tel] = '"';
    if ($row1[od_hp] == $row1[od_b_hp] && $row1[od_hp] != "&nbsp;") $row1[od_b_hp] = '"';

    $od_memo = ($row1[od_memo]) ? stripslashes($row1[od_memo]) : "";
    $od_shop_memo = ($row1[od_shop_memo]) ? stripslashes($row1[od_shop_memo]) : "";

    echo "
        <tr>
            <td rowspan=3 align=center valign=top>$row1[od_id]</td>
            <td>$row1[od_name]</td>
            <td>$row1[od_addr]</td>
            <td>$row1[od_tel]</td>
            <td>$row1[od_hp]</td>
        </tr>
        <tr>
            <td>$row1[od_b_name]</td>
            <td>$row1[od_b_addr]</td>
            <td>$row1[od_b_tel]</td>
            <td>$row1[od_b_hp]</td>
        </tr>
        <tr>    
            <td colspan=4>
                <table width=100% cellpadding=2 cellspacing=0 border=1 bordercolordark='white' bordercolorlight='gray'>
    ";

    $sql2 = " select    a.*,                                 
                        b.it_opt1_subject,
                        b.it_opt2_subject,
                        b.it_opt3_subject,
                        b.it_opt4_subject,
                        b.it_opt5_subject,
                        b.it_opt6_subject,
                        b.it_name
                from $g4[yc4_cart_table] a, $g4[yc4_item_table] b
               where a.it_id = b.it_id
                 and a.on_uid = '$row[on_uid]' ";
    if ($ct_status)
        $sql2 .= " and a.ct_status = '$ct_status' ";
    $sql2 .= "  order by a.ct_id ";

    $res2 = sql_query($sql2);
    $cnt = $sub_tot_qty = $sub_tot_amount = 0;
    while ($row2 = sql_fetch_array($res2)) 
    {
        $row2_tot_amount = $row2[ct_amount] * $row2[ct_qty];
        $sub_tot_qty    += $row2[ct_qty];
        $sub_tot_amount += $row2_tot_amount;

        $it_name = stripslashes($row2[it_name]);
        $it_name = "$it_name ($row2[it_id])<br><font color=#555555>";

        $str_split = "";
        for ($k=1; $k<=6; $k++)
        {
            if ($row2["it_opt{$k}"] == "") continue;
            $it_name .= $str_split;
            $it_opt_subject = $row2["it_opt{$k}_subject"];
            $opt = explode( ";", trim($row2["it_opt{$k}"]) );
            $it_name .= "&nbsp;&nbsp; $it_opt_subject = $opt[0]";

            if ($opt[1] != 0)
            {
                $it_name .= " (";
                //if (ereg("[+]", $opt[1]) == true)
                if (preg_match("/[+]/", $opt[1]) == true)
                    $it_name .= "+";
                // 금액을 전화문의 표시로
                $it_name .= display_amount($opt[1]) . ")";
            }
            $str_split = "<br>";
        }
        $it_name .= "</font>";

        $fontqty1 = $fontqty2 = "";
        if ($row2[ct_qty] >= 2) 
        {
            $fontqty1 = "<font color=crimson><b>";
            $fontqty2 = "</b></font>";
        }

        echo "
            <tr>
                <td>$it_name</td>
                <td width=80 align=right>".number_format($row2[ct_amount])."&nbsp;</td>
                <td width=50 align=center>$fontqty1".number_format($row2[ct_qty])."$fontqty2</td>
                <td width=80 align=right>".number_format($row2_tot_amount)."&nbsp;</td>
            </tr>
        ";
        $cnt++;
    }

    if ($cnt >= 2) 
    {
        echo "
        <tr>
            <td colspan=2 align=right><b>합 계</b> &nbsp;</td>
            <td align=center>".number_format($sub_tot_qty)."</td>
            <td align=right>".number_format($sub_tot_amount)."&nbsp;</td>
        </tr>";
    }

    $tot_tot_qty    += $sub_tot_qty;
    $tot_tot_amount += $sub_tot_amount;

    if ($od_memo) $od_memo = "<font color=crimson>비고 : $od_memo</font>";
    if ($od_shop_memo) $od_shop_memo = "<br/><font color=crimson>상점메모 : $od_shop_memo</font>";

    echo " 
            </table>
            $od_memo
            $od_shop_memo
        </td>
    </tr>
    <tr><td colspan=5><hr></td></tr>";
}
?>
<tr>
    <td></td>
    <td colspan=4>
        <table width=100% cellpadding=2 cellspacing=0 border=1 bordercolordark='white' bordercolorlight='gray'>
        <tr>
        <?
        echo "
            <td colspan=2 align=right><b>전 체 합 계</b> &nbsp;</td>
            <td align=center width=50>".number_format($tot_tot_qty)."</td>
            <td align=right width=80>".number_format($tot_tot_amount)."&nbsp;</td>
        ";
        ?>
        </tr>
        </table>
    </td>
</tr>
</table>

<br>&lt;끝&gt;

</body>
</html>
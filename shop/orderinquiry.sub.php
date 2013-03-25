<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined("_ORDERINQUIRY_")) exit; // 개별 페이지 접근 불가
?>

<table width=98% align=center cellpadding=0 cellspacing=0 border=0>
<colgroup width=100>
<colgroup width=''>
<colgroup width=80>
<colgroup width=120>
<colgroup width=120>
<colgroup width=120>
<? if (!$limit) { echo "<tr><td colspan=6 align=right>총 {$cnt} 건</td></tr>"; } ?>
<tr><td height=2 colspan=6 class=c1></td></tr>
<tr align=center height=28 class=c2>
    <td>주문서번호</td>
    <td>주문일시</td>
    <td>상품수</td>
    <td>주문금액</td>
    <td>입금액</td>
    <td>미입금액</td>
</tr>
<tr><td height=1 colspan=6 class=c1></td></tr>
<?
$sql = " select a.od_id,
                a.*, "._MISU_QUERY_."
           from {$g4['yc4_order_table']} a
           left join {$g4['yc4_cart_table']} b on (b.uq_id=a.uq_id)
          where mb_id = '{$member['mb_id']}'
          group by a.od_id
          order by a.od_id desc
          $limit ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ($i > 0)
        echo "<tr><td colspan=6 height=1 background='".G4_SHOP_URL."/img/dot_line.gif'></td></tr>\n";

    echo "<tr height=28>\n";
    echo "<td align=center>";
    echo "<input type=hidden name='ct_id[$i]' value='{$row['ct_id']}'>\n";
    echo "<a href='./orderinquiryview.php?od_id={$row['od_id']}&uq_id={$row['uq_id']}'><U>{$row['od_id']}</U></a></td>\n";
    echo "<td align=center>".substr($row['od_time'],0,16)." (".get_yoil($row['od_time']).")</td>\n";
    echo "<td align=center>$row[itemcount]</td>\n";
    echo "<td align=right>".display_amount($row['orderamount'])."&nbsp;&nbsp;</td>\n";
    echo "<td align=right>".display_amount($row['receiptamount'])."&nbsp;&nbsp;</td>\n";
    echo "<td align=right>".display_amount($row['misu'])."&nbsp;&nbsp;</td>\n";
    echo "</tr>\n";
}

if ($i == 0)
    echo "<tr><td colspan=20 height=100 align=center><span class=point>주문 내역이 없습니다.</span></td></tr>";
?>
<tr><td colspan=20 height=1 bgcolor=#94D7E7></td></tr>
</table><br>

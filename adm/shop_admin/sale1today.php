<?
$sub_menu = "500110";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $date);

$g4[title] = "$date 매출현황";
include_once ("$g4[admin_path]/admin.head.php");
?>

<?=subtitle($g4[title])?>

<table cellpadding=0 cellspacing=0 width=100%>
<tr><td colspan=9 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td width=100 align=center>주문번호</td>
    <td align=center>주문자</td>
    <td width=90 align=right>주문합계</td>
    <td width=90 align=right>취소+DC</td>
    <td width=90 align=right>무통장입금</td>
    <td width=90 align=right>카드입금</td>
    <td width=90 align=right>포인트입금</td>
    <td width=90 align=right>입금취소</td>
    <td width=90 align=right>미수금</td>
</tr>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
<?
unset($tot);
$sql = " select od_id,
                mb_id, 
                od_name, 
                on_uid,
                od_send_cost,
                od_receipt_bank,
                od_receipt_card,
                od_receipt_point,
                od_dc_amount,
                (od_receipt_bank + od_receipt_card + od_receipt_point) as receiptamount,
                (od_refund_amount + od_cancel_card) as receiptcancel
           from $g4[yc4_order_table] 
          where SUBSTRING(od_time,1,10) = '$date' 
          order by od_id desc ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    if ($i > 0)
        echo "<tr><td colspan=9 height=1 bgcolor=#EEEEEE></td></tr>\n";

    // 장바구니 상태별 금액
    $sql1 = " select (SUM(ct_amount * ct_qty)) as orderamount, /* 주문합계 */
                     (SUM(IF(ct_status = '취소' OR ct_status = '반품' OR ct_status = '품절', ct_amount * ct_qty, 0))) as ordercancel /* 주문취소 */
                from $g4[yc4_cart_table]
               where on_uid = '$row[on_uid]' ";
    $row1 = sql_fetch($sql1);

    if ($row[mb_id] == "") { // 비회원일 경우는 주문자로 링크
        $href = "<a href='./orderlist.php?sel_field=od_name&search=$row[od_name]'>";
    } else { // 회원일 경우는 회원아이디로 링크
        $href = "<a href='./orderlist.php?sel_field=mb_id&search=$row[mb_id]'>";
    }

    $row1[orderamount] += $row[od_send_cost];
    $misu = $row1[orderamount] - $row1[ordercancel] - $row[od_dc_amount] - $row[receiptamount] + $row[receiptcancel];

    echo "
    <tr class=ht>
        <td align=center><a href='./orderform.php?od_id=$row[od_id]'>$row[od_id]</a></td>
        <td align=center>$href$row[od_name]</a></td>
        <td align=right>".number_format($row1[orderamount])."</td>
        <td align=right>".number_format($row1[ordercancel] + $row[od_dc_amount])."</td>
        <td align=right>".number_format($row[od_receipt_bank])."</td>
        <td align=right>".number_format($row[od_receipt_card])."</td>
        <td align=right>".number_format($row[od_receipt_point])."</td>
        <td align=right>".number_format($row[receiptcancel])."</td>
        <td align=right>".number_format($misu)."</td>
    </tr>\n";

    $tot[orderamount]   += $row1[orderamount];
    $tot[ordercancel]   += $row1[ordercancel];
    $tot[dc]            += $row[od_dc_amount];
    $tot[receipt_bank]  += $row[od_receipt_bank];
    $tot[receipt_card]  += $row[od_receipt_card];
    $tot[receipt_point] += $row[od_receipt_point];
    $tot[receiptamount] += $row[receiptamount];
    $tot[receiptcancel] += $row[receiptcancel];
    $tot[misu]          += $misu;
}

if ($i == 0) {
    echo "<tr><td colspan=9 align=center height=100 bgcolor=#FFFFFF><span class=point>자료가 한건도 없습니다.</span></td></tr>";
}
?>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
<tr class=ht>
    <td align=center colspan=2>합 계</td>
    <td align=right><?=number_format($tot[orderamount])?></td>
    <td align=right><?=number_format($tot[ordercancel]+ $tot[dc])?></td>
    <td align=right><?=number_format($tot[receipt_bank])?></td>
    <td align=right><?=number_format($tot[receipt_card])?></td>
    <td align=right><?=number_format($tot[receipt_point])?></td>
    <td align=right><?=number_format($tot[receiptcancel])?></td>
    <td align=right><?=number_format($tot[misu])?></td>
</tr>
<tr><td colspan=9 height=1 bgcolor=#CCCCCC></td></tr>
</table>

<?
include_once ("$g4[admin_path]/admin.tail.php");
?>

<?php
$sub_menu = '500110';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $date);

$g4['title'] = "$date 일 매출현황";
include_once (G4_ADMIN_PATH.'/admin.head.php');

unset($tot);
$lines = $lines1 = array();
$sql = " select od_id,
                mb_id,
                od_name,
                uq_id,
                od_send_cost,
                od_receipt_bank,
                od_receipt_card,
                od_receipt_point,
                od_dc_amount,
                (od_receipt_bank + od_receipt_card + od_receipt_point) as receiptamount,
                (od_refund_amount + od_cancel_card) as receiptcancel
           from {$g4['shop_order_table']}
          where SUBSTRING(od_time,1,10) = '$date'
          order by od_id desc ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $lines[$i] = $row;

    // 장바구니 상태별 금액
    $sql1 = " select (SUM(IF(io_type = 1, io_price * ct_qty, (ct_price + io_price) * ct_qty))) as orderamount, /* 주문합계 */
                     (SUM(IF(ct_status = '취소' OR ct_status = '반품' OR ct_status = '품절', IF(io_type = 1, io_price * ct_qty, (ct_price + io_price) * ct_qty), 0))) as ordercancel /* 주문취소 */
                from {$g4['shop_cart_table']}
               where uq_id = '{$row['uq_id']}' ";
    $row1 = sql_fetch($sql1);

    $row1['orderamount'] += $row['od_send_cost'];
    $misu = $row1['orderamount'] - $row1['ordercancel'] - $row['od_dc_amount'] - $row['receiptamount'] + $row['receiptcancel'];
    $lines1[$i] = $row1;

    $tot['orderamount']   += $row1['orderamount'];
    $tot['ordercancel']   += $row1['ordercancel'];
    $tot['dc']            += $row['od_dc_amount'];
    $tot['receipt_bank']  += $row['od_receipt_bank'];
    $tot['receipt_card']  += $row['od_receipt_card'];
    $tot['receipt_point'] += $row['od_receipt_point'];
    $tot['receiptamount'] += $row['receiptamount'];
    $tot['receiptcancel'] += $row['receiptcancel'];
    $tot['misu']          += $misu;
}
?>

<section class="cbox">
    <h2>일일 매출현황</h2>

    <table>
    <thead>
    <tr>
        <th scope="col">주문번호</th>
        <th scope="col">주문자</th>
        <th scope="col">주문합계</th>
        <th scope="col">취소+DC</th>
        <th scope="col">무통장</th>
        <th scope="col">카드</th>
        <th scope="col">포인트</th>
        <th scope="col">입금취소</th>
        <th scope="col">미수금</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="2">합 계</td>
        <td><?php echo number_format($tot['orderamount']); ?></td>
        <td><?php echo number_format($tot['ordercancel']+ $tot['dc']); ?></td>
        <td><?php echo number_format($tot['receipt_bank']); ?></td>
        <td><?php echo number_format($tot['receipt_card']); ?></td>
        <td><?php echo number_format($tot['receipt_point']); ?></td>
        <td><?php echo number_format($tot['receiptcancel']); ?></td>
        <td><?php echo number_format($tot['misu']); ?></td>
    </tr>
    </tfoot>
    <tbody>
    <?php
    unset($tot);
    for ($i=0; $i<count($lines); $i++)
    {
        if ($row['mb_id'] == '') { // 비회원일 경우는 주문자로 링크
            $href = '<a href="./orderlist.php?sel_field=od_name&amp;search='.$lines[$i]['od_name'].'">';
        } else { // 회원일 경우는 회원아이디로 링크
            $href = '<a href="./orderlist.php?sel_field=mb_id&amp;search='.$lines[$i]['mb_id'].'">';
        }

        $misu = $lines1[$i]['orderamount'] - $lines1[$i]['ordercancel'] - $lines[$i]['od_dc_amount'] - $lines[$i]['receiptamount'] + $lines[$i]['receiptcancel'];

    ?>
        <tr>
            <td class="td_odrnum2"><a href="./orderform.php?od_id=<?php echo $lines[$i]['od_id']; ?>"><?php echo $lines[$i]['od_id']; ?></a></td>
            <td class="td_name"><?php echo $href; ?><?php echo $lines[$i]['od_name']; ?></a></td>
            <td class="td_num"><?php echo number_format($lines1[$i]['orderamount']); ?></td>
            <td class="td_num"><?php echo number_format($lines1[$i]['ordercancel'] + $lines[$i]['od_dc_amount']); ?></td>
            <td class="td_num"><?php echo number_format($lines[$i]['od_receipt_bank']); ?></td>
            <td class="td_num"><?php echo number_format($lines[$i]['od_receipt_card']); ?></td>
            <td class="td_num"><?php echo number_format($lines[$i]['od_receipt_point']); ?></td>
            <td class="td_num"><?php echo number_format($lines[$i]['receiptcancel']); ?></td>
            <td class="td_num"><?php echo number_format($misu); ?></td>
        </tr>
    <?php
    }

    if ($i == 0) {
        echo '<tr><td colspan="9" class="empty_table">자료가 없습니다</td></tr>';
    }
    ?>
    </tbody>
    </table>
</section>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>

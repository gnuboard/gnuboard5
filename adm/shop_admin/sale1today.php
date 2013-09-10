<?php
$sub_menu = '500110';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $date);

$g4['title'] = "$date 일 매출현황";
include_once (G4_ADMIN_PATH.'/admin.head.php');

$sql = " select od_id,
                mb_id,
                od_name,
                od_settle_case,
                od_cart_amount,
                od_receipt_amount,
                od_receipt_point,
                od_cancel_amount,
                (od_cart_amount + od_send_cost + od_send_cost2 - od_cart_coupon - od_coupon - od_send_coupon - od_receipt_amount - od_cancel_amount) as misu,
                (od_cart_coupon + od_coupon + od_send_coupon) as couponamount
           from {$g4['shop_order_table']}
          where SUBSTRING(od_time,1,10) = '$date'
          order by od_id desc ";
$result = sql_query($sql);
?>

<section class="cbox">
    <h2>일일 매출현황</h2>

    <table>
    <thead>
    <tr>
        <th scope="col">주문번호</th>
        <th scope="col">주문자</th>
        <th scope="col">주문합계</th>
        <th scope="col">쿠폰</th>
        <th scope="col">계좌입금</th>
        <th scope="col">카드</th>
        <th scope="col">포인트</th>
        <th scope="col">주문취소</th>
        <th scope="col">미수금</th>
    </tr>
    </thead>
    <tbody>
    <?php
    unset($tot);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        if ($row['mb_id'] == '') { // 비회원일 경우는 주문자로 링크
            $href = '<a href="./orderlist.php?sel_field=od_name&amp;search='.$row['od_name'].'">';
        } else { // 회원일 경우는 회원아이디로 링크
            $href = '<a href="./orderlist.php?sel_field=mb_id&amp;search='.$row['mb_id'].'">';
        }

        $misu = $row['misu'];

        $receipt_bank = $receipt_card = 0;
        if($row['od_settle_case'] == '무통장' || $row['od_settle_case'] == '가상계좌' || $row['od_settle_case'] == '계좌이체')
            $receipt_bank = $row['od_receipt_amount'];
        if($row['od_settle_case'] == '신용카드')
            $receipt_card = $row['od_receipt_amount'];

    ?>
        <tr>
            <td class="td_odrnum2"><a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>"><?php echo $row['od_id']; ?></a></td>
            <td class="td_name"><?php echo $href; ?><?php echo $row['od_name']; ?></a></td>
            <td class="td_num"><?php echo number_format($row['od_cart_amount']); ?></td>
            <td class="td_num"><?php echo number_format($row['couponamount']); ?></td>
            <td class="td_num"><?php echo number_format($receipt_bank); ?></td>
            <td class="td_num"><?php echo number_format($receipt_card); ?></td>
            <td class="td_num"><?php echo number_format($row['od_receipt_point']); ?></td>
            <td class="td_num"><?php echo number_format($row['od_cancel_amount']); ?></td>
            <td class="td_num"><?php echo number_format($misu); ?></td>
        </tr>
    <?php
        $tot['orderamount']   += $row['od_cart_amount'];
        $tot['ordercancel']   += $row1['od_cancel_amount'];
        $tot['coupon']        += $row['couponamount'] ;
        if($row['od_settle_case'] == '무통장' || $row['od_settle_case'] == '가상계좌' || $row['od_settle_case'] == '계좌이체')
            $tot['receipt_bank']  += $row['od_receipt_amount'];
        if($row['od_settle_case'] == '신용카드')
            $tot['receipt_card']  += $row['od_receipt_amount'];
        $tot['receipt_point'] += $row['od_receipt_point'];
        $tot['misu']          += $misu;
    }

    if ($i == 0) {
        echo '<tr><td colspan="9" class="empty_table">자료가 없습니다</td></tr>';
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2">합 계</td>
        <td><?php echo number_format($tot['orderamount']); ?></td>
        <td><?php echo number_format($tot['coupon']); ?></td>
        <td><?php echo number_format($tot['receipt_bank']); ?></td>
        <td><?php echo number_format($tot['receipt_card']); ?></td>
        <td><?php echo number_format($tot['receipt_point']); ?></td>
        <td><?php echo number_format($tot['ordercancel']); ?></td>
        <td><?php echo number_format($tot['misu']); ?></td>
    </tr>
    </tfoot>
    </table>
</section>

<?php
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>

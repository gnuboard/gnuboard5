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
                od_cart_price,
                od_receipt_price,
                od_receipt_point,
                od_cancel_price,
                od_misu,
                (od_cart_price + od_send_cost + od_send_cost2) as orderprice,
                (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
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

        $receipt_bank = $receipt_card = 0;
        if($row['od_settle_case'] == '무통장' || $row['od_settle_case'] == '가상계좌' || $row['od_settle_case'] == '계좌이체')
            $receipt_bank = $row['od_receipt_price'];
        if($row['od_settle_case'] == '신용카드')
            $receipt_card = $row['od_receipt_price'];

    ?>
        <tr>
            <td class="td_odrnum2"><a href="./orderform.php?od_id=<?php echo $row['od_id']; ?>"><?php echo $row['od_id']; ?></a></td>
            <td class="td_name"><?php echo $href; ?><?php echo $row['od_name']; ?></a></td>
            <td class="td_num"><?php echo number_format($row['orderprice']); ?></td>
            <td class="td_num"><?php echo number_format($row['couponprice']); ?></td>
            <td class="td_num"><?php echo number_format($receipt_bank); ?></td>
            <td class="td_num"><?php echo number_format($receipt_card); ?></td>
            <td class="td_num"><?php echo number_format($row['od_receipt_point']); ?></td>
            <td class="td_num"><?php echo number_format($row['od_cancel_price']); ?></td>
            <td class="td_num"><?php echo number_format($row['od_misu']); ?></td>
        </tr>
    <?php
        $tot['orderprice']    += $row['orderprice'];
        $tot['ordercancel']   += $row['od_cancel_price'];
        $tot['coupon']        += $row['couponprice'] ;
        if($row['od_settle_case'] == '무통장' || $row['od_settle_case'] == '가상계좌' || $row['od_settle_case'] == '계좌이체')
            $tot['receipt_bank']  += $row['od_receipt_price'];
        if($row['od_settle_case'] == '신용카드')
            $tot['receipt_card']  += $row['od_receipt_price'];
        $tot['receipt_point'] += $row['od_receipt_point'];
        $tot['misu']          += $row['od_misu'];
    }

    if ($i == 0) {
        echo '<tr><td colspan="9" class="empty_table">자료가 없습니다</td></tr>';
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td colspan="2">합 계</td>
        <td><?php echo number_format($tot['orderprice']); ?></td>
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

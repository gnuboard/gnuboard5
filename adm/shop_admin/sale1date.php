<?php
$sub_menu = '500110';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "r");

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :_\-]/i', '', $_REQUEST['fr_date']) : '';
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :_\-]/i', '', $_REQUEST['to_date']) : '';

$fr_date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $fr_date);
$to_date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $to_date);

$g5['title'] = $fr_date.' ~ '.$to_date.' 일간 매출현황';
include_once (G5_ADMIN_PATH.'/admin.head.php');

function print_line($save)
{
    $date = preg_replace("/-/", "", $save['od_date']);

    ?>
    <tr>
        <td class="td_alignc"><a href="./sale1today.php?date=<?php echo $date; ?>"><?php echo $save['od_date']; ?></a></td>
        <td class="td_num"><?php echo number_format($save['ordercount']); ?></td>
        <td class="td_numsum"><?php echo number_format($save['orderprice']); ?></td>
        <td class="td_numcoupon"><?php echo number_format($save['ordercoupon']); ?></td>
        <td class="td_numincome"><?php echo number_format($save['receiptbank']); ?></td>
        <td class="td_numincome"><?php echo number_format($save['receiptvbank']); ?></td>
        <td class="td_numincome"><?php echo number_format($save['receiptiche']); ?></td>
        <td class="td_numincome"><?php echo number_format($save['receiptcard']); ?></td>
        <td class="td_numincome"><?php echo number_format($save['receipteasy']); ?></td>
        <td class="td_numincome"><?php echo number_format($save['receipthp']); ?></td>
        <td class="td_numincome"><?php echo number_format($save['receiptpoint']); ?></td>
        <td class="td_numcancel1"><?php echo number_format($save['ordercancel']); ?></td>
        <td class="td_numrdy"><?php echo number_format($save['misu']); ?></td>
    </tr>
    <?php
}

$sql = " select od_id,
            SUBSTRING(od_time,1,10) as od_date,
            od_settle_case,
            od_receipt_price,
            od_receipt_point,
            od_cart_price,
            od_cancel_price,
            od_misu,
            (od_cart_price + od_send_cost + od_send_cost2) as orderprice,
            (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
       from {$g5['g5_shop_order_table']}
      where SUBSTRING(od_time,1,10) between '$fr_date' and '$to_date'
      order by od_time desc ";
$result = sql_query($sql);
?>

<div class="tbl_head01 tbl_wrap">

    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <thead>
    <tr>
        <th scope="col">주문일</th>
        <th scope="col">주문수</th>
        <th scope="col">주문합계</th>
        <th scope="col">쿠폰</th>
        <th scope="col">무통장</th>
        <th scope="col">가상계좌</th>
        <th scope="col">계좌이체</th>
        <th scope="col">카드입금</th>
        <th scope="col">간편결제</th>
        <th scope="col">휴대폰</th>
        <th scope="col">포인트입금</th>
        <th scope="col">주문취소</th>
        <th scope="col">미수금</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $save = array('ordercount'=>0, 'orderprice'=>0, 'ordercancel'=>0, 'ordercoupon'=>0, 'receiptbank'=>0, 'receiptvbank'=>0, 'receiptiche'=>0, 'receipthp'=>0, 'receiptcard'=>0, 'receiptpoint'=>0, 'misu'=>0, 'receipteasy'=>0);
    $tot = array('ordercount'=>0, 'orderprice'=>0, 'ordercancel'=>0, 'ordercoupon'=>0, 'receiptbank'=>0, 'receiptvbank'=>0, 'receiptiche'=>0, 'receipthp'=>0, 'receiptcard'=>0, 'receiptpoint'=>0, 'misu'=>0, 'receipteasy'=>0);

    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        if ($i == 0)
            $save['od_date'] = $row['od_date'];

        if ($save['od_date'] != $row['od_date']) {
            print_line($save);
            $save = array('ordercount'=>0, 'orderprice'=>0, 'ordercancel'=>0, 'ordercoupon'=>0, 'receiptbank'=>0, 'receiptvbank'=>0, 'receiptiche'=>0, 'receipthp'=>0, 'receiptcard'=>0, 'receiptpoint'=>0, 'misu'=>0, 'receipteasy'=>0);
            $save['od_date'] = $row['od_date'];
        }

        $save['ordercount']++;
        $save['orderprice']    += $row['orderprice'];
        $save['ordercancel']   += $row['od_cancel_price'];
        $save['ordercoupon']   += $row['couponprice'];
        if($row['od_settle_case'] == '무통장')
            $save['receiptbank']   += $row['od_receipt_price'];
        if($row['od_settle_case'] == '가상계좌')
            $save['receiptvbank']   += $row['od_receipt_price'];
        if($row['od_settle_case'] == '계좌이체')
            $save['receiptiche']   += $row['od_receipt_price'];
        if($row['od_settle_case'] == '휴대폰')
            $save['receipthp']   += $row['od_receipt_price'];
        if($row['od_settle_case'] == '신용카드')
            $save['receiptcard']   += $row['od_receipt_price'];
        $save['receiptpoint']  += $row['od_receipt_point'];
        $save['misu']          += $row['od_misu'];

        $tot['ordercount']++;
        $tot['orderprice']     += $row['orderprice'];
        $tot['ordercancel']    += $row['od_cancel_price'];
        $tot['ordercoupon']    += $row['couponprice'];
        if($row['od_settle_case'] == '무통장')
            $tot['receiptbank']    += $row['od_receipt_price'];
        if($row['od_settle_case'] == '가상계좌')
            $tot['receiptvbank']    += $row['od_receipt_price'];
        if($row['od_settle_case'] == '계좌이체')
            $tot['receiptiche']    += $row['od_receipt_price'];
        if($row['od_settle_case'] == '휴대폰')
            $tot['receipthp']    += $row['od_receipt_price'];
        if($row['od_settle_case'] == '신용카드')
            $tot['receiptcard']    += $row['od_receipt_price'];
        $tot['receiptpoint']  += $row['od_receipt_point'];
        $tot['misu']           += $row['od_misu'];

        if(in_array($row['od_settle_case'], array('간편결제', 'KAKAOPAY', 'lpay', 'inicis_payco', 'inicis_kakaopay', '삼성페이'))) {
            $save['receipteasy'] += $row['od_receipt_price'];
            $tot['receipteasy'] += $row['od_receipt_price'];
        }
    }

    if ($i == 0) {
        echo '<tr><td colspan="13" class="empty_table">자료가 없습니다.</td></tr>';
    } else {
        print_line($save);
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <td>합계</td>
        <td class="td_num_right"><?php echo number_format($tot['ordercount']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['orderprice']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['ordercoupon']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['receiptbank']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['receiptvbank']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['receiptiche']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['receiptcard']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['receipteasy']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['receipthp']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['receiptpoint']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['ordercancel']); ?></td>
        <td class="td_num_right"><?php echo number_format($tot['misu']); ?></td>
    </tr>
    </tfoot>
    </table>
</div>

<?php
include_once (G5_ADMIN_PATH.'/admin.tail.php');
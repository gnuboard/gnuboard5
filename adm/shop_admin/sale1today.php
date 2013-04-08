<?
$sub_menu = '500110';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$date = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3", $date);

$g4['title'] = "$date 매출현황";
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
           from {$g4['yc4_order_table']}
          where SUBSTRING(od_time,1,10) = '$date'
          order by od_id desc ";
$result = sql_query($sql);
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    $lines[$i] = $row;

    // 장바구니 상태별 금액
    $sql1 = " select (SUM(ct_amount * ct_qty)) as orderamount, /* 주문합계 */
                     (SUM(IF(ct_status = '취소' OR ct_status = '반품' OR ct_status = '품절', ct_amount * ct_qty, 0))) as ordercancel /* 주문취소 */
                from {$g4['yc4_cart_table']}
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
<style type="text/css">
    .sale1{text-align:center}
</style>

<section class="cbox">
    <h2>당일 매출현황</h2>
    <table>
    <colgroup>
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
        <col class="grid_2">
    </colgroup>
    <thead>
    <tr>
        <th scope="col" class="sale1">주문번호</th>
        <th scope="col" class="sale1">주문자</th>
        <th scope="col">주문합계</th>
        <th scope="col">취소+DC</th>
        <th scope="col">무통장입금</th>
        <th scope="col">카드입금</th>
        <th scope="col">포인트입금</th>
        <th scope="col">입금취소</th>
        <th scope="col">미수금</th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <td colspan="2">합 계</td>
        <td><?=number_format($tot['orderamount'])?></td>
        <td><?=number_format($tot['ordercancel']+ $tot['dc'])?></td>
        <td><?=number_format($tot['receipt_bank'])?></td>
        <td><?=number_format($tot['receipt_card'])?></td>
        <td><?=number_format($tot['receipt_point'])?></td>
        <td><?=number_format($tot['receiptcancel'])?></td>
        <td><?=number_format($tot['misu'])?></td>
    </tr>
    </tfoot>
    <tbody>
    <?
    unset($tot);
    for ($i=0; $i<count($lines); $i++)
    {
        if ($i > 0)
            echo '<tr><td colspan="9"></td></tr>';

        if ($row['mb_id'] == '') { // 비회원일 경우는 주문자로 링크
            $href = '<a href="./orderlist.php?sel_field=od_name&search='.$lines[$i]['od_name'].'">';
        } else { // 회원일 경우는 회원아이디로 링크
            $href = '<a href="./orderlist.php?sel_field=mb_id&search='.$lines[$i]['mb_id'].'">';
        }

        $misu = $lines1[$i]['orderamount'] - $lines1[$i]['ordercancel'] - $lines[$i]['od_dc_amount'] - $lines[$i]['receiptamount'] + $lines[$i]['receiptcancel'];

        ?>
        <tr class="sale1">
            <td><a href="./orderform.php?od_id=<?=$lines[$i]['od_id']?>"><?=$lines[$i]['od_id']?></a></td>
            <td ><?=$href?><?=$lines[$i]['od_name']?></a></td>
            <td><?=number_format($lines1[$i]['orderamount'])?></td>
            <td><?=number_format($lines1[$i]['ordercancel'] + $lines[$i]['od_dc_amount'])?></td>
            <td><?=number_format($lines[$i]['od_receipt_bank'])?></td>
            <td><?=number_format($lines[$i]['od_receipt_card'])?></td>
            <td><?=number_format($lines[$i]['od_receipt_point'])?></td>
            <td><?=number_format($lines[$i]['receiptcancel'])?></td>
            <td><?=number_format($misu)?></td>
        </tr>
    <?
    }

    if ($i == 0) {
        echo '<tr><td colspan="9" class="sale1"><span>자료가 한건도 없습니다.</span></td></tr>';
    }
    ?>
    </tbody>
    </table>
</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>

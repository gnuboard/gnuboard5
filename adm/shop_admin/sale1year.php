<?
$sub_menu = '500110';
include_once('./_common.php');

auth_check($auth[$sub_menu], "r");

$g4['title'] = "$fr_year ~ $to_year 매출현황"; /*레이블 중복 인식과 페이지와의 연결 때문에 year로 바꿈 김혜련 2013-04-04*/
include_once (G4_ADMIN_PATH.'/admin.head.php');

function print_line($save)
{
    global $admin_dir;
    static $count = 0;

    if ($count++ > 0)
        echo '<tr><td colspan="9"></td></tr>\n';

    ?>
    <tr class="sale1">
        <td><a href="./sale1month.php?fr_month=<?=$save['od_date']?>01&to_month=<?=$save['od_date']?>12"><?=$save['od_date']?></a></td>
        <td><?=number_format($save['ordercount'])?></td>
        <td><?=number_format($save['orderamount'])?></td>
        <td><?=number_format($save['ordercancel'] + $save['dc'])?></td>
        <td><?=number_format($save['receiptbank'])?></td>
        <td><?=number_format($save['receiptcard'])?></td>
        <td><?=number_format($save['receiptpoint'])?></td>
        <td><?=number_format($save['receiptcancel'])?></td>
        <td><?=number_format($save['misu'])?></td>
    </tr>
    <?
}
?>

<style type="text/css">
    .sale1{text-align:center}
</style>
<section class="cbox">
    <h2>연별 매출현황</h2>
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
        <th scope="col">주문년도</th>
        <th scope="col">주문수</th>
        <th scope="col">주문합계</th>
        <th scope="col">취소+DC</th>
        <th scope="col">무통장입금</th>
        <th scope="col">카드입금</th>
        <th scope="col">포인트입금</th>
        <th scope="col">입금취소</th>
        <th scope="col">미수금</th>
    </tr>
    </thead>
    <tbody>
    <?
    unset($save);
    unset($tot);
    $sql = " select uq_id,
                    SUBSTRING(od_time,1,4) as od_date,
                    od_send_cost,
                    od_receipt_bank,
                    od_receipt_card,
                    od_receipt_point,
                    od_dc_amount,
                    (od_receipt_bank + od_receipt_card + od_receipt_point) as receiptamount,
                    (od_refund_amount + od_cancel_card) as receiptcancel
               from {$g4['yc4_order_table']}
              where SUBSTRING(od_time,1,4) between '$fr_year' and '$to_year'
              order by od_time desc ";
    $result = sql_query($sql);
    for ($i=0; $row=mysql_fetch_array($result); $i++)
    {
        if ($i == 0)
            $save['od_date'] = $row['od_date'];

        if ($save['od_date'] != $row['od_date']) {
            print_line($save);
            unset($save);
            $save['od_date'] = $row['od_date'];
        }

        // 장바구니 상태별 금액
        $sql1 = " select (SUM(ct_amount * ct_qty)) as orderamount, /* 주문합계 */
                         (SUM(IF(ct_status = '취소' OR ct_status = '반품' OR ct_status = '품절', ct_amount * ct_qty, 0))) as ordercancel /* 주문취소 */
                    from {$g4['yc4_cart_table']}
                   where uq_id = '{$row['uq_id']}' ";
        $row1 = sql_fetch($sql1);

        $row1['orderamount'] += $row['od_send_cost'];
        $misu = $row1['orderamount'] - $row1['ordercancel'] - $row['od_dc_amount'] - $row['receiptamount'] + $row['receiptcancel'];

        $save['ordercount']++;
        $save['orderamount']   += $row1['orderamount'];
        $save['ordercancel']   += $row1['ordercancel'];
        $save['dc']            += $row['od_dc_amount'];
        $save['receiptbank']   += $row['od_receipt_bank'];
        $save['receiptcard']   += $row['od_receipt_card'];
        $save['receiptpoint']  += $row['od_receipt_point'];
        $save['receiptcancel'] += $row['receiptcancel'];
        $save['misu']          += $misu;

        $tot['ordercount']++;
        $tot['orderamount']   += $row1['orderamount'];
        $tot['ordercancel']   += $row1['ordercancel'];
        $tot['dc']            += $row['od_dc_amount'];
        $tot['receiptbank']   += $row['od_receipt_bank'];
        $tot['receiptcard']   += $row['od_receipt_card'];
        $tot['receiptpoint']  += $row['od_receipt_point'];
        $tot['receiptamount'] += $row['receiptamount'];
        $tot['receiptcancel'] += $row['receiptcancel'];
        $tot['misu']          += $misu;
    }

    if ($i == 0) {
        echo '<tr><td colspan="9" calss="sale1"><span>자료가 한건도 없습니다.</span></td></tr>';
    } else {
        print_line($save);
    }
    ?>
    </tbody>
    <tfoot>
    <tr class="sale1">
        <td>합 계</td>
        <td><?=number_format($tot['ordercount'])?></td>
        <td><?=number_format($tot['orderamount'])?></td>
        <td><?=number_format($tot['ordercancel'] + $tot['dc'])?></td>
        <td><?=number_format($tot['receiptbank'])?></td>
        <td><?=number_format($tot['receiptcard'])?></td>
        <td><?=number_format($tot['receiptpoint'])?></td>
        <td><?=number_format($tot['receiptcancel'])?></td>
        <td><?=number_format($tot['misu'])?></td>
    </tr>
    </tfoot>
    </table>
</section>

<?
include_once (G4_ADMIN_PATH.'/admin.tail.php');
?>

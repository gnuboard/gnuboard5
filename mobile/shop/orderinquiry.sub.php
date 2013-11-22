<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined("_ORDERINQUIRY_")) exit; // 개별 페이지 접근 불가
?>

<?php if (!$limit) { ?>총 <?php echo $cnt; ?> 건<?php } ?>

<div class="tbl_head02 tbl_wrap">
    <table>
    <thead>
    <tr>
        <th scope="col">주문번호</th>
        <th scope="col">주문일시</th>
        <th rowspan="2" scope="col">입금액</th>
    </tr>
    <tr>
        <th colspan="2" scope="col">주문상품</th>
    </tr>
    <tr>
        <th colspan="3" scope="col">배송정보</th>
    </tr>
    </thead>
    <tbody>
    <?php
    $sql = " select *,
                (od_cart_coupon + od_coupon + od_send_coupon) as couponprice
               from {$g5['g5_shop_order_table']}
              where mb_id = '{$member['mb_id']}'
              order by od_id desc
              $limit ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 주문상품
        $sql = " select *, count(ct_id) as cnt
                    from {$g5['g5_shop_cart_table']}
                    where od_id = '{$row['od_id']}'
                    order by ct_id
                    limit 1 ";
        $ct = sql_fetch($sql);
        $ct_name = get_text($ct['it_name']).' '.get_text($ct['ct_option']);
        if($ct['cnt'] > 1)
            $ct_name .= ' 외 '.($ct['cnt'] - 1).'건';

        switch($row['od_status']) {
            case '입금':
                $od_status = '입금완료';
                break;
            case '준비':
                $od_status = '상품준비중';
                break;
            case '배송':
                $od_status = '상품배송';
                break;
            case '완료':
                $od_status = '배송완료';
                break;
            default:
                $od_status = '입금확인중';
                break;
        }

        $od_invoice = '';
        if($row['od_delivery_company'] && $row['od_invoice'])
            $od_invoice = get_text($row['od_delivery_company']).' '.get_text($row['od_invoice']);

        $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
    ?>

    <tr>
        <td>
            <a href="<?php echo G5_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>"><?php echo $row['od_id']; ?></a>
        </td>
        <td><?php echo substr($row['od_time'],2,8); ?></td>
        <td rowspan="2"><?php echo display_price($row['od_receipt_price']); ?></td>
    </tr>
    <tr>
        <td colspan="2"><?php echo $ct_name; ?></td>
    </tr>
    <tr>
        <td colspan="3">
            <?php echo $od_status; ?>
            <?php echo $od_invoice; ?>
        </td>
    </tr>

    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="4" class="empty_table">주문 내역이 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>
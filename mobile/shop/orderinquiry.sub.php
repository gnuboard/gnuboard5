<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined("_ORDERINQUIRY_")) exit; // 개별 페이지 접근 불가
?>

<?php if (!$limit) { ?>총 <?php echo $cnt; ?> 건<?php } ?>


<div id="sod_inquiry">
    <ul>
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
            $sql = " select it_name, ct_option
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '{$row['od_id']}'
                        order by io_type, ct_id
                        limit 1 ";
            $ct = sql_fetch($sql);
            $ct_name = get_text($ct['it_name']).' '.get_text($ct['ct_option']);

            $sql = " select count(*) as cnt
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '{$row['od_id']}' ";
            $ct2 = sql_fetch($sql);
            if($ct2['cnt'] > 1)
                $ct_name .= ' 외 '.($ct2['cnt'] - 1).'건';

            switch($row['od_status']) {
                case '주문':
                    $od_status = '입금확인중';
                    break;
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
                    $od_status = '주문취소';
                    break;
            }

            $od_invoice = '';
            if($row['od_delivery_company'] && $row['od_invoice'])
                $od_invoice = get_text($row['od_delivery_company']).' '.get_text($row['od_invoice']);

            $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
        ?>

        <li>
            <div class="inquiry_idtime">
                <a href="<?php echo G5_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>" class="idtime_link"><?php echo $row['od_id']; ?></a>
                <span class="idtime_time"><?php echo substr($row['od_time'],2,8); ?></span>
            </div>
            <div class="inquiry_name">
                <?php echo $ct_name; ?>
            </div>
            <div class="inquiry_price">
                <?php echo display_price($row['od_receipt_price']); ?>
            </div>
            <div class="inquiry_inv">
                <span class="inv_status"><?php echo $od_status; ?></span>
                <span class="inv_inv"><?php echo $od_invoice; ?></span>
            </div>
        </li>

        <?php
        }

        if ($i == 0)
            echo '<li class="empty_list">주문 내역이 없습니다.</li>';
        ?>
    </ul>
</div>

<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_stylesheet('<link rel="stylesheet" href="'.G5_CSS_URL.'/subscription.css">', 11);
?>
<section class="mypage_subscription">
    <h2>최근 구독내역</h2>
    <div class="tbl_head03 tbl_wrap recent-my-subscription">
        <table>
        <thead>
        <tr>
            <th scope="col">상품정보</th>
            <th scope="col">구독번호</th>
            <th scope="col">구독신청일</th>
            <th scope="col">다음결제일</th>
            <th scope="col">구독금액</th>
            <th scope="col">결제수단</th>
            <th scope="col">상태</th>
            <th scope="col">상세보기</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach($ods as $row) {
            $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
            
            $view_url = G5_SUBSCRIPTION_URL.'/orderinquiryview.php?od_id='.$row['od_id'].'&amp;uid='.$uid;
            
            $cards = get_customer_card_info($row);
        ?>
        <tr>
            <td>
                <div class="text-center">
                    <a href="<?php echo $view_url; ?>">
                    <?php echo $row['goods']['thumb']; ?>
                    <br>
                    <?php echo $row['goods']['full_name']; ?>
                    </a>
                </div>
            </td>
            <td class="text-center"><a href="<?php echo $view_url; ?>"><?php echo $row['od_id']; ?></a></td>
            <td class="td_numbig"><?php echo substr($row['od_time'],2,9); ?> (<?php echo get_yoil($row['od_time']); ?>)</td>
            <td class="td_numbig text-center">
            <?php if (!is_null_date($row['next_billing_date'])) {
                echo substr($row['next_billing_date'],2,9). ' ('. get_yoil($row['next_billing_date']).')';
            } else {
                echo (!$row['od_enable_status']) ? '종료됨' : '없음';
            } ?>
            </td>
            <td class="td_numbig text-center"><?php echo display_price($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']); ?></td>
            <td class="td_numbig text-center">
                <?php if ($cards) { ?>
                    <?php echo subscription_pg_cardname($cards['od_card_name']); ?><br>(<?php echo substr($cards['card_mask_number'], 0, 4); ?>)
                <?php } else { ?>
                    카드정보 없음
                <?php } ?>
            </td>
            <td class="td_numbig text-center">
                <?php echo get_subscription_order_status($row); ?>
            </td>
            <td class="text-center"><a href="<?php echo $view_url; ?>">보기</a></td>
        </tr>
        <?php
        }

        if ($i == 0)
            echo '<tr><td colspan="8" class="empty_table">정기결제 내역이 없습니다.</td></tr>';
        ?>
        </tbody>
        </table>
    </div>
    <div class="smb_my_more">
        <a href="<?php echo G5_SUBSCRIPTION_URL; ?>/subscription_list.php">더보기</a>
    </div>
</section>
<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined("_ORDERINQUIRY_")) exit; // 개별 페이지 접근 불가

// 테마에 subscription_list.sub.php 있으면 include
if (defined('G5_THEME_SUBSCRIPTION_PATH')) {
    $theme_inquiry_file = G5_THEME_SUBSCRIPTION_PATH . '/subscription_list.sub.php';
    if (is_file($theme_inquiry_file)) {
        include_once($theme_inquiry_file);
        return;
        unset($theme_inquiry_file);
    }
}
?>

<!-- 주문 내역 목록 시작 { -->
<?php if (!$sql_limit) { ?>총 <?php echo $cnt; ?> 건<?php } ?>

<div class="mysubs-status-container">
    <ul class="mysubs-status-conditions">
        <li><a href="<?php echo get_params_merge_url(array('status' => '')); ?>" class="<?php if ($status === '') echo 'on'; ?>">전체</a></li>
        <li><a href="<?php echo get_params_merge_url(array('status' => '1')); ?>" class="<?php if ($status === '1') echo 'on'; ?>">구독중</a></li>
        <li><a href="<?php echo get_params_merge_url(array('status' => '0')); ?>" class="<?php if ($status === '0') echo 'on'; ?>">종료됨</a></li>
    </ul>
</div>

<div class="tbl_head03 tbl_wrap">
    <table>
        <thead>
            <tr>
                <th scope="col">상품</th>
                <th scope="col">구독번호</th>
                <th scope="col">결제예정금액</th>
                <th scope="col">결제수단</th>
                <th scope="col">배송주기</th>
                <th scope="col">진행회차</th>
                <th scope="col">상태</th>
            </tr>
        </thead>
        <tbody>
            <?php
            foreach ($orders as $row) {
                $uid = md5($row['od_id'] . $row['od_time'] . $row['od_ip']);
                
                // 배송주기와 이용횟수 구하기
                $crp = calculateRecurringPaymentDetails($row);
                
                $row['goods'] = get_subscription_pay_full_goods($row['od_id']);
                
                $cards = get_customer_card_info($row);
                
                $view_url = get_params_merge_url(array('od_id'=>$row['od_id'], 'uid'=>$uid), G5_SUBSCRIPTION_URL.'/orderinquiryview.php');
            ?>

                <tr>
                    <td>
                        <div class="text_center">
                            <a href="<?php echo $view_url; ?>">
                                <?php echo $row['goods']['thumb']; ?>
                                <br>
                                <?php echo $row['goods']['full_name']; ?>
                            </a>
                        </div>
                    </td>
                    <td>
                        <a href="<?php echo $view_url; ?>"><?php echo $row['od_id']; ?></a>
                    </td>
                    <td class="td_numbig text_center"><?php echo display_price($row['od_receipt_price']); ?></td>
                    <td class="td_numbig text_center">
                        <?php if ($cards) { ?>
                            <?php echo subscription_pg_cardname($cards['od_card_name']); ?><br>(<?php echo substr($cards['card_mask_number'], 0, 4); ?>)
                        <?php } else { ?>
                            카드정보 없음
                        <?php } ?>
                    </td>
                    <td class="td_numbig text_center"><?php echo $crp['deliverys']; ?></td>
                    <td class="td_numbig text_right"><?php echo $crp['usages']; ?></td>
                    <td><?php echo get_subscription_order_status($row); ?></td>
                </tr>

            <?php
            }

            if (empty($orders))
                echo '<tr><td colspan="7" class="empty_table">주문 내역이 없습니다.</td></tr>';
            ?>
        </tbody>
    </table>
</div>
<!-- } 주문 내역 목록 끝 -->
<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

add_event('g5_shop_mypage_sub_top', 'subscription_add_mypage_sub', 1, 0);

function subscription_add_mypage_sub() {
    global $g5, $member, $is_member;
    
    if (!$is_member) {
        return '';
    }
    
    $limit = " limit 0, 5 ";
    
    $sql = " select *
               from {$g5['g5_subscription_order_table']}
              where mb_id = '{$member['mb_id']}'
              order by od_id desc
              $limit ";
    $result = sql_query($sql);
    
    $ods = array();
    
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        $row['goods'] = get_subscription_pay_full_goods($row['od_id']);
        $ods[] = $row;
    }
?>
<!-- 정기결제 내역 목록 시작 { -->
<?php if (!$limit) { ?>총 <?php echo $cnt; ?> 건<?php } ?>
    
<h2>구독내역</h2>
<div class="tbl_head03 tbl_wrap">
    <table>
    <thead>
    <tr>
        <th scope="col">상품정보</th>
        <th scope="col">주문번호</th>
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

        switch($row['od_status']) {
            case '주문':
                $od_status = '<span class="status_01">입금확인중</span>';
                break;
            case '입금':
                $od_status = '<span class="status_02">입금완료</span>';
                break;
            case '준비':
                $od_status = '<span class="status_03">상품준비중</span>';
                break;
            case '배송':
                $od_status = '<span class="status_04">상품배송</span>';
                break;
            case '완료':
                $od_status = '<span class="status_05">배송완료</span>';
                break;
            default:
                $od_status = '<span class="status_06">주문취소</span>';
                break;
        }
    ?>
    <?php /*
    <tr>
        <td>
            <a href="<?php echo G5_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>"><?php echo $row['od_id']; ?></a>
        </td>
        <td><?php echo substr($row['od_time'],2,14); ?> (<?php echo get_yoil($row['od_time']); ?>)</td>
        <td class="td_numbig"><?php echo $row['od_cart_count']; ?></td>
        <td class="td_numbig text_right"><?php echo display_price($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']); ?></td>
        <td class="td_numbig text_right"><?php echo display_price($row['od_receipt_price']); ?></td>
        <td class="td_numbig text_right"><?php echo display_price($row['od_misu']); ?></td>
        <td><?php echo $od_status; ?></td>
    </tr> */
    ?>
    <tr>
        <td>
            <div>
                <?php echo $row['goods']['thumb']; ?>
                <br>
                <?php echo $row['goods']['full_name']; ?>
            </div>
        </td>
        <td><a href="<?php echo G5_SUBSCRIPTION_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>"><?php echo $row['od_id']; ?></td>
        <td class="td_numbig"><?php echo substr($row['od_time'],2,9); ?> (<?php echo get_yoil($row['od_time']); ?>)</td>
        <td class="td_numbig text_right"><?php echo substr($row['next_billing_date'],2,9); ?> (<?php echo get_yoil($row['next_billing_date']); ?>)</td>
        <td class="td_numbig text_right"><?php echo display_price($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']); ?></td>
        <td class="td_numbig text_right"><?php echo subscription_pg_cardname($row['od_card_name']); ?></td>
        <td class="td_numbig text_right"><?php echo $od_status; ?></td>
        <td>보기</td>
    </tr>
    <?php
    }

    if ($i == 0)
        echo '<tr><td colspan="7" class="empty_table">정기결제 내역이 없습니다.</td></tr>';
    ?>
    </tbody>
    </table>
</div>
<!-- } 정기결제 내역 목록 끝 -->
<?php
    
    /*
    if () {
        
    }
    */
}
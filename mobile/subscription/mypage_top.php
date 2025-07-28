<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (! $cnt) {
    return;
}
?>
<h2><a href="<?php echo G5_SUBSCRIPTION_URL; ?>/subscription_list.php">최근 구독내역</a></h2>

<?php if ($cnt) { ?>총 <?php echo $cnt; ?> 건<?php } ?>

<div id="sod_inquiry">
    <ul>
        <?php
        foreach($ods as $row) {
            $uid = md5($row['od_id'].$row['od_time'].$row['od_ip']);
            
            $view_url = G5_SUBSCRIPTION_URL.'/orderinquiryview.php?od_id='.$row['od_id'].'&amp;uid='.$uid;
            
            $od_status = $row['od_enable_status'] ? '활성화' : '비활성화';
        ?>

        <li>
            <div class="inquiry_idtime">
                <a href="<?php echo $view_url; ?>" class="idtime_link"><?php echo $row['od_id']; ?></a>
                <span class="idtime_time"><?php echo substr($row['od_time'],2,25); ?></span>
            </div>
            <div class="inquiry_name">
                <?php echo get_text($row['goods']['full_name']); ?>
            </div>
            <div class="inq_wr">
                <div class="inquiry_price">
                    <?php echo display_price($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']); ?>
                </div>
                <div class="inv_status"><?php echo $od_status; ?></div>
            </div>
            <div class="inquiry_inv">
                
            </div>
        </li>

        <?php
        }

        if ($i == 0)
            echo '<li class="empty_list">주문 내역이 없습니다.</li>';
        ?>
    </ul>
</div>

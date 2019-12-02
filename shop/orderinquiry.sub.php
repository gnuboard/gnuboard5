<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!defined("_ORDERINQUIRY_")) exit; // 개별 페이지 접근 불가

// 테마에 orderinquiry.sub.php 있으면 include
if(defined('G5_THEME_SHOP_PATH')) {
    $theme_inquiry_file = G5_THEME_SHOP_PATH.'/orderinquiry.sub.php';
    if(is_file($theme_inquiry_file)) {
        include_once($theme_inquiry_file);
        return;
        unset($theme_inquiry_file);
    }
}
?>

<!-- 주문 내역 목록 시작 { -->
<?php if (!$limit) { ?>총 <?php echo $cnt; ?> 건<?php } ?>

<p class="tooltip_txt"><i class="fa fa-info-circle" aria-hidden="true"></i> 주문서번호 링크를 누르시면 주문상세내역을 조회하실 수 있습니다.</p>
<ul class="smb_my_od">
	<?php
	    $sql = " select * 
	              from {$g5['g5_shop_order_table']}
	              where mb_id = '{$member['mb_id']}'
	              order by od_id desc
	              $limit ";
	    $result = sql_query($sql);
	    for ($i=0; $row=sql_fetch_array($result); $i++)
	    {
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
	<li>
		<div class="smb_my_od_li smb_my_od_li1">
			<span class="sound_only">주문서번호</span>
        	<input type="hidden" name="ct_id[<?php echo $i; ?>]" value="<?php echo $row['ct_id']; ?>">
        	<a href="<?php echo G5_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>" class="ord_num"><?php echo $row['od_id']; ?></a>
			<br>
			<span class="sound_only">주문일시</span>
			<span class="date"><?php echo substr($row['od_time'],2,14); ?> (<?php echo get_yoil($row['od_time']); ?>)</span>
		</div>
		<div class="smb_my_od_li smb_my_od_li2">
			<a href="<?php echo G5_SHOP_URL; ?>/orderinquiryview.php?od_id=<?php echo $row['od_id']; ?>&amp;uid=<?php echo $uid; ?>" class="ord_name"><?php echo $row['it_name']; ?>상품명입니다</a>
			<br>
			<span class="sound_only">주문금액</span>
			<span class="cost"><?php echo display_price($row['od_cart_price'] + $row['od_send_cost'] + $row['od_send_cost2']); ?></span>
    		<!-- <span clsass="sound_only">입금액</span>
    		<?php echo display_price($row['od_receipt_price']); ?> -->
    		<span class="misu">(미입금액 : <?php echo display_price($row['od_misu']); ?>)</span>
		</div>
		<div class="smb_my_od_li smb_my_od_li3">
			<span class="sound_only">상태</span>
			<?php echo $od_status; ?>
		</div>
	</li>
	<?php
}

if ($i == 0)
    echo '<li class="empty_table">주문 내역이 없습니다.</li>';
?>
</ul>
<!-- } 주문 내역 목록 끝 -->
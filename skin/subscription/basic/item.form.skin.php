<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

include_once(G5_PLUGIN_PATH . '/jquery-ui/datepicker.php');

add_javascript('<script src="'.G5_JS_URL.'/jquerymodal/jquery.modal.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/jquerymodal/jquery.modal.min.css">', 10);

add_javascript('<script src="'.G5_JS_URL.'/pg-calendar/js/pignose.calendar.full.min.js"></script>', 11);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/pg-calendar/css/pignose.calendar.min.css">', 11);

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . G5_SUBSCRIPTION_CSS_URL . '/style.css">', 0);
?>
<div id="sit_ov_from">
	<form name="fitem" method="post" action="<?php echo $action_url; ?>" onsubmit="return fitem_submit(this);">
		<input type="hidden" name="it_id[]" value="<?php echo $it_id; ?>">
		<input type="hidden" name="sw_direct">
		<input type="hidden" name="url">

        <input type="hidden" name="delivery_cycle" id="hidden_delivery_cycle">
        <input type="hidden" name="usage_count" id="hidden_usage_count">
        <input type="hidden" name="hope_delivery_date" id="hidden_hope_delivery_date">
        
		<div id="sit_ov_wrap">
			<!-- 상품이미지 미리보기 시작 { -->
			<div id="sit_pvi">
				<div id="sit_pvi_big">
					<?php
					$big_img_count = 0;
					$thumbnails = array();
					for ($i = 1; $i <= 10; $i++) {
						if (!$it['it_img' . $i])
							continue;

						$img = get_it_thumbnail($it['it_img' . $i], $default['de_mimg_width'], $default['de_mimg_height']);

						if ($img) {
							// 썸네일
							$thumb = get_it_thumbnail($it['it_img' . $i], 70, 70);
							$thumbnails[] = $thumb;
							$big_img_count++;

							echo '<a href="' . G5_SHOP_URL . '/largeimage.php?it_id=' . $it['it_id'] . '&amp;no=' . $i . '" target="_blank" class="popup_item_image">' . $img . '</a>';
						}
					}

					if ($big_img_count == 0) {
						echo '<img src="' . G5_SHOP_URL . '/img/no_image.gif" alt="">';
					}
					?>
					<a href="<?php echo G5_SHOP_URL; ?>/largeimage.php?it_id=<?php echo $it['it_id']; ?>&amp;no=1" target="_blank" id="popup_item_image" class="popup_item_image"><i class="fa fa-search-plus" aria-hidden="true"></i><span class="sound_only">확대보기</span></a>
				</div>
				<?php
				// 썸네일
				$thumb1 = true;
				$thumb_count = 0;
				$total_count = count($thumbnails);
				if ($total_count > 0) {
					echo '<ul id="sit_pvi_thumb">';
					foreach ($thumbnails as $val) {
						$thumb_count++;
						$sit_pvi_last = '';
						if ($thumb_count % 5 == 0) $sit_pvi_last = 'class="li_last"';
						echo '<li ' . $sit_pvi_last . '>';
						echo '<a href="' . G5_SHOP_URL . '/largeimage.php?it_id=' . $it['it_id'] . '&amp;no=' . $thumb_count . '" target="_blank" class="popup_item_image img_thumb">' . $val . '<span class="sound_only"> ' . $thumb_count . '번째 이미지 새창</span></a>';
						echo '</li>';
					}
					echo '</ul>';
				}
				?>
			</div>
			<!-- } 상품이미지 미리보기 끝 -->

			<!-- 상품 요약정보 및 구매 시작 { -->
			<section id="sit_ov" class="2017_renewal_itemform">
				<h2 id="sit_title"><?php echo stripslashes($it['it_name']); ?> <span class="sound_only">요약정보 및 구매</span></h2>
				<p id="sit_desc"><?php echo $it['it_basic']; ?></p>
				<?php if ($is_orderable) { ?>
					<p id="sit_opt_info">
						상품 선택옵션 <?php echo $option_count; ?> 개, 추가옵션 <?php echo $supply_count; ?> 개
					</p>
				<?php } ?>

				<div id="sit_star_sns">
					<?php if ($star_score) { ?>
						<span class="sound_only">고객평점</span>
						<img src="<?php echo G5_SHOP_URL; ?>/img/s_star<?php echo $star_score ?>.png" alt="" class="sit_star" width="100">
						<span class="sound_only">별<?php echo $star_score ?>개</span>
					<?php } ?>

					<span class="">사용후기 <?php echo $it['it_use_cnt']; ?> 개</span>

					<div id="sit_btn_opt">
						<span id="btn_wish"><i class="fa fa-heart-o" aria-hidden="true"></i><span class="sound_only">위시리스트</span><span class="btn_wish_num"><?php echo get_wishlist_count_by_item($it['it_id']); ?></span></span>
						<button type="button" class="btn_sns_share"><i class="fa fa-share-alt" aria-hidden="true"></i><span class="sound_only">sns 공유</span></button>
						<div class="sns_area">
							<?php echo $sns_share_links; ?>
							<a href="javascript:popup_item_recommend('<?php echo $it['it_id']; ?>');" id="sit_btn_rec"><i class="fa fa-envelope-o" aria-hidden="true"></i><span class="sound_only">추천하기</span></a>
						</div>
					</div>
				</div>
				<script>
					$(".btn_sns_share").click(function() {
						$(".sns_area").show();
					});
					$(document).mouseup(function(e) {
						var container = $(".sns_area");
						if (container.has(e.target).length === 0)
							container.hide();
					});
				</script>

				<div class="sit_info">
					<table class="sit_ov_tbl">
						<colgroup>
							<col class="grid_3">
							<col>
						</colgroup>
						<tbody>

							<?php if (!$it['it_use']) { // 판매가능이 아닐 경우 
							?>
								<tr>
									<th scope="row">판매가격</th>
									<td>판매중지</td>
								</tr>
							<?php } ?>
							<?php if ($it['it_cust_price']) { ?>
								<tr>
									<th scope="row">시중가격</th>
									<td><?php echo display_price($it['it_cust_price']); ?></td>
								</tr>
							<?php } // 시중가격 끝 
							?>

							<tr class="tr_price">
								<th scope="row">판매가격</th>
								<td>
									<strong><?php echo display_price(get_subscription_price($it)); ?></strong>
									<input type="hidden" id="it_price" value="<?php echo get_subscription_price($it); ?>">
								</td>
							</tr>

							<?php if ($it['it_maker']) { ?>
								<tr>
									<th scope="row">제조사</th>
									<td><?php echo $it['it_maker']; ?></td>
								</tr>
							<?php } ?>

							<?php if ($it['it_origin']) { ?>
								<tr>
									<th scope="row">원산지</th>
									<td><?php echo $it['it_origin']; ?></td>
								</tr>
							<?php } ?>

							<?php if ($it['it_brand']) { ?>
								<tr>
									<th scope="row">브랜드</th>
									<td><?php echo $it['it_brand']; ?></td>
								</tr>
							<?php } ?>

							<?php if ($it['it_model']) { ?>
								<tr>
									<th scope="row">모델</th>
									<td><?php echo $it['it_model']; ?></td>
								</tr>
							<?php } ?>

							<?php
							/* 재고 표시하는 경우 주석 해제
							<tr>
								<th scope="row">재고수량</th>
								<td><?php echo number_format(get_it_stock_qty($it_id)); ?> 개</td>
							</tr>
							*/
							?>

							<?php if ($config['cf_use_point']) { // 포인트 사용한다면 
							?>
								<tr>
									<th scope="row">포인트</th>
									<td>
										<?php
										if ($it['it_point_type'] == 2) {
											echo '구매금액(추가옵션 제외)의 ' . $it['it_point'] . '%';
										} else {
											$it_point = get_item_point($it);
											echo number_format($it_point) . '점';
										}
										?>
									</td>
								</tr>
							<?php } ?>
							<?php if (isset($it['it_subscription_number']) && $it['it_subscription_number']) {
								$subscription_number_label = '배송주기';
								$subscription_iteration = (isset($it['it_subscription_iteration']) && (int) $it['it_subscription_iteration'] > 1) ? (int) $it['it_subscription_iteration'] : 1;
							?>
								<tr>
									<th><?php echo $subscription_number_label; ?></th>
									<td>
										<select id="it_subscription_number_select" name="it_subscription_number">
											<option selected="" disabled="">선택해주세요</option>
											<?php for ($i = 1; $i <= $subscription_iteration; $i++) { ?>
												<option value="<?php echo (int)$it['it_subscription_number'] * $i; ?>"><?php echo $i; ?> 주기마다 (<?php echo (int)$it['it_subscription_number'] * $i; ?>일마다)</option>
											<?php } ?>
										</select>
									</td>
								</tr>
							<?php } ?>
							<?php if (isset($it['it_subscription_iteration']) && $it['it_subscription_iteration']) { ?>
							<?php } ?>
							<?php if (isset($it['it_subscription_expiration_date']) && $it['it_subscription_expiration_date']) { ?>
							<?php } ?>
							<?php if (isset($it['it_check_firstshipment_day']) && $it['it_check_firstshipment_day']) { ?>
								<tr>
									<th>첫발송일</th>
									<td>
										<span id="firstshipment-datepicker-ymd" data-ymd="<?php echo date('Y-m-d', strtotime(G5_TIME_YMDHIS . ' +' . (int) $it['it_check_firstshipment_day'] . ' day')); ?>">
											<?php echo date('Y년 m월 d일', strtotime(G5_TIME_YMDHIS . ' +' . (int) $it['it_check_firstshipment_day'] . ' day')); ?>
										</span>
                                        <input type="hidden" id="it_firstshipment_date" name="it_firstshipment_date" value="" >
										<div id="firstshipment-datepicker"></div>
										<script>
											jQuery(function($) {
												$('#firstshipment-datepicker').datepicker({
													dateFormat: 'yy-mm-dd',
													changeMonth: true,
													changeYear: true,
													onSelect: function() {
														var dateObject = $(this).datepicker('getDate'),
                                                            datepicker_ymd_val = $.datepicker.formatDate("yy-mm-dd", dateObject);

														$("#firstshipment-datepicker-ymd")
															.attr("data-ymd", datepicker_ymd_val)
															.text($.datepicker.formatDate("yy년 mm월 dd일", dateObject));
                                                        
                                                        $("#it_firstshipment_date").val(datepicker_ymd_val);
														console.log(datepicker_ymd_val);
													},
													beforeShowDay: function(date) {
														var day = date.getDay();
														return [(day != 0 && day != 6)];
													},
													minDate: "+<?php echo (int) $it['it_check_firstshipment_day']; ?>d",
													<?php if (isset($it['it_expire_firstshipment_day']) && $it['it_expire_firstshipment_day']) { ?>
														maxDate: "+<?php echo (int) $it['it_expire_firstshipment_day']; ?>d",
													<?php } ?>
												});
											});
										</script>
										<div class="print-next-shipment-ymd"></div>
									</td>
								</tr>
							<?php } ?>
							<?php if (isset($it['it_expire_firstshipmen_day']) && $it['it_expire_firstshipmen_day']) { ?>
							<?php } ?>
							<?php
							$ct_send_cost_label = '배송비결제';

							if ($it['it_sc_type'] == 1)
								$sc_method = '무료배송';
							else {
								if ($it['it_sc_method'] == 1)
									$sc_method = '수령후 지불';
								else if ($it['it_sc_method'] == 2) {
									$ct_send_cost_label = '<label for="ct_send_cost">배송비결제</label>';
									$sc_method = '<select name="ct_send_cost" id="ct_send_cost">
	                                      <option value="0">주문시 결제</option>
	                                      <option value="1">수령후 지불</option>
	                                  </select>';
								} else
									$sc_method = '주문시 결제';
							}
							?>
							<tr>
								<th><?php echo $ct_send_cost_label; ?></th>
								<td><?php echo $sc_method; ?></td>
							</tr>
							<?php if ($it['it_buy_min_qty']) { ?>
								<tr>
									<th>최소구매수량</th>
									<td><?php echo number_format($it['it_buy_min_qty']); ?> 개</td>
								</tr>
							<?php } ?>
							<?php if ($it['it_buy_max_qty']) { ?>
								<tr>
									<th>최대구매수량</th>
									<td><?php echo number_format($it['it_buy_max_qty']); ?> 개</td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
				<?php
				if ($option_item) {
				?>
					<!-- 선택옵션 시작 { -->
					<section class="sit_option">
						<h3>선택옵션</h3>

						<?php // 선택옵션
						echo $option_item;
						?>
					</section>
					<!-- } 선택옵션 끝 -->
				<?php
				}
				?>

				<?php
				if ($supply_item) {
				?>
					<!-- 추가옵션 시작 { -->
					<section class="sit_option">
						<h3>추가옵션</h3>
						<?php // 추가옵션
						echo $supply_item;
						?>
					</section>
					<!-- } 추가옵션 끝 -->
				<?php
				}
				?>

				<?php if ($is_orderable) { ?>
					<!-- 선택된 옵션 시작 { -->
					<section id="sit_sel_option">
						<h3>선택된 옵션</h3>
						<?php
						if (!$option_item) {
							if (!$it['it_buy_min_qty'])
								$it['it_buy_min_qty'] = 1;
						?>
							<ul id="sit_opt_added">
								<li class="sit_opt_list">
									<input type="hidden" name="io_type[<?php echo $it_id; ?>][]" value="0">
									<input type="hidden" name="io_id[<?php echo $it_id; ?>][]" value="">
									<input type="hidden" name="io_value[<?php echo $it_id; ?>][]" value="<?php echo $it['it_name']; ?>">
									<input type="hidden" class="io_price" value="0">
									<input type="hidden" class="io_stock" value="<?php echo $it['it_stock_qty']; ?>">
									<div class="opt_name">
										<span class="sit_opt_subj"><?php echo $it['it_name']; ?></span>
									</div>
									<div class="opt_count">
										<label for="ct_qty_<?php echo $i; ?>" class="sound_only">수량</label>
										<button type="button" class="sit_qty_minus"><i class="fa fa-minus" aria-hidden="true"></i><span class="sound_only">감소</span></button>
										<input type="text" name="ct_qty[<?php echo $it_id; ?>][]" value="<?php echo $it['it_buy_min_qty']; ?>" id="ct_qty_<?php echo $i; ?>" class="num_input" size="5">
										<button type="button" class="sit_qty_plus"><i class="fa fa-plus" aria-hidden="true"></i><span class="sound_only">증가</span></button>
										<span class="sit_opt_prc">+0원</span>
									</div>
								</li>
							</ul>
							<script>
								$(function() {
									price_calculate();
								});
							</script>
						<?php } ?>
					</section>
					<!-- } 선택된 옵션 끝 -->

					<!-- 총 구매액 -->
					<div id="sit_tot_price"></div>
				<?php } ?>

				<?php if ($is_soldout) { ?>
					<p id="sit_ov_soldout">상품의 재고가 부족하여 구매할 수 없습니다.</p>
				<?php } ?>

                <?php // 정기결제 모달 시작 ?>
                <div id="ex1" class="modal">
                    <div>
                        <h2 class="subscription-title">
                            정기구독 배송일 선택
                        </h2>
                    </div>
                    <?php if (get_subs_option('su_subscription_content_first')) {   // 정기결제 폼 첫번째 안내문이 있다면 ?>
                    <div class="subscription-desc1">
                        <?php echo conv_content(get_subs_option('su_subscription_content_first'), 1); ?>
                    </div>
                    <?php } ?>
                    
                    <?php
                        // 정기구독 설정 불러오기
                        // 배송주기
                        $subscription_info_inputs = get_subscription_info_inputs();

                        // 이용횟수
                        $subscription_use_inputs = get_subscription_use_inputs();
                    ?>
                    <h3>
                        <label for=""><?php echo subscription_item_delivery_title($it); ?></label>
                    </h3>
                    <?php if (get_subs_option('su_chk_user_delivery')) { ?>
                    <div>
                        <input id="od_subscription_select_data" name="od_subscription_select_data" type="number" inputmode="numeric" placeholder="숫자" max="365" maxlength="3" value="<?php echo get_subs_option('su_user_delivery_default_day'); ?>" class="frm_input">
                        <span class="od_subscription_days">일</span>
                    </div>
                    <?php } else { ?>
                    
                    <div>
                        <?php if (get_subs_option('su_output_display_type')) {  // 버튼식 ?>
                            <div class="su-display-btns">
                            <?php 
                            foreach ($subscription_info_inputs as $key=>$opt) {
                                if (! $opt['opt_use']) {
                                    continue;
                                }

                            $opt_print = $opt['opt_print'] ? $opt['opt_print'] : $opt['opt_input'].' 일마다';

                            if ($opt['opt_input'] || $opt['opt_date_format']) {
                                $opt_print = str_replace("{입력}", $opt['opt_input'], $opt_print);
                                $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print);
                            }
                            ?>
                            <input type="radio" id="od_subscription_select_data_<?php echo $key; ?>" class="sound_only" name="od_subscription_select_data" value="<?php echo get_text($key.'||'.$opt['opt_input'].'||'.$opt['opt_date_format']); ?>">
                            <label for="od_subscription_select_data_<?php echo $key; ?>" class="select-icon"><span><?php echo $opt_print; ?></span></label>
                            <?php } ?>
                            </div>
                        <?php } else {  // 셀렉트박스 ?>
                            <select id="od_subscription_select_data" class="frm_input" name="od_subscription_select_data">
                            <option value="" selected="" disabled="">선택해주세요</option>
                            <?php
                            foreach ($subscription_info_inputs as $key=>$opt) {
                                if (! $opt['opt_use']) {
                                    continue;
                                }

                            $opt_print = $opt['opt_print'] ? $opt['opt_print'] : $opt['opt_input'].' 일마다';

                            if ($opt['opt_input'] || $opt['opt_date_format']) {
                                $opt_print = str_replace("{입력}", $opt['opt_input'], $opt_print);
                                $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print);
                            }
                            ?>
                            <option value="<?php echo get_text($key.'||'.$opt['opt_input'].'||'.$opt['opt_date_format']); ?>"><?php echo $opt_print; ?></option>
                            <?php } ?>
                            </select>
                        <?php } ?>
                    </div>
                    <?php } ?>
                    <h3><label for="">이용횟수</label></h3>
                     <div>
                        <?php if (get_subs_option('su_output_display_type')) {  // 버튼식 ?>
                            <input type="hidden" id="od_subscription_select_number" name="od_subscription_select_number">
                            <div class="su-display-btns">
                                <?php foreach ($subscription_use_inputs as $key=>$use) {
                                if (! $use['num_use']) {
                                    continue;
                                }

                                $use_print = $use['use_print'] ? $use['use_print'] : $use['use_input'].' 일마다';

                                if ($use['use_input']) {
                                    $use_print = str_replace("{입력}", $use['use_input'], $use_print);
                                }
                                ?>
                                <input type="radio" id="od_subscription_select_number_<?php echo $key; ?>" class="sound_only" name="od_subscription_select_number" value="<?php echo get_text($key.'||'.$use['use_input']); ?>">
                                <label for="od_subscription_select_number_<?php echo $key; ?>" class="select-icon"><span><?php echo $use_print; ?></span></label>
                                <?php } ?>
                            </div>
                        <?php } else {  // 셀렉트박스 ?>
                            <select id="od_subscription_select_number" class="frm_input" name="od_subscription_select_number">
                            <option value="" selected="" disabled="">선택해주세요</option>
                            <?php
                            foreach ($subscription_use_inputs as $key=>$use) {
                            if (! $use['num_use']) {
                            continue;
                            }

                            $use_print = $use['use_print'] ? $use['use_print'] : $use['use_input'].' 일마다';

                            if ($use['use_input']) {
                            $use_print = str_replace("{입력}", $use['use_input'], $use_print);
                            }
                            ?>
                            <option value="<?php echo get_text($key.'||'.$use['use_input']); ?>"><?php echo $use_print; ?></option>
                            <?php } ?>
                            </select>
                        <?php } ?>
                      </div>

                        <?php if (get_subs_option('su_hope_date_use')) { // 배송희망일 사용 ?>
                        <h3><label for="od_hope_date_print">희망배송일</label></h3>
                        <div class="jquery-datepicker">
                                <input type="hidden" name="od_hope_date" value="" id="od_hope_date" class="frm_input" maxlength="10">
                                <div id="od_hope_date_print" class="jquery-pg-datepicker"></div>

                        </div>
                        <?php } ?>
                            
                        <?php if (get_subs_option('su_subscription_content_end')) {   // 정기결제 폼 마지막 안내문이 있다면 ?>
                        <div class="subscription-desc-end">
                            <?php echo conv_content(get_subs_option('su_subscription_content_end'), 1); ?>
                        </div>
                        <?php } ?>
                            
                        <div class="form-box-btns">
                            <button type="submit" onclick="document.pressed=this.value;" value="정기구독신청" class="sit_btn_subscription sit_btn_buy">정기구독 신청하기</button>
                            <!-- <a href="#" class="sit_btn_subscription sit_btn_buy">정기구독 신청하기</a> -->
                        </div>
                </div>

				<div id="sit_ov_btn">
					<?php if ($is_orderable) { ?>
						<button type="submit" onclick="document.pressed=this.value;" value="구독장바구니" class="sit_btn_cart">구독장바구니</button>
                        <?php if ($is_orderable) { ?>
                        <a href="#ex1" rel="modal:open" class="sit-btn-subscription">정기구독</a>
                        <?php } ?>
						<!-- <button type="submit" onclick="document.pressed=this.value;" value="정기구독구매" class="sit_btn_buy">정기구독구매</button> -->
                        <!-- <button type="button" href="#ex1" rel="modal:open" onclick="document.pressed=this.value;" value="정기구독" class="sit_btn_buy">정기구독</button> -->
					<?php } ?>
					<a href="javascript:item_wish(document.fitem, '<?php echo $it['it_id']; ?>');" class="sit_btn_wish"><i class="fa fa-heart-o" aria-hidden="true"></i><span class="sound_only">위시리스트</span></a>

					<?php if (!$is_orderable && $it['it_soldout'] && $it['it_stock_sms']) { ?>
						<a href="javascript:popup_stocksms('<?php echo $it['it_id']; ?>');" id="sit_btn_alm">재입고알림</a>
					<?php } ?>
				</div>

				<script>
					// 상품보관
					function item_wish(f, it_id) {
						f.url.value = "<?php echo G5_SHOP_URL; ?>/wishupdate.php?it_id=" + it_id;
						f.action = "<?php echo G5_SHOP_URL; ?>/wishupdate.php";
						f.submit();
					}

					// 추천메일
					function popup_item_recommend(it_id) {
						if (!g5_is_member) {
							if (confirm("회원만 추천하실 수 있습니다."))
								document.location.href = "<?php echo G5_BBS_URL; ?>/login.php?url=<?php echo urlencode(shop_item_url($it_id)); ?>";
						} else {
							url = "./itemrecommend.php?it_id=" + it_id;
							opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
							popup_window(url, "itemrecommend", opt);
						}
					}

					// 재입고SMS 알림
					function popup_stocksms(it_id) {
						url = "<?php echo G5_SHOP_URL; ?>/itemstocksms.php?it_id=" + it_id;
						opt = "scrollbars=yes,width=616,height=420,top=10,left=10";
						popup_window(url, "itemstocksms", opt);
					}
				</script>
			</section>
			<!-- } 상품 요약정보 및 구매 끝 -->
		</div>
		<!-- 다른 상품 보기 시작 { -->
		<div id="sit_siblings">
			<?php
			if ($prev_href || $next_href) {
				echo $prev_href . $prev_title . $prev_href2;
				echo $next_href . $next_title . $next_href2;
			} else {
				echo '<span class="sound_only">이 분류에 등록된 다른 상품이 없습니다.</span>';
			}
			?>
		</div>
		<!-- } 다른 상품 보기 끝 -->
	</form>
</div>
                
<script>
	function formatDateKo(date) {
		return date.getFullYear() + '년 ' + (date.getMonth() + 1) + '월' + date.getDate() + '일';
	}

	// 날짜 더하기(빼기)
	function addDays(date, days) {

		// 날짜 문자열에서 '-' 제거
		var dateString = date.replace(/-/g, '');

		// 연, 월, 일 파싱
		var yy = parseInt(dateString.substring(0, 4));
		var mm = parseInt(dateString.substring(4, 6));
		var dd = parseInt(dateString.substring(6, 8));

		console.log(yy, mm, dd);

		var result = new Date(yy, mm, dd);

		console.log(result, result.getDate());
		console.log("days: " + days);
		result.setDate(result.getDate() + parseInt(days));

		console.log(result);
		return formatDateKo(result);
	}

	$(function() {
		// 상품이미지 첫번째 링크
		$("#sit_pvi_big a:first").addClass("visible");

		// 상품이미지 미리보기 (썸네일에 마우스 오버시)
		$("#sit_pvi .img_thumb").bind("mouseover focus", function() {
			var idx = $("#sit_pvi .img_thumb").index($(this));
			$("#sit_pvi_big a.visible").removeClass("visible");
			$("#sit_pvi_big a:eq(" + idx + ")").addClass("visible");
		});

		// 상품이미지 크게보기
		$(".popup_item_image").click(function() {
			var url = $(this).attr("href");
			var top = 10;
			var left = 10;
			var opt = 'scrollbars=yes,top=' + top + ',left=' + left;
			popup_window(url, "largeimage", opt);

			return false;
		});

		$(document).on("change", "#it_subscription_number_select", function(e) {
			var subscription_number = $(this).val();
			var firstshipment_ymd = $("#firstshipment-datepicker-ymd").attr("data-ymd");

			console.log(subscription_number);
			if (subscription_number && firstshipment_ymd) {
				$(".print-next-shipment-ymd").text("다음 정기 발송일 : " + addDays(firstshipment_ymd, subscription_number));
			}
		});
	});

	function fsubmit_check(f) {
		// 판매가격이 0 보다 작다면
		if (document.getElementById("it_price").value < 0) {
			alert("전화로 문의해 주시면 감사하겠습니다.");
			return false;
		}
        
        console.log(f);
        
		if ($(".sit_opt_list").length < 1) {
			alert("상품의 선택옵션을 선택해 주십시오.");
			return false;
		}

		var val, io_type, result = true;
		var sum_qty = 0;
		var min_qty = parseInt(<?php echo $it['it_buy_min_qty']; ?>);
		var max_qty = parseInt(<?php echo $it['it_buy_max_qty']; ?>);
		var $el_type = $("input[name^=io_type]");

		$("input[name^=ct_qty]").each(function(index) {
			val = $(this).val();

			if (val.length < 1) {
				alert("수량을 입력해 주십시오.");
				result = false;
				return false;
			}

			if (val.replace(/[0-9]/g, "").length > 0) {
				alert("수량은 숫자로 입력해 주십시오.");
				result = false;
				return false;
			}

			if (parseInt(val.replace(/[^0-9]/g, "")) < 1) {
				alert("수량은 1이상 입력해 주십시오.");
				result = false;
				return false;
			}

			io_type = $el_type.eq(index).val();
			if (io_type == "0")
				sum_qty += parseInt(val);
		});

		if (!result) {
			return false;
		}

		if (min_qty > 0 && sum_qty < min_qty) {
			alert("선택옵션 개수 총합 " + number_format(String(min_qty)) + "개 이상 주문해 주십시오.");
			return false;
		}

		if (max_qty > 0 && sum_qty > max_qty) {
			alert("선택옵션 개수 총합 " + number_format(String(max_qty)) + "개 이하로 주문해 주십시오.");
			return false;
		}

		return true;
	}

	// 바로구매, 장바구니 폼 전송
	function fitem_submit(f) {
		f.action = "<?php echo $action_url; ?>";
		f.target = "";
        
		if (document.pressed == "구독장바구니") {
			f.sw_direct.value = 0;
		} else { // 바로구매 또는 정기구독신청
			f.sw_direct.value = 1;
		}

        if (document.pressed == "구독장바구니") {
        }
        
		// 판매가격이 0 보다 작다면
		if (document.getElementById("it_price").value < 0) {
			alert("전화로 문의해 주시면 감사하겠습니다.");
			return false;
		}
        
        console.log(f);
        
		if ($(".sit_opt_list").length < 1) {
			alert("상품의 선택옵션을 선택해 주십시오.");
			return false;
		}
        
        if (!$("#it_subscription_number_select").val()) {
			//alert("배송주기를 입력해 주세요.");
			//return false;
        }
        
		var val, io_type, result = true;
		var sum_qty = 0;
		var min_qty = parseInt(<?php echo $it['it_buy_min_qty']; ?>);
		var max_qty = parseInt(<?php echo $it['it_buy_max_qty']; ?>);
		var $el_type = $("input[name^=io_type]");

		$("input[name^=ct_qty]").each(function(index) {
			val = $(this).val();

			if (val.length < 1) {
				alert("수량을 입력해 주십시오.");
				result = false;
				return false;
			}

			if (val.replace(/[0-9]/g, "").length > 0) {
				alert("수량은 숫자로 입력해 주십시오.");
				result = false;
				return false;
			}

			if (parseInt(val.replace(/[^0-9]/g, "")) < 1) {
				alert("수량은 1이상 입력해 주십시오.");
				result = false;
				return false;
			}

			io_type = $el_type.eq(index).val();
			if (io_type == "0")
				sum_qty += parseInt(val);
		});

		if (!result) {
			return false;
		}

		if (min_qty > 0 && sum_qty < min_qty) {
			alert("선택옵션 개수 총합 " + number_format(String(min_qty)) + "개 이상 주문해 주십시오.");
			return false;
		}

		if (max_qty > 0 && sum_qty > max_qty) {
			alert("선택옵션 개수 총합 " + number_format(String(max_qty)) + "개 이하로 주문해 주십시오.");
			return false;
		}

		return true;
	}
    
    <?php if (get_subs_option('su_hope_date_use')) { ?>
        
        var holidays = [
            '2024-01-01', // 신정
            '2024-02-09', '2024-02-10', '2024-02-11', '2024-02-12', // 설날 연휴
            '2024-03-01', // 삼일절
            '2024-05-05', '2024-05-06', // 어린이날 대체 공휴일
            '2024-06-06', // 현충일
            '2024-08-15', // 광복절
            '2024-09-16', '2024-09-17', '2024-09-18', // 추석 연휴
            '2024-10-03', // 개천절
            '2024-10-09', // 한글날
            '2024-12-25'  // 성탄절
        ];
    
        function getBusinessDaysBefore(date, businessDays) {
            // date: 기준 날짜 (Date 객체)
            // businessDays: 몇 영업일 전으로 이동할 것인지
            // holidays: 공휴일 배열 (YYYY-MM-DD 형식의 문자열 배열)
            while (businessDays > 0) {
                date.setDate(date.getDate() - 1); // 하루 전으로 이동
                const dayOfWeek = date.getDay(); // 요일 (0: 일요일, 6: 토요일)
                
                // 날짜 포맷 (YYYY-MM-DD)
                const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;
                
                // 주말(토, 일)이 아니고 공휴일이 아니면 영업일로 간주
                if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                    businessDays--;
                }
            }

            return date;
        }

        function getDateAfterDays(days) {
          const today = new Date(); // 오늘 날짜를 가져옵니다.
          today.setDate(today.getDate() + days); // 현재 날짜에 days(3일)를 더합니다.

          const year = today.getFullYear();
          const month = String(today.getMonth() + 1).padStart(2, '0'); // 월은 0부터 시작하므로 +1 필요
          const day = String(today.getDate()).padStart(2, '0'); // 일도 2자리로 포맷팅

          return `${year}-${month}-${day}`; // YYYY-MM-DD 형식으로 반환
        }

        jQuery(function($) {
            
            /*
            $('#od_hope_date_print').pignoseCalendar({
                lang: 'ko',
                disabledWeekdays: [0, 6], // SUN (0), SAT (6)
                disabledDates: holidays,
                minDate: getDateAfterDays(<?php echo (int) get_subs_option('su_hope_date_after'); ?>),
                maxDate: getDateAfterDays(<?php echo (int) get_subs_option('su_hope_date_after') + 30; ?>)
            });
            */
            
            var g5_yymmdd = "<?php echo G5_TIME_YMD; ?>";
            
            var $od_hope_date_print = $("#od_hope_date_print");
            $od_hope_date_print.datepicker({
                defaultDate: g5_yymmdd,
                dateFormat: "yy-mm-dd",
                inline: true,
                yearRange: "c-99:c+99",
			    beforeShowDay: function(date){      // 토요일 일요일 제외
                    const dayOfWeek = date.getDay(); // 0: 일요일, 6: 토요일
                    const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

                    // 🔒 주말(토, 일) 또는 공휴일이면 비활성화 (false 반환)
                    if (dayOfWeek === 0 || dayOfWeek === 6 || holidays.includes(formattedDate)) {
                        return [false, 'ui-state-disabled', '공휴일 또는 주말입니다.'];
                    }
                    
                    // 🔓 그 외의 날짜는 활성화
                    return [true, '', ''];
                },
                onSelect: function(dateText, inst) {
                    console.log(dateText, inst);
                    change_hope_date_val();
                    
                    $("#od_hope_date").val(dateText);
                    calculate_next_delivery_date();
                },
                // minDate: "+<?php echo (int) get_subs_option('su_hope_date_after'); ?>d",
                // maxDate: "+<?php echo (int) get_subs_option('su_hope_date_after') + 30; ?>d"
                minDate: new Date("<?php echo getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after')); ?>"),
                maxDate: new Date("<?php echo getBusinessDaysNext(G5_TIME_YMD, (int) get_subs_option('su_hope_date_after') + 30); ?>")
            });
            
            function change_hope_date_val() {
                var before_pay_date = "<?php echo (int) get_subs_option('su_auto_payment_lead_days'); ?>";
                
                if (before_pay_date && parseInt(before_pay_date) > 0) {
                    
                    setTimeout(function(){
                        var od_hope_date_print = $od_hope_date_print.datepicker("getDate");
                        
                        if (od_hope_date_print) {
                            
                            var resultDate = getBusinessDaysBefore(new Date(od_hope_date_print), parseInt(before_pay_date));
                            
                            // alert(resultDate);
                            
                            var daysOfWeek = ['일', '월', '화', '수', '목', '금', '토'];
                            var year = resultDate.getFullYear();
                            var month = resultDate.getMonth() + 1; // 월은 0부터 시작하므로 +1
                            var date = resultDate.getDate();
                            var dayOfWeek = daysOfWeek[resultDate.getDay()]; // 요일 가져오기

                            var formattedDate1 = `${month}월 ${date}일 (${dayOfWeek})`;
                            var formattedDate2 = `${year}년 ${month}월 ${date}일 (${dayOfWeek})`;
                            
                            jQuery(".set_pay_date").text(formattedDate1);
                            jQuery(".before_pay_date_tr").show();
                        }
                    }, 100);
                }
            }
            
            change_hope_date_val();

        });
        
        $(".sit_btn_subscription").on("click", function(e){
            e.preventDefault();
            
            // 1. 입력값 가져오기
            const deliveryCycle = $("#od_subscription_select_data").val() || $("input[name='od_subscription_select_data']:checked").val();
            const usageCount = $("#od_subscription_select_number").val()  || $("input[name='od_subscription_select_number']:checked").val();
            const hopeDeliveryDate = $("#od_hope_date").val();
            
            // 2. 유효성 검사 (선택 사항)
            if (!deliveryCycle) {
                alert("배송주기를 선택해주세요.");
                return;
            }
            if (!usageCount) {
                alert("이용횟수를 선택해주세요.");
                return;
            }
            if (!hopeDeliveryDate) {
                alert("희망배송일을 입력해주세요.");
                return;
            }
            
            // 3. 숨겨진 폼에 값 할당
            document.getElementById("hidden_delivery_cycle").value = deliveryCycle;
            document.getElementById("hidden_usage_count").value = usageCount;
            document.getElementById("hidden_hope_delivery_date").value = hopeDeliveryDate;
        
            $("form[name='fitem']").submit();
        });
    <?php } ?>
    
    <?php if (get_subs_option('su_chk_user_delivery')) { ?>
    jQuery(function($){
        $("input#od_subscription_select_data").on('input', function() {
            var $this = $(this),
                $this_val = parseInt($this.val()),
                this_length = $this.val().length,
                ml = parseInt($this.attr("maxlength"));
            
            // 입력 값이 비어있거나 1보다 작은 값이면 1로 설정
            if (isNaN($this_val) || $this_val < 1 || 365 < $this_val) {
                $this_val = "<?php echo get_subs_option('su_user_delivery_default_day'); ?>";
                $(this).val($this_val);
            }
            
            calculate_next_delivery_date();
        });
    });
    <?php } else { ?>
        
        $(document).on("click", "input[name='od_subscription_select_data']", function(e) {
            
            calculate_next_delivery_date();
            
        });
        
    <?php } ?>
    
    function getNextBusinessDay(date) {
        // date: 기준 날짜 (Date 객체)
        // holidays: 공휴일 배열 (YYYY-MM-DD 형식의 문자열 배열)

        // 날짜 객체로 변환 (입력값 검사)
        if (!(date instanceof Date)) {
            date = new Date(date);
        }

        while (true) {
            date.setDate(date.getDate() + 1); // 하루 앞으로 이동
            const dayOfWeek = date.getDay(); // 요일 (0: 일요일, 6: 토요일)

            // 날짜 포맷 (YYYY-MM-DD)
            const formattedDate = `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(date.getDate()).padStart(2, '0')}`;

            // 주말(토, 일)이 아니고 공휴일이 아니면 다음 영업일로 간주
            if (dayOfWeek !== 0 && dayOfWeek !== 6 && !holidays.includes(formattedDate)) {
                break;
            }
        }

        return date;
    }
    
    // 다음 예상 발송일 계산
    function calculate_next_delivery_date() {
        
        if (!$("#od_hope_date").length) {   // 희망배송일이 없으면 리턴
            return false;
        }
        
        if (! jQuery("#od_hope_date").val()) {
            jQuery("#od_hope_date").val($.datepicker.formatDate('yy-mm-dd', $("#od_hope_date_print").datepicker( "getDate" )));
        }
        
        var $od_subscription_select_data = jQuery("#od_subscription_select_data").val() || jQuery("input[name='od_subscription_select_data']:checked").val(),
            $od_subscription_select_number = jQuery("#od_subscription_select_number").val(),
            $od_hope_date_print = $("#od_hope_date").val();
        
        if ($od_subscription_select_number && $od_subscription_select_number < 2) {     // 이용횟수가 1회이면 리턴
            return false;
        }
        
        var $next_el = $('.jquery-pg-datepicker .next-delivery-date-el').length ? $(".jquery-pg-datepicker .next-delivery-date-el") : $('<div class="next-delivery-date-el"></div>').appendTo(".jquery-pg-datepicker");
        
        // 기준 날짜 계산
        let baseDate = new Date($od_hope_date_print);
        
        if (typeof $od_subscription_select_data === 'undefined') {
            return false;
        }
        
        console.log( $od_subscription_select_data );
        
        if ($od_subscription_select_data && $od_subscription_select_data.includes("||")) {
            
            let [no, plus, interval] = $od_subscription_select_data.split("||");
            
            interval = interval || "day";
            plus = Math.abs(parseInt(plus, 10)) || 1;

            let isCheckBefore = false;
            
            switch (interval) {
                case "day":
                    baseDate.setDate(baseDate.getDate() + plus);
                    break;
                case "week":
                    baseDate.setDate(baseDate.getDate() + plus * 7);
                    break;
                case "month":
                    baseDate.setMonth(baseDate.getMonth() + plus);
                    isCheckBefore = true;
                    break;
                case "year":
                    baseDate.setFullYear(baseDate.getFullYear() + plus);
                    isCheckBefore = true;
                    break;
                default:
                    throw new Error(`Unknown billing interval: ${interval}`);
            }
            
            // let formattedDate = baseDate.toISOString().slice(0, 19).replace("T", " ");

            // return isCheckBefore ? getBusinessDaysBefore(formattedDate) : getBusinessDaysNext(formattedDate);
            
            // const nextDeliveryDate = getNextBusinessDay(formattedDate, holidays);
            
        } else {
            
            baseDate.setDate(baseDate.getDate() + parseInt($od_subscription_select_data)); // 몇일 이후 날짜 계산
            
        }
        
        const nextDeliveryDate = getNextBusinessDay(baseDate, holidays);
        
        console.log(nextDeliveryDate);
        
        $next_el.html("다음 예상 배송일 : " + nextDeliveryDate.toISOString().slice(0, 10));
            
        // $next_el.html("다음 예상 배송일 : " + nextDeliveryDate.toISOString().slice(0, 10));
        
        /*
        baseDate.setDate(baseDate.getDate() + parseInt($od_subscription_select_data)); // 몇일 이후 날짜 계산

        const nextDeliveryDate = getNextBusinessDay(baseDate, holidays);
        
        // 결과 출력
        console.log('기준 날짜:', baseDate.toISOString().slice(0, 10));
        console.log('다음 배송일:', nextDeliveryDate.toISOString().slice(0, 10));

        $next_el.html("다음 예상 배송일 : " + nextDeliveryDate.toISOString().slice(0, 10));
        
        */
        
    }
    
</script>
<?php /* 2017 리뉴얼한 테마 적용 스크립트입니다. 기존 스크립트를 오버라이드 합니다. */ ?>
<script src="<?php echo G5_JS_URL; ?>/shop.override.js"></script>
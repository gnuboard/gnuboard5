<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

add_javascript('<script src="'.G5_JS_URL.'/jquerymodal/jquery.modal.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/jquerymodal/jquery.modal.min.css">', 10);

$g5['title'] = '주문상세내역';
include_once('./_head.php');
?>

<!-- 주문상세내역 시작 { -->
<div id="sod_fin">
    <div id="sod_fin_no">구독번호 <strong><?php echo $od_id; ?></strong></div>
    <section id="sod_fin_list">
        <h2>구독하신 상품</h2>

        <?php
        $st_count1 = $st_count2 = 0;
        $custom_cancel = false;
        
        /*
        $sql = " select it_id, it_name, ct_send_cost, it_sc_type
                    from {$g5['g5_shop_cart_table']}
                    where od_id = '$od_id'
                    group by it_id
                    order by ct_id ";
        $result = sql_query($sql);
        */
        $result_row = sql_bind_select_array($g5['g5_subscription_cart_table'], 'it_id, it_name, ct_send_cost, it_sc_type',
        array('od_id'=>$od_id),
        array('groupBy'=>'it_id', 'orderBy'=>'ct_id')
        );
        ?>
        
        <div class="tbl_head03 tbl_wrap">
			<table>
	            <thead>
	            <tr class="th_line">
	            	<th scope="col" id="th_itname">상품명</th>
	                <th scope="col" id="th_itqty">총수량</th>
	                <th scope="col" id="th_itprice">판매가</th>
	                <th scope="col" id="th_itpt">포인트</th>
	                <th scope="col" id="th_itsd">배송비</th>
	                <th scope="col" id="th_itsum">소계</th>
	                <th scope="col" id="th_itst">상태</th>
	            </tr>
	            </thead>
	            <tbody>
	            <?php
	            //for($i=0; $row=sql_fetch_array($result); $i++) {
                $i = 0;
                foreach($result_row as $row) {
	                $image = get_it_image($row['it_id'], 55, 55);
	                
                    /*
	                $sql = " select ct_id, it_name, ct_option, ct_qty, ct_price, ct_point, ct_status, io_type, io_price
	                            from {$g5['g5_shop_cart_table']}
	                            where od_id = '$od_id'
	                              and it_id = '{$row['it_id']}'
	                            order by io_type asc, ct_id asc ";
	                $res = sql_query($sql);
	                $rowspan = sql_num_rows($res) + 1;
                    */
                    
                    $res = sql_bind_select($g5['g5_subscription_cart_table'], 'ct_id, it_name, ct_option, ct_qty, ct_price, ct_point, ct_status, io_type, io_price',
                    array('od_id'=>$od_id, 'it_id'=>$row['it_id']),
                    array('orderBy'=>'io_type, ct_id')
                    );
                    $rowspan = sql_num_rows($res) + 1;
	
	                // 합계금액 계산
                    /*
	                $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
	                                SUM(ct_qty) as qty
	                            from {$g5['g5_subscription_cart_table']}
	                            where it_id = '{$row['it_id']}'
	                              and od_id = '$od_id' ";
	                $sum = sql_fetch($sql);
                    */
                    
                    $sum = sql_bind_select_fetch($g5['g5_subscription_cart_table'], 'SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price, SUM(ct_qty) as qty',
                    array('od_id'=>$od_id, 'it_id'=>$row['it_id'])
                    );
	
	                // 배송비
	                switch($row['ct_send_cost'])
	                {
	                    case 1:
	                        $ct_send_cost = '착불';
	                        break;
	                    case 2:
	                        $ct_send_cost = '무료';
	                        break;
	                    default:
	                        $ct_send_cost = '선불';
	                        break;
	                }
	
	                // 조건부무료
	                if($row['it_sc_type'] == 2) {
	                    $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $od_id);
	
	                    if($sendcost == 0)
	                        $ct_send_cost = '무료';
	                }
	
	                for($k=0; $opt=sql_fetch_array($res); $k++) {
	                    if($opt['io_type'])
	                        $opt_price = $opt['io_price'];
	                    else
	                        $opt_price = $opt['ct_price'] + $opt['io_price'];
	
	                    $sell_price = $opt_price * $opt['ct_qty'];
	                    $point = $opt['ct_point'] * $opt['ct_qty'];
	
	                    if($k == 0) {
	            ?>
	            <?php } ?>
	            <tr>
	                <td headers="th_itopt" class="td_prd">
	                	<div class="sod_img"><a href="<?php echo subscription_item_url($row['it_id']); ?>"><?php echo $image; ?></a></div>
	                	<div class="sod_name">
		                	<a href="<?php echo subscription_item_url($row['it_id']); ?>"><?php echo $row['it_name']; ?></a><br>
		                	<div class="sod_opt"><?php echo get_text($opt['ct_option']); ?></div>
	                	</div>
	                </td>
	                <td headers="th_itqty" class="td_mngsmall"><?php echo number_format($opt['ct_qty']); ?></td>
	                <td headers="th_itprice" class="td_numbig text_right"><?php echo number_format($opt_price); ?></td>
	                <td headers="th_itpt" class="td_numbig text_right"><?php echo number_format($point); ?></td>
	                <td headers="th_itsd" class="td_dvr"><?php echo $ct_send_cost; ?></td>
	                <td headers="th_itsum" class="td_numbig text_right"><?php echo number_format($sell_price); ?></td>
	                <td headers="th_itst" class="td_mngsmall"><?php echo $opt['ct_status']; ?></td>
	            </tr>
	            <?php
	                    $tot_point       += $point;
	
	                    $st_count1++;
	                    if($opt['ct_status'] == '주문')
	                        $st_count2++;
	                }
                    
                    $i++;
	            }
	
	            // 주문 상품의 상태가 모두 주문이면 고객 취소 가능
	            if($st_count1 > 0 && $st_count1 == $st_count2)
	                $custom_cancel = true;
	            ?>
	            </tbody>
            </table>
        </div>
        
        <div id="sod_sts_wrap">
            <span class="sound_only">상품 상태 설명</span>
            <button type="button" id="sod_sts_explan_open" class="btn_frmline">상태설명보기</button>
            <div id="sod_sts_explan">
                <dl id="sod_fin_legend">
                    <dt>주문</dt>
                    <dd>주문이 접수되었습니다.
                    <dt>입금</dt>
                    <dd>입금(결제)이 완료 되었습니다.
                    <dt>준비</dt>
                    <dd>상품 준비 중입니다.
                    <dt>배송</dt>
                    <dd>상품 배송 중입니다.
                    <dt>완료</dt>
                    <dd>상품 배송이 완료 되었습니다.
                </dl>
                <button type="button" id="sod_sts_explan_close" class="btn_frmline">상태설명닫기</button>
            </div>
        </div>
    </section>
    <div class="sod_left">
        <h2>결제/배송 정보</h2>
        <?php
        // 총계 = 주문상품금액합계 + 배송비 - 상품할인 - 결제할인 - 배송비할인
        $tot_price = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2']
                        - $od['od_cart_coupon'] - $od['od_coupon'] - $od['od_send_coupon']
                        - $od['od_cancel_price'];

        $receipt_price  = $od['od_receipt_price']
                        + $od['od_receipt_point'];
        $cancel_price   = $od['od_cancel_price'];

        $misu = true;
        $misu_price = $tot_price - $receipt_price;

        if ($misu_price == 0 && ($od['od_cart_price'] > $od['od_cancel_price'])) {
            $wanbul = " (완불)";
            $misu = false; // 미수금 없음
        }
        else
        {
            $wanbul = display_price($receipt_price);
        }

        // 결제정보처리
        if($od['od_receipt_price'] > 0)
            $od_receipt_price = display_price($od['od_receipt_price']);
        else
            $od_receipt_price = '아직 입금되지 않았거나 입금정보를 입력하지 못하였습니다.';

        $app_no_subj = '';
        $disp_bank = true;
        $disp_receipt = false;
        if($od['od_settle_case'] == '신용카드' || $od['od_settle_case'] == 'KAKAOPAY' || is_inicis_order_pay($od['od_settle_case']) ) {
            $app_no_subj = '승인번호';
            $app_no = $od['od_app_no'];
            $disp_bank = false;
            $disp_receipt = true;
        }
        ?>

        <section id="sod_fin_orderer">
            <h3>주문하신 분</h3>

            <div class="tbl_head01 tbl_wrap">
                <table>

                <tbody>
                <tr>
                    <th scope="row">이 름</th>
                    <td><?php echo get_text($od['od_name']); ?></td>
                </tr>
                <tr>
                    <th scope="row">전화번호</th>
                    <td><?php echo get_text($od['od_tel']); ?></td>
                </tr>
                <tr>
                    <th scope="row">핸드폰</th>
                    <td><?php echo get_text($od['od_hp']); ?></td>
                </tr>
                <tr>
                    <th scope="row">주 소</th>
                    <td><?php echo get_text(sprintf("(%s%s)", $od['od_zip1'], $od['od_zip2']).' '.print_address($od['od_addr1'], $od['od_addr2'], $od['od_addr3'], $od['od_addr_jibeon'])); ?></td>
                </tr>
                <tr>
                    <th scope="row">E-mail</th>
                    <td><?php echo get_text($od['od_email']); ?></td>
                </tr>
                </tbody>
                </table>
            </div>
        </section>

        <section id="sod_fin_receiver">
            <h3>받으시는 분</h3>

            <div class="tbl_head01 tbl_wrap">
                <table>
          
                <tbody>
                <tr>
                    <th scope="row">이 름</th>
                    <td><?php echo get_text($od['od_b_name']); ?></td>
                </tr>
                <tr>
                    <th scope="row">전화번호</th>
                    <td><?php echo get_text($od['od_b_tel']); ?></td>
                </tr>
                <tr>
                    <th scope="row">핸드폰</th>
                    <td><?php echo get_text($od['od_b_hp']); ?></td>
                </tr>
                <tr>
                    <th scope="row">주 소</th>
                    <td><?php echo get_text(sprintf("(%s%s)", $od['od_b_zip1'], $od['od_b_zip2']).' '.print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon'])); ?></td>
                </tr>
                <?php
                // 희망배송일을 사용한다면
                if ($default['de_hope_date_use'])
                {
                ?>
                <tr>
                    <th scope="row">희망배송일</th>
                    <td><?php echo substr($od['od_hope_date'],0,10).' ('.get_yoil($od['od_hope_date']).')' ;?></td>
                </tr>
                <?php }
                if ($od['od_memo'])
                {
                ?>
                <tr>
                    <th scope="row">전하실 말씀</th>
                    <td><?php echo conv_content($od['od_memo'], 0); ?></td>
                </tr>
                <?php } ?>
                </tbody>
                </table>
            </div>
        </section>

        <section id="sod_fin_dvr">
            <h3>정기구독정보</h3>

<?php
    $opt = subscription_serial_decode($od['od_subscription_selected_data']);
    $use = subscription_serial_decode($od['od_subscription_selected_number']);
    
    $opt_print = (isset($opt['opt_print']) && $opt['opt_print']) ? $opt['opt_print'] : $opt['opt_input'].' 일마다';

    if ($opt['opt_input'] || $opt['opt_date_format']) {
        $opt_print = str_replace("{입력}", $opt['opt_input'], $opt_print);
        $opt_print = str_replace("{결제주기}", get_hangul_date_format($opt['opt_date_format']), $opt_print);
    }
    
    $use_print = (isset($use['use_print']) && $use['use_print']) ? $use['use_print'] : $use['use_input'].' 일마다';

    if ($use['use_input']) {
        $use_print = str_replace("{입력}", $use['use_input'], $use_print);
    }
    
    $cards = sql_bind_select_fetch($g5['g5_subscription_mb_cardinfo_table'], '*', array('mb_id'=>$member['mb_id'], 'ci_id'=>$od['ci_id']));
?>
            <div class="tbl_head01 tbl_wrap">
                <table>
	                <tbody>
                    <tr>
	                    <th scope="row">결제카드</th>
	                    <td>
                            <?php if ($cards) { ?>
                                <?php echo $cards['od_card_name']; ?> (<?php echo $cards['card_mask_number']; ?>)
                            <?php } else { ?>
                                카드정보가 지워졌거나 카드정보가 없습니다.
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
	                    <th scope="row">배송주기</th>
	                    <td>
                            <?php echo $opt_print; ?>
                        </td>
                    </tr>
                    <tr>
	                    <th scope="row">이용횟수</th>
	                    <td>
                            <?php echo $use_print; ?>
                        </td>
                    </tr>
	                <?php if ($od['od_hope_date']) { ?>
	                <tr>
	                    <th scope="row">첫 희망배송일</th>
	                    <td><?php echo date('Y-m-d', strtotime($od['od_hope_date'])); ?></td>
	                </tr>
                    <?php } ?>
                    <tr>
	                    <th scope="row">다음 결제일</th>
	                    <td><?php echo date('Y-m-d', strtotime($od['next_billing_date'])); ?></td>
                    </tr>
                    <tr>
	                    <th scope="row">다음 결제가격<br>(예정)</th>
	                    <td>
                            <?php echo number_format(subscription_order_pay_price($od_id)); ?>원
                            <br>
                            <span class="help">결제가격은 장바구니 상태에 따라 변동될수 있습니다.</span>
                        </td>
                    </tr>
                    <tr>
	                    <th scope="row">다음 배송일</th>
	                    <td>
                            몇 회차<span class="set_pay_date"><?php echo get_next_delivery_date($od); ?></span>
                            등록된 수단으로 도착 <?php echo (int) get_subs_option('su_auto_payment_lead_days'); ?>영업일 전 자동결제 됩니다.
                        </td>
                    </tr>
	                </tbody>
                </table>
            </div>
        </section>
        
        <?php
        
        $pay_rows = sql_bind_select_array($g5['g5_subscription_pay_table'], '*', array('od_id'=>$od_id), array('orderBy'=>'pay_id', 'orderType'=>'DESC'));
        
        ?>
        
        <section id="sod_fin_dvr">
            <h3>정기결제내역</h3>
            
            <div class="tbl_head01 tbl_wrap">
                <table>
                    <?php if ($pay_rows) { ?>
                    <tr>
                        <th>회차</th>
                        <th>결제PG사</th>
                        <th>결제된날짜</th>
                        <th>결제금액</th>
                        <th>보기</th>
                    </tr>
                    <?php foreach($pay_rows as $key=>$v) { ?>
                    <tr>
                        <td><?php echo $v['py_round_no']; ?></td>
                        <td><?php echo $v['py_pg']; ?></td>
                        <td><?php echo $v['py_receipt_time']; ?></td>
                        <td><?php echo display_price($v['py_cart_price'] + $v['py_send_cost'] + $v['py_send_cost2']); ?></td>
                        <td><a href="#ex_modal1" rel="modal:open" data-payid="<?php echo $v['pay_id']; ?>" class="mng_mod btn btn_02">상세보기</a></td>
                    </tr>
                    <?php } // end for ?>
                    <?php } else { ?>
	                <tr>
	                    <td class="empty_table">아직 정기결제내역이 없습니다.</td>
	                </tr>
                    <?php } ?>
                </table>
            </div>
        </section>
        
        <section id="sod_fin_dvr">
            <h3>배송정보</h3>

            <div class="tbl_head01 tbl_wrap">
                <table>
	                <tbody>
	                <?php if ($od['od_invoice'] && $od['od_delivery_company']) { ?>
	                <tr>
	                    <th scope="row">배송회사</th>
	                    <td><?php echo $od['od_delivery_company']; ?> <?php echo get_delivery_inquiry($od['od_delivery_company'], $od['od_invoice'], 'dvr_link'); ?></td>
	                </tr>
	                <tr>
	                    <th scope="row">운송장번호</th>
	                    <td><?php echo $od['od_invoice']; ?></td>
	                </tr>
	                <tr>
	                    <th scope="row">배송일시</th>
	                    <td><?php echo $od['od_invoice_time']; ?></td>
	                </tr>
	                <?php } else { ?>
	                <tr>
	                    <td class="empty_table">아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</td>
	                </tr>
	                <?php } ?>
	                </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="sod_right">
        <ul id="sod_bsk_tot2">
            <li class="sod_bsk_dvr">
                <span>주문총액</span>
                <strong><?php echo number_format($od['od_cart_price']); ?> 원</strong>
            </li>
            <?php if($od['od_cart_coupon'] > 0) { ?>
            <li class="sod_bsk_dvr">
                <span>개별상품 쿠폰할인</span>
                <strong><?php echo number_format($od['od_cart_coupon']); ?> 원</strong>
            </li>
            <?php } ?>
            <?php if($od['od_coupon'] > 0) { ?>
            <li class="sod_bsk_dvr">
                <span>주문금액 쿠폰할인</span>
                <strong><?php echo number_format($od['od_coupon']); ?> 원</strong>
            </li>
            <?php } ?>
            <?php if ($od['od_send_cost'] > 0) { ?>
            <li class="sod_bsk_dvr">
                <span>배송비</span>
                <strong><?php echo number_format($od['od_send_cost']); ?> 원</strong>
            </li>
            <?php } ?>
            <?php if($od['od_send_coupon'] > 0) { ?>
            <li class="sod_bsk_dvr">
                <span>배송비 쿠폰할인</span>
                <strong><?php echo number_format($od['od_send_coupon']); ?> 원</strong>
            </li>
            <?php } ?>
            <?php if ($od['od_send_cost2'] > 0) { ?>
            <li class="sod_bsk_dvr">
                <span>추가배송비</span>
                <strong><?php echo number_format($od['od_send_cost2']); ?> 원</strong>
            </li>
            <?php } ?>
            <?php if ($od['od_cancel_price'] > 0) { ?>
            <li class="sod_bsk_dvr">
                <span>취소금액</span>
                <strong><?php echo number_format($od['od_cancel_price']); ?> 원</strong> 
            </li>
            <?php } ?>
            <li class="sod_bsk_cnt">
                <span>총계</span>
                <strong><?php echo number_format($tot_price); ?> 원</strong>
            </li>
            <li class="sod_bsk_point">
                <span>적립포인트</span>
                <strong><?php echo number_format($tot_point); ?> 점</strong>
            </li>
            
            <li class="sod_fin_tot"><span>총 구매액</span><strong><?php echo display_price($tot_price); ?></strong></li>
            <?php
            if ($misu_price > 0) {
            echo '<li class="sod_fin_tot">';
            echo '<span>미결제액</span>'.PHP_EOL;
            echo '<strong>'.display_price($misu_price).'</strong>';
            echo '</li>';
            }
            ?>
            <li id="alrdy" class="sod_fin_tot">
            	<span>결제액</span>
                <strong><?php echo $wanbul; ?></strong>
                <?php if( $od['od_receipt_point'] ){    //포인트로 결제한 내용이 있으면 ?>
                <div>
                    <p><span class="title">포인트 결제</span><?php echo number_format($od['od_receipt_point']); ?>점</p>
                    <p><span class="title">실결제</span><?php echo number_format($od['od_receipt_price']); ?>원</p>
                </div>
                <?php } ?>
            </li>
        </ul>
        
        <section id="sod_fin_pay">
            <h3>결제정보</h3>
            <ul>
	            <li>
	                <strong>구독번호</strong>
	                <span><?php echo $od_id; ?></span>
	            </li>
	            <li>
	                <strong>구독일시</strong>
	                <span><?php echo $od['od_time']; ?></span>
	            </li>
	            <li>
	                <strong>결제방식</strong>
	                <span><?php echo get_subscription_pay_name_replace($od['od_settle_case'], $od, 1); ?></span>
	            </li>
	            <li>
	                <strong>구독금액</strong>
	                <span><?php echo $od_receipt_price; ?></span>
	            </li>
            </ul>
        </section>

        <section id="sod_fin_cancel">
            <?php
            // 취소한 내역이 없다면
            if ($cancel_price == 0) {
                if ($custom_cancel) {
            ?>
            <button type="button" class="sod_fin_c_btn">구독 취소하기</button>
			<div id="sod_cancel_pop">	
	            <div id="sod_fin_cancelfrm">
	            	<h2>구독취소</h2>
	                <form method="post" action="./orderinquirycancel.php" onsubmit="return fcancel_check(this);">
	                <input type="hidden" name="od_id" value="<?php echo $od['od_id']; ?>">
	                <input type="hidden" name="token" value="<?php echo $token; ?>">
	
	                <label for="cancel_memo" class="sound_only">취소사유</label>
	                <input type="text" name="cancel_memo" id="cancel_memo" required class="frm_input required" size="40" maxlength="100" placeholder="취소사유">
	                <input type="submit" value="확인" class="btn_frmline">
	                </form>
	                <button class="sod_cls_btn"><span class="sound_only">닫기</span><i class="fa fa-times" aria-hidden="true"></i></button>
		        </div>
		        <div class="sod_fin_bg"></div>
			</div>
			<script>	
			$(function (){
				$(".sod_fin_c_btn").on("click", function() {
			        $("#sod_cancel_pop").show();
			    });
			    $(".sod_cls_btn").on("click", function() {
			        $("#sod_cancel_pop").hide();
			    });		
			});
			</script>

            <?php
                }
            } else {
            ?>
            <p>구독 취소 내역이 있습니다.</p>
            <?php } ?>
        </section>
    </div>

</div>
<!-- } 주문상세내역 끝 -->

<?php // 정기결제 상세보기 모달 시작 ?>
<div id="ex_modal1" class="modal">
    <div class="modal_contents">
    </div>
</div>

<script>
jQuery(function($) {
    $("#sod_sts_explan_open").on("click", function(e) {
        var $explan = $("#sod_sts_explan");
        if($explan.is(":animated"))
            return false;

        if($explan.is(":visible")) {
            $explan.slideUp(200);
            $("#sod_sts_explan_open").text("상태설명보기");
        } else {
            $explan.slideDown(200);
            $("#sod_sts_explan_open").text("상태설명닫기");
        }
    });

    $("#sod_sts_explan_close").on("click", function(e) {
        var $explan = $("#sod_sts_explan");
        if($explan.is(":animated"))
            return false;

        $explan.slideUp(200);
        $("#sod_sts_explan_open").text("상태설명보기");
    });
    
    $(document).on("click", ".mng_mod.btn", function(e) {
        e.preventDefault();
        
        var pay_id = $(this).attr("data-payid"),
            oDate = new Date(),
            action_url = g5_url + "/subscription/ajax.subscription_pay.php",
            formData = "pay_id="+pay_id;
        
        var ajax_var = $.ajax({
            type:"POST",
            url: action_url + "?t="+ oDate.getTime(),
            data:formData,
            dataType   : 'json', // xml, html, script, json
            cache: false,
            success:function(data, status, xhr){
                if (data.error){ //실패
                    /*
                    var error_msg = data.msg;
                        error_msg = error_msg.replace(/\\n/g, "\n")
                                  .replace(/\\r/g, "\r");

                    alert( error_msg );
                    sir_cm.fn_hide_loading();
                    sir_cm.waiting = false;
                    */
                    
                } else {    //성공
                    
                    console.log(data);
                    /*
                    var obj = {
                        msg : sir_cm.cm_success_msg,
                        type : "success"
                    }
                    sir_cm.fn_load_comment( data.url, obj );

                    if( $(".client-info button[id^='request']").length ){
                        $(".client-info button[id^='request']").attr("data-view", "1");
                    }

                    $("#fcomment").trigger("request_reset", 'write');
                    */
                    
                    // .content 요소 선택
                    var contentEl = $(".modal_contents");

                    // 새로운 ul 요소 생성
                    var innerEl = $("<div class='user-subscription-pay'></div>"),
                        ulEl = $("<ul class='user-subscription-inner'></ul>");

                    // JSON 데이터 순회하며 li 요소 생성 후 ul에 추가
                    $.each(data, function(key, value) {
                        // ulEl.append("<li><strong>" + key + ":</strong> " + value + "</li>");
                    });
                    
                    /*
                    var keys = {
                        "py_receipt_time": "결제시간",
                        "영수증출력": "",
                        "py_receipt_price": "결제금액",
                        };
                    */
                    
                    var html = "",
                        cartHTML = "";
                    
                    html += "<h3>주문하신 분</h3>";
                    html += "<li><span class='th'>이름 :</span> " + data.py_name + "</li>";
                    html += "<li><span class='th'>핸드폰 :</span> " + data.py_hp + "</li>";
                    
                    if (data.py_test) {
                        html += "<li class='is_pay_test'>이 결제는 테스트로 결제되었습니다.</li>";
                    }
                    
                    html += "<h3>받으시는 분</h3>";
                    html += "<li><span class='th'>이름 :</span> " + data.py_b_name + "</li>";
                    html += "<li><span class='th'>전화번호 :</span> " + data.py_b_tel + "</li>";
                    html += "<li><span class='th'>핸드폰 :</span> " + data.py_b_hp + "</li>";
                    html += "<li><span class='th'>주소 :</span> " + data.py_b_full_address + "</li>";
                    
                    html += "<h3>배송정보</h3>";
                    if (data.py_invoice && data.py_delivery_company) {
                        html += "<li><span class='th'>배송회사 :</span> " + data.py_delivery_full_info + "</li>";
                        html += "<li><span class='th'>운송장번호 :</span> " + data.py_invoice + "</li>";
                        html += "<li><span class='th'>배송일시 :</span> " + data.py_invoice_time + "</li>";
                    } else {
                        html += "<li class='is_not_delivery'>아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</li>";
                    }

                    html += "<li><span class='th'>주문총액 :</span> " + data.py_cart_price + "</li>";
                    if (data.py_send_cost) {
                        html += "<li><span class='th'>배송비 :</span> " + data.py_send_cost + "</li>";
                    }
                    html += "<li><span class='th'>총계 :</span> " + data.py_tot_price + "</li>";
                    
                    html += "<h3>결제정보</h3>";
                    html += "<li><span class='th'>주문번호 :</span> " + data.subscription_pg_id + "</li>";
                    html += "<li><span class='th'>주문일시 :</span> " + data.py_time + "</li>";
                    // html += "<li><span class='th'>결제방식 :</span> " + data.py_settle_case + "</li>";
                    html += "<li><span class='th'>결제카드 :</span> " + data.py_settle_case + "</li>";
                    html += "<li><span class='th'>결제금액 :</span> " + data.py_receipt_price + "</li>";
                    html += "<li><span class='th'>결제일시 :</span> " + data.py_receipt_time + "</li>";
                    html += "<li><span class='th'>승인번호 :</span> " + data.py_app_no + "</li>";
                    
                    if (data.py_receipt_url) {
                        html += "<li><span class='th'>영수증 :</span> <a href='" + data.py_receipt_url + "' target='_blank' class='subscription-receipt-view'>영수증클릭</a></li>";
                    }
                    
                    ulEl.append(html);
                    
                    console.log(data.cart_infos);
                    
                    for (var i = 0; i < data.cart_infos.goods.length; i++) {
                        
                        console.log( data.cart_infos.it_options );
                        
                        var productName = data.cart_infos.goods[i],
                            productPrice = 0,
                            img = "";
                        
                        try {
                            img = data.cart_infos.image_urls[i].img;
                        } catch (error) {
                            img = "";
                        }
                        
                        var productOption = data.cart_infos.it_options[i][0].ct_option;
                        // var productPrice = data.cart_infos.it_options[i][0].tot_sell_price;
                        var pioPrice = data.cart_infos.it_options[i][0].io_price;
                        
                        // let optionsHtml = data.cart_infos.it_options.map(opt => `<div>${opt.option} (수량: ${opt.qty}, 가격: ${opt.price}원${opt.point ? `, 포인트: ${opt.point}` : ''})</div>`).join('');
                        
                        var optionsHtml = '';
                        
                        data.cart_infos.it_options[i].forEach(function(opt) {
                            
                            productPrice += parseInt(opt.opt_price);
                            
                            optionsHtml += '<div>' + opt.ct_option + ' (수량: ' + opt.ct_qty + ', 가격: ' + opt.opt_price + '원' + (opt.point ? ', 포인트: ' + opt.point : '') + ')</div>';
                        });
                        
                        productPrice = productPrice ? number_format(productPrice) : 0;
                        
                        cartHTML += `
                            <div class="product-item">
                                <div class="product-img">${img}</div>
                                <div class="product-info">
                                    <div class="product-name"><a href="#">${productName}</a></div>
                                    <div class="product-options">${optionsHtml}</div>
                                </div>
                                <div class="product-meta">
                                    <div>가격: ${productPrice}원</div>
                                </div>
                            </div>
                        `;
                        
                    }
                    
                    if (cartHTML) {
                        innerEl.append('<div class="product-list">' + cartHTML + '</div>');
                    }
                    
                    innerEl.append(ulEl);
                    
                    // 기존 .content 내부에 추가
                    contentEl.html(innerEl);
            
                }

            },
            error : function(request, status, error){
                //alert(sir_cm.cm_false_msg+request.responseText);
                //sir_cm.waiting = false;
            }
        })
        .always(function() {
            /*
            if(typeof(a[0].captcha_key) != 'undefined'){
                $(a[0]).find("#captcha_reload").trigger("click");
            }
            */
        });
            
    });
    
    $(document).on("click", ".subscription-receipt-view", function(e){
        e.preventDefault();
        
        var $href = $(this).attr("href");
        
        window.open($href, "winreceipt", "width=500,height=690,scrollbars=yes,resizable=yes");
    });
    
});

function fcancel_check(f)
{
    if(!confirm("구독을 정말 취소하시겠습니까?"))
        return false;

    var memo = f.cancel_memo.value;
    if(memo == "") {
        alert("취소사유를 입력해 주십시오.");
        return false;
    }

    return true;
}

</script>

<?php
include_once('./_tail.php');
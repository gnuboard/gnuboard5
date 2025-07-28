<?php
include_once('./_common.php');

// 정기구독은 회원만 구독이 가능합니다.
if (!$is_member) {
    goto_url(G5_BBS_URL . '/login.php?url=' . urlencode(G5_SUBSCRIPTION_URL . '/'. basename(__FILE__)));
}

$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';
$uid = isset($_REQUEST['uid']) ? clean_xss_tags($_REQUEST['uid']) : '';

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

add_javascript('<script src="' . G5_JS_URL . '/jquerymodal/jquery.modal.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="' . G5_JS_URL . '/jquerymodal/jquery.modal.min.css">', 10);

$tot_point = 0;

$sql = "SELECT * 
        FROM {$g5['g5_subscription_order_table']} 
        WHERE od_id = '$od_id'";

if ($is_member && !$is_admin) {
    $sql .= " AND mb_id = '" . $member['mb_id'] . "'";
}

$od = sql_fetch($sql);

if (! (isset($od['od_id']) && $od['od_id']) || (!$is_member && md5($od['od_id'] . $od['od_time'] . $od['od_ip']) != get_session('ss_orderview_uid'))) {
    alert("조회하실 주문서가 없습니다.", G5_SUBSCRIPTION_URL);
}

$is_od_update = updateSubscriptionItemIfChanged($od);

if ($is_od_update) {
    // 주문정보가 변경된 경우 다시 값을 가져온다.
    $od = get_subscription_order($od['od_id']);
}

$sql = "SELECT it_id, it_name, ct_send_cost, it_sc_type 
        FROM {$g5['g5_subscription_cart_table']} 
        WHERE od_id = '$od_id' 
        GROUP BY it_id 
        ORDER BY ct_id";
$results = sql_query($sql);
$result_row = sql_result_array($results);

$sql = "SELECT * 
        FROM {$g5['g5_subscription_pay_table']} 
        WHERE od_id = '$od_id' 
        ORDER BY pay_id ASC";
$results = sql_query($sql);

$subscription_pays = array();

for($i=0; $row=sql_fetch_array($results); $i++) {
    $subscription_pays[$i] = $row;
    
    $cards = get_customer_card_info($row);
    
    if ($cards) {
        $subscription_pays[$i]['card_txt'] = $cards['od_card_name'] . ' ('.$cards['card_mask_number'].')';
    } else {
        $subscription_pays[$i]['card_txt'] = '카드정보 없음';
    }
}

// 정기구독정보의 배송주기와 이용횟수 등을 가져옴
$crp = calculateRecurringPaymentDetails($od);

// 배송주기
$od_deliverys = $crp['deliverys'];
// 신청한 총 이용횟수 (숫자 + 글자)
$od_usages = $crp['usages'];
// 신청한 총 이용횟수 (숫자만)
$od_usage_count = $crp['usage_count'];
// 정기구독이 진행중이명 0, 끝났으면 1
$is_end_subscription = $crp['is_end_subscription'];
// 현재횟차
$current_cycle = $crp['current_cycle'];
// 현재횟차 (숫자 + 글자)
$current_cycle_str = $crp['current_cycle_str'];

$recipients_attr = $is_end_subscription ? ' disabled' : '';

// 구독신청 히스토리
$sql = "SELECT * FROM `{$g5['g5_subscription_order_history_table']}` 
        WHERE od_id = '{$od_id}' 
        ORDER BY hs_time DESC 
        LIMIT 30";
$result = sql_query($sql);
$hss = sql_result_array($result);

// 결제방법
$settle_case = $od['od_settle_case'];

if (G5_IS_MOBILE) {
    include_once(G5_MSUBSCRIPTION_PATH . '/orderinquiryview.php');
    return;
}

// 테마에 orderinquiryview.php 있으면 include
if (defined('G5_THEME_SUBSCRIPTION_PATH')) {
    $theme_inquiryview_file = G5_THEME_SUBSCRIPTION_PATH . '/orderinquiryview.php';
    if (is_file($theme_inquiryview_file)) {
        include_once($theme_inquiryview_file);
        return;
        unset($theme_inquiryview_file);
    }
}

$g5['title'] = '주문상세내역';
include_once('./_head.php');
?>
<!-- 주문상세내역 시작 { -->
<div id="sod_fin" class="member-subscription-views">
    <div id="sod_fin_no">구독번호 <strong><?php echo $od_id; ?></strong></div>
    <?php if ($od['od_test']) { ?>
    <div class="is-od-test">이 정기구독은 테스트로 등록되어, 실제로 결제되지 않습니다.</div>
    <?php } ?>
    <section id="sod_fin_list">
        <h2>구독하신 상품</h2>

        <?php
        $st_count1 = $st_count2 = 0;
        $custom_cancel = false;
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
                    $i = 0;
                    foreach ($result_row as $row) {
                        $image = get_it_image($row['it_id'], 55, 55);

                        $sql = "SELECT ct_id, it_name, ct_option, ct_qty, ct_price, ct_point, ct_status, io_type, io_price 
                            FROM {$g5['g5_subscription_cart_table']} 
                            WHERE od_id = '$od_id' 
                            AND it_id = '" . $row['it_id'] . "' 
                            ORDER BY io_type, ct_id";
                        $res = sql_query($sql);

                        $rowspan = sql_num_rows($res) + 1;

                        $sql = "SELECT SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) AS price, 
                                    SUM(ct_qty) AS qty 
                                FROM {$g5['g5_subscription_cart_table']} 
                                WHERE od_id = '$od_id' 
                                AND it_id = '" . $row['it_id'] . "'";
                        $sum = sql_fetch($sql);

                        // 배송비
                        switch ($row['ct_send_cost']) {
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
                        if ($row['it_sc_type'] == 2) {
                            $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $od_id);

                            if ($sendcost == 0)
                                $ct_send_cost = '무료';
                        }

                        for ($k = 0; $opt = sql_fetch_array($res); $k++) {
                            if ($opt['io_type'])
                                $opt_price = $opt['io_price'];
                            else
                                $opt_price = $opt['ct_price'] + $opt['io_price'];

                            $sell_price = $opt_price * $opt['ct_qty'];
                            $point = $opt['ct_point'] * $opt['ct_qty'];

                            if ($k == 0) {
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
                            if ($opt['ct_status'] == '주문')
                                $st_count2++;
                        }

                        $i++;
                    }

                    // 주문 상품의 상태가 모두 주문이면 고객 취소 가능
                    if ($st_count1 > 0 && $st_count1 == $st_count2)
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
                    <dt>취소</dt>
                    <dd>주문이 취소되었습니다.
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
            - $od['od_cart_coupon'] - $od['od_coupon'] - $od['od_send_coupon'];

        $receipt_price  = $od['od_receipt_price']
            + $od['od_receipt_point'];
        $cancel_price   = 0;

        $misu = true;
        $misu_price = $tot_price - $receipt_price;

        if ($misu_price == 0 && ($od['od_cart_price'] > 0)) {
            $wanbul = " (완불)";
            $misu = false; // 미수금 없음
        } else {
            $wanbul = display_price($receipt_price);
        }

        // 결제정보처리
        if ($od['od_receipt_price'] > 0)
            $od_receipt_price = display_price($od['od_receipt_price']);
        else
            $od_receipt_price = '아직 입금되지 않았거나 입금정보를 입력하지 못하였습니다.';

        $app_no_subj = '';
        $disp_bank = true;
        $disp_receipt = false;
        if ($od['od_settle_case'] == '신용카드' || $od['od_settle_case'] == 'KAKAOPAY' || is_inicis_order_pay($od['od_settle_case'])) {
            $app_no_subj = '승인번호';
            $app_no = $od['od_app_no'];
            $disp_bank = false;
            $disp_receipt = true;
        }
        ?>
        
        <form name="forderform" id="forderform" method="post" action="<?php echo G5_SUBSCRIPTION_URL.'/orderinquiry_update.php'; ?>" autocomplete="off">
        <input type="hidden" name="od_id" value="<?php echo get_text($od['od_id']); ?>" >
        <input type="hidden" name="uid" value="<?php echo get_text($uid); ?>" >
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
                            <th scope="row">연락처</th>
                            <td>
                            <?php echo get_text($od['od_tel']); ?>
                            <input type="hidden" name="od_hp" value="<?php echo get_text($od['od_hp']); ?>" >
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">주 소</th>
                            <td><?php echo get_text($od['od_zip'] . ' ' . print_address($od['od_addr1'], $od['od_addr2'], $od['od_addr3'], $od['od_addr_jibeon'])); ?></td>
                        </tr>
                        <tr>
                            <th scope="row">E-mail</th>
                            <td><?php echo get_text($od['od_email']); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="sod_fin_receiver" class="subscription-recipients">
            <div class="pos_rel">
            <h3>받으시는 분</h3>
                <a href="<?php echo G5_SUBSCRIPTION_URL; ?>/orderaddress.php" id="order_address" class="btn_frmline">배송지목록</a>
            </div>
            
            <div class="tbl_head01 tbl_wrap">
                <table class="tbl_frm01">

                    <tbody>
                        <tr>
                            <th scope="row">이 름</th>
                            <td>
                            <input type="hidden" name="ad_subject" id="ad_subject" class="frm_input" maxlength="20">
                            <input type="text" name="od_b_name" id="od_b_name" required="" class="frm_input required" <?php echo $recipients_attr; ?> maxlength="20" value="<?php echo get_text($od['od_b_name']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">연락처</th>
                            <td>
                                <input type="text" name="od_b_tel" id="od_b_tel" required="" class="frm_input required" <?php echo $recipients_attr; ?> maxlength="20" value="<?php echo get_text($od['od_b_tel']); ?>">
                                <input type="hidden" name="od_b_hp" id="od_b_hp" maxlength="20" value="<?php echo get_text($od['od_b_hp']); ?>">
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">주 소</th>
                            <td>
                                <div id="sod_frm_addr">
                            
                                    <label for="od_b_zip" class="sound_only">우편번호<strong class="sound_only"> 필수</strong></label>
                                    <input type="text" name="od_b_zip" id="od_b_zip" required class="frm_input required" size="8" maxlength="6" placeholder="우편번호" <?php echo $recipients_attr; ?> value="<?php echo get_text($od['od_b_zip']); ?>">
                                    <button type="button" class="btn_address" onclick="win_zip('forderform', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');" <?php echo $recipients_attr; ?>>주소 검색</button><br>
                                    <input type="text" name="od_b_addr1" id="od_b_addr1" required class="frm_input frm_address required" size="60" placeholder="기본주소" <?php echo $recipients_attr; ?> value="<?php echo get_text($od['od_b_addr1']); ?>">
                                    <label for="od_b_addr1" class="sound_only">기본주소<strong> 필수</strong></label><br>
                                    <input type="text" name="od_b_addr2" id="od_b_addr2" class="frm_input frm_address" size="60" placeholder="상세주소" <?php echo $recipients_attr; ?> value="<?php echo get_text($od['od_b_addr2']); ?>">
                                    <label for="od_b_addr2" class="sound_only">상세주소</label>
                                    <br>
                                    <input type="text" name="od_b_addr3" id="od_b_addr3" readonly="readonly" class="frm_input frm_address" size="60" placeholder="참고항목" <?php echo $recipients_attr; ?> value="<?php echo get_text($od['od_b_addr3']); ?>">
                                    <label for="od_b_addr3" class="sound_only">참고항목</label><br>
                                    <input type="hidden" name="od_b_addr_jibeon" value="">
                                    
                                </div>
                                <div>
                                <?php echo get_text($od['od_b_zip'] . ' ' . print_address($od['od_b_addr1'], $od['od_b_addr2'], $od['od_b_addr3'], $od['od_b_addr_jibeon'])); ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="od_memo">전하실말씀</label></th>
                            <td>
                                <textarea name="od_memo" id="od_memo" <?php echo $recipients_attr; ?>><?php echo html_purifier($od['od_memo']); ?></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="text-center">
                                <div class="form-buttons">
                                    <input type="submit" name="act_button" value="배송지 수정" class="btn_submit" <?php echo $recipients_attr; ?>>
                                </div>
                                <div class="form-messages">
                                    <?php if ($is_end_subscription) { ?>
                                        <?php if ($od['od_pays_total']) { ?>
                                            <strong><?php echo $od['od_pays_total']; ?> 회차로 종료되었습니다.</strong>
                                        <?php } else { ?>
                                            <strong>종료되었습니다.</strong>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <strong><?php echo $current_cycle; ?> 회차가 진행중입니다.<br>배송지 수정시 <?php echo $current_cycle; ?> 회차부터 적용됩니다.</strong>
                                    <?php } ?>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
        
        </form>
        <section id="sod_fin_dvr">
            <h3>정기구독정보</h3>

            <?php
            $cards = get_customer_card_info($od);

            $payment_date_title = (!empty($subscription_pays)) ? '다음 결제일' : '첫 결제일';
            $upcoming_payment_title = '다음 결제가격';
            $next_delivery_title = '다음 배송일';

            $next_e_number = 0;

            if (empty($subscription_pays)) {
                $payment_date_title = '첫 결제일';
                $upcoming_payment_title = '첫 결제가격';
                $next_delivery_title = '첫 배송일(예정)';
                $e_number = $next_e_number = '1';
            } else {

                $sql = "SELECT MAX(py_round_no) AS max_no 
                        FROM {$g5['g5_subscription_pay_table']} 
                        WHERE od_id = '$od_id'";
                $subscription_pay_max = sql_fetch($sql);

                $e_number = isset($subscription_pay_max['max_no']) ? (int) $subscription_pay_max['max_no'] : '1';
                $next_e_number = $e_number + 1;
            }
            
            $next_subscription_delivery_date = $is_end_subscription ? '' : get_subscription_delivery_date($od, 'y-m-d');
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
                                <?php echo $od_deliverys; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">현재회차</th>
                            <td>
                                <?php echo $current_cycle_str; ?>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">이용횟수</th>
                            <td>
                                <?php echo $od_usages; ?> 
                            </td>
                        </tr>
                        <?php if (!is_null_date($od['od_hope_date'])) { ?>
                            <tr>
                                <th scope="row">첫 희망배송일</th>
                                <td><?php echo date('Y-m-d', strtotime($od['od_hope_date'])). ' (' . get_yoil($od['od_hope_date']) . ')'; ?></td>
                            </tr>
                        <?php } ?>
                        <?php if (!$is_end_subscription) { ?>
                        <tr>
                            <th scope="row"><?php echo $payment_date_title; ?></th>
                            <td><?php echo date('Y-m-d', strtotime($od['next_billing_date'])). ' (' . get_yoil($od['next_billing_date']) . ')'; ?></td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo $upcoming_payment_title; ?><br>(예정)</th>
                            <td>
                                <?php echo number_format(subscription_order_pay_price($od_id)); ?>원
                                <br>
                                <span class="help">결제가격은 장바구니 상태에 따라 변동될수 있습니다.</span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row"><?php echo $next_delivery_title; ?></th>
                            <td>
                                <?php echo $next_e_number; ?> 회차 <span class="set_pay_date"><?php echo $next_subscription_delivery_date. ' (' . get_yoil($next_subscription_delivery_date) . ')'; ?></span>
                                등록된 결제카드로 도착 <?php echo (int) get_subs_option('su_auto_payment_lead_days'); ?>일 전 자동결제 됩니다.
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="sod_fin_dvr_list">
            <h3>정기결제내역</h3>

            <div class="tbl_head01 tbl_wrap my-payment-details">
                <table>
                    <?php if ($subscription_pays) { ?>
                        <tr>
                            <th>회차</th>
                            <th>결제카드</th>
                            <th>결제된날짜</th>
                            <th>결제금액</th>
                            <th>보기</th>
                        </tr>
                        <?php foreach ($subscription_pays as $key => $v) { ?>
                            <tr>
                                <td><?php echo $v['py_round_no']; ?></td>
                                <td><?php echo $v['card_txt']; ?></td>
                                <td><?php echo date('y-m-d', strtotime($v['py_receipt_time'])); ?></td>
                                <td><?php echo display_price($v['py_cart_price'] + $v['py_send_cost'] + $v['py_send_cost2']); ?></td>
                                <td><a href="#ex_modal1" rel="modal:open" data-payid="<?php echo $v['pay_id']; ?>" class="mng_mod btn btn_02">상세보기</a></td>
                            </tr>
                        <?php } // end for 
                        ?>
                    <?php } else { ?>
                        <tr>
                            <td class="empty_table">아직 정기결제내역이 없습니다.</td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </section>
        
        <section id="sod_fin_dvr_list">
        <h3>정기구독 전체 일정</h3>
        <?php
        $schedules = get_subscription_schedule($od, $od_usage_count);
        $schedule_txt = $od['od_enable_status'] ? '진행중' : '종료됨';
        ?>
            <div>
                <ul class="subscription-list">
                <?php foreach ($schedules as $item): ?>
                    <li class="subscription-item<?php echo $item['is_current'] ? ' is-current' : '' ?>">
                        <div class="count-area">
                            <div class="count"><?php echo $item['round'] ?>회차</div>
                            <?php if ($item['is_current']): ?>
                                <span class="badge"><?php echo $schedule_txt; ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="status-area">
                            <div class="status">
                                <span class="date"><?php echo date('y-m-d', strtotime($item['payment_date'])) ?> (<?php echo get_weekday($item['payment_date']); ?>)</span>
                                <span class="text"><?php echo $item['payment_status'] === 'paid' ? '결제완료' : '결제예정' ?></span>
                            </div>
                            <div class="status">
                                <span class="date"><?php echo date('y-m-d', strtotime($item['delivery_date'])); ?> (<?php echo get_weekday($item['delivery_date']); ?>)</span>
                                <span class="text"><?php echo $item['delivery_title'] ?></span>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
                </ul>
            </div>
        </section>
    </div>

    <div class="sod_right">
        <ul id="sod_bsk_tot2">
            <li class="sod_bsk_dvr">
                <span>주문총액</span>
                <strong><?php echo number_format($od['od_cart_price']); ?> 원</strong>
            </li>
            <?php if ($od['od_send_cost'] > 0) { ?>
                <li class="sod_bsk_dvr">
                    <span>배송비</span>
                    <strong><?php echo number_format($od['od_send_cost']); ?> 원</strong>
                </li>
            <?php } ?>
            <?php if ($od['od_send_coupon'] > 0) { ?>
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
            <li class="sod_bsk_point">
                <span>적립포인트</span>
                <strong><?php echo number_format($tot_point); ?> 점</strong>
            </li>

            <li class="sod_fin_tot"><span>총 구독금액</span><strong><?php echo display_price($tot_price); ?></strong></li>

        </ul>

        <section id="sod_fin_pay">
            <h3>구독정보</h3>
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
        
        <?php if (! $is_end_subscription) { ?>
        <section id="sod_fin_cancel" class="is-subscription">
            <?php
            // 취소한 내역이 없다면
            if ($cancel_price == 0) {
                if ($custom_cancel) {
            ?>
                    <button type="button" class="sod_fin_c_btn">정기구독 취소하기</button>
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
                        $(function() {
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
        <?php } ?>
                    
        <section id="sod_fin_history">
            <h3>구독 히스토리</h3>
            <ul class="order-historys">
                <?php if ($hss) {
                    foreach ($hss as $h) { ?>
                        <li rel="<?php echo $h['hs_id']; ?>" class="history-item <?php echo $h['hs_category']; ?>">
                            <div class="history-content">
                                <?php echo conv_content($h['hs_content'], 0); ?>
                            </div>
                            <p class="history-btns">
                                <span class="history-date"><?php echo date('y-m-d', strtotime($h['hs_time'])); ?></span>
                            </p>
                        </li>
                    <?php }
                } else { ?>
                    <li class="no-data">히스토리가 없습니다.</li>
                <?php } ?>
            </ul>
        </section>
    </div>

</div>
<!-- } 주문상세내역 끝 -->

<?php // 정기결제 상세보기 모달 시작 
?>
<div id="ex_modal1" class="modal">
    <div class="modal_contents">
    </div>
</div>

<script>
function calculate_sendcost() {
}

    jQuery(function($) {
        $("#sod_sts_explan_open").on("click", function(e) {
            var $explan = $("#sod_sts_explan");
            if ($explan.is(":animated"))
                return false;

            if ($explan.is(":visible")) {
                $explan.slideUp(200);
                $("#sod_sts_explan_open").text("상태설명보기");
            } else {
                $explan.slideDown(200);
                $("#sod_sts_explan_open").text("상태설명닫기");
            }
        });

        $("#sod_sts_explan_close").on("click", function(e) {
            var $explan = $("#sod_sts_explan");
            if ($explan.is(":animated"))
                return false;

            $explan.slideUp(200);
            $("#sod_sts_explan_open").text("상태설명보기");
        });

        $(document).on("click", ".mng_mod.btn", function(e) {
            e.preventDefault();

            var pay_id = $(this).attr("data-payid"),
                oDate = new Date(),
                action_url = g5_url + "/subscription/ajax.subscription_pay.php",
                formData = "pay_id=" + pay_id;

            var ajax_var = $.ajax({
                    type: "POST",
                    url: action_url + "?t=" + oDate.getTime(),
                    data: formData,
                    dataType: 'json', // xml, html, script, json
                    cache: false,
                    success: function(data, status, xhr) {
                        if (data.error) { //실패
                            
                            alert(data.error);
                            
                            console.log(data.error);
                            
                        } else { //성공

                            // .content 요소 선택
                            var contentEl = $(".modal_contents");

                            // 새로운 ul 요소 생성
                            var innerEl = $("<div class='user-subscription-pay'></div>"),
                                ulEl = $("<ul class='user-subscription-inner'></ul>");

                            // JSON 데이터 순회하며 li 요소 생성 후 ul에 추가
                            $.each(data, function(key, value) {
                                // ulEl.append("<li><strong>" + key + ":</strong> " + value + "</li>");
                            });

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
                            html += "<li><span class='th'>연락처 :</span> " + data.py_b_tel + "</li>";
                            html += "<li><span class='th'>주소 :</span> " + data.py_b_full_address + "</li>";
                            
                            html += "<h3>결제상세</h3>";
                            html += "<li><span class='th'>주문총액 :</span> " + number_format(data.py_cart_price) + " 원</li>";
                            if (data.py_send_cost) {
                                html += "<li><span class='th'>배송비 :</span> " + number_format(data.py_send_cost) + " 원</li>";
                            }
                            html += "<li><span class='th'>총계 :</span> " + number_format(data.py_tot_price) + " 원</li>";

                            html += "<h3>결제정보</h3>";
                            html += "<li><span class='th'>회차 :</span> " + data.py_round_no + " 회</li>";
                            html += "<li><span class='th'>주문번호 :</span> " + data.subscription_pg_id + "</li>";
                            html += "<li><span class='th'>주문일시 :</span> " + data.py_time + "</li>";
                            html += "<li><span class='th'>결제카드 :</span> " + data.card_txt + "</li>";
                            html += "<li><span class='th'>결제금액 :</span> " + number_format(data.py_receipt_price) + " 원</li>";
                            html += "<li><span class='th'>결제일시 :</span> " + data.py_receipt_time + "</li>";
                            html += "<li><span class='th'>승인번호 :</span> " + data.py_app_no + "</li>";
                            
                            if (data.py_receipt_url) {
                                html += "<li><span class='th'>영수증 :</span> <a href='" + data.py_receipt_url + "' target='_blank' class='subscription-receipt-view'>영수증클릭</a></li>";
                            }
                            
                            html += "<h3>배송정보</h3>";
                            if (data.py_invoice && data.py_delivery_company) {
                                html += "<li><span class='th'>배송회사 :</span> " + data.py_delivery_full_info + "</li>";
                                html += "<li><span class='th'>운송장번호 :</span> " + data.py_invoice + "</li>";
                                html += "<li><span class='th'>배송일시 :</span> " + data.py_invoice_time + "</li>";
                            } else {
                                html += "<li class='is_not_delivery'>아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</li>";
                            }

                            ulEl.append(html);

                            for (var i = 0; i < data.cart_infos.goods.length; i++) {

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

                                cartHTML += 
                                    '<div class="product-item">' +
                                        '<div class="product-img">' + img + '</div>' +
                                        '<div class="product-info">' +
                                            '<div class="product-name"><a href="#">' + productName + '</a></div>' +
                                            '<div class="product-options">' + optionsHtml + '</div>' +
                                        '</div>' +
                                        '<div class="product-meta">' +
                                            '<div>가격: ' + productPrice + '원</div>' +
                                        '</div>' +
                                    '</div>';

                            }

                            if (cartHTML) {
                                innerEl.append('<div class="product-list">' + cartHTML + '</div>');
                            }

                            innerEl.append(ulEl);

                            // 기존 .content 내부에 추가
                            contentEl.html(innerEl);

                        }

                    },
                    error: function(request, status, error) {

                    }
                })
                .always(function() {

                });

        });

        $(document).on("click", ".subscription-receipt-view", function(e) {
            e.preventDefault();

            var $href = $(this).attr("href");

            window.open($href, "winreceipt", "width=500,height=690,scrollbars=yes,resizable=yes");
        });

        // 배송미루기 버튼 클릭시
        $(document).on("click", ".delay-subs-order-btn", function(e) {
            e.preventDefault();

            var od_id = $(this).attr("data-oid");
            
            if (!od_id) {
                return false;
            }
            
            $.ajax({
                url: "<?php echo G5_SUBSCRIPTION_URL; ?>/ajax_postpone_delivery.php",
                type: "POST",
                data: {
                    od_id : od_id
                },
                dataType: "json",
                async: false,
                cache: false,
                success: function(result) {
                    if (result.success) {
                        alert(result.success);
                    } else {
                        alert(result.error);
                    }
                },
                error: function(xhr, status, error) {
                    alert('오류 발생: ' + error);
                }
            });
        });
        
        // 배송지목록
        $("#order_address").on("click", function() {
            var url = this.href;
            window.open(url, "win_address", "left=100,top=100,width=800,height=600,scrollbars=1");
            return false;
        });
        
        $("#forderform").on("submit", function(e) {
            const requiredFields = [
                { id: "#od_b_name", name: "받으시는 분 이름" },
                { id: "#od_b_tel", name: "연락처" },
                { id: "#od_b_zip", name: "우편번호" },
                { id: "#od_b_addr1", name: "기본주소" }
            ];

            for (const field of requiredFields) {
                const $input = $(field.id);
                if ($input.val().trim() === "") {
                    alert(field.name + "을(를) 입력해주세요.");
                    $input.focus();
                    e.preventDefault();
                    return false;
                }
            }

            // 연락처: 숫자 또는 하이픈 포함 형식 허용
            const tel = $("#od_b_tel").val().trim();
            if (!/^\d{2,4}-?\d{3,4}-?\d{4}$/.test(tel)) {
                alert("연락처 형식이 올바르지 않습니다. 예: 010-1234-5678");
                $("#od_b_tel").focus();
                e.preventDefault();
                return false;
            }

            // 우편번호: 숫자 5자리
            const zip = $("#od_b_zip").val().trim();
            if (!/^\d{5}$/.test(zip)) {
                alert("우편번호는 숫자 5자리여야 합니다.");
                $("#od_b_zip").focus();
                e.preventDefault();
                return false;
            }

            // 마지막 확인
            if (!confirm("정말 수정하시겠습니까?")) {
                e.preventDefault();
                return false;
            }
    
            // 유효성 검사를 통과했을 때만 전송
            return true;
        });

    });

    function fcancel_check(f) {
        if (!confirm("구독을 정말 취소하시겠습니까?"))
            return false;

        var memo = f.cancel_memo.value;
        if (memo == "") {
            alert("취소사유를 입력해 주십시오.");
            return false;
        }

        return true;
    }
</script>

<?php
include_once('./_tail.php');

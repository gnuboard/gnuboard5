<?php
$sub_menu = '600400';
include_once('./_common.php');

$cart_title3 = '주문번호';
$cart_title4 = '배송완료';

auth_check_menu($auth, $sub_menu, "w");

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : '';
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : '';
$od_enable_status = isset($_REQUEST['od_enable_status']) ? clean_xss_tags($_REQUEST['od_enable_status'], 1, 1) : '';
$od_settle_case = isset($_REQUEST['od_settle_case']) ? clean_xss_tags($_REQUEST['od_settle_case'], 1, 1) : '';
$od_misu = isset($_REQUEST['od_misu']) ? clean_xss_tags($_REQUEST['od_misu'], 1, 1) : '';
$od_receipt_point = isset($_REQUEST['od_receipt_point']) ? clean_xss_tags($_REQUEST['od_receipt_point'], 1, 1) : '';
$od_coupon = isset($_REQUEST['od_coupon']) ? clean_xss_tags($_REQUEST['od_coupon'], 1, 1) : '';
$od_id = isset($_REQUEST['od_id']) ? safe_replace_regex($_REQUEST['od_id'], 'od_id') : '';
$od_escrow = isset($_REQUEST['od_escrow']) ? clean_xss_tags($_REQUEST['od_escrow'], 1, 1) : '';

$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';
$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';


// 완료된 주문에 포인트를 적립한다.
save_order_point("완료");

add_javascript('<script src="' . G5_JS_URL . '/jquerymodal/jquery.modal.min.js"></script>', 10);
add_stylesheet('<link rel="stylesheet" href="' . G5_JS_URL . '/jquerymodal/jquery.modal.min.css">', 10);

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$od = get_subscription_order($od_id);

if (! (isset($od['od_id']) && $od['od_id'])) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

// 장바구니 금액 변동 체크
$is_od_update = updateSubscriptionItemIfChanged($od);

if ($is_od_update) {
    // 주문정보가 변경된 경우 다시 값을 가져온다.
    $od = get_subscription_order($od['od_id']);
}

// 결제되는 카드 정보 가져옴
$cards = get_customer_card_info($od);

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

$od['mb_id'] = $od['mb_id'] ? $od['mb_id'] : "비회원";
//------------------------------------------------------------------------------

$g5['title'] = "정기구독 내역 수정";
include_once(G5_ADMIN_PATH . '/admin.head.php');

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_sodr_list">구독상품 목록</a></li>
<li><a href="#anc_sodr_pay">구독결제 내역</a></li>
<li><a href="#anc_sodr_chk">결제상세정보 확인</a></li>
<li><a href="#anc_sodr_paymo">결제상세정보 수정</a></li>
<li><a href="#anc_sodr_memo">상점메모</a></li>
<li><a href="#anc_sodr_history">정기구독 히스토리</a></li>
<li><a href="#anc_sodr_orderer">주문하신 분</a></li>
<li><a href="#anc_sodr_taker">받으시는 분</a></li>
</ul>';

$html_receipt_chk = '<input type="checkbox" id="od_receipt_chk" value="" onclick="chk_receipt_price()">
<label for="od_receipt_chk">결제금액 입력</label><br>';

$qstr1 = "od_enable_status=" . urlencode($od_enable_status) . "&amp;od_settle_case=" . urlencode($od_settle_case) . "&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if ($default['de_escrow_use'])
    $qstr1 .= "&amp;od_escrow=$od_escrow";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

// 상품목록
$sql = " select it_id,
                it_name,
                cp_price,
                ct_notax,
                ct_send_cost,
                it_sc_type
           from {$g5['g5_subscription_cart_table']}
          where od_id = '{$od['od_id']}'
          group by it_id
          order by ct_id ";
$result = sql_query($sql);

$print_od_deposit_name = $od['od_deposit_name'];

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<script src="<?php echo G5_JS_URL; ?>/shop.js?ver=<?php echo G5_JS_VER; ?>"></script>
<script src="<?php echo G5_JS_URL; ?>/shop.override.js?ver=<?php echo G5_JS_VER; ?>"></script>

<section id="anc_sodr_list">
    <h2 class="h2_frm">구독상품 목록</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            현재 구독상태 <strong><?php echo $od['od_enable_status'] ? '진행중' : '종료됨'; ?></strong>
            |
            구독일시 <strong><?php echo substr($od['od_time'], 0, 16); ?> (<?php echo get_yoil($od['od_time']); ?>)</strong>
            |
            구독금액 <strong><?php echo number_format($od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2']); ?></strong>원
        </p>
        <?php if ($default['de_hope_date_use']) { ?><p>첫 희망배송일은 <?php echo $od['od_hope_date']; ?> (<?php echo get_yoil($od['od_hope_date']); ?>) 입니다.</p><?php } ?>
        <?php if ($od['od_mobile']) { ?>
            <p>모바일 쇼핑몰의 주문입니다.</p>
        <?php } ?>
    </div>

    <form name="frmorderform" method="post" action="./orderformcartupdate.php" onsubmit="return form_submit(this);">
        <input type="hidden" name="return_query" id="return_query" value="<?php echo get_sanitize_input($_SERVER['QUERY_STRING']); ?>">
        <input type="hidden" name="od_id" value="<?php echo get_sanitize_input($od_id); ?>">
        <input type="hidden" name="mb_id" value="<?php echo get_sanitize_input($od['mb_id']); ?>">
        <input type="hidden" name="od_email" value="<?php echo get_sanitize_input($od['od_email']); ?>">
        <input type="hidden" name="sort1" value="<?php echo get_sanitize_input($sort1); ?>">
        <input type="hidden" name="sort2" value="<?php echo get_sanitize_input($sort2); ?>">
        <input type="hidden" name="sel_field" value="<?php echo get_sanitize_input($sel_field); ?>">
        <input type="hidden" name="search" value="<?php echo get_sanitize_input($search); ?>">
        <input type="hidden" name="page" value="<?php echo get_sanitize_input($page); ?>">
        <input type="hidden" name="pg_cancel" value="0">

        <div class="tbl_head01 tbl_wrap">
            <table>
                <caption>주문 상품 목록</caption>
                <thead>
                    <tr>
                        <th scope="col">상품명</th>
                        <th scope="col">옵션항목</th>
                        <th scope="col">상태</th>
                        <th scope="col">수량</th>
                        <th scope="col">판매가</th>
                        <th scope="col">소계</th>
                        <th scope="col">포인트</th>
                        <th scope="col">배송비</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $chk_cnt = 0;
                    for ($i = 0; $row = sql_fetch_array($result); $i++) {
                        // 상품이미지
                        $image = get_it_image($row['it_id'], 50, 50);

                        // 상품의 옵션정보
                        $sql = " select ct_id, it_id, ct_price, ct_point, ct_qty, ct_option, ct_status, cp_price, ct_stock_use, ct_point_use, ct_send_cost, io_type, io_price
                        from {$g5['g5_subscription_cart_table']}
                        where od_id = '{$od['od_id']}'
                          and it_id = '{$row['it_id']}'
                        order by io_type asc, ct_id asc ";
                        $res = sql_query($sql);
                        $rowspan = sql_num_rows($res);

                        // 합계금액 계산
                        $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                            SUM(ct_qty) as qty
                        from {$g5['g5_subscription_cart_table']}
                        where it_id = '{$row['it_id']}'
                          and od_id = '{$od['od_id']}' ";
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
                            $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $od['od_id']);

                            if ($sendcost == 0)
                                $ct_send_cost = '무료';
                        }

                        for ($k = 0; $opt = sql_fetch_array($res); $k++) {
                            if ($opt['io_type'])
                                $opt_price = $opt['io_price'];
                            else
                                $opt_price = $opt['ct_price'] + $opt['io_price'];

                            // 소계
                            $ct_price['stotal'] = $opt_price * $opt['ct_qty'];
                            $ct_point['stotal'] = $opt['ct_point'] * $opt['ct_qty'];
                    ?>
                            <tr>
                                <?php if ($k == 0) { ?>
                                    <td rowspan="<?php echo $rowspan; ?>" class="td_left">
                                        <div class="rel">
                                            <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>"><?php echo $image; ?> <?php echo stripslashes($row['it_name']); ?></a>
                                            <?php if ($od['od_tax_flag'] && $row['ct_notax']) echo '[비과세상품]'; ?>
                            
                                            <div class="sod_option_btn" data-itid="<?php echo $row['it_id']; ?>"><a href="#ex_modal1" rel="modal:open" class="mod_options">상품옵션수정</a></div>
                                        </div>
                                    </td>
                                <?php } ?>
                                <td class="td_left">
                                    <input type="hidden" name="ct_id[<?php echo $chk_cnt; ?>]" value="<?php echo $opt['ct_id']; ?>">
                                    <?php echo sanitize_input($opt['ct_option']); ?>
                                </td>
                                <td class="td_mngsmall"><?php echo $opt['ct_status']; ?></td>
                                <td class="td_num">
                                    <?php echo $opt['ct_qty']; ?>
                                </td>
                                <td class="td_num_right "><?php echo number_format($opt_price); ?></td>
                                <td class="td_num_right"><?php echo number_format($ct_price['stotal']); ?></td>
                                <td class="td_num_right"><?php echo number_format($ct_point['stotal']); ?></td>
                                <td class="td_sendcost_by"><?php echo $ct_send_cost; ?></td>
                            </tr>
                        <?php
                            $chk_cnt++;
                        }
                        ?>
                    <?php
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <div class="btn_list02 btn_list">
            <p>
                <input type="hidden" name="chk_cnt" value="<?php echo $chk_cnt; ?>">
                <strong>구독 상태 변경</strong>
                <input type="submit" name="ct_status" value="활성화" onclick="document.pressed=this.value" class="btn_02 color_01">
                <input type="submit" name="ct_status" value="비활성화" onclick="document.pressed=this.value" class="btn_02 color_06">
            </p>
        </div>

        <div class="local_desc01 local_desc">
            <p>주문, 입금, 준비, 배송, 완료는 장바구니와 주문서 상태를 모두 변경하지만, 취소, 반품, 품절은 장바구니의 상태만 변경하며, 주문서 상태는 변경하지 않습니다.</p>
            <p>개별적인(이곳에서의) 상태 변경은 모든 작업을 수동으로 처리합니다. 예를 들어 주문에서 입금으로 상태 변경시 입금액(결제금액)을 포함한 모든 정보는 수동 입력으로 처리하셔야 합니다.</p>
        </div>

    </form>

    <?php if ($od['od_mod_history']) { ?>
        <section id="sodr_qty_log">
            <h3>주문 수량변경 및 주문 전체취소 처리 내역</h3>
            <div>
                <?php echo conv_content($od['od_mod_history'], 0); ?>
            </div>
        </section>
    <?php } ?>

</section>

<div id="ex_modal1" class="modal">
    <div class="modal_contents">
    </div>
</div>

<?php if ($od['od_test']) { ?>
    <div class="od_test_caution">주의) 이 구독주문은 테스트용으로 실제 결제가 이루어지지 않으므로 절대 배송하시면 안됩니다.</div>
<?php } ?>

<section id="anc_sodr_pay">
    <h2 class="h2_frm">주문결제 내역</h2>
    <?php echo $pg_anchor; ?>

    <?php
    // 주문금액 = 상품구입금액 + 배송비 + 추가배송비
    $amount['order'] = $od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2'];

    // 입금액 = 결제금액 + 포인트
    $amount['receipt'] = $od['od_receipt_price'] + $od['od_receipt_point'];

    // 쿠폰금액
    $amount['coupon'] = $od['od_cart_coupon'] + $od['od_coupon'] + $od['od_send_coupon'];

    // 결제방법
    $s_receipt_way = check_pay_name_replace($od['od_settle_case'], $od);

    if ($od['od_receipt_point'] > 0)
        $s_receipt_way .= "+포인트";
    ?>

    <div class="tbl_head01 tbl_wrap">

        <table>
            <caption>정기구독 내역</caption>
            <thead>
                <tr>
                    <th scope="col">구독번호</th>
                    <th scope="col">지불수단</th>
                    <th scope="col">주문총액</th>
                    <th scope="col">배송비</th>
                    <th scope="col">총결제액</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $od['od_id']; ?></td>
                    <td class="td_paybybig"><?php echo $s_receipt_way; ?></td>
                    <td class="td_numbig td_numsum" title="주문총액"><?php echo display_price($amount['order']); ?></td>
                    <td class="td_numbig" title="배송비"><?php echo display_price($od['od_send_cost'] + $od['od_send_cost2']); ?></td>
                    <td class="td_numbig td_numincome" title="총결제액"><?php echo number_format($amount['receipt']); ?>원</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<section class="subs-receiptform">
    <h2 class="h2_frm">구독상세정보</h2>
    <?php echo $pg_anchor; ?>

    <form name="frmorderreceiptform" action="./orderformbillingupdate.php" method="post" autocomplete="off">
        <input type="hidden" name="od_id" value="<?php echo get_sanitize_input($od_id); ?>">
        <input type="hidden" name="sort1" value="<?php echo get_sanitize_input($sort1); ?>">
        <input type="hidden" name="sort2" value="<?php echo get_sanitize_input($sort2); ?>">
        <input type="hidden" name="sel_field" value="<?php echo get_sanitize_input($sel_field); ?>">
        <input type="hidden" name="search" value="<?php echo get_sanitize_input($search); ?>">
        <input type="hidden" name="page" value="<?php echo get_sanitize_input($page); ?>">
        <input type="hidden" name="od_name" value="<?php echo get_sanitize_input($od['od_name']); ?>">
        <input type="hidden" name="od_hp" value="<?php echo get_sanitize_input($od['od_hp']); ?>">
        <input type="hidden" name="od_tno" value="<?php echo get_sanitize_input($od['od_tno']); ?>">
        <input type="hidden" name="od_pg" value="<?php echo get_sanitize_input($od['od_pg']); ?>">

        <div class="compare_wrap">

            <section id="anc_sodr_chk" class="compare_left">
                <h3>구독상세정보 확인</h3>

                <div class="tbl_frm01">
                    <table>
                        <caption>결제상세정보</caption>
                        <colgroup>
                            <col class="grid_3">
                            <col>
                        </colgroup>
                        <tbody>

                            <tr>
                                <th scope="row">결제대행사 링크</th>
                                <td>
                                    <?php
                                    switch ($od['od_pg']) {
                                        case 'tosspayments':
                                            $pg_url  = 'http://pgweb.uplus.co.kr';
                                            $pg_test = '토스페이먼츠';
                                            if ($default['de_card_test']) {
                                                $pg_url = 'http://pgweb.uplus.co.kr/tmert';
                                                $pg_test .= ' 테스트 ';
                                            }
                                            break;
                                        case 'inicis':
                                            $pg_url  = 'https://iniweb.inicis.com/';
                                            $pg_test = 'KG이니시스';
                                            break;
                                        case 'nicepay':
                                            $pg_url  = 'https://npg.nicepay.co.kr/';
                                            $pg_test = 'NICEPAY';
                                            break;
                                        default:
                                            $pg_url  = 'http://admin8.kcp.co.kr';
                                            $pg_test = 'KCP';
                                            if ($default['de_card_test']) {
                                                // 로그인 아이디 / 비번
                                                // 일반 : test1234 / test12345
                                                // 에스크로 : escrow / escrow913
                                                $pg_url = 'http://testadmin8.kcp.co.kr';
                                                $pg_test .= ' 테스트 ';
                                            }
                                    }
                                    echo "<a href=\"{$pg_url}\" target=\"_blank\">{$pg_test}바로가기</a><br>";
                                    //------------------------------------------------------------------------------
                                    ?>
                                </td>
                            </tr>

                            <?php if ($od['od_tax_flag']) { ?>
                                <tr>
                                    <th scope="row">과세공급가액</th>
                                    <td><?php echo display_price($od['od_tax_mny']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">과세부가세액</th>
                                    <td><?php echo display_price($od['od_vat_mny']); ?></td>
                                </tr>
                                <tr>
                                    <th scope="row">비과세공급가액</th>
                                    <td><?php echo display_price($od['od_free_mny']); ?></td>
                                </tr>
                            <?php } ?>
                            <!--
                            <tr>
                                <th scope="row">주문금액할인</th>
                                <td><?php echo display_price($od['od_coupon']); ?></td>
                            </tr>
                            <tr>
                                <th scope="row">포인트</th>
                                <td><?php echo display_point($od['od_receipt_point']); ?></td>
                            </tr>
                            -->
                            <tr>
                                <th scope="row"><label for="od_send_cost">배송비</label></th>
                                <td>
                                    <input type="text" name="od_send_cost" value="<?php echo $od['od_send_cost']; ?>" id="od_send_cost" class="frm_input" size="10"> 원
                                </td>
                            </tr>
                            <?php if ($od['od_send_coupon']) { ?>
                                <tr>
                                    <th scope="row">배송비할인</th>
                                    <td><?php echo display_price($od['od_send_coupon']); ?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <th scope="row"><label for="od_send_cost2">추가배송비</label></th>
                                <td>
                                    <input type="text" name="od_send_cost2" value="<?php echo $od['od_send_cost2']; ?>" id="od_send_cost2" class="frm_input" size="10"> 원
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="anc_sodr_paymo" class="compare_right">
                <h3>구독상세정보</h3>

                <div class="tbl_frm01">
                    <table>
                        <caption>구독상세정보</caption>
                        <colgroup>
                            <col class="grid_3">
                            <col>
                        </colgroup>
                        <tbody>

                            <tr>
                                <th scope="row"><label for="od_send_cost2">카드정보</label></th>
                                <td>
                                    <?php if ($cards) { ?>
                                        <?php echo $cards['od_card_name']; ?> (<?php echo $cards['card_mask_number']; ?>)
                                    <?php } else { ?>
                                        카드정보가 지워졌거나 카드정보가 없습니다.
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="opt_print">배송주기</label></th>
                                <td>
                                    <?php echo $od_deliverys; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="use_print">이용횟수</label></th>
                                <td>
                                    <?php echo $od_usages; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="od_pays_total">현재 회차</label></th>
                                <td>
                                    <?php echo $current_cycle_str; ?>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="next_billing_date">다음 결제일</label></th>
                                <td>
                                    <div class="subscription-edit-box">
                                    
                                    (<?php echo $od['od_pays_total'] + 1; ?> 회차) <span class="next_billing_date_text txt"><?php echo date('Y-m-d', strtotime($od['next_billing_date'])); ?> (<?php echo get_Ko_DayOfWeek($od['next_billing_date']); ?>)</span>
                                    <input type="hidden" name="next_billing_date" class="hidden_datetime" value="<?php echo $od['next_billing_date']; ?>">
                                    <button data-type="payment" class="subscription-edit mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">결제일 수정</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="next_delivery_date">다음 배송일(예정)</label></th>
                                <td>
                                    <div class="subscription-edit-box" data-time="<?php echo get_subscription_delivery_date($od, 'Y-m-d'); ?>">
                                    (<?php echo $od['od_pays_total'] + 1; ?> 회차) <span class="next_delivery_date_text txt"><?php echo get_subscription_delivery_date($od, 'Y-m-d'); ?> (<?php echo get_Ko_DayOfWeek(get_subscription_delivery_date($od)); ?>)</span>
                                    <input type="hidden" name="next_delivery_date" class="hidden_datetime" value="<?php echo $od['next_delivery_date']; ?>">
                                    <button data-type="delivery" class="subscription-edit mt-2 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">배송일 수정</button>
                                    </div>
                                </td>
                            </tr>
                            
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
        
        <div class="hide warning">
            결제일 또는 배송일 정보를 수정했다면 아래 구독상세정보 수정 버튼을 눌러야 최종적으로 수정이 됩니다.
        </div>
        
        <div class="btn_confirm01 btn_confirm">
            <input type="submit" value="구독상세정보 수정" class="btn_submit btn">
            <a href="./orderlist.php?<?php echo $qstr; ?>" class="btn btn_02">목록</a>
        </div>
    </form>
</section>

<section id="">
    <h3 class="h2_frm">정기결제내역</h3>

    <?php
    $sql = "SELECT * FROM {$g5['g5_subscription_pay_table']} 
        WHERE od_id = '" . $od_id . "' 
        ORDER BY pay_id DESC";
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
    ?>
    <div class="tbl_frm01">
        <table>
            <tr>
                <th>회차</th>
                <th>결제PG</th>
                <th>결제카드</th>
                <th>결제된날짜</th>
                <th>결제금액</th>
                <th>보기</th>
            </tr>
            <?php foreach ($subscription_pays as $key => $v) { ?>
                <tr>
                    <td><?php echo $v['py_round_no']; ?></td>
                    <td><?php echo $v['py_pg']; ?></td>
                    <td><?php echo $v['card_txt']; ?></td>
                    <td><?php echo $v['py_receipt_time']; ?></td>
                    <td><?php echo display_price($v['py_cart_price'] + $v['py_send_cost'] + $v['py_send_cost2']); ?></td>
                    <td><a href="<?php echo G5_SUBSCRIPTION_ADMIN_URL; ?>/payform.php?pay_id=<?php echo $v['pay_id']; ?>" target="_blank" class="mng_mod btn btn_02">상세보기</a></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</section>

<section id="">
    <h3 class="h2_frm">정기구독 전체 일정</h3>
    
    <?php
    $schedules = get_subscription_schedule($od, $od_usage_count);
    ?>
        <div>
            <ul class="subscription-list">
            <?php foreach ($schedules as $item): ?>
                <li class="subscription-item<?php echo $item['is_current'] ? ' is-current' : '' ?>">
                    <div class="count-area">
                        <div class="count"><?php echo $item['round'] ?>회차</div>
                        <?php if ($item['is_current']): ?>
                            <span class="badge">진행중</span>
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
    
    </ul>
</section>

<section id="anc_sodr_memo">
    <h2 class="h2_frm">상점메모</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            현재 열람 중인 주문에 대한 내용을 메모하는곳입니다.<br>
            입금, 배송 내역을 메일로 발송할 경우 함께 기록됩니다.
        </p>
    </div>

    <form name="frmorderform2" action="./orderformupdate.php" method="post">
        <input type="hidden" name="od_id" value="<?php echo sanitize_input($od_id); ?>">
        <input type="hidden" name="sort1" value="<?php echo sanitize_input($sort1); ?>">
        <input type="hidden" name="sort2" value="<?php echo sanitize_input($sort2); ?>">
        <input type="hidden" name="sel_field" value="<?php echo sanitize_input($sel_field); ?>">
        <input type="hidden" name="search" value="<?php echo sanitize_input($search); ?>">
        <input type="hidden" name="page" value="<?php echo sanitize_input($page); ?>">
        <input type="hidden" name="mod_type" value="memo">

        <div class="tbl_wrap">
            <label for="od_subscription_memo" class="sound_only">상점메모</label>
            <textarea name="od_subscription_memo" id="od_subscription_memo" rows="8"><?php echo html_purifier(stripslashes($od['od_subscription_memo'])); ?></textarea>
        </div>

        <div class="btn_confirm01 btn_confirm">
            <input type="submit" value="메모 수정" class="btn_submit btn">
        </div>

    </form>
</section>

<section id="anc_sodr_history" class="subscription_history">
    <h2 class="h2_frm">정기구독 히스토리</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            해당 정기구독에 대한 history 내역입니다. (로그포함)
        </p>
    </div>
    
    <div id="subscription-history-container">
    </div>

    <form name="frmorder_historyform" action="./order_history_update.php" method="post">
        <input type="hidden" name="od_id" value="<?php echo sanitize_input($od_id); ?>">
        <input type="hidden" name="sort1" value="<?php echo sanitize_input($sort1); ?>">
        <input type="hidden" name="sort2" value="<?php echo sanitize_input($sort2); ?>">
        <input type="hidden" name="sel_field" value="<?php echo sanitize_input($sel_field); ?>">
        <input type="hidden" name="search" value="<?php echo sanitize_input($search); ?>">
        <input type="hidden" name="page" value="<?php echo sanitize_input($page); ?>">
        <input type="hidden" name="mod_type" value="add">

        <div class="tbl_wrap">
            <label for="od_subscription_memo" class="sound_only">히스토리 추가하기</label>
            <textarea name="od_subscription_history" id="od_subscription_history" rows="8"></textarea>
        </div>

        <div class="btn_confirm01 btn_confirm">
            <input type="submit" value="히스토리 추가하기" class="btn_submit btn">
        </div>

    </form>

    <script>
        var ajax_history_page = "ajax.subscription_history.php";
        
        // 공백을 유지하기 위해 이스케이프 함수
        function escapeHtml(text) {
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;")
                .replace(/\n/g, "<br>"); // 줄바꿈을 <br>로 변환
        }

        jQuery(function(e) {
            var close_btn_idx;
            
            // 선택사항수정
            $(document).on("click", ".mod_options", function(e) {
                var $this = $(this),
                    it_id = $this.parent().attr("data-itid"),
                    od_id = "<?php echo $od['od_id']; ?>";
                
                close_btn_idx = $(".mod_options").index($this);
                
                $.post(
                    "<?php echo G5_SUBSCRIPTION_URL; ?>/ajax_order_cartoption.php", {
                        it_id: it_id,
                        od_id: od_id
                    },
                    function(data) {
                            
                        var contentEl = $(".modal_contents");
                        
                        contentEl.addClass("adm_mod_option_frm").html(data);
                        price_calculate();
                        
                        $('form[name="foption"] input[name="return_query"]').val($("#return_query").val());
                        
                        /*
                        $("#mod_option_frm").remove();
                        $this.after("<div id=\"mod_option_frm\"></div><div class=\"mod_option_bg\"></div>");
                        $("#mod_option_frm").html(data);
                        price_calculate();
                        */
                        
                    }
                );
            });
            
            $(document).on("click", ".update-subscription-cart-btn", function(e) {
                e.preventDefault();
                
                var pay_id = $(this).attr("data-payid"),
                    oDate = new Date(),
                    action_url = $(this).attr("href"),
                    formData = "pay_id=" + pay_id;
            });
            
            function get_ajax_history(pageUrl, page=1, is_scroll) {
                $.ajax({
                    url: pageUrl,
                    data: {
                        od_id: "<?php echo $od['od_id']; ?>",
                        page: page
                    },
                    type: 'GET',
                    dataType: 'html',
                    success: function(response) {
                        // Parse the response to get only the history container
                        // var newContent = $(response).find('#history-container').html();
                        
                        var newContent = response;
                        
                        $('#subscription-history-container').html(newContent);
                        
                        if (is_scroll) {
                            // Scroll to top of history list
                            $('html, body').animate({
                                scrollTop: ($('#subscription-history-container').offset().top - 150)
                            }, 300);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('히스토리를 불러오는데 실패했습니다.');
                    }
                });
            }
            
            // 정기구독 히스토리
            get_ajax_history(ajax_history_page);
            
            // 정기구독 히스토리 ajax 페이징
            $(document).on('click', '#subscription-history-container .pagination a', function(e) {
                e.preventDefault();
                var pageUrl = $(this).attr('href'),
                    queryString = pageUrl.split('?')[1],
                    params = new URLSearchParams(queryString),
                    page = params.get('page');
                
                get_ajax_history(ajax_history_page, page, 1);
            });
    
            $(document).on("click", ".delete-history", function(e) {
                e.preventDefault();

                if (!confirm("정말 삭제하시겠습니까?")) {
                    return false;
                }

                var $this = $(this);
                var id = $this.data('id');
                var token = get_ajax_token();

                if (!token) {
                    alert("토큰 정보가 올바르지 않습니다.");
                    return false;
                }

                $.ajax({
                    url: "./order_history_update.php",
                    type: "POST",
                    data: {
                        mod_type: 'del',
                        is_ajax: 1,
                        token: token,
                        hs_id: id
                    },
                    timeout: 10000, // 10초 타임아웃 설정 (필요에 따라 조정)
                    beforeSend: function() {
                        $this.prop('disabled', true); // 버튼 비활성화
                    },
                    success: function(response) {
                        // 서버에서 응답을 정상적으로 받은 경우
                        try {
                            if (response.success) {
                                $this.closest('.history-item').fadeOut(400, function() {
                                    $(this).remove(); // 애니메이션이 끝난 후 제거
                                });
                            } else {
                                alert('삭제 실패: ' + (response.message || '알 수 없는 오류'));
                            }
                        } catch (e) {
                            alert('응답 처리 중 오류: ' + e.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        alert('오류 발생: ' + error);
                    },
                    complete: function() {
                        $this.prop('disabled', false); // 버튼 활성화
                    }
                });
            });

            var $btn_submit = $('form[name="frmorder_historyform"]').find(".btn_submit"),
                $btn_text = $btn_submit.val();

            // 폼 제출 이벤트 처리
            $('form[name="frmorder_historyform"]').on('submit', function(e) {
                e.preventDefault(); // 기본 폼 제출 방지

                var historyText = $('#od_subscription_history').val().trim();
                if (!historyText) {
                    alert('내용을 입력해주세요.');
                    return;
                }

                // 폼 데이터 수집
                var formData = $(this).serialize() + '&is_ajax=1'; // 모든 입력 필드 데이터를 직렬화

                // AJAX 요청
                $.ajax({
                    url: $(this).attr('action'), // action 속성에서 URL 가져오기: ./order_history_update.php
                    type: 'POST',
                    data: formData, // 폼 데이터 전송
                    dataType: 'json', // 서버에서 JSON 응답을 기대
                    beforeSend: function() {
                        // 제출 버튼 비활성화 및 상태 표시
                        $btn_submit.prop('disabled', true).val('처리 중...');
                    },
                    success: function(response) {
                        // 응답 처리
                        if (response.success) {
                            
                            /*
                            // 새 히스토리 항목 생성
                            var newHistory = 
                                '<li rel="' + response.hs_id + '" class="history-item">' +
                                    '<div class="history-content">' +
                                        escapeHtml(historyText) +
                                    '</div>' +
                                    '<p class="history-btns">' +
                                        '<span class="history-date">' + response.hs_date + '</span>' +
                                        '<a href="#" class="delete-history" data-id="' + response.hs_id + '" role="button">삭제하기</a>' +
                                    '</p>' +
                                '</li>';

                            // 목록에 추가 (맨 위에 추가)
                            var $newItem = $(newHistory).hide();
                            $('.order-historys').prepend($newItem);
                            $newItem.fadeIn(400, function() { // 페이드인 후 스크롤
                                $('html, body').animate({
                                    scrollTop: $newItem.offset().top - 150
                                }, 300);
                            });
                            */
                            

                            $('#od_subscription_history').val('');
                            
                            get_ajax_history(ajax_history_page, 1, 1);
                            
                        } else {
                            alert(response.message || '히스토리 추가에 실패했습니다.');
                        }
                    },
                    error: function(xhr, status, error) {
                        // 에러 처리
                        let errorMessage = status === 'timeout' ? '서버 응답 시간이 초과되었습니다.' :
                            status === 'parsererror' ? '서버 응답 형식이 잘못되었습니다.' :
                            '오류 발생: ' + (error || '알 수 없는 오류');
                        alert(errorMessage);
                    },
                    complete: function() {
                        // 요청 완료 후 버튼 복구
                        $btn_submit.prop('disabled', false).val($btn_text);
                    },
                    timeout: 10000 // 10초 타임아웃
                });
            });
        });
    </script>
</section>

<section>
    <h2 class="h2_frm">주문자/배송지 정보</h2>
    <?php echo $pg_anchor; ?>

    <form name="frmorderform3" action="./orderformupdate.php" method="post">
        <input type="hidden" name="od_id" value="<?php echo sanitize_input($od_id); ?>">
        <input type="hidden" name="sort1" value="<?php echo sanitize_input($sort1); ?>">
        <input type="hidden" name="sort2" value="<?php echo sanitize_input($sort2); ?>">
        <input type="hidden" name="sel_field" value="<?php echo sanitize_input($sel_field); ?>">
        <input type="hidden" name="search" value="<?php echo sanitize_input($search); ?>">
        <input type="hidden" name="page" value="<?php echo sanitize_input($page); ?>">
        <input type="hidden" name="mod_type" value="info">

        <div class="compare_wrap">

            <section id="anc_sodr_orderer" class="compare_left">
                <h3>주문하신 분</h3>

                <div class="tbl_frm01">
                    <table>
                        <caption>주문자/배송지 정보</caption>
                        <colgroup>
                            <col class="grid_4">
                            <col>
                        </colgroup>
                        <tbody>
                            <tr>
                                <th scope="row"><label for="od_name"><span class="sound_only">주문하신 분 </span>이름</label></th>
                                <td><input type="text" name="od_name" value="<?php echo sanitize_input($od['od_name']); ?>" id="od_name" required class="frm_input required"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="od_tel"><span class="sound_only">주문하신 분 </span>연락처</label></th>
                                <td>
                                    <input type="text" name="od_tel" value="<?php echo sanitize_input($od['od_tel']); ?>" id="od_tel" required class="frm_input required">
                                    <input type="hidden" name="od_hp" value="<?php echo sanitize_input($od['od_hp']); ?>" id="od_hp" class="frm_input">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><span class="sound_only">주문하시는 분 </span>주소</th>
                                <td>
                                    <label for="od_zip" class="sound_only">우편번호</label>
                                    <input type="text" name="od_zip" value="<?php echo sanitize_input($od['od_zip']); ?>" id="od_zip" required class="frm_input required" size="5">
                                    <button type="button" class="btn_frmline" onclick="win_zip('frmorderform3', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon');">주소 검색</button><br>
                                    <span id="od_win_zip" style="display:block"></span>
                                    <input type="text" name="od_addr1" value="<?php echo sanitize_input($od['od_addr1']); ?>" id="od_addr1" required class="frm_input required" size="35">
                                    <label for="od_addr1">기본주소</label><br>
                                    <input type="text" name="od_addr2" value="<?php echo sanitize_input($od['od_addr2']); ?>" id="od_addr2" class="frm_input" size="35">
                                    <label for="od_addr2">상세주소</label>
                                    <br>
                                    <input type="text" name="od_addr3" value="<?php echo sanitize_input($od['od_addr3']); ?>" id="od_addr3" class="frm_input" size="35">
                                    <label for="od_addr3">참고항목</label>
                                    <input type="hidden" name="od_addr_jibeon" value="<?php echo sanitize_input($od['od_addr_jibeon']); ?>"><br>
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="od_email"><span class="sound_only">주문하신 분 </span>E-mail</label></th>
                                <td><input type="text" name="od_email" value="<?php echo $od['od_email']; ?>" id="od_email" required class="frm_input required" size="30"></td>
                            </tr>
                            <tr>
                                <th scope="row"><span class="sound_only">주문하신 분 </span>IP Address</th>
                                <td><?php echo $od['od_ip']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <section id="anc_sodr_taker" class="compare_right">
                <h3>받으시는 분</h3>

                <div class="tbl_frm01">
                    <table>
                        <caption>받으시는 분 정보</caption>
                        <colgroup>
                            <col class="grid_4">
                            <col>
                        </colgroup>
                        <tbody>
                            <tr>
                                <th scope="row"><label for="od_b_name"><span class="sound_only">받으시는 분 </span>이름</label></th>
                                <td><input type="text" name="od_b_name" value="<?php echo sanitize_input($od['od_b_name']); ?>" id="od_b_name" required class="frm_input required"></td>
                            </tr>
                            <tr>
                                <th scope="row"><label for="od_b_tel"><span class="sound_only">받으시는 분 </span>연락처</label></th>
                                <td>
                                    <input type="text" name="od_b_tel" value="<?php echo sanitize_input($od['od_b_tel']); ?>" id="od_b_tel" required class="frm_input required">
                                    <input type="hidden" name="od_b_hp" value="<?php echo sanitize_input($od['od_b_hp']); ?>" id="od_b_hp" class="frm_input">
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
                                <td>
                                    <label for="od_b_zip" class="sound_only">우편번호</label>
                                    <input type="text" name="od_b_zip" value="<?php echo sanitize_input($od['od_b_zip']); ?>" id="od_b_zip" required class="frm_input required" size="5">
                                    <button type="button" class="btn_frmline" onclick="win_zip('frmorderform3', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">주소 검색</button><br>
                                    <input type="text" name="od_b_addr1" value="<?php echo sanitize_input($od['od_b_addr1']); ?>" id="od_b_addr1" required class="frm_input required" size="35">
                                    <label for="od_b_addr1">기본주소</label><br>
                                    <input type="text" name="od_b_addr2" value="<?php echo sanitize_input($od['od_b_addr2']); ?>" id="od_b_addr2" class="frm_input" size="35">
                                    <label for="od_b_addr2">상세주소</label><br>
                                    <input type="text" name="od_b_addr3" value="<?php echo sanitize_input($od['od_b_addr3']); ?>" id="od_b_addr3" class="frm_input" size="35">
                                    <label for="od_b_addr3">참고항목</label>
                                    <input type="hidden" name="od_b_addr_jibeon" value="<?php echo sanitize_input($od['od_b_addr_jibeon']); ?>"><br>
                                </td>
                            </tr>

                            <?php if ($default['de_hope_date_use']) { ?>
                                <tr>
                                    <th scope="row"><label for="od_hope_date">희망배송일</label></th>
                                    <td>
                                        <input type="text" name="od_hope_date" value="<?php echo $od['od_hope_date']; ?>" id="od_hopedate" required class="frm_input required" maxlength="10" minlength="10"> (<?php echo get_yoil($od['od_hope_date']); ?>)
                                    </td>
                                </tr>
                            <?php } ?>

                            <tr>
                                <th scope="row">전달 메세지</th>
                                <td><?php if ($od['od_memo']) echo sanitize_input($od['od_memo'], 1);
                                    else echo "없음"; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>

        <div class="btn_confirm01 btn_confirm">
            <input type="submit" value="주문자/배송지 정보 수정" class="btn_submit btn ">
            <a href="./orderlist.php?<?php echo $qstr; ?>" class="btn">목록</a>
        </div>

    </form>
</section>

<!-- Modal -->
<div id="dateModal" class="subscription modal">
    <div class="modal-content">
        <h2 id="modalTitle" class="text-xl font-bold mb-4"></h2>
        <input type="text" id="datePicker" class="border p-2 w-full mb-4">
        <div class="flex justify-end">
            <button class="modal-submit bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">적용</button>
            <a rel="modal:close" class="bg-gray-500 text-white px-4 py-2 rounded mr-2 hover:bg-gray-600">취소</a>
        </div>
    </div>
</div>

<?php
$php_holidays = get_subscription_business_days();
$php_exception_dates = get_subscription_exception_dates();
?>
<script>
// 한국 공휴일 배열 (yyyy-mm-dd 형식)
var holidays = [];
var exception_dates = [];

<?php if ($php_holidays) { ?>
// 공휴일 날짜
holidays = <?php echo json_encode($php_holidays); ?>;
<?php } ?>
<?php if ($php_exception_dates) { ?>
// 영업일 지정 날짜
exception_dates = <?php echo json_encode($php_exception_dates); ?>;
<?php } ?>

// 영업일인지 확인
function isBusinessDay(date) {
    var dayOfWeek = date.getDay();
    var formattedDate = date.getFullYear() + '-' + String(date.getMonth() + 1).padStart(2, '0') + '-' + String(date.getDate()).padStart(2, '0');
    
    // 예외 날짜는 무조건 영업일로 처리
    if (exception_dates.indexOf(formattedDate) !== -1) {
        return true;
    }
    
    return dayOfWeek != 0 && dayOfWeek != 6 && holidays.indexOf(formattedDate) == -1;
}

let currentType = '';

function getKoreanWeekday(dateStr) {
    const days = ['(일)', '(월)', '(화)', '(수)', '(목)', '(금)', '(토)'];
    const date = new Date(dateStr);
    const dayIndex = date.getDay(); // 0: 일요일 ~ 6: 토요일
    return days[dayIndex];
}
    
jQuery(function($) {
    // 전체 옵션선택
    $("#sit_select_all").click(function() {
        if ($(this).is(":checked")) {
            $("input[name='it_sel[]']").attr("checked", true);
            $("input[name^=ct_chk]").attr("checked", true);
        } else {
            $("input[name='it_sel[]']").attr("checked", false);
            $("input[name^=ct_chk]").attr("checked", false);
        }
    });

    // 상품의 옵션선택
    $("input[name='it_sel[]']").click(function() {
        var cls = $(this).attr("id").replace("sit_", "sct_");
        var $chk = $("input[name^=ct_chk]." + cls);
        if ($(this).is(":checked"))
            $chk.attr("checked", true);
        else
            $chk.attr("checked", false);
    });

    // 개인결제추가
    $("#personalpay_add").on("click", function() {
        var href = this.href;
        window.open(href, "personalpaywin", "left=100, top=100, width=700, height=560, scrollbars=yes");
        return false;
    });

    // 부분취소창
    $("#orderpartcancel").on("click", function() {
        var href = this.href;
        window.open(href, "partcancelwin", "left=100, top=100, width=600, height=350, scrollbars=yes");
        return false;
    });

    $(document).on("click", ".modal-submit", function(e) {
        var selectedDate = $("#datePicker").val();
        
        if (!selectedDate) {
            alert('날짜를 선택해주세요.');
            return;
        }
        
        if (currentType === 'payment') {
            $('.hidden_datetime[name="next_billing_date"]').val(selectedDate);
            $(".next_billing_date_text").text(selectedDate + " "+ getKoreanWeekday(selectedDate));
        } else {
            $('.hidden_datetime[name="next_delivery_date"]').val(selectedDate);
            $(".next_delivery_date_text").text(selectedDate + " "+ getKoreanWeekday(selectedDate));
        }
        
        $(".subs-receiptform .warning").removeClass("hide");
            
        $.modal.close(); // 닫기
    });
    
    $(document).on("click", ".subscription-edit", function(e) {
        e.preventDefault();
        
        var $this = $(this);
        
        currentType = $this.attr("data-type");
            
        var title = (currentType === 'payment') ? '결제일 선택' : '배송일 선택',
            initialDate = $this.parent().find(".txt").text().replace(/\s*\(.*?\)/, "");
        
        $("#modalTitle").text(title);

        // 기존 datepicker 파괴 (중복 방지)
        $("#datePicker").datepicker("destroy");
        
        // 옵션 객체 구성
        const options = {
            dateFormat: 'yy-mm-dd',
            minDate: 0,
            firstDay: 1,
            setDate: initialDate
        };

        // 배송일인 경우에만 beforeShowDay 설정
        if (currentType !== 'payment') {
            options.beforeShowDay = function(date) {
                const today = new Date();
                const isToday =
                    date.getFullYear() === today.getFullYear() &&
                    date.getMonth() === today.getMonth() &&
                    date.getDate() === today.getDate();
                if (isToday) return [true, "today-highlight", "오늘"];
                return [isBusinessDay(date), '', ''];
            };
        }
        
        $("#datePicker").val(initialDate);
        $("#datePicker").datepicker(options);
        
        $("#dateModal").modal({    
        });
    });
    
    $(document).on("sit_sel_option_success", "#sit_sel_option li button", function(e, $button, mode, qty) {
        // mode는 "증가", "감소", "삭제" 중 하나
        // qty는 수량 또는 삭제일 경우 빈 문자열 ("")
        
        // console.log("이벤트 발생");
        // console.log("버튼 객체:", $button);
        // console.log("동작 모드:", mode);
        // console.log("현재 수량:", qty);

        // 예시 동작: 삭제 후 로그 남기기
        if (mode === "삭제") {
            var ct_id = $button.attr("data-ctid"),
                $input = $("input[name='ct_dels']"),
                currentVal = $input.val();

            // 중복 방지를 위해 쉼표로 구분된 목록에 추가
            var newVal = currentVal ? currentVal + "," + ct_id : ct_id;
            
            $input.val(newVal);
            // 여기에 삭제 후 처리 로직을 추가할 수 있습니다.
        }

        // 예시 동작: 수량 변경 시 다른 UI 업데이트
        if (mode === "증가" || mode === "감소") {
            // 예: 수량을 UI 상의 다른 요소에 반영
            // $("#some-element").text("현재 수량: " + qty);
        }
    });
});

function form_submit(f) {
    var status = document.pressed;

    var msg = "";

    if (status === "활성화") {
        msg = "구독을 활성화 하시겠습니까?";
    } else if (status === "비활성화") {
        msg = "구독을 비활성화 하시겠습니까?";
    } else {
        msg = "'" + status + "' 상태를 선택하셨습니다.\n\n선택하신대로 처리하시겠습니까?";
    }
    
    return confirm(msg);
}

function del_confirm() {
    if (confirm("주문서를 삭제하시겠습니까?")) {
        return true;
    } else {
        return false;
    }
}

// 기본 배송회사로 설정
function chk_delivery_company() {
    var chk = document.getElementById("od_delivery_chk");
    var company = document.getElementById("od_delivery_company");
    company.value = chk.checked ? chk.value : company.defaultValue;
}

// 현재 시간으로 배송일시 설정
function chk_invoice_time() {
    var chk = document.getElementById("od_invoice_chk");
    var time = document.getElementById("od_invoice_time");
    time.value = chk.checked ? chk.value : time.defaultValue;
}

// 결제금액 수동 설정
function chk_receipt_price() {
    var chk = document.getElementById("od_receipt_chk");
    var price = document.getElementById("od_receipt_price");
    price.value = chk.checked ? (parseInt(chk.value) + parseInt(price.defaultValue)) : price.defaultValue;
}
</script>

<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');

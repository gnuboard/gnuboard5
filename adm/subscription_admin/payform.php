<?php
$sub_menu = '600410';
include_once './_common.php';

$cart_title3 = '주문번호';
$cart_title4 = '배송완료';

$pay_id = isset($_REQUEST['pay_id']) ? preg_replace('/[^a-z0-9_\-]/i', '', $_REQUEST['pay_id']) : '';
$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : '';
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : '';
$pay_status = isset($_REQUEST['py_status']) ? clean_xss_tags($_REQUEST['py_status'], 1, 1) : '';
$pay_settle_case = isset($_REQUEST['py_settle_case']) ? clean_xss_tags($_REQUEST['py_settle_case'], 1, 1) : '';
$pay_misu = isset($_REQUEST['py_misu']) ? clean_xss_tags($_REQUEST['py_misu'], 1, 1) : '';
$pay_cancel_price = isset($_REQUEST['py_cancel_price']) ? clean_xss_tags($_REQUEST['py_cancel_price'], 1, 1) : '';
$pay_refund_price = isset($_REQUEST['py_refund_price']) ? clean_xss_tags($_REQUEST['py_refund_price'], 1, 1) : '';
$pay_receipt_point = isset($_REQUEST['py_receipt_point']) ? clean_xss_tags($_REQUEST['py_receipt_point'], 1, 1) : '';
$pay_coupon = isset($_REQUEST['py_coupon']) ? clean_xss_tags($_REQUEST['py_coupon'], 1, 1) : '';
$pay_escrow = isset($_REQUEST['py_escrow']) ? clean_xss_tags($_REQUEST['py_escrow'], 1, 1) : ''; 

// $pay_id = isset($_REQUEST['py_id']) ? safe_replace_regex($_REQUEST['py_id'], 'py_id') : '';


$sort1 = isset($_REQUEST['sort1']) ? clean_xss_tags($_REQUEST['sort1'], 1, 1) : '';
$sort2 = isset($_REQUEST['sort2']) ? clean_xss_tags($_REQUEST['sort2'], 1, 1) : '';
$sel_field = isset($_REQUEST['sel_field']) ? clean_xss_tags($_REQUEST['sel_field'], 1, 1) : '';
$search = isset($_REQUEST['search']) ? get_search_string($_REQUEST['search']) : '';

// 완료된 주문에 포인트를 적립한다.
save_order_point("완료");

if (! $pay_id) {
    alert("잘못된 요청입니다. pay_id");
}

//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$pay = get_subscription_pay($pay_id);

if (!(isset($pay['pay_id']) && $pay['pay_id'])) {
    alert("해당 정기결제 내역이 존재하지 않습니다.");
}

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = "정기결제 주문 내역 수정";
include_once(G5_ADMIN_PATH.'/admin.head.php');

include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$pay['mb_id'] = $pay['mb_id'] ? $pay['mb_id'] : "비회원";
//------------------------------------------------------------------------------

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_sodr_list">주문상품 목록</a></li>
<li><a href="#anc_sodr_pay">주문결제 내역</a></li>
<li><a href="#anc_sodr_chk">결제상세정보 확인</a></li>
<li><a href="#anc_sodr_paymo">결제상세정보 수정</a></li>
<li><a href="#anc_sodr_memo">상점메모</a></li>
<li><a href="#anc_sodr_taker">받으시는 분</a></li>
</ul>';

$html_receipt_chk = '<input type="checkbox" id="py_receipt_chk" value="'.$pay['py_misu'].'" onclick="chk_receipt_price()">
<label for="py_receipt_chk">결제금액 입력</label><br>';

$qstr1 = "py_status=".urlencode($pay_status)."&amp;py_settle_case=".urlencode($pay_settle_case)."&amp;py_misu=$pay_misu&amp;py_cancel_price=$pay_cancel_price&amp;py_refund_price=$pay_refund_price&amp;py_receipt_point=$pay_receipt_point&amp;py_coupon=$pay_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if($default['de_escrow_use'])
    $qstr1 .= "&amp;py_escrow=$pay_escrow";
$qstr = "$qstr1&amp;sort1=$sort1&amp;sort2=$sort2&amp;page=$page";

$sql = "SELECT * 
        FROM {$g5['g5_subscription_pay_basket_table']} 
        WHERE pay_id = '" . $pay['pay_id'] . "'";
$pay_basket = sql_fetch($sql);

// 상품목록
$sql = "SELECT it_id, it_name, cp_price, pb_notax, pb_send_cost, it_sc_type 
        FROM {$g5['g5_subscription_pay_basket_table']} 
        WHERE pay_id = '" . $pay['pay_id'] . "' 
        GROUP BY it_id 
        ORDER BY pb_id";
$result = sql_query($sql);

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<section id="anc_sodr_list">
    <h2 class="h2_frm">주문상품 목록</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            현재 주문상태 <strong><?php echo $pay['py_status'] ?></strong>
            |
            주문일시 <strong><?php echo substr($pay['py_time'],0,16); ?> (<?php echo get_yoil($pay['py_time']); ?>)</strong>
            |
            주문총액 <strong><?php echo number_format($pay['py_cart_price'] + $pay['py_send_cost'] + $pay['py_send_cost2']); ?></strong>원
        </p>
        <?php if ($default['de_hope_date_use']) { ?><p>희망배송일은 <?php echo $pay['py_hope_date']; ?> (<?php echo get_yoil($pay['py_hope_date']); ?>) 입니다.</p><?php } ?>
    </div>

    <form name="frmorderform" method="post" action="./subscription_pay_cartupdate.php" onsubmit="return form_submit(this);">
    <input type="hidden" name="pay_id" value="<?php echo get_sanitize_input($pay['pay_id']); ?>">
    <input type="hidden" name="od_id" value="<?php echo get_sanitize_input($pay['od_id']); ?>">
    <input type="hidden" name="mb_id" value="<?php echo get_sanitize_input($pay['mb_id']); ?>">
    <input type="hidden" name="py_email" value="<?php echo get_sanitize_input($pay['py_email']); ?>">
    <input type="hidden" name="sort1" value="<?php echo get_sanitize_input($sort1); ?>">
    <input type="hidden" name="sort2" value="<?php echo get_sanitize_input($sort2); ?>">
    <input type="hidden" name="sel_field" value="<?php echo get_sanitize_input($sel_field); ?>">
    <input type="hidden" name="search" value="<?php echo get_sanitize_input($search); ?>">
    <input type="hidden" name="page" value="<?php echo get_sanitize_input($page);?>">
    <input type="hidden" name="pg_cancel" value="0">

    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption>주문 상품 목록</caption>
        <thead>
        <tr>
            <th scope="col">상품명</th>
            <th scope="col">
                <label for="sit_select_all" class="sound_only">주문 상품 전체</label>
                <input type="checkbox" id="sit_select_all">
            </th>
            <th scope="col">옵션항목</th>
            <th scope="col">상태</th>
            <th scope="col">수량</th>
            <th scope="col">판매가</th>
            <th scope="col">소계</th>
            <th scope="col">쿠폰</th>
            <th scope="col">포인트</th>
            <th scope="col">배송비</th>
            <th scope="col">포인트반영</th>
            <th scope="col">재고반영</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $chk_cnt = 0;
        for($i=0; $row=sql_fetch_array($result); $i++) {
            // 상품이미지
            $image = get_it_image($row['it_id'], 50, 50);

            // 상품의 옵션정보
            $sql = "SELECT pb_id, it_id, pb_price, pb_point, pb_qty, pb_option, pb_status, cp_price, pb_stock_use, pb_point_use, pb_send_cost, io_type, io_price 
                    FROM {$g5['g5_subscription_pay_basket_table']} 
                    WHERE pay_id = '" . $pay['pay_id'] . "' 
                    AND it_id = '" . $row['it_id'] . "' 
                    ORDER BY io_type ASC, pb_id ASC";
            $res = sql_query($sql);

            $rowspan = sql_num_rows($res);
            
            $sql = "SELECT SUM(IF(io_type = 1, (io_price * pb_qty), ((pb_price + io_price) * pb_qty))) AS price, 
                        SUM(pb_qty) AS qty 
                    FROM {$g5['g5_subscription_pay_basket_table']} 
                    WHERE it_id = '" . $row['it_id'] . "' 
                    AND pay_id = '" . $pay['pay_id'] . "'";
            $sum = sql_fetch($sql);
            
            // 배송비
            switch($row['pb_send_cost'])
            {
                case 1:
                    $pb_send_cost = '착불';
                    break;
                case 2:
                    $pb_send_cost = '무료';
                    break;
                default:
                    $pb_send_cost = '선불';
                    break;
            }

            // 조건부무료
            if($row['it_sc_type'] == 2) {
                $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $pay['od_id']);

                if($sendcost == 0)
                    $pb_send_cost = '무료';
            }

            for($k=0; $opt=sql_fetch_array($res); $k++) {
                if($opt['io_type'])
                    $opt_price = $opt['io_price'];
                else
                    $opt_price = $opt['pb_price'] + $opt['io_price'];

                // 소계
                $pb_price['stotal'] = $opt_price * $opt['pb_qty'];
                $pb_point['stotal'] = $opt['pb_point'] * $opt['pb_qty'];
            ?>
            <tr>
                <?php if($k == 0) { ?>
                <td rowspan="<?php echo $rowspan; ?>" class="td_left">
                    <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>"><?php echo $image; ?> <?php echo stripslashes($row['it_name']); ?></a>
                    <?php if($pay['py_tax_flag'] && $row['pb_notax']) echo '[비과세상품]'; ?>
                </td>
                <td rowspan="<?php echo $rowspan; ?>" class="td_chk">
                    <label for="sit_sel_<?php echo $i; ?>" class="sound_only"><?php echo $row['it_name']; ?> 옵션 전체선택</label>
                    <input type="checkbox" id="sit_sel_<?php echo $i; ?>" name="it_sel[]">
                </td>
                <?php } ?>
                <td class="td_left">
                    <label for="pb_chk_<?php echo $chk_cnt; ?>" class="sound_only"><?php echo get_sanitize_input($opt['pb_option']); ?></label>
                    <input type="checkbox" name="pb_chk[<?php echo $chk_cnt; ?>]" id="pb_chk_<?php echo $chk_cnt; ?>" value="<?php echo $chk_cnt; ?>" class="spb_sel_<?php echo $i; ?>">
                    <input type="hidden" name="pb_id[<?php echo $chk_cnt; ?>]" value="<?php echo $opt['pb_id']; ?>">
                    <?php echo get_sanitize_input($opt['pb_option']); ?>
                </td>
                <td class="td_mngsmall"><?php echo $opt['pb_status']; ?></td>
                <td class="td_num">
                    <label for="pb_qty_<?php echo $chk_cnt; ?>" class="sound_only"><?php echo get_sanitize_input($opt['pb_option']); ?> 수량</label>
                    <input type="text" name="pb_qty[<?php echo $chk_cnt; ?>]" id="pb_qty_<?php echo $chk_cnt; ?>" value="<?php echo $opt['pb_qty']; ?>" required class="frm_input required" size="5">
                </td>
                <td class="td_num_right "><?php echo number_format($opt_price); ?></td>
                <td class="td_num_right"><?php echo number_format($pb_price['stotal']); ?></td>
                <td class="td_num_right"><?php echo number_format($opt['cp_price']); ?></td>
                <td class=" td_num_right"><?php echo number_format($pb_point['stotal']); ?></td>
                <td class="td_sendcost_by"><?php echo $pb_send_cost; ?></td>
                <td class="td_mngsmall"><?php echo get_yn($opt['pb_point_use']); ?></td>
                <td class="td_mngsmall"><?php echo get_yn($opt['pb_stock_use']); ?></td>
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
            <strong>주문 상태 변경</strong>
            <input type="submit" name="pb_status" value="입금" onclick="document.pressed=this.value" class="btn_02 color_02">
            <input type="submit" name="pb_status" value="준비" onclick="document.pressed=this.value" class="btn_02 color_03">
            <input type="submit" name="pb_status" value="배송" onclick="document.pressed=this.value" class="btn_02 color_04">
            <input type="submit" name="pb_status" value="완료" onclick="document.pressed=this.value" class="btn_02 color_05">
            <input type="submit" name="pb_status" value="취소" onclick="document.pressed=this.value" class="btn_02 color_06">
            <input type="submit" name="pb_status" value="반품" onclick="document.pressed=this.value" class="btn_02 color_06">
            <input type="submit" name="pb_status" value="품절" onclick="document.pressed=this.value" class="btn_02 color_06">
        </p>
    </div>

    <div class="local_desc01 local_desc">
        <p>주문, 입금, 준비, 배송, 완료는 장바구니와 주문서 상태를 모두 변경하지만, 취소, 반품, 품절은 장바구니의 상태만 변경하며, 주문서 상태는 변경하지 않습니다.</p>
        <p>개별적인(이곳에서의) 상태 변경은 모든 작업을 수동으로 처리합니다. 예를 들어 주문에서 입금으로 상태 변경시 입금액(결제금액)을 포함한 모든 정보는 수동 입력으로 처리하셔야 합니다.</p>
    </div>

    </form>

    <?php if ($pay['py_mod_history']) { ?>
    <section id="sodr_qty_log">
        <h3>주문 수량변경 및 주문 전체취소 처리 내역</h3>
        <div>
            <?php echo conv_content($pay['py_mod_history'], 0); ?>
        </div>
    </section>
    <?php } ?>

</section>

<?php if($pay['py_test']) { ?>
<div class="py_test_caution od_test_caution">주의) 이 주문은 테스트용으로 실제 결제가 이루어지지 않았으므로 절대 배송하시면 안됩니다.</div>
<?php } ?>

<section id="anc_sodr_pay">
    <h2 class="h2_frm">주문결제 내역</h2>
    <?php echo $pg_anchor; ?>

    <?php
    // 주문금액 = 상품구입금액 + 배송비 + 추가배송비
    $amount['order'] = $pay['py_cart_price'] + $pay['py_send_cost'] + $pay['py_send_cost2'];

    // 입금액 = 결제금액 + 포인트
    $amount['receipt'] = $pay['py_receipt_price'] + $pay['py_receipt_point'];

    // 쿠폰금액
    $amount['coupon'] = $pay['py_cart_coupon'] + $pay['py_coupon'] + $pay['py_send_coupon'];

    // 취소금액
    $amount['cancel'] = $pay['py_cancel_price'];

    // 미수금 = 주문금액 - 취소금액 - 입금금액 - 쿠폰금액
    //$amount['미수'] = $amount['order'] - $amount['receipt'] - $amount['coupon'];

    // 결제방법
    $s_receipt_way = check_pay_name_replace($pay['py_settle_case'], $pay);

    if ($pay['py_receipt_point'] > 0)
        $s_receipt_way .= "+포인트";
    ?>

    <div class="tbl_head01 tbl_wrap">
        <strong class="sodr_nonpay">미수금 <?php echo display_price($pay['py_misu']); ?></strong>

        <table>
        <caption>주문결제 내역</caption>
        <thead>
        <tr>
            <th scope="col">구독번호</th>
            <th scope="col">주문번호</th>
            <th scope="col">결제방법</th>
            <th scope="col">주문총액</th>
            <th scope="col">배송비</th>
            <th scope="col">총결제액</th>
            <th scope="col">주문취소</th>
            <th scope="col">쿠폰</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><a href="./orderform.php?od_id=<?php echo $pay['od_id']; ?>" target="_blank"><?php echo $pay['od_id']; ?></a></td>
            <td><?php echo $pay['subscription_pg_id']; ?></td>
            <td class="td_paybybig"><?php echo $s_receipt_way; ?></td>
            <td class="td_numbig td_numsum"><?php echo display_price($amount['order']); ?></td>
            <td class="td_numbig"><?php echo display_price($pay['py_send_cost'] + $pay['py_send_cost2']); ?></td>
            <td class="td_numbig td_numincome"><?php echo number_format($amount['receipt']); ?>원</td>
            <td class="td_numbig td_numcancel"><?php echo number_format($amount['cancel']); ?>원</td>
            <td class="td_numbig td_numcoupon"><?php echo number_format($amount['coupon']); ?></td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section class="">
    <h2 class="h2_frm">결제상세정보</h2>
    <?php echo $pg_anchor; ?>

    <form name="frmorderreceiptform" action="./payformreceiptupdate.php" method="post" autocomplete="off">
    <input type="hidden" name="pay_id" value="<?php echo get_sanitize_input($pay_id); ?>">
    <input type="hidden" name="sort1" value="<?php echo get_sanitize_input($sort1); ?>">
    <input type="hidden" name="sort2" value="<?php echo get_sanitize_input($sort2); ?>">
    <input type="hidden" name="sel_field" value="<?php echo get_sanitize_input($sel_field); ?>">
    <input type="hidden" name="search" value="<?php echo get_sanitize_input($search); ?>">
    <input type="hidden" name="page" value="<?php echo get_sanitize_input($page); ?>">
    <input type="hidden" name="py_name" value="<?php echo get_sanitize_input($pay['py_name']); ?>">
    <input type="hidden" name="py_hp" value="<?php echo get_sanitize_input($pay['py_hp']); ?>">
    <input type="hidden" name="py_tno" value="<?php echo get_sanitize_input($pay['py_tno']); ?>">
    <input type="hidden" name="py_escrow" value="<?php echo get_sanitize_input($pay['py_escrow']); ?>">
    <input type="hidden" name="py_pg" value="<?php echo get_sanitize_input($pay['py_pg']); ?>">

    <div class="compare_wrap">

        <section id="anc_sodr_chk" class="compare_left">
            <h3>결제상세정보 확인</h3>

            <div class="tbl_frm01">
                <table>
                <caption>결제상세정보</caption>
                <colgroup>
                    <col class="grid_3">
                    <col>
                </colgroup>
                <tbody>

                <tr>
                    <th scope="row" class="sodr_sppay">신용카드 결제금액</th>
                    <td>
                        <?php if ($pay['py_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                        <?php } else { ?><?php echo display_price($pay['py_receipt_price']); ?>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="sodr_sppay">카드 승인일시</th>
                    <td>
                        <?php if ($pay['py_receipt_time'] == "0000-00-00 00:00:00") {?>신용카드 결제 일시 정보가 없습니다.
                        <?php } else { ?><?php echo substr($pay['py_receipt_time'], 0, 20); ?>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="sodr_sppay">배송일(예정)</th>
                    <td>
                        <input type="text" name="next_delivery_date" value="<?php echo date('Y-m-d', strtotime($pay['next_delivery_date'])); ?>" id="next_delivery_date" class="frm_input">
                    </td>
                </tr>
                <tr>
                    <th scope="row">결제대행사 링크</th>
                    <td>
                        <?php
                        if ($pay['py_settle_case'] != '무통장') {
                            switch($pay['py_pg']) {
                                case 'lg':
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
                                case 'KAKAOPAY':
                                    $pg_url  = 'https://mms.cnspay.co.kr';
                                    $pg_test = 'KAKAOPAY';
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
                        }
                        //------------------------------------------------------------------------------
                        ?>
                    </td>
                </tr>

                <?php if($pay['py_tax_flag']) { ?>
                <tr>
                    <th scope="row">과세공급가액</th>
                    <td><?php echo display_price($pay['py_tax_mny']); ?></td>
                </tr>
                <tr>
                    <th scope="row">과세부가세액</th>
                    <td><?php echo display_price($pay['py_vat_mny']); ?></td>
                </tr>
                <tr>
                    <th scope="row">비과세공급가액</th>
                    <td><?php echo display_price($pay['py_free_mny']); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th scope="row">주문금액할인</th>
                    <td><?php echo display_price($pay['py_coupon']); ?></td>
                </tr>
                <tr>
                    <th scope="row">포인트</th>
                    <td><?php echo display_point($pay['py_receipt_point']); ?></td>
                </tr>
                <tr>
                    <th scope="row">결제취소/환불액</th>
                    <td><?php echo display_price($pay['py_refund_price']); ?></td>
                </tr>
                <?php if ($pay['py_invoice']) { ?>
                <tr>
                    <th scope="row">배송회사</th>
                    <td><?php echo $pay['py_delivery_company']; ?> <?php echo get_delivery_inquiry($pay['py_delivery_company'], $pay['py_invoice'], 'dvr_link'); ?></td>
                </tr>
                <tr>
                    <th scope="row">운송장번호</th>
                    <td><?php echo $pay['py_invoice']; ?></td>
                </tr>
                <tr>
                    <th scope="row">배송일시</th>
                    <td><?php echo is_null_time($pay['py_invoice_time']) ? "" : $pay['py_invoice_time']; ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th scope="row"><label for="py_send_cost">배송비</label></th>
                    <td>
                        <input type="text" name="py_send_cost" value="<?php echo $pay['py_send_cost']; ?>" id="py_send_cost" class="frm_input" size="10"> 원
                    </td>
                </tr>
                <?php if($pay['py_send_coupon']) { ?>
                <tr>
                    <th scope="row">배송비할인</th>
                    <td><?php echo display_price($pay['py_send_coupon']); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th scope="row"><label for="py_send_cost2">추가배송비</label></th>
                    <td>
                        <input type="text" name="py_send_cost2" value="<?php echo $pay['py_send_cost2']; ?>" id="py_send_cost2" class="frm_input" size="10"> 원
                    </td>
                </tr>

                </tbody>
                </table>
            </div>
        </section>

        <section id="anc_sodr_paymo" class="compare_right">
            <h3>결제상세정보 수정</h3>

            <div class="tbl_frm01">
                <table>
                <caption>결제상세정보 수정</caption>
                <colgroup>
                    <col class="grid_3">
                    <col>
                </colgroup>
                <tbody>
                <tr>
                    <th scope="row" class="sodr_sppay"><label for="py_receipt_price">신용카드 결제금액</label></th>
                    <td>
                        <?php echo $html_receipt_chk; ?>
                        <input type="text" name="py_receipt_price" id="py_receipt_price" value="<?php echo $pay['py_receipt_price']; ?>" class="frm_input" size="10"> 원
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="sodr_sppay"><label for="py_receipt_time">카드 승인일시</label></th>
                    <td>
                        <input type="checkbox" name="py_card_chk" id="py_card_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.py_receipt_time.value=this.form.py_card_chk.value; else this.form.py_receipt_time.value = this.form.py_receipt_time.defaultValue;">
                        <label for="py_card_chk">현재 시간으로 설정</label><br>
                        <input type="text" name="py_receipt_time" value="<?php echo is_null_time($pay['py_receipt_time']) ? "" : $pay['py_receipt_time']; ?>" id="py_receipt_time" class="frm_input" size="19" maxlength="19">
                    </td>
                </tr>

                <tr>
                    <th scope="row"><label for="py_refund_price">결제취소/환불 금액</label></th>
                    <td>
                        <input type="text" name="py_refund_price" value="<?php echo $pay['py_refund_price']; ?>" id="py_refund_price" class="frm_input" size="10"> 원
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="py_invoice">운송장번호</label></th>
                    <td>
                        <?php if ($config['cf_sms_use'] && $default['de_sms_use5']) { ?>
                        <input type="checkbox" name="py_sms_baesong_check" id="py_sms_baesong_check">
                        <label for="py_sms_baesong_check">SMS 배송 문자전송</label>
                        <br>
                        <?php } ?>
                        <input type="text" name="py_invoice" value="<?php echo $pay['py_invoice']; ?>" id="py_invoice" class="frm_input">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="py_delivery_company">배송회사</label></th>
                    <td>
                        <input type="checkbox" id="py_delivery_chk" value="<?php echo $default['de_delivery_company']; ?>" onclick="chk_delivery_company()">
                        <label for="py_delivery_chk">기본 배송회사로 설정</label><br>
                        <input type="text" name="py_delivery_company" id="py_delivery_company" value="<?php echo $pay['py_delivery_company']; ?>" class="frm_input">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="py_invoice_time">배송일시</label></th>
                    <td>
                        <input type="checkbox" id="py_invoice_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="chk_invoice_time()">
                        <label for="py_invoice_chk">현재 시간으로 설정</label><br>
                        <input type="text" name="py_invoice_time" id="py_invoice_time" value="<?php echo is_null_time($pay['py_invoice_time']) ? "" : $pay['py_invoice_time']; ?>" class="frm_input" maxlength="19">
                    </td>
                </tr>

                <?php if ($config['cf_email_use']) { ?>
                <tr>
                    <th scope="row"><label for="py_send_mail">메일발송</label></th>
                    <td>
                        <?php echo help("주문자님께 입금, 배송내역을 메일로 발송합니다.\n메일발송시 상점메모에 기록됩니다."); ?>
                        <input type="checkbox" name="py_send_mail" value="1" id="py_send_mail"> 메일발송
                    </td>
                </tr>
                <?php } ?>

                </tbody>
                </table>
            </div>
        </section>

    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="결제/배송내역 수정" class="btn_submit btn">
        <a href="./paylist.php?<?php echo $qstr; ?>" class="btn btn_02">목록</a>
    </div>
    </form>
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

    <form name="frmorderform2" action="./payformupdate.php" method="post">
    <input type="hidden" name="pay_id" value="<?php echo get_sanitize_input($pay_id); ?>">
    <input type="hidden" name="sort1" value="<?php echo get_sanitize_input($sort1); ?>">
    <input type="hidden" name="sort2" value="<?php echo get_sanitize_input($sort2); ?>">
    <input type="hidden" name="sel_field" value="<?php echo get_sanitize_input($sel_field); ?>">
    <input type="hidden" name="search" value="<?php echo get_sanitize_input($search); ?>">
    <input type="hidden" name="page" value="<?php echo get_sanitize_input($page); ?>">
    <input type="hidden" name="mpy_type" value="memo">

    <div class="tbl_wrap">
        <label for="py_subscription_memo" class="sound_only">상점메모</label>
        <textarea name="py_subscription_memo" id="py_subscription_memo" rows="8"><?php echo html_purifier(stripslashes($pay['py_subscription_memo'])); ?></textarea>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="메모 수정" class="btn_submit btn">
    </div>

    </form>
</section>

<section>
    <h2 class="h2_frm">주문자/배송지 정보</h2>
    <?php echo $pg_anchor; ?>

    <form name="frmorderform3" action="./payformupdate.php" method="post">
    <input type="hidden" name="pay_id" value="<?php echo get_sanitize_input($pay_id); ?>">
    <input type="hidden" name="sort1" value="<?php echo get_sanitize_input($sort1); ?>">
    <input type="hidden" name="sort2" value="<?php echo get_sanitize_input($sort2); ?>">
    <input type="hidden" name="sel_field" value="<?php echo get_sanitize_input($sel_field); ?>">
    <input type="hidden" name="search" value="<?php echo get_sanitize_input($search); ?>">
    <input type="hidden" name="page" value="<?php echo get_sanitize_input($page); ?>">
    <input type="hidden" name="mpy_type" value="info">

    <div class="compare_wrap">

        <section id="anc_sodr_taker">
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
                    <th scope="row"><label for="py_b_name"><span class="sound_only">받으시는 분 </span>이름</label></th>
                    <td><input type="text" name="py_b_name" value="<?php echo get_sanitize_input($pay['py_b_name']); ?>" id="py_b_name" required class="frm_input required"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="py_b_tel"><span class="sound_only">받으시는 분 </span>연락처</label></th>
                    <td>
                    <input type="text" name="py_b_tel" value="<?php echo get_sanitize_input($pay['py_b_tel']); ?>" id="py_b_tel" required class="frm_input required">
                    <input type="hidden" name="py_b_hp" value="<?php echo get_sanitize_input($pay['py_b_hp']); ?>" id="py_b_hp">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
                    <td>
                        <label for="py_b_zip" class="sound_only">우편번호</label>
                        <input type="text" name="py_b_zip" value="<?php echo get_sanitize_input($pay['py_b_zip']); ?>" id="py_b_zip" required class="frm_input required" size="5">
                        <button type="button" class="btn_frmline" onclick="win_zip('frmorderform3', 'py_b_zip', 'py_b_addr1', 'py_b_addr2', 'py_b_addr3', 'py_b_addr_jibeon');">주소 검색</button><br>
                        <input type="text" name="py_b_addr1" value="<?php echo get_sanitize_input($pay['py_b_addr1']); ?>" id="py_b_addr1" required class="frm_input required" size="35">
                        <label for="py_b_addr1">기본주소</label>
                        <br>
                        <input type="text" name="py_b_addr2" value="<?php echo get_sanitize_input($pay['py_b_addr2']); ?>" id="py_b_addr2" class="frm_input" size="35">
                        <label for="py_b_addr2">상세주소</label>
                        <br>
                        <input type="text" name="py_b_addr3" value="<?php echo get_sanitize_input($pay['py_b_addr3']); ?>" id="py_b_addr3" class="frm_input" size="35">
                        <label for="py_b_addr3">참고항목</label>
                        <input type="hidden" name="py_b_addr_jibeon" value="<?php echo get_sanitize_input($pay['py_b_addr_jibeon']); ?>"><br>
                    </td>
                </tr>

                <?php if ($default['de_hope_date_use']) { ?>
                <tr>
                    <th scope="row"><label for="py_hope_date">희망배송일</label></th>
                    <td>
                        <input type="text" name="py_hope_date" value="<?php echo $pay['py_hope_date']; ?>" id="py_hopedate" required class="frm_input required" maxlength="10" minlength="10"> (<?php echo get_yoil($pay['py_hope_date']); ?>)
                    </td>
                </tr>
                <?php } ?>

                <tr>
                    <th scope="row">전달 메세지</th>
                    <td><?php if ($pay['py_memo']) echo get_sanitize_input($pay['py_memo'], 1);else echo "없음";?></td>
                </tr>
                </tbody>
                </table>
            </div>
        </section>

    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="주문자/배송지 정보 수정" class="btn_submit btn ">
        <a href="./paylist.php?<?php echo $qstr; ?>" class="btn">목록</a>
    </div>

    </form>
</section>

<?php
$php_holidays = get_subscription_business_days();
$php_exception_dates = get_subscription_exception_dates();
?>

<script>
$(function() {
    
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

// 옵션 객체 구성
const options = {
    dateFormat: 'yy-mm-dd',
    minDate: 0,
    firstDay: 1
};

// 배송일인 경우에만 beforeShowDay 설정
options.beforeShowDay = function(date) {
    const today = new Date();
    const isToday =
        date.getFullYear() === today.getFullYear() &&
        date.getMonth() === today.getMonth() &&
        date.getDate() === today.getDate();
    if (isToday) return [true, "today-highlight", "오늘"];
    return [isBusinessDay(date), '', ''];
};

$("#next_delivery_date").datepicker(options);
        
    // 전체 옵션선택
    $("#sit_select_all").click(function() {
        if($(this).is(":checked")) {
            $("input[name='it_sel[]']").attr("checked", true);
            $("input[name^=pb_chk]").attr("checked", true);
        } else {
            $("input[name='it_sel[]']").attr("checked", false);
            $("input[name^=pb_chk]").attr("checked", false);
        }
    });

    // 상품의 옵션선택
    $("input[name='it_sel[]']").click(function() {
        var cls = $(this).attr("id").replace("sit_", "sct_");
        var $chk = $("input[name^=pb_chk]."+cls);
        if($(this).is(":checked"))
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
});

function form_submit(f)
{
    var check = false;
    var status = document.pressed;

    for (i=0; i<f.chk_cnt.value; i++) {
        if (document.getElementById('pb_chk_'+i).checked == true)
            check = true;
    }

    if (check == false) {
        alert("처리할 자료를 하나 이상 선택해 주십시오.");
        return false;
    }

    var msg = "";

    <?php if (is_cancel_subscription_pg_order($pay)) { ?>
    if(status == "취소" || status == "반품" || status == "품절") {
        var $pb_chk = $("input[name^=pb_chk]");
        var chk_cnt = $pb_chk.length;
        var chked_cnt = $pb_chk.filter(":checked").length;

        var cancel_pg = "PG사의 <?php echo $pay['py_settle_case']; ?>";

        if(chk_cnt == chked_cnt) {
            if(confirm(cancel_pg+" 결제를 함께 취소하시겠습니까?\n\n한번 취소한 결제는 다시 복구할 수 없습니다.")) {
                f.pg_cancel.value = 1;
                msg = cancel_pg+" 결제 취소와 함께 ";
            } else {
                f.pg_cancel.value = 0;
                msg = "";
            }
        }
    }
    <?php } ?>

    if (confirm(msg+"\'" + status + "\' 상태를 선택하셨습니다.\n\n선택하신대로 처리하시겠습니까?")) {
        return true;
    } else {
        return false;
    }
}

function del_confirm()
{
    if(confirm("주문서를 삭제하시겠습니까?")) {
        return true;
    } else {
        return false;
    }
}

// 기본 배송회사로 설정
function chk_delivery_company()
{
    var chk = document.getElementById("py_delivery_chk");
    var company = document.getElementById("py_delivery_company");
    company.value = chk.checked ? chk.value : company.defaultValue;
}

// 현재 시간으로 배송일시 설정
function chk_invoice_time()
{
    var chk = document.getElementById("py_invoice_chk");
    var time = document.getElementById("py_invoice_time");
    time.value = chk.checked ? chk.value : time.defaultValue;
}

// 결제금액 수동 설정
function chk_receipt_price()
{
    var chk = document.getElementById("py_receipt_chk");
    var price = document.getElementById("py_receipt_price");
    price.value = chk.checked ? (parseInt(chk.value) + parseInt(price.defaultValue)) : price.defaultValue;
}
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
<?php
$sub_menu = '600400';
include_once('./_common.php');

$cart_title3 = '주문번호';
$cart_title4 = '배송완료';

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = "주문 내역 수정";
include_once(G5_ADMIN_PATH.'/admin.head.php');

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


//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g5['g5_subscription_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (! (isset($od['od_id']) && $od['od_id'])) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$od['mb_id'] = $od['mb_id'] ? $od['mb_id'] : "비회원";
//------------------------------------------------------------------------------


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

$qstr1 = "od_enable_status=".urlencode($od_enable_status)."&amp;od_settle_case=".urlencode($od_settle_case)."&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
if($default['de_escrow_use'])
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

<section id="anc_sodr_list">
    <h2 class="h2_frm">구독상품 목록</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            현재 구독상태 <strong><?php echo $od['od_enable_status'] ?></strong>
            |
            주문일시 <strong><?php echo substr($od['od_time'],0,16); ?> (<?php echo get_yoil($od['od_time']); ?>)</strong>
            |
            구독금액 <strong><?php echo number_format($od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2']); ?></strong>원
        </p>
        <?php if ($default['de_hope_date_use']) { ?><p>희망배송일은 <?php echo $od['od_hope_date']; ?> (<?php echo get_yoil($od['od_hope_date']); ?>) 입니다.</p><?php } ?>
        <?php if($od['od_mobile']) { ?>
        <p>모바일 쇼핑몰의 주문입니다.</p>
        <?php } ?>
    </div>

    <form name="frmorderform" method="post" action="./orderformcartupdate.php" onsubmit="return form_submit(this);">
    <input type="hidden" name="od_id" value="<?php echo get_sanitize_input($od_id); ?>">
    <input type="hidden" name="mb_id" value="<?php echo get_sanitize_input($od['mb_id']); ?>">
    <input type="hidden" name="od_email" value="<?php echo get_sanitize_input($od['od_email']); ?>">
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
                $sendcost = get_item_sendcost($row['it_id'], $sum['price'], $sum['qty'], $od['od_id']);

                if($sendcost == 0)
                    $ct_send_cost = '무료';
            }

            for($k=0; $opt=sql_fetch_array($res); $k++) {
                if($opt['io_type'])
                    $opt_price = $opt['io_price'];
                else
                    $opt_price = $opt['ct_price'] + $opt['io_price'];

                // 소계
                $ct_price['stotal'] = $opt_price * $opt['ct_qty'];
                $ct_point['stotal'] = $opt['ct_point'] * $opt['ct_qty'];
            ?>
            <tr>
                <?php if($k == 0) { ?>
                <td rowspan="<?php echo $rowspan; ?>" class="td_left">
                    <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>"><?php echo $image; ?> <?php echo stripslashes($row['it_name']); ?></a>
                    <?php if($od['od_tax_flag'] && $row['ct_notax']) echo '[비과세상품]'; ?>
                </td>
                <td rowspan="<?php echo $rowspan; ?>" class="td_chk">
                    <label for="sit_sel_<?php echo $i; ?>" class="sound_only"><?php echo $row['it_name']; ?> 옵션 전체선택</label>
                    <input type="checkbox" id="sit_sel_<?php echo $i; ?>" name="it_sel[]">
                </td>
                <?php } ?>
                <td class="td_left">
                    <label for="ct_chk_<?php echo $chk_cnt; ?>" class="sound_only"><?php echo get_text($opt['ct_option']); ?></label>
                    <input type="checkbox" name="ct_chk[<?php echo $chk_cnt; ?>]" id="ct_chk_<?php echo $chk_cnt; ?>" value="<?php echo $chk_cnt; ?>" class="sct_sel_<?php echo $i; ?>">
                    <input type="hidden" name="ct_id[<?php echo $chk_cnt; ?>]" value="<?php echo $opt['ct_id']; ?>">
                    <?php echo get_text($opt['ct_option']); ?>
                </td>
                <td class="td_mngsmall"><?php echo $opt['ct_status']; ?></td>
                <td class="td_num">
                    <label for="ct_qty_<?php echo $chk_cnt; ?>" class="sound_only"><?php echo get_text($opt['ct_option']); ?> 수량</label>
                    <input type="text" name="ct_qty[<?php echo $chk_cnt; ?>]" id="ct_qty_<?php echo $chk_cnt; ?>" value="<?php echo $opt['ct_qty']; ?>" required class="frm_input required" size="5">
                </td>
                <td class="td_num_right "><?php echo number_format($opt_price); ?></td>
                <td class="td_num_right"><?php echo number_format($ct_price['stotal']); ?></td>
                <td class="td_num_right"><?php echo number_format($opt['cp_price']); ?></td>
                <td class=" td_num_right"><?php echo number_format($ct_point['stotal']); ?></td>
                <td class="td_sendcost_by"><?php echo $ct_send_cost; ?></td>
                <td class="td_mngsmall"><?php echo get_yn($opt['ct_point_use']); ?></td>
                <td class="td_mngsmall"><?php echo get_yn($opt['ct_stock_use']); ?></td>
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

<?php if($od['od_test']) { ?>
<div class="od_test_caution">주의) 이 주문은 테스트용으로 실제 결제가 이루어지지 않았으므로 절대 배송하시면 안됩니다.</div>
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

    // 취소금액
    // $amount['cancel'] = $od['od_cancel_price'];

    // 미수금 = 주문금액 - 취소금액 - 입금금액 - 쿠폰금액
    //$amount['미수'] = $amount['order'] - $amount['receipt'] - $amount['coupon'];

    // 결제방법
    $s_receipt_way = check_pay_name_replace($od['od_settle_case'], $od);

    if ($od['od_receipt_point'] > 0)
        $s_receipt_way .= "+포인트";
    ?>

    <div class="tbl_head01 tbl_wrap">

        <table>
        <caption>주문결제 내역</caption>
        <thead>
        <tr>
            <th scope="col">주문번호</th>
            <th scope="col">지불수단</th>
            <th scope="col">주문총액</th>
            <th scope="col">배송비</th>
            <th scope="col">포인트결제</th>
            <th scope="col">총결제액</th>
            <th scope="col">쿠폰</th>
            <th scope="col">주문취소</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo $od['od_id']; ?></td>
            <td class="td_paybybig"><?php echo $s_receipt_way; ?></td>
            <td class="td_numbig td_numsum"><?php echo display_price($amount['order']); ?></td>
            <td class="td_numbig"><?php echo display_price($od['od_send_cost'] + $od['od_send_cost2']); ?></td>
            <td class="td_numbig"><?php echo display_point($od['od_receipt_point']); ?></td>
            <td class="td_numbig td_numincome"><?php echo number_format($amount['receipt']); ?>원</td>
            <td class="td_numbig td_numcoupon"><?php echo display_price($amount['coupon']); ?></td>
            <td class="td_numbig td_numcancel">취소금액?</td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section class="">
    <h2 class="h2_frm">구독상세정보</h2>
    <?php echo $pg_anchor; ?>

    <form name="frmorderreceiptform" action="./orderformreceiptupdate.php" method="post" autocomplete="off">
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
                <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체') { ?>
                <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌') { ?>
                <tr>
                    <th scope="row">계좌번호</th>
                    <td><?php echo get_text($od['od_bank_account']); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th scope="row"><?php echo $od['od_settle_case']; ?> 입금액</th>
                    <td><?php echo display_price($od['od_receipt_price']); ?></td>
                </tr>
                <tr>
                    <th scope="row">입금자</th>
                    <td><?php echo get_text($print_od_deposit_name); ?></td>
                </tr>
                <tr>
                    <th scope="row">입금확인일시</th>
                    <td>
                        <?php if ($od['od_receipt_time'] == 0) { ?>입금 확인일시를 체크해 주세요.
                        <?php } else { ?><?php echo $od['od_receipt_time']; ?> (<?php echo get_yoil($od['od_receipt_time']); ?>)
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                <?php if ($od['od_settle_case'] == '휴대폰') { ?>
                <tr>
                    <th scope="row">휴대폰번호</th>
                    <td><?php echo get_text($od['od_bank_account']); ?></td>
                    </tr>
                <tr>
                    <th scope="row"><?php echo $od['od_settle_case']; ?> 결제액</th>
                    <td><?php echo display_price($od['od_receipt_price']); ?></td>
                </tr>
                <tr>
                    <th scope="row">결제 확인일시</th>
                    <td>
                        <?php if ($od['od_receipt_time'] == 0) { ?>결제 확인일시를 체크해 주세요.
                        <?php } else { ?><?php echo $od['od_receipt_time']; ?> (<?php echo get_yoil($od['od_receipt_time']); ?>)
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                <?php if ($od['od_settle_case'] == '신용카드') { ?>
                <tr>
                    <th scope="row" class="sodr_sppay">신용카드 결제금액</th>
                    <td>
                        <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                        <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="sodr_sppay">카드 승인일시</th>
                    <td>
                        <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>신용카드 결제 일시 정보가 없습니다.
                        <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                <?php if ($od['od_settle_case'] == 'KAKAOPAY') { ?>
                <tr>
                    <th scope="row" class="sodr_sppay">KAKOPAY 결제금액</th>
                    <td>
                        <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                        <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="sodr_sppay">KAKAOPAY 승인일시</th>
                    <td>
                        <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>신용카드 결제 일시 정보가 없습니다.
                        <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                <?php if ($od['od_settle_case'] == '간편결제' || ($od['od_pg'] == 'inicis' && is_inicis_order_pay($od['od_settle_case']) ) ) { ?>
                <tr>
                    <th scope="row" class="sodr_sppay"><?php echo $s_receipt_way; ?> 결제금액</th>
                    <td>
                        <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                        <?php } else { ?><?php echo display_price($od['od_receipt_price']); ?>
                        <?php } ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="sodr_sppay"><?php echo $s_receipt_way; ?> 승인일시</th>
                    <td>
                        <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") { echo $s_receipt_way; ?> 결제 일시 정보가 없습니다.
                        <?php } else { ?><?php echo substr($od['od_receipt_time'], 0, 20); ?>
                        <?php } ?>
                    </td>
                </tr>
                <?php } ?>

                <?php if ($od['od_settle_case'] != '무통장') { ?>
                <tr>
                    <th scope="row">결제대행사 링크</th>
                    <td>
                        <?php
                        if ($od['od_settle_case'] != '무통장') {
                            switch($od['od_pg']) {
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
                <?php } ?>

                <?php if($od['od_tax_flag']) { ?>
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
                <tr>
                    <th scope="row">주문금액할인</th>
                    <td><?php echo display_price($od['od_coupon']); ?></td>
                </tr>
                <tr>
                    <th scope="row">포인트</th>
                    <td><?php echo display_point($od['od_receipt_point']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_send_cost">배송비</label></th>
                    <td>
                        <input type="text" name="od_send_cost" value="<?php echo $od['od_send_cost']; ?>" id="od_send_cost" class="frm_input" size="10"> 원
                    </td>
                </tr>
                <?php if($od['od_send_coupon']) { ?>
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
                <tr>
                    <th scope="row"><label for="od_send_cost2">현재 회차</label></th>
                    <td>
                        <?php echo $od['od_pays_total']; ?> 회
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_send_cost2">카드정보</label></th>
                    <td>
                        <?php echo $od['od_card_name']; ?> <?php echo $od['card_mask_number']; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_send_cost2">다음 결제일</label></th>
                    <td>
                        <?php echo $od['next_billing_date']; ?> (<?php echo get_Ko_DayOfWeek($od['next_billing_date']); ?>)
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
                <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체') { ########## 시작?>
                <?php
                if ($od['od_settle_case'] == '무통장')
                {
                    // 은행계좌를 배열로 만든후
                    $str = explode("\n", $default['de_bank_account']);
                    $bank_account = '<select name="od_bank_account" id="od_bank_account">'.PHP_EOL;
                    $bank_account .= '<option value="">선택하십시오</option>'.PHP_EOL;
                    for ($i=0; $i<count($str); $i++) {
                        $str[$i] = str_replace("\r", "", $str[$i]);
                        $bank_account .= '<option value="'.$str[$i].'" '.get_selected($od['od_bank_account'], $str[$i]).'>'.$str[$i].'</option>'.PHP_EOL;
                    }
                    $bank_account .= '</select> ';
                }
                else if ($od['od_settle_case'] == '가상계좌')
                    $bank_account = $od['od_bank_account'].'<input type="hidden" name="od_bank_account" value="'.$od['od_bank_account'].'">';
                else if ($od['od_settle_case'] == '계좌이체')
                    $bank_account = $od['od_settle_case'];
                ?>

                <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌') { ?>
                <tr>
                    <th scope="row"><label for="od_bank_account">계좌번호</label></th>
                    <td><?php echo $bank_account; ?></td>
                </tr>
                <?php } ?>

                <tr>
                    <th scope="row"><label for="od_receipt_price"><?php echo $od['od_settle_case']; ?> 입금액</label></th>
                    <td>
                        <?php echo $html_receipt_chk; ?>
                        <input type="text" name="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" id="od_receipt_price" class="frm_input"> 원
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_deposit_name">입금자명</label></th>
                    <td>
                        <?php if ($config['cf_sms_use'] && $default['de_sms_use4']) { ?>
                        <input type="checkbox" name="od_sms_ipgum_check" id="od_sms_ipgum_check">
                        <label for="od_sms_ipgum_check">SMS 입금 문자전송</label>
                        <br>
                        <?php } ?>
                        <input type="text" name="od_deposit_name" value="<?php echo get_text($od['od_deposit_name']); ?>" id="od_deposit_name" class="frm_input">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_receipt_time">입금 확인일시</label></th>
                    <td>
                        <input type="checkbox" name="od_bank_chk" id="od_bank_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_receipt_time.value=this.form.od_bank_chk.value; else this.form.od_receipt_time.value = this.form.od_receipt_time.defaultValue;">
                        <label for="od_bank_chk">현재 시간으로 설정</label><br>
                        <input type="text" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" maxlength="19">
                    </td>
                </tr>
                <?php } ?>

                <?php if ($od['od_settle_case'] == '휴대폰') { ?>
                <tr>
                    <th scope="row">휴대폰번호</th>
                    <td><?php echo get_text($od['od_bank_account']); ?></td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_receipt_price"><?php echo $od['od_settle_case']; ?> 결제액</label></th>
                    <td>
                        <?php echo $html_receipt_chk; ?>
                        <input type="text" name="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" id="od_receipt_price" class="frm_input"> 원
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="op_receipt_time">휴대폰 결제일시</label></th>
                    <td>
                        <input type="checkbox" name="od_hp_chk" id="od_hp_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_receipt_time.value=this.form.od_hp_chk.value; else this.form.od_receipt_time.value = this.form.od_receipt_time.defaultValue;">
                        <label for="od_hp_chk">현재 시간으로 설정</label><br>
                        <input type="text" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="op_receipt_time" class="frm_input" size="19" maxlength="19">
                    </td>
                </tr>
                <?php } ?>
                
                <?php if ($od['od_settle_case'] == '신용카드') { ?>
                <tr>
                    <th scope="row" class="sodr_sppay"><label for="od_receipt_price">신용카드 결제금액</label></th>
                    <td>
                        <?php echo $html_receipt_chk; ?>
                        <input type="text" name="od_receipt_price" id="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" class="frm_input" size="10"> 원
                    </td>
                </tr>
                <?php } ?>

                <?php if ($od['od_settle_case'] == 'KAKAOPAY') { ?>
                <tr>
                    <th scope="row" class="sodr_sppay"><label for="od_receipt_price">KAKAOPAY 결제금액</label></th>
                    <td>
                        <?php echo $html_receipt_chk; ?>
                        <input type="text" name="od_receipt_price" id="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" class="frm_input" size="10"> 원
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="sodr_sppay"><label for="od_receipt_time">KAKAOPAY 승인일시</label></th>
                    <td>
                        <input type="checkbox" name="od_card_chk" id="od_card_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_receipt_time.value=this.form.od_card_chk.value; else this.form.od_receipt_time.value = this.form.od_receipt_time.defaultValue;">
                        <label for="od_card_chk">현재 시간으로 설정</label><br>
                        <input type="text" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" size="19" maxlength="19">
                    </td>
                </tr>
                <?php } ?>

                <?php if ($od['od_settle_case'] == '간편결제' || ($od['od_pg'] == 'inicis' && is_inicis_order_pay($od['od_settle_case']) )) { ?>
                <tr>
                    <th scope="row" class="sodr_sppay"><label for="od_receipt_price"><?php echo $s_receipt_way; ?> 결제금액</label></th>
                    <td>
                        <?php echo $html_receipt_chk; ?>
                        <input type="text" name="od_receipt_price" id="od_receipt_price" value="<?php echo $od['od_receipt_price']; ?>" class="frm_input" size="10"> 원
                    </td>
                </tr>
                <tr>
                    <th scope="row" class="sodr_sppay"><label for="od_receipt_time"><?php echo $s_receipt_way; ?> 승인일시</label></th>
                    <td>
                        <input type="checkbox" name="od_card_chk" id="od_card_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_receipt_time.value=this.form.od_card_chk.value; else this.form.od_receipt_time.value = this.form.od_receipt_time.defaultValue;">
                        <label for="od_card_chk">현재 시간으로 설정</label><br>
                        <input type="text" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" size="19" maxlength="19">
                    </td>
                </tr>
                <?php } ?>

                <tr>
                    <th scope="row"><label for="od_receipt_point">포인트 결제액</label></th>
                    <td><input type="text" name="od_receipt_point" value="<?php echo $od['od_receipt_point']; ?>" id="od_receipt_point" class="frm_input" size="10"> 점</td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_subscription_number">배송주기</label></th>
                    <td>
                        <?php echo $od['od_subscription_number']; ?> <?php echo $od['od_subscription_date_format']; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_firstshipment_date">첫 발송일</label></th>
                    <td>
                        <?php echo date('Y년 m월 d일', strtotime($od['od_firstshipment_date'])); ?> (<?php echo get_weekend_yoil($od['od_firstshipment_date']); ?>)
                    </td>
                </tr>

                <?php if ($config['cf_email_use']) { ?>
                <tr>
                    <th scope="row"><label for="od_send_mail">메일발송</label></th>
                    <td>
                        <?php echo help("주문자님께 입금, 배송내역을 메일로 발송합니다.\n메일발송시 상점메모에 기록됩니다."); ?>
                        <input type="checkbox" name="od_send_mail" value="1" id="od_send_mail"> 메일발송
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
        <a href="./orderlist.php?<?php echo $qstr; ?>" class="btn btn_02">목록</a>
    </div>
    </form>
</section>

<section id="" >
    <h3>정기결제내역</h3>
    
    <?php
    $pay_rows = sql_bind_select_array($g5['g5_subscription_pay_table'], '*', array('od_id'=>$od_id), array('orderBy'=>'pay_id', 'orderType'=>'DESC'));
    ?>
    <div class="tbl_frm01">
        <table>
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
                <td></td>
                <td><a href="<?php echo G5_SUBSCRIPTION_ADMIN_URL; ?>/payform.php?pay_id=<?php echo $v['pay_id']; ?>" target="_blank" class="mng_mod btn btn_02">상세보기</a></td>
            </tr>
            <?php } ?>
        </table>
    </div>
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
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
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
            해당 정기구독에 대한 history 내역입니다.
        </p>
    </div>
    
    <ul class="order-historys">
    <?php
    $hss = sql_bind_select_array($g5['g5_subscription_order_history_table'], '*', array('hs_type'=>'subscription_order', 'od_id'=>$od_id), array('orderby'=>'hs_id', 'ordertype'=>'desc'));

    if ($hss) {
        foreach($hss as $h) { ?>
        <li rel="<?php echo $h['hs_id']; ?>" class="history-item <?php echo $h['hs_category']; ?>">
            <div class="history-content">
                <?php echo conv_content($h['hs_content'], 0); ?>
            </div>
            <p class="history-btns">
                <span class="history-date"><?php echo $h['hs_time']; ?></span>
                <a href="#" class="delete-history" data-id="<?php echo $h['hs_id']; ?>" role="button">삭제하기</a>
            </p>
        </li>
        <?php }
    } ?>
    </ul>
    
    <form name="frmorder_historyform" action="./order_history_update.php" method="post">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
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

jQuery(function(e){
    $(document).on("click", ".delete-history", function(e) {
        e.preventDefault();
        
        if (!confirm("정말 삭제하시겠습니까?")) {
            return false;
        }
        
        var $this = $(this);
        var id = $this.data('id');
        var token = get_ajax_token();

        if(!token) {
            alert("토큰 정보가 올바르지 않습니다.");
            return false;
        }
        
        $.ajax({
            url: "./order_history_update.php",
            type: "POST",
            data: {
                mod_type : 'del',
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
                    
                    // 새 히스토리 항목 생성
                    var newHistory = `
                        <li rel="${response.hs_id}" class="history-item">
                            <div class="history-content">
                                ${escapeHtml(historyText)}
                            </div>
                            <p class="history-btns">
                                <span class="history-date">${response.hs_date}</span>
                                <a href="#" class="delete-history" data-id="${response.hs_id}" role="button">삭제하기</a>
                            </p>
                        </li>
                    `;

                    // 목록에 추가 (맨 위에 추가)
                    var $newItem = $(newHistory).hide();
                    $('.order-historys').prepend($newItem);
                    $newItem.fadeIn(400, function() { // 페이드인 후 스크롤
                        $('html, body').animate({
                            scrollTop: $newItem.offset().top - 150
                        }, 300);
                    });
                    
                    $('#od_subscription_history').val('');
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
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
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
                    <td><input type="text" name="od_name" value="<?php echo get_text($od['od_name']); ?>" id="od_name" required class="frm_input required"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_tel"><span class="sound_only">주문하신 분 </span>전화번호</label></th>
                    <td><input type="text" name="od_tel" value="<?php echo get_text($od['od_tel']); ?>" id="od_tel" required class="frm_input required"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_hp"><span class="sound_only">주문하신 분 </span>핸드폰</label></th>
                    <td><input type="text" name="od_hp" value="<?php echo get_text($od['od_hp']); ?>" id="od_hp" class="frm_input"></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">주문하시는 분 </span>주소</th>
                    <td>
                        <label for="od_zip" class="sound_only">우편번호</label>
                        <input type="text" name="od_zip" value="<?php echo get_text($od['od_zip1']).get_text($od['od_zip2']); ?>" id="od_zip" required class="frm_input required" size="5">
                        <button type="button" class="btn_frmline" onclick="win_zip('frmorderform3', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon');">주소 검색</button><br>
                        <span id="od_win_zip" style="display:block"></span>
                        <input type="text" name="od_addr1" value="<?php echo get_text($od['od_addr1']); ?>" id="od_addr1" required class="frm_input required" size="35">
                        <label for="od_addr1">기본주소</label><br>
                        <input type="text" name="od_addr2" value="<?php echo get_text($od['od_addr2']); ?>" id="od_addr2" class="frm_input" size="35">
                        <label for="od_addr2">상세주소</label>
                        <br>
                        <input type="text" name="od_addr3" value="<?php echo get_text($od['od_addr3']); ?>" id="od_addr3" class="frm_input" size="35">
                        <label for="od_addr3">참고항목</label>
                        <input type="hidden" name="od_addr_jibeon" value="<?php echo get_text($od['od_addr_jibeon']); ?>"><br>
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
                    <td><input type="text" name="od_b_name" value="<?php echo get_text($od['od_b_name']); ?>" id="od_b_name" required class="frm_input required"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_b_tel"><span class="sound_only">받으시는 분 </span>전화번호</label></th>
                    <td><input type="text" name="od_b_tel" value="<?php echo get_text($od['od_b_tel']); ?>" id="od_b_tel" required class="frm_input required"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_b_hp"><span class="sound_only">받으시는 분 </span>핸드폰</label></th>
                    <td><input type="text" name="od_b_hp" value="<?php echo get_text($od['od_b_hp']); ?>" id="od_b_hp" class="frm_input required"></td>
                </tr>
                <tr>
                    <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
                    <td>
                        <label for="od_b_zip" class="sound_only">우편번호</label>
                        <input type="text" name="od_b_zip" value="<?php echo get_text($od['od_b_zip1']).get_text($od['od_b_zip2']); ?>" id="od_b_zip" required class="frm_input required" size="5">
                        <button type="button" class="btn_frmline" onclick="win_zip('frmorderform3', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon');">주소 검색</button><br>
                        <input type="text" name="od_b_addr1" value="<?php echo get_text($od['od_b_addr1']); ?>" id="od_b_addr1" required class="frm_input required" size="35">
                        <label for="od_b_addr1">기본주소</label>
                        <input type="text" name="od_b_addr2" value="<?php echo get_text($od['od_b_addr2']); ?>" id="od_b_addr2" class="frm_input" size="35">
                        <label for="od_b_addr2">상세주소</label>
                        <input type="text" name="od_b_addr3" value="<?php echo get_text($od['od_b_addr3']); ?>" id="od_b_addr3" class="frm_input" size="35">
                        <label for="od_b_addr3">참고항목</label>
                        <input type="hidden" name="od_b_addr_jibeon" value="<?php echo get_text($od['od_b_addr_jibeon']); ?>"><br>
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
                    <td><?php if ($od['od_memo']) echo get_text($od['od_memo'], 1);else echo "없음";?></td>
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

<script>
$(function() {
    // 전체 옵션선택
    $("#sit_select_all").click(function() {
        if($(this).is(":checked")) {
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
        var $chk = $("input[name^=ct_chk]."+cls);
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
        if (document.getElementById('ct_chk_'+i).checked == true)
            check = true;
    }

    if (check == false) {
        alert("처리할 자료를 하나 이상 선택해 주십시오.");
        return false;
    }

    var msg = "";

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
    var chk = document.getElementById("od_delivery_chk");
    var company = document.getElementById("od_delivery_company");
    company.value = chk.checked ? chk.value : company.defaultValue;
}

// 현재 시간으로 배송일시 설정
function chk_invoice_time()
{
    var chk = document.getElementById("od_invoice_chk");
    var time = document.getElementById("od_invoice_time");
    time.value = chk.checked ? chk.value : time.defaultValue;
}

// 결제금액 수동 설정
function chk_receipt_price()
{
    var chk = document.getElementById("od_receipt_chk");
    var price = document.getElementById("od_receipt_price");
    price.value = chk.checked ? (parseInt(chk.value) + parseInt(price.defaultValue)) : price.defaultValue;
}
</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
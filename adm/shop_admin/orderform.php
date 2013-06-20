<?php
$sub_menu = '400400';
include_once('./_common.php');

// 메세지
$html_title = '주문 내역 수정';
$alt_msg1 = '주문번호 오류입니다.';
$mb_guest = '비회원';

$cart_title1 = '쇼핑';
$cart_title2 = '완료';
$cart_title3 = '주문번호';
$cart_title4 = '배송완료';

auth_check($auth[$sub_menu], "w");

$g4['title'] = $html_title;
include_once(G4_ADMIN_PATH.'/admin.head.php');

//------------------------------------------------------------------------------
// 설정 시간이 지난 주문서 없는 장바구니 자료 삭제
//------------------------------------------------------------------------------
$keep_term = $default['de_cart_keep_term'];
if (!$keep_term) $keep_term = 1; // 기본값 1일
$beforetime = date('Y-m-d H:i:s', ( G4_SERVER_TIME - (86400 * $keep_term) ) );
$sql = " delete from {$g4['shop_cart_table']} where ct_status = '$cart_title1' and ct_time <= '$beforetime' ";
sql_query($sql);
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// 주문완료 포인트
//      설정일이 지난 포인트 부여되지 않은 배송완료된 장바구니 자료에 포인트 부여
//      설정일이 0 이면 주문서 완료 설정 시점에서 포인트를 바로 부여합니다.
//------------------------------------------------------------------------------
if (!isset($order_not_point)) {
    $beforedays = date("Y-m-d H:i:s", ( time() - (60 * 60 * 24 * (int)$default['de_point_days']) ) );
    $sql = " select * from {$g4['shop_cart_table']}
               where ct_status = '$cart_title2'
                 and ct_point_use = '0'
                 and ct_time <= '$beforedays' ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 회원 ID 를 얻는다.
        $tmp_row = sql_fetch("select od_id, mb_id from {$g4['shop_order_table']} where uq_id = '{$row['uq_id']}' ");

        // 회원이면서 포인트가 0보다 크다면
        if ($tmp_row['mb_id'] && $row['ct_point'] > 0)
        {
            if(!$default['de_mileage_use']) {
                $po_point = $row['ct_point'] * $row['ct_qty'];
                $po_content = "$cart_title3 {$tmp_row['od_id']} ({$row['ct_id']}) $cart_title4";
                insert_point($tmp_row['mb_id'], $po_point, $po_content, "@delivery", $tmp_row['mb_id'], "{$tmp_row['od_id']},{$row['uq_id']},{$row['ct_id']}");
            }

            // 주문완료 마일리지 적립
            $ml_point = $row['ct_point'] * $row['ct_qty'];
            $ml_content = "$cart_title3 {$tmp_row['od_id']} ({$row['ct_id']}) $cart_title4";
            insert_mileage($tmp_row['mb_id'], $ml_point, $ml_content, $tmp_row['od_id'], $row['ct_id']);
        }

        sql_query("update {$g4['shop_cart_table']} set ct_point_use = '1' where ct_id = '{$row['ct_id']}' ");
    }
}
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from {$g4['shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert($alt_msg1);
}

if ($od['mb_id'] == "") {
    $od['mb_id'] = $mb_guest;
}
//------------------------------------------------------------------------------


$qstr = "sort1=$sort1&amp;sort2=$sort2&amp;sel_field=$sel_field&amp;search=$search&amp;page=$page";

// PG사를 KCP 사용하면서 테스트 상점아이디라면
if ($default['de_card_test']) {
    // 로그인 아이디 / 비번
    // 일반 : test1234 / test12345
    // 에스크로 : escrow / escrow913
    $g4['shop_cardpg']['kcp'] = "http://testadmin8.kcp.co.kr";
}

// 주문총액
$sql = " select SUM(IF(io_type = 1, io_price * ct_qty, (ct_price + io_price) * ct_qty)) as sum_order
            from {$g4['shop_cart_table']}
            where uq_id = '{$od['uq_id']}' ";
$row = sql_fetch($sql);
$total_order = $row['sum_order'];

// 상품목록
$sql = " select it_id,
                it_name,
                cp_amount
           from {$g4['shop_cart_table']}
          where uq_id = '{$od['uq_id']}'
            and ct_num = '0'
          order by ct_id ";
$result = sql_query($sql);

$pg_anchor = '<ul class="anchor">
<li><a href="#anc_sodr_list">주문상품 목록</a></li>
<li><a href="#anc_sodr_pay">주문결제 내역</a></li>
<li><a href="#anc_sodr_chk">결제상세정보 확인</a></li>
<li><a href="#anc_sodr_paymo">결제상세정보 수정</a></li>
<li><a href="#anc_sodr_memo">상점메모</a></li>
<li><a href="#anc_sodr_payer">주문하신 분</a></li>
<li><a href="#anc_sodr_addressee">받으시는 분</a></li>
</ul>';
?>

<section id="anc_sodr_list" class="cbox">
    <h2>주문상품 목록</h2>
    <?php echo $pg_anchor; ?>
    <p>주문일시 <?php echo substr($od['od_time'],0,16); ?> (<?php echo get_yoil($od['od_time']); ?>) / 주문총액 <strong><?php echo number_format($total_order); ?></strong>원</p>
    <?php if ($default['de_hope_date_use']) { ?><p>희망배송일은 <?php echo $od['od_hope_date']; ?> (<?php echo get_yoil($od['od_hope_date']); ?>) 입니다.</p><?php } ?>
    <?php if($od['od_mobile']) { ?>
    <p>모바일 쇼핑몰의 주문입니다.</p>
    <?php } ?>

    <form name="frmorderform" method="post" action="./ordercartupdate.php" onsubmit="return form_submit(this);">
    <input type="hidden" name="uq_id" value="<?php echo $od['uq_id']; ?>">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="mb_id" value="<?php echo $od['mb_id']; ?>">
    <input type="hidden" name="od_email" value="<?php echo $od['od_email']; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page;?>">

    <label for="sit_select_all" class="sound_only">현재 상품 목록 전체선택</label>
    <input type="checkbox" id="sit_select_all">

    <ul id="sodr_ul">
        <?php
        for($i=0; $row=sql_fetch_array($result); $i++) {
            // 상품이미지
            $image = get_it_image($row['it_id'], 50, 50);
        ?>
        <li>
            <p>
                <a href="./itemform.php?w=u&amp;it_id=<?php echo $row['it_id']; ?>"><?php echo $image; ?> <?php echo stripslashes($row['it_name']); ?></a>
            </p>

            <table>
            <thead>
            <tr>
                <th scope="col">
                    <label for="sit_sel_<?php echo $i; ?>" class="sound_only"><?php echo $row['it_name']; ?> 옵션 전체선택</label>
                    <input type="checkbox" id="sit_sel_<?php echo $i; ?>" name="it_sel[]">
                </th>
                <th scope="col">옵션항목</th>
                <th scope="col">상태</th>
                <th scope="col">수량</th>
                <th scope="col">판매가</th>
                <th scope="col">소계</th>
                <th scope="col">포인트</th>
                <th scope="col">배송비</th>
                <th scope="col">포인트반영</th>
                <th scope="col">재고반영</th>
            </tr>
            </thead>
            <tbody>
            <?php
            // 상품의 옵션정보
            $sql = " select ct_id, it_id, ct_price, ct_qty, ct_option, ct_status, ct_stock_use, ct_point_use, ct_send_cost, io_type, io_price
                        from {$g4['shop_cart_table']}
                        where uq_id = '{$od['uq_id']}'
                          and it_id = '{$row['it_id']}'
                        order by ct_num ";
            $res = sql_query($sql);

            for($k=0; $opt=sql_fetch_array($res); $k++) {
                if($opt['io_type'])
                    $opt_price = $opt['io_price'];
                else
                    $opt_price = $opt['ct_price'] + $opt['io_price'];

                $ct_amount['소계'] = $opt_price * $opt['ct_qty'];
                $ct_point['소계'] = $opt['ct_point'] * $opt['ct_qty'];
                if ($opt['ct_status']=='주문' || $opt['ct_status']=='준비' || $opt['ct_status']=='배송' || $opt['ct_status']=='완료')
                    $t_ct_amount['정상'] += $ct_amount['소계'];
                else if ($opt['ct_status']=='취소' || $opt['ct_status']=='반품' || $opt['ct_status']=='품절')
                    $t_ct_amount['취소'] += $ct_amount['소계'];
            ?>
            <tr>
                <td class="td_chk">
                    <label for="ct_opt_chk_<?php echo $i.$k; ?>" class="sound_only"><?php echo $opt['ct_option']; ?></label>
                    <input type="checkbox" name="ct_chk[]" id="ct_opt_chk_<?php echo $i.$k; ?>" value="<?php echo $opt['ct_id']; ?>">
                </td>
                <td><?php echo $opt['ct_option']; ?></td>
                <td class="td_smallmng"><?php echo $opt['ct_status']; ?></td>
                <td class="td_num"><?php echo $opt['ct_qty']; ?></td>
                <td class="td_bignum"><?php echo number_format($opt_price); ?></td>
                <td class="td_num"><?php echo number_format($ct_amount['소계']); ?></td>
                <td class="td_bignum"><?php echo number_format($ct_point['소계']); ?></td>
                <td class="td_sendcost_by"><?php echo $opt['ct_send_cost'] ? '착불' : '선불'; ?></td>
                <td class="td_smallmng"><?php echo get_yn($opt['ct_point_use']); ?></td>
                <td class="td_smallmng"><?php echo get_yn($opt['ct_stock_use']); ?></td>
            </tr>
            <?php
            }
            ?>
            </tbody>
            </table>

        </li>
        <?php
            $t_cp_amount += $row['cp_amount']; // 쿠폰사용금액
            $t_ct_amount['합계'] += $ct_amount['소계'];
            $t_ct_point['합계'] += $ct_point['소계'];
        }
        ?>
    </ul>

    <div class="btn_list">
        <input type="submit" name="act_button" value="주문" onclick="document.pressed=this.value">
        <input type="submit" name="act_button" value="상품준비중" onclick="document.pressed=this.value">
        <input type="submit" name="act_button" value="배송중" onclick="document.pressed=this.value">
        <input type="submit" name="act_button" value="완료" onclick="document.pressed=this.value">
        <input type="submit" name="act_button" value="취소" onclick="document.pressed=this.value">
        <input type="submit" name="act_button" value="반품" onclick="document.pressed=this.value">
        <input type="submit" name="act_button" value="품절" onclick="document.pressed=this.value">
    </div>

    </form>

</section>

<section id="anc_sodr_pay" class="cbox">
    <h2>주문결제 내역</h2>
    <?php echo $pg_anchor; ?>

    <?php
    // 주문금액 = 상품구입금액 + 배송비 + 추가배송비
    $amount['정상'] = $t_ct_amount['정상'] + $od['od_send_cost'] + $od['od_send_cost2'];

    // 입금액 = 결제금액 + 포인트
    $amount['입금'] = $od['od_receipt_amount'] + $od['od_receipt_point'];

    // 쿠폰금액
    $amount['쿠폰'] = $t_cp_amount + $od['od_coupon'];

    // 미수금 = (주문금액 - DC + 환불액) - (입금액 - 신용카드승인취소) - 쿠폰금액
    $amount['미수'] = ($amount['정상'] - $od['od_dc_amount'] + $od['od_refund_amount']) - ($amount['입금'] - $od['od_cancel_card']) - $amount['쿠폰'];

    // 결제방법
    $s_receipt_way = $od['od_settle_case'];

    if ($od['od_receipt_point'] > 0)
        $s_receipt_way .= "+포인트";
    ?>

    <strong class="sodr_nonpay">미수금 <?php echo display_price($amount['미수']); ?></strong>

    <table>
    <thead>
    <tr>
        <th scope="col">주문번호</th>
        <th scope="col">결제방법</th>
        <th scope="col">주문총액</th>
        <th scope="col">포인트결제</th>
        <th scope="col">총결제액</th>
        <th scope="col">쿠폰</th>
        <th scope="col">DC</th>
        <th scope="col">환불액</th>
        <th scope="col">주문취소</th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td class="td_odrnum2"><?php echo $od['od_id']; ?><!-- uq_id : <?php echo $od['uq_id']; ?> --></td>
        <td class="td_payby"><?php echo $s_receipt_way; ?></td>
        <td class="td_bignum"><?php echo display_price($amount['정상']); ?></td>
        <td class="td_bignum"><?php echo display_point($od['od_receipt_point']); ?></td>
        <td class="td_bignum"><?php echo number_format($amount['입금']); ?>원</td>
        <td class="td_bignum"><?php echo display_price($amount['쿠폰']); ?></td>
        <td class="td_bignum"><?php echo display_price($od['od_dc_amount']); ?></td>
        <td class="td_bignum"><?php echo display_price($od['od_refund_amount']); ?></td>
        <td class="td_bignum"><?php echo number_format($t_ct_amount['취소']); ?>원</td>
    </tr>
    </tbody>
    </table>
</section>


<section class="cbox compare_wrap">
    <h2>결제상세정보</h2>
    <?php echo $pg_anchor; ?>

    <form name="frmorderreceiptform" action="./orderreceiptupdate.php" method="post" autocomplete="off">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="od_name" value="<?php echo $od['od_name']; ?>">
    <input type="hidden" name="od_hp" value="<?php echo $od['od_hp']; ?>">
    <input type="hidden" name="od_tno" value="<?php echo $od['od_tno']; ?>">
    <input type="hidden" name="od_escrow" value="<?php echo $od['od_escrow']; ?>">

    <section id="anc_sodr_chk" class="compare_left">
        <h3>결제상세정보 확인</h3>

        <table class="frm_tbl">
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체') { ?>
        <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌') { ?>
        <tr>
            <th scope="row">계좌번호</th>
            <td><?php echo $od['od_bank_account']; ?></td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row"><?php echo $od['od_settle_case']; ?> 입금액</th>
            <td><?php echo display_price($od['od_receipt_amount']); ?></td>
        </tr>
        <tr>
            <th scope="row">입금자</th>
            <td><?php echo $od['od_deposit_name']; ?></td>
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
            <td><?php echo $od['od_bank_account']; ?></td>
            </tr>
        <tr>
            <th scope="row"><?php echo $od['od_settle_case']; ?> 결제액</th>
            <td><?php echo display_price($od['od_receipt_amount']); ?></td>
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
            <th scope="row" class="sodr_sppay">신용카드 입금액</th>
            <td>
                <?php if ($od['od_receipt_time'] == "0000-00-00 00:00:00") {?>0원
                <?php } else { ?><?php echo display_price($od['od_receipt_amount']); ?>
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
        <tr>
            <th scope="row" class="sodr_sppay">카드 승인취소</th>
            <td><?php echo display_price($od['od_cancel_card']); ?></td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row">포인트</th>
            <td><?php echo display_point($od['od_receipt_point']); ?></td>
        </tr>
        <tr>
            <th scope="row">DC</th>
            <td><?php echo display_price($od['od_dc_amount']); ?></td>
        </tr>
        <tr>
            <th scope="row">환불액</th>
            <td><?php echo display_price($od['od_refund_amount']); ?></td>
        </tr>
        <?php
        $sql = " select dl_company, dl_url, dl_tel from {$g4['shop_delivery_table']} where dl_id = '{$od['dl_id']}' ";
        $dl = sql_fetch($sql);
        ?>
        <tr>
            <th scope="row">배송회사</th>
            <td>
            <?php
            if ($od['dl_id'] > 0) {
                 // get 으로 날리는 경우 운송장번호를 넘김
                if (strpos($dl['dl_url'], "=")) $invoice = $od['od_invoice'];
            ?>
            <a href="<?php echo $dl['dl_url']; ?><?php echo $invoice; ?>" target="_blank"><?php echo $dl['dl_company']; ?></a> (고객센터 <?php echo $dl['dl_tel']; ?>)
            <?php } else { ?>배송회사를 선택해 주세요.
            <?php } ?>
            </td>
        </tr>
        <?php if ($od['od_invoice']) { ?>
        <tr>
            <th scope="row">운송장번호</th>
            <td><?php echo $od['od_invoice']; ?></td>
        </tr>
        <?php } ?>
        <tr>
            <th scope="row">배송일시</th>
            <td><?php echo $od['od_invoice_time']; ?></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_send_cost">배송비</label></th>
            <td>
                <?php echo help("주문취소시 배송비는 취소되지 않으므로 이 배송비를 0으로 설정하여 미수금을 맞추십시오."); ?>
                <input type="text" name="od_send_cost" value="<?php echo $od['od_send_cost']; ?>" id="od_send_cost" class="frm_input" size="10"> 원
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="od_send_cost2">추가배송비</label></th>
            <td>
                <input type="text" name="od_send_cost2" value="<?php echo $od['od_send_cost2']; ?>" id="od_send_cost2" class="frm_input" size="10"> 원
            </td>
        </tr>
        <?php
        if ($amount['미수'] == 0) {
            if ($od['od_receipt_amount'] && ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체')) {
        ?>
        <tr>
            <th scope="row">현금영수증</th>
            <td>
            <?php if ($od['od_cash']) { ?>
                <a href="javascript:;" onclick="window.open('https://admin.kcp.co.kr/Modules/Service/Cash/Cash_Bill_Common_View.jsp?cash_no=<?php echo $od['od_cash_no']; ?>', 'taxsave_receipt', 'width=360,height=647,scrollbars=0,menus=0');">현금영수증 확인</a>
            <?php } else { ?>
                <a href="javascript:;" onclick="window.open('<?php echo G4_SHOP_URL; ?>/taxsave_kcp.php?od_id=<?php echo $od_id; ?>&amp;uq_id=<?php echo $od['uq_id']; ?>', 'taxsave', 'width=550,height=400,scrollbars=1,menus=0');">현금영수증 발급</a>
            <?php } ?>
            </td>
        </tr>
        <?php
            }
        }
        ?>
        </tbody>
        </table>
    </section>

    <section id="anc_sodr_paymo" class="compare_right">
        <h3>결제상세정보 수정</h3>

        <table class="frm_tbl">
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체') { ########## 시작?>
        <?php
        // 주문서
        $sql = " select * from {$g4['shop_order_table']} where od_id = '$od_id' ";
        $result = sql_query($sql);
        $od = sql_fetch_array($result);

        if ($od['od_settle_case'] == '무통장')
        {
            // 은행계좌를 배열로 만든후
            $str = explode("\n", $default['de_bank_account']);
            $bank_account .= '<select name="od_bank_account" id="od_bank_account">'.PHP_EOL;
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
            <th scope="row"><label for="od_receipt_amount"><?php echo $od['od_settle_case']; ?> 입금액</label></th>
            <td>
                <input type="text" name="od_receipt_amount" value="<?php echo $od['od_receipt_amount']; ?>" id="od_receipt_amount" class="frm_input" size="10"> 원
                <?php
                if ($od['od_settle_case'] == '계좌이체' || $od['od_settle_case'] == '가상계좌') {
                    $pg_url = $g4['shop_cardpg'][$default['de_card_pg']];
                ?>
                <a href="<?php echo $pg_url; ?>" target="_blank">결제대행사</a>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="od_deposit_name">입금자명</label></th>
            <td>
                <?php if ($default['de_sms_use3']) { ?>
                <label for="od_sms_ipgum_check">SMS 문자전송</label>
                <input type="checkbox" name="od_sms_ipgum_check" id="od_sms_ipgum_check">
                <br>
                <?php } ?>
                <input type="text" name="od_deposit_name" value="<?php echo $od['od_deposit_name']; ?>" id="od_deposit_name" class="frm_input">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="od_receipt_time">입금 확인일시</label></th>
            <td>
                <label for="od_bank_chk">현재 시간으로 설정</label>
                <input type="checkbox" name="od_bank_chk" id="od_bank_chk" value="<?php echo date("Y-m-d H:i:s", G4_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_receipt_time.value=this.form.od_bank_chk.value; else this.form.od_receipt_time.value = this.form.od_receipt_time.defaultValue;"><br>
                <input type="text" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" maxlength="19">
            </td>
        </tr>
        <?php } ########## 끝 ?>

        <?php if ($od['od_settle_case'] == '휴대폰') { ?>
        <tr>
            <th scope="row">휴대폰번호</th>
            <td><?php echo $od['od_bank_account']; ?></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_receipt_amount"><?php echo $od['od_settle_case']; ?> 결제액</label></th>
            <td>
                <input type="text" name="od_receipt_amount" value="<?php echo $od['od_receipt_amount']; ?>" id="od_receipt_amount" class="frm_input"> 원
                <?php $pg_url = $g4['shop_cardpg'][$default['de_card_pg']];?>
                <a href="<?php echo $pg_url; ?>" target="_blank">결제대행사</a>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="op_receipt_time">휴대폰 결제일시</label></th>
            <td>
                <label for="od_card_chk">현재 시간으로 설정</label>
                <!-- name od_card_chk 를 od_hp_chk 로 수정 - 지운아빠 2013-04-16 -->
                <input type="checkbox" name="od_hp_chk" id="od_hp_chk" value="<?php echo date("Y-m-d H:i:s", G4_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_receipt_time.value=this.form.od_hp_chk.value; else this.form.od_receipt_time.value = this.form.od_receipt_time.defaultValue;"><br>
                <input type="text" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="op_receipt_time" class="frm_input" size="19" maxlength="19">
            </td>
        </tr>
        <?php } ?>

        <?php if ($od['od_settle_case'] == '신용카드') { ?>
        <tr>
            <th scope="row" class="sodr_sppay"><label for="od_receipt_amount">신용카드 결제액</label></th>
            <td>
                <input type="text" name="od_receipt_amount" value="<?php echo $od['od_receipt_amount']; ?>" id="od_receipt_amount" class="frm_input" size="10"> 원
                <?php $card_url = $g4['shop_cardpg'][$default['de_card_pg']]; ?>
                <a href="<?php echo $card_url; ?>" target="_blank">결제대행사</a>
            </td>
        </tr>
        <tr>
            <th scope="row" class="sodr_sppay"><label for="od_receipt_time">카드 승인일시</label></th>
            <td>
                <label for="od_card_chk">현재 시간으로 설정</label>
                <input type="checkbox" name="od_card_chk" id="od_card_chk" value="<?php echo date("Y-m-d H:i:s", G4_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_receipt_time.value=this.form.od_card_chk.value; else this.form.od_receipt_time.value = this.form.od_receipt_time.defaultValue;"><br>
                <input type="text" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" size="19" maxlength="19">
            </td>
        </tr>
        <tr>
            <th scope="row" class="sodr_sppay"><label for="od_cancel_card">카드 승인취소</label></th>
            <td><input type="text" name="od_cancel_card" value="<?php echo $od['od_cancel_card']; ?>" class="frm_input" size="10"> 원</td>
        </tr>
        <?php } ?>

        <tr>
            <th scope="row"><label for="od_receipt_point">포인트 결제액</label></th>
            <td><input type="text" name="od_receipt_point" value="<?php echo $od['od_receipt_point']; ?>" id="od_receipt_point" class="frm_input" size="10"> 점</td>
        </tr>
        <tr>
            <th scope="row"><label for="od_dc_amount">DC</label></th>
            <td><input type="text" name="od_dc_amount" value="<?php echo $od['od_dc_amount']; ?>" id="od_dc_amount" class="frm_input" size="10"> 원</td>
        </tr>
        <tr>
            <th scope="row"><label for="od_refund_amount">환불액</label></th>
            <td>
                <?php echo help("카드승인취소를 입력한 경우에는 중복하여 입력하면 미수금이 틀려집니다."); ?>
                <input type="text" name="od_refund_amount" value="<?php echo $od['od_refund_amount']; ?>" id="od_refund_amount" class="frm_input" size="10"> 원
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="dl_id">배송회사</label></th>
            <td>
                <select name="dl_id" id="dl_id">
                    <option value="">배송시 선택하세요.</option>
                    <?php
                    $sql = "select * from {$g4['shop_delivery_table']} order by dl_order desc, dl_id desc ";
                    $result = sql_query($sql);
                    for ($i=0; $row=sql_fetch_array($result); $i++) {
                    ?>
                    <option value="<?php echo $row['dl_id']; ?>" <?php echo get_selected($od['dl_id'], $row['dl_id']); ?>><?php echo $row['dl_company']; ?></option>
                    <?php
                    }
                    mysql_free_result($result);
                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="od_invoice">운송장번호</label></th>
            <td>
                <?php if ($default['de_sms_use4']) { ?>
                <label for="od_sms_baesong_check">SMS 문자전송</label>
                <input type="checkbox" name="od_sms_baesong_check" id="od_sms_baesong_check">
                <br>
                <?php } ?>
                <input type="text" name="od_invoice" value="<?php echo $od['od_invoice']; ?>" id="od_invoice" class="frm_input">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="od_invoice_time">배송일시</label></th>
            <td>
                <label for="od_invoice_chk">현재 시간으로 설정</label>
                <input type="checkbox" name="od_invoice_chk" id="od_invoice_chk" value="<?php echo date("Y-m-d H:i:s", G4_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_invoice_time.value=this.form.od_invoice_chk.value; else this.form.od_invoice_time.value = this.form.od_invoice_time.defaultValue;"><br>
                <input type="text" name="od_invoice_time" value="<?php echo is_null_time($od['od_invoice_time']) ? "" : $od['od_invoice_time']; ?>" id="od_invoice_time" class="frm_input" maxlength="19">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="od_send_mail">메일발송</label></th>
            <td>
                <?php echo help("주문자님께 입금, 배송내역을 메일로 발송합니다.\n메일발송시 상점메모에 기록됩니다."); ?>
                <input type="checkbox" name="od_send_mail" value="1" id="od_send_mail"> 메일발송
            </td>
        </tr>
        </tbody>
        </table>
    </section>

    <div class="btn_confirm">
        <input type="submit" value="결제/배송내역 수정" class="btn_submit">
    </div>
    </form>
</section>

<section id="anc_sodr_memo" class="cbox">
    <h2>상점메모</h2>
    <?php echo $pg_anchor; ?>
    <p>
        현재 열람 중인 주문에 대한 내용을 메모하는곳입니다.<br>
        입금, 배송 내역을 메일로 발송할 경우 함께 기록됩니다.
    </p>

    <form name="frmorderform2" action="./orderformupdate.php" method="post">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="mod_type" value="memo">

    <div>
        <label for="od_shop_memo" class="sound_only">상점메모</label>
        <textarea name="od_shop_memo" id="od_shop_memo" rows="8"><?php echo stripslashes($od['od_shop_memo']); ?></textarea>
    </div>

    <div class="btn_confirm">
        <input type="submit" value="메모 수정" class="btn_submit">
    </div>

    </form>
</section>

<div class="cbox compare_wrap">
    <h2>주문자/배송지 정보</h2>
    <?php echo $pg_anchor; ?>

    <form name="frmorderform3" action="./orderformupdate.php" method="post">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="mod_type" value="info">

    <section id="anc_sodr_payer" class="compare_left">
        <h3>주문하신 분</h3>

        <table class="frm_tbl">
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="od_name"><span class="sound_only">주문하신 분 </span>이름</label></th>
            <td><input type="text" name="od_name" value="<?php echo $od['od_name']; ?>" id="od_name" required class="frm_input required"></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_tel"><span class="sound_only">주문하신 분 </span>전화번호</label></th>
            <td><input type="text" name="od_tel" value="<?php echo $od['od_tel']; ?>" id="od_tel" required class="frm_input required"></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_hp"><span class="sound_only">주문하신 분 </span>핸드폰</label></th>
            <td><input type="text" name="od_hp" value="<?php echo $od['od_hp']; ?>" id="od_hp" class="frm_input"></td>
        </tr>
        <tr>
            <th scope="row"><span class="sound_only">주문하시는 분 </span>주소</th>
            <td>
                <label for="od_zip1" class="sound_only">우편번호 앞자리</label>
                <input type="text" name="od_zip1" value="<?php echo $od['od_zip1']; ?>" id="od_zip1" required class="frm_input required" size="4">
                -
                <label for="od_zip2" class="sound_only">우편번호 뒷자리</label>
                <input type="text" name="od_zip2" value="<?php echo $od['od_zip2']; ?>" id="od_zip2" required class="frm_input required" size="4">
                <span id="od_win_zip" style="display:block"></span>
                <label for="od_addr1" class="sound_only">주소</label>
                <input type="text" name="od_addr1" value="<?php echo $od['od_addr1']; ?>" id="od_addr1" required class="frm_input required" size="30"><br>
                <label for="od_addr2" class="sound_only">상세주소</label>
                <input type="text" name="od_addr2" value="<?php echo $od['od_addr2']; ?>" id="od_addr2" required class="frm_input required" size="30">

                <script>
                // 우편번호 자바스크립트 비활성화 대응을 위한 코드
                $('<a href="<?php echo G4_BBS_URL; ?>/zip.php?frm_name=frmorderform2&amp;frm_zip1=od_zip1&amp;frm_zip2=od_zip2&amp;frm_addr1=od_addr1&amp;frm_addr2=od_addr2" id="od_zip_find" class="btn_frmline win_zip_find" target="_blank">우편번호 검색</a><br>').appendTo('#od_win_zip');
                $("#od_win_zip").css("display", "inline");
                $("#od_zip1, #od_zip2, #od_addr1").attr('readonly', 'readonly');
                $("#od_zip1, #od_zip2, #od_addr1").addClass('readonly');
                </script>
        </tr>
        <tr>
            <th scope="row"><label for="od_email"><span class="sound_only">주문하신 분 </span>E-mail</label></th>
            <td><input type="text" name="od_email" value="<?php echo $od['od_email']; ?>" id="od_email" required class="frm_input email required" size="30"></td>
        </tr>
        <tr>
            <th scope="row"><span class="sound_only">주문하신 분 </span>IP Address</th>
            <td><?php echo $od['od_ip']; ?></td>
        </tr>
        </tbody>
        </table>

    </section>

    <section id="anc_sodr_addressee" class="compare_right">
        <h3>받으시는 분</h3>

        <table class="frm_tbl">
        <colgroup>
            <col class="grid_3">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="od_b_name"><span class="sound_only">받으시는 분 </span>이름</label></th>
            <td><input type="text" name="od_b_name" value="<?php echo $od['od_b_name']; ?>" id="od_b_name" required class="frm_input required"></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_b_tel"><span class="sound_only">받으시는 분 </span>전화번호</label></th>
            <td><input type="text" name="od_b_tel" value="<?php echo $od['od_b_tel']; ?>" id="od_b_tel" required class="frm_input required"></td>
        </tr>
        <tr>
            <th scope="row"><label for="od_b_hp"><span class="sound_only">받으시는 분 </span>핸드폰</label></th>
            <td><input type="text" name="od_b_hp" value="<?php echo $od['od_b_hp']; ?>" id=-"od_b_hp" class="frm_input required"></td>
        </tr>
        <tr>
            <th scope="row"><span class="sound_only">받으시는 분 </span>주소</th>
            <td>
                <label for="od_b_zip1" class="sound_only">우편번호 앞자리</label>
                <input type="text" name="od_b_zip1" value="<?php echo $od['od_b_zip1']; ?>" id="od_b_zip1" required class="frm_input required" size="4">
                -
                <label for="od_b_zip2" class="sound_only">우편번호 뒷자리</label>
                <input type="text" name="od_b_zip2" value="<?php echo $od['od_b_zip2']; ?>" id="od_b_zip2" required class="frm_input required" size="4">
                <span id="od_win_zipb" style="display:block"></span>
                <label for="od_b_addr1" class="sound_only">주소</label>
                <input type="text" name="od_b_addr1" value="<?php echo $od['od_b_addr1']; ?>" id="od_b_addr1" required class="frm_input required" size="30"><br>
                <label for="od_b_addr2" class="sound_only">상세주소</label>
                <input type="text" name="od_b_addr2" value="<?php echo $od['od_b_addr2']; ?>" id="od_b_addr2" required class="frm_input required" size="30">

                <script>
                // 우편번호 자바스크립트 비활성화 대응을 위한 코드
                $('<a href="<?php echo G4_BBS_URL; ?>/zip.php?frm_name=frmorderform2&amp;frm_zip1=od_b_zip1&amp;frm_zip2=od_b_zip2&amp;frm_addr1=od_b_addr1&amp;frm_addr2=od_b_addr2" id="od_zip_findb" class="btn_frmline win_zip_find" target="_blank">우편번호 검색</a><br>').appendTo('#od_win_zipb');
                $("#od_win_zipb").css("display", "inline");
                $("#od_b_zip1, #od_b_zip2, #od_b_addr1").attr('readonly', 'readonly');
                $("#od_b_zip1, #od_b_zip2, #od_b_addr1").addClass('readonly');
                </script>
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
            <td><?php if ($od['od_memo']) echo nl2br($od['od_memo']);else echo "없음";?></td>
        </tr>
        </tbody>
        </table>

    </section>

    <div class="btn_confirm">
        <input type="submit" value="주문자/배송지 정보 수정" class="btn_submit">
    </div>

    </form>
</div>

<div class="btn_confirm">
    <a href="./orderdelete.php?od_id=<?php echo $od['od_id']; ?>&amp;uq_id=<?php echo $od['uq_id']; ?>&amo;mb_id=<?php echo $od['mb_id']; ?>&amp;<?php echo $qstr; ?>" onclick="return del_confirm();">주문서 삭제</a>
    <a href="./orderlist.php?<?php echo $qstr; ?>">목록</a>
</div>

<script>
$(function() {
    // 전체 옵션선택
    $("#sit_select_all").click(function() {
        if($(this).is(":checked")) {
            $("input[name='it_sel[]']").attr("checked", true);
            $("input[name='ct_chk[]']").attr("checked", true);
        } else {
            $("input[name='it_sel[]']").attr("checked", false);
            $("input[name='ct_chk[]']").attr("checked", false);
        }
    });

    // 상품의 옵션선택
    $("input[name='it_sel[]']").click(function() {
        var $chk = $(this).closest("li").find("input[name='ct_chk[]']");
        if($(this).is(":checked"))
            $chk.attr("checked", true);
        else
            $chk.attr("checked", false);
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

    if (confirm("\'" + status + "\'을(를) 선택하셨습니다.\n\n이대로 처리 하시겠습니까?")) {
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
</script>

<?php
include_once(G4_ADMIN_PATH.'/admin.tail.php');
?>
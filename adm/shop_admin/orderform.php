<?php
$sub_menu = '400400';
include_once('./_common.php');

$cart_title3 = '주문번호';
$cart_title4 = '배송완료';

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = "주문 내역 수정";
include_once(G5_ADMIN_PATH.'/admin.head.php');

$fr_date = isset($_REQUEST['fr_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['fr_date']) : '';
$to_date = isset($_REQUEST['to_date']) ? preg_replace('/[^0-9 :\-]/i', '', $_REQUEST['to_date']) : '';
$od_status = isset($_REQUEST['od_status']) ? clean_xss_tags($_REQUEST['od_status'], 1, 1) : '';
$od_settle_case = isset($_REQUEST['od_settle_case']) ? clean_xss_tags($_REQUEST['od_settle_case'], 1, 1) : '';
$od_misu = isset($_REQUEST['od_misu']) ? clean_xss_tags($_REQUEST['od_misu'], 1, 1) : '';
$od_cancel_price = isset($_REQUEST['od_cancel_price']) ? clean_xss_tags($_REQUEST['od_cancel_price'], 1, 1) : '';
$od_refund_price = isset($_REQUEST['od_refund_price']) ? clean_xss_tags($_REQUEST['od_refund_price'], 1, 1) : '';
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
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (! (isset($od['od_id']) && $od['od_id'])) {
    alert("해당 주문번호로 주문서가 존재하지 않습니다.");
}

$od['mb_id'] = $od['mb_id'] ? $od['mb_id'] : "비회원";
//------------------------------------------------------------------------------


$pg_anchor = '<ul class="anchor">
<li><a href="#anc_sodr_list">주문상품 목록</a></li>
<li><a href="#anc_sodr_pay">주문결제 내역</a></li>
<li><a href="#anc_sodr_chk">결제상세정보 확인</a></li>
<li><a href="#anc_sodr_paymo">결제상세정보 수정</a></li>
<li><a href="#anc_sodr_memo">상점메모</a></li>
<li><a href="#anc_sodr_orderer">주문하신 분</a></li>
<li><a href="#anc_sodr_taker">받으시는 분</a></li>
</ul>';

$html_receipt_chk = '<input type="checkbox" id="od_receipt_chk" value="'.$od['od_misu'].'" onclick="chk_receipt_price()">
<label for="od_receipt_chk">결제금액 입력</label><br>';

$qstr1 = "od_status=".urlencode($od_status)."&amp;od_settle_case=".urlencode($od_settle_case)."&amp;od_misu=$od_misu&amp;od_cancel_price=$od_cancel_price&amp;od_refund_price=$od_refund_price&amp;od_receipt_point=$od_receipt_point&amp;od_coupon=$od_coupon&amp;fr_date=$fr_date&amp;to_date=$to_date&amp;sel_field=$sel_field&amp;search=$search&amp;save_search=$search";
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
           from {$g5['g5_shop_cart_table']}
          where od_id = '{$od['od_id']}'
          group by it_id
          order by ct_id ";
$result = sql_query($sql);

// 주소 참고항목 필드추가
if(!isset($od['od_addr3'])) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_addr3` varchar(255) NOT NULL DEFAULT '' AFTER `od_addr2`,
                    ADD `od_b_addr3` varchar(255) NOT NULL DEFAULT '' AFTER `od_b_addr2` ", true);
}

// 배송목록에 참고항목 필드추가
if(!sql_query(" select ad_addr3 from {$g5['g5_shop_order_address_table']} limit 1", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_address_table']}`
                    ADD `ad_addr3` varchar(255) NOT NULL DEFAULT '' AFTER `ad_addr2` ", true);
}

// 결제 PG 필드 추가
if(!sql_query(" select od_pg from {$g5['g5_shop_order_table']} limit 1 ", false)) {
    sql_query(" ALTER TABLE `{$g5['g5_shop_order_table']}`
                    ADD `od_pg` varchar(255) NOT NULL DEFAULT '' AFTER `od_mobile`,
                    ADD `od_casseqno` varchar(255) NOT NULL DEFAULT '' AFTER `od_escrow` ", true);

    // 주문 결제 PG kcp로 설정
    sql_query(" update {$g5['g5_shop_order_table']} set od_pg = 'kcp' ");
}

// LG 현금영수증 JS
if($od['od_pg'] == 'lg') {
    if($default['de_card_test']) {
    echo '<script language="JavaScript" src="'.SHOP_TOSSPAYMENTS_CASHRECEIPT_TEST_JS.'"></script>'.PHP_EOL;
    } else {
        echo '<script language="JavaScript" src="'.SHOP_TOSSPAYMENTS_CASHRECEIPT_REAL_JS.'"></script>'.PHP_EOL;
    }
}

$print_od_deposit_name = $od['od_deposit_name'];
// nicepay 로 주문하고 가상계좌인 경우
if ($od['od_pg'] === 'nicepay' && $od['od_settle_case'] === '가상계좌' && $od['od_deposit_name']){
    $print_od_deposit_name .= '_NICE';
}

if (!function_exists('shop_admin_get_nicepay_status')) {
    function shop_admin_get_nicepay_status($tid, $default)
    {
        $result = array(
            'data' => array(),
            'error' => ''
        );

        if (!function_exists('curl_init')) {
            $result['error'] = 'cURL 모듈이 설치되어 있지 않아 나이스페이 거래 상태를 조회할 수 없습니다.';
            return $result;
        }

        $tid = preg_replace('/[^0-9a-zA-Z]/', '', (string) $tid);
        if ($tid === '') {
            $result['error'] = '거래 ID가 없어 나이스페이 거래 상태를 조회할 수 없습니다.';
            return $result;
        }

        if (!empty($default['de_card_test'])) {
            $mid = 'nicepay00m';
            $merchantKey = 'EYzu8jGGMfqaDEp76gSckuvnaHHu+bC4opsSN6lHv3b2lurNYkVXrZ7Z1AoqQnXI3eLuaUFyoRNC6FkrzVjceg==';
        } else {
            $mid = 'SR'.$default['de_nicepay_mid'];
            $merchantKey = $default['de_nicepay_key'];
        }

        if ($mid === 'SR' || $merchantKey === '') {
            $result['error'] = '나이스페이 MID 또는 KEY가 설정되어 있지 않아 거래 상태를 조회할 수 없습니다.';
            return $result;
        }

        $ediDate = preg_replace('/[^0-9]/', '', G5_TIME_YMDHIS);
        $signData = bin2hex(hash('sha256', $tid.$mid.$ediDate.$merchantKey, true));
        $postUrl = 'https://webapi.nicepay.co.kr/webapi/inquery/trans_status.jsp';
        $data = array(
            'TID' => $tid,
            'MID' => $mid,
            'EdiDate' => $ediDate,
            'SignData' => $signData,
            'CharSet' => 'utf-8',
            'EdiType' => 'JSON'
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $postUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 25);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_POST, true);
        $response = curl_exec($ch);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $response === '') {
            $result['error'] = '나이스페이 거래 상태 조회 응답이 없습니다.'.($curl_error ? ' '.$curl_error : '');
            return $result;
        }

        $responseData = json_decode($response, true);
        if (!is_array($responseData)) {
            $utf8_response = @iconv('EUC-KR', 'UTF-8//IGNORE', $response);
            if ($utf8_response) {
                $responseData = json_decode($utf8_response, true);
            }
        }

        if (!is_array($responseData)) {
            $result['error'] = '나이스페이 거래 상태 조회 응답을 해석할 수 없습니다.';
            return $result;
        }

        $result['data'] = $responseData;
        return $result;
    }
}

if (!function_exists('shop_admin_format_nicepay_auth_date')) {
    function shop_admin_format_nicepay_auth_date($auth_date)
    {
        $auth_date = preg_replace('/[^0-9]/', '', (string) $auth_date);

        if (strlen($auth_date) === 12) {
            return '20'.substr($auth_date, 0, 2).'-'.substr($auth_date, 2, 2).'-'.substr($auth_date, 4, 2).' '.substr($auth_date, 6, 2).':'.substr($auth_date, 8, 2).':'.substr($auth_date, 10, 2);
        }

        if (strlen($auth_date) === 14) {
            return substr($auth_date, 0, 4).'-'.substr($auth_date, 4, 2).'-'.substr($auth_date, 6, 2).' '.substr($auth_date, 8, 2).':'.substr($auth_date, 10, 2).':'.substr($auth_date, 12, 2);
        }

        return $auth_date;
    }
}

$nicepay_status_result = array();
$nicepay_status_error = '';
if ($od['od_pg'] === 'nicepay') {
    $nicepay_status = shop_admin_get_nicepay_status($od['od_tno'], $default);
    $nicepay_status_result = $nicepay_status['data'];
    $nicepay_status_error = $nicepay_status['error'];
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js

$is_nicepay_vbank_pg_cancel = ($od['od_pg'] === 'nicepay' && $od['od_settle_case'] === '가상계좌' && is_cancel_shop_pg_order($od));
if ($is_nicepay_vbank_pg_cancel) {
    add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal.css">', 11);
    add_stylesheet('<link rel="stylesheet" href="'.G5_JS_URL.'/remodal/remodal-default-theme.css">', 12);
    add_javascript('<script src="'.G5_JS_URL.'/remodal/remodal.js"></script>', 10);
}
?>

<section id="anc_sodr_list">
    <h2 class="h2_frm">주문상품 목록</h2>
    <?php echo $pg_anchor; ?>
    <div class="local_desc02 local_desc">
        <p>
            현재 주문상태 <strong><?php echo $od['od_status'] ?></strong>
            |
            주문일시 <strong><?php echo substr($od['od_time'],0,16); ?> (<?php echo get_yoil($od['od_time']); ?>)</strong>
            |
            주문총액 <strong><?php echo number_format($od['od_cart_price'] + $od['od_send_cost'] + $od['od_send_cost2']); ?></strong>원
        </p>
        <?php if ($default['de_hope_date_use']) { ?><p>희망배송일은 <?php echo $od['od_hope_date']; ?> (<?php echo get_yoil($od['od_hope_date']); ?>) 입니다.</p><?php } ?>
        <?php if($od['od_mobile']) { ?>
        <p>모바일 쇼핑몰의 주문입니다.</p>
        <?php } ?>
    </div>

    <form name="frmorderform" method="post" action="./orderformcartupdate.php" onsubmit="return form_submit(this);">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="mb_id" value="<?php echo $od['mb_id']; ?>">
    <input type="hidden" name="od_email" value="<?php echo $od['od_email']; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page;?>">
    <input type="hidden" name="pg_cancel" value="0">
    <input type="hidden" name="submit_ct_status" value="">
    <?php if ($is_nicepay_vbank_pg_cancel) { ?>
    <input type="hidden" name="RefundAcctNo" value="">
    <input type="hidden" name="RefundBankCd" value="">
    <input type="hidden" name="RefundAcctNm" value="">
    <input type="hidden" name="nicepay_vbank_refund_confirmed" value="0">
    <?php } ?>

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
                        from {$g5['g5_shop_cart_table']}
                        where od_id = '{$od['od_id']}'
                          and it_id = '{$row['it_id']}'
                        order by io_type asc, ct_id asc ";
            $res = sql_query($sql);
            $rowspan = sql_num_rows($res);

            // 합계금액 계산
            $sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as price,
                            SUM(ct_qty) as qty
                        from {$g5['g5_shop_cart_table']}
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
            <strong>주문 및 장바구니 상태 변경</strong>
            <input type="submit" name="ct_status" value="주문" onclick="document.pressed=this.value" class="btn_02 color_01">
            <input type="submit" name="ct_status" value="입금" onclick="document.pressed=this.value" class="btn_02 color_02">
            <input type="submit" name="ct_status" value="준비" onclick="document.pressed=this.value" class="btn_02 color_03">
            <input type="submit" name="ct_status" value="배송" onclick="document.pressed=this.value" class="btn_02 color_04">
            <input type="submit" name="ct_status" value="완료" onclick="document.pressed=this.value" class="btn_02 color_05">
            <input type="submit" name="ct_status" value="취소" onclick="document.pressed=this.value" class="btn_02 color_06">
            <input type="submit" name="ct_status" value="반품" onclick="document.pressed=this.value" class="btn_02 color_06">
            <input type="submit" name="ct_status" value="품절" onclick="document.pressed=this.value" class="btn_02 color_06">
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
<?php if($od['od_pg'] === 'inicis' && !$od['od_test']) {
    $sql = "select P_TID from {$g5['g5_shop_inicis_log_table']} where oid = '$od_id' and P_STATUS = 'cancel' ";
    $tmp_row = sql_fetch($sql);
    if(isset($tmp_row['P_TID']) && $tmp_row['P_TID']){
?>
<div class="od_test_caution">주의) 이 주문은 결제취소된 내역이 있습니다. 이니시스 관리자 상점에서 반드시 재확인을 해 주세요.</div>
<?php 
    }   //end if
}   //end if
?>

<?php if ($od['od_pg'] === 'nicepay') { ?>
<section id="anc_sodr_nicepay_status">
    <h2 class="h2_frm">나이스페이 결제 조회</h2>
    <?php if ($nicepay_status_error) { ?>
    <div class="local_desc01 local_desc">
        <p><?php echo get_text($nicepay_status_error); ?></p>
    </div>
    <?php } else {
        $nicepay_status_map = array(
            '0' => '승인 상태',
            '1' => '취소 상태',
            '9' => '승인 거래 없음'
        );
        $nicepay_status_code = isset($nicepay_status_result['Status']) ? (string) $nicepay_status_result['Status'] : '';
        $nicepay_status_text = isset($nicepay_status_map[$nicepay_status_code]) ? $nicepay_status_map[$nicepay_status_code] : '';
        $nicepay_auth_date = isset($nicepay_status_result['AuthDate']) ? shop_admin_format_nicepay_auth_date($nicepay_status_result['AuthDate']) : '';
    ?>
    <div class="tbl_head01 tbl_wrap">
        <table>
        <caption>나이스페이 결제 조회</caption>
        <thead>
        <tr>
            <th scope="col">결과코드</th>
            <th scope="col">결과 메시지</th>
            <th scope="col">거래 ID</th>
            <th scope="col">거래 상태</th>
            <th scope="col">승인번호</th>
            <th scope="col">승인일시</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td><?php echo isset($nicepay_status_result['ResultCode']) ? get_text($nicepay_status_result['ResultCode']) : '-'; ?></td>
            <td><?php echo isset($nicepay_status_result['ResultMsg']) ? get_text($nicepay_status_result['ResultMsg']) : '-'; ?></td>
            <td><?php echo isset($nicepay_status_result['TID']) ? get_text($nicepay_status_result['TID']) : get_text($od['od_tno']); ?></td>
            <td><?php echo $nicepay_status_code !== '' ? get_text($nicepay_status_code.($nicepay_status_text ? ' ('.$nicepay_status_text.')' : '')) : '-'; ?></td>
            <td><?php echo isset($nicepay_status_result['AuthCode']) && $nicepay_status_result['AuthCode'] !== '' ? get_text($nicepay_status_result['AuthCode']) : '-'; ?></td>
            <td><?php echo $nicepay_auth_date !== '' ? get_text($nicepay_auth_date) : '-'; ?></td>
        </tr>
        </tbody>
        </table>
    </div>
    <?php } ?>
</section>
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
    $amount['cancel'] = $od['od_cancel_price'];

    // 미수금 = 주문금액 - 취소금액 - 입금금액 - 쿠폰금액
    //$amount['미수'] = $amount['order'] - $amount['receipt'] - $amount['coupon'];

    // 결제방법
    $s_receipt_way = get_text(check_pay_name_replace($od['od_settle_case'], $od));

    if ($od['od_receipt_point'] > 0)
        $s_receipt_way .= "+포인트";
    ?>

    <div class="tbl_head01 tbl_wrap">
        <strong class="sodr_nonpay">미수금 <?php echo display_price($od['od_misu']); ?></strong>

        <table>
        <caption>주문결제 내역</caption>
        <thead>
        <tr>
            <th scope="col">주문번호</th>
            <th scope="col">결제방법</th>
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
            <td class="td_numbig td_numcancel"><?php echo number_format($amount['cancel']); ?>원</td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section class="">
    <h2 class="h2_frm">결제상세정보</h2>
    <?php echo $pg_anchor; ?>

    <form name="frmorderreceiptform" action="./orderformreceiptupdate.php" method="post" autocomplete="off">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="od_name" value="<?php echo get_text($od['od_name']); ?>">
    <input type="hidden" name="od_hp" value="<?php echo get_text($od['od_hp']); ?>">
    <input type="hidden" name="od_tno" value="<?php echo $od['od_tno']; ?>">
    <input type="hidden" name="od_escrow" value="<?php echo $od['od_escrow']; ?>">
    <input type="hidden" name="od_pg" value="<?php echo $od['od_pg']; ?>">

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
                <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체') { ?>
                <?php if ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌') { ?>
                <tr>
                    <th scope="row">계좌번호</th>
                    <td><?php echo get_text($od['od_bank_account']); ?></td>
                </tr>
                <?php } ?>
                <tr>
                    <th scope="row"><?php echo get_text($od['od_settle_case']); ?> 입금액</th>
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
                    <th scope="row"><?php echo get_text($od['od_settle_case']); ?> 결제액</th>
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
                                case 'inicis':
                                    $pg_url  = 'https://iniweb.inicis.com/';
                                    $pg_test = 'KG이니시스 ';
                                    break;
                                case 'KAKAOPAY':
                                    $pg_url  = 'https://mms.cnspay.co.kr';
                                    $pg_test = 'KAKAOPAY ';
                                    break;
                                case 'nicepay':
                                    $pg_url  = 'https://npg.nicepay.co.kr/';
                                    $pg_test = 'NICEPAY ';
                                    break;
                                case 'lg':
                                case 'toss':
                                    $pg_url  = 'https://app.tosspayments.com';
                                    $pg_test = '토스페이먼츠 ';
                                    // 상점관리자 로그인 후 상단 '테스트 모드' 활성화 시 테스트 화면 노출
                                    break;
                                default:
                                    $pg_url  = 'https://partner.kcp.co.kr';
                                    $pg_test = 'KCP ';
                                    if ($default['de_card_test']) {
                                        // 로그인 아이디 / 비번
                                        // 일반 : test1234 / test12345
                                        // 에스크로 : escrow / escrow913
                                        $pg_url = 'https://testpartner.kcp.co.kr';
                                        $pg_test .= '테스트 ';
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
                    <th scope="row">결제취소/환불액</th>
                    <td><?php echo display_price($od['od_refund_price']); ?></td>
                </tr>
                <?php if ($od['od_invoice']) { ?>
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
                    <td><?php echo is_null_time($od['od_invoice_time']) ? "" : $od['od_invoice_time']; ?></td>
                </tr>
                <?php } ?>
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
                <?php
                if ($od['od_misu'] == 0 && $od['od_receipt_price'] && ($od['od_settle_case'] == '무통장' || $od['od_settle_case'] == '가상계좌' || $od['od_settle_case'] == '계좌이체')) {
                ?>
                <tr>
                    <th scope="row">현금영수증</th>
                    <td>
                    <?php
                    if ($od['od_cash']) {
                        if($od['od_pg'] == 'lg') {
                            require G5_SHOP_PATH.'/settle_lg.inc.php';

                            switch($od['od_settle_case']) {
                                case '계좌이체':
                                    $trade_type = 'BANK';
                                    break;
                                case '가상계좌':
                                    $trade_type = 'CAS';
                                    break;
                                default:
                                    $trade_type = 'CR';
                                    break;
                            }
                            $cash_receipt_script = 'javascript:showCashReceipts(\''.$LGD_MID.'\',\''.$od['od_id'].'\',\''.$od['od_casseqno'].'\',\''.$trade_type.'\',\''.$CST_PLATFORM.'\');';
                        } else if($od['od_pg'] == 'toss') {
                            $cash_receipt_script = 'window.open(\'https://dashboard.tosspayments.com/receipt/mids/si_'.$config['cf_lg_mid'].'/orders/'.$od['od_id'].'/cash-receipt?ref=dashboard\',\'receipt\',\'width=430,height=700\');';
                        } else if($od['od_pg'] == 'inicis') {
                            $cash = unserialize($od['od_cash_info']);
                            $cash_receipt_script = 'window.open(\'https://iniweb.inicis.com/DefaultWebApp/mall/cr/cm/Cash_mCmReceipt.jsp?noTid='.$cash['TID'].'&clpaymethod=22\',\'showreceipt\',\'width=380,height=540,scrollbars=no,resizable=no\');';
                        } else if($od['od_pg'] == 'nicepay') {
                            
                            $od_tid = $od['od_tno'];
                            $cash_type = 0;

                            if (! $od_tid) {
                                $cash = unserialize($od['od_cash_info']);
                                $od_tid = isset($cash['TID']) ? $cash['TID'] : '';
                                $cash_type = $od_tid ? 1 : 0;
                            }

                            $cash_receipt_script = 'window.open(\'https://npg.nicepay.co.kr/issue/IssueLoader.do?type='.$cash_type.'&TID='.$od_tid.'&noMethod=1\',\'receipt\',\'width=430,height=700\');';
                        } else {
                            require G5_SHOP_PATH.'/settle_kcp.inc.php';

                            $cash = unserialize($od['od_cash_info']);
                            $cash_receipt_script = 'window.open(\''.G5_CASH_RECEIPT_URL.$default['de_kcp_mid'].'&orderid='.$od_id.'&bill_yn=Y&authno='.$cash['receipt_no'].'\', \'taxsave_receipt\', \'width=360,height=647,scrollbars=0,menus=0\');';
                        }
                    ?>
                        <a href="javascript:;" onclick="<?php echo $cash_receipt_script; ?>">현금영수증 확인</a>
                    <?php } else { ?>
                        <a href="javascript:;" onclick="window.open('<?php echo G5_SHOP_URL; ?>/taxsave.php?od_id=<?php echo $od_id; ?>', 'taxsave', 'width=550,height=400,scrollbars=1,menus=0');">현금영수증 발급</a>
                    <?php } ?>
                    </td>
                </tr>
                <?php
                }
                ?>
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
                    <th scope="row"><label for="od_receipt_price"><?php echo get_text($od['od_settle_case']); ?> 입금액</label></th>
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
                    <th scope="row"><label for="od_receipt_price"><?php echo get_text($od['od_settle_case']); ?> 결제액</label></th>
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
                <tr>
                    <th scope="row" class="sodr_sppay"><label for="od_receipt_time">카드 승인일시</label></th>
                    <td>
                        <input type="checkbox" name="od_card_chk" id="od_card_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="if (this.checked == true) this.form.od_receipt_time.value=this.form.od_card_chk.value; else this.form.od_receipt_time.value = this.form.od_receipt_time.defaultValue;">
                        <label for="od_card_chk">현재 시간으로 설정</label><br>
                        <input type="text" name="od_receipt_time" value="<?php echo is_null_time($od['od_receipt_time']) ? "" : $od['od_receipt_time']; ?>" id="od_receipt_time" class="frm_input" size="19" maxlength="19">
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
                    <th scope="row"><label for="od_refund_price">결제취소/환불 금액</label></th>
                    <td>
                        <input type="text" name="od_refund_price" value="<?php echo $od['od_refund_price']; ?>" id="od_refund_price" class="frm_input" size="10"> 원
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_invoice">운송장번호</label></th>
                    <td>
                        <?php if ($config['cf_sms_use'] && $default['de_sms_use5']) { ?>
                        <input type="checkbox" name="od_sms_baesong_check" id="od_sms_baesong_check">
                        <label for="od_sms_baesong_check">SMS 배송 문자전송</label>
                        <br>
                        <?php } ?>

                        <input type="text" name="od_invoice" value="<?php echo $od['od_invoice']; ?>" id="od_invoice" class="frm_input">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_delivery_company">배송회사</label></th>
                    <td>
                        <input type="checkbox" id="od_delivery_chk" value="<?php echo $default['de_delivery_company']; ?>" onclick="chk_delivery_company()">
                        <label for="od_delivery_chk">기본 배송회사로 설정</label><br>
                        <input type="text" name="od_delivery_company" id="od_delivery_company" value="<?php echo $od['od_delivery_company']; ?>" class="frm_input">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="od_invoice_time">배송일시</label></th>
                    <td>
                        <input type="checkbox" id="od_invoice_chk" value="<?php echo date("Y-m-d H:i:s", G5_SERVER_TIME); ?>" onclick="chk_invoice_time()">
                        <label for="od_invoice_chk">현재 시간으로 설정</label><br>
                        <input type="text" name="od_invoice_time" id="od_invoice_time" value="<?php echo is_null_time($od['od_invoice_time']) ? "" : $od['od_invoice_time']; ?>" class="frm_input" maxlength="19">
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
        <?php if($od['od_status'] == '주문' && $od['od_misu'] > 0) { ?>
        <a href="./personalpayform.php?popup=yes&amp;od_id=<?php echo $od_id; ?>" id="personalpay_add" class="btn btn_02">개인결제추가</a>
        <?php } ?>
        <?php if($od['od_misu'] < 0 && ($od['od_receipt_price'] - $od['od_refund_price']) > 0 && ($od['od_settle_case'] == '신용카드' || $od['od_settle_case'] == '계좌이체' || $od['od_settle_case'] == 'KAKAOPAY' || ($od['od_pg'] == 'nicepay' && $od['od_settle_case'] == '간편결제'))) { ?>
        <a href="./orderpartcancel.php?od_id=<?php echo $od_id; ?>" id="orderpartcancel" class="btn btn_02"><?php echo get_text($od['od_settle_case']); ?> 부분취소</a>
        <?php } ?>
        <a href="./orderlist.php?<?php echo $qstr; ?>" class="btn btn_02">목록</a>
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

    <form name="frmorderform2" action="./orderformupdate.php" method="post">
    <input type="hidden" name="od_id" value="<?php echo $od_id; ?>">
    <input type="hidden" name="sort1" value="<?php echo $sort1; ?>">
    <input type="hidden" name="sort2" value="<?php echo $sort2; ?>">
    <input type="hidden" name="sel_field" value="<?php echo $sel_field; ?>">
    <input type="hidden" name="search" value="<?php echo $search; ?>">
    <input type="hidden" name="page" value="<?php echo $page; ?>">
    <input type="hidden" name="mod_type" value="memo">

    <div class="tbl_wrap">
        <label for="od_shop_memo" class="sound_only">상점메모</label>
        <textarea name="od_shop_memo" id="od_shop_memo" rows="8"><?php echo html_purifier(stripslashes($od['od_shop_memo'])); ?></textarea>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="메모 수정" class="btn_submit btn">
    </div>

    </form>
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

<?php if ($is_nicepay_vbank_pg_cancel) {
    $nicepay_refund_bank_codes = array(
        '은행/기관' => array(
            '001' => '한국은행',
            '002' => '산업은행',
            '003' => '기업은행',
            '004' => '국민은행',
            '005' => '외환은행',
            '007' => '수협은행',
            '008' => '수출입은행',
            '011' => 'NH농협은행',
            '012' => '농협중앙회(지역농축협)',
            '020' => '우리은행',
            '023' => 'SC제일은행',
            '026' => '서울은행',
            '027' => '한국씨티은행',
            '030' => '수협중앙회',
            '031' => 'iM뱅크(대구)',
            '032' => '부산은행',
            '034' => '광주은행',
            '035' => '제주은행',
            '037' => '전북은행',
            '039' => '경남은행',
            '045' => '새마을금고',
            '048' => '신협',
            '050' => '저축은행',
            '051' => '기타 외국계은행',
            '052' => '모건스탠리은행',
            '054' => 'HSBC은행',
            '055' => '도이치은행',
            '056' => '알비에스피엘씨은행',
            '057' => '제이피모간체이스은행',
            '058' => '미즈호은행',
            '059' => '엠유에프지은행',
            '060' => 'BOA은행',
            '061' => '비엔피파리바은행',
            '062' => '중국공상은행',
            '063' => '중국은행',
            '064' => '산림조합중앙회',
            '065' => '대화은행',
            '071' => '우체국',
            '076' => '신용보증기금',
            '077' => '기술보증기금',
            '081' => '하나은행',
            '088' => '신한은행',
            '089' => '케이뱅크',
            '090' => '카카오뱅크',
            '092' => '토스뱅크',
            '093' => '한국주택금융공사',
            '094' => '서울보증보험',
            '095' => '경찰청',
            '099' => '금융결제원'
        ),
        '증권사' => array(
            '209' => '유안타증권',
            '218' => 'KB증권',
            '238' => '미래에셋증권',
            '240' => '삼성증권',
            '243' => '한국투자증권',
            '247' => 'NH투자증권',
            '261' => '교보증권',
            '262' => 'iM증권',
            '263' => '현대차증권',
            '264' => '키움증권',
            '265' => 'LS증권',
            '266' => '에스케이증권',
            '267' => '대신증권',
            '269' => '한화투자증권',
            '270' => '하나증권',
            '271' => '토스증권',
            '278' => '신한투자증권',
            '279' => 'DB증권',
            '280' => '유진투자증권',
            '287' => '메리츠증권',
            '288' => '카카오페이증권',
            '289' => '엔에이치투자증권',
            '290' => '부국증권',
            '291' => '신영증권',
            '292' => '케이프투자증권'
        )
    );
?>
<div class="remodal" data-remodal-id="nicepay-vbank-refund" data-remodal-options="hashTracking:false, closeOnOutsideClick:false">
    <button data-remodal-action="close" class="remodal-close"></button>
    <h3>나이스페이 가상계좌 환불 정보</h3>
    <p>입금 후 환불 건은 환불계좌 정보를 입력해야 합니다. 입금 전 발급취소 건은 비워두고 진행할 수 있습니다.</p>
    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>나이스페이 가상계좌 환불 정보 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="nicepay_refund_acct_no">환불계좌번호</label></th>
            <td><input type="text" id="nicepay_refund_acct_no" class="frm_input" size="22" maxlength="16" inputmode="numeric" autocomplete="off"> 숫자만 입력</td>
        </tr>
        <tr>
            <th scope="row"><label for="nicepay_refund_bank_cd">환불은행</label></th>
            <td>
                <select id="nicepay_refund_bank_cd" class="frm_input">
                    <option value="">은행을 선택해 주세요</option>
                    <?php foreach ($nicepay_refund_bank_codes as $bank_group => $bank_codes) { ?>
                    <optgroup label="<?php echo $bank_group; ?>">
                        <?php foreach ($bank_codes as $bank_code => $bank_name) { ?>
                        <option value="<?php echo $bank_code; ?>"><?php echo $bank_name; ?> (<?php echo $bank_code; ?>)</option>
                        <?php } ?>
                    </optgroup>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="nicepay_refund_acct_nm">환불계좌주명</label></th>
            <td><input type="text" id="nicepay_refund_acct_nm" class="frm_input" size="18" maxlength="10" autocomplete="off"> 10 byte 이하</td>
        </tr>
        </tbody>
        </table>
    </div>
    <br>
    <button type="button" class="remodal-cancel" data-remodal-action="cancel">취소</button>
    <button type="button" class="remodal-confirm" id="nicepay_vbank_refund_submit">확인</button>
</div>
<?php } ?>

<script>
<?php if ($is_nicepay_vbank_pg_cancel) { ?>
var nicepayVbankCancel = true;
var nicepayVbankRefundRequired = <?php echo ((int) $od['od_receipt_price'] > 0) ? 'true' : 'false'; ?>;
<?php } else { ?>
var nicepayVbankCancel = false;
var nicepayVbankRefundRequired = false;
<?php } ?>

function nicepay_submit_orderform(f)
{
    var status = document.pressed || (f.submit_ct_status ? f.submit_ct_status.value : "");
    var statusInput = f.querySelector("input[type=hidden][name=ct_status]");

    if (!statusInput) {
        statusInput = document.createElement("input");
        statusInput.type = "hidden";
        statusInput.name = "ct_status";
        f.appendChild(statusInput);
    }

    statusInput.value = status;
    f.submit();
}

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

    $("#nicepay_refund_acct_no").on("input", function() {
        this.value = this.value.replace(/[^0-9]/g, "");
    });

    $("#nicepay_vbank_refund_submit").on("click", function() {
        var f = document.frmorderform;
        var acctNo = $.trim($("#nicepay_refund_acct_no").val()).replace(/[^0-9]/g, "");
        var bankCd = $.trim($("#nicepay_refund_bank_cd").val()).replace(/[^0-9]/g, "");
        var acctNm = $.trim($("#nicepay_refund_acct_nm").val());
        var hasRefundInfo = (acctNo !== "" || bankCd !== "" || acctNm !== "");

        if (!nicepayVbankRefundRequired && !hasRefundInfo) {
            f.RefundAcctNo.value = "";
            f.RefundBankCd.value = "";
            f.RefundAcctNm.value = "";
            f.nicepay_vbank_refund_confirmed.value = 1;

            if (form_submit(f)) {
                nicepay_submit_orderform(f);
            }

            return false;
        }

        if (acctNo === "" || acctNo.length > 16) {
            alert("환불계좌번호를 숫자 16자리 이하로 입력해 주십시오.");
            $("#nicepay_refund_acct_no").focus();
            return false;
        }

        if (!/^[0-9]{3}$/.test(bankCd)) {
            alert("환불은행을 선택해 주십시오.");
            $("#nicepay_refund_bank_cd").focus();
            return false;
        }

        if (acctNm === "") {
            alert("환불계좌주명을 입력해 주십시오.");
            $("#nicepay_refund_acct_nm").focus();
            return false;
        }

        f.RefundAcctNo.value = acctNo;
        f.RefundBankCd.value = bankCd;
        f.RefundAcctNm.value = acctNm;
        f.nicepay_vbank_refund_confirmed.value = 1;

        if (form_submit(f)) {
            nicepay_submit_orderform(f);
        }

        return false;
    });
});

function form_submit(f)
{
    var check = false;
    var status = document.pressed || (f.submit_ct_status ? f.submit_ct_status.value : "");

    if (f.submit_ct_status) {
        f.submit_ct_status.value = status;
    }

    for (i=0; i<f.chk_cnt.value; i++) {
        if (document.getElementById('ct_chk_'+i).checked == true)
            check = true;
    }

    if (check == false) {
        alert("처리할 자료를 하나 이상 선택해 주십시오.");
        return false;
    }

    var msg = "";

    <?php if (is_cancel_shop_pg_order($od)) { ?>
    if(status == "취소" || status == "반품" || status == "품절") {
        var $ct_chk = $("input[name^=ct_chk]");
        var chk_cnt = $ct_chk.length;
        var chked_cnt = $ct_chk.filter(":checked").length;
        <?php if($od['od_pg'] == 'KAKAOPAY') { ?>
        var cancel_pg = "카카오페이";
        <?php } else { ?>
        var cancel_pg = "PG사의 <?php echo get_text($od['od_settle_case']); ?>";
        <?php } ?>

        // 체크하지 않은 나머지 품목이 모두 취소류 상태이면 이번 처리로 주문 전체가 취소된다.
        var remain_active_cnt = $ct_chk.not(":checked").filter(function() {
            var row_status = $.trim($(this).closest("tr").find("td.td_mngsmall").first().text());
            return row_status != "취소" && row_status != "반품" && row_status != "품절";
        }).length;

        if(chked_cnt > 0 && remain_active_cnt == 0) {
            if(nicepayVbankCancel && f.nicepay_vbank_refund_confirmed.value == "1") {
                f.pg_cancel.value = 1;
                msg = cancel_pg+" 결제 취소와 함께 ";
            } else if(confirm(cancel_pg+" 결제를 함께 취소하시겠습니까?\n\n한번 취소한 결제는 다시 복구할 수 없습니다.")) {
                f.pg_cancel.value = 1;
                msg = cancel_pg+" 결제 취소와 함께 ";
                if (nicepayVbankCancel && f.nicepay_vbank_refund_confirmed.value != "1") {
                    $('[data-remodal-id=nicepay-vbank-refund]').remodal().open();
                    return false;
                }
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
        if (nicepayVbankCancel && f.nicepay_vbank_refund_confirmed) {
            f.nicepay_vbank_refund_confirmed.value = 0;
        }
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

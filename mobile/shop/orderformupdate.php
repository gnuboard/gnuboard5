<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

$post_p_hash = isset($_POST['P_HASH']) ? $_POST['P_HASH'] : '';
$post_enc_data = isset($_POST['enc_data']) ? $_POST['enc_data'] : '';
$post_enc_info = isset($_POST['enc_info']) ? $_POST['enc_info'] : '';
$post_tran_cd = isset($_POST['tran_cd']) ? $_POST['tran_cd'] : '';
$post_lgd_paykey = isset($_POST['LGD_PAYKEY']) ? $_POST['LGD_PAYKEY'] : '';

//삼성페이 또는 lpay 또는 이니시스 카카오페이 요청으로 왔다면 현재 삼성페이 또는 lpay 또는 이니시스 카카오페이는 이니시스 밖에 없으므로 $default['de_pg_service'] 값을 이니시스로 변경한다.
if( is_inicis_order_pay($od_settle_case) && !empty($_POST['P_HASH']) ){
    $default['de_pg_service'] = 'inicis';
}

// 타 PG 사용시 NHN KCP 네이버페이로 결제 요청이 왔다면 $default['de_pg_service'] 값을 kcp 로 변경합니다.
if(function_exists('is_use_easypay') && is_use_easypay('global_nhnkcp') && $post_enc_data && isset($_POST['site_cd']) && isset($_POST['nhnkcp_pay_case']) && $_POST['nhnkcp_pay_case'] === "naverpay"){
    $default['de_pg_service'] = 'kcp';
}

if( $default['de_pg_service'] == 'inicis' && get_session('ss_order_id') ){
    if( $exist_order = get_shop_order_data(get_session('ss_order_id')) ){    //이미 상품이 주문되었다면 리다이렉트
        if(isset($exist_order['od_tno']) && $exist_order['od_tno']){
            exists_inicis_shop_order(get_session('ss_order_id'), array(), $exist_order['od_time'], $exist_order['od_ip']);
            exit;
        }
    }
}

if(function_exists('add_order_post_log')) add_order_post_log('init', 'init');

$page_return_url = G5_SHOP_URL.'/orderform.php';
if(get_session('ss_direct'))
    $page_return_url .= '?sw_direct=1';

// 결제등록 완료 체크
if($od_settle_case != '무통장' && $od_settle_case != 'KAKAOPAY') {
    if($default['de_pg_service'] == 'kcp' && ($post_tran_cd === '' || $post_enc_info === '' || $post_enc_data === ''))
        alert('결제등록 요청 후 주문해 주십시오.', $page_return_url);

    if($default['de_pg_service'] == 'lg' && ! $post_lgd_paykey)
        alert('결제등록 요청 후 주문해 주십시오.', $page_return_url);

    if($default['de_pg_service'] == 'inicis' && ! $post_p_hash)
        alert('결제등록 요청 후 주문해 주십시오.', $page_return_url);
}

// 장바구니가 비어있는가?
if (get_session('ss_direct'))
    $tmp_cart_id = get_session('ss_cart_direct');
else
    $tmp_cart_id = get_session('ss_cart_id');

if (get_cart_count($tmp_cart_id) == 0) {    // 장바구니에 담기
    if(function_exists('add_order_post_log')) add_order_post_log('장바구니가 비어 있습니다.');
    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SHOP_URL.'/cart.php');
}

$sql = "select * from {$g5['g5_shop_order_table']} limit 1";
$check_tmp = sql_fetch($sql);

if(!isset($check_tmp['od_other_pay_type'])){
    $sql = "ALTER TABLE `{$g5['g5_shop_order_table']}` 
            ADD COLUMN `od_other_pay_type` VARCHAR(100) NOT NULL DEFAULT '' AFTER `od_settle_case`; ";
    sql_query($sql, false);
}

// 변수 초기화
$od_other_pay_type = '';

$od_temp_point = isset($_POST['od_temp_point']) ? (int) $_POST['od_temp_point'] : 0;
$od_hope_date = isset($_POST['od_hope_date']) ? clean_xss_tags($_POST['od_hope_date'], 1, 1) : '';
$ad_default = isset($_POST['ad_default']) ? (int) $_POST['ad_default'] : 0;

$error = "";
// 장바구니 상품 재고 검사
$sql = " select it_id,
                ct_qty,
                it_name,
                io_id,
                io_type,
                ct_option
           from {$g5['g5_shop_cart_table']}
          where od_id = '$tmp_cart_id'
            and ct_select = '1' ";
$result = sql_query($sql);
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    // 상품에 대한 현재고수량
    if($row['io_id']) {
        $it_stock_qty = (int)get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);
    } else {
        $it_stock_qty = (int)get_it_stock_qty($row['it_id']);
    }
    // 장바구니 수량이 재고수량보다 많다면 오류
    if ($row['ct_qty'] > $it_stock_qty)
        $error .= "{$row['ct_option']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
}

if($i == 0) {
    if(function_exists('add_order_post_log')) add_order_post_log('장바구니가 비어 있습니다.');
    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SHOP_URL.'/cart.php');
}

if ($error != "")
{
    $error .= "다른 고객님께서 {$od_name}님 보다 먼저 주문하신 경우입니다. 불편을 끼쳐 죄송합니다.";
    if(function_exists('add_order_post_log')) add_order_post_log($error);
    alert($error, $page_return_url);
}

$i_price     = isset($_POST['od_price']) ? (int) $_POST['od_price'] : 0;
$i_send_cost  = isset($_POST['od_send_cost']) ? (int) $_POST['od_send_cost'] : 0;
$i_send_cost2  = isset($_POST['od_send_cost2']) ? (int) $_POST['od_send_cost2'] : 0;
$i_send_coupon  = isset($_POST['od_send_coupon']) ? abs((int) $_POST['od_send_coupon']) : 0;
$i_temp_point = isset($_POST['od_temp_point']) ? (int) $_POST['od_temp_point'] : 0;


// 주문금액이 상이함
$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as od_price,
              COUNT(distinct it_id) as cart_count
            from {$g5['g5_shop_cart_table']} where od_id = '$tmp_cart_id' and ct_select = '1' ";
$row = sql_fetch($sql);
$tot_ct_price = $row['od_price'];
$cart_count = $row['cart_count'];
$tot_od_price = $tot_ct_price;

// 쿠폰금액계산
$tot_cp_price = $tot_it_cp_price = $tot_od_cp_price = 0;
if($is_member) {
    // 상품쿠폰
    $it_cp_cnt = (isset($_POST['cp_id']) && is_array($_POST['cp_id'])) ? count($_POST['cp_id']) : 0;
    $arr_it_cp_prc = array();
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = isset($_POST['cp_id'][$i]) ? $_POST['cp_id'][$i] : '';
        $it_id = isset($_POST['it_id'][$i]) ? safe_replace_regex($_POST['it_id'][$i], 'it_id') : '';
        $sql = " select cp_id, cp_method, cp_target, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '$cid'
                      and mb_id IN ( '{$member['mb_id']}', '전체회원' )
                      and cp_start <= '".G5_TIME_YMD."'
                      and cp_end >= '".G5_TIME_YMD."'
                      and cp_method IN ( 0, 1 ) ";
        $cp = sql_fetch($sql);
        if(! (isset($cp['cp_id']) && $cp['cp_id']))
            continue;

        // 사용한 쿠폰인지
        if(is_used_coupon($member['mb_id'], $cp['cp_id']))
            continue;

        // 분류할인인지
        if($cp['cp_method']) {
            $sql2 = " select it_id, ca_id, ca_id2, ca_id3
                        from {$g5['g5_shop_item_table']}
                        where it_id = '$it_id' ";
            $row2 = sql_fetch($sql2);

            if(!$row2['it_id'])
                continue;

            if($row2['ca_id'] != $cp['cp_target'] && $row2['ca_id2'] != $cp['cp_target'] && $row2['ca_id3'] != $cp['cp_target'])
                continue;
        } else {
            if($cp['cp_target'] != $it_id)
                continue;
        }

        // 상품금액
        $sql = " select SUM( IF(io_type = '1', io_price * ct_qty, (ct_price + io_price) * ct_qty)) as sum_price
                    from {$g5['g5_shop_cart_table']}
                    where od_id = '$tmp_cart_id'
                      and it_id = '$it_id'
                      and ct_select = '1' ";
        $ct = sql_fetch($sql);
        $item_price = $ct['sum_price'];

        if($cp['cp_minimum'] > $item_price)
            continue;

        $dc = 0;
        if($cp['cp_type']) {
            $dc = floor(($item_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
        } else {
            $dc = $cp['cp_price'];
        }

        if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
            $dc = $cp['cp_maximum'];

        if($item_price < $dc)
            continue;

        $tot_it_cp_price += $dc;
        $arr_it_cp_prc[$it_id] = $dc;
    }

    $tot_od_price -= $tot_it_cp_price;

    // 주문쿠폰
    if(isset($_POST['od_cp_id']) && $_POST['od_cp_id']) {
        $sql = " select cp_id, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '{$_POST['od_cp_id']}'
                      and mb_id IN ( '{$member['mb_id']}', '전체회원' )
                      and cp_start <= '".G5_TIME_YMD."'
                      and cp_end >= '".G5_TIME_YMD."'
                      and cp_method = '2' ";
        $cp = sql_fetch($sql);

        // 사용한 쿠폰인지
        $cp_used = is_used_coupon($member['mb_id'], $cp['cp_id']);

        $dc = 0;
        if(!$cp_used && $cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_price)) {
            if($cp['cp_type']) {
                $dc = floor(($tot_od_price * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_price'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            if($tot_od_price < $dc)
                die('Order coupon error.');

            $tot_od_cp_price = $dc;
            $tot_od_price -= $tot_od_cp_price;
        }
    }

    $tot_cp_price = $tot_it_cp_price + $tot_od_cp_price;
}

if ((int)($row['od_price'] - $tot_cp_price) !== $i_price) {
    if(function_exists('add_order_post_log')) add_order_post_log('쿠폰금액 최종 계산 Error.');
    die("Error.");
}

// 배송비가 상이함
$send_cost = get_sendcost($tmp_cart_id);

$tot_sc_cp_price = 0;
if($is_member && $send_cost > 0) {
    // 배송쿠폰
    if(isset($_POST['sc_cp_id']) && $_POST['sc_cp_id']) {
        $sql = " select cp_id, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum
                    from {$g5['g5_shop_coupon_table']}
                    where cp_id = '{$_POST['sc_cp_id']}'
                      and mb_id IN ( '{$member['mb_id']}', '전체회원' )
                      and cp_start <= '".G5_TIME_YMD."'
                      and cp_end >= '".G5_TIME_YMD."'
                      and cp_method = '3' ";
        $cp = sql_fetch($sql);

        // 사용한 쿠폰인지
        $cp_used = is_used_coupon($member['mb_id'], $cp['cp_id']);

        $dc = 0;
        if(!$cp_used && $cp['cp_id'] && ($cp['cp_minimum'] <= $tot_od_price)) {
            if($cp['cp_type']) {
                $dc = floor(($send_cost * ($cp['cp_price'] / 100)) / $cp['cp_trunc']) * $cp['cp_trunc'];
            } else {
                $dc = $cp['cp_price'];
            }

            if($cp['cp_maximum'] && $dc > $cp['cp_maximum'])
                $dc = $cp['cp_maximum'];

            if($dc > $send_cost)
                $dc = $send_cost;

            $tot_sc_cp_price = $dc;
        }
    }
}

if ((int)($send_cost - $tot_sc_cp_price) !== (int)($i_send_cost - $i_send_coupon)) {
    if(function_exists('add_order_post_log')) add_order_post_log('배송비 최종 계산 Error..');
    die("Error..");
}

// 추가배송비가 상이함
$od_b_zip   = preg_replace('/[^0-9]/', '', $od_b_zip);
$od_b_zip1  = substr($od_b_zip, 0, 3);
$od_b_zip2  = substr($od_b_zip, 3);
$zipcode = $od_b_zip1 . $od_b_zip2;
$sql = " select sc_id, sc_price from {$g5['g5_shop_sendcost_table']} where sc_zip1 <= '$zipcode' and sc_zip2 >= '$zipcode' ";
$tmp = sql_fetch($sql);
if(! (isset($tmp['sc_id']) && $tmp['sc_id']))
    $send_cost2 = 0;
else
    $send_cost2 = (int)$tmp['sc_price'];

if($send_cost2 !== $i_send_cost2) {
    if(function_exists('add_order_post_log')) add_order_post_log('추가배송비 최종 계산 Error...');
    die("Error...");
}

// 결제포인트가 상이함
// 회원이면서 포인트사용이면
$temp_point = 0;
if ($is_member && $config['cf_use_point'])
{
    if($member['mb_point'] >= $default['de_settle_min_point']) {
        $temp_point = (int)$default['de_settle_max_point'];

        if($temp_point > (int)$tot_od_price)
            $temp_point = (int)$tot_od_price;

        if($temp_point > (int)$member['mb_point'])
            $temp_point = (int)$member['mb_point'];

        $point_unit = (int)$default['de_settle_point_unit'];
        $temp_point = (int)((int)($temp_point / $point_unit) * $point_unit);
    }
}

if (($i_temp_point > (int)$temp_point || $i_temp_point < 0) && $config['cf_use_point']) {
    if(function_exists('add_order_post_log')) add_order_post_log('포인트 최종 계산 Error....');
    die("Error....");
}

if ($od_temp_point)
{
    if ($member['mb_point'] < $od_temp_point) {
        if(function_exists('add_order_post_log')) add_order_post_log('회원님의 포인트가 부족하여 포인트로 결제 할 수 없습니다.');
        alert('회원님의 포인트가 부족하여 포인트로 결제 할 수 없습니다.', $page_return_url);
    }
}

$i_price = $i_price + $i_send_cost + $i_send_cost2 - $i_temp_point - $i_send_coupon;
$order_price = $tot_od_price + $send_cost + $send_cost2 - $tot_sc_cp_price - $od_temp_point;

$od_status = '주문';
$od_tno    = '';

if (function_exists('check_payment_method')) {
    check_payment_method($od_settle_case);
}

if ($od_settle_case == "무통장")
{
    $od_receipt_point   = $i_temp_point;
    $od_receipt_price   = 0;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0) {
        $od_status      = '입금';
        $od_receipt_time = G5_TIME_YMDHIS;
    }
    $tno = $od_receipt_time = $od_app_no = '';
}
else if ($od_settle_case == "계좌이체")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_MSHOP_PATH.'/inicis/pay_result.php';
            break;
        case 'nicepay':
            include G5_MSHOP_PATH.'/nicepay/nicepay_result.php';
            break;
        default:
            include G5_MSHOP_PATH.'/kcp/pp_ax_hub.php';
            $bank_name  = iconv("cp949", "utf-8", $bank_name);
            break;
    }

    $od_tno             = $tno;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_deposit_name    = $od_name;
    $od_bank_account    = $bank_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "가상계좌")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_MSHOP_PATH.'/inicis/pay_result.php';
            break;
        case 'nicepay':
            include G5_MSHOP_PATH.'/nicepay/nicepay_result.php';
            break;
        default:
            include G5_MSHOP_PATH.'/kcp/pp_ax_hub.php';
            $bankname   = iconv("cp949", "utf-8", $bankname);
            $depositor  = iconv("cp949", "utf-8", $depositor);
            break;
    }

    $od_receipt_point   = $i_temp_point;
    $od_tno             = $tno;
    $od_app_no          = $app_no;
    $od_receipt_price   = 0;
    $od_bank_account    = $bankname.' '.$account;
    $od_deposit_name    = $depositor;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    $od_receipt_time    = '';
}
else if ($od_settle_case == "휴대폰")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_MSHOP_PATH.'/inicis/pay_result.php';
            break;
        case 'nicepay':
            include G5_MSHOP_PATH.'/nicepay/nicepay_result.php';
            break;
        default:
            include G5_MSHOP_PATH.'/kcp/pp_ax_hub.php';
            break;
    }

    $od_tno             = $tno;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $commid.' '.$mobile_no;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "신용카드")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_MSHOP_PATH.'/inicis/pay_result.php';
            break;
        case 'nicepay':
            include G5_MSHOP_PATH.'/nicepay/nicepay_result.php';
            break;
        default:
            include G5_MSHOP_PATH.'/kcp/pp_ax_hub.php';
            $card_name  = iconv("cp949", "utf-8", $card_name);
            break;
    }

    $od_tno             = $tno;
    $od_app_no          = $app_no;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $card_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "간편결제")
{
    switch($default['de_pg_service']) {
        case 'lg':
            include G5_SHOP_PATH.'/lg/xpay_result.php';
            break;
        case 'inicis':
            include G5_MSHOP_PATH.'/inicis/pay_result.php';
            break;
        case 'nicepay':
            include G5_MSHOP_PATH.'/nicepay/nicepay_result.php';
            break;
        default:
            include G5_MSHOP_PATH.'/kcp/pp_ax_hub.php';
            $card_name  = iconv("cp949", "utf-8", $card_name);
            break;
    }

    $od_tno             = $tno;
    $od_app_no          = $app_no;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $card_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ( is_inicis_order_pay($od_settle_case) )    //이니시스의 삼성페이 또는 L.pay 또는 이니시스 카카오페이
{
    // 이니시스에서만 지원
    include G5_MSHOP_PATH.'/inicis/pay_result.php';

    $od_tno             = $tno;
    $od_app_no          = $app_no;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $card_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else if ($od_settle_case == "KAKAOPAY")
{
    include G5_SHOP_PATH.'/kakaopay/kakaopay_result.php';

    $od_tno             = $tno;
    $od_app_no          = $app_no;
    $od_receipt_price   = $amount;
    $od_receipt_point   = $i_temp_point;
    $od_receipt_time    = preg_replace("/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/", "\\1-\\2-\\3 \\4:\\5:\\6", $app_time);
    $od_bank_account    = $card_name;
    $pg_price           = $amount;
    $od_misu            = $i_price - $od_receipt_price;
    if($od_misu == 0)
        $od_status      = '입금';
}
else
{
    die("od_settle_case Error!!!");
}

$od_pg = $default['de_pg_service'];
if($od_settle_case == 'KAKAOPAY')
    $od_pg = 'KAKAOPAY';

// 주문금액과 결제금액이 일치하는지 체크
if($tno) {
    if((int)$order_price !== (int)$pg_price) {
        $cancel_msg = '결제금액 불일치';
        switch($od_pg) {
            case 'lg':
                include G5_SHOP_PATH.'/lg/xpay_cancel.php';
                break;
            case 'inicis':
                include G5_SHOP_PATH.'/inicis/inipay_cancel.php';
                break;
            case 'nicepay':
                $cancelAmt = (int)$pg_price;
                include G5_SHOP_PATH.'/nicepay/cancel_process.php';
                break;
            case 'KAKAOPAY':
                $_REQUEST['TID']               = $tno;
                $_REQUEST['Amt']               = $amount;
                $_REQUEST['CancelMsg']         = $cancel_msg;
                $_REQUEST['PartialCancelCode'] = 0;
                include G5_SHOP_PATH.'/kakaopay/kakaopay_cancel.php';
                break;
            default:
                include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
                break;
        }
        
        if(function_exists('add_order_post_log')) add_order_post_log($cancel_msg);
        die("Receipt Amount Error");
    }
}

if ($is_member) {
    $od_pwd = $member['mb_password'];
} else {
    $post_od_pwd = isset($_POST['od_pwd']) ? $_POST['od_pwd'] : sha1(rand());
    $od_pwd = get_encrypt_string($_POST['od_pwd']);
}

// 주문번호를 얻는다.
$od_id = get_session('ss_order_id');

if( !$od_id ){
    if(function_exists('add_order_post_log')) add_order_post_log('주문번호가 없습니다.');
    die("주문번호가 없습니다.");
}

$od_escrow = 0;
if(isset($escw_yn) && $escw_yn == 'Y')
    $od_escrow = 1;

// 복합과세 금액
$od_tax_mny = round($i_price / 1.1);
$od_vat_mny = $i_price - $od_tax_mny;
$od_free_mny = 0;
if($default['de_tax_flag_use']) {
    $od_tax_mny = isset($_POST['comm_tax_mny']) ? (int) $_POST['comm_tax_mny'] : 0;
    $od_vat_mny = isset($_POST['comm_vat_mny']) ? (int) $_POST['comm_vat_mny'] : 0;
    $od_free_mny = isset($_POST['comm_free_mny']) ? (int) $_POST['comm_free_mny'] : 0;
}

$od_email         = get_email_address($od_email);
$od_name          = clean_xss_tags($od_name);
$od_tel           = clean_xss_tags($od_tel);
$od_hp            = clean_xss_tags($od_hp);
$od_zip           = preg_replace('/[^0-9]/', '', $od_zip);
$od_zip1          = substr($od_zip, 0, 3);
$od_zip2          = substr($od_zip, 3);
$od_addr1         = clean_xss_tags($od_addr1);
$od_addr2         = clean_xss_tags($od_addr2);
$od_addr3         = clean_xss_tags($od_addr3);
$od_addr_jibeon   = preg_match("/^(N|R)$/", $od_addr_jibeon) ? $od_addr_jibeon : '';
$od_b_name        = clean_xss_tags($od_b_name);
$od_b_tel         = clean_xss_tags($od_b_tel);
$od_b_hp          = clean_xss_tags($od_b_hp);
$od_b_addr1       = clean_xss_tags($od_b_addr1);
$od_b_addr2       = clean_xss_tags($od_b_addr2);
$od_b_addr3       = clean_xss_tags($od_b_addr3);
$od_b_addr_jibeon = preg_match("/^(N|R)$/", $od_b_addr_jibeon) ? $od_b_addr_jibeon : '';
$od_memo          = clean_xss_tags($od_memo, 0, 1, 0, 0);
$od_deposit_name  = clean_xss_tags($od_deposit_name);
$od_tax_flag      = $default['de_tax_flag_use'];

// 주문서에 입력
$sql = " insert {$g5['g5_shop_order_table']}
            set od_id             = '$od_id',
                mb_id             = '{$member['mb_id']}',
                od_pwd            = '$od_pwd',
                od_name           = '$od_name',
                od_email          = '$od_email',
                od_tel            = '$od_tel',
                od_hp             = '$od_hp',
                od_zip1           = '$od_zip1',
                od_zip2           = '$od_zip2',
                od_addr1          = '$od_addr1',
                od_addr2          = '$od_addr2',
                od_addr3          = '$od_addr3',
                od_addr_jibeon    = '$od_addr_jibeon',
                od_b_name         = '$od_b_name',
                od_b_tel          = '$od_b_tel',
                od_b_hp           = '$od_b_hp',
                od_b_zip1         = '$od_b_zip1',
                od_b_zip2         = '$od_b_zip2',
                od_b_addr1        = '$od_b_addr1',
                od_b_addr2        = '$od_b_addr2',
                od_b_addr3        = '$od_b_addr3',
                od_b_addr_jibeon  = '$od_b_addr_jibeon',
                od_deposit_name   = '$od_deposit_name',
                od_memo           = '$od_memo',
                od_cart_count     = '$cart_count',
                od_cart_price     = '$tot_ct_price',
                od_cart_coupon    = '$tot_it_cp_price',
                od_send_cost      = '$od_send_cost',
                od_send_coupon    = '$tot_sc_cp_price',
                od_send_cost2     = '$od_send_cost2',
                od_coupon         = '$tot_od_cp_price',
                od_receipt_price  = '$od_receipt_price',
                od_receipt_point  = '$od_receipt_point',
                od_bank_account   = '$od_bank_account',
                od_receipt_time   = '$od_receipt_time',
                od_misu           = '$od_misu',
                od_pg             = '$od_pg',
                od_tno            = '$od_tno',
                od_app_no         = '$od_app_no',
                od_escrow         = '$od_escrow',
                od_tax_flag       = '$od_tax_flag',
                od_tax_mny        = '$od_tax_mny',
                od_vat_mny        = '$od_vat_mny',
                od_free_mny       = '$od_free_mny',
                od_status         = '$od_status',
                od_shop_memo      = '',
                od_hope_date      = '$od_hope_date',
                od_time           = '".G5_TIME_YMDHIS."',
                od_mobile         = '1',
                od_ip             = '$REMOTE_ADDR',
                od_settle_case    = '$od_settle_case',
                od_other_pay_type = '$od_other_pay_type',
                od_test           = '{$default['de_card_test']}'
                ";
$result = sql_query($sql, false);

// 정말로 insert 가 되었는지 한번더 체크한다.
$exists_sql = "select od_id, od_tno, od_ip from {$g5['g5_shop_order_table']} where od_id = '$od_id'";
$exists_order = sql_fetch($exists_sql);

if(! $result && (isset($exists_order['od_id']) && $od_id && $exists_order['od_id'] === $od_id)) {
    if(isset($exists_order['od_tno']) && $exists_order['od_tno']){
        //이미 상품이 주문되었다면 리다이렉트
        exists_inicis_shop_order($od_id, array(), $exists_order['od_time'], $REMOTE_ADDR);
        goto_url(G5_SHOP_URL);
    }
}

// 주문정보 입력 오류시 결제 취소
if(! $result || ! (isset($exists_order['od_id']) && $od_id && $exists_order['od_id'] === $od_id)) {
    if($tno) {
        $cancel_msg = '주문정보 입력 오류 : '.$sql;
        switch($od_pg) {
            case 'lg':
                include G5_SHOP_PATH.'/lg/xpay_cancel.php';
                break;
            case 'inicis':
                include G5_SHOP_PATH.'/inicis/inipay_cancel.php';
                break;
            case 'KAKAOPAY':
                $_REQUEST['TID']               = $tno;
                $_REQUEST['Amt']               = $amount;
                $_REQUEST['CancelMsg']         = $cancel_msg;
                $_REQUEST['PartialCancelCode'] = 0;
                include G5_SHOP_PATH.'/kakaopay/kakaopay_cancel.php';
                break;
            default:
                include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
                break;
        }
    }

    // 관리자에게 오류 알림 메일발송
    $error = 'order';
    include G5_SHOP_PATH.'/ordererrormail.php';

    if(function_exists('add_order_post_log')) add_order_post_log($cancel_msg);
    // 주문삭제
    sql_query(" delete from {$g5['g5_shop_order_table']} where od_id = '$od_id' ", false);

    die('<p>고객님의 주문 정보를 처리하는 중 오류가 발생해서 주문이 완료되지 않았습니다.</p><p>'.strtoupper($od_pg).'를 이용한 전자결제(신용카드, 계좌이체, 가상계좌 등)은 자동 취소되었습니다.');
}

// 장바구니 상태변경
// 신용카드로 주문하면서 신용카드 포인트 사용하지 않는다면 포인트 부여하지 않음
$cart_status = $od_status;
$sql_card_point = "";
if ($od_receipt_price > 0 && !$default['de_card_point']) {
    $sql_card_point = " , ct_point = '0' ";
}

// 회원 아이디 값 변경
$sql_mb_id = "";
if ($is_member) {
    $sql_mb_id = " , mb_id = '{$member['mb_id']}' ";
}

$sql = "update {$g5['g5_shop_cart_table']}
           set od_id = '$od_id',
               ct_status = '$cart_status'
               $sql_card_point
               $sql_mb_id
         where od_id = '$tmp_cart_id'
           and ct_select = '1' ";
$result = sql_query($sql, false);

// 주문정보 입력 오류시 결제 취소
if(!$result) {
    if($tno) {
        $cancel_msg = '주문상태 변경 오류';
        switch($od_pg) {
            case 'lg':
                include G5_SHOP_PATH.'/lg/xpay_cancel.php';
                break;
            case 'inicis':
                include G5_SHOP_PATH.'/inicis/inipay_cancel.php';
                break;
            case 'KAKAOPAY':
                $_REQUEST['TID']               = $tno;
                $_REQUEST['Amt']               = $amount;
                $_REQUEST['CancelMsg']         = $cancel_msg;
                $_REQUEST['PartialCancelCode'] = 0;
                include G5_SHOP_PATH.'/kakaopay/kakaopay_cancel.php';
                break;
            default:
                include G5_SHOP_PATH.'/kcp/pp_ax_hub_cancel.php';
                break;
        }
    }

    // 관리자에게 오류 알림 메일발송
    $error = 'status';
    include G5_SHOP_PATH.'/ordererrormail.php';

    if(function_exists('add_order_post_log')) add_order_post_log($cancel_msg);
    // 주문삭제
    sql_query(" delete from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");

    die('<p>고객님의 주문 정보를 처리하는 중 오류가 발생해서 주문이 완료되지 않았습니다.</p><p>'.strtoupper($od_pg).'를 이용한 전자결제(신용카드, 계좌이체, 가상계좌 등)은 자동 취소되었습니다.');
}

// 회원이면서 포인트를 사용했다면 포인트 테이블에 사용을 추가
if ($is_member && $od_receipt_point)
    insert_point($member['mb_id'], (-1) * $od_receipt_point, "주문번호 $od_id 결제");

$od_memo = nl2br(htmlspecialchars2(stripslashes($od_memo))) . "&nbsp;";

// 쿠폰사용내역기록
if($is_member) {
    $it_cp_cnt = (isset($_POST['cp_id']) && is_array($_POST['cp_id'])) ? count($_POST['cp_id']) : 0;
    for($i=0; $i<$it_cp_cnt; $i++) {
        $cid = isset($_POST['cp_id'][$i]) ? clean_xss_tags($_POST['cp_id'][$i], 1, 1) : '';
        $cp_it_id = isset($_POST['it_id'][$i]) ? safe_replace_regex($_POST['it_id'][$i], 'it_id') : '';
        $cp_prc = isset($arr_it_cp_prc[$cp_it_id]) ? (int) $arr_it_cp_prc[$cp_it_id] : 0;

        if(trim($cid)) {
            $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                        set cp_id       = '$cid',
                            mb_id       = '{$member['mb_id']}',
                            od_id       = '$od_id',
                            cp_price    = '$cp_prc',
                            cl_datetime = '".G5_TIME_YMDHIS."' ";
            sql_query($sql);
        }

        // 쿠폰사용금액 cart에 기록
        $sql = " update {$g5['g5_shop_cart_table']}
                    set cp_price = '$cp_prc'
                    where od_id = '$od_id'
                      and it_id = '$cp_it_id'
                      and ct_select = '1'
                    order by ct_id asc
                    limit 1 ";
        sql_query($sql);
    }

    if(isset($_POST['od_cp_id']) && $_POST['od_cp_id']) {
        $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                    set cp_id       = '{$_POST['od_cp_id']}',
                        mb_id       = '{$member['mb_id']}',
                        od_id       = '$od_id',
                        cp_price    = '$tot_od_cp_price',
                        cl_datetime = '".G5_TIME_YMDHIS."' ";
        sql_query($sql);
    }

    if(isset($_POST['sc_cp_id']) && $_POST['sc_cp_id']) {
        $sql = " insert into {$g5['g5_shop_coupon_log_table']}
                    set cp_id       = '{$_POST['sc_cp_id']}',
                        mb_id       = '{$member['mb_id']}',
                        od_id       = '$od_id',
                        cp_price    = '$tot_sc_cp_price',
                        cl_datetime = '".G5_TIME_YMDHIS."' ";
        sql_query($sql);
    }
}


include_once(G5_SHOP_PATH.'/ordermail1.inc.php');
include_once(G5_SHOP_PATH.'/ordermail2.inc.php');

// SMS BEGIN --------------------------------------------------------
// 주문고객과 쇼핑몰관리자에게 SMS 전송
if($config['cf_sms_use'] && ($default['de_sms_use2'] || $default['de_sms_use3'])) {
    $is_sms_send = (function_exists('is_sms_send')) ? is_sms_send('orderformupdate') : false;

    if($is_sms_send) {
        $sms_contents = array($default['de_sms_cont2'], $default['de_sms_cont3']);
        $recv_numbers = array($od_hp, $default['de_sms_hp']);
        $send_numbers = array($default['de_admin_company_tel'], $default['de_admin_company_tel']);

        $sms_count = 0;
        $sms_messages = array();

        for($s=0; $s<count($sms_contents); $s++) {
            $sms_content = $sms_contents[$s];
            $recv_number = preg_replace("/[^0-9]/", "", $recv_numbers[$s]);
            $send_number = preg_replace("/[^0-9]/", "", $send_numbers[$s]);

            $sms_content = str_replace("{이름}", $od_name, $sms_content);
            $sms_content = str_replace("{보낸분}", $od_name, $sms_content);
            $sms_content = str_replace("{받는분}", $od_b_name, $sms_content);
            $sms_content = str_replace("{주문번호}", $od_id, $sms_content);
            $sms_content = str_replace("{주문금액}", number_format($tot_ct_price + $od_send_cost + (int) $od_send_cost2), $sms_content);
            $sms_content = str_replace("{회원아이디}", $member['mb_id'], $sms_content);
            $sms_content = str_replace("{회사명}", $default['de_admin_company_name'], $sms_content);

            $idx = 'de_sms_use'.($s + 2);

            if($default[$idx] && $recv_number) {
                $sms_messages[] = array('recv' => $recv_number, 'send' => $send_number, 'cont' => $sms_content);
                $sms_count++;
            }
        }

        // 무통장 입금 때 고객에게 계좌정보 보냄
        if($od_settle_case == '무통장' && $default['de_sms_use2'] && $od_misu > 0) {
            $sms_content = $od_name."님의 입금계좌입니다.\n금액:".number_format($od_misu)."원\n계좌:".$od_bank_account."\n".$default['de_admin_company_name'];

            $recv_number = preg_replace("/[^0-9]/", "", $od_hp);
            $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']);

            $sms_messages[] = array('recv' => $recv_number, 'send' => $send_number, 'cont' => $sms_content);
            $sms_count++;
        }

        // SMS 전송
        if($sms_count > 0) {
            if($config['cf_sms_type'] == 'LMS') {
                include_once(G5_LIB_PATH.'/icode.lms.lib.php');

                $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

                // SMS 모듈 클래스 생성
                if($port_setting !== false) {
                    $SMS = new LMS;
                    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                    for($s=0; $s<count($sms_messages); $s++) {
                        $strDest     = array();
                        $strDest[]   = $sms_messages[$s]['recv'];
                        $strCallBack = $sms_messages[$s]['send'];
                        $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                        $strSubject  = '';
                        $strURL      = '';
                        $strData     = iconv_euckr($sms_messages[$s]['cont']);
                        $strDate     = '';
                        $nCount      = count($strDest);

                        $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                        $SMS->Send();
                        $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
                    }
                }
            } else {
                include_once(G5_LIB_PATH.'/icode.sms.lib.php');

                $SMS = new SMS; // SMS 연결
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);

                for($s=0; $s<count($sms_messages); $s++) {
                    $recv_number = $sms_messages[$s]['recv'];
                    $send_number = $sms_messages[$s]['send'];
                    $sms_content = iconv_euckr($sms_messages[$s]['cont']);

                    $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], $sms_content, "");
                }

                $SMS->Send();
                $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }
        }
    }
}
// SMS END   --------------------------------------------------------


// orderview 에서 사용하기 위해 session에 넣고
$uid = md5($od_id.G5_TIME_YMDHIS.$REMOTE_ADDR);
set_session('ss_orderview_uid', $uid);

// 주문 정보 임시 데이터 삭제
$sql = " delete from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' and dt_pg = '$od_pg' ";
sql_query($sql);

if( $od_pg == 'inicis' && $od_tno ){
    $sql = "delete from {$g5['g5_shop_inicis_log_table']} where oid = '$od_id' and P_TID = '$od_tno' ";
    sql_query($sql, false);
}

if(function_exists('add_order_post_log')) add_order_post_log('', 'delete');

// 주문번호제거
set_session('ss_order_id', '');

// 기존자료 세션에서 제거
if (get_session('ss_direct'))
    set_session('ss_cart_direct', '');

// 배송지처리
if($is_member) {
    $sql = " select * from {$g5['g5_shop_order_address_table']}
                where mb_id = '{$member['mb_id']}'
                  and ad_name = '$od_b_name'
                  and ad_tel = '$od_b_tel'
                  and ad_hp = '$od_b_hp'
                  and ad_zip1 = '$od_b_zip1'
                  and ad_zip2 = '$od_b_zip2'
                  and ad_addr1 = '$od_b_addr1'
                  and ad_addr2 = '$od_b_addr2'
                  and ad_addr3 = '$od_b_addr3' ";
    $row = sql_fetch($sql);

    // 기본배송지 체크
    if($ad_default) {
        $sql = " update {$g5['g5_shop_order_address_table']}
                    set ad_default = '0'
                    where mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }

    $ad_subject = clean_xss_tags($ad_subject);

    if($row['ad_id']){
        $sql = " update {$g5['g5_shop_order_address_table']}
                      set ad_default = '$ad_default',
                          ad_subject = '$ad_subject',
                          ad_jibeon  = '$od_b_addr_jibeon'
                    where mb_id = '{$member['mb_id']}'
                      and ad_id = '{$row['ad_id']}' ";
    } else {
        $sql = " insert into {$g5['g5_shop_order_address_table']}
                    set mb_id       = '{$member['mb_id']}',
                        ad_subject  = '$ad_subject',
                        ad_default  = '$ad_default',
                        ad_name     = '$od_b_name',
                        ad_tel      = '$od_b_tel',
                        ad_hp       = '$od_b_hp',
                        ad_zip1     = '$od_b_zip1',
                        ad_zip2     = '$od_b_zip2',
                        ad_addr1    = '$od_b_addr1',
                        ad_addr2    = '$od_b_addr2',
                        ad_addr3    = '$od_b_addr3',
                        ad_jibeon   = '$od_b_addr_jibeon' ";
    }

    sql_query($sql);
}

$is_noti_pay = isset($is_noti_pay) ? $is_noti_pay : false;

if( $is_noti_pay ){
    $order_id = $od_id;
    return;
}

goto_url(G5_SHOP_URL.'/orderinquiryview.php?od_id='.$od_id.'&amp;uid='.$uid);
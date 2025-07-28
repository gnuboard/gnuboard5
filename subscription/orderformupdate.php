<?php
include_once './_common.php';
include_once G5_LIB_PATH . '/mailer.lib.php';

// 정기구독은 회원만 구독이 가능합니다.
if (!$is_member) {
    alert('정기구독은 로그인이 필요합니다.', G5_SHOP_URL);
}

$od_subscription_select_data = isset($_POST['od_subscription_select_data']) ? $_POST['od_subscription_select_data'] : '';
$od_subscription_select_number = isset($_POST['od_subscription_select_number']) ? $_POST['od_subscription_select_number'] : '';
$od_select_card_number = isset($_POST['od_select_card_number']) ? preg_replace('/[^a-z0-9_\-]/i', '', $_POST['od_select_card_number']) : '';
$od_hope_date = isset($_POST['od_hope_date']) ? preg_replace('/[^0-9_\-]/i', '', $_POST['od_hope_date']) : '';

$is_enable_user_input = 0;

if (!$od_subscription_select_data) {
    alert('배송주기를 선택해 주세요.');
}

if (!$od_subscription_select_number) {
    alert('이용횟수를 선택해 주세요.');
}

// 사용자가 배송주기를 입력하는 단계일때는
if ($od_subscription_select_data && get_subs_option('su_chk_user_delivery') && ctype_digit($od_subscription_select_data)) {

    $is_enable_user_input = 1;

    // $subscription_selected_data = '0||'.$od_subscription_select_data.'||day';

    $subscription_selected_data = array(
        'opt_id' => 0,
        'opt_input' => $od_subscription_select_data,
        'opt_date_format' => 'day',
        'opt_etc' => '',
        'opt_print' => '',
        'opt_use' => 1
    );
} else {

    $arr_subs_data = explode('||', $od_subscription_select_data);

    $subscription_info_inputs = get_subscription_info_inputs();

    $key = $arr_subs_data[0];

    $subscription_selected_data = isset($subscription_info_inputs[$key]) ? $subscription_info_inputs[$key] : array();

    if (!($subscription_selected_data && $subscription_selected_data['opt_input'] == $arr_subs_data[1] && $subscription_selected_data['opt_date_format'] == $arr_subs_data[2])) {
        alert('선택한 데이터에 오류가 있습니다.');
    }
}

$arr_subs_number = explode('||', $od_subscription_select_number);

$subscription_use_inputs = get_subscription_use_inputs();

$key = $arr_subs_number[0];

$subscription_selected_number = isset($subscription_use_inputs[$key]) ? $subscription_use_inputs[$key] : array();

$card_ci_id = 0;

// 기존에 등록된 결제수단으로 결제가 맞으면
if ($od_select_card_number === $od_settle_case) {

    $sql = "SELECT * 
            FROM {$g5['g5_subscription_mb_cardinfo_table']} 
            WHERE ci_id = '" . $od_select_card_number . "' 
            AND mb_id = '" . $member['mb_id'] . "' 
            AND pg_service = '" . get_subs_option('su_pg_service') . "'";
    $select_before_od = sql_fetch($sql);

    if (!($select_before_od && $select_before_od['card_billkey'])) {
        alert("등록된 결제수단에 문제가 있어서 신청이 불가합니다.");
    }

    $od_settle_case = '카드재사용';

    $card_ci_id = $od_select_card_number;
}

if (function_exists('add_order_post_log')) {
    add_order_post_log('init', 'init');
}

// 장바구니가 비어있는가?
if (get_session('subs_direct')) {
    $tmp_cart_id = get_session('subs_cart_direct');
} else {
    $tmp_cart_id = get_session('subs_cart_id');
}

if (get_subscription_cart_count($tmp_cart_id) == 0) {    // 장바구니에 담기
    if (function_exists('add_order_post_log')) {
        add_order_post_log('장바구니가 비어 있습니다.');
    }

    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SUBSCRIPTION_URL . '/cart.php');
}

$od_pg = get_subs_option('su_pg_service');

// 변수 초기화
$card_mask_number = '';
$card_billkey = '';
$od_other_pay_type = '';

$od_hope_date = isset($_POST['od_hope_date']) ? clean_xss_tags($_POST['od_hope_date'], 1, 1) : '';
$ad_default = !empty($_POST['ad_default']) ? (int) $_POST['ad_default'] : 0;

$error = '';

// 정기결제 관련
$od_subscription_number = '';
$od_firstshipment_date = '';
$od_subscription_date_format = '';

// 장바구니 상품 재고 검사
$sql = " select it_id,
                ct_qty,
                it_name,
                io_id,
                io_type,
                ct_option,
                ct_subscription_number,
                ct_firstshipment_date,
                ct_date_format
           from {$g5['g5_subscription_cart_table']}
          where od_id = '$tmp_cart_id'
            and ct_select = '1' ";
$result = sql_query($sql);
for ($i = 0; $row = sql_fetch_array($result); ++$i) {
    // 상품에 대한 현재고수량
    if ($row['io_id']) {
        $it_stock_qty = (int) get_subscription_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);
    } else {
        $it_stock_qty = (int) get_subscription_it_stock_qty($row['it_id']);
    }
    // 장바구니 수량이 재고수량보다 많다면 오류
    if ($row['ct_qty'] > $it_stock_qty) {
        $error .= "{$row['ct_option']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
    }

    // 정기결제 관련
    $od_subscription_number = $row['ct_subscription_number'];
    $od_firstshipment_date = $row['ct_firstshipment_date'];
    $od_subscription_date_format = $row['ct_date_format'];
}

if ($i == 0) {
    if (function_exists('add_order_post_log')) {
        add_order_post_log('장바구니가 비어 있습니다.');
    }

    alert('장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.', G5_SUBSCRIPTION_URL . '/cart.php');
}

if ($error != '') {
    $error .= "다른 고객님께서 {$od_name}님 보다 먼저 주문하신 경우입니다. 불편을 끼쳐 죄송합니다.";
    if (function_exists('add_order_post_log')) {
        add_order_post_log($error);
    }
    alert($error);
}

$i_price = isset($_POST['od_price']) ? (int) $_POST['od_price'] : 0;
$i_send_cost = isset($_POST['od_send_cost']) ? (int) $_POST['od_send_cost'] : 0;
$i_send_cost2 = isset($_POST['od_send_cost2']) ? (int) $_POST['od_send_cost2'] : 0;
$i_send_coupon = isset($_POST['od_send_coupon']) ? abs((int) $_POST['od_send_coupon']) : 0;
$i_temp_point = isset($_POST['od_temp_point']) ? (int) $_POST['od_temp_point'] : 0;

// 주문금액이 상이함
$sql = " select SUM(IF(io_type = 1, (io_price * ct_qty), ((ct_price + io_price) * ct_qty))) as od_price,
              COUNT(distinct it_id) as cart_count
            from {$g5['g5_subscription_cart_table']} where od_id = '$tmp_cart_id' and ct_select = '1' ";
$row = sql_fetch($sql);
$tot_ct_price = $row['od_price'];
$cart_count = $row['cart_count'];
$tot_od_price = $tot_ct_price;

// 쿠폰과 배송비 변수
$tot_it_cp_price = 0;
$tot_od_cp_price = 0;
$tot_sc_cp_price = 0;

// 배송비가 상이함
$send_cost = get_subscription_sendcost($tmp_cart_id);

$tot_sc_cp_price = 0;
// 배송비 쿠폰 적용해야 해야함

$send_cost2 = 0;


// 추가배송비가 상이함
$od_b_zip   = preg_replace('/[^0-9]/', '', $od_b_zip);
$od_b_zip1  = substr($od_b_zip, 0, 3);
$od_b_zip2  = substr($od_b_zip, 3);
$zipcode = $od_b_zip;
$sql = " select sc_id, sc_price from {$g5['g5_shop_sendcost_table']} where sc_zip1 <= '$zipcode' and sc_zip2 >= '$zipcode' ";
$tmp = sql_fetch($sql);
if (! (isset($tmp['sc_id']) && $tmp['sc_id']))
    $send_cost2 = 0;
else
    $send_cost2 = (int)$tmp['sc_price'];

if ($send_cost2 !== $i_send_cost2) {
    if (function_exists('add_order_post_log')) add_order_post_log('추가배송비 최종 계산 Error...');
    die("Error...");
}

$i_price = $i_price + $i_send_cost + $i_send_cost2 - $i_send_coupon;
$order_price = $tot_od_price + $send_cost + $send_cost2 - $tot_sc_cp_price;

$od_status = '주문';
$od_tno = '';

check_subscription_pay_method($od_settle_case);

// 변수 초기화
$od_card_name = '';

$pg_receipt_infos = array(
    'od_cash' => '',
    'od_cash_no' => '',
    'od_cash_info' => ''
);

if ($od_settle_case == '카드재사용') {
    $od_receipt_point = $i_temp_point;
    $od_receipt_price = 0;
    $od_misu = $i_price - $od_receipt_price;

    $card_mask_number = $select_before_od['card_mask_number'];
    $card_billkey = $select_before_od['card_billkey'];
    $od_subscription_date_format = isset($select_before_od['od_subscription_date_format']) ? $select_before_od['od_subscription_date_format'] : '';
    $od_subscription_selected_data = isset($select_before_od['od_subscription_selected_data']) ? $select_before_od['od_subscription_selected_data'] : '';
    $od_card_name = $select_before_od['od_card_name'];

    // 구독 등록될 가격
    $od_receipt_price = $order_price;
    $od_subscription_date_format = '';
    $od_firstshipment_date = '';
} elseif ($od_settle_case == '신용카드') {
    switch ($od_pg) {
        case 'inicis':
            include G5_SUBSCRIPTION_PATH . '/inicis/inicis_bill_result.php';
            break;
        case 'tosspayments':
            include G5_SUBSCRIPTION_PATH . '/tosspayments/tosspayments_bill_result.php';
            break;
        case 'nicepay':
            include G5_SUBSCRIPTION_PATH . '/nicepay/nicepay_subscription_result.php';
            break;
        case 'kcp':
        default:
            // include G5_SUBSCRIPTION_PATH.'/kcp/kcp_api_batch_key_req.php';
            include G5_SUBSCRIPTION_PATH . '/kcp/pp_cli_hub.php';
            break;
    }

    $od_tno = $tno;
    $od_receipt_price = $amount;
    $od_receipt_point = $i_temp_point;
    // $od_receipt_time = preg_replace('/([0-9]{4})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})([0-9]{2})/', '\\1-\\2-\\3 \\4:\\5:\\6', $app_time);
    $od_card_name = $card_name;
    $pg_price = $amount;
    $od_misu = $i_price - $od_receipt_price;
} else {
    exit('od_settle_case Error!!!');
}

$tno = isset($tno) ? $tno : '';

// 주문금액과 결제금액이 일치하는지 체크
if ($tno) {
    if ((int) $order_price !== (int) $pg_price) {
        $cancel_msg = '결제금액 불일치';
        switch ($od_pg) {
            case 'inicis':
                include G5_SUBSCRIPTION_PATH . '/inicis/inipay_cancel.php';
                break;
            case 'tosspayments':
                include G5_SUBSCRIPTION_PATH . '/tosspayments/tosspayments_cancel.php';
                break;
            case 'nicepay':
                $cancelAmt = (int) $pg_price;
                include G5_SUBSCRIPTION_PATH . '/nicepay/cancel_process.php';
                break;
            case 'kcp':
            default:
                include G5_SUBSCRIPTION_PATH . '/kcp/pp_ax_hub_cancel.php';
                break;
        }

        if (function_exists('add_order_post_log')) {
            add_order_post_log($cancel_msg);
        }
        exit('Receipt Amount Error');
    }
}

// 복합과세 금액
$od_tax_mny = round($i_price / 1.1);
$od_vat_mny = $i_price - $od_tax_mny;
$od_free_mny = 0;

if (get_subs_option('su_tax_flag_use')) {
    $od_tax_mny = isset($_POST['comm_tax_mny']) ? (int) $_POST['comm_tax_mny'] : 0;
    $od_vat_mny = isset($_POST['comm_vat_mny']) ? (int) $_POST['comm_vat_mny'] : 0;
    $od_free_mny = isset($_POST['comm_free_mny']) ? (int) $_POST['comm_free_mny'] : 0;
}

// 주문번호를 얻는다.
$od_id = get_session('subs_order_id');

$od_email = get_email_address($od_email);
$od_name = clean_xss_tags($od_name);
$od_tel = clean_xss_tags($od_tel);
$od_hp = clean_xss_tags($od_hp);
$od_zip = preg_replace('/[^0-9]/', '', $od_zip);
$od_zip1 = substr($od_zip, 0, 3);
$od_zip2 = substr($od_zip, 3);
$od_addr1 = clean_xss_tags($od_addr1);
$od_addr2 = clean_xss_tags($od_addr2);
$od_addr3 = clean_xss_tags($od_addr3);
$od_addr_jibeon = preg_match('/^(N|R)$/', $od_addr_jibeon) ? $od_addr_jibeon : '';
$od_b_name = clean_xss_tags($od_b_name);
$od_b_tel = clean_xss_tags($od_b_tel);
$od_b_hp = clean_xss_tags($od_b_hp);
$od_b_addr1 = clean_xss_tags($od_b_addr1);
$od_b_addr2 = clean_xss_tags($od_b_addr2);
$od_b_addr3 = clean_xss_tags($od_b_addr3);
$od_b_addr_jibeon = preg_match('/^(N|R)$/', $od_b_addr_jibeon) ? $od_b_addr_jibeon : '';
$od_memo = clean_xss_tags($od_memo, 1, 1, 0, 0);
// $od_deposit_name = clean_xss_tags($od_deposit_name);     // 정기결제에서 사용안함
$od_tax_flag      = get_subs_option('su_tax_flag_use');

// 회원 카드정보 테이블에 등록
$sql = "SELECT card_billkey 
        FROM {$g5['g5_subscription_mb_cardinfo_table']} 
        WHERE card_billkey = '" . $card_billkey . "'";
$exist_card = sql_fetch($sql);

if (!(isset($exist_card['card_billkey']) && $exist_card['card_billkey'])) {

    $sql = "INSERT INTO {$g5['g5_subscription_mb_cardinfo_table']} 
            (mb_id, pg_service, pg_id, pg_apikey, first_ordernumber, card_mask_number, card_billkey, od_card_name, od_tno, od_test) 
            VALUES (
                '" . $member['mb_id'] . "', 
                '" . $od_pg . "', 
                '" . get_subscription_pg_id() . "', 
                '" . get_subscription_pg_apikey() . "', 
                '" . $od_id . "', 
                '" . $card_mask_number . "', 
                '" . $card_billkey . "', 
                '" . $od_card_name . "', 
                '" . $od_tno . "', 
                '" . get_subs_option('su_card_test') . "'
            )";
    sql_query($sql);

    $card_ci_id = sql_insert_id();
}

$lead_days = get_subs_option('su_auto_payment_lead_days') ? (int) get_subs_option('su_auto_payment_lead_days') : 0;

// 주 또는 월 단위에 요일이나 날이 지정되어 있는 경우
if (isset($subscription_selected_data['opt_etc']) && $subscription_selected_data['opt_etc']) {
    $next_delivery_date = getIntervalBasedNextDate(G5_SERVER_TIME, $subscription_selected_data, $subscription_selected_number, 1);
    $nextBillingDate = getNextPaymentDate($next_delivery_date, $lead_days);
} else {
    // 바로 1회차 결제 되게 한다.
    $nextBillingDate = G5_TIME_YMDHIS;
    // 배송 예정일은 영업일 이후로 한다.
    $next_delivery_date = $lead_days ? getBusinessDaysNext(date('Y-m-d H:i:s', strtotime("+$lead_days days", strtotime(G5_TIME_YMDHIS)))) : G5_TIME_YMDHIS;
}

if ($od_settle_case === '카드재사용') {
    $od_settle_case = '신용카드';
}

$sql = "INSERT INTO {$g5['g5_subscription_order_table']} 
        (
            od_id, 
            mb_id, 
            ci_id, 
            od_name, 
            od_email, 
            od_tel, 
            od_hp, 
            od_zip,
            od_addr1, 
            od_addr2, 
            od_addr3, 
            od_addr_jibeon, 
            od_b_name, 
            od_b_tel, 
            od_b_hp, 
            od_b_zip,
            od_b_addr1, 
            od_b_addr2, 
            od_b_addr3, 
            od_b_addr_jibeon, 
            od_memo, 
            od_cart_count, 
            od_cart_price, 
            od_cart_coupon, 
            od_send_cost, 
            od_send_coupon, 
            od_send_cost2, 
            od_coupon, 
            od_receipt_price, 
            od_receipt_point,
            od_pg, 
            od_tno, 
            od_tax_flag, 
            od_tax_mny, 
            od_vat_mny, 
            od_free_mny, 
            od_subscription_memo, 
            od_hope_date, 
            od_time, 
            od_ip, 
            od_settle_case, 
            od_other_pay_type, 
            od_test, 
            od_subscription_number, 
            od_firstshipment_date, 
            od_subscription_date_format, 
            od_subscription_selected_data, 
            od_subscription_selected_number, 
            is_enable_user_input,
            next_billing_date,
            next_delivery_date
        ) 
        VALUES (
            '$od_id', 
            '" . $member['mb_id'] . "', 
            '$card_ci_id', 
            '$od_name', 
            '$od_email', 
            '$od_tel', 
            '$od_hp', 
            '$od_zip',
            '$od_addr1', 
            '$od_addr2', 
            '$od_addr3', 
            '$od_addr_jibeon', 
            '$od_b_name', 
            '$od_b_tel', 
            '$od_b_hp', 
            '$od_b_zip',
            '$od_b_addr1', 
            '$od_b_addr2', 
            '$od_b_addr3', 
            '$od_b_addr_jibeon', 
            '$od_memo', 
            '$cart_count', 
            '$tot_ct_price', 
            '$tot_it_cp_price', 
            '$od_send_cost', 
            '$tot_sc_cp_price', 
            '$od_send_cost2', 
            '$tot_od_cp_price', 
            '$od_receipt_price', 
            '$od_receipt_point', 
            '$od_pg', 
            '$od_tno', 
            '$od_tax_flag', 
            '$od_tax_mny', 
            '$od_vat_mny', 
            '$od_free_mny', 
            '', 
            '$od_hope_date', 
            '" . G5_TIME_YMDHIS . "', 
            '" . $_SERVER['REMOTE_ADDR'] . "', 
            '$od_settle_case', 
            '$od_other_pay_type', 
            '" . get_subs_option('su_card_test') . "', 
            '$od_subscription_number', 
            '$od_firstshipment_date', 
            '$od_subscription_date_format', 
            '" . base64_encode(serialize($subscription_selected_data)) . "', 
            '" . base64_encode(serialize($subscription_selected_number)) . "', 
            '$is_enable_user_input',
            '". $nextBillingDate . "',
            '". $next_delivery_date . "'
        )";

$result = sql_query($sql, false);

// 정말로 insert 가 되었는지 한번더 체크한다.
$exists_sql = "select * from {$g5['g5_subscription_order_table']} where od_id = '$od_id'";
$exists_order = sql_fetch($exists_sql);

// 주문정보 입력 오류시 결제 취소
if (!$result || !(isset($exists_order['od_id']) && $od_id && $exists_order['od_id'] === $od_id)) {
    // 관리자에게 오류 알림 메일발송
    $error = 'order';
    include G5_SUBSCRIPTION_PATH.'/mail/subscription_ordererrormail.php';

    run_event('fail_insert_susbscription', $od_id);
    die('<p>고객님의 주문 정보를 처리하는 중 오류가 발생해서 주문이 완료되지 않았습니다.</p><p>정기구독은 자동 취소되었습니다.');
}

// 장바구니 상태변경
// 신용카드로 주문하면서 신용카드 포인트 사용하지 않는다면 포인트 부여하지 않음
$cart_status = $od_status;
$sql_card_point = '';
if ($od_receipt_price > 0 && !$default['de_card_point']) {
    $sql_card_point = " , ct_point = '0' ";
}

// 회원 아이디 값 변경
$sql_mb_id = '';
if ($is_member) {
    $sql_mb_id = " , mb_id = '{$member['mb_id']}' ";
}

$sql = "update {$g5['g5_subscription_cart_table']}
           set od_id = '$od_id',
               ct_status = '$cart_status'
               $sql_card_point
               $sql_mb_id
         where od_id = '$tmp_cart_id'
           and ct_select = '1' ";
$result = sql_query($sql, false);

// 결제할 날짜가 오늘이거나 오늘보다 낮으면 1회차 결제
$is_first_pay = (date('Y-m-d', strtotime($nextBillingDate)) <= G5_TIME_YMD) ? true : false;
$is_pay_success = 0;
$pay_id = 0;

// 희망배송일과 배송일 이전 자동결제 설정일이 있으면 다시 계산한다.
if (get_subs_option('su_hope_date_use') && $od_hope_date) {
    $nextBillingDate = calculateNextBillingDate($exists_order, $od_hope_date);

    // 결제일이 오늘이거나 이전일이면 바로 1회차 결제한다.
    $current_time = strtotime(G5_TIME_YMDHIS);
    $compare_time = strtotime($nextBillingDate);

    if (date('Y-m-d', $current_time) === date('Y-m-d', $compare_time)) {
        $is_first_pay = true;
    }

    if ($compare_time <= $current_time) {
        $is_first_pay = true;
    }
}

if ($is_first_pay) {

    $pays = subscription_process_payment($exists_order, $od_pg);

    // 정기결제가 성공이면
    if ($pays && (isset($pays['code']) && $pays['code'] === 'success')) {

        $pay_round_no = (int) $exists_order['od_pays_total'] + 1;

        $insert_id = subscription_order_pay($exists_order, $pays['response'], $pay_round_no);

        // 성공이면
        if ($insert_id) {

            $nextBillingDate = calculateNextBillingDate($exists_order);
            
            // 1회차 결제가 성공이면, 2회차 결제예정일과 배송예정일을 업데이트 한다.
            $updateQuery = "UPDATE {$g5['g5_subscription_order_table']} 
                    SET next_billing_date = '$nextBillingDate', 
                        next_delivery_date = '". calculateNextDeliveryDate($exists_order) ."',
                        last_billed_date = '" . G5_TIME_YMDHIS . "', 
                        od_pays_total = '$pay_round_no' 
                    WHERE od_id = '$od_id'";

            sql_query($updateQuery, false);

            add_subscription_order_history('정기구독 1회차 결제가 완료되었습니다.', array(
                'hs_type' => 'subscription_order_success',
                'hs_category' => 'all',     // hs_category 가 all 이면 사용자와 관리자 둘다 보기 가능, admin 이면 관리자만 보기 가능
                'od_id' => $od_id,
                'mb_id' => $member['mb_id']
            ));
            
            // 첫회 결제가 성공
            $is_pay_success = 1;
            $pay_id = $insert_id;
            
        } else {
            // 실패시 처리

            add_subscription_order_history('정기구독 1회차 결제에 성공했으나, DB 기록이 실패했습니다.', array(
                'hs_type' => 'subscription_pay_db_fail',
                'hs_category' => 'all',     // hs_category 가 all 이면 사용자와 관리자 둘다 보기 가능, admin 이면 관리자만 보기 가능
                'od_id' => $od_id,
                'mb_id' => $member['mb_id']
            ));
        }
    } else {
        // 실패시 처리

        add_subscription_order_history('정기구독 1회차 결제에 실패했습니다. 코드 : ' . $pays['code'] . ' 이유 : ' . $pays['message'], array(
            'hs_type' => 'subscription_pay_pg_fail',
            'hs_category' => 'all',     // hs_category 가 all 이면 사용자와 관리자 둘다 보기 가능, admin 이면 관리자만 보기 가능
            'od_id' => $od_id,
            'mb_id' => $member['mb_id']
        ));
    }
}

$od_memo = nl2br(htmlspecialchars2(stripslashes($od_memo))) . '&nbsp;';

$od = $exists_order;

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

include_once G5_SUBSCRIPTION_PATH . '/ordermail1.inc.php';
include_once G5_SUBSCRIPTION_PATH . '/ordermail2.inc.php';

// 1회차가 오늘 결제일이고 1회차가 결제 되었다면 1회차가 결제 되었다고 메일을 보낸다.
if ($is_pay_success) {
    
    $pay = get_subscription_pay($pay_id);
    
    if (isset($pay['pay_id']) && $pay['pay_id']) {
        
        $py_send_cost = $pay['py_send_cost'];
        $py_send_cost2 = $pay['py_send_cost2'];
        // 1회차 결제 메일 보냄
        include_once(G5_SUBSCRIPTION_PATH . '/subscription_pay_mail1.inc.php');
        include_once(G5_SUBSCRIPTION_PATH . '/subscription_pay_mail2.inc.php');
    }
}

// SMS BEGIN --------------------------------------------------------

// SMS END   --------------------------------------------------------

// orderview 에서 사용하기 위해 session에 넣고
$uid = md5($od_id . G5_TIME_YMDHIS . $_SERVER['REMOTE_ADDR']);
set_session('subs_orderview_uid', $uid);

if (function_exists('add_order_post_log')) {
    add_order_post_log('', 'delete');
}

// 주문 정보 임시 데이터 삭제
$sql = " delete from {$g5['g5_subscription_order_data_table']} where od_id = '$od_id' and dt_pg = '$od_pg' ";
sql_query($sql);

// 주문번호제거
set_session('subs_order_id', '');

// 기존자료 세션에서 제거
if (get_session('subs_direct')) {
    set_session('subs_cart_direct', '');
}

// 배송지처리
if ($is_member) {
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
    if ($ad_default) {
        $sql = " update {$g5['g5_shop_order_address_table']}
                    set ad_default = '0'
                    where mb_id = '{$member['mb_id']}' ";
        sql_query($sql);
    }

    $ad_subject = isset($_POST['ad_subject']) ? clean_xss_tags($_POST['ad_subject']) : '';

    if (isset($row['ad_id']) && $row['ad_id']) {
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

goto_url(G5_SUBSCRIPTION_URL . '/orderinquiryview.php?od_id=' . $od_id . '&amp;uid=' . $uid);
?>
<html>

<head>
    <title>주문정보 기록</title>
    <script>
        // 결제 중 새로고침 방지 샘플 스크립트 (중복결제 방지)
        function noRefresh() {
            /* CTRL + N키 막음. */
            if ((event.keyCode == 78) && (event.ctrlKey == true)) {
                event.keyCode = 0;
                return false;
            }
            /* F5 번키 막음. */
            if (event.keyCode == 116) {
                event.keyCode = 0;
                return false;
            }
        }

        document.onkeydown = noRefresh;
    </script>
</head>

</html>
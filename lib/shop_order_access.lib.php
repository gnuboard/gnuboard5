<?php
if (!defined('_GNUBOARD_')) exit;
include_once(dirname(__FILE__).'/shop_order_compat.lib.php');
include_once(dirname(__FILE__).'/shop_order_state.lib.php');
include_once(dirname(__FILE__).'/shop_order_fields.lib.php');

// 결제 복귀는 주문번호가 아니라 임시 저장 시 발급한 서버 세션 상태로 인증한다.
function shop_order_access_fail()
{
    header('Cache-Control: no-store, private');
    header('Referrer-Policy: no-referrer');
    alert('결제 요청을 확인할 수 없습니다. 결제 내역을 확인한 후 다시 시도해 주십시오.', G5_SHOP_URL);
    exit;
}

function shop_order_access_id($value)
{
    return is_string($value) && preg_match('/\A[0-9]{1,20}\z/D', $value);
}

function shop_order_access_load_legacy($order_id, $pg)
{
    global $g5, $member;

    header('Cache-Control: no-store, private');
    header('Referrer-Policy: no-referrer');
    if (!shop_order_access_id($order_id)) shop_order_access_fail();
    $states = get_session('ss_order_data_access');
    $state = is_array($states) && isset($states[$order_id]) ? $states[$order_id] : null;
    $member_id = isset($member['mb_id']) ? (string)$member['mb_id'] : '';
    if (!is_array($state) || $state['pg'] !== $pg || $state['member'] !== $member_id ||
        $state['expires'] <= G5_SERVER_TIME) shop_order_access_fail();

    if ($state['personal']) {
        if ((string)get_session('ss_personalpay_id') !== $order_id ||
            !$state['personal_hash'] ||
            $state['personal_hash'] !== get_session('ss_personalpay_hash')) shop_order_access_fail();
        $done = sql_fetch("select pp_tno, pp_use from {$g5['g5_shop_personalpay_table']} where pp_id = '$order_id'");
        if (!$done || !$done['pp_use'] || $done['pp_tno']) shop_order_access_fail();
    } else {
        $cart = get_session($state['direct'] ? 'ss_cart_direct' : 'ss_cart_id');
        if ((string)get_session('ss_order_id') !== $order_id ||
            (bool)get_session('ss_direct') !== $state['direct'] ||
            !$cart || (string)$cart !== $state['cart']) shop_order_access_fail();
        $done = sql_fetch("select od_id from {$g5['g5_shop_order_table']} where od_id = '$order_id'");
        if (!empty($done['od_id'])) shop_order_access_fail();
    }

    // 중복 행을 임의로 선택하지 않고, 최소 메타데이터부터 대조한다.
    $rows = sql_query("select cart_id, mb_id, dt_pg, dt_time from {$g5['g5_shop_order_data_table']} where od_id = '$order_id'");
    if (sql_num_rows($rows) !== 1) shop_order_access_fail();
    $meta = sql_fetch_array($rows);
    if ((string)$meta['cart_id'] !== $state['cart'] || $meta['mb_id'] !== $state['member'] ||
        $meta['dt_pg'] !== $pg || $meta['dt_time'] !== $state['time']) shop_order_access_fail();

    $row = sql_fetch("select dt_data from {$g5['g5_shop_order_data_table']} where od_id = '$order_id' and dt_pg = '".sql_escape_string($pg)."'");
    if (empty($row['dt_data']) || !shop_order_equals($state['digest'], hash('sha256', $row['dt_data']))) shop_order_access_fail();
    $data = shop_order_decode_data($row['dt_data']);
    if (!is_array($data) || (!empty($data['pp_id']) !== $state['personal']) ||
        ($state['personal'] && (string)$data['pp_id'] !== $order_id)) shop_order_access_fail();
    return $data;
}

function shop_order_access_load($order_id, $pg)
{
    header('Cache-Control: no-store, private');
    header('Referrer-Policy: no-referrer');
    return shop_order_state_load($order_id, $pg);
}

function shop_order_access_payment($order_id, $payment_key, $amount = null)
{
    if (!is_string($payment_key) || !preg_match('/\A[A-Za-z0-9_-]{1,200}\z/D', $payment_key)) shop_order_access_fail();
    if ($amount !== null && (!is_string($amount) || !preg_match('/\A[0-9]{1,12}\z/D', $amount))) shop_order_access_fail();
    $data = shop_order_access_load($order_id, 'toss');
    if ($amount !== null && (!is_string($amount) || !preg_match('/\A[0-9]{1,12}\z/D', $amount) ||
        !isset($data['amountValue']) || (int)$amount <= 0 || (int)$amount !== (int)$data['amountValue'])) shop_order_access_fail();
    $states = get_session('ss_order_data_access');
    if (isset($states[$order_id]['payment_key']) && $states[$order_id]['payment_key'] !== $payment_key) shop_order_access_fail();
    $states[$order_id]['payment_key'] = $payment_key;
    shop_order_state_write($order_id, array('payment_key'=>$payment_key));
    set_session('ss_order_data_access', $states);
    $data['paymentKey'] = $payment_key;
    $data['orderId'] = $order_id;
    return $data;
}

// 클라이언트가 보낸 해시를 신뢰하지 않고 저장 당시 서버 상태만 사용한다.
function shop_order_access_password($order_id)
{
    $states = get_session('ss_order_data_access');
    if (!is_array($states) || !isset($states[$order_id])) return null;
    $state = $states[$order_id];
    shop_order_access_load($order_id, $state['pg']);
    if ($state['personal'] || empty($state['password_hash'])) shop_order_access_fail();
    return $state['password_hash'];
}

function shop_order_access_forget($order_id)
{
    shop_order_state_complete($order_id);
    $states = get_session('ss_order_data_access');
    if (is_array($states)) {
        unset($states[$order_id]);
        set_session('ss_order_data_access', $states);
    }
}

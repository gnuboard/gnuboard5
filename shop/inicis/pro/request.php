<?php
include_once('./_common.php');
include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    inicis_pro_json_response(array('success' => false, 'message' => '올바른 요청이 아닙니다.'));

if (function_exists('check_request_origin'))
    check_request_origin(G5_SHOP_URL);

if (!inicis_pro_is_enabled())
    inicis_pro_json_response(array('success' => false, 'message' => 'INIpay PRO 결제가 활성화되어 있지 않습니다.'));

$oid = isset($_POST['oid']) ? inicis_pro_clean_oid($_POST['oid']) : '';
$device = isset($_POST['device_type']) ? strtoupper(preg_replace('/[^A-Z]/i', '', $_POST['device_type'])) : '';
if ($oid === '' || ($device !== 'WEB' && $device !== 'MOBILE'))
    inicis_pro_json_response(array('success' => false, 'message' => '결제 요청 정보가 올바르지 않습니다.'));

$sql = " select * from {$g5['g5_shop_order_data_table']}
          where od_id = '".sql_escape_string($oid)."'
            and dt_pg = 'inicis'
          order by dt_time desc
          limit 1 ";
$temp_row = sql_fetch($sql);
if (empty($temp_row['od_id']))
    inicis_pro_json_response(array('success' => false, 'message' => '임시 주문정보가 저장되지 않았습니다.'));

$order_data = @unserialize(base64_decode($temp_row['dt_data']));
if (!is_array($order_data))
    inicis_pro_json_response(array('success' => false, 'message' => '임시 주문정보를 확인할 수 없습니다.'));

$is_personal = !empty($order_data['pp_id']);
if ($is_personal) {
    if ((string) get_session('ss_personalpay_id') !== (string) $oid)
        inicis_pro_json_response(array('success' => false, 'message' => '개인결제 세션이 일치하지 않습니다.'));
} else {
    if ((string) get_session('ss_order_id') !== (string) $oid)
        inicis_pro_json_response(array('success' => false, 'message' => '주문 세션이 일치하지 않습니다.'));

    $session_member_id = isset($member['mb_id']) ? (string) $member['mb_id'] : '';
    if ((string) $temp_row['mb_id'] !== $session_member_id)
        inicis_pro_json_response(array('success' => false, 'message' => '주문 회원정보가 일치하지 않습니다.'));

    $cart_id = preg_replace('/[^0-9]/', '', (string) $temp_row['cart_id']);
    if ($cart_id === '' || get_cart_count($cart_id) === 0)
        inicis_pro_json_response(array('success' => false, 'message' => '결제할 장바구니 상품이 없습니다.'));
    if (function_exists('before_check_cart_price') && !before_check_cart_price($cart_id))
        inicis_pro_json_response(array('success' => false, 'message' => '상품 가격이 변경되었습니다. 장바구니를 다시 확인해 주십시오.'));
}

$settle_case_key = $is_personal ? 'pp_settle_case' : 'od_settle_case';
$settle_case = isset($order_data[$settle_case_key]) ? $order_data[$settle_case_key] : '';
$pay_type = inicis_pro_pay_type($settle_case);
$easypay_code = inicis_pro_easypay_code($settle_case);
$easypay_name = inicis_pro_easypay_name($settle_case);
$amount = inicis_pro_expected_amount($order_data, $temp_row);
$mid = inicis_pro_get_mid($pay_type);
$hashkey = inicis_pro_get_hashkey($pay_type);

if ($pay_type === '' || $amount <= 0 || $amount > 999999999)
    inicis_pro_json_response(array('success' => false, 'message' => '결제수단 또는 결제금액이 올바르지 않습니다.'));
if ($easypay_code !== '' && !inicis_pro_easypay_is_enabled($settle_case))
    inicis_pro_json_response(array('success' => false, 'message' => '선택한 간편결제는 현재 사용할 수 없습니다.'));
if ($mid === '' || $hashkey === '')
    inicis_pro_json_response(array('success' => false, 'message' => 'KG이니시스 MID 또는 HashKey가 설정되지 않았습니다.'));
if (!function_exists('hash') || !in_array('sha512', hash_algos()))
    inicis_pro_json_response(array('success' => false, 'message' => '서버에서 SHA-512 해시를 지원하지 않습니다.'));

$timestamp = inicis_pro_timestamp();
$buyer_name = $is_personal ? (isset($order_data['pp_name']) ? $order_data['pp_name'] : '') : (isset($order_data['od_name']) ? $order_data['od_name'] : '');
$buyer_email = $is_personal ? (isset($order_data['pp_email']) ? $order_data['pp_email'] : '') : (isset($order_data['od_email']) ? $order_data['od_email'] : '');
$buyer_phone = $is_personal ? (isset($order_data['pp_hp']) ? $order_data['pp_hp'] : '') : (!empty($order_data['od_hp']) ? $order_data['od_hp'] : (isset($order_data['od_tel']) ? $order_data['od_tel'] : ''));

$reserved = array(
    'phonenum' => preg_replace('/[^0-9]/', '', $buyer_phone)
);
// 이메일은 선택 항목이며, 형식이 확인되지 않으면 결제창 요청이 거부되므로 확인된 경우에만 전달한다.
if (inicis_pro_valid_email($buyer_email))
    $reserved['email'] = inicis_pro_cut($buyer_email, 60);
if ($pay_type === 'CARD') {
    $reserved['quotabase'] = '01:02:03:04:05:06:07:08:09:10:11:12';
    if ($easypay_code !== '')
        $reserved['d_easypay'] = $easypay_code;
}
if ($pay_type === 'BANK')
    $reserved['bank_receipt'] = 'N';
if ($pay_type === 'VBANK') {
    $reserved['vbank_receipt'] = 'N';
    $reserved['vbank_dt'] = date('Ymd', G5_SERVER_TIME + (86400 * 3));
    $reserved['vbank_tm'] = '2359';
}
if (!empty($default['de_escrow_use']) && ($pay_type === 'BANK' || $pay_type === 'VBANK'))
    $reserved['useescrow'] = 'Y';

$base_url = defined('G5_HTTPS_SHOP_URL') && G5_HTTPS_SHOP_URL ? G5_HTTPS_SHOP_URL : G5_SHOP_URL;
$noti = inicis_pro_make_noti($oid, $amount, $device, $hashkey);

$request = array(
    'P_MID' => $mid,
    'P_OID' => $oid,
    'P_PAY_TYPE' => $pay_type,
    'P_DEVICE_TYPE' => $device,
    'P_IDCCODE' => 'Y',
    'P_AMT' => (string) $amount,
    'P_GOODS' => inicis_pro_goods_name($order_data, $temp_row),
    'P_UNAME' => inicis_pro_cut($buyer_name, 30),
    'P_NEXT_URL' => $base_url.'/inicis/pro/return.php',
    'P_CHARSET' => 'UTF-8',
    'P_NOTI' => $noti,
    'P_TIMESTAMP' => $timestamp,
    'P_CHKFAKE' => inicis_pro_hash($amount, $oid, $timestamp, $hashkey),
    'P_RESERVED' => inicis_pro_json_encode($reserved)
);

if ($pay_type === 'VBANK')
    $request['P_NOTI_URL'] = $base_url.'/inicis/pro/noti.php';

// PC 결제창은 iframe 모달이므로 닫기 URL을 전달하면 주문서 전체가 다시
// 로드된다. 모바일은 현재 창에서 결제가 진행되므로 돌아갈 URL을 유지한다.
if ($device === 'MOBILE') {
    if ($is_personal)
        $request['P_CLOSE_URL'] = G5_SHOP_URL.'/personalpayform.php?pp_id='.$oid;
    elseif (!empty($order_data['sw_direct']))
        $request['P_CLOSE_URL'] = G5_SHOP_URL.'/orderform.php?sw_direct=1';
    else
        $request['P_CLOSE_URL'] = G5_SHOP_URL.'/orderform.php';
}

if ($pay_type === 'HPP')
    $request['P_HPP_METHOD'] = '2';
if (!empty($default['de_tax_flag_use'])) {
    $request['P_TAX'] = isset($order_data['comm_vat_mny']) ? (string) (int) $order_data['comm_vat_mny'] : '0';
    $request['P_TAXFREE'] = isset($order_data['comm_free_mny']) ? (string) (int) $order_data['comm_free_mny'] : '0';
}

inicis_pro_audit_write($oid, 'request', 'request_ready', array(
    'mid' => $mid,
    'environment' => inicis_pro_environment(),
    'member_id' => isset($temp_row['mb_id']) ? $temp_row['mb_id'] : '',
    'amount' => $amount,
    'pay_type' => $pay_type,
    'easy_pay' => $easypay_name,
    'device' => $device,
    'order_type' => $is_personal ? 'personal' : 'order',
    'source' => $device === 'MOBILE' ? 'mobile' : 'web',
    'vbank_due_at' => $pay_type === 'VBANK' ? date('Y-m-d 23:59:00', G5_SERVER_TIME + (86400 * 3)) : '',
    'message' => $easypay_code !== '' ? $settle_case.' 결제창 요청정보 생성 완료' : '결제창 요청정보 생성 완료'
));

inicis_pro_json_response(array('success' => true, 'request' => $request));

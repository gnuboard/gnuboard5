<?php
include_once('./_common.php');

// 요청 출처 검증
if (function_exists('check_request_origin')) check_request_origin(G5_SHOP_URL);

if(empty($_POST))
    die('정보가 넘어오지 않았습니다.');

include_once(G5_LIB_PATH.'/shop_order_access.lib.php');
shop_order_checkout_restore();

$od_settle_case = isset($_POST['od_settle_case']) ? clean_xss_tags($_POST['od_settle_case'], 1, 1) : '';
if ($od_settle_case === 'KAKAOPAY') {
    die('이 결제 방식은 더 이상 지원하지 않습니다. 다른 결제수단을 선택해 주십시오.');
}

// 저장되는 원본에도 정제한 값을 반영한다.
$_POST['od_settle_case'] = $od_settle_case;

// 결제환경 정보는 요청값을 사용하지 않고 서버에서 결정한다.
$_POST['od_test'] = $default['de_card_test'];
$_POST['od_ip']   = function_exists('get_real_client_ip') ? get_real_client_ip() : $_SERVER['REMOTE_ADDR'];

if(isset($_POST['pp_id']) && $_POST['pp_id']) {
    $od_id   = get_session('ss_personalpay_id');
    $cart_id = 0;
    if (!is_string($_POST['pp_id']) || $_POST['pp_id'] !== (string)$od_id ||
        !shop_order_access_id((string)$od_id) || !get_session('ss_personalpay_hash')) shop_order_access_fail();

    $sql = "select pp_use, pp_tno, pp_price, pp_time from {$g5['g5_shop_personalpay_table']} where pp_id = '$od_id' ";
    $pp_row = sql_fetch($sql);

    if (!$pp_row || md5($od_id.$pp_row['pp_price'].$pp_row['pp_time']) !== get_session('ss_personalpay_hash')) shop_order_access_fail();

    if( $pp_row['pp_tno'] ){
        die('해당 개인결제는 이미 결제되었습니다.');
    } else if( ! $pp_row['pp_use'] ){
        die('해당 개인결제는 사용이 금지되어 있습니다.');
    }

} else {
    $od_id   = get_session('ss_order_id');
    $_POST['sw_direct'] = get_session('ss_direct');

    if ($_POST['sw_direct']) {
        $cart_id = get_session('ss_cart_direct');
    }
    else {
        $cart_id = get_session('ss_cart_id');
    }

    if( G5_IS_MOBILE && $default['de_pg_service'] == 'inicis' ){
        $_POST['post_cart_id'] = $cart_id;
    }
}

if (!shop_order_access_id((string)$od_id) || (empty($_POST['pp_id']) && !$cart_id)) shop_order_access_fail();

// 비회원 비밀번호는 HTML 복원용 데이터와 분리하고 서버 세션에 해시만 보관한다.
$order_password_hash = '';
if (empty($member['mb_id']) && empty($_POST['pp_id'])) {
    if (isset($_POST['od_pwd']) && !is_string($_POST['od_pwd'])) shop_order_access_fail();
    $order_password_hash = get_encrypt_string(isset($_POST['od_pwd']) ? $_POST['od_pwd'] : shop_order_random_hex(16));
}
unset($_POST['od_pwd']);


$default_pg = $default['de_pg_service'];
if (function_exists('is_use_easypay') && is_use_easypay('global_nhnkcp') &&
    isset($_POST['nhnkcp_pay_case']) && $_POST['nhnkcp_pay_case'] === 'naverpay') {
    $default_pg = 'kcp';
}

if( $od_settle_case == '삼성페이' ){    //현재 삼성페이인 경우에는 pg를 inicis로 처리 
    $default_pg = 'inicis';
}

shop_order_state_can_save((string)$od_id);
$_POST = shop_order_filter_data($_POST, $default_pg);
$dt_data = base64_encode(serialize($_POST));

// 동일한 주문번호가 있는지 체크
$sql = " select count(*) as cnt from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' ";
$row = sql_fetch($sql);
if($row['cnt'])
    sql_query(" delete from {$g5['g5_shop_order_data_table']} where od_id = '$od_id' ");

$sql = " insert into {$g5['g5_shop_order_data_table']}
            set od_id   = '$od_id',
                cart_id = '$cart_id',
                mb_id   = '{$member['mb_id']}',
                dt_pg   = '$default_pg',
                dt_data = '$dt_data',
                dt_time = '".G5_TIME_YMDHIS."' ";
if (!sql_query($sql, false)) shop_order_access_fail();

// 요청 본문을 그대로 인증 상태로 사용하지 않는다. 세션은 응답 HTML에 노출하지 않는다.
$states = get_session('ss_order_data_access');
if (!is_array($states)) $states = array();
foreach ($states as $key => $state) {
    if ($state['expires'] < G5_SERVER_TIME) unset($states[$key]);
}
$states[(string)$od_id] = array(
    'pg' => $default_pg,
    'member' => isset($member['mb_id']) ? (string)$member['mb_id'] : '',
    'cart' => (string)$cart_id,
    'personal' => !empty($_POST['pp_id']),
    'personal_hash' => get_session('ss_personalpay_hash'),
    'direct' => (bool)get_session('ss_direct'),
    'time' => G5_TIME_YMDHIS,
    'expires' => G5_SERVER_TIME + (defined('G5_ORDER_DATA_ACCESS_TTL') ? G5_ORDER_DATA_ACCESS_TTL : 7200),
    'digest' => hash('sha256', $dt_data),
    'password_hash' => $order_password_hash,
);
$states[(string)$od_id] = shop_order_state_save((string)$od_id, $states[(string)$od_id]);

die('');
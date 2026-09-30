<?php
include_once('./_common.php');
include_once(G5_MSHOP_PATH.'/settle_inicis.inc.php');
include_once(G5_SHOP_PATH.'/inicis/pro/inicis_pro.lib.php');
include_once(G5_LIB_PATH.'/shop_order_access.lib.php');

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, private');
if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    inicis_pro_json_response(array('error' => '올바른 요청이 아닙니다.'));
if (function_exists('check_request_origin')) check_request_origin(G5_SHOP_URL);

$oid = isset($_POST['oid']) && is_string($_POST['oid']) ? $_POST['oid'] : '';
$price = isset($_POST['price']) && is_string($_POST['price']) ? $_POST['price'] : '';
if (!shop_order_access_id($oid) || !preg_match('/\A[0-9]{1,8}\z/', $price) || (int) $price <= 0)
    inicis_pro_json_response(array('error' => '결제 요청 정보가 올바르지 않습니다.'));

// 타 PG와 병행하는 간편결제도 저장 당시의 주문 접근 상태로 검증한다.
$states = get_session('ss_order_data_access');
if (!is_array($states) || !isset($states[$oid]['pg']))
    inicis_pro_json_response(array('error' => '임시 주문정보를 확인할 수 없습니다.'));
$data = shop_order_access_load($oid, $states[$oid]['pg']);
$settle_case = !empty($data['pp_id']) ? $data['pp_settle_case'] : $data['od_settle_case'];
if (!inicis_pro_easypay_is_enabled($settle_case)
    || ($default['de_pg_service'] !== 'inicis' && !is_inicis_order_pay($settle_case)))
    inicis_pro_json_response(array('error' => '사용할 수 없는 결제수단입니다.'));

$amount = inicis_pro_expected_amount($data, array());
if ($amount <= 0 || (string) $amount !== $price)
    inicis_pro_json_response(array('error' => '결제금액이 일치하지 않습니다. 다시 확인해 주십시오.'));

// 구 모바일 모듈은 결제 폼에 설정된 MID 기준으로 테스트 키를 선택한다.
$hashkey = inicis_pro_get_hashkey();
if ($hashkey === '')
    inicis_pro_json_response(array('error' => 'KG이니시스 모바일 금액위변조 HashKey가 설정되지 않았습니다. 상점관리자에게 문의해 주십시오.'));
$timestamp = inicis_pro_timestamp();
$hash = inicis_pro_hash($price, $oid, $timestamp, $hashkey);
if ($hash === '')
    inicis_pro_json_response(array('error' => '결제 해시를 생성할 수 없습니다.'));

inicis_pro_json_response(array('error' => '', 'timestamp' => $timestamp, 'hash' => $hash));

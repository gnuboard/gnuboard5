<?php
if (!defined('_GNUBOARD_')) exit;
include_once(G5_LIB_PATH.'/shop_order_access.lib.php');
header('Content-Type: text/plain; charset=utf-8');
header('Cache-Control: no-store, private');
$rest_order = isset($_GET['ordr_idxx']) ? $_GET['ordr_idxx'] : '';
$rest_data = shop_order_access_load($rest_order, 'kcp');
$rest_methods = array('신용카드'=>'CARD', '간편결제'=>'CARD', '계좌이체'=>'BANK', '가상계좌'=>'VCNT', '휴대폰'=>'MOBX');
$rest_case = isset($rest_data['pp_settle_case']) && !empty($rest_data['pp_id']) ? $rest_data['pp_settle_case'] :
    (isset($rest_data['od_settle_case']) ? $rest_data['od_settle_case'] : '');
try {
    // 등록은 세션으로 인증된 저장 주문에서 가져온다. 승인 단계에서 DB 재계산 금액을 다시 대조한다.
    if (!isset($rest_methods[$rest_case], $rest_data['good_mny'], $rest_data['g5_order_state']) ||
        !preg_match('/\A[0-9]{1,12}\z/D', (string)$rest_data['good_mny']) || $rest_data['good_mny'] <= 0) throw new Exception('KCP registration order');
    $rest_request = array('site_cd'=>$g_conf_site_cd, 'ordr_idxx'=>$rest_order,
        'good_mny'=>(string)$rest_data['good_mny'],
        'good_name'=>isset($rest_data['good_name']) ? $rest_data['good_name'] : '주문상품',
        'pay_method'=>$rest_methods[$rest_case],
        'escw_used'=>!empty($default['de_escrow_use']) && $rest_case !== '간편결제' ? 'Y' : 'N',
        'Ret_URL'=>G5_MSHOP_URL.'/kcp/order_approval_form.php?g5_order_state='.rawurlencode($rest_data['g5_order_state']),
        'user_agent'=>isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '');
    $rest_client = new G5_KCP_REST_Client(kcp_rest_config());
    $rest_result = $rest_client->register($rest_request);
    if ($rest_result['Code'] !== '0000') throw new Exception('KCP registration rejected');
    echo '0000,'.$rest_result['approvalKey'].','.$rest_result['PayUrl'].',Success';
} catch (Exception $e) {
    echo 'R999,,,KCP REST 거래등록을 완료하지 못했습니다. 설정 및 주문 정보를 확인해 주십시오.';
}

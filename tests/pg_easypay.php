<?php
if (PHP_SAPI !== 'cli') exit;
// 실행: php tests/pg_easypay.php (DB 및 외부 PG 연결 없음)
define('_GNUBOARD_', true);
require dirname(__DIR__).'/lib/shop.easypay.lib.php';
require dirname(__DIR__).'/lib/shop.lib.php';
function check($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
function settings($pg, $services, $use = 1) {
    return shop_easypay_normalize(array('de_pg_service' => $pg, 'de_easy_pay_services' => $services,
        'de_easy_pay_use' => $use, 'de_card_test' => 1, 'de_kcp_mid' => '', 'de_kcp_site_key' => '', 'de_inicis_pro_use' => 1));
}
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)';
foreach (array('toss', 'nicepay') as $pg) {
    $default = settings($pg, $pg.'_naverpay,global_nhnkcp_naverpay');
    foreach (array(false, true) as $mobile) {
        $buttons = shop_easypay_buttons($mobile);
        check(isset($buttons[$pg.'_naverpay']) && !isset($buttons['nhnkcp_naverpay']), '기본 PG 네이버페이 우선');
        check(!is_use_easypay('global_nhnkcp'), '병용 요청 경로 차단');
    }
    $default = settings($pg, 'toss_configured,global_nhnkcp_naverpay');
    check(isset(shop_easypay_buttons()['nhnkcp_naverpay']), '기본 PG 네이버페이 비활성 시 병용');
    $default['de_card_test'] = 0;
    check(!is_use_easypay('global_nhnkcp'), '실결제 인증정보 필수');
    $default['de_kcp_mid'] = 'merchant'; $default['de_kcp_site_key'] = 'key';
    check(is_use_easypay('global_nhnkcp'), '병용 인증정보 설정');
}
foreach (array('kcp', 'inicis', 'toss', 'nicepay') as $pg) {
    $default = settings($pg, implode(',', array_keys(shop_easypay_catalog($pg))));
    foreach (array(false, true) as $mobile) {
        $buttons = shop_easypay_buttons($mobile);
        foreach ($buttons as $key => $html) {
            check(isset(shop_easypay_catalog($pg)[$key]), '다른 PG 설정 혼입 금지');
            check((strpos($html, '<li>') === 0) === $mobile, 'PC/모바일 마크업');
            check(strpos($html, 'data-pay="'.shop_easypay_catalog($pg)[$key][1].'"') !== false, '요청 provider');
        }
        check(strpos(implode('', $buttons), 'KPAY') === false, '범용 KPAY 중복 제거');
    }
    $default['de_easy_pay_use'] = 0;
    check(!shop_easypay_buttons(), '간편결제 전체 비활성');
}
$default = settings('kcp', 'global_nhnkcp_naverpay');
check(!shop_easypay_buttons(), '병용 설정이 KCP 기본 네이버페이로 오인되지 않음');
$default = settings('lg', 'global_nhnkcp_naverpay,used_nhnkcp_naverpay_point', 0);
check(count(shop_easypay_buttons(true)) === 2, '병용 카드/머니 독립 노출');
$default = settings('inicis', 'inicis_samsungpay');
$default['de_inicis_pro_use'] = 0;
check(!shop_easypay_buttons(false) && count(shop_easypay_buttons(true)) === 1, '구 INIpay 삼성페이 모바일 제한');
$default = settings('toss', 'toss_configured,toss_applepay');
check(!shop_easypay_buttons(false) && count(shop_easypay_buttons(true)) === 1, '애플페이 iOS 모바일');
$_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Linux; Android 14)';
check(!shop_easypay_buttons(true), 'Android 애플페이 숨김');
$old = array('de_pg_service' => 'inicis', 'de_easy_pay_use' => 0, 'de_inicis_lpay_use' => 1);
$new = shop_easypay_normalize($old);
check($new['de_easy_pay_use'] && strpos($new['de_easy_pay_services'], 'inicis_lpay') !== false, '기존 이니시스 설정 승계');
$old['de_easy_pay_services'] = 'inicis_configured';
check(!shop_easypay_normalize($old)['de_inicis_lpay_use'], '명시적 선택 해제 유지');
$default = settings('toss', '');
check(isset(shop_easypay_buttons()['toss_payco']), '기존 토스 PAYCO 승계');
$default = settings('toss', 'toss_configured');
check(!shop_easypay_buttons(), '토스 전체 선택 해제 유지');

// 실제 승인 성공 처리 블록에 카드 없는 머니 결제와 복합결제 응답을 투입한다.
$source = file_get_contents(dirname(__DIR__).'/shop/toss/toss_result.php');
$start = strpos($source, '        // 공통 DB처리 변수 설정');
$end = strpos($source, "\n    } else {", $start);
$code = substr($source, $start, $end - $start);
foreach (array(false, true) as $with_card) {
    $method = '간편결제';
    $toss = (object) array('easyPayCode' => array('NAVERPAY' => '네이버페이'), 'responseData' => array(
        'paymentKey' => 'fixture-key', 'totalAmount' => 10000, 'useEscrow' => false, 'approvedAt' => '2026-09-09T12:00:00+09:00',
        'easyPay' => array('provider' => 'NAVERPAY', 'amount' => 4000, 'discountAmount' => 1000),
        'cashReceipt' => array('type' => '소득공제', 'receiptKey' => 'receipt', 'issueNumber' => '123', 'receiptUrl' => 'https://example.test/receipt')));
    if ($with_card) $toss->responseData['card'] = array('approveNo' => '456', 'amount' => 5000);
    $pg_receipt_infos = array();
    eval($code);
    check($amount === 10000 && $card_name === '네이버페이', '총 승인금액과 provider 유지');
    check($app_no === ($with_card ? '456' : ''), '전액 머니/복합결제 승인번호');
    check($pg_receipt_infos['od_cash'] === 1 && $pg_receipt_infos['od_cash_no'] === '123', '간편결제 현금영수증 보존');
}
echo "PG 간편결제 회귀 테스트 통과\n";

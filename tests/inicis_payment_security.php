<?php
if (PHP_SAPI !== 'cli') exit;
// 외부 PG/DB 호출 없이 실제 서명 엔드포인트를 실행한다. 공통 부트스트랩만 대체한다.
error_reporting(E_ALL);
set_error_handler(function ($severity, $message) { throw new Exception($message); });
define('_GNUBOARD_', true);
$root = dirname(__DIR__);
require $root.'/shop/inicis/pro/inicis_pro.lib.php';
require $root.'/shop/inicis/libs/properties.php';

function check($ok, $message) {
    if (!$ok) throw new Exception($message);
}

if (isset($argv[1])) {
    $case = json_decode($argv[1], true);
    $_POST = isset($case['post']) ? $case['post'] : array('oid'=>'202609300001', 'price'=>'1200');
    $_SERVER['REQUEST_METHOD'] = isset($case['method']) ? $case['method'] : 'POST';
    $default = array('de_pg_service'=>'inicis', 'de_card_test'=>0, 'de_inicis_hash_key'=>'fixture-secret', 'de_inicis_lpay_use'=>0, 'de_inicis_kakaopay_use'=>0);
    $default = array_merge($default, isset($case['default']) ? $case['default'] : array());
    $g5 = array('g5_shop_personalpay_table'=>'personal');
    function get_session($key) {
        global $case;
        if ($key === 'ss_order_inicis_id') return isset($case['oid']) ? $case['oid'] : '202609300001';
        return !empty($case['no_session']) ? array() : array('202609300001'=>array('pg'=>'inicis'));
    }
    function shop_order_access_id($id) { return is_string($id) && preg_match('/\A[0-9]{1,20}\z/', $id); }
    function shop_order_access_load($id, $pg) {
        global $case;
        if (!empty($case['access_denied'])) die(json_encode(array('error'=>'access denied')));
        if (!empty($case['personal'])) return array('pp_id'=>$id, 'pp_settle_case'=>'신용카드');
        return array('od_price'=>1300, 'od_send_cost'=>200, 'od_temp_point'=>300, 'od_settle_case'=>isset($case['settle_case']) ? $case['settle_case'] : '신용카드');
    }
    function sql_escape_string($value) { return addslashes($value); }
    function sql_fetch($sql) { return array('pp_id'=>'202609300001', 'pp_price'=>1200, 'pp_use'=>1, 'pp_tno'=>''); }
    function check_request_origin($url) {}
    function is_inicis_simple_pay() { global $default; return !empty($default['de_inicis_lpay_use']) || !empty($default['de_inicis_kakaopay_use']); }
    function is_inicis_order_pay($method) { return in_array($method, array('삼성페이', 'lpay', 'inicis_kakaopay'), true); }
    define('G5_SHOP_URL', 'https://shop.example/shop');
    $signKey = isset($case['key']) ? $case['key'] : 'fixture-secret';
    $timestamp = '1760000000000';
    if (isset($case['pc_approval']) || isset($case['pc_transport'])) {
        $idc = isset($case['pc_approval']) ? $case['pc_approval'] : 'fc';
        $prop = new properties();
        $_REQUEST = array('resultCode'=>'0000', 'authToken'=>'fixture-token', 'idc_name'=>$idc,
            'authUrl'=>$prop->getAuthUrl($idc), 'netCancelUrl'=>$prop->getNetCancel($idc));
        if (isset($case['pc_transport'])) {
            $_REQUEST = array_merge($_REQUEST, $case['pc_transport']);
            $calls = array();
            $buffer_level = ob_get_level();
            ob_start();
            register_shutdown_function(function () use (&$calls, $buffer_level) {
                while (ob_get_level() > $buffer_level) ob_end_clean();
                echo json_encode(array('calls'=>$calls));
            });
        }
        require $root.'/shop/inicis/libs/INIStdPayUtil.php';
        $util = new INIStdPayUtil();
        $mid = 'INIpayTest';
        class HttpClient {
            public $errormsg = 'fixture timeout';
            public $body = '{"resultCode":"0000"}';
            function processHTTP($url, $params) {
                global $case, $calls;
                if (isset($case['pc_transport'])) {
                    $calls[] = array('url'=>$url, 'params'=>$params);
                    // 최초 승인 통신 실패를 재현하고 망취소에서만 성공한다.
                    return count($calls) > 1;
                }
                die(json_encode(array('error'=>'', 'url'=>$url, 'params'=>$params)));
            }
        }
        $source = file_get_contents($root.'/shop/inicis/inistdpay_result.php');
        $source = preg_replace('/^(?:include_once|require_once)\([^\n]+\);\r?\n/m', '', $source);
        eval(substr($source, 5));
        exit;
    }
    if (isset($case['callback'])) {
        $_REQUEST = $case['callback'];
        function set_session($key, $value) {}
        function alert($message) { die(json_encode(array('error'=>$message))); }
        $source = file_get_contents($root.'/mobile/shop/inicis/pay_approval.php');
        $source = substr($source, 0, strpos($source, '$sql ='));
        $source = preg_replace('/^(?:include_once|require_once)\([^\n]+\);\r?\n/m', '', $source);
        eval(substr($source, 5));
        die(json_encode(array('error'=>'', 'url'=>$p_req_url)));
    }
    $path = !empty($case['pc']) ? '/shop/inicis/makesignature.php' : '/mobile/shop/inicis/makesignature.php';
    $source = file_get_contents($root.$path);
    $source = preg_replace('/^include_once\([^\n]+\);\r?\n/m', '', $source);
    eval(substr($source, 5));
    exit;
}

function request($case) {
    $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' '.escapeshellarg(json_encode($case));
    exec($command, $output, $status);
    check($status === 0, '엔드포인트 실행 실패');
    $data = json_decode(implode("\n", $output), true);
    check(is_array($data), 'JSON 응답 확인');
    return $data;
}
foreach (array(false,true) as $pc_mode) {
    foreach (array('lpay'=>'de_inicis_lpay_use', 'inicis_kakaopay'=>'de_inicis_kakaopay_use') as $method=>$flag) {
        $case = array('pc'=>$pc_mode, 'settle_case'=>$method, 'default'=>array('de_pg_service'=>'kcp', $flag=>1));
        check(request($case)['error'] === '', '타 PG 병행 간편결제 정상 서명');
        $case['default'][$flag] = 0;
        check(request($case)['error'] !== '', '비활성 간편결제 거부');
    }
}
foreach (array(false,true) as $pc_mode) {
    foreach (array('inicis','kcp') as $pg) {
        check(request(array('pc'=>$pc_mode, 'settle_case'=>'lpay', 'default'=>array('de_pg_service'=>$pg, 'de_inicis_lpay_use'=>0, 'de_inicis_kakaopay_use'=>1)))['error'] !== '', '다른 간편결제만 활성화된 경우 거부');
    }
}
$ok = request(array());
check($ok['error'] === '' && preg_match('/\A[0-9]{13}\z/', $ok['timestamp']), '모바일 정상 요청');
check($ok['hash'] === base64_encode(hash('sha512', '1200202609300001'.$ok['timestamp'].'fixture-secret', true)), '모바일 SHA512 원시 바이트의 Base64');
check(request(array('personal'=>true))['error'] === '', '개인결제 DB 금액');
foreach (array(
    array('no_session'=>true), array('access_denied'=>true), array('method'=>'GET'),
    array('post'=>array('oid'=>'202609300001','price'=>'1')),
    array('post'=>array('oid'=>'202609300002','price'=>'1200')),
    array('post'=>array('oid'=>array('202609300001'),'price'=>'1200')),
    array('post'=>array('oid'=>'202609300001','price'=>array('1200'))),
    array('default'=>array('de_inicis_hash_key'=>'')),
    array('default'=>array('de_pg_service'=>'kcp')),
) as $case) check(request($case)['error'] !== '', '모바일 비정상 요청 차단');
foreach (array(0=>'3CB8183A4BE283555ACC8363C0360223', 1=>'37F3A120C1DFCDEB708B00F5383D2263') as $escrow=>$key) {
    $test = request(array('default'=>array('de_card_test'=>1, 'de_escrow_use'=>$escrow, 'de_inicis_hash_key'=>'')));
    check($test['hash'] === base64_encode(hash('sha512', '1200202609300001'.$test['timestamp'].$key, true)), '일반/에스크로 테스트 HashKey 선택');
}
$pc = request(array('pc'=>true));
check($pc['verification'] === hash('sha256', 'oid=202609300001&price=1200&signKey=fixture-secret&timestamp=1760000000000'), 'PC verification');
check($pc['sign'] === hash('sha256', 'oid=202609300001&price=1200&timestamp=1760000000000'), 'PC 기존 signature 유지');
foreach (array(array('key'=>''), array('post'=>array('oid'=>'','price'=>'1200')), array('method'=>'GET'), array('post'=>array('oid'=>'202609300001','price'=>'12abc')), array('post'=>array('oid'=>'202609300001','price'=>'1')), array('post'=>array('oid'=>'202609300002','price'=>'1200')), array('no_session'=>true), array('access_denied'=>true)) as $case)
    check(request(array_merge(array('pc'=>true), $case))['error'] !== '', 'PC 비정상 요청 차단');
foreach (array('fc','ks','stg') as $idc) {
    $approval = request(array('pc_approval'=>$idc));
    check($approval['url'] === 'https://'.$idc.'stdpay.inicis.com/api/payAuth', '실제 승인 전송 URL');
    check($approval['params']['verification'] === hash('sha256', 'authToken=fixture-token&signKey=fixture-secret&timestamp=1760000000000'), '실제 승인 전송 verification');
}
foreach (array('fc','ks','stg') as $idc) {
    $prop = new properties();
    $calls = request(array('pc_transport'=>array('idc_name'=>$idc, 'authUrl'=>$prop->getAuthUrl($idc), 'netCancelUrl'=>$prop->getNetCancel($idc))))['calls'];
    check(count($calls) === 2, '승인 통신 실패 시 망취소 1회');
    check($calls[1]['url'] === $prop->getNetCancel($idc), '망취소 IDC 허용 URL');
    check($calls[0]['params'] === $calls[1]['params'], '망취소 시 원래 signature·verification 유지');
}
foreach (array(
    'https://evil.example/api/netCancel',
    'https://fcstdpay.inicis.com.evil.example/api/netCancel',
    'https://fcstdpay.inicis.com/api/netCancel?redirect=evil',
    'https://ksstdpay.inicis.com/api/netCancel',
    '', array('https://fcstdpay.inicis.com/api/netCancel')
) as $url) {
    $calls = request(array('pc_transport'=>array('netCancelUrl'=>$url)))['calls'];
    check(count($calls) === 1, '불일치 망취소 URL로 전송하지 않음');
}
foreach (array(
    array('authUrl'=>'https://evil.example/api/payAuth'),
    array('authUrl'=>'https://ksstdpay.inicis.com/api/payAuth'),
    array('authUrl'=>array('https://fcstdpay.inicis.com/api/payAuth')),
    array('idc_name'=>''), array('idc_name'=>array('fc'))
) as $fields) check(request(array('pc_transport'=>$fields))['calls'] === array(), '잘못된 인증 URL/IDC 승인 전 차단');
check(request(array('pc'=>true, 'personal'=>true))['error'] === '', 'PC 개인결제 정상 서명');
check(request(array('pc'=>true, 'oid'=>'다른 탭 주문'))['error'] === '', 'PC 전역 주문 세션 대신 폼 주문 사용');
$prop = new properties();
foreach (array('fc','ks','stg') as $idc) {
    check($prop->getMobileAuthUrl($idc) === 'https://'.$idc.'mobile.inicis.com/smart/payReq.ini', '모바일 IDC');
    check($prop->getAuthUrl($idc) === 'https://'.$idc.'stdpay.inicis.com/api/payAuth', '웹 IDC');
    check($prop->getNetCancel($idc) === 'https://'.$idc.'stdpay.inicis.com/api/netCancel', '망취소 IDC');
}
foreach (array('', 'FC', 'evil', 'fc/../ks') as $idc) {
    check($prop->getMobileAuthUrl($idc) === '' && $prop->getAuthUrl($idc) === '' && $prop->getNetCancel($idc) === '', '미등록 IDC 차단');
}
foreach (array('fc','ks','stg') as $idc) {
    $url = $prop->getMobileAuthUrl($idc);
    check(request(array('callback'=>array('idc_name'=>$idc, 'P_REQ_URL'=>$url)))['url'] === $url, '실제 콜백 정상 URL');
}
foreach (array(
    'http://fcmobile.inicis.com/smart/payReq.ini',
    'https://fcmobile.inicis.com.evil.example/smart/payReq.ini',
    'https://fcmobile.inicis.com@evil.example/smart/payReq.ini',
    'https://fcmobile.inicis.com/smart/payReq.ini?redirect=evil',
    'https://fcmobile.inicis.com/other',
    'https://ksmobile.inicis.com/smart/payReq.ini',
    array('https://fcmobile.inicis.com/smart/payReq.ini')
) as $url) check(request(array('callback'=>array('idc_name'=>'fc', 'P_REQ_URL'=>$url)))['error'] !== '', '변조 URL의 승인 전 차단');
check(request(array('callback'=>array('P_REQ_URL'=>$prop->getMobileAuthUrl('fc'))))['error'] !== '', 'IDC 누락 차단');
check(request(array('callback'=>array('idc_name'=>array('fc'), 'P_REQ_URL'=>$prop->getMobileAuthUrl('fc'))))['error'] !== '', 'IDC 배열 차단');
echo "이니시스 서명 엔드포인트 및 IDC 회귀 검증 통과\n";

<?php
if (PHP_SAPI !== 'cli') exit;
// cURL 확장을 로딩하지 않은 독립 프로세스: php -n tests/kcp_rest_transport_test.php
if (extension_loaded('curl')) { fwrite(STDERR, "Run with php -n\n"); exit(1); }
define('_GNUBOARD_', true);
require dirname(dirname(__FILE__)).'/lib/kcp_rest.lib.php';
foreach (array('CURLOPT_POST','CURLOPT_POSTFIELDS','CURLOPT_HTTPHEADER','CURLOPT_RETURNTRANSFER',
    'CURLOPT_CONNECTTIMEOUT','CURLOPT_TIMEOUT','CURLOPT_SSL_VERIFYPEER','CURLOPT_SSL_VERIFYHOST',
    'CURLOPT_SSLVERSION','CURL_SSLVERSION_TLSv1_2','CURLOPT_FOLLOWLOCATION','CURLINFO_HTTP_CODE') as $n=>$name) define($name, $n+1);
$calls = 0; $status = 200; $raw = '{"res_cd":"0000"}'; $options = array();
function curl_init($url) { $GLOBALS['url'] = $url; return true; }
function curl_setopt_array($ch, $value) { $GLOBALS['options'] = $value; }
function curl_exec($ch) { $GLOBALS['calls']++; return $GLOBALS['raw']; }
function curl_getinfo($ch, $option) { return $GLOBALS['status']; }
function curl_close($ch) {}
$traces = array();
function g5_kcp_rest_test_trace($trace) { $GLOBALS['traces'][] = $trace; }
$count = 0;
function verify($ok) { global $count; if (!$ok) throw new Exception('Transport check failed'); $count++; }
$client = new G5_KCP_REST_Client(array('test'=>true));
$client->post('payment', array('ordr_mony'=>'1004','enc_data'=>'data'));
verify($options[CURLOPT_SSL_VERIFYPEER] === true);
verify($options[CURLOPT_SSL_VERIFYHOST] === 2);
verify($options[CURLOPT_SSLVERSION] === CURL_SSLVERSION_TLSv1_2);
verify($options[CURLOPT_FOLLOWLOCATION] === false);
verify($options[CURLOPT_CONNECTTIMEOUT] === 10 && $options[CURLOPT_TIMEOUT] === 45);
verify(json_decode($options[CURLOPT_POSTFIELDS], true)['ordr_mony'] === '1004');
verify($url === 'https://stg-spl.kcp.co.kr/gw/enc/v1/payment');
verify($traces[0]['http_status'] === 200 && $traces[0]['response_res_cd'] === '0000');
verify(!isset($traces[0]['enc_data']) && $traces[0]['ordr_mony'] === '1004');
$production = new G5_KCP_REST_Client(array('test'=>false));
$production->post('payment', array('ordr_mony'=>'1004'));
verify(count($traces) === 1); // 운영 요청은 테스트 계측 대상에서 제외한다.
foreach (array(array(false,200), array('',200), array('bad json',200), array('{"res_cd":"0000"}',302),
    array('{"res_cd":"0000"}',500), array(str_repeat('x',1048577),200)) as $failure) {
    $raw = $failure[0]; $status = $failure[1]; $before = $calls; $failed = false;
    try { $client->post('payment', array()); } catch (Exception $e) { $failed = true; }
    verify($failed && $calls === $before+1); // 통신 불확실 시 재전송하지 않는다.
}

// 모바일 등록 처리기까지 실행하여 정상 응답이 R999로 바뀌지 않는지 검사한다.
$fixture_lib = sys_get_temp_dir().'/g5-kcp-register-'.uniqid('', true);
mkdir($fixture_lib, 0700);
file_put_contents($fixture_lib.'/shop_order_access.lib.php', '<?php // 주문 인증 대역');
define('G5_LIB_PATH', $fixture_lib);
define('G5_MSHOP_URL', 'https://shop.example.invalid/mobile/shop');
function shop_order_access_load($id, $pg) {
    if ($id !== '202611090001' || $pg !== 'kcp') throw new Exception('registration order lookup');
    return array('od_settle_case'=>'신용카드', 'good_mny'=>'1004',
        'good_name'=>'등록 검증', 'g5_order_state'=>'fixture-state');
}
$g_conf_site_cd = 'A1234';
$_GET['ordr_idxx'] = '202611090001';
$status = 200;
foreach (array(
    array(false, 'https://rsmpay.kcp.co.kr/pay/mobileGW.kcp', true),
    array(false, 'https://smpay.kcp.co.kr/pay/mobileGW.kcp', true),
    array(true, 'https://testsmpay.kcp.co.kr/pay/mobileGW.kcp', true),
    array(false, 'https://testsmpay.kcp.co.kr/pay/mobileGW.kcp', false),
    array(true, 'https://rsmpay.kcp.co.kr/pay/mobileGW.kcp', false),
    array(false, 'https://rsmpay.kcp.co.kr.evil.invalid/pay', false)
) as $case) {
    list($test_mode, $pay_url, $accepted) = $case;
    $default = array('de_card_test'=>$test_mode, 'de_escrow_use'=>0);
    $raw = json_encode(array('Code'=>'0000','Message'=>'Success',
        'approvalKey'=>'key+base64=','PayUrl'=>$pay_url));
    $before = $calls;
    ob_start();
    include dirname(dirname(__FILE__)).'/mobile/shop/kcp/rest_register.php';
    $body = ob_get_clean();
    verify($body === ($accepted ? '0000,key+base64=,'.$pay_url.',Success' :
        'R999,,,KCP REST 거래등록을 완료하지 못했습니다. 설정 및 주문 정보를 확인해 주십시오.'));
    verify($calls === $before + 1);
    verify($url === ($test_mode ? 'https://testsmpay.kcp.co.kr/trade/register.do' :
        'https://smpay.kcp.co.kr/trade/register.do'));
}
unlink($fixture_lib.'/shop_order_access.lib.php');
rmdir($fixture_lib);
echo 'OK '.$count." transport checks\n";

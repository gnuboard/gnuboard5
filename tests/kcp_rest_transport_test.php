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
echo 'OK '.$count." transport checks\n";

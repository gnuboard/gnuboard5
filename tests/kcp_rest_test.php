<?php
if (PHP_SAPI !== 'cli') exit;
// 외부 KCP/DB 연결 없이 실행: php tests/kcp_rest_test.php [desktop|mobile]
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) { throw new Exception($message.' at '.$file.':'.$line); });
define('_GNUBOARD_', true);
define('G5_PATH', dirname(dirname(__FILE__)));
define('G5_LIB_PATH', G5_PATH.'/lib');
$default = array('de_kcp_payment_api'=>'rest');
require G5_LIB_PATH.'/kcp_rest.lib.php';
$checks = 0;
function check($ok, $name) { global $checks; if (!$ok) throw new Exception('FAIL: '.$name); $checks++; }
function rejects($fn, $name) { $thrown = false; try { $fn(); } catch (Exception $e) { $thrown = true; } check($thrown, $name); }
class RestFixture extends G5_KCP_REST_Client {
    var $requests = array();
    var $response;
    function post($operation, $data) { $this->requests[] = array($operation, $data); return $this->response; }
}
$temp = sys_get_temp_dir().'/g5-kcp-rest-'.uniqid('', true);
mkdir($temp, 0700);
$key = openssl_pkey_new(array('private_key_bits'=>2048, 'private_key_type'=>OPENSSL_KEYTYPE_RSA));
$csr = openssl_csr_new(array('commonName'=>'KCP offline fixture'), $key);
$cert = openssl_csr_sign($csr, null, $key, 1);
openssl_x509_export($cert, $pem);
openssl_pkey_export($key, $private, 'fixture-password');
file_put_contents($temp.'/cert.pem', $pem);
file_put_contents($temp.'/key.pem', $private);
$config = array('test'=>true,'cert'=>$temp.'/cert.pem','key'=>$temp.'/key.pem','password'=>'fixture-password');
$c = new RestFixture($config);
check(kcp_rest_enabled(), 'opt in');
define('G5_KCP_PAYMENT_API', 'rest'); // 이전 서버 상수가 관리자 선택을 덮어쓰면 안 된다.
$default = array();
check(!kcp_rest_enabled(), 'missing setting keeps legacy');
$default['de_kcp_payment_api'] = 'legacy';
check(!kcp_rest_enabled(), 'legacy overrides obsolete constant');
$default['de_kcp_payment_api'] = 'invalid';
check(!kcp_rest_enabled(), 'invalid setting keeps legacy');
$default['de_kcp_payment_api'] = 'rest';
check(kcp_rest_enabled(), 'administrator can enable REST');
check($c->endpoint('payment') === 'https://stg-spl.kcp.co.kr/gw/enc/v1/payment', 'test payment endpoint');
check($c->endpoint('cancel') === 'https://stg-spl.kcp.co.kr/gw/mod/v1/cancel', 'test cancel endpoint');
check($c->endpoint('register') === 'https://testsmpay.kcp.co.kr/trade/register.do', 'test register endpoint');
$live = new RestFixture(array_merge($config, array('test'=>false)));
check($live->endpoint('payment') === 'https://spl.kcp.co.kr/gw/enc/v1/payment', 'production endpoint');
check($live->endpoint('cancel') === 'https://spl.kcp.co.kr/gw/mod/v1/cancel', 'production cancel');
check($live->endpoint('register') === 'https://smpay.kcp.co.kr/trade/register.do', 'production register');
rejects(function () use ($c) { $c->endpoint('https://attacker.invalid'); }, 'fixed endpoint allowlist');
rejects(function () use ($c) { $c->secret_file('file:///tmp/cert'); }, 'no wrappers');
rejects(function () use ($c) { $c->secret_file(__FILE__); }, 'no web root credentials');
$credentials = $c->credentials('T0000');
check(strpos($credentials['kcp_cert_info'], "\n") === false, 'certificate serialization');
$good = array('res_cd'=>'0000','res_msg'=>'정상처리','tno'=>'25536002322422','amount'=>'1004','pay_method'=>'PACA','card_mny'=>'0','coupon_mny'=>'1004','card_name'=>'국민카드');
$c->response = $good;
$response = $c->payment('T0000','202610290001',1004,'PACA','00100000','enc+data=','enc/info');
$request = $c->requests[0][1];
check($request['ordr_no'] === '202610290001' && $request['ordr_mony'] === '1004', 'server order and amount');
check($request['enc_data'] === 'enc+data=' && $request['enc_info'] === 'enc/info', 'opaque encryption preserved');
check($response['amount'] === '1004' && $response['card_mny'] === '0', 'coupon-only total preserved');
foreach (array('PABK','PAVC','PAMC','PACA') as $type) {
    $c->response = array_merge($good, array('pay_method'=>$type, 'res_cd'=>$type === 'PAVC' ? 'V000' : '0000'));
    check($c->payment('T0000','202610290001',1004,$type,'00100000','encrypted','info')['pay_method'] === $type, 'payment '.$type);
}
foreach (array('PAKM','PANP','PATO','PASG','PASM') as $type) {
    $c->response = array_merge($good, array('pay_method'=>$type));
    check($c->payment('T0000','202610290001',1004,'PACA','00100000','encrypted','info')['pay_method'] === $type, 'easy pay '.$type);
}
foreach (array(array('amount'=>'1'), array('amount'=>'1004x'), array('tno'=>''), array('pay_method'=>'PABK'), array('res_cd'=>'V000')) as $bad) {
    $c->response = array_merge($good, $bad);
    rejects(function () use ($c) { $c->payment('T0000','202610290001',1004,'PACA','00100000','encrypted','info'); }, 'approval mismatch');
}
$before = count($c->requests);
rejects(function () use ($c) { $c->payment('T0000','202610290001',0,'PACA','00100000','encrypted','info'); }, 'zero amount');
rejects(function () use ($c) { $c->payment('T0000','202610290001',1004,'INVALID','00100000','encrypted','info'); }, 'invalid method');
check(count($c->requests) === $before, 'invalid input never sent');
foreach (array('', 'null', '[]', '<html>bad gateway</html>', '{"res_cd":0}', '{"res_cd":"0000","tno":[]}') as $raw) {
    rejects(function () use ($c, $raw) { $c->decode('payment', $raw); }, 'malformed response');
}
check($c->decode('payment', '{"res_cd":"1234","res_msg":"declined"}')['res_cd'] === '1234', 'decline retained');
$cancel = array('tno'=>'25536002322422','mod_type'=>'STSC','mod_desc'=>'취소');
$c->response = array('res_cd'=>'0000','tno'=>$cancel['tno']);
$c->cancel('T0000', $cancel);
$last = end($c->requests);
check(openssl_verify('T0000^'.$cancel['tno'].'^STSC', base64_decode($last[1]['kcp_sign_data']), $pem, OPENSSL_ALGO_SHA256) === 1, 'RSA SHA256 cancellation signature');
foreach (array('RN07','STPA','STPC') as $type) {
    $c->response = array('res_cd'=>'0000','tno'=>$cancel['tno'],'mod_mny'=>'500','rem_mny'=>'504');
    $part = array_merge($cancel, array('mod_type'=>$type,'mod_mny'=>'500','rem_mny'=>'1004','tax_flag'=>'TG03','mod_tax_mny'=>'454','mod_vat_mny'=>'46','mod_free_mny'=>'0'));
    $result = $c->cancel('T0000', $part);
    $last = end($c->requests);
    check($last[1]['mod_type'] === 'STPC' && $last[1]['mod_free_mny'] === '0', 'partial type and zero tax');
    check($result['panc_mod_mny'] === '500' && $result['panc_rem_mny'] === '504', 'partial legacy result');
}
$part['mod_tax_mny'] = '450';
rejects(function () use ($c, $part) { $c->cancel('T0000', $part); }, 'partial tax mismatch');
$part['mod_tax_mny'] = '454';
$c->response['rem_mny'] = '505';
rejects(function () use ($c, $part) { $c->cancel('T0000', $part); }, 'partial response mismatch');
$c->response = array('res_cd'=>'0000','tno'=>$cancel['tno']);
$c->cancel('T0000', array_merge($cancel, array('mod_type'=>'STE1','deli_numb'=>'123','deli_corp'=>'한글택배')));
$last = end($c->requests);
check($last[1]['deli_corp'] === '한글택배', 'escrow delivery fields');
$c->response = array('Code'=>'0000','approvalKey'=>'key+base64=','PayUrl'=>'https://testsmpay.kcp.co.kr/pay/mobileGW.kcp');
check($c->register(array('ordr_idxx'=>'202610290001'))['Code'] === '0000', 'mobile registration');
foreach (array('http://testsmpay.kcp.co.kr/pay', 'https://testsmpay.kcp.co.kr.evil.invalid/pay', 'https://smpay.kcp.co.kr/pay') as $url) {
    $c->response['PayUrl'] = $url;
    rejects(function () use ($c) { $c->register(array()); }, 'registration redirect allowlist');
}
$converted = kcp_rest_legacy_result($good + array('easypoint_mny'=>'1004','app_easypoint_time'=>'20261029120000'));
check(iconv('CP949','UTF-8',$converted['card_name']) === '국민카드', 'legacy output encoding');
check($converted['kakaomny_mny'] === '1004' && $converted['app_time'] === '20261029120000', 'easy money result aliases');
check(kcp_rest_fields('mod_desc=a+b=c'.chr(31))['mod_desc'] === 'a+b=c', 'legacy fields preserve plus and equals');

// 실제 기존 라이브러리 진입점에서 REST 실패 후 바이너리를 실행하지 않는지 검사한다.
$default = array('de_kcp_payment_api'=>'rest','de_card_test'=>1);
function get_session($key) { return '202610290001'; }
$order_price = 1004; $od_settle_case = '신용카드';
$variant = isset($argv[1]) && $argv[1] === 'mobile' ? 'mobile/shop' : 'shop';
require G5_PATH.'/'.$variant.'/kcp/pp_ax_hub_lib.php';
$legacy = new C_PP_CLI_T();
$legacy->mf_clear(); $legacy->mf_set_encx_data('data','info');
$legacy->mf_do_tx('', '', 'T0000', '', '00100000', '', '', '', '', '202610290001', '', '', '', 0, '', '');
check($legacy->m_res_cd === 'R999', 'legacy approval routes to REST and fails closed');
$legacy->mf_clear(); $legacy->mf_set_modx_data('tno','25536002322422'); $legacy->mf_set_modx_data('mod_type','STSC');
$legacy->mf_do_tx('', '', 'T0000', '', '00200000', '', '', '', '', '', '', '', '', 0, '', '');
check($legacy->m_res_cd === 'R999', 'legacy cancel routes to REST and fails closed');
require G5_PATH.'/shop/kcp/pp_cli_hub_lib.php';
$legacy = new C_PAYPLUS_CLI_T(); $legacy->mf_clear();
$legacy->mf_set_modx_data('tno','25536002322422'); $legacy->mf_set_modx_data('mod_type','RN07');
$legacy->mf_set_modx_data('mod_mny','500'); $legacy->mf_set_modx_data('rem_mny','1004');
$legacy->mf_do_tx('', '', 'T0000', '', '00200000', '', '', '', '', '', '', '', '', 0, '', '');
check($legacy->m_res_cd === 'R999', 'legacy partial routes to REST and fails closed');
// 현금영수증 전송 규격, 서명, 실패 시 모듈 재호출 차단.
$cash = array('tr_code'=>'0','id_info'=>'01012345678','trad_time'=>'20261029120000',
    'ordr_idxx'=>'202610290001','amt_tot'=>1100,'amt_sup'=>1000,'amt_tax'=>100,'amt_svc'=>0,
    'good_name'=>'한글 상품','buyr_name'=>'테스트','buyr_mail'=>'test@example.com');
$cash_response = array('res_cd'=>'0000','cash_no'=>'26552077660459','receipt_no'=>'123456789','app_time'=>'20261029120000','reg_stat'=>'NTRW','reg_desc'=>'등록 완료');
$c->response = $cash_response;
$c->requests = array();
check($c->cash_issue('T0000', $cash) === $cash_response, 'cash issue response');
$request = $c->requests[0][1];
check($c->requests[0][0] === 'cash_issue' && $request['pay_method'] === 'CASH' && $request['user_type'] === 'PGNW' && $request['corp_type'] === '0', 'cash issue protocol');
check($request['good_name'] === '한글 상품' && $request['amt_tot'] === '1100' && !isset($request['pay_type']), 'cash UTF8 and server amount');
check($c->endpoint('cash_issue') === 'https://stg-spl.kcp.co.kr/gw/hub/v1/payment', 'cash test endpoint');
check($live->endpoint('cash_issue') === 'https://spl.kcp.co.kr/gw/hub/v1/payment', 'cash production endpoint');
check($c->cash_cancel('T0000', $cash_response['cash_no']) === $cash_response, 'cash cancel response');
$request = $c->requests[1][1];
check($request['mod_gubn'] === 'MG01' && $request['mod_value'] === $cash_response['cash_no'] && !isset($request['tno']), 'cash cancel identifier');
check(openssl_verify('T0000^'.$cash_response['cash_no'].'^STSC', base64_decode($request['kcp_sign_data']), $pem, OPENSSL_ALGO_SHA256) === 1, 'cash cancel signature');
rejects(function () use ($c, $cash) { $cash['amt_tot'] = 1; $c->cash_issue('T0000', $cash); }, 'cash inconsistent total');
rejects(function () use ($c) { $c->cash_cancel('T0000', 'invalid'); }, 'cash invalid identifier');
$c->response['cash_no'] = '26552077660458';
rejects(function () use ($c, $cash_response) { $c->cash_cancel('T0000', $cash_response['cash_no']); }, 'cash cancel response mismatch');
$c->response = array('res_cd'=>'9999');
check($c->cash_issue('T0000', $cash)['res_cd'] === '9999', 'cash rejection preserved');
foreach (array('07010000','07020000') as $tran) {
    $legacy->mf_clear();
    $legacy->mf_do_tx('', '', 'T0000', '', $tran, '', '', '', '', '', '', '', '', 0, '', '');
    check($legacy->m_res_cd === 'R999', 'cash legacy entrypoint cannot run binary in REST');
}
require G5_LIB_PATH.'/kcp_cash.lib.php';
rejects(function () use ($cash) { kcp_cash_gateway('pay', $cash); }, 'cash gateway REST credentials required without legacy fallback');
unlink($temp.'/cert.pem'); unlink($temp.'/key.pem'); rmdir($temp);
echo 'OK '.$checks.' checks ('.$variant.")\n";

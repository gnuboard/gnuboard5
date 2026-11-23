<?php
// 실행: php tests/kve_3248_3250.php
// DB 없이 KVE-2026-3248~3250(서버 간 HTTPS 인증서 검증, 이니시스 현금영수증·간편인증 응답 검증, 소셜 로그인 state·응답 검증)을 검사한다.
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
define('_GNUBOARD_', true);
define('G5_SERVER_TIME', time());
$checks = 0; $fails = 0;
// 실패해도 계속 검사해 KVE별 실패 항목을 모두 보여준다. 하나라도 실패하면 종료 코드 1.
function check($ok, $name) {
    if (!$ok) { echo 'FAIL '.$name."\n"; $GLOBALS['fails']++; return; }
    echo 'PASS '.$name."\n";
    $GLOBALS['checks']++;
}
function src($path) { return str_replace("\r\n", "\n", file_get_contents($GLOBALS['root'].'/'.$path)); }
function func_src($path, $name, $indent) {
    if (!preg_match('/^'.$indent.'function '.$name.'\(.*?^'.$indent.'\}/ms', src($path), $m)) throw new Exception('함수 찾기 실패: '.$name);
    return $m[0];
}

// ---------- 공통: TLS 검증 해제 재발 방지(정적 검사) ----------
// 예외: LG U+ XPay는 mall.conf 설정(verify_cert/verify_host)을 따른다. 기본 설정은 둘 다 1이다.
$allow = array('plugin/lgxpay/lgdacom/XPayClient.php', 'plugin/lgxpay/lgdacom/XPayClient4DB.php');
$patterns = array(
    'VERIFYPEER false/0'    => '/CURLOPT_SSL_VERIFYPEER\s*(?:,|=>)\s*(?:false|0|null)\b/i',
    'VERIFYHOST false/0/1'  => '/CURLOPT_SSL_VERIFYHOST\s*(?:,|=>)\s*(?:false|0|1|null)\b/i',
    'VERIFYPEER 변수 지정'  => '/CURLOPT_SSL_VERIFY(?:PEER|HOST)\s*,\s*\$/i',
    'verify_peer false'     => '/[\'"]verify_peer(?:_name)?[\'"]\s*=>\s*(?:false|0)\b/i',
    'allow_self_signed'     => '/[\'"]allow_self_signed[\'"]\s*=>\s*(?:true|1)\b/i',
    '검증 끄는 기본값'      => '/\$(?:curl_)?ssl_verify(?:peer|host)\s*=\s*(?:false|0)\b/i',
);
$found = array();
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($it as $file) {
    $rel = str_replace('\\', '/', substr($file->getPathname(), strlen($root) + 1));
    if (substr($rel, -4) !== '.php' || preg_match('#^(tests|\.git|data|vendor)/#', $rel) || in_array($rel, $allow, true)) continue;
    $s = file_get_contents($file->getPathname());
    foreach ($patterns as $label => $re) {
        if (preg_match_all($re, $s, $m)) $found[] = $rel.' ('.$label.')';
    }
}
check(!$found, '공통 저장소 전체에서 TLS 검증을 끄는 코드 없음(LG XPay 설정 분기만 예외) '.implode(', ', $found));
foreach ($allow as $rel) {
    check(preg_match('/verify_cert/', src($rel)) && preg_match('/verify_cert\s*=\s*1/', src('plugin/lgxpay/lgdacom/conf/mall.conf')), '공통 예외 '.$rel.'는 설정값을 따르고 기본 설정은 검증함');
}

// ---------- 공통 함수 ----------
foreach (array('is_inicis_url_return', 'g5_tls_ca_file', 'g5_curl_tls_verify', 'g5_stream_tls_context_options', 'g5_tls_http_post') as $fn) {
    eval(func_src('lib/common.lib.php', $fn, ''));
}
$ch = curl_init();
g5_curl_tls_verify($ch);
// curl_getinfo로는 옵션을 읽을 수 없으므로 함수 본문을 확인한다.
$body = func_src('lib/common.lib.php', 'g5_curl_tls_verify', '');
check(strpos($body, 'CURLOPT_SSL_VERIFYPEER, true') !== false && strpos($body, 'CURLOPT_SSL_VERIFYHOST, 2') !== false && strpos($body, '$_') === false,
    '공통 g5_curl_tls_verify가 VERIFYPEER=true, VERIFYHOST=2 지정(요청 값 미사용)');
check(g5_tls_ca_file() === '', '공통 G5_TLS_CA_FILE 미정의 시 기본 CA 사용');
$opt = g5_stream_tls_context_options('stdpay.inicis.com');
check($opt['ssl']['verify_peer'] === true && $opt['ssl']['verify_peer_name'] === true && $opt['ssl']['allow_self_signed'] === false && $opt['ssl']['peer_name'] === 'stdpay.inicis.com',
    '공통 소켓 연결 TLS 컨텍스트가 인증서·호스트명 검증');
$post = func_src('lib/common.lib.php', 'g5_tls_http_post', '');
check(strpos($post, 'g5_curl_tls_verify($ch)') !== false && strpos($post, 'curl_errno') !== false && strpos($post, '$status < 200') !== false && strpos($post, 'FOLLOWLOCATION, false') !== false,
    '공통 g5_tls_http_post가 검증·전송 오류·HTTP 상태를 확인하고 리다이렉트를 따르지 않음');
check(g5_tls_http_post('http://kssa.inicis.com/', '') === false, '공통 g5_tls_http_post는 https가 아니면 요청하지 않음');
$hc = src('shop/inicis/libs/HttpClient.php');
check(strpos($hc, 'stream_context_create(g5_stream_tls_context_options($this->host))') !== false && strpos($hc, '@fsockopen') === false,
    '공통 이니시스 웹표준 승인 HttpClient가 검증 컨텍스트로 연결');
foreach (array('shop/naverpay/naverpay_order.php', 'shop/naverpay/naverpay_wish.php') as $rel) {
    check(strpos(src($rel), 'g5_stream_tls_context_options($req_host)') !== false && strpos(src($rel), '@fsockopen') === false, '공통 '.$rel.' 검증 컨텍스트로 연결');
}

// is_inicis_url_return: https, 포트·사용자 정보 없음, 허용 호스트 정확히 일치
$hosts = array('kssa.inicis.com', 'fcsa.inicis.com');
$ok = array('https://kssa.inicis.com/api/v1/result', 'https://FCSA.inicis.com/x?y=1', 'https://kssa.inicis.com');
$bad = array('http://kssa.inicis.com/api', 'https://evil.inicis.com/api', 'https://kssa.inicis.com:8443/api', 'https://u:p@kssa.inicis.com/',
    'https://kssa.inicis.com.evil.test/', 'https://evil.test/?h=kssa.inicis.com', 'https://evil.test#@kssa.inicis.com/', "https://kssa.inicis.com/\n",
    'https://kssa.inicis.com\\@evil.test/', '//kssa.inicis.com/', 'javascript://kssa.inicis.com/', array('https://kssa.inicis.com/'), '');
$r = array();
foreach ($ok as $u) if (is_inicis_url_return($u, $hosts) !== $u) $r[] = 'ok:'.$u;
foreach ($bad as $u) if (is_inicis_url_return($u, $hosts) !== '') $r[] = 'bad:'.json_encode($u);
check(!$r, '3249 is_inicis_url_return 허용 목록: https·정확한 호스트만 통과 '.implode(' ', $r));
check(is_inicis_url_return('https://fcstdpay.inicis.com/api/payAuth') !== '' && is_inicis_url_return('http://fcstdpay.inicis.com/api/payAuth') === ''
    && is_inicis_url_return('https://inicis.com.evil.test/') === '' && is_inicis_url_return('https://xinicis.com/') === '',
    '3249 is_inicis_url_return 기본(목록 없음): https와 .inicis.com 하위 호스트만 통과');
check(strpos($hc, "is_inicis_url_return(\$url, array('fcstdpay.inicis.com', 'ksstdpay.inicis.com', 'stgstdpay.inicis.com'))") !== false,
    '3249 HttpClient 다른 사용처도 승인·망취소 호스트로 고정');

// ---------- KVE-2026-3248 이니시스 현금영수증 ----------
$tx = src('shop/inicis/taxsave_result.php');
// KVE-2026-3247 이후에는 요청·응답 처리가 공통 현금영수증 라이브러리의 이니시스 게이트웨이로 옮겨졌다.
$cash_lib = is_file($root.'/lib/shop_cash.lib.php') ? src('lib/shop_cash.lib.php') : '';
$txg = $tx.($cash_lib !== '' ? func_src('lib/shop_cash.lib.php', 'shop_cash_gateway_inicis', '') : '');
check(preg_match('/g5_tls_http_post\((?:\$url|"https:\/\/iniapi\.inicis\.com\/api\/v1\/receipt")/', $txg) && !preg_match('/CURLOPT_SSL_VERIFYPEER/', $txg), '3248 현금영수증 요청이 공통 검증 함수 사용');
check(preg_match('/if \(!is_array\(\$(?:ini_result|res)\).*?(?:alert\(|return array\(\'ok\'=>false, \'definite\'=>false\))/s', $txg) && preg_match('/\$result_code (?:===|!==) \'00\'/', $txg), '3248 전송·JSON 실패 시 상태 변경 전에 중단');
check(strpos($txg, "'authNo'     => '/\\A[A-Za-z0-9]{1,20}\\z/'") !== false && strpos($txg, 'checkdate(') !== false && preg_match('/\(string\) ?\$(?:ini_result|res)\[\'crPrice\'\] !== \(string\) ?\$crPrice/', $txg),
    '3248 승인번호·거래번호·승인일시 형식과 응답 금액 확인');
check(preg_match('/\$result = sql_query\(\$sql, false\);\s*if \(!\$result\) \{\s*\/\/[^\n]*\n\s*alert\(/', $tx)
    || (strpos($cash_lib, "shop_cash_update(\$table, \"{\$p}_cash=1, ") !== false && strpos($cash_lib, "'shop_cash_state'=>'reconcile'") !== false),
    '3248 DB 저장 실패를 성공으로 표시하지 않음');
check(!preg_match('/echo\s*\(?\s*\$ini_result\[/', $tx) && substr_count($tx, '<?php echo get_text(') === 5 && strpos($tx, 'encodeURIComponent(<?php echo get_js_safe_string($cash_tid); ?>)') !== false,
    '3248 결과 화면은 get_text/get_js_safe_string으로만 출력(응답 원문 미출력)');

// ---------- KVE-2026-3249 이니시스 간편인증 ----------
$GLOBALS['__sess'] = array();
function set_session($k, $v) { $GLOBALS['__sess'][$k] = $v; }
function get_session($k) { return isset($GLOBALS['__sess'][$k]) ? $GLOBALS['__sess'][$k] : ''; }
function cut_str($s, $n, $t = '') { return mb_substr($s, 0, $n); }
$config = array('cf_cert_use' => 1, 'cf_cert_kg_mid' => 'abcdefg');
require $root.'/plugin/inicert/libs/inicert.lib.php';
inicert_save_request('SIR_1_2', 'register', 'SRAiasTest');
$saved = inicert_consume_request('register');
check(is_array($saved) && $saved['mTxId'] === 'SIR_1_2' && inicert_consume_request('register') === false, '3249 시작 거래는 한 번만 꺼낼 수 있음');
inicert_save_request('SIR_1_2', 'find', 'SRAiasTest');
check(inicert_consume_request('register') === false && get_session('ss_inicert_request') === '', '3249 용도가 다르면 거부하고 저장 값 폐기');
$GLOBALS['__sess']['ss_inicert_request'] = array('mTxId' => 'SIR_1', 'purpose' => 'register', 'mid' => 'SRAiasTest', 'time' => G5_SERVER_TIME - 601);
check(inicert_consume_request('register') === false, '3249 10분 지난 거래 거부');
inicert_save_request('SIR_1', 'register', 'SRAiasTest');
$config['cf_cert_use'] = 2;
check(inicert_consume_request('register') === false, '3249 시작 후 설정 MID가 바뀌면 거부');
$config['cf_cert_use'] = 1;
$saved = array('mTxId' => 'SIR_1', 'purpose' => 'register', 'mid' => 'SRAiasTest', 'time' => G5_SERVER_TIME);
$base = array('txId' => 'ISA01_SRAiasTest0123456789', 'resultCode' => '0000', 'authRequestUrl' => 'https://kssa.inicis.com/api', 'token' => 'AAAAAAAAAAAAAAAAAAAAAA==');
$req = inicert_check_return($saved, $base);
check(is_array($req) && $req['mid'] === 'SRAiasTest' && $req['url'] === $base['authRequestUrl'], '3249 정상 결과 POST 통과(조회 MID는 설정 값)');
$cases = array('다른 MID' => array('txId' => 'ISA01_SRAotherMID0123456789'), '형식' => array('txId' => 'ISA01_SRAiasTest<>'), 'http' => array('authRequestUrl' => 'http://kssa.inicis.com/api'),
    '하위 도메인' => array('authRequestUrl' => 'https://x.inicis.com/api'), 'mTxId' => array('mTxId' => 'SIR_2'), 'token' => array('token' => '<x>'), '배열' => array('txId' => array('x')));
$r = array();
foreach ($cases as $label => $c) if (is_array(inicert_check_return($saved, array_merge($base, $c)))) $r[] = $label;
check(!$r, '3249 MID·형식·URL·mTxId 불일치 거부 '.implode(',', $r));
check(inicert_valid_identity('홍"길\'동', '010-1234-5678', '19900101', 'CI') && !inicert_valid_identity('<b>', '01012345678', '19900101', 'CI')
    && !inicert_valid_identity('홍길동', '<script>', '19900101', 'CI') && !inicert_valid_identity('홍길동', '01012345678', '19901340', 'CI') && !inicert_valid_identity('홍길동', '01012345678', '19900101', ''),
    '3249 복호화한 이름·전화·생년월일·CI 형식 확인');
foreach (array('plugin/inicert/ini_result.php' => 'register', 'plugin/inicert/ini_find_result.php' => 'find') as $rel => $purpose) {
    $s = src($rel);
    check(strpos($s, "inicert_consume_request('".$purpose."')") < strpos($s, '$_POST["resultCode"]') && strpos($s, 'inicert_fetch_result(') !== false
        && strpos($s, 'curl_init') === false && strpos($s, "\$_POST['token']") === false, '3249 '.$rel.' 거래 소모 후 공통 검증 조회');
}
check(strpos(src('plugin/inicert/ini_request.php'), 'inicert_save_request($mTxId, $_GET[\'pageType\'], $mid);') !== false, '3249 ini_request.php가 거래 정보를 세션에 저장');
$s = src('plugin/inicert/ini_result.php');
check(!preg_match('/"<\?php echo \$/', $s) && substr_count($s, '<?php echo get_js_safe_string(') === 4, '3249 이름·전화번호 등 JS 리터럴 인코딩');
$lib = src('plugin/inicert/libs/inicert.lib.php');
check(strpos($lib, "g5_tls_http_post(\$req['url']") !== false && strpos($lib, "is_inicis_url_return(\$post['authRequestUrl'], inicert_allowed_hosts())") !== false
    && strpos($lib, "return array('kssa.inicis.com', 'fcsa.inicis.com');") !== false, '3249 고정 HTTPS 허용 목록과 검증 조회 사용');

// ---------- KVE-2026-3250 소셜 로그인 ----------
class Hybrid_Logger { static function info() {} static function debug() {} static function error() {} }
require $root.'/plugin/social/Hybrid/thirdparty/OAuth/OAuth2Client.php';
$c = new OAuth2Client('id', 'secret');
check($c->curl_ssl_verifypeer === true && $c->curl_ssl_verifyhost === 2, '3250 OAuth2Client 기본값 verifypeer=true, verifyhost=2');
$o2 = src('plugin/social/Hybrid/thirdparty/OAuth/OAuth2Client.php');
check(strpos($o2, 'g5_curl_tls_verify($ch);') !== false && strpos($o2, '$this->curl_ssl_verifypeer )') === false, '3250 OAuth2Client 요청은 속성 값과 관계없이 검증');
check(preg_match('/if\( \$response === false \|\| ! \$this->isHttpSuccess\(\) \)\{\s*throw/', $o2) && preg_match('/if\( \$response === false \|\| ! \$this->isHttpSuccess\(\) \)\{\s*return \$this->response = null;/', $o2),
    '3250 OAuth2Client 토큰·API 응답의 전송 오류·HTTP 2xx 아님은 실패 처리');
$c->http_code = 200; $a = $c->isHttpSuccess(); $c->http_code = 302; $b = $c->isHttpSuccess(); $c->http_code = 0;
check($a && !$b && !$c->isHttpSuccess(), '3250 OAuth2Client isHttpSuccess 2xx만 성공');
$o1 = src('plugin/social/Hybrid/thirdparty/OAuth/OAuth1Client.php');
check(strpos($o1, 'public $curl_ssl_verifypeer   = true;') !== false && strpos($o1, 'g5_curl_tls_verify( $ci );') !== false, '3250 OAuth1Client 검증 기본값·요청 적용');
foreach (array('Naver', 'Kakao') as $p) {
    $s = src('plugin/social/Hybrid/Providers/'.$p.'.php');
    check(strpos($s, 'g5_curl_tls_verify($ch);') !== false && strpos($s, 'curl_ssl_verifypeer') === false && strpos($s, "(int) \$this->http_code < 200") !== false && strpos($s, 'curl_errno($ch)') !== false,
        '3250 '.$p.' 자체 요청 검증·전송 오류·HTTP 상태 확인');
}
check(strpos(src('plugin/sns/twitter/twitteroauth/twitteroauth.php'), 'g5_curl_tls_verify($ci);') !== false, '3250 SNS Twitter 클라이언트 검증');
check(strpos(src('plugin/social/Hybrid/thirdparty/OpenID/LightOpenID.php'), 'g5_curl_tls_verify($curl);') !== false, '3250 LightOpenID 검증');

// state: 생성·대조·1회용
class Hybrid_FakeStorage { public $d = array(); function get($k) { return isset($this->d[$k]) ? $this->d[$k] : null; } function set($k, $v) { $this->d[$k] = $v; } function delete($k) { unset($this->d[$k]); } }
class Hybrid_Auth { static $s; static function storage() { return self::$s; } }
Hybrid_Auth::$s = new Hybrid_FakeStorage();
class Hybrid_Provider_Model { public $providerId; }
function get_random_token_string($n) { return bin2hex(random_bytes($n)); }
eval(func_src('lib/pbkdf2.compat.php', 'slow_equals', ''));
require $root.'/plugin/social/Hybrid/Provider_Model_OAuth2.php';
$rc = new ReflectionClass('Hybrid_Provider_Model_OAuth2');
$m = $rc->newInstanceWithoutConstructor();
$m->providerId = 'Naver';
$st = $m->createAuthState();
$fail = function ($state) use ($m) { if ($state === null) unset($_REQUEST['state']); else $_REQUEST['state'] = $state; try { $m->checkAuthState(); return false; } catch (Exception $e) { return true; } };
$r1 = $fail('x' . $st); $st = $m->createAuthState(); $r2 = $fail(null); $st = $m->createAuthState(); $r3 = !$fail($st); $r4 = $fail($st);
$st = $m->createAuthState(); Hybrid_Auth::$s->d['hauth_session.Naver.g5_oauth_state']['time'] = time() - 1801; $r5 = $fail($st);
check(strlen($st) === 64 && $r1 && $r2 && $r3 && $r4 && $r5, '3250 state: 다른 값·없음·재사용·만료 거부, 일치 시 통과');
$prov = array('Naver' => 2, 'Kakao' => 1, 'Facebook' => 2, 'Payco' => 2, 'Google' => 1);
foreach ($prov as $p => $n) {
    $s = src('plugin/social/Hybrid/Providers/'.$p.'.php');
    check(substr_count($s, 'createAuthState()') + substr_count($s, 'checkAuthState()') === $n, '3250 '.$p.' 인가 요청·콜백 state 적용');
}
$pm = src('plugin/social/Hybrid/Provider_Model_OAuth2.php');
check(preg_match('/function loginBegin\(\) \{.*?createAuthState\(\)/s', $pm) && preg_match('/function loginFinish\(\) \{\s*\$this->checkAuthState\(\);/', $pm), '3250 기본 OAuth2 loginBegin/loginFinish state 적용(Kakao·Google 기본 경로)');
check(strpos(src('plugin/social/Hybrid/Provider_Model_OAuth1.php'), 'slow_equals($request_token, $oauth_token)') !== false, '3250 OAuth1 콜백 oauth_token을 세션 요청 토큰과 대조');
eval(func_src('plugin/social/includes/functions.php', 'social_is_valid_identifier', ''));
$okp = array('123456789', 12345678901, 'AbC-_x.9', '1|2');
$badp = array('', '<x>', 'a b', "a\n", str_repeat('a', 256), 1.5, null, array('1'));
$r = array();
foreach ($okp as $v) { $p = new stdClass(); $p->identifier = $v; if (!social_is_valid_identifier($p)) $r[] = 'ok:'.json_encode($v); }
foreach ($badp as $v) { $p = new stdClass(); $p->identifier = $v; if (social_is_valid_identifier($p)) $r[] = 'bad:'.json_encode($v); }
check(!$r, '3250 제공자 식별자 형식 확인 '.implode(' ', $r));
check(substr_count(src('plugin/social/includes/functions.php'), 'if( ! social_is_valid_identifier($user_profile) ){') === 2, '3250 프로필 사용 두 경로에서 식별자 확인');

echo 'TOTAL '.$checks.($fails ? ' FAIL '.$fails : '')."\n";
exit($fails ? 1 : 0);

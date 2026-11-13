<?php
// 실행: php tests/kve_3198_3200.php (DB 없이 KVE-2026-3198 토큰 검증과 KVE-2026-3200 제거 상태를 검사)
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
define('_GNUBOARD_', true);
$checks = 0;
function check($ok, $name) {
    if (!$ok) throw new Exception('FAIL '.$name);
    echo 'PASS '.$name."\n";
    $GLOBALS['checks']++;
}
function src($path) { return file_get_contents($GLOBALS['root'].'/'.$path); }

// ---------- KVE-2026-3198: 토큰 함수 ----------
// slow_equals()는 lib/pbkdf2.compat.php에 있으며 lib/common.lib.php가 항상 불러온다.
if (!preg_match('/^function slow_equals\(.*?^\}/ms', src('lib/pbkdf2.compat.php'), $m)) throw new Exception('함수 찾기 실패: slow_equals');
eval($m[0]);
$lib = src('lib/common.lib.php');
foreach (array('get_token', 'get_token_error', 'check_token', '_get_token_key', '_get_token_secret') as $name) {
    if (!preg_match('/^function '.preg_quote($name, '/').'\(.*?^\}/ms', $lib, $m)) throw new Exception('함수 찾기 실패: '.$name);
    eval($m[0]);
}
function get_session($key) { return isset($_SESSION[$key]) ? $_SESSION[$key] : ''; }
function set_session($key, $value) { $_SESSION[$key] = $value; }
function get_random_token_string($length) { return bin2hex(random_bytes($length)); }
function alert($message, $url = '') { throw new RuntimeException($message); }

$_SESSION = array();
$token = get_token();
check(get_token_error($token) === '', '3198 정상 토큰 통과');
check(get_token_error($token) === '', '3198 같은 토큰 재사용 허용(쿠폰 여러 개 다운로드)');
check(get_token_error('') !== '' && get_token_error(null) !== '' && get_token_error(array($token)) !== '', '3198 빈 값·비문자열 거부');
check(get_token_error('no-dot-token') !== '', '3198 형식 오류 거부');
check(get_token_error(substr($token, 0, -1).(substr($token, -1) === 'a' ? 'b' : 'a')) !== '', '3198 변조 토큰 거부');
$dot = strpos($token, '.');
$old_time = time() - 7300;
$old = $old_time.'.'.hash_hmac('sha256', $_SESSION['ss_token_secret'].'|csrf_token|'.$old_time, _get_token_key());
check(strpos(get_token_error($old), '만료') !== false, '3198 만료 토큰 거부');
$saved = $_SESSION;
$_SESSION = array();
check(get_token_error($token) !== '', '3198 다른 세션 토큰 거부');
$_SESSION = $saved;
$_POST = array('token' => $token);
check(check_token() === true, '3198 check_token 기존 동작 유지(통과)');
$_POST = array('token' => 'invalid');
try { check_token(); $alerted = false; } catch (RuntimeException $e) { $alerted = true; }
check($alerted, '3198 check_token 기존 동작 유지(실패 시 alert)');

// ---------- KVE-2026-3198: 쿠폰 발급 경로 ----------
$ajax = src('shop/ajax.coupondownload.php');
$guard = strpos($ajax, "!== 'POST'");
$token_check = strpos($ajax, 'get_token_error(');
$first_db = min(array_filter(array(strpos($ajax, 'sql_fetch('), strpos($ajax, 'sql_query('), strpos($ajax, 'get_lock')), 'is_int'));
check($guard !== false && $token_check !== false && $guard < $first_db && $token_check < $first_db, '3198 POST·토큰 검사가 DB 조회·잠금보다 먼저 실행');
check(strpos($ajax, '$_GET') === false && strpos($ajax, "\$_POST['cz_id']") !== false, '3198 cz_id를 POST에서만 받음');
$zone = src('shop/couponzone.php');
$var_pos = strpos($zone, 'g5_couponzone_token');
$js_pos = strpos($zone, 'shop.couponzone.js');
check($var_pos !== false && $var_pos < $js_pos && strpos($zone, 'get_token()') !== false, '3198 쿠폰존이 스크립트보다 먼저 토큰 제공');
$js = src('js/shop.couponzone.js');
check(strpos($js, 'type: "POST"') !== false && strpos($js, 'token:') !== false && strpos($js, 'type: "GET"') === false, '3198 쿠폰존 JS가 POST로 토큰 전송');

// ---------- KVE-2026-3200: 현재접속자 제거 ----------
foreach (array('head.sub.php', 'theme/basic/head.sub.php') as $f) {
    $s = src($f);
    check(strpos($s, "\$g5['lo_location'] = '';") !== false && strpos($s, "\$g5['lo_url'] = '';") !== false
        && strpos($s, "addslashes(\$g5['title'])") === false, '3200 '.$f.' 위치 미계산');
}
if (!preg_match('/public static function run\(\).*?insert into \{\$g5\[\'login_table\'\]\}/s', $lib, $m)) throw new Exception('접속자 기록 코드 찾기 실패');
check(strpos($m[0], "\$g5['lo_location'] = '';") !== false && strpos($m[0], "\$g5['lo_url'] = '';") !== false, '3200 접속자 기록 직전 위치·주소 비움(테마 head.sub.php 무관)');
$cc = src('bbs/current_connect.php');
check(strpos($cc, 'login_table') === false && strpos($cc, 'sql_') === false && strpos($cc, 'alert(') !== false, '3200 current_connect.php가 접속자 정보를 조회하지 않음');
$links = array();
foreach (array('head.php', 'theme/basic/head.php', 'mobile/head.php', 'theme/basic/mobile/head.php', 'yc4_import.php', 'yc4_import_run.php') as $f) {
    if (strpos(src($f), 'current_connect.php') !== false) $links[] = $f;
    if (strpos(src($f), 'connect(') === false) $links[] = $f.'(접속자 수 없음)';
}
check(!$links, '3200 헤더 링크 제거·접속자 수 유지 '.implode(',', $links));
$skins = array('skin/connect/basic', 'theme/basic/skin/connect/basic', 'mobile/skin/connect/basic', 'theme/basic/mobile/skin/connect/basic');
$left = array();
foreach ($skins as $d) {
    if (file_exists($root.'/'.$d.'/current_connect.skin.php')) $left[] = $d.'/current_connect.skin.php';
    if (strpos(src($d.'/connect.skin.php'), 'style.css') !== false) $left[] = $d.'/connect.skin.php';
}
check(!$left, '3200 현재접속자 스킨 삭제·스타일 참조 제거 '.implode(',', $left));

echo 'TOTAL '.$checks."\n";

<?php
// 실행: php tests/kve_3199.php (DB 없이 KVE-2026-3199 본인인증 비밀번호 재설정 보호를 검사)
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
define('_GNUBOARD_', true);
define('G5_SERVER_TIME', time());
$checks = 0;
function check($ok, $name) {
    if (!$ok) throw new Exception('FAIL '.$name);
    echo 'PASS '.$name."\n";
    $GLOBALS['checks']++;
}
function src($path) { return file_get_contents($GLOBALS['root'].'/'.$path); }

// slow_equals()는 lib/pbkdf2.compat.php에 있으며 lib/common.lib.php가 항상 불러온다.
if (!preg_match('/^function slow_equals\(.*?^\}/ms', src('lib/pbkdf2.compat.php'), $m)) throw new Exception('함수 찾기 실패: slow_equals');
eval($m[0]);
$lib = src('lib/common.lib.php');
if (!preg_match("/^if \(!defined\('G5_CERT_FIND_TTL'\)\).*?;\n/m", $lib, $m)) throw new Exception('만료 시간 상수 찾기 실패');
eval($m[0]);
$config = array('cf_admin' => 'superadmin');
foreach (array('g5_set_cert_find_member', 'g5_clear_cert_find', 'g5_is_cert_find_excluded', 'g5_cert_find_member_id', 'g5_get_password_reset_token', 'g5_check_password_reset_token') as $name) {
    if (!preg_match('/^function '.$name.'\(.*?^\}/ms', $lib, $m)) throw new Exception('함수 찾기 실패: '.$name);
    eval($m[0]);
}
function get_session($key) { return isset($_SESSION[$key]) ? $_SESSION[$key] : ''; }
function set_session($key, $value) { $_SESSION[$key] = $value; }
function get_random_token_string($length) { return random_bytes($length); }

// ---------- 인증 완료 상태 ----------
$_SESSION = array();
g5_set_cert_find_member('victim');
$_SESSION['ss_cert_dupinfo'] = 'dup';
check(g5_cert_find_member_id() === 'victim', '인증 완료 회원 확인');
$_SESSION['ss_cert_find_time'] = G5_SERVER_TIME - G5_CERT_FIND_TTL - 1;
check(g5_cert_find_member_id() === '' && $_SESSION['ss_cert_mb_id'] === '' && $_SESSION['ss_cert_dupinfo'] === '', '만료된 인증 상태 거부·정리');
$_SESSION = array('ss_cert_mb_id' => 'victim', 'ss_cert_dupinfo' => 'dup');
check(g5_cert_find_member_id() === '', '인증 시각 없는 이전 세션 거부');
$_SESSION = array('ss_cert_mb_id' => array('victim'), 'ss_cert_find_time' => G5_SERVER_TIME);
check(g5_cert_find_member_id() === '', '비문자열 회원 ID 거부');

// ---------- 최고관리자 제외 ----------
check(g5_is_cert_find_excluded('superadmin') && g5_is_cert_find_excluded(' SuperAdmin ') && !g5_is_cert_find_excluded('victim'), '최고관리자 판정(대소문자·공백 무시)');
$_SESSION = array();
g5_set_cert_find_member('superadmin');
$_SESSION['ss_cert_dupinfo'] = 'dup';
check(g5_cert_find_member_id() === '' && $_SESSION['ss_cert_mb_id'] === '' && $_SESSION['ss_cert_dupinfo'] === '', '최고관리자 인증 상태 거부·정리');
$config = array('cf_admin' => '');
check(!g5_is_cert_find_excluded(''), '최고관리자 미설정 시 빈 ID를 제외 대상으로 보지 않음');
$config = array('cf_admin' => 'superadmin');

// ---------- 재설정 토큰 ----------
$_SESSION = array();
$token = g5_get_password_reset_token('victim');
check(strlen($token) >= 64 && ctype_xdigit($token), '토큰 길이·형식');
check(g5_check_password_reset_token('victim', $token) === true, '정상 토큰 통과');
check(g5_check_password_reset_token('victim', $token) === false, '사용한 토큰 재사용 거부');
$token = g5_get_password_reset_token('victim');
check(g5_check_password_reset_token('victim', 'x'.$token) === false && g5_check_password_reset_token('victim', $token) === false, '틀린 토큰 거부 후 원래 토큰도 폐기');
$token = g5_get_password_reset_token('victim');
check(g5_check_password_reset_token('other', $token) === false, '다른 회원 ID에 묶인 토큰 거부');
foreach (array('', null, array('a'), 123) as $bad) {
    g5_get_password_reset_token('victim');
    if (g5_check_password_reset_token('victim', $bad) !== false) throw new Exception('FAIL 비정상 토큰 허용');
}
check(true, '빈 값·비문자열 토큰 거부');
$token = g5_get_password_reset_token('victim');
check(g5_check_password_reset_token('', $token) === false, '인증 회원 없음 거부');
$token = g5_get_password_reset_token('victim');
$_SESSION['ss_password_reset_token']['time'] = G5_SERVER_TIME - G5_CERT_FIND_TTL - 1;
check(g5_check_password_reset_token('victim', $token) === false, '만료 토큰 거부');
$_SESSION = array();
check(g5_check_password_reset_token('victim', $token) === false, '다른 세션(토큰 없음) 거부');
g5_set_cert_find_member('victim');
g5_get_password_reset_token('victim');
g5_clear_cert_find();
check($_SESSION['ss_cert_mb_id'] === '' && $_SESSION['ss_cert_find_time'] === '' && $_SESSION['ss_password_reset_token'] === '', '정리 함수가 인증 상태·토큰 삭제');

// ---------- 변경 처리 순서 ----------
$update = src('bbs/password_reset_update.php');
$write = strpos($update, "update {\$g5['member_table']}");
$guards = array('$is_member', "cf_cert_find", "!== 'POST'", 'check_request_origin()', 'g5_check_password_reset_token(', 'g5_cert_find_member_id()');
$missing = array();
foreach ($guards as $g) { $p = strpos($update, $g); if ($p === false || $p > $write) $missing[] = $g; }
check(!$missing, '비밀번호 변경 전 로그인·설정·POST·출처·토큰·만료 검사 '.implode(',', $missing));
check(strpos($update, "trim(\$_POST['mb_password'])") !== false && substr_count($update, "trim(\$_POST['mb_password_re'])") === 1, '새 비밀번호를 mb_password에서 읽음(오타 수정)');
check(strpos($update, 'member_auto_login_table') > $write && strpos($update, 'g5_clear_cert_find()') > $write, '변경 후 자동 로그인 해제·인증 상태 정리');
check(strpos($update, "\$_SESSION['ss_cert_mb_id']") === false && strpos($update, "'{\$mb_id}'") === false, '세션 값 직접 사용·미이스케이프 SQL 없음');

// ---------- 재설정 화면·스킨·찾기 경로 ----------
$reset = src('bbs/password_reset.php');
check(strpos($reset, 'g5_cert_find_member_id()') !== false && strpos($reset, 'g5_get_password_reset_token(') !== false, '재설정 화면이 만료 확인 후 토큰 발급');
check(strpos($reset, 'g5_is_cert_find_excluded(') !== false && strpos($reset, 'g5_is_cert_find_excluded(') < strpos($reset, 'g5_get_password_reset_token('), '재설정 화면이 토큰 발급 전에 최고관리자 제외');
$skins = array('skin/member/basic', 'theme/basic/skin/member/basic', 'mobile/skin/member/basic', 'theme/basic/mobile/skin/member/basic');
$bad = array();
foreach ($skins as $d) if (!preg_match('/<input type="hidden" name="token" value="<\?php echo [^>]*\$password_reset_token/', src($d.'/password_reset.skin.php'))) $bad[] = $d;
check(!$bad, '재설정 스킨 4곳 토큰 필드 '.implode(',', $bad));
$finds = array('plugin/inicert/ini_find_result.php', 'plugin/kcpcert/find_kcpcert_result.php', 'plugin/kcpcert_v2/find_kcpcert_result.php',
    'plugin/lgxpay/find_AuthOnlyRes.php', 'plugin/okname/find_hpcert2.php', 'plugin/okname/find_ipin2.php');
$bad = array();
foreach ($finds as $f) { $s = src($f); if (strpos($s, 'g5_set_cert_find_member(') === false || preg_match("/set_session\(['\"]ss_cert_mb_id/", $s)) $bad[] = $f; }
check(!$bad, '찾기 결과 6개 경로가 인증 시각 기록 '.implode(',', $bad));

echo 'TOTAL '.$checks."\n";

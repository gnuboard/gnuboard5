<?php
// 실행: php tests/kve_3246_3253.php
// DB 없이 KVE-2026-3246 check_url_host() 판정, KVE-2026-3252 회원정보 수정 토큰·재확인 증표와 처리기 순서,
// KVE-2026-3253 KCP v1 시작 상태 대조와 출력 방식을 검사한다.
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
define('_GNUBOARD_', true);
define('G5_URL', 'http://good.example');
define('G5_SERVER_TIME', 1700000000);
$checks = 0;
function check($ok, $name) {
    if (!$ok) throw new Exception('FAIL '.$name);
    echo 'PASS '.$name."\n";
    $GLOBALS['checks']++;
}
function src($path) { return str_replace("\r\n", "\n", file_get_contents($GLOBALS['root'].'/'.$path)); }
function pos_of($s, $needle) { $p = strpos($s, $needle); return $p === false ? -1 : $p; }
function func_src($path, $name, $indent) {
    if (!preg_match('/^'.$indent.'function '.$name.'\(.*?^'.$indent.'\}/ms', src($path), $m)) throw new Exception('함수 찾기 실패: '.$name);
    return $m[0];
}
class AlertCalled extends Exception {}
function alert($msg = '', $url = '') { throw new AlertCalled($msg); }
function run_replace($tag, $value) {
    if ($tag === 'check_same_url_host') { $GLOBALS['url_rejected'] = (bool)$value; return false; }
    return $value;
}
function set_session($k, $v) { $_SESSION[$k] = $v; }
function get_session($k) { return isset($_SESSION[$k]) ? $_SESSION[$k] : ''; }
function get_random_token_string($n) { return bin2hex(openssl_random_pseudo_bytes($n)); }
eval(func_src('lib/pbkdf2.compat.php', 'slow_equals', ''));
eval(func_src('lib/common.lib.php', 'get_js_safe_string', '    '));

// ---------- KVE-2026-3246 check_url_host() ----------
$lib = src('lib/common.lib.php');
if (strpos($lib, 'function g5_is_malformed_url_host(') !== false) eval(func_src('lib/common.lib.php', 'g5_is_malformed_url_host', ''));
eval(func_src('lib/common.lib.php', 'check_url_host', ''));
// 거부되면 true. 훅(check_same_url_host)으로 차단 판정을 받아 exit 없이 확인한다. alert()로 막는 기존 차단도 거부로 본다.
function url_rejected($url, $is_redirect) {
    $_SERVER['HTTP_HOST'] = 'good.example';
    $GLOBALS['url_rejected'] = false;
    try { check_url_host($url, '', G5_URL, $is_redirect); } catch (AlertCalled $e) { return true; }
    return $GLOBALS['url_rejected'];
}
$bad = array(
    '//attacker.example:0000000443/zz', 'https://attacker.example:0000000443/zz', 'http://attacker.example:000000080/',
    '%2F%2Fattacker.example%3A0000000443%2Fzz', '%252F%252Fattacker.example%253A0000000443%252Fzz',
    '//good.example:0000000443/zz', '//good.example:44x/zz', '//good.example:123456/', 'https://good.example:99999999/',
    '\\\\attacker.example/zz', '\\/attacker.example', '/\\attacker.example', '\\\\attacker.example:0000000443/zz',
    'http:/attacker.example', 'http:attacker.example', 'http:\\\\attacker.example', 'https:', '//',
    ' //attacker.example', "\t//attacker.example", "/\t/attacker.example", 'javascript:alert(1)', 'HTTPS://ATTACKER.example/',
    '//attacker.example\\@good.example', 'http://good.example@attacker.example/', '//attacker.example:00443/zz',
);
$fail = array();
foreach (array(false, true) as $redirect) foreach ($bad as $u) if (!url_rejected($u, $redirect)) $fail[] = ($redirect ? 'R:' : 'N:').$u;
check(!$fail, '3246 외부 이동·포트 0 채움·역슬래시·호스트 없는 절대 URL 거부(일반·is_redirect) '.implode(' | ', $fail));
$good = array('', '/', '/bbs/board.php?bo_table=free&wr_id=1', './board.php', 'board.php?x=a:b&y=http://z', 'register_form.php',
    '/bbs/login.php?url=%2Fbbs%2Fboard.php', 'http://good.example/bbs/', 'https://good.example:8443/x?a=b:c', '//good.example/bbs/',
    'http://good.example:80/', '/shop/item.php?it_id=1#a:b');
$fail = array();
foreach (array(false, true) as $redirect) foreach ($good as $u) if (url_rejected($u, $redirect)) $fail[] = ($redirect ? 'R:' : 'N:').$u;
check(!$fail, '3246 같은 사이트 절대·상대 URL과 쿼리의 콜론은 그대로 허용 '.implode(' | ', $fail));
$cu = func_src('lib/common.lib.php', 'check_url_host', '');
check(substr_count($cu, 'if (!is_array($p)) {') === 2, '3246 parse_url() 두 호출 모두 배열이 아니면 차단 표시');
check(pos_of($cu, "run_replace('check_url_host_before'") < pos_of($cu, '@parse_url(')
    && pos_of($cu, 'g5_is_malformed_url_host($url, $p)') < pos_of($cu, "run_replace('check_same_url_host'")
    && pos_of($cu, 'g5_is_malformed_url_host($url, $p)') > 0, '3246 훅 호출 순서 유지(check_url_host_before → 판정 → check_same_url_host)');
check(strpos($cu, 'alert("url에 타 도메인을 지정할 수 없습니다.");') !== false, '3246 기존 차단 응답(고정 문구 + $return_url 이동) 사용');
foreach (array('bbs/alert.php', 'bbs/login.php', 'bbs/login_check.php', 'bbs/confirm.php', 'bbs/member_confirm.php',
               'bbs/member_cert_refresh_update.php', 'plugin/social/includes/functions.php') as $f)
    check(strpos(src($f), 'check_url_host(') !== false, '3246 호출 경로가 같은 함수 사용: '.$f);

// ---------- KVE-2026-3252 회원정보 수정 ----------
foreach (array('g5_get_form_token', 'g5_check_form_token', 'g5_peek_form_token', 'g5_set_member_reconfirm', 'g5_check_member_reconfirm') as $fn) {
    if (strpos($lib, 'function '.$fn.'(') === false) throw new Exception('FAIL 3252 함수 없음: '.$fn);
    eval(func_src('lib/common.lib.php', $fn, ''));
}
$_SESSION = array();
$tok = g5_get_form_token('member_update', 'alice');
check(g5_peek_form_token('member_update', 'alice', $tok) && g5_peek_form_token('member_update', 'alice', $tok), '3252 미리 확인은 토큰을 소모하지 않음');
check(!g5_peek_form_token('member_update', 'bob', $tok) && !g5_peek_form_token('member_update', 'alice', 'x'.$tok)
    && !g5_peek_form_token('member_update', 'alice', array($tok)) && !g5_peek_form_token('member_update', 'alice', ''), '3252 다른 회원·변조·배열·빈 토큰 거부');
check(g5_check_form_token('member_update', 'alice', $tok) && !g5_check_form_token('member_update', 'alice', $tok)
    && !g5_peek_form_token('member_update', 'alice', $tok), '3252 소모 후 재사용 거부');
$tok = g5_get_form_token('member_update', 'alice');
$_SESSION['ss_form_token_member_update']['time'] = G5_SERVER_TIME - 1801;
check(!g5_peek_form_token('member_update', 'alice', $tok), '3252 만료 토큰 거부');
check(!g5_check_member_reconfirm('alice'), '3252 재확인 증표 없음 거부');
g5_set_member_reconfirm('alice');
check(g5_check_member_reconfirm('alice') && !g5_check_member_reconfirm('bob') && !g5_check_member_reconfirm(''), '3252 재확인 증표는 같은 회원만');
$_SESSION['ss_member_reconfirm']['time'] = G5_SERVER_TIME - 601;
check(!g5_check_member_reconfirm('alice'), '3252 재확인 증표 10분 경과 거부');
$_SESSION['ss_member_reconfirm'] = array('mb_id' => 'alice', 'time' => G5_SERVER_TIME - 599);
check(g5_check_member_reconfirm('alice'), '3252 재확인 증표 10분 안 허용');

$u = src('bbs/register_form_update.php');
check(strpos($u, 'referer_check();') === false && pos_of($u, 'check_request_origin();') > 0
    && pos_of($u, 'check_request_origin();') < pos_of($u, 'chk_captcha()'), '3252 처리기 입구에서 출처 검사(빈 referer_check 대체)');
$top = substr($u, 0, pos_of($u, "if (run_replace('register_member_chk_captcha'"));
check(strpos($top, "strtoupper(\$_SERVER['REQUEST_METHOD']) !== 'POST'") !== false
    && strpos($top, "g5_peek_form_token('member_update', \$member['mb_id'], \$member_update_token)") !== false
    && preg_match("/if \(\\\$is_admin == 'super'\) \{\n\s+if \(file_exists\(G5_PATH.'\/DEMO'\)\)\n.*\n\s+alert\('관리자의 회원정보는/", $top), '3252 w=u: POST만·최고관리자 차단·토큰 확인이 CAPTCHA 앞');
$upd = pos_of($u, "\$sql = \" update {\$g5['member_table']}\n                set mb_nick");
check($upd > 0 && pos_of($u, "g5_check_form_token('member_update', \$member['mb_id'], \$member_update_token)") > pos_of($u, "if (\$msg = exist_mb_email(")
    && pos_of($u, "g5_check_form_token('member_update', \$member['mb_id'], \$member_update_token)") < $upd, '3252 입력 검증 뒤 UPDATE 직전에 토큰 소모');
check(pos_of($u, "\$is_member_credential_change = (\$mb_password !== '' || \$mb_email != \$member['mb_email']);") > 0
    && pos_of($u, 'g5_check_member_reconfirm($member[\'mb_id\'])') < pos_of($u, "run_event('register_form_update_before'"), '3252 비밀번호·이메일 변경은 재확인 증표 필요');
$after = substr($u, $upd);
check(pos_of($after, "delete from {\$g5['member_auto_login_table']} where mb_id = '{\$mb_id}'") > 0 && pos_of($after, 'session_regenerate_id(true);') > 0
    && pos_of($after, "set_cookie('ck_auto', '', 0);") > 0 && pos_of($after, "set_session('ss_member_reconfirm', '');") > 0, '3252 비밀번호 변경 후 자동로그인 폐기·세션 ID 재생성·증표 폐기');
$f = src('bbs/register_form.php');
check(strpos($f, 'ss_token') === false && strpos($f, "\$member_update_token = g5_get_form_token('member_update', \$member['mb_id']);") !== false
    && pos_of($f, "\$member_update_token = '';") < pos_of($f, 'if ($w == "")'), '3252 수정 화면에서 회원별 토큰 발급(요청 변수로 덮어쓰지 못함), ss_token 제거');
check(substr_count($f, 'g5_set_member_reconfirm($member[\'mb_id\'])') === 2 && strpos($f, "if (!(isset(\$_POST['is_update']) && \$_POST['is_update']))\n            g5_set_member_reconfirm") !== false
    && strpos($f, "} else if (\$is_social_login_modify) {\n") !== false, '3252 현재 비밀번호 확인·소셜 재인증에서만 증표 발급(is_update 제외)');
check(strpos($f, "// 리퍼러 체크\n    referer_check();") !== false, '3252 회원가입 화면의 referer_check() 호출은 유지(문서화)');
foreach (array('skin/member/basic', 'mobile/skin/member/basic', 'theme/basic/skin/member/basic', 'theme/basic/mobile/skin/member/basic') as $d)
    check(strpos(src($d.'/register_form.skin.php'), '<input type="hidden" name="member_update_token" value="<?php echo isset($member_update_token) ? $member_update_token : \'\'; ?>">') !== false, '3252 스킨 토큰 필드: '.$d);

// ---------- KVE-2026-3253 KCP v1 ----------
$cfg = src('plugin/kcpcert/kcpcert_config.php');
if (strpos($cfg, 'function kcpcert_v1_state_valid(') === false) throw new Exception('FAIL 3253 kcpcert_v1_state_valid 없음');
eval(func_src('plugin/kcpcert/kcpcert_config.php', 'kcpcert_v1_state_valid', ''));
$st = array('ordr_idxx' => '2026112312345678', 'page_type' => 'register', 'time' => G5_SERVER_TIME - 10);
check(kcpcert_v1_state_valid($st, 'register', '2026112312345678'), '3253 같은 세션·용도·주문번호·10분 안이면 허용');
check(!kcpcert_v1_state_valid('', 'register', '2026112312345678') && !kcpcert_v1_state_valid(array(), 'register', '2026112312345678'), '3253 시작 상태 없음 거부');
check(!kcpcert_v1_state_valid($st, 'find', '2026112312345678'), '3253 용도 교차 거부');
check(!kcpcert_v1_state_valid($st, 'register', '2026112312345679') && !kcpcert_v1_state_valid($st, 'register', '')
    && !kcpcert_v1_state_valid($st, 'register', array('2026112312345678')), '3253 주문번호 불일치·빈 값·배열 거부');
$st['time'] = G5_SERVER_TIME - 601;
check(!kcpcert_v1_state_valid($st, 'register', '2026112312345678'), '3253 10분 경과 거부');
check(strpos(src('plugin/kcpcert/kcpcert_form.php'), "set_session('ss_kcpcert_v1', array(") !== false, '3253 시작 페이지가 주문번호·용도·시각 저장');
foreach (array('plugin/kcpcert/kcpcert_result.php' => 'register', 'plugin/kcpcert/find_kcpcert_result.php' => 'find') as $path => $type) {
    $r = src($path);
    check(pos_of($r, "\$kcpcert_state = get_session('ss_kcpcert_v1');\nset_session('ss_kcpcert_v1', '');") > 0
        && pos_of($r, "set_session('ss_kcpcert_v1', '');") < pos_of($r, 'kcpcert_config.php') && pos_of($r, "set_session('ss_kcpcert_v1', '');") < pos_of($r, '$key = array_keys($_POST);'), '3253 시작 상태를 가장 먼저 꺼내고 지움: '.$type);
    check(pos_of($r, "kcpcert_v1_state_valid(\$kcpcert_state, '".$type."', \$ordr_idxx)") > 0
        && pos_of($r, "kcpcert_v1_state_valid(\$kcpcert_state, '".$type."', \$ordr_idxx)") < pos_of($r, '@insert_cert_history(')
        && pos_of($r, 'check_valid_hash') > pos_of($r, 'kcpcert_v1_state_valid(') && pos_of($r, 'set_session("ss_cert_type"') > pos_of($r, 'kcpcert_v1_state_valid('), '3253 대조가 dn_hash·세션 생성보다 앞: '.$type);
    check(strpos($r, '$sbParam .=') === false && strpos($r, 'echo $sbParam') === false
        && strpos($r, '<input type="hidden" name="up_hash" value="<?php echo htmlspecialchars($up_hash, ENT_QUOTES); ?>">') !== false
        && strpos($r, "\$_POST['res_cd']") === false, '3253 POST 전체 재출력 제거, up_hash만 이스케이프 출력: '.$type);
}
$r = src('plugin/kcpcert/kcpcert_result.php');
check(strpos($r, '.val("<?php echo') === false && substr_count($r, '.val(<?php echo get_js_safe_string(') === 4, '3253 부모창 JS 값 4개를 JS 리터럴로 출력');
$d = src('plugin/kcpcert/find_kcpcert_result.php');
check(pos_of($d, "\$cert_type   = 'hp';") > 0 && pos_of($d, "\$cert_type   = 'hp';") < pos_of($d, '$hash_data   = md5($user_name.$cert_type'), '3253 찾기 처리기 $cert_type 지정');
check(strpos($d, 'value="<?php echo htmlspecialchars($row["mb_id"], ENT_QUOTES); ?>"') !== false, '3253 찾기 결과 mb_id 이스케이프');
$a = src('adm/config_form.php');
check(strpos($a, 'id="cf_cert_hp_kcp_v1_notice"') !== false && strpos($a, "(\$config['cf_cert_hp'] == 'kcp') ? 'block' : 'none'") !== false
    && strpos($a, 'id="cf_cert_hp_kcp_test_notice"') !== false, '3253 관리자 설정에 api_v2 전환 권고·테스트 모드 경고');

echo 'TOTAL '.$checks."\n";

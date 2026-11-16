<?php
// 실행: php tests/kve_3201_3203.php (DB 없이 KVE-2026-3201·3202·3203 토큰·처리 순서·권한 조건을 검사)
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
function pos_of($s, $needle) { $p = strpos($s, $needle); return $p === false ? -1 : $p; }

// ---------- 용도별 1회용 토큰 ----------
if (!preg_match('/^function slow_equals\(.*?^\}/ms', src('lib/pbkdf2.compat.php'), $m)) throw new Exception('함수 찾기 실패: slow_equals');
eval($m[0]);
$lib = src('lib/common.lib.php');
foreach (array('g5_get_form_token', 'g5_check_form_token') as $name) {
    if (!preg_match('/^function '.$name.'\(.*?^\}/ms', $lib, $m)) throw new Exception('함수 찾기 실패: '.$name);
    eval($m[0]);
}
function get_session($key) { return isset($_SESSION[$key]) ? $_SESSION[$key] : ''; }
function set_session($key, $value) { $_SESSION[$key] = $value; }
function get_random_token_string($length) { return bin2hex(random_bytes($length)); }

$_SESSION = array();
$t = g5_get_form_token('cert_refresh', 'm1');
check(strlen($t) >= 64 && ctype_xdigit($t), '토큰 길이·형식');
check(g5_check_form_token('cert_refresh', 'm1', $t) === true, '정상 토큰 통과');
check(g5_check_form_token('cert_refresh', 'm1', $t) === false, '사용한 토큰 재사용 거부');
$t = g5_get_form_token('cert_refresh', 'm1');
check(g5_check_form_token('cert_refresh', 'm1', 'x'.$t) === false && g5_check_form_token('cert_refresh', 'm1', $t) === false, '틀린 토큰 거부 후 원래 토큰도 폐기');
$t = g5_get_form_token('cert_refresh', 'm1');
check(g5_check_form_token('cert_refresh', 'm2', $t) === false, '다른 대상에 묶인 토큰 거부');
$t = g5_get_form_token('cert_refresh', 'm1');
check(g5_check_form_token('memo_delete', 'm1', $t) === false, '다른 용도 토큰 거부');
$t = g5_get_form_token('cert_refresh', 'm1');
$_SESSION['ss_form_token_cert_refresh']['time'] = G5_SERVER_TIME - 1801;
check(g5_check_form_token('cert_refresh', 'm1', $t) === false, '만료 토큰 거부');
foreach (array('', null, array('a'), 123) as $bad) {
    g5_get_form_token('cert_refresh', 'm1');
    if (g5_check_form_token('cert_refresh', 'm1', $bad) !== false) throw new Exception('FAIL 비정상 토큰 허용');
}
check(true, '빈 값·비문자열 토큰 거부');
$t = g5_get_form_token('cert_refresh', '');
check(g5_check_form_token('cert_refresh', '', $t) === false, '대상 없는 토큰 거부');

// ---------- KVE-2026-3201 본인인증 갱신 ----------
$u = src('bbs/member_cert_refresh_update.php');
$update = pos_of($u, "update {\$g5['member_table']}");
$need = array("!== 'POST'", 'check_request_origin()', "g5_check_form_token('cert_refresh'", "\$config['cf_cert_use']", 'strlen($member[\'mb_dupinfo\']) != 64');
$bad = array(); foreach ($need as $n) { $p = pos_of($u, $n); if ($p < 0 || $p > $update) $bad[] = $n; }
check(!$bad, '3201 갱신 전 POST·출처·토큰·진입 조건 검사 '.implode(',', $bad));
check(strpos($u, "mb_certify = ''") === false && strpos($u, 'ss_reg_mb_name') === false, '3201 인증 결과 없는 초기화 분기 제거');
check(preg_match('/\} else \{\s*\/\/[^\n]*\n\s*alert\(/', $u) === 1, '3201 인증 결과 없으면 실패');
check(pos_of($u, "set_session(\$ss_cert_name, '')") > pos_of($u, 'run_event(\'cert_refresh_update_after\''), '3201 처리 후 인증 결과 세션 정리');
check(strpos(src('bbs/member_cert_refresh.php'), "g5_get_form_token('cert_refresh'") !== false, '3201 갱신 화면 토큰 발급');

// ---------- KVE-2026-3202 스크랩 ----------
$s = src('bbs/scrap_popin_update.php');
$lock = pos_of($s, 'g5_get_named_lock($scrap_lock)'); $dup = pos_of($s, 'select count(*) as cnt from {$g5[\'scrap_table\']}');
$ins = pos_of($s, "insert into {\$g5['scrap_table']}"); $rel = strrpos($s, 'g5_release_named_lock($scrap_lock)');
$cmt = pos_of($s, 'insert into $write_table'); $pt = pos_of($s, 'insert_point(');
check($lock >= 0 && $lock < $dup && $dup < $ins && $ins < $rel && $rel < $cmt && $cmt < $pt, '3202 잠금→중복확인→스크랩 등록→잠금 해제→댓글→포인트 순서');
check(substr_count($s, "insert into {\$g5['scrap_table']}") === 1, '3202 스크랩 INSERT 1곳');
check(pos_of($s, "g5_check_form_token('scrap_'") >= 0 && pos_of($s, "g5_check_form_token('scrap_'") < $lock && pos_of($s, 'check_request_origin()') < $lock, '3202 등록 전 토큰·출처 검사');
check(strpos($s, "if (\$w == 'c'") === false && pos_of($s, "cf_delay_sec") >= 0 && pos_of($s, "cf_delay_sec") < $lock, '3202 연속 등록 제한을 w와 관계없이 등록 전에 적용');
check(preg_match('/if \(!\$scrap_id\)/', $s) === 1 && pos_of($s, 'if (!$scrap_id)') < $cmt, '3202 스크랩 등록 실패 시 댓글·포인트 미처리');
check(strpos(src('bbs/scrap_popin.php'), "g5_get_form_token('scrap_'") !== false, '3202 스크랩 창 토큰 발급');

// ---------- KVE-2026-3203 쪽지 삭제 ----------
$d = src('bbs/memo_delete.php');
check(strpos($d, "\$kind !== 'recv' && \$kind !== 'send'") !== false, '3203 kind 화이트리스트');
check(strpos($d, "me_type = '{\$kind}' and me_{\$kind}_mb_id = '{\$sql_mb_id}'") !== false && substr_count($d, '{$sql_owner}') === 2, '3203 조회·삭제 모두 행 유형과 역할 결합');
check(strpos($d, 'me_recv_mb_id = \'{$member') === false && strpos($d, ' or me_send_mb_id') === false, '3203 수신자·발신자 OR 조건 제거');
$side = pos_of($d, "set mb_memo_call = ''"); $cond = pos_of($d, "if (\$kind === 'recv' && !\$row['me_read_datetime'][0])");
check($cond >= 0 && $cond < $side && pos_of($d, '$left = sql_fetch') < $cond, '3203 실제 삭제 확인 후 자기 수신 쪽지일 때만 알림 갱신');
check(strpos($d, "g5_check_form_token('memo_delete'") !== false && strpos($d, 'ss_memo_delete_token') === false, '3203 삭제 토큰 검증 강화');
foreach (array('bbs/memo.php', 'bbs/memo_view.php') as $f) {
    $c = src($f);
    if (strpos($c, 'uniqid(time())') !== false || strpos($c, "g5_get_form_token('memo_delete'") === false) throw new Exception('FAIL 3203 '.$f);
}
check(true, '3203 쪽지함·보기 화면 CSPRNG 토큰 발급');

// ---------- 스킨 토큰 필드 ----------
$bad = array();
foreach (array('skin/member/basic', 'mobile/skin/member/basic', 'theme/basic/skin/member/basic', 'theme/basic/mobile/skin/member/basic') as $dir) {
    if (strpos(src($dir.'/member_cert_refresh.skin.php'), 'name="token" value="<?php echo isset($cert_refresh_token)') === false) $bad[] = $dir.'/member_cert_refresh';
    if (strpos(src($dir.'/scrap_popin.skin.php'), 'name="token" value="<?php echo isset($scrap_token)') === false) $bad[] = $dir.'/scrap_popin';
}
check(!$bad, '본인인증 갱신·스크랩 스킨 4곳씩 토큰 필드 '.implode(',', $bad));

echo 'TOTAL '.$checks."\n";

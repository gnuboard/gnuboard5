<?php
// 실행: php tests/kve_3251_3255.php
// DB 없이 KVE-2026-3254 CSV 직렬화, KVE-2026-3255 Browscap 전송·캐시 생성, KVE-2026-3251 에디터 업로드 제한을 검사한다.
// 외부(browscap.org 등)에 접속하지 않는다. 원격 방식 검사는 접속 전에 거부되는 주소만 쓴다.
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
define('_GNUBOARD_', true);
// CLI에서 세션 ID를 바꿔 가며 검사하려면 출력 전에 헤더가 전송되지 않아야 한다.
ob_start();
$checks = 0;
function check($ok, $name) {
    if (!$ok) throw new Exception('FAIL '.$name);
    echo 'PASS '.$name."\n";
    $GLOBALS['checks']++;
}
function src($path) { return str_replace("\r\n", "\n", file_get_contents($GLOBALS['root'].'/'.$path)); }
function func_src($path, $name, $indent) {
    if (!preg_match('/^'.$indent.'function '.$name.'\(.*?^'.$indent.'\}/ms', src($path), $m)) throw new Exception('함수 찾기 실패: '.$name);
    return $m[0];
}
function rrmdir($dir) {
    if (!is_dir($dir)) return;
    foreach (scandir($dir) as $f) {
        if ($f === '.' || $f === '..') continue;
        is_dir("$dir/$f") ? rrmdir("$dir/$f") : unlink("$dir/$f");
    }
    rmdir($dir);
}
function php_bin() { return defined('PHP_BINARY') && PHP_BINARY ? PHP_BINARY : 'php'; }
$tmp = sys_get_temp_dir().'/kve3251_'.bin2hex(random_bytes(4));
mkdir($tmp, 0700);
register_shutdown_function('rrmdir', $tmp);

// ---------- KVE-2026-3254 CSV ----------
eval(func_src('lib/common.lib.php', 'csv_safe_cell', '    '));
eval(func_src('lib/common.lib.php', 'csv_quote_cell', '    '));
eval(func_src('lib/common.lib.php', 'csv_build_row', '    '));
$cells = array(
    '",=cmd|\'/C dir\'!A0,"', '=1+1', '+1', '-1', '@SUM(A1)', "\t=1", "\r=1", 'a"b""c', "line1\n=line2", 'a,b', '',
    null, iconv('UTF-8', 'UHC', '홍길동 "따옴표", 주소'), "\\\"", '\'=already',
);
$line = csv_build_row($cells);
// RFC 4180 기준으로 읽는다(escape 문자 없음).
$parsed = str_getcsv(rtrim($line, "\n"), ',', '"', '');
$bad = array();
foreach ($cells as $i => $v) {
    $expect = (string)csv_safe_cell($v);
    if (!array_key_exists($i, $parsed) || $parsed[$i] !== $expect) $bad[] = $i.':roundtrip';
    if (isset($parsed[$i][0]) && strpos("=+-@\t\r", $parsed[$i][0]) !== false) $bad[] = $i.':formula';
}
check(count($parsed) === count($cells) && !$bad, '3254 csv_build_row: 셀 수 유지, 내부 " , 줄바꿈 복원, 수식 시작 문자 없음 '.implode(',', $bad));
check(substr($line, -1) === "\n" && csv_build_row(array('a'), "\r\n") === "\"a\"\r\n", '3254 행 끝 줄바꿈 지정');
// 수정 전 방식(따옴표로 감싸기만 함)은 같은 값에서 셀이 늘어난다(검사의 유효성 확인).
$legacy = str_getcsv('"'.csv_safe_cell('",=1+1,"').'","x"', ',', '"', '');
check(count($legacy) > 2 && $legacy[1] === '=1+1', '3254 기준 확인: 이스케이프 없는 연결은 경계를 벗어남');

$o = src('adm/shop_admin/orderprintresult.php');
preg_match('/if \(\$csv == \'csv\'\)\s*\{(.*?)\n\}\n/s', $o, $m);
$csvb = isset($m[1]) ? $m[1] : '';
check($csvb && strpos($csvb, "echo '\"'") === false && strpos($csvb, 'echo \'"\\\'\'') === false && substr_count($csvb, 'echo csv_build_row(array(') === 1,
    '3254 CSV 분기는 문자열 연결 없이 csv_build_row()로만 행을 출력');
preg_match('/echo csv_build_row\(array\((.*?)\n\s*\)\);/s', $csvb, $m2);
$cols = isset($m2[1]) ? array_map('trim', explode(",\n", trim($m2[1]))) : array();
check(count($cols) === 13 && in_array("conv_telno(\$row['od_b_tel'])", $cols, true) && in_array("conv_telno(\$row['od_b_hp'])", $cols, true)
    && in_array("\$row['it_name']", $cols, true) && in_array("\$row['od_memo']", $cols, true), '3254 13개 열(전화·휴대폰 포함)이 모두 직렬화 함수를 거침');
check(strpos($csvb, '"&#034;"') === false && strpos($csvb, "array_map('iconv_euckr', \$row)") !== false, '3254 &#034; 치환 제거, 셀 단위 EUC-KR 변환 유지');
$bad = array();
foreach (array('adm', 'shop', 'bbs', 'plugin', 'lib') as $d) {
    $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root.'/'.$d, FilesystemIterator::SKIP_DOTS));
    foreach ($it as $f) {
        if (substr($f, -4) !== '.php' || strpos($f, 'PHPExcel') !== false) continue;
        $c = file_get_contents($f);
        if (preg_match('/text\/(?:x-)?csv/i', $c) && substr($f, -strlen('orderprintresult.php')) !== 'orderprintresult.php') $bad[] = substr($f, strlen($root) + 1);
    }
}
check(!$bad, '3254 orderprintresult.php 외에 문자열 CSV 응답 없음 '.implode(',', $bad));

// ---------- KVE-2026-3255 Browscap ----------
require $root.'/plugin/browscap/Browscap.php';
class KveBrowscap extends Browscap {
    public function fetch($url, $max = null) { return $this->_getRemoteData($url, $max); }
}
$payload = "\\',define(chr(103).chr(53),1),//";
$ini = "[GJK_Browscap_Version]\nVersion=6001007\nReleased=Mon, 01 Jan 2026 00:00:00 +0000\n\n"
    ."[DefaultProperties]\nComment=\"DefaultProperties\"\nBrowser=\"DefaultProperties\"\nPlatform=\"unknown\"\nDevice_Type=\"unknown\"\n\n"
    ."[Mozilla/5.0 (*KVETest/*]\nParent=\"DefaultProperties\"\nComment=\"{$payload}\"\nBrowser=\"KVE\\\"\nPlatform=\"P\\'.die(7).\\'\"\nDevice_Type=\"Desktop\"\n\n"
    ."[*]\nParent=\"DefaultProperties\"\nComment=\"Default Browser\"\nBrowser=\"Default Browser\"\nPlatform=\"unknown\"\nDevice_Type=\"unknown\"\n";
mkdir("$tmp/bc", 0700);
file_put_contents("$tmp/src.ini", $ini);
$b = new Browscap("$tmp/bc");
$b->localFile = "$tmp/src.ini";
$b->cacheFilename = 'browscap_cache.php';
$err = '';
try { $b->updateCache(); } catch (Exception $e) { $err = $e->getMessage(); }
$cache = "$tmp/bc/browscap_cache.php";
check($err === '' && is_file($cache), '3255 악성 값이 든 INI로 캐시 생성 '.$err);
exec(escapeshellarg(php_bin()).' -l '.escapeshellarg($cache).' 2>&1', $out, $rc);
check($rc === 0, '3255 생성 캐시 문법 정상(php -l)');
$probe = "$tmp/probe.php";
file_put_contents($probe, '<?php $cache_version = null; require $argv[1]; echo json_encode(array("marker" => defined("g5"), "browsers" => $browsers, "version" => $source_version));');
$res = json_decode(shell_exec(escapeshellarg(php_bin()).' '.escapeshellarg($probe).' '.escapeshellarg($cache).' 2>/dev/null'), true);
$found = false;
if (is_array($res)) foreach ($res['browsers'] as $s) { $a = @unserialize($s); if (is_array($a) && in_array($payload, $a, true)) $found = true; }
check(is_array($res) && $res['marker'] === false && $found && $res['version'] === '6001007', '3255 캐시를 별도 프로세스에서 require해도 주입 표현식 미실행, 값은 문자열 그대로');
$b2 = new Browscap("$tmp/bc"); $b2->doAutoUpdate = false; $b2->updateMethod = false; $b2->cacheFilename = 'browscap_cache.php';
$info = $b2->getBrowser('Mozilla/5.0 (X11) KVETest/1.0');
check($info->Comment === $payload && $info->Browser === 'KVE\\' && $info->Platform === "P\\'.die(7).\\'", '3255 조회 결과가 INI 원래 값과 같음');
check(strpos(src('plugin/browscap/Browscap.php'), "str_replace(\"'\", \"\\'\"") === false && strpos(src('plugin/browscap/Browscap.php'), 'addcslashes(serialize(') === false, '3255 작은따옴표만 이스케이프하는 직렬화 제거');

// 검증 실패 시 기존 캐시 유지
$before = md5_file($cache);
foreach (array("[DefaultProperties]\nComment=\"x\"\n[*]\nComment=\"y\"\n" => 'no-version', str_replace('Device_Type', 'Device_X', $ini) => 'no-property') as $bad_ini => $label) {
    file_put_contents("$tmp/bad.ini", $bad_ini);
    touch("$tmp/bad.ini", time() + 10);
    $b3 = new Browscap("$tmp/bc"); $b3->localFile = "$tmp/bad.ini"; $b3->cacheFilename = 'browscap_cache.php';
    $e3 = '';
    try { $b3->updateCache(); } catch (Exception $e) { $e3 = $e->getMessage(); }
    check($e3 !== '' && md5_file($cache) === $before && !file_exists("$tmp/bc/cache.lock") && !glob("$tmp/bc/temp_*"), "3255 비정상 INI($label) 거부, 기존 캐시 유지·임시 파일·잠금 정리");
}

// 이전 형식 캐시는 require하지 않음
mkdir("$tmp/old", 0700);
$marker = "$tmp/old_marker";
file_put_contents("$tmp/old/browscap_cache.php", "<?php\n\$source_version='1';\n\$cache_version='2.0.5';\ntouch(".var_export($marker, true).");\n\$properties=array();\$browsers=array();\$userAgents=array();\$patterns=array();\n");
file_put_contents("$tmp/old/browscap.ini", $ini);
$b4 = new Browscap("$tmp/old"); $b4->doAutoUpdate = false; $b4->updateMethod = false; $b4->cacheFilename = 'browscap_cache.php';
$e4 = '';
try { $b4->getBrowser('x'); } catch (Exception $e) { $e4 = $e->getMessage(); }
check($e4 !== '' && !file_exists($marker), '3255 머리말 없는 이전 형식 캐시는 실행하지 않고 원격 업데이트도 하지 않음');

// 원격 방식: https가 아니면 접속 전에 거부(평문으로 내려가지 않음)
$bad = array();
foreach (array(Browscap::UPDATE_CURL, Browscap::UPDATE_FOPEN, Browscap::UPDATE_FSOCKOPEN) as $method) {
    foreach (array('http://127.0.0.1:9/x', 'ftp://127.0.0.1/x', 'https://user:pw@127.0.0.1/x', "https://127.0.0.1/x\r\nHost: y", '//127.0.0.1/x') as $url) {
        $k = new KveBrowscap("$tmp/bc"); $k->updateMethod = $method; $k->timeout = 1;
        $msg = '';
        try { $k->fetch($url); } catch (Exception $e) { $msg = $e->getMessage(); }
        if (strpos($msg, 'must be a valid https URL') === false) $bad[] = $method.':'.$url.':'.$msg;
    }
}
check(!$bad, '3255 cURL·fopen·socket 모두 https가 아닌 주소를 접속 전에 거부 '.implode(' | ', $bad));
$k = new KveBrowscap("$tmp/bc");
check($k->remoteIniUrl === 'https://browscap.org/stream?q=PHP_BrowscapINI' && $k->remoteVerUrl === 'https://browscap.org/version', '3255 기본 원격 주소 https');
$s = src('plugin/browscap/Browscap.php');
check(strpos($s, 'CURLOPT_SSL_VERIFYPEER, true') !== false && strpos($s, 'CURLOPT_SSL_VERIFYHOST, 2') !== false
    && strpos($s, 'CURLOPT_FOLLOWLOCATION, false') !== false && strpos($s, "'verify_peer'         => true") !== false
    && strpos($s, "'allow_self_signed'   => false") !== false && strpos($s, "'follow_location'] = 0") !== false
    && strpos($s, "'ssl://' . \$parts['host']") !== false && strpos($s, 'fsockopen(') === false, '3255 cURL·fopen·socket TLS 검증·무리다이렉트 설정');
foreach (array('adm/browscap_update.php', 'adm/browscap_converter.php') as $f) {
    $c = src($f);
    check(preg_match("/REQUEST_METHOD'\]\) !== 'POST'/", $c) && strpos($c, 'check_admin_token();') !== false
        && strpos($c, 'check_admin_token();') < strpos($c, 'new Browscap('), "3255 $f POST·관리자 토큰 검사 후 실행");
}
check(strpos(src('adm/browscap_converter.php'), "\$_GET['rows']") === false && strpos(src('adm/browscap_converter.php'), '$browscap->updateMethod = false;') !== false
    && strpos(src('adm/browscap_converter.php'), 'sql_real_escape_string($brow)') !== false, '3255 변환기: POST rows, 원격 업데이트 안 함, DB 값 이스케이프');
foreach (array('adm/browscap.php' => 'browscap_update.php', 'adm/browscap_convert.php' => 'browscap_converter.php') as $f => $target) {
    $c = src($f);
    check(strpos($c, 'get_ajax_token()') !== false && strpos($c, 'type: "POST"') !== false && strpos($c, 'token: token') !== false && strpos($c, 'method: "GET"') === false, "3255 $f AJAX를 POST+토큰으로 호출");
}
$v = src('bbs/visit_browscap.inc.php');
check(strpos($v, '$browscap->updateMethod = false;') !== false && strpos($v, 'catch (Exception $e)') !== false && substr_count($v, 'sql_real_escape_string(') === 3, '3255 방문 기록: 원격 업데이트 안 함, 실패 시 빈 값, 값 이스케이프');
$u = src('adm/browscap_update.php');
check(strpos($u, "defined('G5_BROWSCAP_REMOTE_INI_URL')") !== false && !preg_match('/\$_(?:GET|POST|REQUEST)\[[^\]]*(?:url|ca)/i', $u), '3255 원격 주소·CA 재정의는 상수로만(요청 값 미사용)');

// ---------- KVE-2026-3251 에디터 업로드 제한 ----------
define('G5_DATA_PATH', "$tmp/data");
define('G5_DIR_PERMISSION', 0755);
define('G5_FILE_PERMISSION', 0644);
define('G5_MYSQL_PASSWORD', 'unit-secret');
define('G5_EDITOR_UPLOAD_MAX_FILES', 3);
define('G5_EDITOR_UPLOAD_MAX_BYTES', 1000);
define('G5_EDITOR_UPLOAD_FILE_MAX_SIZE', 600);
mkdir("$tmp/data/cache", 0755, true);
require $root.'/lib/editor_upload.lib.php';
check(G5_EDITOR_UPLOAD_GUEST === false && G5_EDITOR_UPLOAD_WINDOW === 3600, '3251 기본값: 비회원 업로드 끔, 1시간 기간');
$ext = src('extend/editor_upload.extend.php');
check(strpos($ext, "define('G5_EDITOR_UPLOAD_GUEST', false)") !== false && strpos($ext, "define('G5_EDITOR_UPLOAD_MAX_FILES', 100)") !== false
    && strpos($ext, "define('G5_EDITOR_UPLOAD_MAX_BYTES', 200 * 1024 * 1024)") !== false && strpos($ext, "define('G5_EDITOR_UPLOAD_FILE_MAX_SIZE', 20 * 1024 * 1024)") !== false, '3251 extend 설정 기본값');
ini_set('session.save_path', $tmp);
ini_set('session.use_cookies', '0');
function sess($id) {
    if (session_status() === PHP_SESSION_ACTIVE) session_write_close();
    session_id($id.bin2hex(random_bytes(4)));
    if (!session_start()) throw new Exception('session_start 실패');
}
sess('kveunitsessionaaaa');
$is_member = false; $member = array('mb_id' => ''); $is_admin = '';
check(g5_editor_upload_access_error() !== '', '3251 비회원 업로드 거부(기본값)');
$is_member = true; $member = array('mb_id' => 'kveuser1');
check(g5_editor_upload_access_error() === '', '3251 회원 업로드 허용');
check(g5_editor_upload_reserve(601, $r) !== '' && $r === null, '3251 파일당 크기 초과 거부');
$res = array(); $rs = array();
for ($i = 0; $i < 3; $i++) { $res[] = g5_editor_upload_reserve(100, $r); $rs[] = $r; }
check($res === array('', '', '') && g5_editor_upload_reserve(100, $r) !== '', '3251 기간 내 파일 수 한도 초과 거부');
g5_editor_upload_release($rs[2]);
check(g5_editor_upload_reserve(100, $r) === '', '3251 실패한 업로드 예약을 되돌리면 다시 허용');
// 같은 회원의 다른 세션도 회원 기준으로 막힘
sess('kveunitsessionbbbb');
check(g5_editor_upload_reserve(10, $r) !== '', '3251 같은 회원은 새 세션에서도 파일 수 한도 적용');
$member = array('mb_id' => 'kveuser2');
check(g5_editor_upload_reserve(500, $r) === '' && g5_editor_upload_reserve(500, $r) === '' && g5_editor_upload_reserve(1, $r) !== '', '3251 다른 회원은 별도 집계, 누적 바이트 한도 초과 거부');
// 비회원(허용 설정 시)은 세션과 IP 둘 다로 센다.
$is_member = false; $member = array('mb_id' => '');
sess('kveunitguestaaaa'); $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
$ok = true; for ($i = 0; $i < 3; $i++) if (g5_editor_upload_reserve(10, $r) !== '') $ok = false;
check($ok && g5_editor_upload_reserve(10, $r) !== '', '3251 비회원: 세션·IP 기준 파일 수 한도');
$_SERVER['REMOTE_ADDR'] = '192.0.2.11';
check(g5_editor_upload_reserve(10, $r) !== '', '3251 비회원: IP를 바꿔도 같은 세션이면 거부');
sess('kveunitguestbbbb'); $_SERVER['REMOTE_ADDR'] = '192.0.2.10';
check(g5_editor_upload_reserve(10, $r) !== '', '3251 비회원: 세션을 바꿔도 같은 IP면 거부');
$_SERVER['REMOTE_ADDR'] = '192.0.2.12';
check(g5_editor_upload_reserve(10, $r) === '', '3251 비회원: 다른 세션·IP는 별도 집계');
$is_admin = 'super'; $is_member = true; $member = array('mb_id' => 'admin');
$ok = true; for ($i = 0; $i < 5; $i++) if (g5_editor_upload_reserve(500, $r) !== '') $ok = false;
check($ok && g5_editor_upload_reserve(601, $r) !== '', '3251 최고관리자는 수·누적 제한 제외, 파일당 크기는 적용');
$files = glob("$tmp/data/cache/editor_upload_limit/*.php");
check($files && strpos(file_get_contents($files[0]), "<?php exit; ?>\n") === 0, '3251 기록 파일은 웹에서 실행해도 내용이 나오지 않는 .php 형식');

$se = src('plugin/editor/smarteditor2/photo_uploader/popup/php/index.php');
check(strpos($se, "include_once(G5_LIB_PATH.'/editor_upload.lib.php')") !== false && strpos($se, 'g5_editor_upload_access_error()') < strpos($se, "require('UploadHandler.php')")
    && strpos($se, "'max_file_size' => g5_editor_upload_max_file_size()") !== false, '3251 SmartEditor2: 비회원 거부·파일당 크기 지정');
$uh = src('plugin/editor/smarteditor2/photo_uploader/popup/php/UploadHandler.php');
check(strpos($uh, 'g5_editor_upload_reserve(') !== false && strpos($uh, 'g5_editor_upload_reserve(') < strpos($uh, 'move_uploaded_file($uploaded_file, $file_path)')
    && strpos($uh, 'g5_editor_upload_release($limit_reservation)') !== false, '3251 SmartEditor2: 저장 전 예약, 실패 시 되돌림');
$ch = src('plugin/editor/cheditor5/imageUpload/upload.php');
check(strpos($ch, 'g5_editor_upload_access_error()') !== false && strpos($ch, 'g5_editor_upload_reserve(') < strpos($ch, 'move_uploaded_file($tempfile, $savefile)')
    && substr_count($ch, 'g5_editor_upload_release($upload_limit_reservation)') === 3, '3251 CHEditor5: 비회원 거부, 저장 전 예약, 실패 시 되돌림');

echo 'TOTAL '.$checks."\n";

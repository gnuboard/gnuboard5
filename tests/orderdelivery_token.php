<?php
// 배송 업로드 폼의 토큰 생성과 실제 관리자 토큰 검증 함수를 연결해 검사한다.
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
define('_GNUBOARD_', true);
define('G5_URL', 'https://example.invalid');
$admin = file_get_contents($root.'/adm/admin.lib.php');
foreach (array('get_admin_token', 'check_admin_token') as $name) {
    if (!preg_match('/function '. $name .'\(\)\s*\{.*?^\}/ms', $admin, $m)) throw new Exception('함수 찾기 실패');
    eval($m[0]);
}
function get_session($key) { return isset($_SESSION[$key]) ? $_SESSION[$key] : ''; }
function set_session($key, $value) { $_SESSION[$key] = $value; }
function get_random_token_string($length) { return bin2hex(random_bytes($length)); }
function alert($message, $url = '') { throw new RuntimeException($message); }
$source = file_get_contents($root.'/adm/shop_admin/orderdelivery.php');
if (!preg_match('/<input[^\n]+name="token"[^\n]+>/', $source, $match)) {
    throw new Exception('배송 업로드 폼에 토큰 필드 없음');
}
function render_token($markup) {
    ob_start(); eval('?>'.$markup); $html = ob_get_clean();
    if (!preg_match('/value="([a-f0-9]+)"/', $html, $m)) throw new Exception('토큰 생성 실패');
    return $m[1];
}
function verify_token($request, $expected) {
    $_REQUEST = $request;
    try { $actual = check_admin_token(); } catch (RuntimeException $e) { $actual = false; }
    if ($actual !== $expected || get_session('ss_admin_token') !== '') throw new Exception('토큰 검증 결과 불일치');
}
$_SESSION = array();
$token = render_token($match[0]);
verify_token(array('token'=>$token), true);
verify_token(array('token'=>$token), false);
render_token($match[0]);
verify_token(array('token'=>''), false);
render_token($match[0]);
verify_token(array('token'=>'invalid'), false);
$old = render_token($match[0]);
$new = render_token($match[0]);
if ($old === $new) throw new Exception('토큰 갱신 실패');
verify_token(array('token'=>$old), false);
$token = render_token($match[0]);
verify_token(array('token'=>$token), true);
$update = file_get_contents($root.'/adm/shop_admin/orderdeliveryupdate.php');
if (strpos($update, 'check_admin_token();') > strpos($update, 'PHPExcel_IOFactory::load')) throw new Exception('파일 처리 전 검증 누락');
echo "PASS: 정상·재사용·빈 토큰·변조·이전 창 토큰·새 토큰 6건\n";

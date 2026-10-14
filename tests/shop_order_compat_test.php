<?php
// PHP 5.2.17/7.x/8.x 공통 실행. DB/PG 없이 호환 함수와 구형 mysql 잠금 의미를 검사한다.
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
define('G5_MYSQLI_USE', false);
define('G5_MYSQL_HOST', 'localhost');
define('G5_MYSQL_USER', 'fixture');
define('G5_MYSQL_PASSWORD', 'fixture');
define('G5_MYSQL_DB', 'fixture');
define('G5_SHOP_TABLE_PREFIX', 'custom_shop_');
require dirname(dirname(__FILE__)).'/lib/shop_order_compat.lib.php';
require dirname(dirname(__FILE__)).'/lib/shop_order_state.lib.php';
function shop_order_access_fail() { throw new RuntimeException('denied'); }
function compat_expect($ok, $name) {
    global $checks;
    if (!$ok) { fwrite(STDERR, '실패: '.$name."\n"); exit(1); }
    $checks++;
}
$checks = 0;
if (isset($argv[1]) && $argv[1] === '--no-rng') {
    // 이 모드는 random_bytes/OpenSSL/mcrypt를 disable_functions로 끄고 실행한다.
    stream_wrapper_unregister('file');
    $denied = false;
    try { shop_order_random_hex(32); } catch (Exception $e) { $denied = true; }
    compat_expect($denied, '강한 난수원 부재 시 발급 거부');
    echo "안전한 난수원 부재 시 거부 통과\n";
    exit;
}
$one = shop_order_random_hex(32);
$two = shop_order_random_hex(32);
compat_expect(strlen($one) === 64 && preg_match('/^[a-f0-9]+$/D', $one) && $one !== $two, '256비트 토큰');
compat_expect(shop_order_equals($one, $one), '동일 토큰');
compat_expect(!shop_order_equals($one, $two) && !shop_order_equals($one, '') && !shop_order_equals($one, array()), '변조·길이·유형 거부');
$values = array(
    array('name'=>'한글', 'binary'=>"\x00\xff\";O:8:\"stdClass\":0:{}", 'integer'=>123, 'negative'=>-7,
          'float'=>1.5, 'bool'=>true, 'false'=>false, 'null'=>null, 'array'=>array(0=>'first', 'key'=>'last')),
    array(), array('a'=>array('b'=>array('c'=>'nested'))), array('large_id'=>'20260914123456789012')
);
foreach ($values as $value) compat_expect(shop_order_decode_data(base64_encode(serialize($value))) === $value, '기존 직렬화 배열 복원');
class CompatWakeup {
    function __wakeup() { $GLOBALS['woke_up'] = true; }
}
$woke_up = false;
$bad = array(
    'O:12:"CompatWakeup":0:{}', 'a:1:{s:1:"x";O:12:"CompatWakeup":0:{}}',
    'a:1:{s:1:"x";C:4:"Evil":0:{}}', 'a:1:{i:0;R:1;}', 'a:1:{i:0;r:1;}',
    'a:1:{i:0;s:999:"short";}', 'a:1000000:{}', 'a:0:{}trailing',
    'a:1:{i:0;d:INF;}', 'a:1:{i:0;d:1E999;}', 'a:1:{i:0;i:999999999999999999999;}',
    'a:1:{b:1;s:1:"x";}', 'a:1:{s:1:"x";N:}', 'a:1:{i:0;S:1:"x";}', 'a:0:{',
    str_repeat('a:1:{i:0;', 40).'N;'.str_repeat('}', 40), serialize(array(new CompatWakeup()))
);
foreach ($bad as $value) compat_expect(shop_order_decode_data(base64_encode($value)) === false, '객체·참조·잘못된 형식 거부');
compat_expect(!$woke_up, '객체 훅 미실행');
compat_expect(shop_order_decode_data('!invalid!') === false, 'Base64 오류 거부');
compat_expect(shop_order_decode_data(str_repeat('a', 1398105)) === false, '입력 크기 제한');

// 구버전에서도 실제 Toss 클래스의 요청/인증 헤더 구성이 유지된다. 외부 호출 없음.
require dirname(dirname(__FILE__)).'/shop/toss/toss.inc.php';
$toss = new TossPayments('test_client', 'test_secret', 'test_mid');
$toss->setPaymentHeader();
$toss->setPaymentData(array('amount'=>1000, 'orderId'=>'123', 'paymentKey'=>'test_key'));
compat_expect(in_array('Authorization: Basic '.base64_encode('test_secret:'), $toss->headers, true), 'Toss 인증 헤더 호환');
compat_expect($toss->paymentData === array('amount'=>1000, 'orderId'=>'123', 'paymentKey'=>'test_key'), 'Toss 승인 요청 본문 호환');

// MySQL 5.0 의미: 같은 연결의 새 GET_LOCK은 이전 이름을 해제한다.
$connections = array(); $next_connection = 0; $denied_key = '';
function mysql_connect($host, $user, $password, $new_link = false) {
    global $connections, $next_connection;
    if (!$new_link) throw new RuntimeException('잠금 전용 새 연결이 아님');
    $connections[++$next_connection] = '';
    return $next_connection;
}
function mysql_query($sql, $link) {
    global $connections, $denied_key;
    if (strpos($sql, 'IS_USED_LOCK') !== false) return array('owned'=>!empty($connections[$link]) ? 1 : 0);
    preg_match("/GET_LOCK\('([^']+)'/", $sql, $m);
    $connections[$link] = ''; // 구버전 서버처럼 이전 잠금 해제
    if ($m[1] === $denied_key || in_array($m[1], $connections, true)) return array('acquired'=>0);
    $connections[$link] = $m[1];
    return array('acquired'=>1);
}
function mysql_fetch_assoc($result) { return $result; }
function mysql_close($link) { global $connections; unset($connections[$link]); }
shop_order_state_lock('order', '123');
shop_order_state_lock('cart', '456');
compat_expect(count($connections) === 2 && in_array(shop_order_lock_name('order','123'),$connections,true), '구형 서버에서도 주문·장바구니 잠금 동시 유지');
shop_order_state_lock('order', '123');
compat_expect(count($connections) === 2, '같은 요청 재획득 시 연결 재사용');
$denied_key = shop_order_lock_name('order', 'blocked');
$denied = false;
try { shop_order_state_lock('order', 'blocked'); } catch (Exception $e) { $denied = true; }
compat_expect($denied && count($connections) === 2, '획득 실패 시 연결 닫기 및 기존 잠금 보존');
shop_order_state_unlock('order','123');
compat_expect(count($connections) === 1 && in_array(shop_order_lock_name('cart','456'),$connections,true), '선택한 잠금만 해제');
shop_order_state_unlock_all();
compat_expect(count($connections) === 0, '종료 정리');
for ($i=0; $i<500; $i++) { shop_order_state_lock('order',(string)$i); shop_order_state_unlock('order',(string)$i); }
compat_expect(count($connections) === 0, '배치 처리 연결 누적 없음');
echo 'PHP '.PHP_VERSION.' 호환 검사 '.$checks."건 통과\n";

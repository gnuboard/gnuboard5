<?php
// 실행: php tests/shop_order_lock_test.php 127.0.0.1:포트 issue48_테스트DB
// root/빈 비밀번호인 격리 MySQL에서 실제 연결 분리·경합·종료 해제를 검사한다.
if (PHP_SAPI !== 'cli' || !isset($argv[2]) || strpos($argv[1], '127.0.0.1:') !== 0 || strpos($argv[2], 'issue48_') !== 0) exit(1);
define('_GNUBOARD_', true);
define('G5_MYSQLI_USE', true);
define('G5_MYSQL_HOST', $argv[1]);
define('G5_MYSQL_USER', 'root');
define('G5_MYSQL_PASSWORD', '');
define('G5_MYSQL_DB', $argv[2]);
define('G5_SHOP_TABLE_PREFIX', 'lock_test_');
require dirname(dirname(__FILE__)).'/lib/shop_order_state.lib.php';
mysqli_report(MYSQLI_REPORT_OFF);
function shop_order_access_fail() { throw new RuntimeException('denied'); }
function lock_expect($ok, $name) { if (!$ok) throw new RuntimeException($name); echo '통과: '.$name."\n"; }
function scalar_query($db, $sql) { $result = mysqli_query($db, $sql); $row = mysqli_fetch_row($result); return $row[0]; }
if (isset($argv[3])) {
    try {
        shop_order_state_lock('order', $argv[4]);
        echo "held\n"; flush();
        if ($argv[3] === '--hold') sleep(30);
    } catch (Exception $e) { echo "blocked\n"; }
    exit;
}
$main = mysqli_connect(G5_MYSQL_HOST, 'root', '', G5_MYSQL_DB);
if (!$main) exit(1);
$id = (string)getmypid();
$main_key = 'g5_point_fixture_'.$id;
scalar_query($main, "SELECT GET_LOCK('$main_key', 0)");
shop_order_state_lock('order', $id);
shop_order_state_lock('cart', $id);
$order_key = shop_order_lock_name('order', $id);
$cart_key = shop_order_lock_name('cart', $id);
$main_id = scalar_query($main, 'SELECT CONNECTION_ID()');
$order_id = scalar_query($main, "SELECT IS_USED_LOCK('$order_key')");
$cart_id = scalar_query($main, "SELECT IS_USED_LOCK('$cart_key')");
lock_expect($order_id && $cart_id && $main_id != $order_id && $main_id != $cart_id && $order_id != $cart_id, '업무·주문·장바구니 잠금의 DB 연결 분리');
lock_expect(scalar_query($main, "SELECT IS_USED_LOCK('$main_key')") == $main_id, '기존 포인트 잠금 보존');
scalar_query($main, "SELECT RELEASE_LOCK('$main_key')");
lock_expect(scalar_query($main, "SELECT IS_USED_LOCK('$order_key')") == $order_id, '포인트 잠금 해제가 주문 잠금에 영향 없음');
$command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' '.escapeshellarg(G5_MYSQL_HOST).' '.escapeshellarg(G5_MYSQL_DB);
$lines = array();
exec($command.' --try '.escapeshellarg($id), $lines, $code);
lock_expect($code === 0 && $lines === array('blocked'), '다른 프로세스의 동일 주문 접근 차단');
shop_order_state_unlock('order', $id);
$lines = array();
exec($command.' --try '.escapeshellarg($id), $lines, $code);
lock_expect($code === 0 && $lines === array('held') && scalar_query($main, "SELECT IS_FREE_LOCK('$order_key')") == 1, '해제 후 획득 및 정상 종료 시 해제');
lock_expect(scalar_query($main, "SELECT IS_USED_LOCK('$cart_key')") == $cart_id, '다른 주문 연결 종료 중 장바구니 잠금 유지');
shop_order_state_unlock_all();
$child = proc_open('exec '.$command.' --hold '.escapeshellarg($id), array(0=>array('pipe','r'),1=>array('pipe','w'),2=>array('pipe','w')), $pipes);
lock_expect(trim(fgets($pipes[1])) === 'held', '중단 검사 프로세스 잠금 획득');
// 잠금 전용 DB 연결의 단절을 재현한다.
$connection = scalar_query($main, "SELECT IS_USED_LOCK('$order_key')");
mysqli_query($main, 'KILL CONNECTION '.(int)$connection);
lock_expect(scalar_query($main, "SELECT GET_LOCK('$order_key', 3)") == 1, 'DB 연결 단절 시 서버 잠금 해제');
scalar_query($main, "SELECT RELEASE_LOCK('$order_key')");
proc_terminate($child);
foreach ($pipes as $pipe) fclose($pipe);
proc_close($child);
shop_order_state_lock('order', $id);
$connection = scalar_query($main, "SELECT IS_USED_LOCK('$order_key')");
mysqli_query($main, 'KILL CONNECTION '.(int)$connection);
$denied = false;
try { shop_order_state_lock('order', $id); } catch (Exception $e) { $denied = true; }
lock_expect($denied, '잠금 연결 유실 후 보유 상태 재사용 거부');
shop_order_state_unlock_all();
mysqli_close($main);
echo "실제 MySQL 연결 잠금 검사 완료\n";

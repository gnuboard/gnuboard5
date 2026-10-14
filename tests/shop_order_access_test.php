<?php
if (PHP_SAPI !== 'cli') exit;
// 실제 DB와 PG 호출 없이 접근 통제 경계를 검증한다.
define('_GNUBOARD_', true);
define('G5_SERVER_TIME', 1800000000);
define('G5_SHOP_URL', '/shop');
function alert($message, $url) { throw new RuntimeException('denied'); }
function get_session($key) { global $session; return isset($session[$key]) ? $session[$key] : ''; }
function set_session($key, $value) { global $session; $session[$key] = $value; }
function sql_escape_string($value) { return addslashes($value); }
function sql_query($query) { global $queries; $queries[] = $query; return 'metadata'; }
function sql_num_rows($result) { global $count; return $count; }
function sql_fetch_array($result) { global $meta; return $meta; }
function sql_fetch($query) {
    global $queries, $payload, $completed;
    $queries[] = $query;
    if (strpos($query, 'select count(*)') !== false) return array('cnt' => 0);
    if (strpos($query, 'select dt_data') === 0) return array('dt_data' => $payload);
    if (strpos($query, 'select pp_tno') === 0) return array('pp_use' => 1, 'pp_tno' => $completed ? 'paid' : '');
    return $completed ? array('od_id' => '123') : array();
}
require __DIR__.'/../lib/shop_order_access.lib.php';
function fixture($personal = false) {
    global $session, $member, $g5, $payload, $meta, $queries, $count, $completed;
    $member = array('mb_id' => '');
    $g5 = array('g5_shop_order_data_table' => 'temporary', 'g5_shop_order_table' => 'orders', 'g5_shop_personalpay_table' => 'personal');
    $data = array('od_name' => '가상 구매자', 'amountValue' => '1000');
    if ($personal) $data['pp_id'] = '123';
    $payload = base64_encode(serialize($data));
    $state = array('pg' => 'toss', 'member' => '', 'cart' => $personal ? '0' : '456', 'personal' => $personal,
        'personal_hash' => 'personal-secret', 'direct' => false, 'time' => '2027-01-15 08:00:00',
        'expires' => G5_SERVER_TIME + 7200, 'digest' => hash('sha256', $payload));
    $session = array('ss_order_id' => '123', 'ss_cart_id' => '456', 'ss_direct' => '',
        'ss_personalpay_id' => '123', 'ss_personalpay_hash' => 'personal-secret', 'ss_order_data_access' => array('123' => $state));
    $meta = array('cart_id' => $state['cart'], 'mb_id' => '', 'dt_pg' => 'toss', 'dt_time' => $state['time']);
    $queries = array(); $count = 1; $completed = false;
}
// 1차 패치의 강한 세션 상태만 새 영속 상태로 전환할 수 있는지 검사한다.
// 새 복귀/승인/비밀번호 통합 검증은 shop_order_state_test.py에서 실제 DB와 실행한다.
$passed = 0;
foreach (array('missing','other','cart','expired','owner','duplicate','tampered','completed') as $case) {
    fixture();
    if ($case === 'missing') $session = array();
    if ($case === 'other') { unset($session['ss_order_data_access']); $session['PAYREQ_MAP'] = array('orderId'=>'123'); }
    if ($case === 'cart') $session['ss_cart_id'] = '999';
    if ($case === 'expired') $session['ss_order_data_access']['123']['expires'] = 1;
    if ($case === 'owner') $member['mb_id'] = 'other';
    if ($case === 'duplicate') $count = 2;
    if ($case === 'tampered') $payload .= 'x';
    if ($case === 'completed') $completed = true;
    $denied = false;
    try { shop_order_access_load_legacy('123','toss'); } catch (RuntimeException $e) { $denied = true; }
    if (!$denied) throw new Exception('과거 상태 전환 거부 실패: '.$case);
    $passed++;
}
fixture();
$data = shop_order_access_load_legacy('123','toss');
if ($data['od_name'] !== '가상 구매자') throw new Exception('검증 가능한 과거 세션 전환 실패');
$passed++;
$filtered = shop_order_filter_data(array('od_name'=>'가상 구매자','od_pwd'=>'secret','arbitrary'=>'secret','amountValue'=>'1000'),'toss');
if (isset($filtered['od_pwd']) || isset($filtered['arbitrary']) || $filtered['amountValue'] !== '1000') throw new Exception('저장 필드 허용 목록 실패');
$passed++;
echo "통과: $passed (과거 상태 전환 경계와 저장 허용 목록)\n";

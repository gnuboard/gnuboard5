<?php
// 실행: php tests/order_completion_point.php
// data/dbconfig.php의 DB 연결을 사용하며 세션 전용 TEMPORARY TABLE만 변경한다.
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
define('G5_TIME_YMDHIS', date('Y-m-d H:i:s'));
$root = dirname(dirname(__FILE__));
require $root.'/data/dbconfig.php';
require $root.'/lib/shop.lib.php';
require $root.'/adm/shop_admin/admin.shop.lib.php';
require $root.'/lib/migration.lib.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$db = new mysqli(G5_MYSQL_HOST, G5_MYSQL_USER, G5_MYSQL_PASSWORD, G5_MYSQL_DB);
$db->set_charset('utf8mb4');
$g5['g5_shop_cart_table'] = 'test_completion_cart_'.getmypid();
$g5['g5_shop_order_table'] = 'test_completion_order_'.getmypid();
$cart = $g5['g5_shop_cart_table'];
$order = $g5['g5_shop_order_table'];
$points = array();
function sql_query($sql, $error = true) { global $db; return $db->query($sql); }
function sql_fetch_array($result) { return $result->fetch_assoc(); }
function sql_fetch($sql) { return sql_fetch_array(sql_query($sql)); }
function insert_point($member, $amount, $content, $table, $id, $action) {
    global $points;
    $points[] = array($member, $amount, $action);
}
function expect_completion($condition, $message) {
    if (!$condition) throw new Exception($message);
}
function completion_row($id) {
    global $cart;
    return sql_fetch("SELECT * FROM $cart WHERE ct_id = $id");
}
function complete_item($id, $status) {
    // 관리자 개별 처리의 실제 UPDATE 구문을 실행한다.
    global $cart, $root, $g5;
    $source = file_get_contents($root.'/adm/shop_admin/orderformcartupdate.php');
    $start = strpos($source, '    $complete_time_sql = get_cart_complete_time_sql($ct_status);');
    $end = strpos($source, '    sql_query($sql);', $start) + strlen('    sql_query($sql);');
    $ct_status = $status;
    $ct_id = $id;
    $ct = completion_row($id);
    $od_id = $ct['od_id'];
    $point_use = $ct['ct_point_use'];
    $stock_use = 0;
    $ct_history = '';
    eval(substr($source, $start, $end - $start));
}
sql_query("CREATE TEMPORARY TABLE $order (od_id bigint PRIMARY KEY, mb_id varchar(255), od_status varchar(255))");
sql_query("CREATE TEMPORARY TABLE $cart (ct_id int PRIMARY KEY, od_id bigint, ct_status varchar(255), ct_point_use int DEFAULT 0, ct_point int DEFAULT 10, ct_qty int DEFAULT 2, ct_stock_use int DEFAULT 0, ct_history text, ct_time datetime, ct_select_time datetime)");
$old = date('Y-m-d H:i:s', time() - 86400 * 30);
$now = G5_TIME_YMDHIS;
sql_query("INSERT INTO $order VALUES (1, 'member', '배송'), (2, '', '완료')");
sql_query("INSERT INTO $cart (ct_id, od_id, ct_status, ct_time, ct_select_time, ct_history) VALUES (1, 1, '완료', '$old', '$old', ''), (2, 1, '배송', '$old', '$old', '')");
$migration = g5_migration_parse_file($root.'/migrations/20260909_002_cart_complete_time.sql');
expect_completion(!isset($migration['error']), '마이그레이션 파싱');
foreach ($migration['statements'] as $statement) {
    sql_query(g5_migration_replace_placeholders($statement['sql']));
}
expect_completion(completion_row(1)['ct_complete_time'] === null, '기존 완료 시각은 NULL 유지');
complete_item(1, '완료');
expect_completion(completion_row(1)['ct_complete_time'] === null, '기존 완료 재저장 시 종전 정책 유지');
change_status(1, '배송', '완료');
expect_completion(completion_row(2)['ct_complete_time'] === $now, '일괄 완료 시각 기록');
expect_completion(completion_row(1)['ct_complete_time'] === null, '일괄 처리에서 기존 완료 상품 유지');
complete_item(2, '배송');
expect_completion(completion_row(2)['ct_complete_time'] === null, '개별 완료 해제');
complete_item(2, '완료');
expect_completion(completion_row(2)['ct_complete_time'] === $now, '개별 재완료 시각 기록');
sql_query("UPDATE $cart SET ct_complete_time = '$old' WHERE ct_id = 2");
complete_item(2, '완료');
expect_completion(completion_row(2)['ct_complete_time'] === $old, '완료 재저장 시 최초 시각 유지');
change_status(1, '완료', '배송');
expect_completion(completion_row(2)['ct_complete_time'] === null, '일괄 완료 해제');
change_status(1, '배송', '완료');
expect_completion(completion_row(2)['ct_complete_time'] === $now, '일괄 재완료');

// 날짜 경계와 적립 루프를 실제 SQL로 검증한다.
sql_query("DELETE FROM $cart");
$cutoff = date('Y-m-d H:i:s', time() - 86400 * 7);
$future = date('Y-m-d H:i:s', time() + 86400);
$rows = array(
    array(1, 1, '완료', 0, $old, $now), // 오래된 장바구니, 오늘 완료
    array(2, 1, '완료', 0, $old, $cutoff), // 정확히 7일 경과
    array(3, 1, '완료', 0, $old, $old),
    array(4, 1, '배송', 0, $old, $old),
    array(5, 1, '취소', 0, $old, $old),
    array(6, 1, '완료', 1, $old, $old),
    array(7, 1, '완료', 0, $old, null), // 기존 완료
    array(8, 1, '완료', 0, $now, null), // 기존 기준에서도 대기
    array(9, 2, '완료', 0, $old, $old), // 비회원
    array(10, 1, '완료', 0, $old, $future)
);
foreach ($rows as $row) {
    list($id, $od_id, $status, $used, $created, $completed) = $row;
    $completed_sql = $completed === null ? 'NULL' : "'$completed'";
    sql_query("INSERT INTO $cart (ct_id, od_id, ct_status, ct_point_use, ct_time, ct_complete_time) VALUES ($id, $od_id, '$status', $used, '$created', $completed_sql)");
}
$default = array('de_point_days' => 7);
save_order_point();
expect_completion($points === array(array('member', 20, '1,2'), array('member', 20, '1,3'), array('member', 20, '1,7')), '배송완료 기간, 기존 정책, 상태, 회원, 수량 검증');
save_order_point();
expect_completion(count($points) === 3, '중복 지급 방지');
$default['de_point_days'] = 0;
save_order_point();
expect_completion(count($points) === 5, '0일이면 오늘 완료 및 기존 당일 상품 지급');
expect_completion(completion_row(10)['ct_point_use'] == 0, '미래 완료 시각 제외');
foreach (array('orderformcartupdate.php', 'orderlistupdate.php') as $file) {
    $source = file_get_contents($root.'/adm/shop_admin/'.$file);
    expect_completion(strpos($source, 'save_order_point();') !== false, $file.' 완료 후 적립 호출');
}
echo "배송완료 시각 기록·유지·초기화, 부분 완료, 기존 상품 전환, 7일·0일 지급, 중복 지급 방지 통과\n";

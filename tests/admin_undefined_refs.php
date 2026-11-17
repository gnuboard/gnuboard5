<?php
// 관리자 화면의 정의되지 않은 함수·변수 참조 회귀 검사.
// - SMS 주소록 초성 검색(ap): 정의된 적 없는 korean_index()를 불러 요청 시 치명적 오류로 멈췄다.
// - 미완료주문 배송비 쿠폰: 정의되지 않은 $send_cost로 할인액을 계산해 할인이 항상 0이었다.
// 실행: php tests/admin_undefined_refs.php
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(dirname(__FILE__));
$count = 0;
function ok($cond, $label) { global $count; if (!$cond) { fwrite(STDERR, "실패: $label\n"); exit(1); } $count++; }
// 주석을 뺀 코드 토큰
function tokens($path) {
    global $root;
    $out = array();
    foreach (token_get_all(file_get_contents($root.'/'.$path)) as $t) {
        if (is_array($t) && ($t[0] === T_COMMENT || $t[0] === T_DOC_COMMENT || $t[0] === T_WHITESPACE)) continue;
        $out[] = $t;
    }
    return $out;
}
function code($path) { $s = ''; foreach (tokens($path) as $t) $s .= is_array($t) ? $t[1] : $t; return $s; }

// 1. SMS 주소록: korean_index()를 부르지 않고, ap 요청값을 무시한다.
foreach (array('adm/sms_admin/num_book.php', 'adm/sms_admin/ajax.sms_write_person.php') as $path) {
    $c = code($path);
    ok(stripos($c, 'korean_index(') === false, $path.' korean_index 호출 없음');
    ok(strpos($c, "\$sql_korean='';\$ap=0;") !== false, $path.' 초성 조건 비움');
}
ok(!function_exists('korean_index'), 'korean_index 미정의 전제');

// 2. 미완료주문 화면·처리: 배송비 쿠폰은 $od_send_cost 기준으로 계산하고 $send_cost를 쓰지 않는다.
foreach (array('adm/shop_admin/inorderform.php', 'adm/shop_admin/inorderformupdate.php') as $path) {
    $vars = array();
    foreach (tokens($path) as $t) if (is_array($t) && $t[0] === T_VARIABLE) $vars[$t[1]] = true;
    ok(!isset($vars['$send_cost']), $path.' 정의되지 않은 $send_cost 없음');
    $c = code($path);
    ok(strpos($c, "\$od_send_cost=get_sendcost(\$od['cart_id']);") !== false, $path.' 배송비 계산');
    ok(strpos($c, "\$dc=floor((\$od_send_cost*(\$cp['cp_price']/100))/\$cp['cp_trunc'])*\$cp['cp_trunc'];") !== false, $path.' 정률 배송비 쿠폰');
    ok(strpos($c, "if(\$dc>\$od_send_cost)\$dc=\$od_send_cost;") !== false, $path.' 배송비 쿠폰 상한');
}

echo "admin_undefined_refs: {$count}건 통과\n";

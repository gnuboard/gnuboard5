<?php
// 실제 공통 함수를 사용해 주문 필드 복원 시 0 보존과 HTML 이스케이프를 검증한다.
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
require dirname(dirname(__FILE__)).'/lib/common.lib.php';
require dirname(dirname(__FILE__)).'/lib/shop.lib.php';

$checks = 0;
function order_field_expect($actual, $expected, $name) {
    global $checks;
    if ($actual !== $expected) {
        fwrite(STDERR, '실패: '.$name.PHP_EOL);
        exit(1);
    }
    $checks++;
}

// 면세금액 0원, 면세 상품 포함 주문, 빈 값의 복원 결과를 구분한다.
foreach (array('0', 0, 0.0, '1000', '', null, false) as $value) {
    $expected = '<input type="hidden" name="taxFreeAmount" value="'.(string)$value.'">'.PHP_EOL;
    order_field_expect(make_order_field(array('taxFreeAmount' => $value), array()), $expected, '면세금액 복원: '.var_export($value, true));
}

order_field_expect(
    make_order_field(array('amounts' => array('0', 0, '1000')), array()),
    '<input type="hidden" name="amounts[0]" value="0">'.PHP_EOL.
    '<input type="hidden" name="amounts[1]" value="0">'.PHP_EOL.
    '<input type="hidden" name="amounts[2]" value="1000">'.PHP_EOL,
    '배열 필드의 0 보존'
);

$unsafe = '"<>&amp;';
$escaped = '&#034;&lt;&gt;&#038;amp;';
order_field_expect(
    make_order_field(array($unsafe => $unsafe, 'items' => array($unsafe => $unsafe)), array()),
    '<input type="hidden" name="'.$escaped.'" value="'.$escaped.'">'.PHP_EOL.
    '<input type="hidden" name="items['.$escaped.']" value="'.$escaped.'">'.PHP_EOL,
    '필드명과 일반·배열 값의 HTML 이스케이프 유지'
);
order_field_expect(make_order_field(array('excluded' => '0'), array('excluded')), '', '제외 필드 유지');

// SQL 이스케이프된 저장 필드와 일반 호출의 원본 문자열을 구분한다.
$original = "O'Reilly \"quoted\" & C:\\new\\item";
$stored = addslashes($original);
$decode = function ($html) {
    preg_match('/value="([^"]*)"/', $html, $match);
    return html_entity_decode($match[1], ENT_QUOTES, 'UTF-8');
};
order_field_expect($decode(make_order_field(array('orderName'=>$stored), array(), true)), $original, '저장 상품명의 따옴표·역슬래시 복원');
order_field_expect($decode(make_order_field(array('orderName'=>$original), array())), $original, '일반 호출의 원본 역슬래시 보존');
order_field_expect($decode(make_order_field(array('items'=>array($stored)), array(), true)), $original, '배열 저장 필드 복원');
$json = json_encode(array(array('id'=>'item', 'name'=>$original, 'unitPrice'=>3000, 'quantity'=>1)));
order_field_expect($decode(make_order_field(array('escrowProducts'=>addslashes($json)), array(), true)), $json, '에스크로 JSON의 이중 역슬래시 제거 방지');
$injection = '\" onmouseover=\"alert(1)\"><script>alert(1)</script>';
order_field_expect(make_order_field(array('orderName'=>addslashes($injection)), array(), true), make_order_field(array('orderName'=>$injection), array()), '복원 후 HTML 이스케이프 유지');
order_field_expect(make_order_field(array('taxFreeAmount'=>'0'), array(), true), '<input type="hidden" name="taxFreeAmount" value="0">'.PHP_EOL, 'SQL 복원 모드에서도 면세금액 0 보존');
order_field_expect(make_order_field(array('taxFreeAmount'=>'3000'), array(), true), '<input type="hidden" name="taxFreeAmount" value="3000">'.PHP_EOL, 'SQL 복원 모드의 양수 면세금액 보존');
order_field_expect(make_order_field(array('excluded'=>$stored), array('excluded'), true), '', '복원 모드의 제외 필드 유지');

echo '통과: '.$checks.' (주문 필드 복원)'.PHP_EOL;

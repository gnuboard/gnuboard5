<?php
// 실행: php tests/shop_cart_validation.php (DB 연결 없이 공통 규칙 검증)
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
require dirname(__FILE__).'/../lib/shop.cartvalidate.lib.php';
$checks = 0;
function check_cart($name, $item, $options, $rows, $expected, $stored = false)
{
    global $checks;
    $result = shop_validate_cart_rows($item, $options, $rows, $stored);
    $actual = $result['error'] === '' ? $result['total'] : false;
    if ($actual !== $expected) {
        fwrite(STDERR, $name.': '.json_encode($result)."\n");
        exit(1);
    }
    $checks++;
}
function test_row($id = '', $type = '0', $qty = '1')
{
    return array('io_id' => $id, 'io_type' => $type, 'ct_qty' => $qty);
}
$item = array('it_id' => 'item', 'it_use' => 1, 'it_tel_inq' => 0, 'it_price' => 50000, 'it_stock_qty' => 100, 'it_buy_min_qty' => 0, 'it_buy_max_qty' => 0);
$base = test_row();
$addon = array('it_id' => 'item', 'io_id' => 'wrap', 'io_type' => '1', 'io_use' => 1, 'io_price' => 1000, 'io_stock_qty' => 100);
$selection = array('it_id' => 'item', 'io_id' => 'red', 'io_type' => '0', 'io_use' => 1, 'io_price' => 2000, 'io_stock_qty' => 100);
check_cart('기본상품', $item, array(), array($base), 50000);
check_cart('빈 보조옵션', $item, array(), array(test_row('', '1')), false);
check_cart('정상 행 뒤 빈 보조옵션', $item, array(), array($base, test_row('', '1')), false);
foreach (array('', '01', '11', '2', '-1', '1x', '1.0', true, array('1')) as $type)
    check_cart('종류 형식', $item, array(), array(test_row('', $type)), false);
foreach (array('0', '-1', '1.5', '1x', '2147483648', array(1), true) as $qty)
    check_cart('수량 형식', $item, array(), array(test_row('', '0', $qty)), false);
check_cart('수량 재고 초과', $item, array(), array(test_row('', '0', '101')), false);
check_cart('동일 옵션 누적 재고', $item, array(), array(test_row('', '0', '60'), test_row('', '0', '60')), false);
check_cart('정상 추가옵션', $item, array($addon), array($base, test_row('wrap', '1')), 51000);
check_cart('첫 행 추가옵션', $item, array($addon), array(test_row('wrap', '1'), $base), false);
check_cart('추가옵션 단독', $item, array($addon), array(test_row('wrap', '1')), false);
check_cart('추가옵션 종류 불일치', $item, array($addon), array(test_row('wrap', '0')), false);
check_cart('없는 옵션', $item, array(), array($base, test_row('wrap', '1')), false);
$disabled = $addon; $disabled['io_use'] = 0;
check_cart('비활성 옵션', $item, array($disabled), array($base, test_row('wrap', '1')), false);
$foreign = $addon; $foreign['it_id'] = 'other';
check_cart('타 상품 옵션', $item, array($foreign), array($base, test_row('wrap', '1')), false);
check_cart('모호한 중복 옵션', $item, array($addon, $addon), array($base, test_row('wrap', '1')), false);
check_cart('필수 선택옵션 누락', $item, array($selection), array($base), false);
check_cart('정상 선택옵션', $item, array($selection), array(test_row('red')), 52000);
$disabled = $selection; $disabled['io_use'] = 0;
check_cart('모든 선택옵션 비활성', $item, array($disabled), array($base), false);
$same_id = $addon; $same_id['io_id'] = 'red';
check_cart('다른 종류의 동일 ID', $item, array($selection, $same_id), array(test_row('red'), test_row('red', '1')), 53000);
$free = $item; $free['it_price'] = 0;
check_cart('정상 무료상품', $free, array(), array($base), 0);
$discount = $selection; $discount['io_price'] = -50000;
check_cart('정상 0원 선택옵션', $item, array($discount), array(test_row('red')), 0);
$discount['io_price'] = -50001;
check_cart('음수 합계', $item, array($discount), array(test_row('red')), false);
$negative = $addon; $negative['io_price'] = -1;
check_cart('음수 추가옵션', $item, array($negative), array($base, test_row('wrap', '1')), false);
$stored = $base; $stored['ct_price'] = '50000'; $stored['io_price'] = '0';
check_cart('정상 저장 행', $item, array(), array($stored), 50000, true);
$stored['io_type'] = '1';
check_cart('과거 조작 행', $item, array(), array($stored), false, true);
$stored['io_type'] = '0'; $stored['ct_price'] = 1;
check_cart('상품가격 변경', $item, array(), array($stored), false, true);
$stored['ct_price'] = 50000; $stored['io_price'] = 1;
check_cart('옵션가격 불일치', $item, array(), array($stored), false, true);
foreach (array('it_use' => 0, 'it_tel_inq' => 1, 'it_id' => '') as $key => $value) {
    $invalid = $item; $invalid[$key] = $value;
    check_cart('구매불가 상품', $invalid, array(), array($base), false);
}
$limited = $item; $limited['it_buy_min_qty'] = 2;
check_cart('최소 수량', $limited, array(), array($base), false);
$limited = $item; $limited['it_buy_max_qty'] = 1;
check_cart('최대 수량', $limited, array(), array(test_row('', '0', '2')), false);
echo $checks."개 검증 통과\n";

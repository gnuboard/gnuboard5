<?php
// 실행: php tests/shop_category_navigation.php [--json]
// DB 대신 분류 조회 대역을 사용하여 실제 공통 함수와 스킨을 실행한다.
define('_GNUBOARD_', true);
define('G5_SHOP_CSS_URL', '/css');
define('G5_SHOP_SKIN_URL', '/skin');
define('G5_SHOP_URL', '/shop');
define('G5_JS_URL', '/js');
$g5 = array('g5_shop_category_table'=>'categories', 'g5_shop_item_table'=>'items', 'title'=>'분류');
$fixture_ids = array('10','20','1010','1020','101010','101020','10101010','10101020','1010101010','1010101020');
function run_replace($name, $data, $cache) { return $data; }
function shop_category_url($id) { return '/shop/list.php?ca_id='.$id; }
function get_text($text) { return htmlspecialchars($text, ENT_QUOTES, 'UTF-8'); }
function add_stylesheet($html, $priority) {}
function add_javascript($html, $priority) {}
function sql_query($sql) {
    global $fixture_ids;
    preg_match("/ca_id like '([^']*)%'/", $sql, $prefix);
    preg_match("/length\(ca_id\) = '?([0-9]+)/", $sql, $length);
    $result = (object)array('rows'=>array(), 'index'=>0);
    foreach ($fixture_ids as $id) {
        if ((!isset($prefix[1]) || strpos($id, $prefix[1]) === 0) && strlen($id) == $length[1]) {
            $result->rows[] = array('ca_id'=>$id, 'ca_name'=>'분류 '.$id);
        }
    }
    return $result;
}
function sql_fetch_array($result) { return isset($result->rows[$result->index]) ? $result->rows[$result->index++] : false; }
function sql_fetch($sql) { return array('cnt'=>3); }
function expect_category($condition, $message) { if (!$condition) throw new RuntimeException($message); }
require dirname(__DIR__).'/lib/shop.data.lib.php';
$artifacts = array();
$cases = array(
    '10'=>array(array('1010','1020')),
    '20'=>array(array('10','20')),
    '1010'=>array(array('101010','101020')),
    '101010'=>array(array('10101010','10101020')),
    '101020'=>array(array('101010','101020')),
    '10101010'=>array(array('1010101010','1010101020')),
    '10101020'=>array(array('10101010','10101020')),
    '1010101010'=>array(array('1010101010','1010101020')),
);
foreach ($cases as $code=>$expected) {
    $ca_id = (string)$code;
    $groups = get_shop_category_menu_groups($ca_id);
    $actual = array();
    foreach ($groups as $group) {
        $ids = array();
        foreach ($group['categories'] as $row) $ids[] = $row['ca_id'];
        $actual[] = $ids;
    }
    expect_category($actual === $expected, $ca_id.' 메뉴 단계');
    foreach (array('skin/shop/basic', 'theme/basic/skin/shop/basic') as $skin) {
        foreach (array(false, true) as $item_view) {
            $it_id = $item_view ? 'item' : '';
            $it = $item_view ? array('it_id'=>'item', 'ca_id'=>$ca_id) : array();
            ob_start(); include dirname(__DIR__).'/'.$skin.'/navigation.skin.php'; $nav = ob_get_clean();
            preg_match_all('/value="([0-9]+)"[^>]* selected/', $nav, $selected);
            $path = array();
            for ($depth=1; $depth<=strlen($ca_id)/2; $depth++) $path[] = substr($ca_id, 0, $depth*2);
            expect_category($selected[1] === $path, $skin.' '.$ca_id.' breadcrumb 현재 경로');
            $artifacts[$skin][$ca_id] = $nav;
        }
    }
    foreach (array('skin/shop/basic', 'theme/basic/skin/shop/basic', 'mobile/skin/shop/basic', 'theme/basic/mobile/skin/shop/basic') as $skin) {
        ob_start(); include dirname(__DIR__).'/'.$skin.'/listcategory.skin.php'; $menu = ob_get_clean();
        preg_match_all('/href="\/shop\/list.php\?ca_id=([0-9]+)"/', $menu, $links);
        $flattened = array(); foreach ($expected as $group) $flattened = array_merge($flattened, $group);
        expect_category($links[1] === $flattened, $skin.' '.$ca_id.' 메뉴 링크');
        preg_match_all('/aria-current="page" href="\/shop\/list.php\?ca_id=([0-9]+)"/', $menu, $selected);
        expect_category($selected[1] === (in_array($ca_id, $flattened, true) ? array($ca_id) : array()), $skin.' 현재 메뉴 표시');
        $artifacts[$skin][$ca_id] = (isset($artifacts[$skin][$ca_id]) ? $artifacts[$skin][$ca_id] : '').$menu;
    }
}
// 5단계 전체 트리를 전역 메뉴에 추가하지 않았는지 확인한다.
$tree = get_shop_category_array(true);
expect_category(!isset($tree['10']['1010']['101010']['10101010']), '공통 분류 트리 호환성');
if (isset($argv[1]) && $argv[1] === '--json') echo json_encode($artifacts);
else echo "8개 분류 경로: PC·테마 breadcrumb 목록/상세 및 4개 스킨 메뉴·선택 상태 통과\n";

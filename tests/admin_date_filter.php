<?php
if (PHP_SAPI !== 'cli') exit;
// 실행: php tests/admin_date_filter.php [--json] (실제 DB·관리자 세션 사용 안 함)
define('_GNUBOARD_', true);
$root = dirname(__DIR__);
require $root.'/adm/shop_admin/date_filter.lib.php';
function expect_date($condition, $message) {
    if (!$condition) throw new RuntimeException($message);
}
// 의존 함수는 실제 소스에서 읽고, DB·인증·화면 헤더만 대역으로 교체한다.
function load_date_test_function($file, $name) {
    $source = file_get_contents($file);
    $start = strpos($source, 'function '.$name.'(');
    if ($start === false) throw new RuntimeException('함수 없음: '.$name);
    $code = ''; $depth = 0; $opened = false;
    foreach (token_get_all('<?php '.substr($source, $start)) as $token) {
        if (is_array($token) && $token[0] === T_OPEN_TAG) continue;
        if ($token === '{' || (is_array($token) && in_array($token[0], array(T_CURLY_OPEN, T_DOLLAR_OPEN_CURLY_BRACES), true))) { $depth++; $opened = true; }
        if ($token === '}') $depth--;
        $code .= is_array($token) ? $token[1] : $token;
        if ($opened && $depth === 0) { eval($code); return; }
    }
    throw new RuntimeException('함수 추출 실패: '.$name);
}
foreach (array('get_paging', 'clean_xss_tags', 'get_search_string') as $name) load_date_test_function($root.'/lib/common.lib.php', $name);
load_date_test_function($root.'/lib/shop.lib.php', 'title_sort');
load_date_test_function($root.'/adm/admin.lib.php', 'get_sanitize_input');
load_date_test_function($root.'/common.php', 'sql_escape_string');
function sql_query($sql) { $GLOBALS['date_test_queries'][] = $sql; return true; }
function sql_num_rows($result) { return 30; }
function sql_fetch_array($result) { return false; }
function auth_check_menu($auth, $menu, $permission) { $GLOBALS['date_test_auth'] = array($menu, $permission); }
function alert($message) { throw new InvalidArgumentException($message); }

$fixture = sys_get_temp_dir().'/g5-date-filter-'.bin2hex(random_bytes(6));
mkdir($fixture); mkdir($fixture.'/jquery-ui');
file_put_contents($fixture.'/_common.php', '<?php');
file_put_contents($fixture.'/admin.head.php', '<?php echo "<!-- report-header -->";');
file_put_contents($fixture.'/admin.tail.php', '<?php');
file_put_contents($fixture.'/jquery-ui/datepicker.php', '<?php');
file_put_contents($fixture.'/date_filter.lib.php', '<?php'); // 실제 함수는 위에서 로드됨
register_shutdown_function(function () use ($fixture) {
    foreach (array('_common.php', 'admin.head.php', 'admin.tail.php', 'date_filter.lib.php', 'jquery-ui/datepicker.php') as $file) unlink($fixture.'/'.$file);
    rmdir($fixture.'/jquery-ui'); rmdir($fixture);
});
define('G5_ADMIN_PATH', $fixture);
define('G5_PLUGIN_PATH', $fixture);
define('G5_IS_MOBILE', false);

function render_date_report($name, $params) {
    global $root, $fixture, $sort1, $sort2, $page, $doc, $fr_date, $to_date;
    $g5 = array('g5_shop_wish_table' => 'wish', 'g5_shop_item_table' => 'item', 'g5_shop_category_table' => 'category', 'g5_shop_cart_table' => 'cart');
    $config = array('cf_page_rows' => 10, 'cf_mobile_pages' => 5, 'cf_write_pages' => 5);
    $auth = array(); $page = isset($params['page']) ? (int) $params['page'] : 1;
    $_GET = array();
    foreach ($params as $key => $value) $_GET[$key] = is_string($value) ? sql_escape_string($value) : $value;
    $_SERVER['SCRIPT_NAME'] = '/adm/shop_admin/'.$name.'.php';
    $GLOBALS['date_test_queries'] = array();
    $GLOBALS['date_test_auth'] = null;
    http_response_code(200);
    $cwd = getcwd(); chdir($fixture); ob_start();
    try { include $root.'/adm/shop_admin/'.$name.'.php'; }
    catch (InvalidArgumentException $e) { echo '조회 기간 오류'; }
    finally { $html = ob_get_clean(); chdir($cwd); }
    return array('html' => $html, 'status' => http_response_code(), 'queries' => $GLOBALS['date_test_queries'], 'auth' => $GLOBALS['date_test_auth']);
}

$bad_dates = array(
    '1" autofocus onfocus=document.documentElement.dataset.g5xss=1 x="',
    "1' onfocus=alert(1)", '20260909" onfocus="alert(1)', '20260909\\', '<svg onload=alert(1)>1',
    '', '20260229', '20260431', '20261301', '20260001', '20260900', '00000101', '2026099', '202609090',
    "20260909\n", "20260909\r\n", ' 20260909', '20260909 ', '２０２６０９０９', '2026-09-09',
    array('20260909'), array(array('x')), null, 20260909, true
);
$results = array();
foreach (array('wishlist' => '500140', 'itemsellrank' => '500100') as $name => $menu) {
    foreach (array('fr_date', 'to_date') as $field) {
        foreach ($bad_dates as $i => $bad) {
            $params = array('fr_date' => '20260101', 'to_date' => '20261231'); $params[$field] = $bad;
            $result = render_date_report($name, $params);
            expect_date($result['status'] === 400 && !$result['queries'], $name.' '.$field.' 잘못된 입력 '.$i);
            expect_date(strpos($result['html'], '<input') === false && strpos($result['html'], 'onfocus') === false, '거부 응답에 입력 반영 금지');
            expect_date($result['auth'] === array($menu, 'r'), '메뉴 읽기 권한 검사 유지');
            if ($i === 0) $results[$name.'-'.$field.'-blocked'] = $result;
        }
    }
    foreach (array(array('fr_date' => '20260909'), array('to_date' => '20260909'), array('fr_date' => '', 'to_date' => ''), array('fr_date' => '20260910', 'to_date' => '20260909')) as $params) {
        $result = render_date_report($name, $params);
        expect_date($result['status'] === 400 && !$result['queries'], '누락·빈값·역전 범위 거부');
    }
    $initial = render_date_report($name, array());
    expect_date($initial['status'] === 200 && count($initial['queries']) === 3, '최초 전체 조회 유지');
    expect_date(strpos(implode('', $initial['queries']), 'between') === false, '최초 조회 기간 조건 없음');
    $results[$name.'-initial'] = $initial;
    $sort = $name === 'wishlist' ? 'wi_time' : 'ct_status_1';
    $valid = render_date_report($name, array('fr_date' => '20240229', 'to_date' => '20240301', 'sort1' => $sort, 'sort2' => 'asc', 'sel_ca_id' => '10'));
    expect_date($valid['status'] === 200, '윤년 정상 범위');
    expect_date(strpos($valid['queries'][0], "between '2024-02-29 00:00:00' and '2024-03-01 23:59:59'") !== false, '정상 SQL 기간');
    foreach (array('initial' => $initial, 'valid' => $valid) as $kind => $result) {
        preg_match_all('/href="([^"]+)"/', $result['html'], $links);
        $paging_count = 0;
        foreach ($links[1] as $link) {
            $url = html_entity_decode($link, ENT_QUOTES, 'UTF-8');
            parse_str((string) parse_url($url, PHP_URL_QUERY), $params);
            if (!isset($params['page'])) continue;
            $paging_count++;
            expect_date(!isset($params['amp;fr_date']), 'URL 중복 인코딩 없음');
            if ($kind === 'initial') expect_date(!isset($params['fr_date']) && !isset($params['to_date']), '최초 조회 링크에 빈 날짜 없음');
            else expect_date($params['fr_date'] === '20240229' && $params['to_date'] === '20240301' && $params['sel_ca_id'] === '10', '검색·분류 유지');
            $next = render_date_report($name, $params);
            expect_date($next['status'] === 200, '정렬·페이지 이동 재조회 성공');
        }
        expect_date($paging_count >= 2, '실제 페이지 링크 검증');
    }
    $results[$name.'-valid'] = $valid;
    // 검증 단계가 우회되더라도 실제 input 출력의 속성 인코딩이 동작하는지 확인한다.
    $fr_date = $to_date = $bad_dates[0];
    preg_match_all('/<input type="text" name="(?:fr_date|to_date)"[^\r\n]+/', file_get_contents($root.'/adm/shop_admin/'.$name.'.php'), $inputs);
    ob_start(); eval('?>'.implode("\n", $inputs[0]));
    $results[$name.'-encoded-output'] = array('html' => ob_get_clean(), 'status' => 200, 'queries' => array());

}
if (in_array('--json', $argv, true)) echo json_encode($results, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
else echo "날짜 필터 100개 잘못된 입력 및 누락·역전·최초 조회·윤년·정렬·페이징 회귀 검증 통과\n";

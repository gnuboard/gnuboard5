<?php
// KVE-2026-3256 상품·배송 엑셀 일괄처리 입력 정화·분류 소유자·출력 인코딩 회귀 검사. DB와 네트워크를 쓰지 않는다.
// 실행: php8.4 tests/kve_3256.php [검사할 코드 루트, 기본은 이 저장소]
// 대상 코드의 함수(정화·출력 함수, 배송조회 링크)를 소스에서 꺼내 실행하고, 처리기·스킨은 소스 구조를 검사한다.
// 수정 전 코드를 루트로 주면 정화 함수가 없거나 출력이 인코딩되지 않아 실패한다.
if (PHP_SAPI !== 'cli') exit(1);

$root = isset($argv[1]) ? rtrim($argv[1], '/') : dirname(__DIR__);
define('_GNUBOARD_', true);
define('G5_DELIVERY_COMPANY', '(로젠택배^https://www.ilogen.com/m/personal/trace.pop/^1588-9988)(테스트택배^https://example.invalid/track?a=1&no=^000-0000)');

$passed = 0; $failed = 0;
function check($ok, $label, $detail = null) {
    global $passed, $failed;
    if ($ok) { $passed++; echo "PASS $label\n"; }
    else { $failed++; echo "FAIL $label".($detail !== null ? ' -- '.substr(var_export($detail, true), 0, 400) : '')."\n"; }
}
function src($p) { global $root; return is_file($root.'/'.$p) ? file_get_contents($root.'/'.$p) : ''; }
// 소스에서 최상위 함수 정의를 꺼내 정의한다. 없으면 false.
function load_function($file, $name) {
    if (function_exists($name)) return true;
    if (!preg_match('/^function '.preg_quote($name, '/').'\s*\(.*?^\}/ms', src($file), $m)) return false;
    eval($m[0]);
    return function_exists($name);
}
foreach (array('get_text', 'html_symbol', 'clean_xss_tags', 'clean_xss_attributes', 'utf8_strcut') as $f) {
    if (!load_function('lib/common.lib.php', $f)) { echo "FAIL lib/common.lib.php 함수 $f 없음\n"; exit(1); }
}
$has_clean = load_function('adm/shop_admin/admin.shop.lib.php', 'shop_admin_clean_item_text');
$has_ca = load_function('adm/shop_admin/admin.shop.lib.php', 'shop_admin_clean_category_id');
$has_id = load_function('adm/shop_admin/admin.shop.lib.php', 'shop_admin_clean_item_id');
$has_inquiry = load_function('lib/shop.lib.php', 'get_delivery_inquiry');

$payloads = array(
    '<script>alert(1)</script>상품',
    '<img src=x onerror=alert(1)>이미지',
    '"><svg onload=alert(1)>속성',
    "<b onmouseover='alert(1)'>굵게</b>",
    '<a href="javascript:alert(1)">링크</a>',
    "  앞뒤 공백 상품명  ",
    "O'Reilly \"따옴표\" & 기호 \\ 역슬래시",
    '정상 상품명 123',
);

// ---------- 1. 공통 정화 함수 ----------
check($has_clean, 'shop_admin_clean_item_text() 정의(admin.shop.lib.php)');
check($has_ca, 'shop_admin_clean_category_id() 정의(admin.shop.lib.php)');
if ($has_clean) {
    foreach (array('it_name', 'it_maker', 'it_origin', 'it_brand', 'it_model') as $key) {
        $bad = array();
        foreach ($payloads as $p) {
            $v = shop_admin_clean_item_text($key, $p);
            if (strpos($v, '<') !== false || preg_match('/on\w+\s*=/i', $v)) $bad[] = array($p, $v);
        }
        check(!$bad, "$key: 태그·이벤트 속성 제거", $bad);
    }
    // 일반 상품 등록(itemformupdate.php)의 기존 식과 결과가 같아야 한다(동작 불변).
    $diff = array();
    foreach ($payloads as $p) {
        $slashed = addslashes($p);
        $old_name = addslashes(strip_tags(clean_xss_attributes(trim(stripslashes($slashed)))));
        $old_text = addslashes(strip_tags(clean_xss_attributes(stripslashes($slashed))));
        $old_basic = preg_replace('#<script(.*?)>(.*?)<\/script>#is', '', $slashed);
        if ($old_name !== addslashes(shop_admin_clean_item_text('it_name', $slashed, true))) $diff[] = array('it_name', $p);
        if ($old_text !== addslashes(shop_admin_clean_item_text('it_maker', $slashed, true))) $diff[] = array('it_maker', $p);
        if ($old_text !== addslashes(shop_admin_clean_item_text('it_price', $slashed, true))) $diff[] = array('it_price', $p);
        if ($old_basic !== addslashes(shop_admin_clean_item_text('it_basic', $slashed, true))) $diff[] = array('it_basic', $p);
    }
    check(!$diff, '일반 상품 등록의 기존 정화 결과와 동일(it_name·텍스트 항목·it_basic)', $diff);
    $v = shop_admin_clean_item_text('it_basic', '<p>설명</p><script src="//x"></script><SCRIPT>alert(1)</SCRIPT>끝');
    check($v === '<p>설명</p>끝', 'it_basic: script 요소 제거, 허용 HTML 유지(출력 때 conv_content로 정화)', $v);
    check(shop_admin_clean_item_text('it_name', array('<x>')) === '' && shop_admin_clean_item_text('it_name', null) === ''
        && shop_admin_clean_item_text('it_name', 12000.0) === '12000', '배열·null은 빈 값, 숫자 셀은 문자열');
}
if ($has_ca) {
    check(shop_admin_clean_category_id("10'\"<b>20") === '10b20' && shop_admin_clean_category_id(1010.0) === '1010'
        && shop_admin_clean_category_id(array('10')) === '', '분류코드는 영문·숫자만 남김');
}
check($has_id, 'shop_admin_clean_item_id() 정의(admin.shop.lib.php)');
if ($has_id) {
    $cases = array(
        array('AB2E3', 'AB2E3'), array('kv3e5a01x1', 'kv3e5a01x1'), array('2E3XYZ', '2E3XYZ'), array('SKU-1e5_b', 'SKU-1e5_b'),
        array('1.23457E+11', '123457000000'), array(' 9.87654321E+11 ', '987654321000'), array('2E3', '2000'),
        array(123457000000.0, '123457000000'), array(1.2345678901234E+15, '1234567890123400'), array(1234, '1234'),
        array("A-1_b'<x>\"", 'A-1_bx'), array(array('1'), ''), array(null, ''),
    );
    $bad = array();
    foreach ($cases as $c) { $v = shop_admin_clean_item_id($c[0]); if ($v !== $c[1]) $bad[] = array($c[0], $v, $c[1]); }
    check(!$bad, '상품코드: 값 전체가 지수 표기일 때만 숫자로 변환, 중간에 "숫자 e 숫자"가 든 코드는 유지', $bad);
}

// ---------- 2. 상품 엑셀 처리기 ----------
$s = src('adm/shop_admin/itemexcelupdate.php');
$tok = strpos($s, 'check_admin_token();'); $open = strpos($s, 'g5_spreadsheet_upload_open(');
check($tok !== false && $open !== false && $tok < $open, '상품 엑셀: 파일을 읽기 전에 관리자 토큰 검사');
check(preg_match('/<input type="hidden" name="token" value="<\?php echo get_admin_token\(\); \?>">/', src('adm/shop_admin/itemexcel.php')) === 1
    && strpos(src('adm/shop_admin/itemexcel.php'), 'action="./itemexcelupdate.php"') !== false, '상품 엑셀 폼에 관리자 토큰');
$bad = array();
foreach (array('it_name', 'it_maker', 'it_origin', 'it_brand', 'it_model', 'it_basic') as $key) {
    if (!preg_match('/\$'.$key.'\s*=\s*addslashes\(shop_admin_clean_item_text\(\''.$key.'\', \$rowData\[0\]\[\$j\+\+\]\)\);/', $s)) $bad[] = $key;
}
check(!$bad, '상품 엑셀: 상품명·제조사·원산지·브랜드·모델·기본설명에 공통 정화 함수 적용', $bad);
$bad = array();
foreach (array('ca_id', 'ca_id2', 'ca_id3') as $key) {
    if (!preg_match('/\$'.$key.'\s*=\s*shop_admin_clean_category_id\(\$rowData\[0\]\[\$j\+\+\]\);/', $s)) $bad[] = $key;
}
check(!$bad, '상품 엑셀: 분류코드 영문·숫자만', $bad);
check(preg_match('/\$it_id\s*=\s*shop_admin_clean_item_id\(\$rowData\[0\]\[\$j\+\+\]\);/', $s) === 1
    && strpos($s, "sprintf(\"%.0f\"") === false, '상품 엑셀: 상품코드에 공통 함수 적용(부분 일치 지수 변환 제거)');
check(preg_match('/if \(\$is_admin !== \'super\'\) \{.*?foreach \(array\(\$ca_id, \$ca_id2, \$ca_id3\) as \$check_ca_id\).*?shop_admin_category_owner_check\(\$check_ca_id\).*?\$fail_reasons\[\].*?continue;/s', $s) === 1
    && strpos($s, 'shop_admin_category_owner_check') < strpos($s, 'INSERT INTO'), '상품 엑셀: 위임 관리자는 행마다 기본·2·3차 분류 소유자 확인 후 실패 처리(INSERT 전)');
check(strpos($s, "array_map('get_text', \$fail_reasons)") !== false, '상품 엑셀: 실패 사유를 인코딩해 표시');
// itemformupdate가 같은 함수를 쓴다.
$f = src('adm/shop_admin/itemformupdate.php');
check(strpos($f, "shop_admin_clean_item_text('it_name', \$_POST['it_name'], true)") !== false
    && strpos($f, "shop_admin_clean_item_text(\$key, \$_POST[\$key], true)") !== false
    && strpos($f, "shop_admin_clean_item_text('it_basic'") !== false, '일반 상품 등록도 같은 정화 함수 사용');

// ---------- 3. 배송 엑셀 처리기 ----------
$d = src('adm/shop_admin/orderdeliveryupdate.php');
$ok = true; $bad = array();
foreach (array(8 => 'od_delivery_company', 9 => 'od_invoice') as $col => $key) {
    $re = '/\$'.$key.'\s*=\s*isset\(\$rowData\[0\]\['.$col.'\]\) && is_scalar\(\$rowData\[0\]\['.$col.'\]\) \? clean_xss_tags\(\(string\)\$rowData\[0\]\['.$col.'\], 1, 1, 255\) : \'\';/';
    if (!preg_match($re, $d)) $bad[] = $key;
}
check(!$bad, '배송 엑셀: 배송회사·운송장에 clean_xss_tags(..., 1, 1, 255)', $bad);
$bad = array();
foreach ($payloads as $p) {
    $v = clean_xss_tags($p, 1, 1, 255);
    if (strpos($v, '<') !== false || stripos($v, 'javascript:') !== false) $bad[] = array($p, $v);
}
$long = clean_xss_tags(str_repeat('가', 300), 1, 1, 255);
check(!$bad && mb_strlen($long, 'UTF-8') === 255, '배송 값 정화 결과에 태그 없음, 300자는 255자로 자름', $bad);
check(strpos($d, 'get_text(stripslashes(implode(\', \', $fail_od_id)))') !== false, '배송 엑셀: 실패 주문번호 출력 인코딩');

// ---------- 4. 배송조회 링크 ----------
if ($has_inquiry) {
    $normal = get_delivery_inquiry('로젠택배', '12345678901', 'dvr_link');
    check($normal === '<a href="https://www.ilogen.com/m/personal/trace.pop/12345678901" target="_blank" class="dvr_link">배송조회</a> (문의전화: 1588-9988)',
        '배송조회 링크: 정상 운송장은 기존과 같은 주소', $normal);
    $x = get_delivery_inquiry('로젠택배', '1" onmouseover="alert(1)', 'dvr_link');
    check(strpos($x, '"1"') === false && strpos($x, 'onmouseover="') === false && strpos($x, 'trace.pop/1%22+onmouseover%3D%22alert%281%29"') !== false,
        '배송조회 링크: 운송장 따옴표가 href 속성을 벗어나지 못함', $x);
    $x = get_delivery_inquiry('테스트택배', '9<b>', 'a" onclick="x');
    check(strpos($x, 'href="https://example.invalid/track?a=1&amp;no=9%3Cb%3E"') !== false && strpos($x, 'class="a&quot; onclick=&quot;x"') !== false,
        '배송조회 링크: 주소·class 속성 인코딩', $x);
} else {
    check(false, 'get_delivery_inquiry() 찾기');
}

// ---------- 5. 출력 인코딩: 스킨 ----------
$dirs = array('skin/shop/basic', 'mobile/skin/shop/basic', 'theme/basic/skin/shop/basic', 'theme/basic/mobile/skin/shop/basic');
$raw = array(); $encoded = 0; $double = array();
foreach ($dirs as $dir) {
    foreach ((array) glob($root.'/'.$dir.'/*.php') as $file) {
        $c = file_get_contents($file); $rel = substr($file, strlen($root) + 1);
        if (preg_match_all('/(?<!get_text\()stripslashes\(\$(row|it)\[\'it_name\'\]\)|<\?php echo \$(it|row|row2)\[\'it_name\'\]; \?>/', $c, $m)) $raw[$rel] = $m[0];
        $encoded += substr_count($c, "get_text(stripslashes(\$row['it_name']))") + substr_count($c, "get_text(\$it['it_name'])");
        if (strpos($c, 'get_text(get_text(') !== false) $double[] = $rel;
    }
}
check(!$raw, '기본·모바일·테마 스킨: 상품명 원문 출력 없음', $raw);
check($encoded >= 70 && !$double, '스킨 상품명 get_text 적용(중복 인코딩 없음)', array($encoded, $double));
// 기본설명은 목록(item_list)·상세(get_shop_item*)에서 conv_content(..., 1)로 정화된 값을 받는다.
check(strpos(src('lib/shop.lib.php'), "\$row['it_basic'] = conv_content(\$row['it_basic'], 1);") !== false
    && substr_count(src('lib/shop.data.lib.php'), "\$item['it_basic'] = conv_content(\$item['it_basic'], 1);") === 2, '기본설명은 출력 전 HTML 정화 유지');

// ---------- 6. 출력 인코딩: 주문 화면 ----------
$files = array('shop/orderinquiryview.php', 'mobile/shop/orderinquiryview.php', 'theme/basic/shop/orderinquiryview.php', 'adm/shop_admin/orderform.php');
$raw = array();
foreach ($files as $p) {
    $c = src($p);
    if ($c === '' || preg_match_all('/echo \$od\[\'od_(invoice|delivery_company)\'\];/', $c, $m)) $raw[$p] = isset($m[0]) ? $m[0] : 'missing';
    if (substr_count($c, "get_text(\$od['od_invoice'])") < 1 || substr_count($c, "get_text(\$od['od_delivery_company'])") < 1) $raw[$p.' get_text'] = 0;
}
$c = src('adm/shop_admin/orderform.php');
if (substr_count($c, "get_text(\$od['od_invoice'])") !== 2 || substr_count($c, "get_text(\$od['od_delivery_company'])") !== 2) $raw['orderform 4곳'] = 0;
$c = src('adm/shop_admin/orderlist.php');
if (strpos($c, "value=\"<?php echo get_text(\$row['od_invoice']); ?>\"") === false || strpos($c, "get_text(\$row['od_invoice']) : '-'") === false
    || strpos($c, "get_text(\$row['od_delivery_company']) : '-'") === false) $raw['orderlist'] = 0;
$c = src('shop/mail/ordermail.mail.php');
if (strpos($c, "get_text(\$delivery_list['dl_company'])") === false || strpos($c, "get_text(\$delivery_list['od_invoice'])") === false) $raw['ordermail'] = 0;
check(!$raw, '고객 주문상세·관리자 주문서·주문 목록·배송 메일: 배송회사·운송장 인코딩', $raw);

echo "TOTAL ", $passed + $failed, " PASS ", $passed, " FAIL ", $failed, "\n";
exit($failed ? 1 : 0);

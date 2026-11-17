<?php
// KG이니시스 PRO 모바일 보조 폼(sm_form)과 토스 승인 실패 복귀 주소 회귀 검사.
// 모바일 주문서는 User-Agent(is_mobile())로 고르므로, 화면 모드(G5_IS_MOBILE)가 PC여도
// 모바일 주문서의 pay_approval()이 쓰는 sm_form·good_mny가 있어야 한다(#61).
// 실행: php tests/inicis_pro_mobile_form.php
if (PHP_SAPI !== 'cli') exit(1);
error_reporting(E_ALL);

$root = dirname(dirname(__FILE__));

// 하위 프로세스: 화면 모드와 주문서 종류를 받아 이니시스 주문서 폼 1단계를 출력한다.
if (isset($argv[1]) && $argv[1] === '--render') {
    set_error_handler(function ($level, $message, $file, $line) { throw new ErrorException($message, 0, $level, $file, $line); });
    define('_GNUBOARD_', true);
    define('G5_IS_MOBILE', $argv[2] === '1');
    define('G5_SHOP_PATH', $root.'/shop');
    function add_javascript($js, $order = 0) {}
    $inicis_pro_use = true;
    $tot_price = 12345;
    include $root.($argv[3] === 'mobile' ? '/mobile/shop/inicis/orderform.1.php' : '/shop/inicis/orderform.1.php');
    exit(0);
}

$count = 0;
function ok($cond, $label) { global $count; if (!$cond) { fwrite(STDERR, "실패: $label\n"); exit(1); } $count++; }

// 1. 화면 모드 × 주문서 종류 4가지 조합
foreach (array('1', '0') as $screen_mobile) {
    foreach (array('mobile', 'pc') as $form) {
        $out = array(); $code = 0;
        exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg(__FILE__).' --render '.$screen_mobile.' '.$form.' 2>&1', $out, $code);
        $html = implode("\n", $out);
        $label = '화면 '.($screen_mobile === '1' ? '모바일' : 'PC').', '.$form.' 주문서';
        ok($code === 0 && strpos($html, 'function inicis_pro_pay') !== false, $label.' PRO 스크립트 출력');
        $has_form = strpos($html, '<form name="sm_form"') !== false;
        $has_mny = strpos($html, '<input type="hidden" name="good_mny" value="12345">') !== false;
        if ($form === 'mobile') ok($has_form && $has_mny, $label.' 보조 폼·good_mny 있음');
        else ok(!$has_form && !$has_mny, $label.' 보조 폼 없음');
    }
}

// 2. PRO 폼을 부르는 곳은 모두 주문서 종류를 지정한다.
$pro = file_get_contents($root.'/shop/inicis/pro/orderform.1.php');
ok(preg_match('/if\s*\(\s*G5_IS_MOBILE/', $pro) === 0, 'PRO 폼이 화면 모드로 보조 폼을 정하지 않음');
foreach (array('mobile/shop/inicis/orderform.1.php' => 'true', 'shop/inicis/orderform.1.php' => 'false') as $path => $value) {
    $src = file_get_contents($root.'/'.$path);
    ok(preg_match('/\$inicis_pro_mobile_form = '.$value.';\s*require\(G5_SHOP_PATH\.\'\/inicis\/pro\/orderform\.1\.php\'\);/', $src) === 1, $path.' 주문서 종류 지정');
}

// 3. 모바일 주문서·개인결제 폼은 모바일 이니시스 폼 1단계를 불러오고 sm_form을 쓴다.
foreach (array('mobile/shop/orderform.sub.php', 'mobile/shop/personalpayform.sub.php') as $path) {
    $src = file_get_contents($root.'/'.$path);
    ok(strpos($src, "require_once(G5_MSHOP_PATH.'/'.\$default['de_pg_service'].'/orderform.1.php');") !== false, $path.' 모바일 PG 폼 1단계');
    ok(strpos($src, 'var f = document.sm_form;') !== false, $path.' sm_form 사용');
}

// 4. 토스 승인 실패 복귀: 화면 모드와 무관하게 개인결제·바로구매 화면으로 돌아간다.
$toss = file_get_contents($root.'/shop/toss/toss_result.php');
$fail = substr($toss, strpos($toss, '// 화면 모드와 관계없이 결제를 시작한 화면으로 돌려보낸다'));
ok($fail !== '' && strpos($toss, 'G5_IS_MOBILE') === false, '토스 결과 처리에 화면 모드 분기 없음');
ok(strpos($fail, "if (\$is_personal) {") !== false && strpos($fail, "/personalpayform.php?pp_id='.urlencode((string)get_session('ss_personalpay_id'))") !== false, '토스 실패 개인결제 복귀');
ok(strpos($fail, "\$page_return_url .= '?sw_direct=1';") !== false, '토스 실패 바로구매 복귀');
ok(substr_count($fail, "alert(\$toss->responseData['message'].' 코드 : '.\$toss->responseData['code'], \$page_return_url);") === 1, '토스 실패 알림 1곳');

echo "inicis_pro_mobile_form: {$count}건 통과\n";

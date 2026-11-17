<?php
// PG 승인 후 주문 처리 실패 시 결제 취소 공통 처리(shop/cancel_pg.inc.php) 회귀 검사.
// 실제 PG에 요청하지 않는다. PG별 취소 파일을 임시 대역으로 바꿔, 공통 처리가 해당 PG 취소를
// 실행하고 호출부로 돌아오는지 확인한다.
// 실행: php tests/pg_cancel_dispatch.php
if (PHP_SAPI !== 'cli') exit(1);
error_reporting(E_ALL);
set_error_handler(function ($level, $message, $file, $line) { throw new ErrorException($message, 0, $level, $file, $line); });

$root = dirname(dirname(__FILE__));
$count = 0;
$done = false;
// 공통 처리가 include 중에 종료(exit)하면 검사가 끝까지 가지 못한다. 그 경우 실패로 끝낸다.
register_shutdown_function(function () {
    if (!$GLOBALS['done']) { fwrite(STDERR, "실패: 검사 도중 종료됨(취소 공통 처리가 exit했을 수 있음)\n"); exit(1); }
});
function ok($cond, $label) { global $count; if (!$cond) { $GLOBALS['done'] = true; fwrite(STDERR, "실패: $label\n"); exit(1); } $count++; }

// 1. 직접 호출하면 아무것도 실행하지 않는다.
$out = array(); $code = 0;
exec(escapeshellarg(PHP_BINARY).' '.escapeshellarg($root.'/shop/cancel_pg.inc.php').' 2>&1', $out, $code);
ok($code === 0 && implode('', $out) === '', '직접 호출 차단');

// 2. PG별 취소 파일 대역
$shop = sys_get_temp_dir().'/g5-pg-cancel-'.getmypid();
$stubs = array('lg' => 'lg/xpay_cancel.php', 'toss' => 'toss/toss_cancel.php', 'inicis' => 'inicis/inipay_cancel.php',
               'nicepay' => 'nicepay/cancel_process.php', 'kcp' => 'kcp/pp_ax_hub_cancel.php');
foreach ($stubs as $pg => $rel) {
    @mkdir(dirname($shop.'/'.$rel), 0700, true);
    file_put_contents($shop.'/'.$rel, '<?php $GLOBALS[\'cancelled\'][] = array('.var_export($pg, true).', isset($cancelAmt) ? $cancelAmt : null, $cancel_msg, $tno);');
}
define('_GNUBOARD_', true);
define('G5_SHOP_PATH', $shop);

function run_cancel($od_pg)
{
    $GLOBALS['cancelled'] = array();
    $tno = 'T-'.$od_pg; $pg_price = '1500'; $amount = 1500; $cancel_msg = '쿠폰 이중사용 감지';
    include $GLOBALS['root'].'/shop/cancel_pg.inc.php';
    // include 뒤의 호출부 코드(주문 삭제·장바구니 복구·포인트 원복)가 이어서 실행되어야 한다.
    return array($GLOBALS['cancelled'], isset($pg_res_cd) ? $pg_res_cd : null, 'after-include');
}

foreach (array('lg', 'toss', 'inicis', 'nicepay', 'kcp') as $pg) {
    list($cancelled, $res_cd, $after) = run_cancel($pg);
    ok($after === 'after-include', $pg.' 취소 후 호출부로 복귀');
    ok(count($cancelled) === 1 && $cancelled[0][0] === $pg && $cancelled[0][2] === '쿠폰 이중사용 감지' && $cancelled[0][3] === 'T-'.$pg, $pg.' 취소 파일 실행');
}
list($cancelled) = run_cancel('nicepay');
ok($cancelled[0][1] === 1500, 'nicepay 취소 금액 정수 전달');
list($cancelled) = run_cancel('');
ok(count($cancelled) === 1 && $cancelled[0][0] === 'kcp', '기본값(KCP) 취소');
list($cancelled, $res_cd) = run_cancel('KAKAOPAY');
ok($cancelled === array() && $res_cd === 'SIRK_RETIRED', '종료된 SIRK 카카오페이는 안내 코드만 설정');

foreach ($stubs as $rel) @unlink($shop.'/'.$rel);
foreach (array('lg', 'toss', 'inicis', 'nicepay', 'kcp') as $dir) @rmdir($shop.'/'.$dir);
@rmdir($shop);

// 3. 주문서 처리의 취소 호출 지점(PC 5곳, 모바일 5곳)이 공통 처리를 쓴다.
foreach (array('shop/orderformupdate.php', 'mobile/shop/orderformupdate.php') as $path) {
    $source = file_get_contents($root.'/'.$path);
    ok(substr_count($source, "include G5_SHOP_PATH.'/cancel_pg.inc.php';") === 5, $path.' 취소 호출 5곳');
}
// 4. 개인결제 처리의 PG별 취소 분기에는 모두 토스가 있다(PC·모바일 각 3곳).
foreach (array('shop/personalpayformupdate.php', 'mobile/shop/personalpayformupdate.php') as $path) {
    $source = file_get_contents($root.'/'.$path);
    preg_match_all('/switch\(\$default\[\'de_pg_service\'\]\)\s*\{(.*?)default:/s', $source, $blocks);
    $cancel_blocks = array();
    foreach ($blocks[1] as $block) {
        if (strpos($block, '_cancel.php') !== false || strpos($block, 'cancel_process.php') !== false) $cancel_blocks[] = $block;
    }
    ok(count($cancel_blocks) === 3, $path.' 취소 분기 3곳');
    foreach ($cancel_blocks as $i => $block) {
        ok(strpos($block, "case 'toss':") !== false && strpos($block, "toss/toss_cancel.php") !== false, $path.' 취소 분기 '.($i + 1).' 토스 처리');
    }
}

$done = true;
echo "통과: PG 결제 취소 공통 처리 {$count}건\n";

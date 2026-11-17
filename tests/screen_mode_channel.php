<?php
// 결제·인증 흐름이 화면 모드(G5_IS_MOBILE)에 따라 어긋나지 않는지 확인하는 정적 회귀 검사.
// 모바일 주문서는 User-Agent로 고르므로 모바일 기기 + PC 화면 모드에서도 같은 흐름이어야 한다(#61).
// 실행: php tests/screen_mode_channel.php
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(dirname(__FILE__));
$count = 0;
function ok($cond, $label) { global $count; if (!$cond) { fwrite(STDERR, "실패: $label\n"); exit(1); } $count++; }
function src($path) { global $root; return file_get_contents($root.'/'.$path); }
// 주석을 뺀 코드만 돌려준다(설명 주석의 상수 이름은 검사 대상이 아니다).
function code($s) { $out = ''; foreach (token_get_all($s) as $t) { if (is_array($t) && ($t[0] === T_COMMENT || $t[0] === T_DOC_COMMENT)) continue; $out .= is_array($t) ? $t[1] : $t; } return $out; }

// 1. 임시주문 저장: 장바구니 주문은 항상 서버 세션의 장바구니 ID를 저장하고, 개인결제는 저장하지 않는다.
$save = src('shop/ajax.orderdatasave.php');
ok(strpos(code($save), 'G5_IS_MOBILE') === false, '임시주문 저장이 화면 모드를 쓰지 않음');
$personal = substr($save, strpos($save, "if(isset(\$_POST['pp_id']) && \$_POST['pp_id']) {"), strpos($save, '} else {', strpos($save, "die('해당 개인결제는 사용이 금지되어 있습니다.');")) - strpos($save, "if(isset(\$_POST['pp_id']) && \$_POST['pp_id']) {"));
ok(strpos($personal, "unset(\$_POST['post_cart_id']);") !== false, '개인결제는 요청의 post_cart_id 제거');
$cart_start = strpos($save, "\$od_id   = get_session('ss_order_id');");
$cart = substr($save, $cart_start, strpos($save, 'shop_order_access_id((string)$od_id) ||', $cart_start) - $cart_start);
ok(preg_match('/^\s*\$_POST\[\'post_cart_id\'\] = \$cart_id;\s*$/m', $cart) === 1, '장바구니 주문은 post_cart_id를 조건 없이 서버 값으로 저장');
ok(substr_count($cart, "\$_POST['post_cart_id']") === 1, 'post_cart_id 저장 1곳');
// 복원하는 쪽(모바일 이니시스 결과 통보)은 요청값이 섞일 수 있는 post_cart_id 대신 서버가 기록한 cart_id 컬럼을 쓴다.
$notify = code(src('mobile/shop/inicis/settle_common.php'));
ok(strpos($notify, "\$restore_cart_id = isset(\$od['cart_id']) ? preg_replace('/[^0-9]/', '', (string)\$od['cart_id']) : '';") !== false, '이니시스 결과 통보: 서버 cart_id 컬럼으로 복원값 결정');
ok(strpos($notify, "set_session('ss_cart_id', \$restore_cart_id);") !== false && strpos($notify, "set_session('ss_cart_direct', \$restore_cart_id);") !== false, '이니시스 결과 통보의 장바구니 복원');
ok(preg_match('/set_session\(\'ss_cart_(id|direct)\', \$params/', $notify) === 0, '이니시스 결과 통보가 post_cart_id로 복원하지 않음');
// 임시주문 저장은 서버 cart_id를 컬럼에도 기록한다.
ok(strpos(src('shop/ajax.orderdatasave.php'), "cart_id = '\$cart_id',") !== false, '임시주문 cart_id 컬럼 기록');
// 공통 필드 목록에 있어 PG와 관계없이 저장된다.
ok(preg_match("/\\\$common = explode\(' ', '[^']*\\bpost_cart_id\\b/", src('lib/shop_order_fields.lib.php')) === 1, 'post_cart_id가 공통 저장 필드');

// 2. LG U+ 승인 실패: 화면 모드와 관계없이 결제를 시작한 화면 주소로 돌려보낸다.
$lg = src('shop/lg/xpay_result.php');
ok(strpos(code($lg), 'G5_IS_MOBILE') === false, 'LG 결과 처리에 화면 모드 분기 없음');
ok(strpos($lg, "/personalpayform.php?pp_id='.urlencode((string)get_session('ss_personalpay_id'))") !== false, 'LG 실패 개인결제 복귀');
ok(strpos($lg, "\$page_return_url .= '?sw_direct=1';") !== false, 'LG 실패 바로구매 복귀');
ok(strpos($lg, "alert(\$xpay->Response_Msg().' 코드 : '.\$xpay->Response_Code(), \$page_return_url);") !== false, 'LG 실패 알림에 복귀 주소');
ok(preg_match('/alert\(\$xpay->Response_Msg\(\)\.\' 코드 : \'\.\$xpay->Response_Code\(\)\);/', $lg) === 0, 'LG 실패 알림에 뒤로 가기 없음');

// 3. KCP 본인확인: 인증창은 항상 팝업이고, 결과 페이지의 취소 처리도 항상 창을 닫는다.
ok(preg_match('/var use_iframe = false;/', src('js/certify.js')) === 1, 'KCP 인증창 팝업 고정');
foreach (array('plugin/kcpcert/kcpcert_result.php', 'plugin/kcpcert/find_kcpcert_result.php') as $path) {
    $s = src($path);
    ok(strpos(code($s), 'G5_IS_MOBILE') === false, $path.' 화면 모드 분기 없음');
    ok(strpos($s, "window.parent.\$(\"#kcp_cert\" )") === false, $path.' iframe 전제 코드 없음');
    ok(strpos($s, 'alert_close("휴대폰 본인확인을 취소 하셨습니다.");') !== false, $path.' 취소 안내 후 창 닫기');
}

echo "screen_mode_channel: {$count}건 통과\n";

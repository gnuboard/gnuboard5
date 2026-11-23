<?php
// 실행: php tests/kve_3247.php
// DB 없이 KVE-2026-3247(비KCP 현금영수증 요청 위조·중복 발급)의 공통 라이브러리와 처리기 적용을 검사한다.
if (PHP_SAPI !== 'cli') exit(1);
$root = dirname(__DIR__);
define('_GNUBOARD_', true);
define('G5_SHOP_URL', 'http://127.0.0.1/shop');
define('G5_TOKEN_ENCRYPTION_KEY', 'kve-3247-unit-test');
$checks = 0; $fails = 0;
function check($ok, $name) {
    if (!$ok) { echo 'FAIL '.$name."\n"; $GLOBALS['fails']++; return; }
    echo 'PASS '.$name."\n";
    $GLOBALS['checks']++;
}
function src($path) { return str_replace("\r\n", "\n", file_get_contents($GLOBALS['root'].'/'.$path)); }
function func_src($path, $name, $indent) {
    if (!preg_match('/^'.$indent.'function '.$name.'\(.*?^'.$indent.'\}/ms', src($path), $m)) return '';
    return $m[0];
}

// ---------- 처리기·발급 화면 적용(정적) ----------
$pgs = array('lg', 'toss', 'inicis', 'nicepay');
foreach ($pgs as $pg) {
    $h = src('shop/'.$pg.'/taxsave_result.php');
    check(strpos($h, "shop_cash_handle('".$pg."', 'shop_cash_gateway_".$pg."')") !== false, '3247 '.$pg.' 처리기가 공통 처리(POST·출처·토큰·형식·발급 조건·선점) 사용');
    check(!preg_match('/\$_REQUEST|\$_POST\[|\$id_info|\$tr_code|\$buyeremail|sql_query\(|curl_init|->TX\(\)|issueCashReceipt|nicepay_reqPost|g5_tls_http_post/', $h),
        '3247 '.$pg.' 처리기에 요청 전역·직접 갱신·직접 PG 호출이 남지 않음');
    $f = src('shop/'.$pg.'/taxsave_form.php');
    check(strpos($f, "shop_cash_form_token('".$pg."', \$cash_type, (string)\$od_id)") !== false && strpos($f, 'name="cash_token" value="<?php echo $cash_token; ?>"') !== false
        && strpos($f, 'value="<?php echo $tx; ?>"') === false, '3247 '.$pg.' 발급 화면이 원장 검사 후 주문별 일회용 토큰 출력');
}
check(strpos(src('shop/kcp/pp_cli_hub.php'), "kcp_cash_process(\$request, 'kcp_cash_gateway')") !== false, '3247 KCP 처리기는 기존 경로 유지');

// ---------- 공통 라이브러리 동작(DB 대역) ----------
$GLOBALS['__sess'] = array();
function set_session($k, $v) { $GLOBALS['__sess'][$k] = $v; }
function get_session($k) { return isset($GLOBALS['__sess'][$k]) ? $GLOBALS['__sess'][$k] : ''; }
function sql_escape_string($v) { return addslashes($v); }
function get_goods($id) { return array('full_name' => '상품 '.$id); }
function alert($m) { throw new Exception('ALERT '.$m); }
// 원장 대역: 행 하나를 두고 UPDATE의 상태 조건만 해석한다.
$GLOBALS['__row'] = null; $GLOBALS['__sql'] = array(); $GLOBALS['__affected'] = 0; $GLOBALS['__fail_save'] = false;
function fake_where_ok($where) {
    $r = $GLOBALS['__row'];
    foreach (array('cash', 'cash_no', 'cash_info', 'misu', 'receipt_price') as $f) {
        if (preg_match_all("/od_".$f."='?((?:[^'\\\\]|\\\\.)*)'?(?= AND|\)|$)/", $where, $m)) {
            foreach ($m[1] as $v) if ((string)$r['od_'.$f] !== stripslashes($v)) return false;
        }
    }
    return true;
}
function sql_query($sql, $e = true) {
    $GLOBALS['__sql'][] = $sql;
    $GLOBALS['__affected'] = 0;
    if (!preg_match('/\AUPDATE \S+ SET (.*?) WHERE (.*)\z/s', $sql, $m)) return true;
    if ($GLOBALS['__fail_save'] && strpos($m[1], 'od_cash=1') === 0) return false;
    // OR로 묶인 조건은 하나라도 맞으면 갱신
    $ok = false;
    foreach (preg_split('/\) OR \(/', trim($m[2], '()')) as $part) if (fake_where_ok($part)) $ok = true;
    if (!$ok) return true;
    preg_match_all("/(od_[a-z_]+)=(?:'((?:[^'\\\\]|\\\\.)*)'|([0-9]+))/", $m[1], $sets, PREG_SET_ORDER);
    foreach ($sets as $s) $GLOBALS['__row'][$s[1]] = isset($s[3]) && $s[3] !== '' ? $s[3] : stripslashes($s[2]);
    $GLOBALS['__affected'] = 1;
    return true;
}
function sql_fetch($sql, $e = true) {
    if (strpos($sql, 'SELECT 1 AS matched') === 0) { preg_match('/WHERE (.*)\z/s', $sql, $m); return fake_where_ok($m[1]) ? array('matched' => 1) : false; }
    return $GLOBALS['__row'];
}
function get_sql_affected_rows() { return $GLOBALS['__affected']; }
function is_shop_order_owner($row, $type) { return $GLOBALS['__owner']; }
$GLOBALS['__owner'] = true;
$g5 = array('g5_shop_order_table' => 'g5_shop_order', 'g5_shop_personalpay_table' => 'g5_shop_personalpay');
$default = array('de_pg_service' => 'inicis', 'de_taxsave_use' => 1, 'de_taxsave_types' => 'account,transfer,vbank');
require $root.'/lib/shop_cash.lib.php';
require $root.'/lib/kcp_cash.lib.php';
function base_row($pg) {
    return array('od_id' => '2026112012345', 'mb_id' => 'owner', 'od_pg' => $pg, 'od_settle_case' => '무통장', 'od_receipt_price' => '11000', 'od_refund_price' => '0',
        'od_misu' => '0', 'od_status' => '입금', 'od_tax_mny' => '10000', 'od_vat_mny' => '1000', 'od_free_mny' => '0', 'od_cash' => '0', 'od_cash_no' => '', 'od_cash_info' => '',
        'od_name' => 'BUYER', 'od_email' => 'b@example.com', 'od_tel' => '010');
}
function fails($fn) { try { call_user_func($fn); return false; } catch (ShopCashRequestException $e) { return $e->getMessage(); } }

// 요청 형식
$ok = array('tx' => '', 'od_id' => '2026112012345', 'cash_token' => str_repeat('a', 64), 'tr_code' => '0', 'id_info' => '01012345678', 'buyeremail' => 'a@b.c', 'buyertel' => '010-1-2');
$r = shop_cash_request($ok, 'POST', 'inicis');
check($r['tx'] === 'order' && $r['id_info'] === '01012345678', '3247 공통 요청 형식 통과');
$bad = array('GET' => array(array(), 'GET', 'inicis'), '토큰 없음' => array(array('cash_token' => null), 'POST', 'inicis'), '토큰 형식' => array(array('cash_token' => 'x'), 'POST', 'inicis'),
    'tx' => array(array('tx' => 'other'), 'POST', 'inicis'), '주문번호' => array(array('od_id' => "1' or 1"), 'POST', 'inicis'),
    '용도(토스 2)' => array(array('tr_code' => '2'), 'POST', 'toss'), '용도(LG 0)' => array(array('tr_code' => '0'), 'POST', 'lg'),
    '소득공제 12자리' => array(array('id_info' => '010123456789'), 'POST', 'inicis'), '지출증빙 11자리' => array(array('tr_code' => '1'), 'POST', 'inicis'),
    '배열' => array(array('id_info' => array('1')), 'POST', 'inicis'), '이메일' => array(array('buyeremail' => '<x>@y'), 'POST', 'inicis'), '전화' => array(array('buyertel' => '010;x'), 'POST', 'inicis'));
$r = array();
foreach ($bad as $label => $c) {
    $post = $ok;
    foreach ($c[0] as $k => $v) { if ($v === null) unset($post[$k]); else $post[$k] = $v; }
    if (!fails(create_function_compat($post, $c[1], $c[2]))) $r[] = $label;
}
function create_function_compat($post, $method, $pg) { return function () use ($post, $method, $pg) { shop_cash_request($post, $method, $pg); }; }
check(!$r, '3247 GET·토큰·주문번호·PG별 용도·식별번호·이메일·전화 형식 오류 거부 '.implode(',', $r));
check(shop_cash_tr_codes('lg') === array('1', '2') && shop_cash_tr_codes('toss') === array('0', '1') && shop_cash_tr_codes('nicepay') === array('1', '2'), '3247 PG별 발행용도 값은 기존 화면 값 유지');

// 토큰: 주문 유형·번호에 묶임, 1회용, 10분
$t1 = shop_cash_token('inicis', 'order', '1');
check(!shop_cash_consume_token('inicis', 'order', '2', $t1) && shop_cash_consume_token('inicis', 'order', '1', $t1) === true && !shop_cash_consume_token('inicis', 'order', '1', $t1), '3247 토큰은 주문에 묶이고 한 번만 쓸 수 있음');
$t2b = shop_cash_token('inicis', 'order', '1');
check(!shop_cash_consume_token('toss', 'order', '1', $t2b) && !shop_cash_consume_token('inicis', 'order', '1', str_repeat('0', 64)) && !shop_cash_consume_token('inicis', 'order', '1', $t2b),
    '3247 다른 PG 처리기에는 쓸 수 없고, 틀린 값으로 한 번 시도하면 폐기');
$t3 = shop_cash_token('inicis', 'personalpay', '1'); $k = shop_cash_key('inicis', 'personalpay', '1'); $GLOBALS['__sess'][$k]['expires'] = time() - 1;
check(!shop_cash_consume_token('inicis', 'personalpay', '1', $t3), '3247 만료 토큰 거부');

// 발급 조건 재검사
$cases = array('다른 PG' => array('od_pg' => 'toss'), '발급됨' => array('od_cash' => '1'), '처리 중' => array('od_cash_info' => 'x'), '미수' => array('od_misu' => '1'),
    '환불' => array('od_refund_price' => '11000'), '주문 상태' => array('od_status' => '주문'), '결제수단' => array('od_settle_case' => '신용카드'), '금액 불일치' => array('od_vat_mny' => '999'));
$r = array();
foreach ($cases as $label => $c) { $row = array_merge(base_row('inicis'), $c); if (!fails(create_data_compat($row))) $r[] = $label; }
function create_data_compat($row) { return function () use ($row) { shop_cash_data($row, 'order', 'inicis'); }; }
$default['de_taxsave_use'] = 0; if (!fails(create_data_compat(base_row('inicis')))) $r[] = '설정'; $default['de_taxsave_use'] = 1;
$default['de_taxsave_types'] = 'transfer'; if (!fails(create_data_compat(base_row('inicis')))) $r[] = '허용 결제수단'; $default['de_taxsave_types'] = 'account,transfer,vbank';
check(!$r, '3247 발급 조건(PG·설정·결제수단·발급 여부·처리 중·미수·환불·주문 상태·금액) 재검사 '.implode(',', $r));
$d = shop_cash_data(base_row('inicis'), 'order', 'inicis');
check($d['amt_tot'] === 11000 && $d['amt_sup'] === 10000 && $d['amt_tax'] === 1000 && $d['amt_free'] === 0, '3247 정상 주문 금액 계산');

// 처리: 선점(1행) 전에는 PG를 호출하지 않고, 저장 실패는 취소 또는 확인 대기
$GLOBALS['__calls'] = array();
function gw_ok($op, $d) { $GLOBALS['__calls'][] = $op; if ($op === 'cancel') return array('ok' => $GLOBALS['__cancel_ok']); return array('ok' => true, 'cash_no' => '12345', 'info' => array('TID' => 'T1'), 'display' => array('x' => 1)); }
function gw_decline($op, $d) { $GLOBALS['__calls'][] = $op; return array('ok' => false, 'definite' => true, 'message' => '거절'); }
function gw_unknown($op, $d) { $GLOBALS['__calls'][] = $op; return array('ok' => false); }
function gw_throw($op, $d) { $GLOBALS['__calls'][] = $op; throw new RuntimeException('x'); }
function run_process($gw, $row_over = array()) {
    $GLOBALS['__row'] = array_merge(base_row('inicis'), $row_over); $GLOBALS['__calls'] = array();
    $token = shop_cash_token('inicis', 'order', '2026112012345');
    $req = array('tx' => 'order', 'od_id' => '2026112012345', 'cash_token' => $token, 'tr_code' => '0', 'id_info' => '01012345678', 'buyeremail' => '', 'buyertel' => '');
    try { return shop_cash_process($req, 'inicis', $gw); } catch (ShopCashRequestException $e) { return 'FAIL:'.$e->getMessage(); }
}
$GLOBALS['__cancel_ok'] = true;
$res = run_process('gw_ok');
check(is_array($res) && $res['state'] === 'issued' && $GLOBALS['__row']['od_cash'] === '1' && $GLOBALS['__row']['od_cash_no'] === '12345' && strpos($GLOBALS['__row']['od_cash_info'], 'attempt') !== false
    && $GLOBALS['__calls'] === array('pay'), '3247 정상 처리: 선점 후 PG 1회, 조건부 저장');
$claim = array_values(array_filter($GLOBALS['__sql'], 'is_claim_sql'));
function is_claim_sql($s) { return strpos($s, 'shop_cash_state') !== false && strpos($s, "AND od_cash=0 AND od_cash_no='' AND od_cash_info=''") !== false; }
check(count($claim) >= 1, '3247 선점 UPDATE는 미발급·미처리 조건과 원장 스냅샷 조건을 건다');
$res = run_process('gw_ok', array('od_cash_info' => 'a:1:{s:15:"shop_cash_state";s:7:"pending";}'));
check(is_string($res) && strpos($res, 'FAIL:') === 0 && !$GLOBALS['__calls'], '3247 이미 처리 중이면 PG 미호출');
// 선점 경합: 화면 검사 뒤 다른 요청이 먼저 선점한 경우(UPDATE 0행)
function gw_never($op, $d) { $GLOBALS['__calls'][] = $op; return array('ok' => true, 'cash_no' => '1', 'info' => array()); }
$GLOBALS['__row'] = base_row('inicis'); $GLOBALS['__calls'] = array();
$token = shop_cash_token('inicis', 'order', '2026112012345');
$req = array('tx' => 'order', 'od_id' => '2026112012345', 'cash_token' => $token, 'tr_code' => '0', 'id_info' => '01012345678', 'buyeremail' => '', 'buyertel' => '');
try {
    // shop_cash_order는 깨끗한 행을 읽고, 선점 UPDATE 시점에는 다른 요청의 표식이 있는 상태
    $row = shop_cash_order('order', '2026112012345');
    $GLOBALS['__row']['od_cash_info'] = 'other-pending';
    $ok_claim = shop_cash_update('g5_shop_order', "od_cash_info='x'", shop_cash_snapshot_where($row, 'od')." AND od_cash=0 AND od_cash_no='' AND od_cash_info=''");
} catch (Exception $e) { $ok_claim = true; }
check($ok_claim === false, '3247 다른 요청이 먼저 선점하면 조건부 UPDATE가 0행');
$res = run_process('gw_decline');
check(is_array($res) && $res['state'] === 'declined' && $GLOBALS['__row']['od_cash_info'] === '' && $GLOBALS['__row']['od_cash'] === '0', '3247 PG 명시 거절이면 선점 해제');
$res = run_process('gw_unknown');
check(is_array($res) && $res['state'] === 'pending' && strpos($GLOBALS['__row']['od_cash_info'], 'pending') !== false, '3247 결과 불명이면 확인 대기 유지');
$res = run_process('gw_throw');
check(is_array($res) && $res['state'] === 'pending' && strpos($GLOBALS['__row']['od_cash_info'], 'pending') !== false, '3247 PG 예외도 확인 대기 유지');
$GLOBALS['__fail_save'] = true;
$res = run_process('gw_ok');
check(is_array($res) && $res['state'] === 'compensated' && $GLOBALS['__calls'] === array('pay', 'cancel') && $GLOBALS['__row']['od_cash_info'] === '' && $GLOBALS['__row']['od_cash'] === '0',
    '3247 저장 실패 시 이번 승인만 취소하고 선점 해제');
$GLOBALS['__cancel_ok'] = false;
$res = run_process('gw_ok');
check(is_array($res) && $res['state'] === 'pending' && strpos($GLOBALS['__row']['od_cash_info'], 'reconcile') !== false, '3247 취소 실패·취소 미지원 PG는 확인 대기로 남김');
$GLOBALS['__fail_save'] = false;
$GLOBALS['__owner'] = false;
$res = run_process('gw_ok');
check(is_string($res) && strpos($res, '권한') !== false && !$GLOBALS['__calls'], '3247 주문 소유자가 아니면 PG 미호출');
$GLOBALS['__owner'] = true;
$GLOBALS['__row'] = base_row('inicis'); $GLOBALS['__calls'] = array();
$req['cash_token'] = str_repeat('b', 64);
try { shop_cash_process($req, 'inicis', 'gw_ok'); $r = false; } catch (ShopCashRequestException $e) { $r = strpos($e->getMessage(), '만료') !== false; }
check($r && !$GLOBALS['__calls'] && $GLOBALS['__row']['od_cash_info'] === '', '3247 토큰 불일치면 선점·PG 호출 없음');

// 관리자 안내, KCP 호환
$n = shop_cash_pending_notice(serialize(array('shop_cash_state' => 'pending', 'pg' => 'toss', 'attempt' => '<x>')));
check(strpos($n, '토스페이먼츠 현금영수증 처리 확인 필요') === 0 && strpos($n, '&lt;x&gt;') !== false && shop_cash_pending_notice(serialize(array('shop_cash_state' => 'pending', 'pg' => 'evil', 'attempt' => '1'))) === '',
    '3247 관리자 화면에 비KCP 확인 대기 안내(이스케이프)');
check(kcp_cash_pending_notice(serialize(array('kcp_cash_state' => 'reconcile', 'attempt' => 'a1'))) === 'KCP 현금영수증 처리 확인 필요 (확인번호: a1). KCP 거래 내역과 주문을 대조하기 전에는 재발급하지 마십시오.',
    '3247 KCP 안내 문구 불변');
check(kcp_cash_key('order', '1') === 'ss_kcp_cash_'.hash('sha256', 'order:1:pay'), '3247 KCP 세션 키 불변');
$e = null; try { kcp_cash_order('order', 'x'); } catch (Exception $e) {}
$GLOBALS['__owner'] = false; $e = null; try { kcp_cash_order('order', 'x'); } catch (Exception $e) {}
check($e instanceof KcpCashRequestException && $e->getMessage() === '해당 주문 정보에 접근 권한이 없습니다.', '3247 KCP 공통 함수 실패는 기존 KCP 예외로 전달');
$GLOBALS['__owner'] = true;
$kd = kcp_cash_data(base_row('kcp'), 'order');
check(!isset($kd['amt_free']) && array_keys($kd) === array('ordr_idxx','good_name','buyr_name','buyr_mail','buyr_tel1','amt_tot','amt_sup','amt_tax','amt_svc','trad_time'), '3247 KCP 게이트웨이 입력 필드 불변');
$lg = func_src('lib/shop_cash.lib.php', 'shop_cash_gateway_lg', '');
check(strpos($lg, "if ((string)\$xpay->Response(\"LGD_OID\",0) !== \$data['ordr_idxx']) return array('ok'=>false, 'definite'=>false") !== false, '3247 LG 응답 LGD_OID가 요청 주문과 다르면 저장하지 않음');

echo 'TOTAL '.$checks.($fails ? ' FAIL '.$fails : '')."\n";
exit($fails ? 1 : 0);

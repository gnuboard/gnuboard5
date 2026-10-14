<?php
// PHP 5.2 이상에서 실행하는 현금영수증 보안 보조 함수 호환 검사.
define('_GNUBOARD_', true);
require dirname(__FILE__).'/../lib/kcp_cash.lib.php';
$passed = 0;
function cash_compat_expect($ok) { global $passed; if (!$ok) { fwrite(STDERR, "FAIL\n"); exit(1); } $passed++; }
function set_session($key, $value) { $GLOBALS['cash_session'][$key] = $value; }
function sql_query($sql, $error) { throw new Exception('injected DB failure'); }
function sql_fetch($sql, $error) { throw new Exception('injected read failure'); }
function cash_compat_error($severity, $message) { if (error_reporting() & $severity) { fwrite(STDERR, $message."\n"); exit(1); } }
error_reporting(E_ALL);
set_error_handler('cash_compat_error');
$token = kcp_cash_token('order', '100');
cash_compat_expect(preg_match('/\A[0-9a-f]{64}\z/D', $token) === 1);
cash_compat_expect(kcp_cash_token('order', '100') !== $token);
cash_compat_expect(shop_order_equals($token, $token));
cash_compat_expect(!shop_order_equals($token, str_repeat('0', 64)));
cash_compat_expect(kcp_cash_html('<script>"') === '&lt;script&gt;&quot;');
cash_compat_expect(kcp_cash_pending_notice(serialize(array('kcp_cash_state'=>'pending','attempt'=>'<test>'))) !== '');
cash_compat_expect(strpos(kcp_cash_pending_notice(serialize(array('kcp_cash_state'=>'reconcile','attempt'=>'<test>'))), '&lt;test&gt;') !== false);
class CashCompatObject { function __wakeup() { exit(2); } }
cash_compat_expect(kcp_cash_pending_notice(serialize(new CashCompatObject())) === '');
cash_compat_expect(kcp_cash_pending_notice('invalid') === '');
cash_compat_expect(kcp_cash_update('fixture', 'x=1', 'id=1') === false);
cash_compat_expect(kcp_cash_matches('fixture', 'id=1') === false);
try { kcp_cash_request(array(), 'GET'); cash_compat_expect(false); } catch (KcpCashRequestException $e) { cash_compat_expect(true); }
echo 'PASS '.PHP_VERSION.' KCP compatibility '.$passed."\n";

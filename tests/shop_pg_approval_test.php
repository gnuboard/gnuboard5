<?php
if (PHP_SAPI !== 'cli') exit;
define('_GNUBOARD_', true);
define('G5_SHOP_TABLE_PREFIX', 'g5_shop_');
define('G5_TIME_YMDHIS', '2026-10-29 16:00:00');
require dirname(__FILE__).'/../lib/shop_pg_approval.lib.php';
require dirname(__FILE__).'/../shop/inicis/pro/inicis_pro.lib.php';
error_reporting(E_ALL);
set_error_handler(function ($n,$s) { throw new Exception($s); });
$checks=0;
function expect_pg($actual,$expected) { global $checks; if ($actual!==$expected) throw new Exception(var_export($actual,true)); $checks++; }
function sql_escape_string($s) { return addslashes($s); }
function sql_query($s,$error=false) { global $written; $written=$s; return true; }
foreach (array('kcp','kcp_rest','inicis_pc','inicis_mobile','inicis_pro','lg','nicepay','toss') as $pg) {
    $raw=array('AuthToken'=>'secret','cardNumber'=>'1234','P_CHKFAKE'=>'secret');
    foreach(shop_pg_approval_card_fields($pg) as $key) $raw[$key]='0';
    $input=$pg==='toss' ? array('card'=>$raw) : $raw;
    $card=shop_pg_approval_card_data($pg,$input);
    expect_pg(count($card),count(shop_pg_approval_card_fields($pg)));
    expect_pg(isset($card['AuthToken']),false);
    expect_pg(shop_pg_approval_log($pg,'202610290001','fixture-tid',60000,'00',$input),true);
    expect_pg(strpos($written,'secret'),false);
    expect_pg(strpos($written,'fixture-tid')!==false,true);
}
expect_pg(shop_pg_approval_card_data('toss',array('card'=>array('installmentPlanMonths'=>0,'isInterestFree'=>false))),array('installmentPlanMonths'=>'0','isInterestFree'=>false));
expect_pg(shop_pg_approval_card_data('inicis_pro',array('P_CARD_QUOTA'=>array('bad'),'P_CARD_INTEREST'=>null)),array());
expect_pg(shop_pg_approval_card_data('nicepay',array()),array());
expect_pg(shop_pg_approval_log('unknown','1','tid',1,'00',array()),false);
$saved=inicis_pro_log_payload(array('P_CARD_QUOTA'=>'03','P_CARD_INTEREST'=>'0','P_CARD_NUM'=>'1234','P_AUTH_TOKEN'=>'secret'));
expect_pg($saved,array('P_CARD_QUOTA'=>'03','P_CARD_INTEREST'=>'0'));
expect_pg(unserialize(base64_decode(base64_encode(serialize($saved)))),$saved);
echo 'OK '.$checks." approval log checks\n";

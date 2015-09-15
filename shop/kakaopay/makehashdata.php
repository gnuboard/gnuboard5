<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/json.lib.php');

include(G5_SHOP_PATH.'/kakaopay/incKakaopayCommon.php');
include(G5_SHOP_PATH.'/kakaopay/lgcns_CNSpay.php');

$Amt = (int)preg_replace('#[^0-9]#', '', $_POST['Amt']);
$ediDate = trim($_POST['ediDate']);

////////위변조 처리/////////
//결제요청용 키값
$cnspay_lib = new CnsPayWebConnector($LogDir);
$md_src = $ediDate.$MID.$Amt;
$salt = hash("sha256",$merchantKey.$md_src,false);
$hash_input = $cnspay_lib->makeHashInputString($salt);
$hash_calc = hash("sha256", $hash_input, false);
$hash_String = base64_encode($hash_calc);

die(json_encode(array('hash_String' => $hash_String, 'error' => '')));
?>
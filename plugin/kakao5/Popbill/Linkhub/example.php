<?php

require_once 'linkhub.auth.php';

$ServiceID = 'POPBILL_TEST';
$LinkID = 'TESTER';
$SecretKey = 'SwWxqU+0TErBXy/9TVjIPEnI0VTUMMSQZtJf3Ed8q3I=';

//통신방식 기본은 CURL , curl 사용에 문제가 있을경우 STREAM 사용가능.
//STREAM 사용시에는 allow_fopen_url = on 으로 설정해야함.
define('LINKHUB_COMM_MODE','STREAM');

$AccessID = '1231212312';
$Linkhub = Linkhub::getInstance($LinkID,$SecretKey);

try
{
	$Token = $Linkhub->getToken($ServiceID,$AccessID, array('member','110'));
}catch(LinkhubException $le) {
	echo $le;
	
	exit();
}
echo 'Token is issued : '.substr($Token->session_token,0,20).' ...';
echo chr(10);

try
{
	$balance = $Linkhub->getBalance($Token->session_token,$ServiceID);
}catch(LinkhubException $le) {
	echo $le;
	
	exit();
}
echo 'remainPoint is '. $balance;
echo chr(10);

try
{
	$balance = $Linkhub->getPartnerBalance($Token->session_token,$ServiceID);
}catch(LinkhubException $le) {
	echo $le;
	
	exit();
}
echo 'remainPartnerPoint is '. $balance;
echo chr(10);

?>

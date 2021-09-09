<?php
include_once('./_common.php');
extract($_POST);

echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/> ';

print_r2($_COOKIE);
// STEP2 에 이어 인증결과가 성공일(resultCode=0000) 경우 STEP2 에서 받은 인증결과로 아래 승인요청 진행
$txId = $_POST['txId'];
$mid  = substr($txId, 6, 10); 
print_r2($_SERVER);
print_r2($_SESSION);
print_r2($_POST);
die;
// echo '<인증결과내역>'."<br/><br/>";
// echo 'resultCode : '.$_REQUEST["resultCode"]."<br/>";
// echo 'resultMsg : '.$_REQUEST["resultMsg"]."<br/>";
// echo 'authRequestUrl : '.$_REQUEST["authRequestUrl"]."<br/>";
// echo 'txId : '.$_REQUEST["txId"]."<br/>";

// 인증실패
alert_close('코드 : '.$_POST['res_cd'].'  '.urldecode($_POST['res_msg']));
exit;

include_once(G5_PATH.'/tail.sub.php');
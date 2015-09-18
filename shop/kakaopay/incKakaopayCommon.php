<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//인증,결제 및 웹 경로
$CNSPAY_WEB_SERVER_URL = 'https://kmpay.lgcns.com:8443';
$targetUrl = 'https://kmpay.lgcns.com:8443';
$msgName = '/merchant/requestDealApprove.dev';
$CnsPayDealRequestUrl = 'https://pg.cnspay.co.kr:443';

if ($default['de_card_test']) {
    $MID = 'cnstest25m';
    $merchantEncKey = '10a3189211e1dfc6';
    $merchantHashKey = '10a3189211e1dfc6';
    $cancelPwd = '123456';
    //가맹점서명키
    $merchantKey = '33F49GnCMS1mFYlGXisbUDzVf2ATWCl9k3R++d5hDd3Frmuos/XLx8XhXpe+LDYAbpGKZYSwtlyyLOtS/8aD7A==';
} else {
    $MID = 'KHSIR'.$default['de_kakaopay_mid'].'m';
    $merchantEncKey = trim($default['de_kakaopay_enckey']);
    $merchantHashKey = trim($default['de_kakaopay_hashkey']);
    $cancelPwd = trim($default['de_kakaopay_cancelpwd']);
    //가맹점서명키
    $merchantKey = trim($default['de_kakaopay_key']);
}

//버전
$phpVersion = 'PLP-0.1.1.3';

//로그 경로
$LogDir = G5_SHOP_PATH.'/kakaopay/log';

// TXN_ID를 가져오기 위해 세팅
$ediDate = date("YmdHis");  // 전문생성일시

$_REQUEST['PayMethod'] = 'KAKAOPAY';
$_REQUEST['CERTIFIED_FLAG'] = 'CN';
$_REQUEST['AuthFlg'] = '10';
$_REQUEST['currency'] = 'KRW';
$_REQUEST['MID'] = $MID;
$_REQUEST['merchantEncKey'] = $merchantEncKey;
$_REQUEST['merchantHashKey'] = $merchantHashKey;
$_REQUEST['requestDealApproveUrl'] = $targetUrl.$msgName;
?>
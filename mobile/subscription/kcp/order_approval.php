<?php
include_once('./_common.php');

@header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
@header("Cache-Control: no-store");
@header("Pragma: no-cache");

// library [수정불가]
include_once(G5_MSUBSCRIPTION_PATH.'/settle_kcp.inc.php');
require_once(G5_MSUBSCRIPTION_PATH.'/kcp/KCPComLibrary.php');

    // 쇼핑몰 페이지에 맞는 문자셋을 지정해 주세요.
    $charSetType      = 'utf-8';             // UTF-8인 경우 "utf-8"로 설정

    $siteCode         = isset($_GET['site_cd']) ? $_GET['site_cd'] : '';
    $orderID          = isset($_GET['ordr_idxx']) ? $_GET['ordr_idxx'] : '';
    $paymentMethod    = isset($_GET['pay_method']) ? $_GET[ 'pay_method'  ] : '';
    $escrow           = (isset($_GET['escw_used']) && $_GET[ 'escw_used'   ] == 'Y' ) ? true : false;
    $productName      = isset($_GET['good_name']) ? $_GET[ 'good_name'   ] : '';

    // 아래 두값은 POST된 값을 사용하지 않고 서버에 SESSION에 저장된 값을 사용하여야 함.
    $paymentAmount    = isset($_GET['good_name']) ? $_GET[ 'good_mny'    ] : 0; // 결제 금액
    $returnUrl        = isset($_GET['Ret_URL']) ? $_GET[ 'Ret_URL'     ] : '';

    // Access Credential 설정
    $accessLicense    = '';
    $signature        = '';
    $timestamp        = '';

    // Base Request Type 설정
    $detailLevel      = '0';
    $requestApp       = 'WEB';
    $requestID        = $orderID;
    $userAgent        = $_SERVER['HTTP_USER_AGENT'];
    $version          = '0.1';

    try
    {
        $payService = new PayService( $g_wsdl );

        $payService->setCharSet( $charSetType );

        $payService->setAccessCredentialType( $accessLicense, $signature, $timestamp );
        $payService->setBaseRequestType( $detailLevel, $requestApp, $requestID, $userAgent, $version );
        $payService->setApproveReq( $escrow, $orderID, $paymentAmount, $paymentMethod, $productName, $returnUrl, $siteCode );

        $approveRes = $payService->approve();

        printf( "%s,%s,%s,%s", $payService->resCD,  $approveRes->approvalKey,
                               $approveRes->payUrl, $payService->resMsg );

    }
    catch (SoapFault $ex )
    {
        printf( "%s,%s,%s,%s", "95XX", "", "", "연동 오류 (PHP SOAP 모듈 설치 필요)" );
    }
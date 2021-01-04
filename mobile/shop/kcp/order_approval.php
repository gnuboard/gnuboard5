<?php
    include_once('./_common.php');

    include_once(G5_MSHOP_PATH.'/settle_kcp.inc.php');
    require_once(G5_MSHOP_PATH.'/kcp/KCPComLibrary.php');              // library [수정불가]

?>
<?php
    // 쇼핑몰 페이지에 맞는 문자셋을 지정해 주세요.
    $charSetType      = 'utf-8';             // UTF-8인 경우 "utf-8"로 설정

    $siteCode         = $_GET[ 'site_cd'     ];
    $orderID          = $_GET[ 'ordr_idxx'   ];
    $paymentMethod    = $_GET[ 'pay_method'  ];
    $escrow           = ( $_GET[ 'escw_used'   ] == 'Y' ) ? true : false;
    $productName      = $_GET[ 'good_name'   ];

    // 아래 두값은 POST된 값을 사용하지 않고 서버에 SESSION에 저장된 값을 사용하여야 함.
    $paymentAmount    = $_GET[ 'good_mny'    ]; // 결제 금액
    $returnUrl        = $_GET[ 'Ret_URL'     ];

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
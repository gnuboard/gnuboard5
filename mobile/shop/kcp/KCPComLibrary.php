<?php
  /* ============================================================================== */
  /* =   PAGE : 라이브버리 PAGE                                                   = */
  /* = -------------------------------------------------------------------------- = */
  /* =   Copyright (c)  2010.02   KCP Co., Ltd.   All Rights Reserved.            = */
  /* = -------------------------------------------------------------------------- = */
  /* +   이 모듈에 대한 수정을 금합니다.                                          + */
  /* ============================================================================== */

  /* ============================================================================== */
  /* +   SOAP 연동 CALSS                                                          + */
  /* ============================================================================== */

class   ApproveReq
{
    public  $accessCredentialType;    // AccessCredentialType
    public  $baseRequestType;         // BaseRequestType
    public  $escrow;                  // boolean
    public  $orderID;                 // string
    public  $paymentAmount;           // string
    public  $paymentMethod;           // string
    public  $productName;             // string
    public  $returnUrl;               // string
    public  $siteCode;                // string
}

class ApproveRes
{
    public  $approvalKey;             // string
    public  $baseResponseType;        // BaseResponseType
    public  $payUrl;                  // string
}

class approve
{
    public  $req;                     // ApproveReq
}

class approveResponse
{
    public  $return;                  // ApproveRes
}

class AccessCredentialType
{
    public $accessLicense;            // string
    public $signature;                // string
    public $timestamp;                // string
}

class BaseRequestType
{
    public  $detailLevel;             // string
    public  $requestApp;              // string
    public  $requestID;               // string
    public  $userAgent;               // string
    public  $version;                 // string
}

class BaseResponseType
{
    public  $detailLevel;             // string
    public  $error;                   // ErrorType
    public  $messageID;               // string
    public  $release;                 // string
    public  $requestID;               // string
    public  $responseType;            // string
    public  $timestamp;               // string
    public  $version;                 // string
    public  $warningList;             // ErrorType
}

class ErrorType
{
    public  $code;                    // string
    public  $detail;                  // string
    public  $message;                 // string
}

class PayService extends  SoapClient
{
    private   static    $classmap = array(
                                          'ApproveReq' => 'ApproveReq',
                                          'ApproveRes' => 'ApproveRes',
                                          'approve' => 'approve',
                                          'approveResponse' => 'approveResponse',
                                          'AccessCredentialType' => 'AccessCredentialType',
                                          'BaseRequestType' => 'BaseRequestType',
                                          'BaseResponseType' => 'BaseResponseType',
                                          'ErrorType' => 'ErrorType',
                                         );

    var   $chatsetType;
    var   $accessCredentialType;
    var   $baseRequestType;
    var   $approveReq;
    var   $approveResponse;
    var   $resCD;
    var   $resMsg;


    public  function  PayService( $wsdl = "", $options = array() )
    {
        foreach( self::$classmap as $key => $value )
        {
            if ( !isset( $options[ 'classmap' ][ $key ] ) )
            {
                $options[ 'classmap' ][ $key ] = $value;
            }
        }

        parent::__construct( $wsdl, $options );

        $accessCredentialType = null;
        $baseRequestType      = null;
        $approveReq           = null;
        $resCD                = "95XX";
        $resMsg               = "연동 오류";
    }

    public  function  setCharSet( $charsetType )
    {
        $this->chatsetType = $charsetType;
    }

    public  function  setAccessCredentialType( $accessLicense,
                                               $signature,
                                               $timestamp )
    {
        $this->accessCredentialType = new AccessCredentialType();

        $this->accessCredentialType->accessLicense  = $accessLicense;
        $this->accessCredentialType->signature      = $signature;
        $this->accessCredentialType->timestamp      = $timestamp;
    }

    public  function  setBaseRequestType( $detailLevel,
                                          $requestApp,
                                          $requestID,
                                          $userAgent,
                                          $version   )
    {
        $this->baseRequestType = new BaseRequestType();

        $this->baseRequestType->detailLevel      = $detailLevel;
        $this->baseRequestType->requestApp       = $requestApp;
        $this->baseRequestType->requestID        = $requestID;
        $this->baseRequestType->userAgent        = $userAgent;
        $this->baseRequestType->version          = $version;
    }

    public  function  setApproveReq( $escrow,
                                     $orderID,
                                     $paymentAmount,
                                     $paymentMethod,
                                     $productName,
                                     $returnUrl,
                                     $siteCode )
    {
        $this->approveReq = new ApproveReq();

        $productName_utf8 = ( $this->chatsetType == "euc-kr" ) ? iconv( "EUC-KR", "UTF-8", $productName ) : $productName;

        $this->approveReq->accessCredentialType = $this->accessCredentialType;
        $this->approveReq->baseRequestType      = $this->baseRequestType;
        $this->approveReq->escrow               = $escrow;
        $this->approveReq->orderID              = $orderID;
        $this->approveReq->paymentAmount        = $paymentAmount;
        $this->approveReq->paymentMethod        = $paymentMethod;
        $this->approveReq->productName          = $productName_utf8;
        $this->approveReq->returnUrl            = $returnUrl;
        $this->approveReq->siteCode             = $siteCode;
    }

    public  function  approve()
    {
        $approve = new approve();

        $approve->req = $this->approveReq;

        $this->approveResponse = $this->__soapCall( "approve", array( $approve ),
                                                               array( 'uri' => 'http://webservice.act.webpay.service.kcp.kr',
                                                                      'soapaction' => ''
                                                                     )
                                                  );

        $this->resCD  = $this->approveResponse->return->baseResponseType->error->code;
        $this->resMsg = $this->approveResponse->return->baseResponseType->error->message;

        return  $this->approveResponse->return;
    }
}
<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/json.lib.php');

/**
 * 2014.12.02 : 인증요청 송신 전문 외 항목 제거
 */

include(G5_SHOP_PATH.'/kakaopay/incKakaopayCommon.php');
include(G5_SHOP_PATH.'/kakaopay/lgcns_KMpay.php');

function KMPayRequest($key) {
    return (isset($_REQUEST[$key])?$_REQUEST[$key]:"");
}

// 로그 저장 위치 지정
$kmFunc = new kmpayFunc($LogDir);
$kmFunc->setPhpVersion($phpVersion);

// TXN_ID를 요청하기 위한 PARAMETERR
$REQUESTDEALAPPROVEURL = KMPayRequest("requestDealApproveUrl"); //인증 요청 경로
$PR_TYPE = KMPayRequest("prType");                              //결제 요청 타입
$MERCHANT_ID = KMPayRequest("MID");                             //가맹점 ID
$MERCHANT_TXN_NUM = KMPayRequest("merchantTxnNum");             //가맹점 거래번호
$channelType = KMPayRequest("channelType");
$PRODUCT_NAME = KMPayRequest("GoodsName");                      //상품명
$AMOUNT = KMPayRequest("Amt");                                  //상품금액(총거래금액) (총거래금액 = 공급가액 + 부가세 + 봉사료)

$CURRENCY = KMPayRequest("currency");                           //거래통화(KRW/USD/JPY 등)
$RETURN_URL = KMPayRequest("returnUrl");                        //결제승인결과전송URL
$CERTIFIED_FLAG = KMPayRequest("CERTIFIED_FLAG");               //가맹점 인증 구분값 ("N","NC")

$OFFER_PERIOD_FLAG = KMPayRequest("OFFER_PERIOD_FLAG");         //상품제공기간 플래그
$OFFER_PERIOD = KMPayRequest("OFFER_PERIOD");                   //상품제공기간


//무이자옵션
$NOINTYN = KMPayRequest("noIntYN");                             //무이자 설정
$NOINTOPT = KMPayRequest("noIntOpt");                           //무이자 옵션
$MAX_INT =KMPayRequest("maxInt");                               //최대할부개월
$FIXEDINT = KMPayRequest("fixedInt");                           //고정할부개월
$POINT_USE_YN = KMPayRequest("pointUseYn");                     //카드사포인트사용여부
$POSSICARD = KMPayRequest("possiCard");                         //결제가능카드설정
$BLOCK_CARD = KMPayRequest("blockCard");                        //금지카드설정

// 복합과세
if($default['de_tax_flag_use']) {
    $SUPPLY_AMT  = KMPayRequest("SupplyAmt");                   // 공급가액
    $GOODS_VAT   = KMPayRequest("GoodsVat");                    // 부가가치세
    $SERVICE_AMT = KMPayRequest("ServiceAmt");                  // 봉사료
}

// ENC KEY와 HASH KEY는 가맹점에서 생성한 KEY 로 SETTING 한다.
$merchantEncKey = KMPayRequest("merchantEncKey");
$merchantHashKey = KMPayRequest("merchantHashKey");
    $hashTarget = $MERCHANT_ID.$MERCHANT_TXN_NUM.str_pad($AMOUNT,7,"0",STR_PAD_LEFT);

// payHash 생성
$payHash = strtoupper(hash("sha256", $hashTarget.$merchantHashKey, false));

//json string 생성
$strJsonString = new JsonString($LogDir);

$strJsonString->setValue("PR_TYPE", $PR_TYPE);
$strJsonString->setValue("channelType", $channelType);
$strJsonString->setValue("MERCHANT_ID", $MERCHANT_ID);
$strJsonString->setValue("MERCHANT_TXN_NUM", $MERCHANT_TXN_NUM);
$strJsonString->setValue("PRODUCT_NAME", $PRODUCT_NAME);

$strJsonString->setValue("AMOUNT", $AMOUNT);

$strJsonString->setValue("CURRENCY", $CURRENCY);
$strJsonString->setValue("CERTIFIED_FLAG", $CERTIFIED_FLAG);

$strJsonString->setValue("OFFER_PERIOD_FLAG", $OFFER_PERIOD_FLAG);
$strJsonString->setValue("OFFER_PERIOD", $OFFER_PERIOD);

$strJsonString->setValue("NO_INT_YN", $NOINTYN);
$strJsonString->setValue("NO_INT_OPT", $NOINTOPT);
$strJsonString->setValue("MAX_INT", $MAX_INT);
$strJsonString->setValue("FIXED_INT", $FIXEDINT);

$strJsonString->setValue("POINT_USE_YN", $POINT_USE_YN);
$strJsonString->setValue("POSSI_CARD", $POSSICARD);
$strJsonString->setValue("BLOCK_CARD", $BLOCK_CARD);

// 복합과세
if($default['de_tax_flag_use']) {
    $strJsonString->setValue("SUPPLY_AMT",  $SUPPLY_AMT);
    $strJsonString->setValue("GOODS_VAT",   $GOODS_VAT);
    $strJsonString->setValue("SERVICE_AMT", $SERVICE_AMT);
}

$strJsonString->setValue("PAYMENT_HASH", $payHash);

// 결과값을 담는 부분
$resultCode = "";
$resultMsg = "";
$txnId = "";
$merchantTxnNum = "";
$prDt = "";
$strValid = "";

// Data 검증
$dataValidator = new KMPayDataValidator($strJsonString->getArrayValue());
$strValid = $dataValidator->resultValid;
if (strlen($strValid) > 0) {
    $arrVal = explode(",", $strValid);
    if (count($arrVal) == 3) {
        $resultCode = $arrVal[1];
        $resultMsg = $arrVal[2];
    } else {
        $resultCode = $strValid;
        $resultMsg = $strValid;
    }
}

// Data에 이상 없는 경우
if (strlen($strValid) == 0) {
    // CBC 암호화
    $paramStr = $strJsonString->getJsonString();
    $kmFunc->writeLog("Request");
    $kmFunc->writeLog($paramStr);
    $kmFunc->writeLog($strJsonString->getArrayValue());
    $encryptStr = $kmFunc->parameterEncrypt($merchantEncKey, $paramStr);
    $payReqResult = $kmFunc->connMPayDLP($REQUESTDEALAPPROVEURL, $MERCHANT_ID, $encryptStr);
    $resultString = $kmFunc->parameterDecrypt($merchantEncKey, $payReqResult);

    $resultJSONObject = new JsonString($LogDir);
    if (substr($resultString, 0, 1) == "{") {
        $resultJSONObject->setJsonString($resultString);
        $resultCode = $resultJSONObject->getValue("RESULT_CODE");
        $resultMsg = $resultJSONObject->getValue("RESULT_MSG");
        if ($resultCode == "00") {
            $txnId = $resultJSONObject->getValue("TXN_ID");
            $merchantTxnNum = $resultJSONObject->getValue("MERCHANT_TXN_NUM");
            $prDt = $resultJSONObject->getValue("PR_DT");
        }
    }
    $kmFunc->writeLog("Result");
    $kmFunc->writeLog($resultString);
    $kmFunc->writeLog($resultJSONObject->getArrayValue());
}

$result = array();

$result = array(
    'resultCode' => $resultCode,
    'resultMsg'  => $resultMsg,
    'txnId'      => $txnId,
    'prDt'       => $prDt
);

die(json_encode($result));
?>
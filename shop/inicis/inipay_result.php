<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

/* INIsecureresult.php
 *
 * 이니페이 플러그인을 통해 요청된 지불을 처리한다.
 * 지불 요청을 처리한다.
 * 코드에 대한 자세한 설명은 매뉴얼을 참조하십시오.
 * <주의> 구매자의 세션을 반드시 체크하도록하여 부정거래를 방지하여 주십시요.
 *
 * http://www.inicis.com
 * Copyright (C) 2006 Inicis Co., Ltd. All rights reserved.
 */

 include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

/*********************
 * 3. 지불 정보 설정 *
*********************/
if($default['de_tax_flag_use']) { // 복합과세 사용 때
    $inipay->SetXPath("INIpay/GoodsInfo/Tax",     $_POST['comm_vat_mny']);  // 부가세 금액
    $inipay->SetXPath("INIpay/GoodsInfo/TaxFree", $_POST['comm_free_mny']); // 면세 금액
}
$inipay->SetField("type",     "securepay");                // 고정 (절대 수정 불가)
$inipay->SetField("pgid",     "INIphp".$pgid);             // 고정 (절대 수정 불가)
$inipay->SetField("subpgip",  "203.238.3.10");             // 고정 (절대 수정 불가)
$inipay->SetField("admin",    get_session('INI_ADMIN'));   // 키패스워드(상점아이디에 따라 변경)
$inipay->SetField("uid",      $uid);                       // INIpay User ID (절대 수정 불가)
$inipay->SetField("goodname", iconv_euckr($goodname));     // 상품명
$inipay->SetField("currency", $currency);                  // 화폐단위

$inipay->SetField("mid",      get_session('INI_MID'));     // 상점아이디
$inipay->SetField("rn",       get_session('INI_RN'));      // 웹페이지 위변조용 RN값
$inipay->SetField("price",    get_session('INI_PRICE'));   // 가격
$inipay->SetField("enctype",  get_session('INI_ENCTYPE')); // 고정 (절대 수정 불가)


 /*----------------------------------------------------------------------------------------
price 등의 중요데이터는
브라우저상의 위변조여부를 반드시 확인하셔야 합니다.

결제 요청페이지에서 요청된 금액과
실제 결제가 이루어질 금액을 반드시 비교하여 처리하십시오.

설치 메뉴얼 2장의 결제 처리페이지 작성부분의 보안경고 부분을 확인하시기 바랍니다.
적용참조문서: 이니시스홈페이지->가맹점기술지원자료실->기타자료실 의
              '결제 처리 페이지 상에 결제 금액 변조 유무에 대한 체크' 문서를 참조하시기 바랍니다.
예제)
원 상품 가격 변수를 OriginalPrice 하고  원 가격 정보를 리턴하는 함수를 Return_OrgPrice()라 가정하면
다음 같이 적용하여 원가격과 웹브라우저에서 Post되어 넘어온 가격을 비교 한다.

$OriginalPrice = Return_OrgPrice();
$PostPrice = $_SESSION['INI_PRICE'];
if ( $OriginalPrice != $PostPrice )
{
    //결제 진행을 중단하고  금액 변경 가능성에 대한 메시지 출력 처리
    //처리 종료
}

----------------------------------------------------------------------------------------*/
$inipay->SetField("buyername",   iconv_euckr($buyername)); // 구매자 명
$inipay->SetField("buyertel",    $buyertel);               // 구매자 연락처(휴대폰 번호 또는 유선전화번호)
$inipay->SetField("buyeremail",  $buyeremail);             // 구매자 이메일 주소
$inipay->SetField("paymethod",   $paymethod);              // 지불방법 (절대 수정 불가)
$inipay->SetField("encrypted",   $encrypted);              // 암호문
$inipay->SetField("sessionkey",  $sessionkey);             // 암호문
$inipay->SetField("url",         G5_URL);                  // 실제 서비스되는 상점 SITE URL로 변경할것
$inipay->SetField("cardcode",    $cardcode);               // 카드코드 리턴
$inipay->SetField("parentemail", $parentemail);            // 보호자 이메일 주소(핸드폰 , 전화결제시에 14세 미만의 고객이 결제하면  부모 이메일로 결제 내용통보 의무, 다른결제 수단 사용시에 삭제 가능)

/*-----------------------------------------------------------------*
 * 수취인 정보 *                                                   *
 *-----------------------------------------------------------------*
 * 실물배송을 하는 상점의 경우에 사용되는 필드들이며               *
 * 아래의 값들은 INIsecurepay.html 페이지에서 포스트 되도록        *
 * 필드를 만들어 주도록 하십시요.                                  *
 * 컨텐츠 제공업체의 경우 삭제하셔도 무방합니다.                   *
 *-----------------------------------------------------------------*/
$inipay->SetField("recvname",    iconv_euckr($recvname)); // 수취인 명
$inipay->SetField("recvtel",     $recvtel);               // 수취인 연락처
$inipay->SetField("recvaddr",    iconv_euckr($recvaddr)); // 수취인 주소
$inipay->SetField("recvpostnum", $recvpostnum);           // 수취인 우편번호
$inipay->SetField("recvmsg",     $recvmsg);               // 전달 메세지

$inipay->SetField("joincard",    $joincard);              // 제휴카드코드
$inipay->SetField("joinexpire",  $joinexpire);            // 제휴카드유효기간
$inipay->SetField("id_customer", $id_customer);           //user_id


/****************
 * 4. 지불 요청 *
 ****************/
$inipay->startAction();

/****************************************************************************************************************
 * 5. 결제  결과
 *
 *  1 모든 결제 수단에 공통되는 결제 결과 데이터
 *  거래번호 : $inipay->GetResult('TID')
 *  결과코드 : $inipay->GetResult('ResultCode') ("00"이면 지불 성공)
 *  결과내용 : $inipay->GetResult('ResultMsg') (지불결과에 대한 설명)
 *  지불방법 : $inipay->GetResult('PayMethod') (매뉴얼 참조)
 *  상점주문번호 : $inipay->GetResult('MOID')
 *  결제완료금액 : $inipay->GetResult('TotPrice')
 *
 * 결제 되는 금액 =>원상품가격과  결제결과금액과 비교하여 금액이 동일하지 않다면
 * 결제 금액의 위변조가 의심됨으로 정상적인 처리가 되지않도록 처리 바랍니다. (해당 거래 취소 처리)
 *
 *
 *  2. 신용카드,ISP,핸드폰, 전화 결제, 은행계좌이체, OK CASH BAG Point 결제 결과 데이터
 *      (무통장입금 , 문화 상품권 포함)
 *  이니시스 승인날짜 : $inipay->GetResult('ApplDate') (YYYYMMDD)
 *  이니시스 승인시각 : $inipay->GetResult('ApplTime') (HHMMSS)
 *
 *  3. 신용카드 결제 결과 데이터
     *
 *  신용카드 승인번호 : $inipay->GetResult('ApplNum')
 *  할부기간 : $inipay->GetResult('CARD_Quota')
 *  무이자할부 여부 : $inipay->GetResult('CARD_Interest') ("1"이면 무이자할부)
 *  신용카드사 코드 : $inipay->GetResult('CARD_Code') (매뉴얼 참조)
 *  카드발급사 코드 : $inipay->GetResult('CARD_BankCode') (매뉴얼 참조)
 *  본인인증 수행여부 : $inipay->GetResult('CARD_AuthType') ("00"이면 수행)
 *      각종 이벤트 적용 여부 : $inipay->GetResult('EventCode')
 *
 *      ** 달러결제 시 통화코드와  환률 정보 **
 *  해당 통화코드 : $inipay->GetResult('OrgCurrency')
 *  환율 : $inipay->GetResult('ExchangeRate')
 *
 *      아래는 "신용카드 및 OK CASH BAG 복합결제" 또는"신용카드 지불시에 OK CASH BAG적립"시에 추가되는 데이터
 *  OK Cashbag 적립 승인번호 : $inipay->GetResult('OCB_SaveApplNum')
 *  OK Cashbag 사용 승인번호 : $inipay->GetResult('OCB_PayApplNum')
 *  OK Cashbag 승인일시 : $inipay->GetResult('OCB_ApplDate') (YYYYMMDDHHMMSS)
 *  OCB 카드번호 : $inipay->GetResult('OCB_Num')
 *  OK Cashbag 복합결재시 신용카드 지불금액 : $inipay->GetResult('CARD_ApplPrice')
 *  OK Cashbag 복합결재시 포인트 지불금액 : $inipay->GetResult('OCB_PayPrice')
 *
 * 4. 실시간 계좌이체 결제 결과 데이터
 *
 *  은행코드 : $inipay->GetResult('ACCT_BankCode')
 *  현금영수증 발행결과코드 : $inipay->GetResult('CSHR_ResultCode')
 *  현금영수증 발행구분코드 : $inipay->GetResult('CSHR_Type')
 *                                                      *
 * 5. OK CASH BAG 결제수단을 이용시에만  결제 결과 데이터
 *  OK Cashbag 적립 승인번호 : $inipay->GetResult('OCB_SaveApplNum')
 *  OK Cashbag 사용 승인번호 : $inipay->GetResult('OCB_PayApplNum')
 *  OK Cashbag 승인일시 : $inipay->GetResult('OCB_ApplDate') (YYYYMMDDHHMMSS)
 *  OCB 카드번호 : $inipay->GetResult('OCB_Num')
 *
     * 6. 무통장 입금 결제 결과 데이터                                                  *
 *  가상계좌 채번에 사용된 주민번호 : $inipay->GetResult('VACT_RegNum')                               *
 *  가상계좌 번호 : $inipay->GetResult('VACT_Num')                                                    *
 *  입금할 은행 코드 : $inipay->GetResult('VACT_BankCode')                                             *
 *  입금예정일 : $inipay->GetResult('VACT_Date') (YYYYMMDD)                                          *
 *  송금자 명 : $inipay->GetResult('VACT_InputName')                                                    *
 *  예금주 명 : $inipay->GetResult('VACT_Name')                                                     *
 *                                                      *
 * 7. 핸드폰, 전화 결제 결과 데이터( "실패 내역 자세히 보기"에서 필요 , 상점에서는 필요없는 정보임)             *
     *  전화결제 사업자 코드 : $inipay->GetResult('HPP_GWCode')                                          *
 *                                                      *
 * 8. 핸드폰 결제 결과 데이터                                                     *
 *  휴대폰 번호 : $inipay->GetResult('HPP_Num') (핸드폰 결제에 사용된 휴대폰번호)                          *
 *                                                      *
 * 9. 전화 결제 결과 데이터                                                      *
*   전화번호 : $inipay->GetResult('ARSB_Num') (전화결제에  사용된 전화번호)                             *
*                                                       *
* 10. 문화 상품권 결제 결과 데이터                                                  *
*   컬쳐 랜드 ID : $inipay->GetResult('CULT_UserID')                                                *
*                                                       *
* 11. K-merce 상품권 결제 결과 데이터 (K-merce ID, 틴캐시 아이디 공통사용)                                     *
*      K-merce ID : $inipay->GetResult('CULT_UserID')                                                                       *
*                                                                                                              *
* 12. 모든 결제 수단에 대해 결제 실패시에만 결제 결과 데이터                           *
*   에러코드 : $inipay->GetResult('ResultErrorCode')                                                *
*                                                       *
* 13.현금영수증 발급 결과코드 (은행계좌이체시에만 리턴)                           *
*    $inipay->GetResult('CSHR_ResultCode')                                                                                     *
*                                                                                                              *
* 14.틴캐시 잔액 데이터                                                         *
*    $inipay->GetResult('TEEN_Remains')                                                                             *
*   틴캐시 ID : $inipay->GetResult('CULT_UserID')                                                  *
* 15.게임문화 상품권                           *
*   사용 카드 갯수 : $inipay->GetResult('GAMG_Cnt')                                           *
*                                                       *
****************************************************************************************************************/

if( $inipay->GetResult("ResultCode") == "00" ) {
    //최종결제요청 결과 성공 DB처리
    $tno             = $inipay->GetResult('TID');
    $amount          = $inipay->GetResult('TotPrice');
    $app_time        = $inipay->GetResult('ApplDate').$inipay->GetResult('ApplTime');
    $pay_method      = $inipay->GetResult('PayMethod');
    $pay_type        = $PAY_METHOD[$pay_method];
    $depositor       = iconv_utf8($inipay->GetResult('VACT_InputName'));
    $commid          = '';
    $mobile_no       = $inipay->GetResult('HPP_Num');
    $app_no          = $inipay->GetResult('ApplNum');
    $card_name       = $CARD_CODE[$inipay->GetResult('CARD_Code')];
    if ($default['de_escrow_use'] == 1)
        $escw_yn         = 'Y';
    switch($pay_type) {
        case '계좌이체':
            $bank_name = $BANK_CODE[$inipay->GetResult('ACCT_BankCode')];
            break;
        case '가상계좌':
            $bankname  = $BANK_CODE[$inipay->GetResult('VACT_BankCode')];
            $account   = $inipay->GetResult('VACT_Num').' '.iconv_utf8($inipay->GetResult('VACT_Name'));
            $app_no    = $inipay->GetResult('VACT_Num');
            break;
        default:
            break;
    }
} else {
    alert(iconv_utf8($inipay->GetResult("ResultMsg")).' 코드 : '.$inipay->GetResult("ResultCode"));
}

?>
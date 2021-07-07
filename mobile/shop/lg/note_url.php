<?php
include_once('./_common.php');

/*
 * 공통결제결과 정보
 */
$LGD_RESPCODE = "";           			// 응답코드: 0000(성공) 그외 실패
$LGD_RESPMSG = "";            			// 응답메세지
$LGD_MID = "";                			// 상점아이디
$LGD_OID = "";                			// 주문번호
$LGD_AMOUNT = "";             			// 거래금액
$LGD_TID = "";                			// LG유플러스에서 부여한 거래번호
$LGD_PAYTYPE = "";            			// 결제수단코드
$LGD_PAYDATE = "";            			// 거래일시(승인일시/이체일시)
$LGD_HASHDATA = "";           			// 해쉬값
$LGD_FINANCECODE = "";        			// 결제기관코드(카드종류/은행코드/이통사코드)
$LGD_FINANCENAME = "";        			// 결제기관이름(카드이름/은행이름/이통사이름)
$LGD_ESCROWYN = "";           			// 에스크로 적용여부
$LGD_TIMESTAMP = "";          			// 타임스탬프
$LGD_FINANCEAUTHNUM = "";     			// 결제기관 승인번호(신용카드, 계좌이체, 상품권)

/*
 * 신용카드 결제결과 정보
 */
$LGD_CARDNUM = "";            			// 카드번호(신용카드)
$LGD_CARDINSTALLMONTH = "";   			// 할부개월수(신용카드)
$LGD_CARDNOINTYN = "";        			// 무이자할부여부(신용카드) - '1'이면 무이자할부 '0'이면 일반할부
$LGD_TRANSAMOUNT = "";        			// 환율적용금액(신용카드)
$LGD_EXCHANGERATE = "";       			// 환율(신용카드)

/*
 * 휴대폰
 */
$LGD_PAYTELNUM = "";          			// 결제에 이용된전화번호

/*
 * 계좌이체, 무통장
 */
$LGD_ACCOUNTNUM = "";         			// 계좌번호(계좌이체, 무통장입금)
$LGD_CASTAMOUNT = "";         			// 입금총액(무통장입금)
$LGD_CASCAMOUNT = "";         			// 현입금액(무통장입금)
$LGD_CASFLAG = "";            			// 무통장입금 플래그(무통장입금) - 'R':계좌할당, 'I':입금, 'C':입금취소
$LGD_CASSEQNO = "";           			// 입금순서(무통장입금)
$LGD_CASHRECEIPTNUM = "";     			// 현금영수증 승인번호
$LGD_CASHRECEIPTSELFYN = "";  			// 현금영수증자진발급제유무 Y: 자진발급제 적용, 그외 : 미적용
$LGD_CASHRECEIPTKIND = "";    			// 현금영수증 종류 0: 소득공제용 , 1: 지출증빙용

/*
 * OK캐쉬백
 */
$LGD_OCBSAVEPOINT = "";       			// OK캐쉬백 적립포인트
$LGD_OCBTOTALPOINT = "";      			// OK캐쉬백 누적포인트
$LGD_OCBUSABLEPOINT = "";     			// OK캐쉬백 사용가능 포인트

/*
 * 구매정보
 */
$LGD_BUYER = "";              			// 구매자
$LGD_PRODUCTINFO = "";        			// 상품명
$LGD_BUYERID = "";            			// 구매자 ID
$LGD_BUYERADDRESS = "";       			// 구매자 주소
$LGD_BUYERPHONE = "";         			// 구매자 전화번호
$LGD_BUYEREMAIL = "";         			// 구매자 이메일
$LGD_BUYERSSN = "";           			// 구매자 주민번호
$LGD_PRODUCTCODE = "";        			// 상품코드
$LGD_RECEIVER = "";           			// 수취인
$LGD_RECEIVERPHONE = "";      			// 수취인 전화번호
$LGD_DELIVERYINFO = "";       			// 배송지


$LGD_RESPCODE            = $_POST["LGD_RESPCODE"];
$LGD_RESPMSG             = $_POST["LGD_RESPMSG"];
$LGD_MID                 = $_POST["LGD_MID"];
$LGD_OID                 = $_POST["LGD_OID"];
$LGD_AMOUNT              = $_POST["LGD_AMOUNT"];
$LGD_TID                 = $_POST["LGD_TID"];
$LGD_PAYTYPE             = $_POST["LGD_PAYTYPE"];
$LGD_PAYDATE             = $_POST["LGD_PAYDATE"];
$LGD_HASHDATA            = $_POST["LGD_HASHDATA"];
$LGD_FINANCECODE         = $_POST["LGD_FINANCECODE"];
$LGD_FINANCENAME         = $_POST["LGD_FINANCENAME"];
$LGD_ESCROWYN            = $_POST["LGD_ESCROWYN"];
$LGD_TRANSAMOUNT         = $_POST["LGD_TRANSAMOUNT"];
$LGD_EXCHANGERATE        = $_POST["LGD_EXCHANGERATE"];
$LGD_CARDNUM             = $_POST["LGD_CARDNUM"];
$LGD_CARDINSTALLMONTH    = $_POST["LGD_CARDINSTALLMONTH"];
$LGD_CARDNOINTYN         = $_POST["LGD_CARDNOINTYN"];
$LGD_TIMESTAMP           = $_POST["LGD_TIMESTAMP"];
$LGD_FINANCEAUTHNUM      = $_POST["LGD_FINANCEAUTHNUM"];
$LGD_PAYTELNUM           = $_POST["LGD_PAYTELNUM"];
$LGD_ACCOUNTNUM          = $_POST["LGD_ACCOUNTNUM"];
$LGD_CASTAMOUNT          = $_POST["LGD_CASTAMOUNT"];
$LGD_CASCAMOUNT          = $_POST["LGD_CASCAMOUNT"];
$LGD_CASFLAG             = $_POST["LGD_CASFLAG"];
$LGD_CASSEQNO            = $_POST["LGD_CASSEQNO"];
$LGD_CASHRECEIPTNUM      = $_POST["LGD_CASHRECEIPTNUM"];
$LGD_CASHRECEIPTSELFYN   = $_POST["LGD_CASHRECEIPTSELFYN"];
$LGD_CASHRECEIPTKIND     = $_POST["LGD_CASHRECEIPTKIND"];
$LGD_OCBSAVEPOINT        = $_POST["LGD_OCBSAVEPOINT"];
$LGD_OCBTOTALPOINT       = $_POST["LGD_OCBTOTALPOINT"];
$LGD_OCBUSABLEPOINT      = $_POST["LGD_OCBUSABLEPOINT"];

$LGD_BUYER               = $_POST["LGD_BUYER"];
$LGD_PRODUCTINFO         = $_POST["LGD_PRODUCTINFO"];
$LGD_BUYERID             = $_POST["LGD_BUYERID"];
$LGD_BUYERADDRESS        = $_POST["LGD_BUYERADDRESS"];
$LGD_BUYERPHONE          = $_POST["LGD_BUYERPHONE"];
$LGD_BUYEREMAIL          = $_POST["LGD_BUYEREMAIL"];
$LGD_BUYERSSN            = $_POST["LGD_BUYERSSN"];
$LGD_PRODUCTCODE         = $_POST["LGD_PRODUCTCODE"];
$LGD_RECEIVER            = $_POST["LGD_RECEIVER"];
$LGD_RECEIVERPHONE       = $_POST["LGD_RECEIVERPHONE"];
$LGD_DELIVERYINFO        = $_POST["LGD_DELIVERYINFO"];

$LGD_MERTKEY = $config['cf_lg_mert_key'];  //LG유플러스에서 발급한 상점키로 변경해 주시기 바랍니다.

$LGD_HASHDATA2 = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_RESPCODE.$LGD_TIMESTAMP.$LGD_MERTKEY);

/*
 * 상점 처리결과 리턴메세지
 *
 * OK   : 상점 처리결과 성공
 * 그외 : 상점 처리결과 실패
 *
 * ※ 주의사항 : 성공시 'OK' 문자이외의 다른문자열이 포함되면 실패처리 되오니 주의하시기 바랍니다.
 */
$resultMSG = "결제결과 상점 DB처리(NOTE_URL) 결과값을 입력해 주시기 바랍니다.";

if ($LGD_HASHDATA2 == $LGD_HASHDATA) {      //해쉬값 검증이 성공하면
    if($LGD_RESPCODE == "0000"){            //결제가 성공이면
        /*
         * 거래성공 결과 상점 처리(DB) 부분
         * 상점 결과 처리가 정상이면 "OK"
         */
        //if( 결제성공 상점처리결과 성공 )
        $resultMSG = "OK";
    }else {                                 //결제가 실패이면
        /*
         * 거래실패 결과 상점 처리(DB) 부분
         * 상점결과 처리가 정상이면 "OK"
         */
       //if( 결제실패 상점처리결과 성공 )
       $resultMSG = "OK";
    }
} else {                                    //해쉬값 검증이 실패이면
    /*
     * hashdata검증 실패 로그를 처리하시기 바랍니다.
     */
    $resultMSG = "결제결과 상점 DB처리(NOTE_URL) 해쉬값 검증이 실패하였습니다.";
}

echo $resultMSG;
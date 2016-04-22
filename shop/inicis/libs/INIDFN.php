<?php

/**
 * Copyright (C) 2007 INICIS Inc.
 *
 * 해당 라이브러리는 절대 수정되어서는 안됩니다.
 * 임의로 수정된 코드에 대한 책임은 전적으로 수정자에게 있음을 알려드립니다.
 *
 * @version         5.0
 * @author          ddaemiri
 *
 * @buildno			    5000
 * @date            2007.12.27
 * @note						first release
 *
 * @buildno			    5001
 * @date            2008.01.15
 * @note						자체에스크로 추가, DNS Lookup Timeout 추가, DNS Lookup 실패시 socket close안하는 부분 추가
 * @date            2008.01.16
 * @note						Encrypt,Decrypt 모듈 개선, pkcs5 padding 추가
 * @date            2008.01.24
 * @note						non block connect immediate return check code/str	
 * @date            2008.02.11
 * @note						key load 후 read에러발생시 fclose 추가
 * @date            2008.03.03
 * @note						참조에 의한 전달(passing by reference) 수정
 * @date            2008.03.11
 * @note						에스크로 망취소 Cmd 추가
 * @date            2008.03.12
 * @note						기존 연결된 IP로 망취소 수정
 * @buildno			    5002
 * @note						망취소시 Sign추가(기존에 안했음->5001은 망취소시 서버측에서 서명오류가 남,그래도 망취소처리는 됨)
 * @date            2008.03.12
 * @buildno			    5016
 * @note						pg1ip, pg2ip 추가/취소 원거래없음시 drpg로 재시도
 * @date            2008.04.02
 * @buildno			    5017
 * @note						1)chkfake시 파라미터 로깅 삭제(중복)
 * 									2)call-time pass-by-reference => pass-by-value로 수정
 * @date            2009.01.12
 * @buildno			    5019
 * @note						delete UIP
 * @date            2009.01.21
 * @note						add mkey/mergelog ( for Makeshop )
 * @date            2009.02.24
 * @note            1)define MKEY deprecated 2009.02.19 ( Makeshop 초기 한번나감. 서비스되고 있는지는 모름)
 *                  2)Makeshop PG Updrade용으로 새로나감 2009.02.19 (interface에서 mkey값을 받아 처리하게 변경)
 *                  3)Makeshop PG Updrade용으로 새로나감 2009.02.24 (interface에서 mergelog값을 받아 처리하게 변경)
 * @date            2009.05.07
 * @note						add BUILDDATE in TXVersion
 * @date            2009.08.05
 * @buildno			    5030
 * @note						add vacct refund
 * @date            2009.12.16
 * @buildno			    5030
 * @note						add recv info
 * @date			2010.07.14  
 * @note 						add Tax, TaxFree info(TX_TAX, TX_TAXFREE)
 * @date 			2010.09.09
 * @note						취소요청시 PG 설정 변경(도메인->IP, INILib.php)
 * @note 						BUILDDATE update(100414 -> 100909)
 * @note            2011.05.23      5030                XML ELEMENT 중  ROOT 를 ROOTINFO로 수정
 * @buildno			    5032
 * @note			2012.07.09	거래조회 추가 (TYPE_INQUIRY)
 * @note			2012.07.09	PHP ini 파일 error display setting 추가
 * @buildno			    5036
 * @note			2014.12.09	add gather parameter tid, type
 */
ini_set('error_reporting', E_ALL ^ E_NOTICE);
ini_set('display_errors', 'Off');

/* GLOBAL */
define("PROGRAM", "INIPHP");
define("LANG", "PHP");
define("VERSION", "5036");
define("BUILDDATE", "141209");
define("TID_LEN", 40);
define("MAX_KEY_LEN", 24);
define("MAX_IV_LEN", 8);

/* TIMEOUT */
define("TIMEOUT_CONNECT", 5);
define("TIMEOUT_WRITE", 2);
define("TIMEOUT_READ", 20);
define("G_TIMEOUT_CONNECT", 2);
define("DNS_LOOKUP_TIMEOUT", 5);

/* LOG LEVEL */
define("CRITICAL", 1);
define("ERROR", 2);
define("NOTICE", 3);
define("INFO", 5);
define("DEBUG", 7);

/* SERVER INFO */
define("PG_HOST", "pg.inicis.com");
define("DRPG_HOST", "drpg.inicis.com");
define("PG_IP", "203.238.37.3");
define("DRPG_IP", "211.219.96.180");
define("PG_PORT", 34049);
define("G_SERVER", "gthr.inicis.com");
define("G_CGI", "/cgi-bin/g.cgi");
define("G_PORT", 80);

define("OK", "0");

define("IV", "Initiative Tech");
define("IMHK", "SFBQSU5JTkZPUk1BVElPTg==");
define("IMHV", "SU5JQ0lTIENJUEhFUi4uLg==");
define("IMJK", "UkVHSVNUX05PX1JDNEtFWQ==");
define("IMJV", "UkVHSVNUX05PX1JDNElW");

//define for mkey
//deprecated 2009.02.19 ( Makeshop 초기 한번나감. 서비스되고 있는지는 모름)
//Makeshop PG Updrade용으로 새로나감 2009.02.19 (interface에서 mkey값을 받아 처리하게 변경)
//define("MKEY", 1);
//non block connect immediate return check code/str
define("ERRSTR_INPROGRESS", "Operation now in progress");
define("ERRCODE_INPROGRESS_LINUX", 115);
define("ERRCODE_INPROGRESS_FREEBSD", 36);
define("ERRCODE_INPROGRESS_WIN", 10035);

//------------------------------------------------------
// IFD Header
//------------------------------------------------------
define("MSGHEADER_LEN", 150);
define("BODY_LEN", 5);
define("TAIL_LEN", 5);
define("FLGCRYPTO_LEN", 1);
define("FLGSIGN_LEN", 5);
define("MPUBSN_LEN", 20);
define("PIPGPUBSN_LEN", 20);
define("TXPGPUBSN_LEN", 20);
define("CMD_LEN", 4);
define("MID_LEN", 10);
define("TOTPRICE_LEN", 20);
define("TID_LEN", 40);


//------------------------------------------------------
// IFD CMD
//------------------------------------------------------
define("CMD_REQ_PAY", "0200");
define("CMD_RES_PAY", "0210");
define("CMD_REQ_CAP", "0300");
define("CMD_RES_CAP", "0310");
define("CMD_REQ_CAN", "0420");
define("CMD_RES_CAN", "0430");
define("CMD_REQ_NETC", "0520");
define("CMD_RES_NETC", "0530");
define("CMD_REQ_PRTC", "0620");
define("CMD_RES_PRTC", "0630");
define("CMD_REQ_ACK", "0800");
define("CMD_RES_ACK", "0810");
//자체에스크로
//added 2008.01.08
define("CMD_REQ_DLV", "3020"); //배송등록
define("CMD_REQ_CNF", "3030"); //구매확인
define("CMD_REQ_DNY", "3040"); //구매거절 
define("CMD_REQ_DNY_CNF", "3080"); //거절확인
define("CMD_REQ_DLV_NETC", "3520"); //배송등록망상취소
define("CMD_REQ_CNF_NETC", "3530"); //구매확인망상취소
define("CMD_REQ_DNY_NETC", "3540"); //구매거절망상취소 
//가상계좌환불(09.08.05)
define("CMD_REQ_RFD", "0421");
define("CMD_RES_RFD", "0431");

//거래조회(12.04.20)
define("CMS_REQ_INQR", "0900");
define("CMS_RES_INQR", "0910");

//서브몰하위가맹점등록(14.03.06)
define("CMS_REQ_OPEN_SUB", "1040");
define("CMS_RES_OPEN_SUB", "1041");

//------------------------------------------------------
// HEADER FLAGS
//------------------------------------------------------
define("FLAG_TEST", "T");
define("FLAG_REAL", "R");
define("FLAG_CRYPTO_NONE", "N");
define("FLAG_CRYPTO_SEED", "S");
define("FLAG_CRYPTO_RC4", "R");
define("FLAG_CRYPTO_3DES", "D");
define("FLAG_SIGN_SHA", "SHA");
define("FLAG_SIGN_SHA1", "SHA1");
define("FLAG_SIGN_MD5", "MD5");

//------------------------------------------------------
//TYPE(서비스별)
//------------------------------------------------------
define("TYPE_SECUREPAY", "securepay");
define("TYPE_CANCEL", "cancel");
define("TYPE_FORMPAY", "formpay");
define("TYPE_RECEIPT", "receipt");
define("TYPE_REPAY", "repay");
define("TYPE_ESCROW", "escrow");  //자체에스크로!
define("TYPE_CONFIRM", "confirm");
define("TYPE_OCBQUERY", "ocbquery");
define("TYPE_OCBSAVE", "ocbsave");
define("TYPE_OCBPOINT", "OCBPoint");
define("TYPE_AUTH", "auth");
define("TYPE_AUTHBILL", "auth_bill");
define("TYPE_CAPTURE", "capture");
define("TYPE_CMS", "CMS");
define("TYPE_VBANK", "VBank");
define("TYPE_REQREALBILL", "reqrealbill");
define("TYPE_FORMAUTH", "formauth");
define("TYPE_CHKFAKE", "chkfake");
//가상계좌환불(09.08.05)
define("TYPE_REFUND", "refund");
//가상계좌부분환불(12.06.05)
define("TYPE_VACCTREPAY", "vacctrepay");
//거래조회(12.04.20)
define("TYPE_INQUIRY", "inquiry");
//서브몰하위가맹점등록(14.03.06)
define("TYPE_OPENSUB", "opensub");
//------------------------------------------------------
//EscrowType(자체에스크로 타입)
//added 2008.01.08
//------------------------------------------------------
define("TYPE_ESCROW_DLV", "dlv");
define("TYPE_ESCROW_CNF", "confirm"); //구매확인/거절(플러그인)
define("TYPE_ESCROW_DNY", "deny");  //위에서 처리됨,의미없음
define("TYPE_ESCROW_DNY_CNF", "dcnf");


//------------------------------------------------------
//PayMethod(서비스별, TX)
//------------------------------------------------------
define("NM_TX_ISP", "VCard");
define("NM_TX_CARD", "Card");
define("NM_TX_HPP", "HPP");
define("NM_TX_ACCT", "DirectBank");
define("NM_TX_VACT", "VBank");
define("NM_TX_OCB", "OCBPoint");
define("NM_TX_CSHR", "CASH");
define("NM_TX_ARSB", "Ars1588Bill");
define("NM_TX_PHNB", "PhoneBill");
define("NM_TX_CULT", "Culture");
define("NM_TX_GAMG", "DGCL");
define("NM_TX_EDUG", "EDCL");
define("NM_TX_TEEN", "TEEN");
define("NM_TX_ESCR", "Escrow");

//------------------------------------------------------
//PayMethod(서비스별, PG)
//------------------------------------------------------
define("NM_ISP", "ISP");
define("NM_CARD", "CARD");
define("NM_HPP", "HPP");
define("NM_ACCT", "ACCT");
define("NM_VACT", "VACT");
define("NM_OCB", "OCB");
define("NM_CSHR", "CASH");
define("NM_ARSB", "ARSB");
define("NM_PHNB", "PHNB");
define("NM_CULT", "CULT");
define("NM_GAMG", "DGCL");
define("NM_EDUG", "EDCL");
define("NM_TEEN", "TEEN");
define("NM_ESCR", "Escrow");

//------------------------------------------------------
//Charset
//------------------------------------------------------
define("EUCKR", "EUC-KR");
define("UTF8", "UTF-8");

//------------------------------------------------------
//URL Encoding/Decoding Name
//------------------------------------------------------
define("URLENCODE", "urlencode");
define("URLDECODE", "urldecode");

//------------------------------------------------------
//요청전문
//------------------------------------------------------
define("TX_GOOSCNT", "GoodsCnt");
define("TX_MOID", "MOID");
define("TX_CURRENCY", "Currency");
define("TX_SMID", "SMID");
define("TX_GOODSCNTS", "GoodsCnts");
define("TX_GOODSNAME", "GoodsName");
define("TX_GOODSPRICE", "GoodsPrice");
define("TX_BUYERNAME", "BuyerName");
define("TX_BUYEREMAIL", "BuyerEmail");
define("TX_BUYERTEL", "BuyerTel");
define("TX_PARENTEMAIL", "ParentEmail");
define("TX_RECVNAME", "RecvName");
define("TX_RECVTEL", "RecvTel");
define("TX_RECVMSG", "RecvMsg");
define("TX_RECVADDR", "RecvAddr");
define("TX_RECVPOSTNUM", "RecvPostNum");
define("TX_TAXFREE", "TaxFree");
define("TX_TAX", "Tax");
//PaymentInfo
define("TX_PAYMETHOD", "PayMethod");
define("TX_JOINCARD", "JoinCard");
define("TX_JOINEXPIRE", "JoinExpire");
define("TX_MAILORDER", "MailOrder");
define("TX_SESSIONKEY", "SessionKey");
define("TX_ENCRYPTED", "Encrypted");
//ReservedInfo
define("TX_MRESERVED1", "MReserved1");
define("TX_MRESERVED2", "MReserved2");
define("TX_MRESERVED3", "MReserved3");
//ManageInfo
define("TX_LANGUAGE", "Language");
define("TX_URL", "URL");
define("TX_TXVERSION", "TXVersion");
define("TX_TXUSERIP", "TXUserIP");
define("TX_TXUSERID", "TXUserID");
define("TX_TXREGNUM", "TXRegNum");
define("TX_ACK", "Ack");
define("TX_RN", "TXRN");
//CancelInfo
define("TX_CANCELTID", "CancelTID");
define("TX_CANCELMSG", "CancelMsg");
define("TX_CANCELREASON", "CancelReason");      //2012-10-19 취소사유코드 추가
//가상계좌환불(09.08.05)
define("TX_REFUNDACCTNUM", "RefundAcctNum");
define("TX_REFUNDBANKCODE", "RefundBankCode");
define("TX_REFUNDACCTNAME", "RefundAcctName");
//PartCancelInfo
define("TX_PRTC_TID", "PRTC_TID");
define("TX_PRTC_PRICE", "PRTC_Price");
define("TX_PRTC_REMAINS", "PRTC_Remains");
define("TX_PRTC_QUOTA", "PRTC_Quota");
define("TX_PRTC_INTEREST", "PRTC_Interest");
define("TX_PRTC_TAX", "Tax");
define("TX_PRTC_TAXFREE", "TaxFree");

define("TX_PRTC_CURRENCY", "Currency");

//국민은행 I계좌이체 부분취소시 계좌번호/계좌주성명추가 2011-10-06
define("TX_PRTC_NOACCT", "PRTC_NoAcctFNBC");
define("TX_PRTC_NMACCT", "PRTC_NmAcctFNBC");
//가상계좌 부분환불 관련 추가 
define("TX_PRTC_REFUNDFLGREMIT", "PRTC_RefundFlgRemit");
define("TX_PRTC_REFUNDBANKCODE", "PRTC_RefundBankCode");
//CaptureInfo
define("TX_CAPTURETID", "CaptureTID");
//현금영수증
define("TX_CSHR_APPLPRICE", "CSHR_ApplPrice");
define("TX_CSHR_SUPPLYPRICE", "CSHR_SupplyPrice");
define("TX_CSHR_TAX", "CSHR_Tax");
define("TX_CSHR_SERVICEPRICE", "CSHR_ServicePrice");
define("TX_CSHR_REGNUM", "CSHR_RegNum");
define("TX_CSHR_TYPE", "CSHR_Type");
define("TX_CSHR_COMPANYNUM", "CSHR_CompanyNum");
define("TX_CSHR_OPENMARKET", "CSHR_OpenMarket");
define("TX_CSHR_SUBCNT", "CSHR_SubCnt");
define("TX_CSHR_SUBCOMPANYNAME1", "CSHR_SubCompanyName1");
define("TX_CSHR_SUBCOMPANYNUM1", "CSHR_SubCompanyNum1");
define("TX_CSHR_SUBREGNUM1", "CSHR_SubRegNum1");
define("TX_CSHR_SUBMID1", "CSHR_SubMID1");
define("TX_CSHR_SUBAPPLPRICE1", "CSHR_SubApplPrice1");
define("TX_CSHR_SUBSERVICEPRICE1", "CSHR_SubServicePrice1");
//거래조회(12.04.20)
define("TX_INQR_TID", "INQR_TID");
//서브몰하위가맹점등록(14.03.06)
define("TX_OPENREG_TID", "OrgTID");
define("TX_OPENREG_MID", "MID");
define("TX_OPENREG_SUBCNT", "SubCnt");
define("TX_OPENREG_SUBGOODS", "SubGoods");
define("TX_OPENREG_SUBCOMPNO", "SubCompNo");
define("TX_OPENREG_SUBCOMPNM", "SubCompNm");
define("TX_OPENREG_SUBPRSUPPLY", "SubPrSupply");
define("TX_OPENREG_SUBPRFREE", "SubPrFree");
define("TX_OPENREG_SUBPRTAX", "SubPrTax");
define("TX_OPENREG_SUBPRSERVICE", "SubPrService");
define("TX_OPENREG_SUBPRICE", "SubPrice");

//------------------------------------------------------
//
//응답전문
//
//------------------------------------------------------
//HEAD
define("NM_MID", "MID");
define("NM_TID", "TID");
define("NM_TOTPRICE", "TotPrice");

//BODY
define("NM_GOODSCNT", "GoodsCnt");
define("NM_MOID", "MOID");
define("NM_CURRENCY", "Currency");
define("NM_SMID", "SMID");
define("NM_GOODSNAME", "GoodsName");
define("NM_GOODSPRICE", "GoodsPrice");
define("NM_PAYMETHOD", "PayMethod");
define("NM_RESULTCODE", "ResultCode");
define("NM_RESULTERRORCODE", "ResultErrorCode");
define("NM_RESULTMSG", "ResultMsg");
define("NM_SESSIONKEY", "SessionKey");
define("NM_ENCRYPTED", "Encrypted");
define("NM_CANCELDATE", "CancelDate");
define("NM_CANCELTIME", "CancelTime");
define("NM_EVENTCODE", "EventCode");
define("NM_ORGCURRENCY", "OrgCurrency");
define("NM_ORGPRICE", "OrgPrice");
define("NM_EXCHANGERATE", "ExchangeRate");
define("NM_RESERVEDINFO", "ReservedInfo");
define("NM_MRESERVED1", "MReserved1");
define("NM_MRESERVED2", "MReserved2");
define("NM_MRESERVED3", "MReserved3");
define("PRTC_TID", "PRTC_TID");
define("PRTC_PRICE", "PRTC_Price");
define("PRTC_REMAINS", "PRTC_Remains");
define("PRTC_QUOTA", "PRTC_Quota");
define("PRTC_INTEREST", "PRTC_Interest");
define("PRTC_TYPE", "PRTC_Type");
define("PRTC_CNT", "PRTC_Cnt");
define("NM_CAPTUREDATE", "CaptureDate");
define("NM_CAPTURETIME", "CaptureTime");

define("NM_PGPUBKEY", "PGcertKey");

//RECV DATA XPATH
//XML XPATH
define("ROOTINFO", "INIpay");
define("GOODSINFO", "GoodsInfo");
define("GOODS", "Goods");
define("BUYERINFO", "BuyerInfo");
define("PAYMENTINFO", "PaymentInfo");
define("PAYMENT", "Payment");
define("MANAGEINFO", "ManageInfo");
define("RESERVEDINFO", "ReservedInfo");
//Cancel(NetCancel)
define("CANCELINFO", "CancelInfo");
//PartCancel Encrypt
define("PARTCANCELINFO", "PartCancelInfo");
//Capture
define("CAPTUREINFO", "CaptureInfo");
//거래조회(12.04.20)
define("INQUIRYINFO", "InquiryInfo");
//서브몰하위가맹점등록(14.03.06)
define("OPENSUBINFO", "OpenSubInfo");
//Escrow
//added 2008.01.09
define("ESCROWINFO", "EscrowInfo");
define("ESCROW_DELIVERY", "Delivery");
define("ESCROW_CONFIRM", "Confirm");
define("ESCROW_DENY", "Deny");
define("ESCROW_DENYCONFIRM", "DenyConfirm");


//------------------------------------------------------
//Auth Encrypt XPATH
//------------------------------------------------------
//CARD COMMON
define("APPLDATE", "ApplDate");
define("APPLTIME", "ApplTime");
define("APPLNUM", "ApplNum");
//CARD
define("CARD_NUM", "CARD_Num");
define("CARD_EXPIRE", "CARD_Expire");
define("CARD_CODE", "CARD_Code");
define("CARD_APPLPRICE", "CARD_ApplPrice");
define("CARD_BANKCODE", "CARD_BankCode");
define("CARD_QUOTA", "CARD_Quota");
define("CARD_INTEREST", "CARD_Interest");
define("CARD_POINT", "CARD_Point");
define("CARD_AUTHTYPE", "CARD_AuthType");
define("CARD_REGNUM", "CARD_RegNum");
define("CARD_APPLDATE", "CARD_ApplDate");
define("CARD_APPLTIME", "CARD_ApplTime");
define("CARD_APPLNUM", "CARD_ApplNum");
define("CARD_RESULTCODE", "CARD_ResultCode");
define("CARD_RESULTMSG", "CARD_ResultMsg");
define("CARD_TERMINALNUM", "CARD_TerminalNum");
define("CARD_MEMBERNUM", "CARD_MemberNum");
define("CARD_PURCHASECODE", "CARD_PurchaseCode");
//ISP
define("ISP_BANKCODE", "ISP_BankCode");
define("ISP_QUOTA", "ISP_Quota");
define("ISP_INTEREST", "ISP_Interest");
define("ISP_APPLPRICE", "ISP_ApplPrice");
define("ISP_CARDCODE", "ISP_CardCode");
define("ISP_CARDNUM", "ISP_CardNum");
define("ISP_POINT", "ISP_Point");
define("ISP_APPLDATE", "ISP_ApplDate");
define("ISP_APPLTIME", "ISP_ApplTime");
define("ISP_APPLNUM", "ISP_ApplNum");
define("ISP_RESULTCODE", "ISP_ResultCode");
define("ISP_RESULTMSG", "ISP_ResultMsg");
define("ISP_TERMINALNUM", "ISP_TerminalNum");
define("ISP_MEMBERNUM", "ISP_MemberNum");
define("ISP_PURCHASECODE", "ISP_PurchaseCode");
//ACCT
define("ACCT_APPLDATE", "ACCT_ApplDate");
define("ACCT_APPLTIME", "ACCT_ApplTime");
define("ACCT_APPLNUM", "ACCT_ApplNum");
//HPP
define("HPP_APPLDATE", "HPP_ApplDate");
define("HPP_APPLTIME", "HPP_ApplTime");
define("HPP_APPLNUM", "HPP_ApplNum");
//VACT
define("VACT_APPLDATE", "VACT_ApplDate");
define("VACT_APPLTIME", "VACT_ApplTime");
//CASH
define("CSHR_APPLDATE", "CSHR_ApplDate");
define("CSHR_APPLTIME", "CSHR_ApplTime");
define("CSHR_APPLNUM", "CSHR_ApplNum");
//ARSB
define("ARSB_APPLDATE", "ARSB_ApplDate");
define("ARSB_APPLTIME", "ARSB_ApplTime");
define("ARSB_APPLNUM", "ARSB_ApplNum");
//PHNB
define("PHNB_APPLDATE", "PHNB_ApplDate");
define("PHNB_APPLTIME", "PHNB_ApplTime");
define("PHNB_APPLNUM", "PHNB_ApplNum");
//CULT
define("CULT_APPLDATE", "CULT_ApplDate");
define("CULT_APPLTIME", "CULT_ApplTime");
define("CULT_APPLNUM", "CULT_ApplNum");
//DGCL
define("GAMG_CNT", "GAMG_Cnt");
define("GAMG_APPLDATE", "GAMG_ApplDate");
define("GAMG_APPLTIME", "GAMG_ApplTime");
define("GAMG_APPLNUM", "GAMG_ApplNum");

function MakePathGAMG($cnt) {
    for ($i = 1; $i <= $cnt; $i++) {
        define("GAMG_NUM$i", "GAMG_Num$i");
        define("GAMG_REMAINS$i", "GAMG_Remains$i");
        define("GAMG_ERRMSG$i", "GAMG_ErrMsg$i");
    }
}

//EDUG
define("EDUG_APPLDATE", "EDUG_ApplDate");
define("EDUG_APPLTIME", "EDUG_ApplTime");
define("EDUG_APPLNUM", "EDUG_ApplNum");
//TEEN
define("TEEN_APPLDATE", "TEEN_ApplDate");
define("TEEN_APPLTIME", "TEEN_ApplTime");
define("TEEN_APPLNUM", "TEEN_ApplNum");

//----------------------------------
//ERROR CODE
//----------------------------------
//!!신TX에 추가된 에러!!!
define("NULL_DIR_ERR", "TX9001");
define("NULL_TYPE_ERR", "TX9002");
define("NULL_NOINTEREST_ERR", "TX9003");
define("NULL_QUOTABASE_ERR", "TX9004");
define("DNS_LOOKUP_ERR", "TX9005");
define("MERCHANT_DB_ERR", "TX9006");
define("DNS_LOOKUP_TIMEOUT_ERR", "TX9007");
define("PGPUB_UPDATE_ERR", "TX9612");

//암복호화 에러
define("B64DECODE_UPDATE_ERR", "TX9101");
define("B64DECODE_FINAL_ERR", "TX9102");
define("B64DECODE_LENGTH_ERR", "TX9103");
define("GET_KEYPW_EVP_B2K_ERR", "TX9104");
define("GET_KEYPW_FILE_OPEN_ERR", "TX9105");
define("GET_KEYPW_FILE_READ_ERR", "TX9106");
define("GET_KEYPW_DECRYPT_INIT_ERR", "TX9107");
define("GET_KEYPW_DECRYPT_UPDATE_ERR", "TX9108");
define("GET_KEYPW_DECRYPT_FINAL_ERR", "TX9109");
define("ENC_RAND_BYTES_ERR", "TX9110");
define("ENC_INIT_ERR", "TX9111");
define("ENC_UPDATE_ERR", "TX9112");
define("ENC_FINAL_ERR", "TX9113");
define("ENC_RSA_ERR", "TX9114");
define("DEC_RSA_ERR", "TX9115");
define("DEC_CIPHER_ERR", "TX9116");
define("DEC_INIT_ERR", "TX9117");
define("DEC_UPDATE_ERR", "TX9118");
define("DEC_FINAL_ERR", "TX9119");
define("SIGN_FINAL_ERR", "TX9120");
define("SIGN_CHECK_ERR", "TX9121");
define("ENC_NULL_F_ERR", "TX9122");
define("ENC_INIT_RAND_ERR", "TX9123");
define("ENC_PUTENV_ERR", "TX9124");
//필드체크
define("NULL_KEYPW_ERR", "TX9201");
define("NULL_MID_ERR", "TX9202");
define("NULL_PGID_ERR", "TX9203");
define("NULL_TID_ERR", "TX9204");
define("NULL_UIP_ERR", "TX9205");
define("NULL_URL_ERR", "TX9206");
define("NULL_PRICE_ERR", "TX9207");
define("NULL_PRICE1_ERR", "TX9208");
define("NULL_PRICE2_ERR", "TX9209");
define("NULL_CARDNUMBER_ERR", "TX9210");
define("NULL_CARDEXPIRE_ERR", "TX9211");
define("NULL_ENCRYPTED_ERR", "TX9212");
define("NULL_CARDQUOTA_ERR", "TX9213");
define("NULL_QUOTAINTEREST_ERR", "TX9214");
define("NULL_AUTHENTIFICATION_ERR", "TX9215");
define("NULL_AUTHFIELD1_ERR", "TX9216");
define("NULL_AUTHFIELD2_ERR", "TX9217");
define("NULL_BANKCODE_ERR", "TX9218");
define("NULL_BANKACCOUNT_ERR", "TX9219");
define("NULL_REGNUMBER_ERR", "TX9220");
define("NULL_OCBCARDNUM_ERR", "TX9221");
define("NULL_OCBPASSWD_ERR", "TX9222");
define("NULL_PASSWD_ERR", "TX9223");
define("NULL_CURRENCY_ERR", "TX9224");
define("NULL_PAYMETHOD_ERR", "TX9225");
define("NULL_GOODNAME_ERR", "TX9226");
define("NULL_BUYERNAME_ERR", "TX9227");
define("NULL_BUYERTEL_ERR", "TX9228");
define("NULL_BUYEREMAIL_ERR", "TX9229");
define("NULL_SESSIONKEY_ERR", "TX9230");
//pg공개키 로드 오류
define("NULL_PGCERT_FP_ERR", "TX9231");
define("NULL_X509_ERR", "TX9232");
define("NULL_PGCERT_ERR", "TX9233");

define("RESULT_MSG_FORMAT_ERR", "TX9234");

// 가상 계좌 이체 예약
define("NULL_PERNO_ERR", "TX9235");  // 주민번호 빠짐
define("NULL_OID_ERR", "TX9236");  // 주문번호 빠짐
define("NULL_VCDBANK_ERR", "TX9237");  // 은행코드 빠짐
define("NULL_DTINPUT_ERR", "TX9238");  // 입금 예정일 빠짐
define("NULL_NMINPUT_ERR", "TX9239");  // 송금자 성명 빠짐
//실시간 빌링
define("NULL_BILLKEY_ERR", "TX9240");  // 빌키 빠짐
define("NULL_CARDPASS_ERR", "TX9241");  // 카드 비번 빠짐
define("NULL_BILLTYPE_ERR", "TX9242");  // 빌타입 누락
// CMS 계좌이체
define("NULL_PRICE_ORG_ERR", "TX9250"); // CMS 출금총금액 빠짐
define("NULL_CMSDAY_ERR", "TX9251"); // CMS 출금일자 빠짐
define("NULL_CMSDATEFROM_ERR", "TX9252"); // CMS 출금시작월 빠짐
define("NULL_CMSDATETO_ERR", "TX9253"); // CMS 출금종료월 빠짐
// 부분취소
define("NULL_CONFIRM_PRICE_ERR", "TX9260"); // 재승인 요청금액 누락 에러
// 현금영수증 발행
define("NULL_CR_PRICE_ERR", "TX9270"); // 현금결제 금액 빠짐
define("NULL_SUP_PRICE_ERR", "TX9271"); // 공급가액 빠짐
define("NULL_TAX_ERR", "TX9272"); // 부가세 빠짐
define("NULL_SRVC_PRICE_ERR", "TX9273"); // 봉사료 빠짐
define("NULL_REG_NUM_ERR", "TX9274");  // 주민번호(사업자번호)
define("NULL_USEOPT_ERR", "TX9275"); // 현금영수증 용도 구분자 빠짐

define("PRIVKEY_FILE_OPEN_ERR", "TX9301");
define("INVALID_KEYPASS_ERR", "TX9302");

define("MAKE_TID_ERR", "TX9401");
define("ACK_CHECKSUM_ERR", "TX9402");
define("NETCANCEL_SOCK_CREATE_ERR", "TX9403");
define("NETCANCEL_SOCK_SEND_ERR", "TX9404");
define("NETCANCEL_SOCK_RECV_ERR", "TX9405");
define("LOG_OPEN_ERR", "TX9406");
define("LOG_WRITE_ERR", "TX9407");

define("SOCK_MAKE_EP_ERR", "TX9501");
define("SOCK_CONN_ERR", "TX9502");
define("SOCK_SEND1_ERR", "TX9503");
define("SOCK_SEND2_ERR", "TX9504");
define("SOCK_CLOSED_BY_PEER_ERR", "TX9505");
define("SOCK_RECV1_ERR", "TX9506");
define("SOCK_RECV2_ERR", "TX9507");
define("SOCK_RECV_LEN_ERR", "TX9508");
define("SOCK_TIMEO_ERR", "TX9509");
define("SOCK_ETC1_ERR", "TX9510");
define("SOCK_ETC2_ERR", "TX9511");

define("NULL_ESCROWTYPE_ERR", "TX6000");
define("NULL_ESCROWMSG_ERR", "TX6001");

define("NULL_FIELD_REFUNDACCTNUM", "TX9245");
define("NULL_FIELD_REFUNDBANKCODE", "TX9243");
define("NULL_FIELD_REFUNDACCTNAME", "TX9244");
?>
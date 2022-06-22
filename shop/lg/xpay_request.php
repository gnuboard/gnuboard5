<?php
include_once('./_common.php');

// LG유플러스 공통 설정
require_once(G5_SHOP_PATH.'/settle_lg.inc.php');

/*
 * 1. 기본결제 인증요청 정보 변경
 *
 * 기본정보를 변경하여 주시기 바랍니다.(파라미터 전달시 POST를 사용하세요)
 */
$LGD_OID                    = isset($_POST['LGD_OID']) ? $_POST['LGD_OID'] : '';                //주문번호(상점정의 유니크한 주문번호를 입력하세요)
$LGD_AMOUNT                 = isset($_POST['LGD_AMOUNT']) ? $_POST['LGD_AMOUNT'] : 0;             //결제금액("," 를 제외한 결제금액을 입력하세요)
$LGD_TIMESTAMP              = isset($_POST['LGD_TIMESTAMP']) ? $_POST['LGD_TIMESTAMP'] : '';          //타임스탬프
$LGD_BUYER                  = isset($_POST['LGD_BUYER']) ? $_POST['LGD_BUYER'] : '';              //구매자명
$LGD_PRODUCTINFO            = isset($_POST['LGD_PRODUCTINFO']) ? $_POST['LGD_PRODUCTINFO'] : '';        //상품명
$LGD_BUYEREMAIL             = isset($_POST['LGD_BUYEREMAIL']) ? $_POST['LGD_BUYEREMAIL'] : '';         //구매자 이메일
$LGD_CUSTOM_FIRSTPAY        = isset($_POST['LGD_CUSTOM_FIRSTPAY']) ? $_POST['LGD_CUSTOM_FIRSTPAY'] : '';    //상점정의 초기결제수단
$LGD_CUSTOM_SKIN            = 'red';                            //상점정의 결제창 스킨
$LGD_CUSTOM_USABLEPAY       = isset($_POST['LGD_CUSTOM_USABLEPAY']) ? $_POST['LGD_CUSTOM_USABLEPAY'] : '';   //디폴트 결제수단 (해당 필드를 보내지 않으면 결제수단 선택 UI 가 노출됩니다.)
$LGD_WINDOW_VER             = '2.5';                            //결제창 버젼정보
$LGD_WINDOW_TYPE            = $LGD_WINDOW_TYPE;                 //결제창 호출방식 (수정불가)
$LGD_CUSTOM_SWITCHINGTYPE   = $LGD_CUSTOM_SWITCHINGTYPE;        //신용카드 카드사 인증 페이지 연동 방식 (수정불가)
$LGD_CUSTOM_PROCESSTYPE     = 'TWOTR';                          //수정불가

/*
 *************************************************
 * 2. MD5 해쉬암호화 (수정하지 마세요) - BEGIN
 *
 * MD5 해쉬암호화는 거래 위변조를 막기위한 방법입니다.
 *************************************************
 *
 * 해쉬 암호화 적용( LGD_MID + LGD_OID + LGD_AMOUNT + LGD_TIMESTAMP + LGD_MERTKEY )
 * LGD_MID          : 상점아이디
 * LGD_OID          : 주문번호
 * LGD_AMOUNT       : 금액
 * LGD_TIMESTAMP    : 타임스탬프
 * LGD_MERTKEY      : 상점MertKey (mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)
 *
 * MD5 해쉬데이터 암호화 검증을 위해
 * LG유플러스에서 발급한 상점키(MertKey)를 환경설정 파일(lgdacom/conf/mall.conf)에 반드시 입력하여 주시기 바랍니다.
 */

$xpay = new XPay($configPath, $CST_PLATFORM);

// Mert Key 설정
$xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
$xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);

$xpay->Init_TX($LGD_MID);
$LGD_HASHDATA = md5($LGD_MID.$LGD_OID.$LGD_AMOUNT.$LGD_TIMESTAMP.$xpay->config[$LGD_MID]);
/*
 *************************************************
 * 2. MD5 해쉬암호화 (수정하지 마세요) - END
 *************************************************
 */

$payReqMap['CST_PLATFORM']              = $CST_PLATFORM;                // 테스트, 서비스 구분
$payReqMap['LGD_WINDOW_TYPE']           = $LGD_WINDOW_TYPE;             // 수정불가
$payReqMap['CST_MID']                   = $CST_MID;                     // 상점아이디
$payReqMap['LGD_MID']                   = $LGD_MID;                     // 상점아이디
$payReqMap['LGD_OID']                   = $LGD_OID;                     // 주문번호
$payReqMap['LGD_BUYER']                 = $LGD_BUYER;                   // 구매자
$payReqMap['LGD_PRODUCTINFO']           = $LGD_PRODUCTINFO;             // 상품정보
$payReqMap['LGD_AMOUNT']                = $LGD_AMOUNT;                  // 결제금액
$payReqMap['LGD_BUYEREMAIL']            = $LGD_BUYEREMAIL;              // 구매자 이메일
$payReqMap['LGD_CUSTOM_SKIN']           = $LGD_CUSTOM_SKIN;             // 결제창 SKIN
$payReqMap['LGD_CUSTOM_PROCESSTYPE']    = $LGD_CUSTOM_PROCESSTYPE;      // 트랜잭션 처리방식
$payReqMap['LGD_TIMESTAMP']             = $LGD_TIMESTAMP;               // 타임스탬프
$payReqMap['LGD_HASHDATA']              = $LGD_HASHDATA;                // MD5 해쉬암호값
$payReqMap['LGD_RETURNURL']             = $LGD_RETURNURL;               // 응답수신페이지
$payReqMap['LGD_VERSION']               = $LGD_VERSION;                 // 버전정보 (삭제하지 마세요)
$payReqMap['LGD_CUSTOM_USABLEPAY']      = $LGD_CUSTOM_USABLEPAY;        // 디폴트 결제수단
$payReqMap['LGD_CUSTOM_SWITCHINGTYPE']  = $LGD_CUSTOM_SWITCHINGTYPE;    // 신용카드 카드사 인증 페이지 연동 방식
$payReqMap['LGD_WINDOW_VER']            = $LGD_WINDOW_VER;
$payReqMap['LGD_ENCODING']              = 'UTF-8';
$payReqMap['LGD_ENCODING_RETURNURL']    = 'UTF-8';


// 가상계좌(무통장) 결제연동을 하시는 경우  할당/입금 결과를 통보받기 위해 반드시 LGD_CASNOTEURL 정보를 LG 유플러스에 전송해야 합니다 .
$payReqMap['LGD_CASNOTEURL']            = $LGD_CASNOTEURL;              // 가상계좌 NOTEURL

//Return URL에서 인증 결과 수신 시 셋팅될 파라미터 입니다.*/
$payReqMap['LGD_RESPCODE']              = '';
$payReqMap['LGD_RESPMSG']               = '';
$payReqMap['LGD_PAYKEY']                = '';

$_SESSION['PAYREQ_MAP'] = $payReqMap;

die(json_encode(array('LGD_HASHDATA' => $LGD_HASHDATA, 'error' => '')));

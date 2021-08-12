<?php
include_once('./_common.php');
require_once(G5_MSHOP_PATH.'/settle_lg.inc.php');

/*
 * 1. 기본결제 인증요청 정보 변경
 *
 * 기본정보를 변경하여 주시기 바랍니다.(파라미터 전달시 POST를 사용하세요)
 */
$LGD_OID                    = $_POST['LGD_OID'];                //주문번호(상점정의 유니크한 주문번호를 입력하세요)
$LGD_AMOUNT                 = $_POST['LGD_AMOUNT'];             //결제금액("," 를 제외한 결제금액을 입력하세요)
$LGD_BUYER                  = $_POST['LGD_BUYER'];              //구매자명
$LGD_PRODUCTINFO            = $_POST['LGD_PRODUCTINFO'];        //상품명
$LGD_BUYEREMAIL             = $_POST['LGD_BUYEREMAIL'];         //구매자 이메일
$LGD_CUSTOM_FIRSTPAY        = $_POST['LGD_CUSTOM_FIRSTPAY'];    //상점정의 초기결제수단
$LGD_TAXFREEAMOUNT          = $_POST['LGD_TAXFREEAMOUNT'];      //비과세 금액
$LGD_CASHRECEIPTYN          = $_POST['LGD_CASHRECEIPTYN'];      //현금영수증 사용설정
$LGD_BUYERID                = $_POST['LGD_BUYERID'];            //구매자 ID
$LGD_BUYERPHONE             = $_POST['LGD_BUYERPHONE'];         //구매자 휴대폰번호
$LGD_EASYPAY_ONLY           = $_POST['LGD_EASYPAY_ONLY'];       //페이나우결제창 호출

$LGD_RETURNURL			    = G5_MSHOP_URL.'/lg/returnurl.php';
$LGD_KVPMISPNOTEURL         = G5_MSHOP_URL.'/lg/note_url.php';
$LGD_KVPMISPWAPURL		    = G5_MSHOP_URL.'/lg/mispwapurl.php?LGD_OID='.$LGD_OID;   //ISP 카드 결제시, URL 대신 앱명 입력시, 앱호출함
$LGD_KVPMISPCANCELURL       = G5_MSHOP_URL.'/lg/cancel_url.php';
$LGD_MTRANSFERWAPURL        = G5_MSHOP_URL.'/lg/returnurl.php';
$LGD_MTRANSFERCANCELURL     = G5_MSHOP_URL.'/lg/cancel_url.php';
$LGD_MTRANSFERNOTEURL       = G5_MSHOP_URL.'/lg/note_url.php';
if(preg_match('/iPhone|iPad/', $_SERVER['HTTP_USER_AGENT']))
    $LGD_MTRANSFERAUTOAPPYN     = 'N';
else
    $LGD_MTRANSFERAUTOAPPYN     = 'A';

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
$LGD_CUSTOM_PROCESSTYPE = "TWOTR";
/*
 *************************************************
 * 2. MD5 해쉬암호화 (수정하지 마세요) - END
 *************************************************
 */
$CST_WINDOW_TYPE = "submit";                                       // 수정불가
$payReqMap['CST_PLATFORM']           = $CST_PLATFORM;              // 테스트, 서비스 구분
$payReqMap['CST_WINDOW_TYPE']        = $CST_WINDOW_TYPE;           // 수정불가
$payReqMap['CST_MID']                = $CST_MID;                   // 상점아이디
$payReqMap['LGD_MID']                = $LGD_MID;                   // 상점아이디
$payReqMap['LGD_OID']                = $LGD_OID;                   // 주문번호
$payReqMap['LGD_BUYER']              = $LGD_BUYER;                 // 구매자
$payReqMap['LGD_PRODUCTINFO']        = $LGD_PRODUCTINFO;           // 상품정보
$payReqMap['LGD_AMOUNT']             = $LGD_AMOUNT;                // 결제금액
$payReqMap['LGD_BUYEREMAIL']         = $LGD_BUYEREMAIL;            // 구매자 이메일
$payReqMap['LGD_CUSTOM_SKIN']        = $LGD_CUSTOM_SKIN;           // 결제창 SKIN
$payReqMap['LGD_CUSTOM_PROCESSTYPE'] = $LGD_CUSTOM_PROCESSTYPE;    // 트랜잭션 처리방식
$payReqMap['LGD_TIMESTAMP']          = $LGD_TIMESTAMP;             // 타임스탬프
$payReqMap['LGD_HASHDATA']           = $LGD_HASHDATA;              // MD5 해쉬암호값
$payReqMap['LGD_RETURNURL']          = $LGD_RETURNURL;             // 응답수신페이지
$payReqMap['LGD_VERSION']            = "PHP_SmartXPay_1.0";        // 버전정보 (삭제하지 마세요)
$payReqMap['LGD_CUSTOM_FIRSTPAY']    = $LGD_CUSTOM_FIRSTPAY;       // 디폴트 결제수단
$payReqMap['LGD_CUSTOM_SWITCHINGTYPE']  = "SUBMIT";        // 신용카드 카드사 인증 페이지 연동 방식
$payReqMap['LGD_ENCODING']           = "UTF-8";
$payReqMap['LGD_ENCODING_NOTEURL']   = "UTF-8";
$payReqMap['LGD_ENCODING_RETURNURL'] = "UTF-8";
$payReqMap['LGD_TAXFREEAMOUNT']      = $LGD_TAXFREEAMOUNT;
$payReqMap['LGD_CASHRECEIPTYN']      = $LGD_CASHRECEIPTYN;
$payReqMap['LGD_BUYERPHONE']         = $LGD_BUYERPHONE;
$payReqMap['LGD_BUYERID']            = $LGD_BUYERID;
$payReqMap['LGD_EASYPAY_ONLY']       = $LGD_EASYPAY_ONLY;

/*
****************************************************
* 안드로이드폰 신용카드 ISP(국민/BC)결제에만 적용 (시작)*
****************************************************

(주의)LGD_CUSTOM_ROLLBACK 의 값을  "Y"로 넘길 경우, LG U+ 전자결제에서 보낸 ISP(국민/비씨) 승인정보를 고객서버의 note_url에서 수신시  "OK" 리턴이 안되면  해당 트랜잭션은  무조건 롤백(자동취소)처리되고,
LGD_CUSTOM_ROLLBACK 의 값 을 "C"로 넘길 경우, 고객서버의 note_url에서 "ROLLBACK" 리턴이 될 때만 해당 트랜잭션은  롤백처리되며  그외의 값이 리턴되면 정상 승인완료 처리됩니다.
만일, LGD_CUSTOM_ROLLBACK 의 값이 "N" 이거나 null 인 경우, 고객서버의 note_url에서  "OK" 리턴이  안될시, "OK" 리턴이 될 때까지 3분간격으로 2시간동안  승인결과를 재전송합니다.
*/

$payReqMap['LGD_CUSTOM_ROLLBACK']    = "";                             // 비동기 ISP에서 트랜잭션 처리여부
$payReqMap['LGD_KVPMISPNOTEURL']     = $LGD_KVPMISPNOTEURL;            // 비동기 ISP(ex. 안드로이드) 승인결과를 받는 URL
$payReqMap['LGD_KVPMISPWAPURL']      = $LGD_KVPMISPWAPURL;             // 비동기 ISP(ex. 안드로이드) 승인완료후 사용자에게 보여지는 승인완료 URL
$payReqMap['LGD_KVPMISPCANCELURL']   = $LGD_KVPMISPCANCELURL;          // ISP 앱에서 취소시 사용자에게 보여지는 취소 URL

/*
****************************************************
* 안드로이드폰 신용카드 ISP(국민/BC)결제에만 적용    (끝) *
****************************************************
*/

// 안드로이드 에서 신용카드 적용  ISP(국민/BC)결제에만 적용 (선택)
// $payReqMap['LGD_KVPMISPAUTOAPPYN'] = "Y";
// Y: 안드로이드에서 ISP신용카드 결제시, 고객사에서 'App To App' 방식으로 국민, BC카드사에서 받은 결제 승인을 받고 고객사의 앱을 실행하고자 할때 사용

// 가상계좌(무통장) 결제연동을 하시는 경우  할당/입금 결과를 통보받기 위해 반드시 LGD_CASNOTEURL 정보를 LG 유플러스에 전송해야 합니다 .
$payReqMap['LGD_CASNOTEURL'] = $LGD_CASNOTEURL;               // 가상계좌 NOTEURL

// 계좌이체 파라미터
//$payReqMap['LGD_MTRANSFERWAPURL']    = $LGD_MTRANSFERWAPURL;
//$payReqMap['LGD_MTRANSFERCANCELURL'] = $LGD_MTRANSFERCANCELURL;
$payReqMap['LGD_MTRANSFERNOTEURL']   = $LGD_MTRANSFERNOTEURL;
$payReqMap['LGD_MTRANSFERAUTOAPPYN'] = $LGD_MTRANSFERAUTOAPPYN;

//Return URL에서 인증 결과 수신 시 셋팅될 파라미터 입니다.*/
$payReqMap['LGD_RESPCODE']           = "";
$payReqMap['LGD_RESPMSG']            = "";
$payReqMap['LGD_PAYKEY']             = "";

$_SESSION['PAYREQ_MAP'] = $payReqMap;

$g5['title'] = '토스페이먼츠 eCredit서비스 결제';
$g5['body_script'] = ' onload="launchCrossPlatform();"';
include_once(G5_PATH.'/head.sub.php');
?>

<?php if ($default['de_card_test']) {   // 테스트 결제시 ?>
<script language="javascript" src="https://pretest.uplus.co.kr:9443/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
<?php } else {      //실 결제시 ?>
<script language="javascript" src="//xpay.uplus.co.kr/xpay/js/xpay_crossplatform.js" type="text/javascript"></script>
<?php } ?>

<script type="text/javascript">

/*
* iframe으로 결제창을 호출하시기를 원하시면 iframe으로 설정 (변수명 수정 불가)
*/
    var LGD_window_type = '<?php echo $CST_WINDOW_TYPE; ?>';
/*
* 수정불가
*/
function launchCrossPlatform(){
    lgdwin = open_paymentwindow(document.getElementById('LGD_PAYINFO'), '<?php echo $CST_PLATFORM; ?>', LGD_window_type);
}
/*
* FORM 명만  수정 가능
*/
function getFormObject() {
    return document.getElementById("LGD_PAYINFO");
}

</script>
<form method="post" name="LGD_PAYINFO" id="LGD_PAYINFO" action="">
<?php
  foreach ($payReqMap as $key => $value) {
    echo'"<input type="hidden" name="'.$key.'" id="'.$key.'" value="'.$value.'">';
  }
?>
</form>

<?php
include_once(G5_PATH.'/tail.sub.php');
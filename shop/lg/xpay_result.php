<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// LG유플러스 공통 설정
require_once(G5_SHOP_PATH.'/settle_lg.inc.php');

/*
 * [최종결제요청 페이지(STEP2-2)]
 *
 * LG유플러스으로 부터 내려받은 LGD_PAYKEY(인증Key)를 가지고 최종 결제요청.(파라미터 전달시 POST를 사용하세요)
 */

/* ※ 중요
* 환경설정 파일의 경우 반드시 외부에서 접근이 가능한 경로에 두시면 안됩니다.
* 해당 환경파일이 외부에 노출이 되는 경우 해킹의 위험이 존재하므로 반드시 외부에서 접근이 불가능한 경로에 두시기 바랍니다.
* 예) [Window 계열] C:\inetpub\wwwroot\lgdacom ==> 절대불가(웹 디렉토리)
*/

/*
 *************************************************
 * 1.최종결제 요청 - BEGIN
 *  (단, 최종 금액체크를 원하시는 경우 금액체크 부분 주석을 제거 하시면 됩니다.)
 *************************************************
 */
$LGD_PAYKEY                 = isset($_POST['LGD_PAYKEY']) ? $_POST['LGD_PAYKEY'] : '';

$xpay = new XPay($configPath, $CST_PLATFORM);

// Mert Key 설정
$xpay->set_config_value('t'.$LGD_MID, $config['cf_lg_mert_key']);
$xpay->set_config_value($LGD_MID, $config['cf_lg_mert_key']);

$xpay->Init_TX($LGD_MID);

$xpay->Set('LGD_TXNAME', 'PaymentByKey');
$xpay->Set('LGD_PAYKEY', $LGD_PAYKEY);

//금액을 체크하시기 원하는 경우 아래 주석을 풀어서 이용하십시요.
//$DB_AMOUNT = "DB나 세션에서 가져온 금액"; //반드시 위변조가 불가능한 곳(DB나 세션)에서 금액을 가져오십시요.
//$xpay->Set('LGD_AMOUNTCHECKYN', 'Y');
//$xpay->Set('LGD_AMOUNT', $DB_AMOUNT);

/*
 *************************************************
 * 1.최종결제 요청(수정하지 마세요) - END
 *************************************************
 */

/*
 * 2. 최종결제 요청 결과처리
 *
 * 최종 결제요청 결과 리턴 파라미터는 연동메뉴얼을 참고하시기 바랍니다.
 */
if ($xpay->TX()) {
    //1)결제결과 화면처리(성공,실패 결과 처리를 하시기 바랍니다.)
    /*
    echo "결제요청이 완료되었습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

    echo "거래번호 : " . $xpay->Response("LGD_TID",0) . "<br>";
    echo "상점아이디 : " . $xpay->Response("LGD_MID",0) . "<br>";
    echo "상점주문번호 : " . $xpay->Response("LGD_OID",0) . "<br>";
    echo "결제금액 : " . $xpay->Response("LGD_AMOUNT",0) . "<br>";
    echo "결과코드 : " . $xpay->Response("LGD_RESPCODE",0) . "<br>";
    echo "결과메세지 : " . $xpay->Response("LGD_RESPMSG",0) . "<p>";

    $keys = $xpay->Response_Names();
    foreach($keys as $name) {
        echo $name . " = " . $xpay->Response($name, 0) . "<br>";
    }

    echo "<p>";
    exit;
    */

    if( '0000' == $xpay->Response_Code() ) {
        //최종결제요청 결과 성공 DB처리
        $tno             = $xpay->Response('LGD_TID',0);
        $amount          = $xpay->Response('LGD_AMOUNT',0);
        $app_time        = $xpay->Response('LGD_PAYDATE',0);
        $bank_name = $bankname = $xpay->Response('LGD_FINANCENAME',0);
        $depositor       = $xpay->Response('LGD_PAYER',0);
        $account         = $xpay->Response('LGD_FINANCENAME',0).' '.$xpay->Response('LGD_ACCOUNTNUM',0).' '.$xpay->Response('LGD_SAOWNER',0);
        $commid          = $xpay->Response('LGD_FINANCENAME',0);
        $mobile_no       = $xpay->Response('LGD_TELNO',0);
        $app_no = $od_app_no = $xpay->Response('LGD_FINANCEAUTHNUM',0);
        $card_name       = $xpay->Response('LGD_FINANCENAME',0);
        $pay_type        = $xpay->Response('LGD_PAYTYPE',0);
        $escw_yn         = $xpay->Response('LGD_ESCROWYN',0);
    } else {
        //최종결제요청 결과 실패 DB처리
        //echo "최종결제요청 결과 실패 DB처리하시기 바랍니다.<br>";

        if(G5_IS_MOBILE) {
            if(isset($_POST['pp_id']) && $_POST['pp_id']) {
                $page_return_url = G5_SHOP_URL.'/personalpayform.php?pp_id='.get_session('ss_personalpay_id');
            } else {
                $page_return_url = G5_SHOP_URL.'/orderform.php';
                if(get_session('ss_direct'))
                    $page_return_url .= '?sw_direct=1';
            }

            alert($xpay->Response_Msg().' 코드 : '.$xpay->Response_Code(), $page_return_url);
        } else {
            alert($xpay->Response_Msg().' 코드 : '.$xpay->Response_Code());
        }
    }
} else {
    //2)API 요청실패 화면처리
    /*
    echo "결제요청이 실패하였습니다.  <br>";
    echo "TX Response_code = " . $xpay->Response_Code() . "<br>";
    echo "TX Response_msg = " . $xpay->Response_Msg() . "<p>";

    //최종결제요청 결과 실패 DB처리
    echo "최종결제요청 결과 실패 DB처리하시기 바랍니다.<br>";
    */

    alert($xpay->Response_Msg().' 코드 : '.$xpay->Response_Code());
}
<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

@header('Progma:no-cache');
@header('Cache-Control:no-cache,must-revalidate');

include_once G5_SUBSCRIPTION_PATH.'/inicis/libs/INIStdPayUtil.php';
include_once G5_SUBSCRIPTION_PATH.'/inicis/libs/HttpClient.php';
include_once G5_SUBSCRIPTION_PATH.'/inicis/libs/properties.php';

$util = new INIStdPayUtil();
$prop = new properties();

$inicis_pay_result = false;

try {
    // #############################
    // 인증결과 파라미터 수신
    // #############################

    if (strcmp('0000', $resultCode) == 0) {
        // ############################################
        // 1.전문 필드 값 설정(***가맹점 개발수정***)
        // ############################################

        $timestamp = $util->getTimestamp();
        $charset = 'UTF-8';
        $format = 'JSON';

        // ##########################################################################
        // 승인요청 API url (authUrl) 리스트 는 properties 에 세팅하여 사용합니다.
        // idc_name 으로 수신 받은 센터 네임을 properties 에서 include 하여 승인요청하시면 됩니다.
        // ##########################################################################
        $idc_name = $_REQUEST['idc_name'];
        $authUrl = $prop->getAuthUrl($idc_name);

        if (strcmp($authUrl, $authUrl) == 0) {
            // #####################
            // 2.signature 생성
            // #####################
            $signParam['authToken'] = $authToken;   // 필수
            $signParam['timestamp'] = $timestamp;   // 필수
            // signature 데이터 생성 (모듈에서 자동으로 signParam을 알파벳 순으로 정렬후 NVP 방식으로 나열해 hash)
            $signature = $util->makeSignature($signParam);

            $veriParam['authToken'] = $authToken;   // 필수
            $veriParam['signKey'] = $signKey;     // 필수
            $veriParam['timestamp'] = $timestamp;   // 필수
            // verification 데이터 생성 (모듈에서 자동으로 signParam을 알파벳 순으로 정렬후 NVP 방식으로 나열해 hash)
            $verification = $util->makeSignature($veriParam);

            // #####################
            // 3.API 요청 전문 생성
            // #####################
            $authMap['mid'] = $mid;            // 필수
            $authMap['authToken'] = $authToken;      // 필수
            $authMap['signature'] = $signature;      // 필수
            $authMap['verification'] = $verification;   // 필수
            $authMap['timestamp'] = $timestamp;      // 필수
            $authMap['charset'] = $charset;        // default=UTF-8
            $authMap['format'] = $format;         // default=XML

            try {
                $httpUtil = new HttpClient();

                // #####################
                // 4.API 통신 시작
                // #####################

                $authResultString = '';
                if ($httpUtil->processHTTP($authUrl, $authMap)) {
                    $authResultString = $httpUtil->body;
                } else {
                    echo "Http Connect Error\n";
                    echo $httpUtil->errormsg;

                    throw new Exception('Http Connect Error');
                }

                // ############################################################
                // 5.API 통신결과 처리(***가맹점 개발수정***)
                // ############################################################

                $resultMap = json_decode($authResultString, true);
                
                /*************************  결제보안 추가 2016-05-18 START ****************************/
                $secureMap = array(
                    'mid' => $mid,
                    'tstamp' => $timestamp,
                    'MOID' => $resultMap['MOID'],
                    'TotPrice' => $resultMap['TotPrice'],
                );
                
                run_event('subscription_inicis_bill_result', $resultMap, $secureMap);
                
                // signature 데이터 생성
                $secureSignature = $util->makeSignatureAuth($secureMap);
                /*************************  결제보안 추가 2016-05-18 END ****************************/

                if ((strcmp('0000', $resultMap['resultCode']) == 0) && (strcmp($secureSignature, $resultMap['authSignature']) == 0)) { // 결제보안 추가 2016-05-18
                    $inicis_pay_result = true;

                    /*                         * ***************************************************************************
                    * 여기에 가맹점 내부 DB에 결제 결과를 반영하는 관련 프로그램 코드를 구현한다.

                    [중요!] 승인내용에 이상이 없음을 확인한 뒤 가맹점 DB에 해당건이 정상처리 되었음을 반영함
                    처리중 에러 발생시 망취소를 한다.
                    * **************************************************************************** */

                    $tno = $resultMap['tid'];
                    $amount = $resultMap['TotPrice'];
                    $app_time = $resultMap['applDate'].$resultMap['applTime'];
                    $pay_method = $resultMap['payMethod'];

                    // 결제된 카드요금
                    $CARD_ApplPrice = $resultMap['CARD_ApplPrice'];
                    $CARD_GWCode = $resultMap['CARD_GWCode'];
                    $CARD_BillKey = $resultMap['CARD_BillKey'];
                    $CARD_AuthType = $resultMap['CARD_AuthType'];
                    $payDevice = $resultMap['payDevice'];
                    $CARD_Interest = $resultMap['CARD_Interest'];
                    $payMethodDetail = $resultMap['payMethodDetail'];
                    
                    // 카드 코드
                    $card_code = isset($resultMap['CARD_Code']) ? $resultMap['CARD_Code'] : '';

                    // 마스킹 된 카드번호 : 숫자6자리 마스킹* 9자리 끝자리숫자1 자리 이렇게 마스킹 되어 넘겨 받는다. 
                    $card_mask_number = $resultMap['CARD_Num'];
                    $card_billkey = $CARD_BillKey;
                    
                    // 카드이름의 경우 
                    $card_name = ($card_code && isset($CARD_CODE[$card_code])) ? $CARD_CODE[$card_code] : $card_code;
                    
                } else {
                    $page_return_url = G5_SUBSCRIPTION_URL.'/orderform.php';
                    if (get_session('subs_direct')) {
                        $page_return_url .= '?sw_direct=1';
                    }

                    $s = '(오류코드:'.$resultMap['resultCode'].') '.$resultMap['resultMsg'];
                    alert($s, $page_return_url);
                }
            } catch (Exception $e) {
                //    $s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
                // ####################################
                // 실패시 처리(***가맹점 개발수정***)
                // ####################################
                // ---- db 저장 실패시 등 예외처리----//
                $s = $e->getMessage().' (오류코드:'.$e->getCode().')';
                echo $s;

                // #####################
                // 망취소 API
                // #####################

                $netcancelResultString = ''; // 망취소 요청 API url(고정, 임의 세팅 금지)
                $netCancel = $prop->getNetCancel($idc_name);

                if (strcmp($netCancel, $_REQUEST['netCancelUrl']) == 0) {
                    if ($httpUtil->processHTTP($netCancel, $authMap)) {
                        $netcancelResultString = $httpUtil->body;
                    } else {
                        echo "Http Connect Error\n";
                        echo $httpUtil->errormsg;

                        throw new Exception('Http Connect Error');
                    }

                    echo '<br/>## 망취소 API 결과 ##<br/>';

                    /* ##XML output## */
                    // $netcancelResultString = str_replace("<", "&lt;", $$netcancelResultString);
                    // $netcancelResultString = str_replace(">", "&gt;", $$netcancelResultString);

                    // 취소 결과 확인
                    echo '<p>'.$netcancelResultString.'</p>';
                }
            }
        } else {
            echo "authUrl check Fail\n";
        }
    } else {
    }
} catch (Exception $e) {
    $s = $e->getMessage().' (오류코드:'.$e->getCode().')';
    echo $s;
}

if (!$inicis_pay_result) {
    exit('<br><br>결제 에러가 일어났습니다. 에러 이유는 위와 같습니다.');
}

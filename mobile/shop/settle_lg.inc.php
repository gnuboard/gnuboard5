<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

require_once(G5_LGXPAY_PATH.'/lgdacom/XPayClient.php');

class XPay extends XPayClient
{
    public function set_config_value($key, $val)
    {
        $this->config[$key] = $val;
    }
}

/*
 * 1. 기본결제 인증요청 정보 변경
 *
 * 기본정보를 변경하여 주시기 바랍니다.(파라미터 전달시 POST를 사용하세요)
 */
$CST_PLATFORM           = $default['de_card_test'] ? 'test' : 'service';    //LG유플러스 결제 서비스 선택(test:테스트, service:서비스)
$CST_MID                = 'si_'.$config['cf_lg_mid'];                       //상점아이디(LG유플러스으로 부터 발급받으신 상점아이디를 입력하세요)
                                                                        //테스트 아이디는 't'를 반드시 제외하고 입력하세요.
$LGD_MID                = (('test' == $CST_PLATFORM) ? 't' : '').$CST_MID;  //상점아이디(자동생성)
$LGD_TIMESTAMP          = date('YmdHis');                                   //타임스탬프
$LGD_BUYERIP            = $_SERVER['REMOTE_ADDR'];                          //구매자IP
$LGD_BUYERID            = '';                                               //구매자ID
$LGD_CUSTOM_SKIN        = 'SMART_XPAY2';                                    //상점정의 결제창 스킨 (red, purple, yellow)
$LGD_MERTKEY            = '';                                               //상점MertKey(mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)

$configPath             = G5_LGXPAY_PATH.'/lgdacom';                               //LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf") 위치 지정.

/*
 * 가상계좌(무통장) 결제 연동을 하시는 경우 아래 LGD_CASNOTEURL 을 설정하여 주시기 바랍니다.
 */
$LGD_CASNOTEURL         = G5_SHOP_URL.'/settle_lg_common.php';
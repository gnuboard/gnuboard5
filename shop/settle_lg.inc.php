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
$LGD_CUSTOM_SKIN        = 'red';                                            //상점정의 결제창 스킨 (red, purple, yellow)
$LGD_WINDOW_VER         = '2.5';                                            //결제창 버젼정보
$LGD_MERTKEY            = '';                                               //상점MertKey(mertkey는 상점관리자 -> 계약정보 -> 상점정보관리에서 확인하실수 있습니다)
$LGD_WINDOW_TYPE        = 'iframe';                                         //결제창 호출 방식
$LGD_CUSTOM_SWITCHINGTYPE = 'IFRAME';                                       //신용카드 카드사 인증 페이지 연동 방식
$LGD_RETURNURL          = G5_SHOP_URL.'/lg/returnurl.php';                  //LGD_RETURNURL 을 설정하여 주시기 바랍니다. 반드시 현재 페이지와 동일한 프로트콜 및  호스트이어야 합니다. 아래 부분을 반드시 수정하십시요.
$LGD_VERSION            = 'PHP_Non-ActiveX_Standard';                       // 버전정보 (삭제하지 마세요)

// 결제가능 수단
$useablepay = array();
$LGD_CUSTOM_USABLEPAY = '';
if($default['de_iche_use'])
    $useablepay[] = 'SC0030';
if($default['de_vbank_use'])
    $useablepay[] = 'SC0040';
if($default['de_card_use'])
    $useablepay[] = 'SC0010';
if($default['de_hp_use'])
    $useablepay[] = 'SC0060';
if(count($useablepay) > 0)
    $LGD_CUSTOM_USABLEPAY = implode("-", $useablepay);

$configPath             = G5_LGXPAY_PATH.'/lgdacom';                               //LG유플러스에서 제공한 환경파일("/conf/lgdacom.conf") 위치 지정.

/*
 * 가상계좌(무통장) 결제 연동을 하시는 경우 아래 LGD_CASNOTEURL 을 설정하여 주시기 바랍니다.
 */
$LGD_CASNOTEURL     = G5_SHOP_URL.'/settle_lg_common.php';
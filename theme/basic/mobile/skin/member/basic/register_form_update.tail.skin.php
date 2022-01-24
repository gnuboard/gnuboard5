<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

//----------------------------------------------------------
// SMS 문자전송 시작
//----------------------------------------------------------

$sms_contents = $default['de_sms_cont1'];
$sms_contents = str_replace("{이름}", $mb_name, $sms_contents);
$sms_contents = str_replace("{회원아이디}", $mb_id, $sms_contents);
$sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);

// 핸드폰번호에서 숫자만 취한다
//$receive_number = preg_replace("/[^0-9]/", "", $mb_hp);  // 수신자번호 (회원님의 핸드폰번호)
$receive_number = '01075998385';
$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호

//popbill 데이터 추가
$send_name = $default['de_admin_company_name'];
$receive_name = $od['od_name'];
//popbill 문자메시지 전송에 필요함
$Messages[] = array(
    'snd'   => $send_number,	    // 발신번호
    'sndnm' => $send_name,		    // 발신자명
    'rcv'   => $receive_number,	    // 수신번호
    'rcvnm' => $receive_name,		// 수신자성명
    'msg'	=> $sms_contents	    // 개별 메시지 내용
    );

if ($w == "" && $default['de_sms_use1'] && $receive_number)
{
	if($config['cf_sms_type'] == 'LMS') {
        if($config['cf_sms_use']=='icode'){
            include_once(G5_LIB_PATH.'/icode.lms.lib.php');
            $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);
            // SMS 모듈 클래스 생성
            if($port_setting !== false) {
                $SMS = new LMS;
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                $strDest     = array();
                $strDest[]   = $receive_number;
                $strCallBack = $send_number;
                $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                $strSubject  = '';
                $strURL      = '';
                $strData     = iconv_euckr($sms_contents);
                $strDate     = '';
                $nCount      = count($strDest);

                $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                $SMS->Send();
                $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }
        }elseif($config['cf_sms_use']=='popbill'){
            include_once (G5_ADMIN_PATH.'/popbill/popbill_config.php');
                           
                $recv_number = $Messages[$s]['rcv'];
                $send_number = $Messages[$s]['snd'];
                $sms_contents = $Messages[$s]['msg'];
                $send_name = $Messages[$s]['rcvnm'];
                try {
                    $receiptNum = $MessagingService->SendLMS($CorpNum, $send_number, '', $sms_contents, $Messages, $reserveDT, $adsYN, $LinkID, $send_name, '', $requestNum);
                }
                catch (PopbillException $pe) {
                    $code = $pe->getCode();
                    $message = $pe->getMessage();
                }
           
        }
    } else {
        if($config['cf_sms_use']=='icode'){
            $SMS = new SMS; // SMS 연결
            $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
            
            $SMS->Add($receive_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_contents)), "");
            $SMS->Send();
            $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
        }elseif($config['cf_sms_use']=='popbill'){
            include_once (G5_ADMIN_PATH.'/popbill/popbill_config.php');
            
            $recv_number = $Messages[$s]['rcv'];
            $send_number = $Messages[$s]['snd'];
            $sms_contents = $Messages[$s]['msg'];
            $send_name = $Messages[$s]['rcvnm']; 
            try {
                $receiptNum = $MessagingService->SendSMS($CorpNum, $send_number, $sms_contents, $Messages, $reserveDT, $adsYN, $LinkID, $pop_snd_name, '', $requestNum);
            }
            catch (PopbillException $pe) {
                $code = $pe->getCode();
                $message = $pe->getMessage();
            }
        }              
    }
}

//----------------------------------------------------------
// SMS 문자전송 끝
//----------------------------------------------------------;
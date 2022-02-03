<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
if (!defined("_ORDERSMS_")) exit;

$recv_number = preg_replace("/[^0-9]/", "", $od_hp);	// 수신자번호 (받는사람 핸드폰번호 ... 여기서는 주문자님의 핸드폰번호임)
$send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호
//popbill 데이터 추가
$send_name = $default['de_admin_company_name'];
$recv_name = $od_name;



if ($config['cf_sms_use']) {
    $sms_messages = array();

    if ($od_sms_ipgum_check && $default['de_sms_use4'])
    {
        if ($od_bank_account && $od_receipt_price && $od_deposit_name)
        {
            $sms_contents = $default['de_sms_cont4'];
            $sms_contents = str_replace("{이름}", $od_name, $sms_contents);
            $sms_contents = str_replace("{입금액}", number_format($od_receipt_price), $sms_contents);
            $sms_contents = str_replace("{주문번호}", $od_id, $sms_contents);
            $sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);
            
            //icode, popbill 같이 사용하도록 배열의 이름들 수정
            if($recv_number)
            //팝빌에서 제공한 sms, lms 전송하는 메소드에서는 수신자의 번호와 이름을 아래의 배열에서 추출함.
                $sms_messages[] = array('rcv'  => $recv_number,        //수신자번호
                                        'snd'  => $send_number,        //발신자번호
                                        'msg'  => $sms_contents,       //개별메시지 내용
                                        'sndnm' => $send_name,         //발신자이름
                                        'rcvnm' => $recv_name,         //수신자이름
                                        'sjt'	=> ''	               //LMS 제목
                                    );   
        
        }
    }

    if ($od_sms_baesong_check && $default['de_sms_use5'])
    {
        if ($od_delivery_company && $od_invoice)
        {
            $sms_contents = $default['de_sms_cont5'];
            $sms_contents = str_replace("{이름}", $od_name, $sms_contents);
            $sms_contents = str_replace("{택배회사}", $od_delivery_company, $sms_contents);
            $sms_contents = str_replace("{운송장번호}", $od_invoice, $sms_contents);
            $sms_contents = str_replace("{주문번호}", $od_id, $sms_contents);
            $sms_contents = str_replace("{회사명}", $default['de_admin_company_name'], $sms_contents);

            //icode, popbill 같이 사용하도록 배열의 이름들 수정
            if($recv_number)
            //팝빌에서 제공한 sms, lms 전송하는 메소드에서는 수신자의 번호와 이름을 아래의 배열에서 추출함.
                $sms_messages[] = array('rcv'  => $recv_number,        //수신자번호
                                        'snd'  => $send_number,        //발신자번호
                                        'msg'  => $sms_contents,       //개별메시지 내용
                                        'sndnm' => $send_name,         //발신자이름
                                        'rcvnm' => $recv_name,         //수신자이름
                                        'sjt'	=> ''	               //LMS 제목
                                    );   
        }
    }

    $sms_count = count($sms_messages);

    if($sms_count > 0) {
        if($config['cf_sms_type'] == 'LMS') {
            if($config['cf_sms_use']=='icode'){
                include_once(G5_LIB_PATH.'/icode.lms.lib.php');
                $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);
                // SMS 모듈 클래스 생성
                if($port_setting !== false) {
                    $SMS = new LMS;
                    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);
    
                    for($s=0; $s<$sms_count; $s++){
                        $strDest     = array();
                            $strDest[]   = $sms_messages[$s]['rcv'];
                            $strCallBack = $sms_messages[$s]['snd'];
                            $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                            $strSubject  = '';
                            $strURL      = '';
                            $strData     = iconv_euckr($sms_messages[$s]['msg']);
                            $strDate     = '';
                            $nCount      = count($strDest);
    
                        $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);
    
                    $SMS->Send();
                    $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
    
                    }
                }
            }elseif($config['cf_sms_use']=='popbill'){
                include_once (G5_LIB_PATH.'/popbill/popbill_config.php');
                    try {
                        $receiptNum = $MessagingService->SendLMS($corpnum, $send_number, '', $sms_contents, $sms_messages, $reserveDT, $adsYN, $linkid, $send_name, '', $requestNum);
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
    
                for($s=0; $s<$sms_count; $s++) {
                    $recv_number = $sms_messages[$s]['rcv'];
                    $send_number = $sms_messages[$s]['snd'];
                    $sms_content = iconv_euckr($sms_messages[$s]['msg']);
                    $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], $sms_content, "");
                }
                    $SMS->Send();
                    $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
            }elseif($config['cf_sms_use']=='popbill'){
                include_once (G5_LIB_PATH.'/popbill/popbill_config.php');
                    try {
                        $receiptNum = $MessagingService->SendSMS($corpnum, $send_number, $sms_contents, $sms_messages, $reserveDT, $adsYN, $linkid, $pop_snd_name, '', $requestNum);
                        }
                    catch (PopbillException $pe) {
                        $code = $pe->getCode();
                        $message = $pe->getMessage();
                        }
            }              
        }
    }
}
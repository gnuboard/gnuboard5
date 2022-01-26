<?php
$sub_menu = '400660';
include_once('./_common.php');

check_demo();

if ($w == 'd')
    auth_check_menu($auth, $sub_menu, "d");
else
    auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

if ($w == "u")
{
    $sql = "update {$g5['g5_shop_item_qa_table']}
               set iq_subject = '$iq_subject',
                   iq_question = '$iq_question',
                   iq_answer = '$iq_answer'
             where iq_id = '$iq_id' ";
    sql_query($sql);

    if(trim($iq_answer)) {
        $sql = " select a.iq_email, a.iq_hp,a.iq_name, b.it_name
                    from {$g5['g5_shop_item_qa_table']} a left join {$g5['g5_shop_item_table']} b on ( a.it_id = b.it_id )
                    where a.iq_id = '$iq_id' ";
        $row = sql_fetch($sql);

        // SMS 알림
        if($config['cf_sms_use'] == 'popbill' || 'icode' && $row['iq_hp']) {
            $sms_content = get_text($row['it_name']).' 상품문의에 답변이 등록되었습니다.';
            $send_number = preg_replace('/[^0-9]/', '', $default['de_admin_company_tel']);
            $send_name = get_text($default['de_admin_company_name']);
            $recv_number = preg_replace('/[^0-9]/', '', $row['iq_hp']);
            $recv_name = get_text($row['iq_name']);
            //popbill 문자메시지 전송에 필요함
            $Messages[] = array(
                'snd' => $send_number,		// 발신번호
                'sndnm' => $send_name,		// 발신자명
                'rcv' => $recv_number,		// 수신번호
                'rcvnm' => $recv_name,		// 수신자성명
                'msg'	=> $sms_content	    // 개별 메시지 내용
            );
            if($recv_number) {
                if($config['cf_sms_type'] == 'LMS') {
                    if($config['cf_sms_use']=='icode'){
                        include_once(G5_LIB_PATH.'/icode.lms.lib.php');

                        $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);

                        // SMS 모듈 클래스 생성
                        if($port_setting !== false) {
                            $SMS = new LMS;
                            $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);

                            $strDest     = array();
                            $strDest[]   = $recv_number;
                            $strCallBack = $send_number;
                            $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                            $strSubject  = '';
                            $strURL      = '';
                            $strData     = iconv_euckr($sms_content);
                            $strDate     = '';
                            $nCount      = count($strDest);

                            $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);

                            $SMS->Send();
                            $SMS->Init(); // 보관하고 있던 결과값을 지웁니다.
                        }
                    }elseif($config['cf_sms_use']=='popbill'){
                        include_once (G5_LIB_PATH.'/popbill/popbill_config.php');
                        try {
                            $receiptNum = $MessagingService->SendLMS($CorpNum, $send_number, '', $sms_content, $Messages, $reserveDT, $adsYN, $linkid, $send_name, '', $requestNum);
                        }
                        catch (PopbillException $pe) {
                            $code = $pe->getCode();
                            $message = $pe->getMessage();
                        }
                    }
                    } else {
                        if($config['cf_sms_use']=='icode'){
                            include_once(G5_LIB_PATH.'/icode.sms.lib.php');
                            $SMS = new SMS; // SMS 연결
                            $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
                            $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], iconv_euckr(stripslashes($sms_content)), "");
                            $SMS->Send();
                        }elseif($config['cf_sms_use']=='popbill'){
                           include_once (G5_LIB_PATH.'/popbill/popbill_config.php');
                            try {
                                $receiptNum = $MessagingService->SendSMS($CorpNum, $send_number, $sms_content, $Messages, $reserveDT, $adsYN, $linkid, $send_name, '', $requestNum);
                            } catch(PopbillException $pe) {
                                $code = $pe->getCode();
                                $message = $pe->getMessage();
                            }                     
                        }
                    }
            }
        }

        // 답변 이메일전송
        if(trim($row['iq_email'])) {
            include_once(G5_LIB_PATH.'/mailer.lib.php');

            $subject = $config['cf_title'].' '.$row['it_name'].' 상품문의 답변 알림 메일';
            $content = conv_content($iq_answer, 1);

            mailer($config['cf_title'], $config['cf_admin_email'], $row['iq_email'], $subject, $content, 1);
        }
    }

    goto_url("./itemqaform.php?w=$w&amp;iq_id=$iq_id&amp;sca=$sca&amp;$qstr");
}
else {
    alert();
}
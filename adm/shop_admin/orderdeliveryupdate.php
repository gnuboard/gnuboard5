<?php
$sub_menu = '400400';
include_once('./_common.php');
include_once('./admin.shop.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

auth_check_menu($auth, $sub_menu, "w");

define("_ORDERMAIL_", true);

$sms_count = 0;
$sms_messages = array();

if(isset($_FILES['excelfile']['tmp_name']) && $_FILES['excelfile']['tmp_name']) {
    $file = $_FILES['excelfile']['tmp_name'];

    include_once(G5_LIB_PATH.'/PHPExcel/IOFactory.php');

    $objPHPExcel = PHPExcel_IOFactory::load($file);
    $sheet = $objPHPExcel->getSheet(0);

    $num_rows = $sheet->getHighestRow();
    $highestColumn = $sheet->getHighestColumn();

    $fail_od_id = array();
    $total_count = 0;
    $fail_count = 0;
    $succ_count = 0;

    // $i 사용시 ordermail.inc.php의 $i 때문에 무한루프에 빠짐
    for ($k = 2; $k <= $num_rows; $k++) {
        $total_count++;

        $rowData = $sheet->rangeToArray('A' . $k . ':' . $highestColumn . $k,
                                            NULL,
                                            TRUE,
                                            FALSE);

        $od_id               = isset($rowData[0][0]) ? addslashes(trim($rowData[0][0])) : '';
        $od_delivery_company = isset($rowData[0][8]) ? addslashes($rowData[0][8]) : '';
        $od_invoice          = isset($rowData[0][9]) ? addslashes($rowData[0][9]) : '';

        if(!$od_id || !$od_delivery_company || !$od_invoice) {
            $fail_count++;
            $fail_od_id[] = $od_id;
            continue;
        }

        // 주문정보
        $od = sql_fetch(" select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ");
        if (!$od) {
            $fail_count++;
            $fail_od_id[] = $od_id;
            continue;
        }

        if($od['od_status'] != '준비') {
            $fail_count++;
            $fail_od_id[] = $od_id;
            continue;
        }

        $delivery['invoice'] = $od_invoice;
        $delivery['invoice_time'] = G5_TIME_YMDHIS;
        $delivery['delivery_company'] = $od_delivery_company;

        // 주문정보 업데이트
        order_update_delivery($od_id, $od['mb_id'], '배송', $delivery);
        change_status($od_id, '준비', '배송');

        $succ_count++;
        
        $send_sms = isset($_POST['send_sms']) ? clean_xss_tags($_POST['send_sms'], 1, 1) : '';
        $od_send_mail = isset($_POST['od_send_mail']) ? clean_xss_tags($_POST['od_send_mail'], 1, 1) : '';
        $send_escrow = isset($_POST['send_escrow']) ? clean_xss_tags($_POST['send_escrow'], 1, 1) : '';

        // SMS
        if($config['cf_sms_use'] == 'icode' || 'popbill' && $send_sms && $default['de_sms_use5']) {
            $sms_contents = conv_sms_contents($od_id, $default['de_sms_cont5']);
            if($sms_contents) {
                $recv_number = preg_replace("/[^0-9]/", "", $od['od_hp']);	// 수신자번호
                $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호
                //popbill 데이터 추가
                $send_name = $default['de_admin_company_name'];
                $recv_name = $od['od_name'];
                //popbill 문자메시지 전송에 필요함
                //icode, popbill 같이 사용하도록 수정
                if($recv_number)
                    $sms_messages[] = array('rcv'  => $recv_number,        //수신자번호
                                            'snd'  => $send_number,        //발신자번호
                                            'msg'  => $sms_contents,       //개별메시지 내용
                                            'sndnm' => $send_name,         //발신자이름
                                            'rcvnm' => $recv_name,         //수신자이름
                                            'sjt'	=> ''	               //LMS 제목
                                        );  
                } 
        }
        // 메일
        if($config['cf_email_use'] && $od_send_mail)
            include './ordermail.inc.php';

        // 에스크로 배송
        if($send_escrow && $od['od_tno'] && $od['od_escrow']) {
            $escrow_tno  = $od['od_tno'];
            $escrow_numb = $od_invoice;
            $escrow_corp = $od_delivery_company;

            include(G5_SHOP_PATH.'/'.$od['od_pg'].'/escrow.register.php');
        }
    }
}

// SMS
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
            print_r2($sms_messages);
                try {
                    $receiptNum = $MessagingService->SendLMS($corpnum, $send_number, '', $sms_contents, $sms_messages, $reserveDT, $adsYN, $linkid, $send_name, '', $requestNum);
                }
                catch (PopbillException $pe) {
                    $code = $pe->getCode();
                    $message = $pe->getMessage();
                }
                exit;
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

$g5['title'] = '엑셀 배송일괄처리 결과';
include_once(G5_PATH.'/head.sub.php');
?>

<div class="new_win">
    <h1><?php echo $g5['title']; ?></h1>

    <div class="local_desc01 local_desc">
        <p>배송일괄처리를 완료했습니다.</p>
    </div>

    <dl id="excelfile_result">
        <dt>총배송건수</dt>
        <dd><?php echo number_format($total_count); ?></dd>
        <dt class="result_done">완료건수</dt>
        <dd class="result_done"><?php echo number_format($succ_count); ?></dd>
        <dt class="result_fail">실패건수</dt>
        <dd class="result_fail"><?php echo number_format($fail_count); ?></dd>
        <?php if($fail_count > 0) { ?>
        <dt>실패주문코드</dt>
        <dd><?php echo implode(', ', $fail_od_id); ?></dd>
        <?php } ?>
    </dl>

    <div class="btn_confirm01 btn_confirm">
        <button type="button" onclick="window.close();">창닫기</button>
    </div>

</div>

<?php
include_once(G5_PATH.'/tail.sub.php');
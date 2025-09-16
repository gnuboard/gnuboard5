<?php
include_once('./_common.php');
include_once(G5_KAKAO5_PATH.'/kakao5.lib.php'); // 카카오톡 알림톡 설정
require_once(G5_SHOP_PATH.'/toss/toss.inc.php'); // 토스페이먼츠 v2 공통 설정

/*************************************************************************
**
**  토스페이먼츠 v2 가상계좌 입금 결과 처리 - 웹훅
**
*************************************************************************/

$payLog = true; // 로그 사용 여부
$log_file = G5_DATA_PATH . '/log/tosspayment_result_log.txt';

/**
 * 토스페이먼츠 로그 기록 함수
 */
function write_toss_log($reason, $orderId = '', $status = '')
{
    global $payLog, $log_file;

    if($payLog) {
        $logfile = fopen($log_file, "a+");
        
        fwrite( $logfile,"************************************************\r\n");
        fwrite( $logfile,"Reason        : ".$reason."\r\n");
        fwrite( $logfile,"Status        : ".$status."\r\n");
        fwrite( $logfile,"OrderId       : ".$orderId."\r\n");
        fwrite( $logfile,"************************************************\r\n");
        
        fclose( $logfile );
    }
}

// 토스페이먼츠 입금통보 결과 데이터 읽기
$raw = file_get_contents('php://input');
if ($raw == false) {
    write_toss_log("입력 데이터 읽기 실패");
    http_response_code(400);
    exit;
}

// json 파싱 체크
$data = json_decode($raw, true);
if (!is_array($data)) {
    $json_error = json_last_error_msg();
    write_toss_log("데이터 파싱 실패: " . $json_error);
    http_response_code(400);
    exit;
}

// 공통 필드 정리
$TOSS_CREATEDAT = isset($data["createdAt"]) ? $data["createdAt"] : '';
$TOSS_SECRET = isset($data["secret"]) ? $data["secret"] : '';
$TOSS_ORDERID = isset($data["orderId"]) ? $data["orderId"] : '';
$TOSS_STATUS = isset($data["status"]) ? $data["status"] : '';
$TOSS_TRANSACTIONKEY = isset($data["transactionKey"]) ? $data["transactionKey"] : '';

// 결제 상세 조회
$toss = new TossPayments(
    $config['cf_toss_client_key'],
    $config['cf_toss_secret_key'],
    $config['cf_lg_mid']
);
$toss->setPaymentHeader();

$orderResult = $toss->getPaymentByOrderId($TOSS_ORDERID);
$order_info = $toss->responseData;

if (!$orderResult || $order_info['secret'] !== $TOSS_SECRET) {
    $error_msg = isset($order_info['message']) ? $order_info['message'] : '주문 정보 조회 실패';
    $error_code = isset($order_info['code']) ? $order_info['code'] : 'UNKNOWN_ERROR';
    write_toss_log("주문 정보 조회 실패 - {$error_code} : {$error_msg}", $TOSS_ORDERID, $TOSS_STATUS);
    http_response_code(400);
    exit;
}

// 결제 정보
$paymentKey     = isset($order_info["paymentKey"])                      ? clean_xss_tags($order_info["paymentKey"]) : ''; // 결제 키
$customerName   = isset($order_info["virtualAccount"]["customerName"])  ? clean_xss_tags($order_info["virtualAccount"]["customerName"]) : ''; // 주문자명 (가상계좌 발급 시 고객명)
$depositorName  = isset($order_info["virtualAccount"]["depositorName"]) ? clean_xss_tags($order_info["virtualAccount"]["depositorName"]) : ''; // 입금자명 (실제 입금자 입력 이름)
$totalAmount    = isset($order_info["totalAmount"])                     ? clean_xss_tags($order_info["totalAmount"]) : ''; // 입금 금액 (결제 총액)
$bankCode       = isset($order_info["virtualAccount"]["bankCode"])      ? clean_xss_tags($order_info["virtualAccount"]["bankCode"]) : ''; // 은행코드 (가상계좌 발급 은행, 예: 11 → 농협)
$accountNumber  = isset($order_info["virtualAccount"]["accountNumber"]) ? clean_xss_tags($order_info["virtualAccount"]["accountNumber"]) : ''; // 가상계좌 입금계좌번호
$approvedAt     = isset($order_info['approvedAt'])                      ? clean_xss_tags($order_info['approvedAt']) : ''; //입금일시
$dueDate        = isset($order_info["virtualAccount"]['dueDate'])       ? clean_xss_tags($order_info['virtualAccount']['dueDate']) : ''; // 만료일시
$receipt_time   = $approvedAt ? (strtotime($approvedAt) !== false ? date("Y-m-d H:i:s", strtotime($approvedAt)) : '') : '';
$due_time       = $dueDate ? (strtotime($dueDate) !== false ? date("Y-m-d H:i:s", strtotime($dueDate)) : '') : '';

// 가상계좌 채번시 현금영수증 자동발급신청이 되었을 경우 전달되며
// RcptTID에 값이 있는 경우만 발급처리 됨
$RcptTID = isset($order_info['cashReceipt']['receiptKey']) ? clean_xss_tags($order_info['cashReceipt']['receiptKey']) : '';   // 현금영수증 거래번호
$RcptAuthCode = isset($order_info['cashReceipt']['issueNumber']) ? clean_xss_tags($order_info['cashReceipt']['issueNumber']) : ''; // 현금영수증 승인번호
// 현금영수증 구분(0:미발행, 1:소득공제용, 2:지출증빙용)
$RcptType = isset($order_info['cashReceipt']['type']) ? clean_xss_tags($order_info['cashReceipt']['type'] === '소득공제' ? '1' : ($order_info['cashReceipt']['type'] === '지출증빙' ? '2' : '0')) : '0';
$RcptReceiptUrl = isset($order_info['cashReceipt']['receiptUrl']) ? clean_xss_tags($order_info['cashReceipt']['receiptUrl']) : ''; // 현금영수증 URL

$result = false;

/** 
 * 입금 완료 처리
 */
if($TOSS_STATUS == "DONE"){
    
    // 입금결과 처리
    $sql = " select pp_id, od_id from {$g5['g5_shop_personalpay_table']} where pp_id = '{$TOSS_ORDERID}' and pp_tno = '{$paymentKey}'";
    $row = sql_fetch($sql);

    if($row['pp_id']) {
        // 개인결제 UPDATE
        $add_update_sql = '';
        
        // 현금영수증 발급시 1 또는 2 이면
        if ($RcptType) {
            $add_update_sql = "
            , pp_cash           = '1',
            pp_cash_no        = '".$RcptAuthCode."',
            pp_cash_info      = '".serialize(array('TID'=>$RcptTID, 'ApplNum'=>$RcptAuthCode, 'AuthDate'=>$approvedAt, 'receiptUrl'=>$RcptReceiptUrl))."'
            ";
        }
        
        $sql = " update {$g5['g5_shop_personalpay_table']}
                    set pp_receipt_price    = '$totalAmount',
                        pp_receipt_time     = '$receipt_time',
                        pp_deposit_name = '$depositorName'
                        $add_update_sql
                    where pp_id = '$TOSS_ORDERID'";
        $result = sql_query($sql, false);

        if($row['od_id']) {
            // 주문서 UPDATE
            $sql = " update {$g5['g5_shop_order_table']}
                        set od_receipt_price = od_receipt_price + '$totalAmount',
                            od_receipt_time = '$receipt_time',
                            od_deposit_name = '$depositorName',
                            od_shop_memo = concat(od_shop_memo, \"\\n개인결제 ".$row['pp_id']." 로 결제완료 - ".$receipt_time."\")
                        where od_id = '{$row['od_id']}' ";
            $result = sql_query($sql, FALSE);
        }
    } else {
        // 주문내역에 secret 검증 추가
        $sql = " select od_id from {$g5['g5_shop_order_table']} where od_id = '$TOSS_ORDERID' and od_tno = '$paymentKey'";
        $row = sql_fetch($sql);
        if(!$row['od_id']) {
            write_toss_log("주문내역 조회 실패", $TOSS_ORDERID, $TOSS_STATUS);
            http_response_code(400);
            exit;
        }

        // 주문서 UPDATE
        $sql = " update {$g5['g5_shop_order_table']}
                    set od_receipt_price = '$totalAmount',
                        od_receipt_time = '$receipt_time',
                        od_deposit_name = '$depositorName'
                    where od_id = '$TOSS_ORDERID'
                    and od_tno = '$paymentKey'";
        $result = sql_query($sql, FALSE);
    }

    if($result) {
        if (isset($row['od_id']) && $row['od_id'])
            $od_id = $row['od_id'];
        else
            $od_id = $TOSS_ORDERID;

        // 주문정보 체크
        $sql = " select count(od_id) as cnt
                    from {$g5['g5_shop_order_table']}
                    where od_id = '$od_id'
                        and od_status = '주문' ";
        $row = sql_fetch($sql);

        if($row['cnt'] == 1) {
            // 미수금 정보 업데이트
            $info = get_order_info($od_id);
            
            $add_update_sql = '';

            // 현금영수증 발급시 1 또는 2 이면
            if ($RcptType) {
                $add_update_sql = "
                , od_cash           = '1',
                od_cash_no        = '".$RcptAuthCode."',
                od_cash_info      = '".serialize(array('TID'=>$RcptTID, 'ApplNum'=>$RcptAuthCode, 'AuthDate'=>$approvedAt, 'receiptUrl'=>$RcptReceiptUrl))."'
                ";
            }

            $sql = " update {$g5['g5_shop_order_table']}
                        set od_misu = '{$info['od_misu']}' $add_update_sql ";
            if($info['od_misu'] == 0)
                $sql .= " , od_status = '입금' ";
            $sql .= " where od_id = '$od_id' ";
            sql_query($sql, FALSE);

            // 장바구니 상태변경
            if($info['od_misu'] == 0) {
                $sql = " update {$g5['g5_shop_cart_table']}
                            set ct_status = '입금'
                            where od_id = '$od_id' ";
                sql_query($sql, FALSE);
            }
        }
    }
}

/** 
 * 입금 오류 처리 (입금 오류로 인해 WAITING_FOR_DEPOSIT으로 되돌아온 경우)
 */
elseif($TOSS_STATUS == "WAITING_FOR_DEPOSIT")
{
    // 개인결제 정보 조회
    $sql = " select pp_id, od_id, pp_name, pp_hp, pp_tel from {$g5['g5_shop_personalpay_table']} where pp_id = '{$TOSS_ORDERID}' and pp_tno = '{$paymentKey}'";
    $row = sql_fetch($sql);
    
    if($row['pp_id']) {        
        // 개인결제 정보 롤백
        $sql = " update {$g5['g5_shop_personalpay_table']}
                    set pp_receipt_price = 0,
                        pp_receipt_time = '',
                        pp_cash = 0,
                        pp_cash_no = '',
                        pp_cash_info = ''
                    where pp_id = '{$TOSS_ORDERID}' and pp_tno = '{$paymentKey}'";
        $result = sql_query($sql, FALSE);
        
        if($row['od_id']) {
            // 주문서에서 개인결제 금액 차감
            $sql = " update {$g5['g5_shop_order_table']}
                        set od_receipt_price = od_receipt_price - '$totalAmount',
                            od_shop_memo = concat(od_shop_memo, \"\\n개인결제 ".$row['pp_id']." 가상계좌 입금 오류로 취소 - ".date('Y-m-d H:i:s')."\")
                        where od_id = '{$row['od_id']}' ";
            $result = sql_query($sql, FALSE);
        }
    } else {
        // 일반 주문 롤백 전에 데이터 존재 확인
        $sql = " select od_id, od_name, od_hp, od_tel from {$g5['g5_shop_order_table']} where od_id = '{$TOSS_ORDERID}' and od_tno = '{$paymentKey}'";
        $row = sql_fetch($sql);
        if(empty($row['od_id'])) {
            write_toss_log("주문 데이터가 존재하지 않음", $TOSS_ORDERID, $TOSS_STATUS);
            http_response_code(400);
            exit;
        }
        
        // 일반 주문 입금완료 - 주문 상태 롤백 (입금 → 주문)
        $sql = " update {$g5['g5_shop_order_table']}
                    set od_status = '주문',
                        od_receipt_price = 0,
                        od_receipt_time = '',
                        od_shop_memo = concat(od_shop_memo, \"\\n가상계좌 입금 오류로 취소 - ".date('Y-m-d H:i:s')."\"),
                        od_cash = 0,
                        od_cash_no = '',
                        od_cash_info = ''
                    where od_id = '{$TOSS_ORDERID}' and od_tno = '{$paymentKey}' ";
        $result = sql_query($sql, FALSE);
    }
    
    // 공통 처리: 미수금 정보 재계산 및 상태 롤백
    if($result) {
        if (isset($row['od_id']) && $row['od_id'])
            $od_id = $row['od_id'];
        else
            $od_id = $TOSS_ORDERID;

        // 미수금 정보 재계산
        $info = get_order_info($od_id);
        
        if($info) {
            $sql = " update {$g5['g5_shop_order_table']}
                        set od_misu = '{$info['od_misu']}',
                            od_status = '주문',
                            od_cash = 0,
                            od_cash_no = '',
                            od_cash_info = ''
                        where od_id = '{$od_id}' ";
            sql_query($sql, FALSE);
            
            // 장바구니 상태 롤백 (입금 → 주문)
            $sql = " update {$g5['g5_shop_cart_table']}
                        set ct_status = '주문'
                        where od_id = '{$od_id}' ";
            sql_query($sql, FALSE);
        }

        // SMS 발송 - 재입금 안내
        $sms_message = '';
        
        // 개인결제인지 일반주문인지 확인하여 연락처 조회
        if($row['pp_id']) {
            // 개인결제인 경우
            $customer_name = $row['pp_name'];
            $customer_phone = $row['pp_hp'] ? $row['pp_hp'] : ($row['pp_tel'] ? $row['pp_tel'] : '');
            $title = "개인결제번호 {$TOSS_ORDERID}";
        } else {
            // 일반주문인 경우
            $customer_name = $row['od_name'];
            $customer_phone = $row['od_hp'] ? $row['od_hp'] : ($row['od_tel'] ? $row['od_tel'] : '');
            $title = "주문번호 {$od_id}";
        }

        if($customer_phone) {
            $sms_message = "{$customer_name}님, {$title} 가상계좌 입금이 완료되지 않았습니다. 재입금 또는 관리자 문의 바랍니다.\n";
            $sms_message .= $default['de_admin_company_name'];
        }
                
        // 전화번호가 있고 SMS 발송 설정이 활성화된 경우에만 발송
        if($customer_phone && $sms_message && $config['cf_icode_id'] && $config['cf_icode_pw']) {
            // SMS 발송
            $sms_messages = array();
            $receive_number = preg_replace("/[^0-9]/", "", $customer_phone);   // 수신자번호
            $send_number = preg_replace("/[^0-9]/", "", $default['de_admin_company_tel']); // 발신자번호
            $sms_messages[] = array('recv' => $receive_number, 'send' => $send_number, 'cont' => $sms_message);
            
            // SMS 발송 처리
            if($config['cf_sms_type'] == 'LMS') {
                include_once(G5_LIB_PATH.'/icode.lms.lib.php');
                
                $port_setting = get_icode_port_type($config['cf_icode_id'], $config['cf_icode_pw']);
                
                if($port_setting !== false) {
                    $SMS = new LMS;
                    $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $port_setting);
                    
                    for($s=0; $s<count($sms_messages); $s++) {
                        $strDest     = array();
                        $strDest[]   = $sms_messages[$s]['recv'];
                        $strCallBack = $sms_messages[$s]['send'];
                        $strCaller   = iconv_euckr(trim($default['de_admin_company_name']));
                        $strSubject  = '';
                        $strURL      = '';
                        $strData     = iconv_euckr($sms_messages[$s]['cont']);
                        $strDate     = '';
                        $nCount      = count($strDest);
                        
                        $res = $SMS->Add($strDest, $strCallBack, $strCaller, $strSubject, $strURL, $strData, $strDate, $nCount);
                        $SMS->Send();
                        $SMS->Init();
                    }
                }
            } else {
                include_once(G5_LIB_PATH.'/icode.sms.lib.php');
                
                $SMS = new SMS;
                $SMS->SMS_con($config['cf_icode_server_ip'], $config['cf_icode_id'], $config['cf_icode_pw'], $config['cf_icode_server_port']);
                
                for($s=0; $s<count($sms_messages); $s++) {
                    $recv_number = $sms_messages[$s]['recv'];
                    $send_number = $sms_messages[$s]['send'];
                    $sms_content = iconv_euckr($sms_messages[$s]['cont']);
                    
                    $SMS->Add($recv_number, $send_number, $config['cf_icode_id'], $sms_content, "");
                }
                
                $SMS->Send();
                $SMS->Init();
            }
            
            // SMS 발송 로그 기록
            write_toss_log("가상계좌 재입금 안내 SMS 발송 완료", $TOSS_ORDERID, "SMS_SENT");
        }
    }
}

/** 
 * 입금 전 취소 처리
 */
elseif($TOSS_STATUS == "CANCELED")
{
    $sql = " update {$g5['g5_shop_order_table']}
                set od_shop_memo = concat(od_shop_memo, \"\\n가상계좌 입금 전 취소 - ".date('Y-m-d H:i:s')."\")
                where od_id = '{$TOSS_ORDERID}' ";
    $result = sql_query($sql, FALSE);
}

//************************************************************************************
// 위에서 상점 데이터베이스에 등록 성공유무에 따라서 성공시에는 성공응답인 `HTTP 200` 상태 코드를 리턴해야 합니다.
// (주의) 성공응답인 `HTTP 200` 상태 코드를 리턴하지 않으면 토스페이먼츠에서 7회까지 재전송에 실패하면 웹훅 상태가 실패로 변경됩니다.

// 토스페이먼츠 로그 기록 (nicepay 형태)
if($payLog) {
    $logfile = fopen($log_file, "a+");

    // 은행명 조회
    $bankName = '';
    if($bankCode && isset($toss->bankCode[$bankCode])) {
        $bankName = $toss->bankCode[$bankCode];
    }

    fwrite( $logfile,"************************************************\r\n");
    fwrite( $logfile,"GoodsName     : 토스페이먼츠 가상계좌\r\n");
    fwrite( $logfile,"OrderId       : ".$TOSS_ORDERID."\r\n");
    fwrite( $logfile,"Status        : ".$TOSS_STATUS."\r\n");
    fwrite( $logfile,"ResultMsg     : ".($result ? "SUCCESS" : "FAIL")."\r\n");
    fwrite( $logfile,"Amt           : ".$totalAmount."\r\n");
    fwrite( $logfile,"name          : ".$customerName."\r\n");
    fwrite( $logfile,"TID           : ".$paymentKey."\r\n");
    fwrite( $logfile,"AuthDate      : ".$approvedAt."\r\n");
    fwrite( $logfile,"VbankNum      : ".$accountNumber."\r\n");
    fwrite( $logfile,"VbankCode     : ".$bankCode."\r\n");
    fwrite( $logfile,"VbankName     : ".$bankName."\r\n");
    fwrite( $logfile,"VbankInputName: ".$depositorName."\r\n");
    fwrite( $logfile,"RcptTID       : ".$RcptTID."\r\n");
    fwrite( $logfile,"RcptAuthCode  : ".$RcptAuthCode."\r\n");
    fwrite( $logfile,"RcptType      : ".$RcptType."\r\n");
    fwrite( $logfile,"************************************************\r\n");

    fclose( $logfile );
}

if ($result)
{
    http_response_code(200); // 절대로 지우지마세요
    echo "OK";
    exit;
}
else
{
    http_response_code(400);
    echo "FAIL";
    exit;
}

//*************************************************************************************
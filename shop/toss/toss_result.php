<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 토스페이먼츠 v2 공통 설정
require_once(G5_SHOP_PATH.'/toss/toss.inc.php');

$orderId = isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : '';
$paymentKey = isset($_POST['paymentKey']) ? $_POST['paymentKey'] : '';

if (empty($orderId) || empty($paymentKey)) {
    alert('주문정보가 올바르지 않습니다.', G5_SHOP_URL);
}

$sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$orderId' limit 1 ";
$row = sql_fetch($sql);

$data = isset($row['dt_data']) ? unserialize(base64_decode($row['dt_data'])) : array();

$amount = isset($data['amountValue']) ? (int)$data['amountValue'] : 0;

if ($amount <= 0 || $amount !== (int)$order_price) {
    alert('결제금액이 올바르지 않습니다.', G5_SHOP_URL);
}

$toss = new TossPayments(
    $config['cf_toss_client_key'],
    $config['cf_toss_secret_key'],
    $config['cf_lg_mid']
);

// 결제데이터 셋팅
$toss->setPaymentData([
    'amount' => $amount,
    'orderId' => $orderId,
    'paymentKey' => $paymentKey
]);
$toss->setPaymentHeader();

// 결제승인 요청
$result = $toss->approvePayment();

if ($result) {
    // 결제승인 성공시 처리
    $status = isset($toss->responseData['status']) ? $toss->responseData['status'] : '';
    $method = isset($toss->responseData['method']) ? $toss->responseData['method'] : '';
    
    // 가상계좌(VIRTUAL_ACCOUNT)만 입금대기(WAITING_FOR_DEPOSIT) 상태 값을 가질 수 있음
    if ($status === 'DONE' || ($status === 'WAITING_FOR_DEPOSIT' && $method === '가상계좌')) {
        // 공통 DB처리 변수 설정
        $tno = isset($toss->responseData['paymentKey']) ? $toss->responseData['paymentKey'] : '';
        $amount = isset($toss->responseData['totalAmount']) ? $toss->responseData['totalAmount'] : 0;
        $escw_yn = $toss->responseData['useEscrow'] === true ? 'Y' : 'N';
        $app_time = isset($toss->responseData['approvedAt']) ? date('Y-m-d H:i:s', strtotime($toss->responseData['approvedAt'])) : '';
        
        // 결제수단별 데이터 처리 (카드, 가상계좌, 계좌이체, 휴대폰, 간편결제 순)
        if ($method === '카드') {
            // 카드
            $app_no = $od_app_no = isset($toss->responseData['card']['approveNo']) ? $toss->responseData['card']['approveNo'] : '00000000';
            $card_name = isset($toss->cardCode[$toss->responseData['card']['issuerCode']]) ? $toss->cardCode[$toss->responseData['card']['issuerCode']] : '';            
        } else if ($method === '가상계좌') {
            // 가상계좌
            $bank_name = $bankname = isset($toss->bankCode[$toss->responseData['virtualAccount']['bankCode']]) ? $toss->bankCode[$toss->responseData['virtualAccount']['bankCode']] : '';
            $depositor = isset($toss->responseData['virtualAccount']['customerName']) ? $toss->responseData['virtualAccount']['customerName'] : '';
            $account = isset($toss->responseData['virtualAccount']['accountNumber']) ? $toss->responseData['virtualAccount']['accountNumber'] : '';
        } else if ($method === '계좌이체') {
            // 계좌이체
            $bank_name = isset($toss->bankCode[$toss->responseData['transfer']['bankCode']]) ? $toss->bankCode[$toss->responseData['transfer']['bankCode']] : '';

            // 현금영수증 데이터 처리
            $cashReceiptType = isset($toss->responseData['cashReceipt']['type']) ? $toss->responseData['cashReceipt']['type'] : '';
            $RcptType = $cashReceiptType === '소득공제' ? '1' : ($cashReceiptType === '지출증빙' ? '2' : '0');
            $RcptTID = isset($toss->responseData['cashReceipt']['receiptKey']) ? $toss->responseData['cashReceipt']['receiptKey'] : ''; // 현금영수증 TID, 현금영수증 거래인 경우 리턴
            $RcptAuthCode = isset($toss->responseData['cashReceipt']['issueNumber']) ? $toss->responseData['cashReceipt']['issueNumber'] : ''; // 현금영수증 승인번호, 현금영수증 거래인 경우 리턴
            $RcptReceiptUrl = isset($toss->responseData['cashReceipt']['receiptUrl']) ? $toss->responseData['cashReceipt']['receiptUrl'] : ''; // 현금영수증 URL

            // 현금영수증 발급시 1 또는 2 이면
            if ($RcptType) {
                $pg_receipt_infos['od_cash'] = 1;   // 현금영수증 발급인것으로 처리
                $pg_receipt_infos['od_cash_no'] = $RcptAuthCode;    // 현금영수증 승인번호
                $pg_receipt_infos['od_cash_info'] = serialize(array('TID'=>$RcptTID, 'ApplNum'=>$RcptAuthCode, 'receiptUrl'=>$RcptReceiptUrl));
            }
        } else if ($method === '휴대폰') {
            // 휴대폰
            $mobile_no = isset($toss->responseData['mobilePhone']['customerMobilePhone']) ? $toss->responseData['mobilePhone']['customerMobilePhone'] : '';            
        } else if ($method === '간편결제') {
            // 간편결제
            $provider = isset($toss->responseData['easyPay']['provider']) ? $toss->responseData['easyPay']['provider'] : '';
            $card_name = isset($toss->easyPayCode[$provider]) ? $toss->easyPayCode[$provider] : $provider;
        }
    } else {

        if(G5_IS_MOBILE) {
            if(isset($_POST['pp_id']) && $_POST['pp_id']) {
                $page_return_url = G5_SHOP_URL.'/personalpayform.php?pp_id='.get_session('ss_personalpay_id');
            } else {
                $page_return_url = G5_SHOP_URL.'/orderform.php';
                if(get_session('ss_direct'))
                    $page_return_url .= '?sw_direct=1';
            }

            alert($toss->responseData['message'].' 코드 : '.$toss->responseData['code'], $page_return_url);
        } else {
            alert($toss->responseData['message'].' 코드 : '.$toss->responseData['code'], G5_SHOP_URL.'/orderform.php');
        }
    }
} else {
    alert($toss->responseData['message'].' 코드 : '.$toss->responseData['code'], G5_SHOP_URL);
}
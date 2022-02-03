<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once('./_common.php');
include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$nicepay_result = false;

try {
    if(isset($_REQUEST['AuthResultCode']) && strcmp('0000', $_REQUEST['AuthResultCode']) == 0) {
        try {
            $oid = $_POST['Moid'];
            if(empty($oid)) throw new Exception("주문번호가 존재하지 않습니다.");

            // 주문데이터 존재여부 확인
            $sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$oid' ";
            $row = sql_fetch($sql);

            $data = isset($row['dt_data']) ? unserialize(base64_decode($row['dt_data'])) : array();

            // 개인결제 or 일반주문결제 확인
            if (isset($data['pp_id']) && $data['pp_id']) {
                $page_return_url  = G5_SHOP_URL.'/personalpayform.php?pp_id='.$data['pp_id'];
            } else {
                $page_return_url  = G5_SHOP_URL.'/orderform.php';
                if(get_session('ss_direct'))
                    $page_return_url .= '?sw_direct=1';
            }

            $veri_encrypt = bin2hex(hash("sha256", $_POST['EdiDate'].$_POST['MID'].$_POST['Amt'].$nicepay->m_MerchantKey, true));
            if($veri_encrypt !== $data['EncryptData']) alert("위변조 데이터가 일치하지 않습니다.", $page_return_url);

            $nicepay->m_ActionType      = "PYO";
            $nicepay->m_Price           = $_REQUEST['Amt'];
            $nicepay->m_NetCancelAmt    = $_REQUEST['Amt'];

            /*
            *******************************************************
            * <결제 결과 필드>
            *******************************************************
            */
            $nicepay->m_BuyerName       = $_REQUEST['BuyerName'];               // 구매자명
            $nicepay->m_BuyerEmail      = $_REQUEST['BuyerEmail'];              // 구매자이메일
            $nicepay->m_BuyerTel        = $_REQUEST['BuyerTel'];                // 구매자연락처
            $nicepay->m_GoodsName       = $_REQUEST['GoodsName'];               // 상품명
            $nicepay->m_GoodsCnt        = $_REQUEST['GoodsCnt'];                // 상품개수
            $nicepay->m_GoodsCl         = $_REQUEST['GoodsCl'];                 // 실물 or 컨텐츠
            $nicepay->m_PayMethod       = $_REQUEST['PayMethod'];               // 결제수단
            $nicepay->m_Moid            = $_REQUEST['Moid'];                    // 주문번호
            $nicepay->m_MallUserID      = $_REQUEST['MallUserID'];              // 회원사ID
            $nicepay->m_MID             = $_REQUEST['MID'];                     // MID
            $nicepay->m_MallIP          = $_REUQEST['MallIP'];                  // Mall IP
            $nicepay->m_LicenseKey      = $nicepay->m_MerchantKey;              // 상점키
            $nicepay->m_TrKey           = $_REQIEST['TrKey'];                   // 거래키
            $nicepay->m_TransType       = $_REQUEST['TransType'];               // 일반 or 에스크로
            $nicepay->startAction();

            $resultCode = $nicepay->m_ResultData["ResultCode"];                 // 결과코드
            $payMethod  = $nicepay->m_ResultData["PayMethod"];                  // 결제수단

            $paySuccess = false;

            // 결제항목에 맞는 결과값 확인 후 결제성공 반환
            switch($payMethod) {
                case "CARD":
                    if($resultCode == "3001") $paySuccess = true;
                    break;
                case "BANK":
                    if($resultCode == "4000") $paySuccess = true;
                    break;
                case "VBANK":
                    if($resultCode == "4100") $paySuccess = true;
                    break;
                case "CELLPHONE":
                    if($resultCode == "A000") $paySuccess = true;
                    break;
                case "SSG_BANK":
                    if($resultCode == "0000") $paySuccess = true;
                    break;
            }

            $resultData = $nicepay->m_ResultData;                               // 결과값

            // 결제승인시 추가 진행
            if ($paySuccess != false) { 
                $tno        = $resultData['TID'];
                $amount     = $resultData['Amt'];
                $app_time   = isset($resultData['AuthDate']) ? "20".$resultData['AuthDate'] : '';
                $pay_method = $resultData['PayMethod'];
                $pay_type   = $PAY_METHOD[$pay_method];
                // 나이스페이 입금자명은 데이터로 전송되지 않음
                // 주문자의 이름으로 입금자 노출
                $depositor  = isset($data['od_name']) ? $data['od_name'] : '';
                $app_no     = isset($resultData['AuthCode']) ? $resultData['AuthCode'] : '';
                $commid     = isset($resultData['Carrier']) ? $resultData['Carrier'] : '';
                $card_name  = isset($resultData['CardName']) ? $resultData['CardName'] : '';

                switch($pay_type) {
                    case '계좌이체':
                        $bank_name = isset($resultData['BankName']) ? $resultData['BankName'] : '';
                        if($default['de_escrow_use'] == 1) $escw_yn = 'Y';
                        break;
                    case '가상계좌':
                        $bankname   = isset($resultData['VbankBankName']) ? $resultData['VbankBankName'] : '';
                        $account    = $resultData['VbankNum'].' '.$resultData['VbankAccountName'];
                        $app_no     = $resultData['VbankNum'];
                        if($default['de_escrow_use'] == 1) $escw_yn = 'Y';
                        break;
                    default:
                        break;
                }

                // 결제완료
                $nicepay_result = true;
            } else {
                $s = '(오류코드:'.auto_convert_charset($resultData['ResultCode'], 'UTF-8').') '.auto_convert_charset($resultData['ResultMsg'], 'UTF-8');
                alert($s, $page_return_url);
            }
        } catch (Exception $e) {
            $s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
            echo $s;
        }

    } else {
        //#############
        // 인증 실패시
        //#############
        echo "<br/>";
        echo "####인증실패####";

        ob_start();
        var_dump($_REQUEST);
        $debug_msg = ob_get_contents();
        ob_clean();

        echo "<pre>" . strip_tags($debug_msg) . "</pre>";
    }
    
} catch (Exception $e) {
    $s = $e->getMessage() . ' (오류코드:' . $e->getCode() . ')';
    echo $s;
}

if( !$nicepay_result ){
    die("<br><br>결제 에러가 일어났습니다. 에러 이유는 위와 같습니다.");
}
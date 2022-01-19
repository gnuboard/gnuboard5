<?php
include_once('./_common.php');
include_once(G5_MSHOP_PATH.'/settle_nicepay.inc.php');

set_session('P_TID', '');
set_session('P_AMT', '');
set_session('P_HASH', '');

$oid = isset($_REQUEST['Moid']) ? trim($_REQUEST['Moid']) : '';
$auth_result_code = isset($_REQUEST['AuthResultCode']) ? $_REQUEST['AuthResultCode'] : '';
$auth_result_msg = isset($_REQUEST['AuthResultMsg']) ? trim($_REQUEST['AuthResultMsg']) : '';

$sql = " select * from {$g5['g5_shop_order_data_table']} where od_id = '$oid' ";
$row = sql_fetch($sql);

$data = isset($row['dt_data']) ? unserialize(base64_decode($row['dt_data'])) : array();

if(isset($data['pp_id']) && $data['pp_id']) {
    $order_action_url = G5_HTTPS_MSHOP_URL.'/personalpayformupdate.php';
    $page_return_url  = G5_SHOP_URL.'/personalpayform.php?pp_id='.$data['pp_id'];
} else {
    $order_action_url = G5_HTTPS_MSHOP_URL.'/orderformupdate.php';
    $page_return_url  = G5_SHOP_URL.'/orderform.php';
    if(get_session('ss_direct'))
        $page_return_url .= '?sw_direct=1';

    // 장바구니가 비어있는가?
    if (get_session('ss_direct'))
        $tmp_cart_id = get_session('ss_cart_direct');
    else
        $tmp_cart_id = get_session('ss_cart_id');

    if (get_cart_count($tmp_cart_id) == 0)// 장바구니에 담기
        alert('세션을 잃거나 다른 브라우저에서 데이터가 변경된 경우입니다. 장바구니 상태를 확인후에 다시 시도해 주세요.', G5_SHOP_URL.'/cart.php');

    $error = "";
    // 장바구니 상품 재고 검사
    $sql = " select it_id,
                    ct_qty,
                    it_name,
                    io_id,
                    io_type,
                    ct_option
               from {$g5['g5_shop_cart_table']}
              where od_id = '$tmp_cart_id'
                and ct_select = '1' ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++)
    {
        // 상품에 대한 현재고수량
        if($row['io_id']) {
            $it_stock_qty = (int)get_option_stock_qty($row['it_id'], $row['io_id'], $row['io_type']);
        } else {
            $it_stock_qty = (int)get_it_stock_qty($row['it_id']);
        }
        // 장바구니 수량이 재고수량보다 많다면 오류
        if ($row['ct_qty'] > $it_stock_qty)
            $error .= "{$row['ct_option']} 의 재고수량이 부족합니다. 현재고수량 : $it_stock_qty 개\\n\\n";
    }

    if($i == 0)
        alert('장바구니가 비어 있습니다.', G5_SHOP_URL.'/cart.php');

    if ($error != "")
    {
        $error .= "결제진행이 중단 되었습니다.";
        alert($error, G5_SHOP_URL.'/cart.php');
    }
}

if(strcmp('0000', $auth_result_code) !== 0) {
    alert('오류 : '.iconv_utf8($auth_result_msg).' 코드 : '.$auth_result_code, $page_return_url);
} else {
    $nicepay->m_ActionType      = 'PYO';
    $nicepay->m_Price           = $_REQUEST['Amt'];
    $nicepay->m_NetCancelAmt    = $_REQUEST['Amt'];
    
    /*
    *******************************************************
    * <결제 결과 필드>
    *******************************************************
    */

    $nicepay->m_BuyerName     = $_REQUEST['BuyerName'];             // 구매자명
    $nicepay->m_BuyerEmail    = $_REQUEST['BuyerEmail'];            // 구매자이메일
    $nicepay->m_BuyerTel      = $_REQUEST['BuyerTel'];              // 구매자연락처
    $nicepay->m_GoodsName     = iconv_utf8($_REQUEST['GoodsName']); // 상품명
    $nicepay->m_GoodsCnt      = $_REQUEST['GoodsCnt'];              // 상품개수
    $nicepay->m_GoodsCl       = $_REQUEST['GoodsCl'];               // 실물 or 컨텐츠
    $nicepay->m_PayMethod     = $_REQUEST['PayMethod'];             // 결제수단
    $nicepay->m_Moid          = $_REQUEST['Moid'];                  // 주문번호
    $nicepay->m_MallUserID    = $_REQUEST['MallUserID'];            // 회원사ID
    $nicepay->m_MID           = $_REQUEST['MID'];                   // MID
    $nicepay->m_MallIP        = $_REUQEST['MallIP'];                // Mall IP
    $nicepay->m_LicenseKey    = $nicepay->m_MerchantKey;            // 상점키
    $nicepay->m_TrKey         = $_REQIEST['TrKey'];                 // 거래키
    $nicepay->m_TransType     = $_REQUEST['TransType'];             // 일반 or 에스크로
    $nicepay->m_PayMethod     = $_REQUEST['PayMethod'];             // 거래수단

    $nicepay->startAction();

    $resultCode = $nicepay->m_ResultData["ResultCode"];
    $payMethod  = $nicepay->m_ResultData["PayMethod"];

    $paySuccess = false;

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

    $resultData = $nicepay->m_ResultData;

    if($paySuccess == false)
        alert('오류 : '.$resultData['ResultMsg'].' 코드 : '.$resultData['ResultCode'], $page_return_url);

    $hash = md5($resultData['TID'].$resultData['MID'].$resultData['Amt']);
    set_session('P_TID', $resultData['TID']);
    set_session('P_AMT', $resultData['Amt']);
    set_session('P_HASH', $hash);
}

print_r2(1234);
exit;

$params = array();

//개인결제
if(isset($data['pp_id']) && !empty($data['pp_id'])) {
    // 개인결제 정보
} else {
    // 상점 결제
    $exclude = array('PayMethod', 'MID', 'Amt', 'BuyerName', 'BuyerTel', 'BuyerEmail', 'GoodsName', 'Moid', 'MallReserved', 'EdiDate', 'MallIP', 'GoodsCnt', 'TransType', 'SupplyAmt', 'GoodsVat', 'ServiceAmt', 'TaxFreeAmt', 'AuthResultCode', 'AuthResultMsg', 'TrKey', 'TxTid', 'MallUserID', 'VbankExpDate', 'TID');

    foreach($data as $key=>$value) {
        if(!empty($exclude) && in_array($key, $exclude))
            continue;

        if(is_array($value)) {
            foreach($value as $k=>$v) {
                $_REQUEST[$key][$k] = $params[$key][$k] = clean_xss_tags(strip_tags($v));
            }
        } else {
            $_REQUEST[$key] = $params[$key] = clean_xss_tags(strip_tags($value));
        }
    }

    $result_code = $_POST['Auth'] = isset($PAY['P_STATUS']) ? $PAY['P_STATUS'] : '';
    $P_HASH = $_POST['P_HASH'] = $hash;
    $P_TYPE = $_POST['P_TYPE'] = isset($PAY['P_TYPE']) ? $PAY['P_TYPE'] : '';
    $P_AUTH_DT = $_POST['P_AUTH_DT'] = isset($PAY['P_AUTH_DT']) ? $PAY['P_AUTH_DT'] : '';
    $P_AUTH_NO = $_POST['P_AUTH_NO'] = isset($PAY['P_AUTH_NO']) ? $PAY['P_AUTH_NO'] : '';
    $P_HPP_CORP = $_POST['P_HPP_CORP'] = isset($PAY['P_HPP_CORP']) ? $PAY['P_HPP_CORP'] : '';
    $P_APPL_NUM = $_POST['P_APPL_NUM'] = isset($PAY['P_APPL_NUM']) ? $PAY['P_APPL_NUM'] : '';
    $P_VACT_NUM = $_POST['P_VACT_NUM'] = isset($PAY['P_VACT_NUM']) ? $PAY['P_VACT_NUM'] : '';
    $P_VACT_NAME = $_POST['P_VACT_NAME'] = isset($PAY['P_VACT_NAME']) ? iconv_utf8($PAY['P_VACT_NAME']) : '';
    $P_VACT_BANK = $_POST['P_VACT_BANK'] = (isset($PAY['P_VACT_BANK_CODE']) && isset($BANK_CODE[$PAY['P_VACT_BANK_CODE']])) ? $BANK_CODE[$PAY['P_VACT_BANK_CODE']] : '';
    // $P_CARD_ISSUER = $_POST['P_CARD_ISSUER'] = isset($CARD_CODE[$PAY['P_CARD_ISSUER_CODE']]) ? $CARD_CODE[$PAY['P_CARD_ISSUER_CODE']] : '';
    $P_CARD_ISSUER = $_POST['P_CARD_ISSUER'] = isset($CARD_CODE[$PAY['P_FN_CD1']]) ? $CARD_CODE[$PAY['P_FN_CD1']] : '';
    $P_UNAME = $_POST['P_UNAME'] = isset($PAY['P_UNAME']) ? iconv_utf8($PAY['P_UNAME']) : '';

    $check_keys = array('od_name', 'od_tel', 'od_pwd', 'od_hp', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon', 'od_email', 'ad_default', 'ad_subject', 'od_hope_date', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon', 'od_memo', 'od_settle_case', 'max_temp_point', 'od_temp_point', 'od_send_cost', 'od_send_cost2', 'od_bank_account', 'od_deposit_name', 'od_test', 'od_ip');

    foreach($check_keys as $key){
        $$key = isset($params[$key]) ? $params[$key] : '';
    }

    include_once( G5_MSHOP_PATH.'/orderformupdate.php' );
}
exit;

?>
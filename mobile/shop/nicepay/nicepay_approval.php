<?php
include_once('./_common.php');
include_once(G5_MSHOP_PATH.'/settle_nicepay.inc.php');

/* 모바일 결제시, 반환되는 파라미터를 통한 결제 이어서 진행 */
set_session('NICE_TID', '');
set_session('NICE_AMT', '');
set_session('NICE_HASH', '');

$oid = isset($_REQUEST['Moid']) ? trim($_REQUEST['Moid']) : '';                                     // 주문번호 확인
$auth_result_code = isset($_REQUEST['AuthResultCode']) ? $_REQUEST['AuthResultCode'] : '';          // 결제인증코드 확인
$auth_result_msg = isset($_REQUEST['AuthResultMsg']) ? trim($_REQUEST['AuthResultMsg']) : '';       // 결제인증메시지 확인

// 주문데이터 조회
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
    $nicepay->m_ActionType      = 'PYO';                            // 결제타입
    $nicepay->m_Price           = $_REQUEST['Amt'];                 // 가격
    $nicepay->m_NetCancelAmt    = $_REQUEST['Amt'];                 // 취소가격
    
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

    // 나이스페이 결제 프로세스 진행
    $nicepay->startAction();

    $resultCode = $nicepay->m_ResultData["ResultCode"];             // 결과코드
    $payMethod  = $nicepay->m_ResultData["PayMethod"];              // 결제수단

    $paySuccess = false;

    // 결제승인 성공 확인
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

    $resultData = $nicepay->m_ResultData;                           // 결제성공확인

    if($paySuccess == false)
        alert('오류 : '.$resultData['ResultMsg'].' 코드 : '.$resultData['ResultCode'], $page_return_url);

    $hash = md5($resultData['TID'].$resultData['MID'].$resultData['Amt']);

    set_session('NICE_TID', $resultData['TID']);
    set_session('NICE_AMT', $resultData['Amt']);
    set_session('NICE_HASH', $hash);
}

$params = array();

//개인결제
if(isset($data['pp_id']) && !empty($data['pp_id'])) {
    // 개인결제 정보
    $pp_check = false;
    $sql = " select * from {$g5['g5_shop_personalpay_table']} where pp_id = '{$resultData['P_OID']}' and pp_tno = '{$resultData['P_TID']}' and pp_use = '1' ";
    $pp = sql_fetch($sql);

    if( !$pp['pp_tno'] && $data['pp_id'] == $oid ){
        $res_cd = $resultData['ResultCode'];
        $pp_id = $oid;

        $exclude = array('PayMethod', 'MID', 'Amt', 'BuyerName', 'BuyerTel', 'BuyerEmail', 'GoodsName', 'Moid', 'MallReserved', 'EdiDate', 'MallIP', 'GoodsCnt', 'TransType', 'SupplyAmt', 'GoodsVat', 'ServiceAmt', 'TaxFreeAmt', 'AuthResultCode', 'AuthResultMsg', 'TrKey', 'TxTid', 'MallUserID', 'VbankExpDate', 'TID');

        foreach($data as $key=>$v) {
            if( !in_array($key, $exclude) ){
                $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($v));
            }
        }

        $good_mny = isset($resultData['Amt']) ? $resultData['Amt'] : 0;
        $pp_name = clean_xss_tags($data['pp_name']);
        $pp_email = clean_xss_tags($data['pp_email']);
        $pp_hp = clean_xss_tags($data['pp_hp']);
        $pp_settle_case = clean_xss_tags($data['pp_settle_case']);

        $_POST['NICE_HASH'] = $hash;
        $_POST['AuthResultCode'] = isset($resultData['AuthResultCode']) ? $resultData['AuthResultCode'] : '';
        $_POST['pp_id'] = isset($resultData['Moid']) ? $resultData['Moid'] : '';
        $_POST['good_mny'] = isset($resultData['Amt']) ? $resultData['Amt'] : 0;
        $_POST['PayMethod'] = isset($resultData['PayMethod']) ? $resultData['PayMethod'] : '';
        $_POST['AuthDate'] = isset($resultData['AuthDate']) ? $resultData['AuthDate'] : '';
        $_POST['Carrier'] = isset($resultData['Carrier']) ? $resultData['Carrier'] : '';
        $_POST['DstAddr'] = isset($resultData['DstAddr']) ? $resultData['DstAddr'] : '';
        $_POST['VbankNum'] = isset($resultData['VbankNum']) ? $resultData['VbankNum'] : '';
        $_POST['VbankAccountName'] = isset($resultData['VbankAccountName']) ? $resultData['VbankAccountName'] : '';
        $_POST['VbankBankName'] = isset($resultData['VbankBankName']) ? $resultData['VbankBankName'] : '';

        include_once( G5_MSHOP_PATH.'/personalpayformupdate.php' );
    }
} else {
    // 상점 결제
    $exclude = array('PayMethod', 'MID', 'Amt', 'BuyerName', 'BuyerTel', 'BuyerEmail', 'GoodsName', 'Moid', 'MallReserved', 'EdiDate', 'MallIP', 'GoodsCnt', 'TransType', 'SupplyAmt', 'GoodsVat', 'ServiceAmt', 'TaxFreeAmt', 'AuthResultCode', 'AuthResultMsg', 'TrKey', 'TxTid', 'MallUserID', 'VbankExpDate', 'TID');

    // DB에서 조회한 주문데이터를 기준으로 변수의 $_POST 형식으로 적용
    foreach($data as $key=>$value) {
        if(!empty($exclude) && in_array($key, $exclude))
            continue;

        if(is_array($value)) {
            foreach($value as $k=>$v) {
                $_POST[$key][$k] = $params[$key][$k] = clean_xss_tags(strip_tags($v));
            }
        } else {
            $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($value));
        }
    }

    $_POST['NICE_HASH'] = $hash;
    foreach($resultData as $key => $var) {
        $_POST[$key] = $params[$key] = $var;
    }

    $check_keys = array('od_name', 'od_tel', 'od_pwd', 'od_hp', 'od_zip', 'od_addr1', 'od_addr2', 'od_addr3', 'od_addr_jibeon', 'od_email', 'ad_default', 'ad_subject', 'od_hope_date', 'od_b_name', 'od_b_tel', 'od_b_hp', 'od_b_zip', 'od_b_addr1', 'od_b_addr2', 'od_b_addr3', 'od_b_addr_jibeon', 'od_memo', 'od_settle_case', 'max_temp_point', 'od_temp_point', 'od_send_cost', 'od_send_cost2', 'od_bank_account', 'od_deposit_name', 'od_test', 'od_ip');

    foreach($check_keys as $key){
        $$key = isset($params[$key]) ? $params[$key] : '';
    }

    include_once( G5_MSHOP_PATH.'/orderformupdate.php' );
}
exit;

?>
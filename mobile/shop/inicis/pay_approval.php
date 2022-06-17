<?php
include_once('./_common.php');
include_once(G5_MSHOP_PATH.'/settle_inicis.inc.php');

// 세션 초기화
set_session('P_TID',  '');
set_session('P_AMT',  '');
set_session('P_HASH', '');

$oid  = isset($_REQUEST['P_NOTI']) ? trim($_REQUEST['P_NOTI']) : '';
$p_req_url = isset($_REQUEST['P_REQ_URL']) ? is_inicis_url_return(trim($_REQUEST['P_REQ_URL'])) : '';
$p_status = isset($_REQUEST['P_STATUS']) ? trim($_REQUEST['P_STATUS']) : '';
$p_tid = isset($_REQUEST['P_TID']) ? trim($_REQUEST['P_TID']) : '';
$p_rmesg1 = isset($_REQUEST['P_RMESG1']) ? trim($_REQUEST['P_RMESG1']) : '';

if( ! $p_req_url || !preg_match('/^https\:\/\//i', $p_req_url)){
    alert("잘못된 요청 URL 입니다.");
}

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

if($p_status !== '00') {
    alert('오류 : '.iconv_utf8($p_rmesg1).' 코드 : '.$p_status, $page_return_url);
} else {
    $post_data = array(
        'P_MID' => $default['de_inicis_mid'],
        'P_TID' => $p_tid
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_PORT, 443);
    curl_setopt($ch, CURLOPT_URL, $p_req_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $return = curl_exec($ch);

    if(!$return)
        alert('KG이니시스와 통신 오류로 결제등록 요청을 완료하지 못했습니다.\\n결제등록 요청을 다시 시도해 주십시오.', $page_return_url);

    // 결과를 배열로 변환
    parse_str($return, $ret);
    $PAY = array_map('trim', $ret);
    $PAY = array_map('strip_tags', $PAY);
    $PAY = array_map('get_search_string', $PAY);

    if($PAY['P_STATUS'] != '00')
        alert('오류 : '.iconv_utf8($PAY['P_RMESG1']).' 코드 : '.$PAY['P_STATUS'], $page_return_url);

    // TID, AMT 를 세션으로 주문완료 페이지 전달
    $hash = md5($PAY['P_TID'].$PAY['P_MID'].$PAY['P_AMT']);
    set_session('P_TID',  $PAY['P_TID']);
    set_session('P_AMT',  $PAY['P_AMT']);
    set_session('P_HASH', $hash);
}

$params = array();

//개인결제
if(isset($data['pp_id']) && !empty($data['pp_id'])) {
    // 개인결제 정보
    $pp_check = false;
    $sql = " select * from {$g5['g5_shop_personalpay_table']} where pp_id = '{$PAY['P_OID']}' and pp_tno = '{$PAY['P_TID']}' and pp_use = '1' ";
    $pp = sql_fetch($sql);

    if( !$pp['pp_tno'] && $data['pp_id'] == $oid ){
        $res_cd = $PAY['P_STATUS'];
        $pp_id = $oid;

        $exclude = array('res_cd', 'P_HASH', 'P_TYPE', 'P_AUTH_DT', 'P_VACT_BANK', 'LGD_PAYKEY', 'pp_id', 'good_mny', 'pp_name', 'pp_email', 'pp_hp', 'pp_settle_case');

        foreach($data as $key=>$v) {
            if( !in_array($key, $exclude) ){
                $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($v));
            }
        }

        $good_mny = isset($PAY['P_AMT']) ? $PAY['P_AMT'] : 0;
        $pp_name = clean_xss_tags($data['pp_name']);
        $pp_email = clean_xss_tags($data['pp_email']);
        $pp_hp = clean_xss_tags($data['pp_hp']);
        $pp_settle_case = clean_xss_tags($data['pp_settle_case']);

        $_POST['P_HASH'] = $hash;
        $_POST['P_AUTH_NO'] = isset($PAY['P_AUTH_NO']) ? $PAY['P_AUTH_NO'] : '';
        $_POST['pp_id'] = isset($PAY['P_OID']) ? $PAY['P_OID'] : '';
        $_POST['good_mny'] = isset($PAY['P_AMT']) ? $PAY['P_AMT'] : 0;

        $_POST['P_TYPE'] = isset($PAY['P_TYPE']) ? $PAY['P_TYPE'] : '';
        $_POST['P_AUTH_DT'] = isset($PAY['P_AUTH_DT']) ? $PAY['P_AUTH_DT'] : '';
        $_POST['P_HPP_CORP'] = isset($PAY['P_HPP_CORP']) ? $PAY['P_HPP_CORP'] : '';
        $_POST['P_APPL_NUM'] = isset($PAY['P_APPL_NUM']) ? $PAY['P_APPL_NUM'] : '';
        $_POST['P_VACT_NUM'] = isset($PAY['P_VACT_NUM']) ? $PAY['P_VACT_NUM'] : '';
        $_POST['P_VACT_NAME'] = isset($PAY['P_VACT_NAME']) ? iconv_utf8($PAY['P_VACT_NAME']) : '';
        $_POST['P_VACT_BANK'] = (isset($PAY['P_VACT_BANK_CODE']) && isset($BANK_CODE[$PAY['P_VACT_BANK_CODE']])) ? $BANK_CODE[$PAY['P_VACT_BANK_CODE']] : '';
        $_POST['P_CARD_ISSUER'] = isset($CARD_CODE[$PAY['P_CARD_ISSUER_CODE']]) ? $CARD_CODE[$PAY['P_CARD_ISSUER_CODE']] : '';
        $_POST['P_UNAME'] = isset($PAY['P_UNAME']) ? iconv_utf8($PAY['P_UNAME']) : '';

        include_once( G5_MSHOP_PATH.'/personalpayformupdate.php' );
    }

} else {
    // 상점 결제
    $exclude = array('res_cd', 'P_HASH', 'P_TYPE', 'P_AUTH_DT', 'P_VACT_BANK', 'P_AUTH_NO');

    foreach($data as $key=>$value) {
        if(!empty($exclude) && in_array($key, $exclude))
            continue;

        if(is_array($value)) {
            foreach($value as $k=>$v) {
                $_POST[$key][$k] = $params[$key][$k] = clean_xss_tags(strip_tags($v));
            }
        } else {
            if(in_array($key, array('od_memo'))){
                $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($value), 0, 0, 0, 0);
            } else {
                $_POST[$key] = $params[$key] = clean_xss_tags(strip_tags($value));
            }
        }
    }

    $res_cd = $_POST['res_cd'] = isset($PAY['P_STATUS']) ? $PAY['P_STATUS'] : '';
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
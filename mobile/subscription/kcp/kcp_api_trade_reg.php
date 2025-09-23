<?php
define('IS_SUBSCRIPTION_ORDER_FORM', 1);
include_once('./_common.php');

if (!$is_member) {
    goto_url(G5_SHOP_URL);
}

include_once(G5_MSUBSCRIPTION_PATH . '/settle_kcp.inc.php');       // 환경설정 파일 include

if (get_subs_option('su_card_test')) {
    $target_URL = 'https://testsmpay.kcp.co.kr/trade/register.do'; // 개발서버 (배치키 발급)
} else {
    $target_URL = 'https://smpay.kcp.co.kr/trade/register.do'; // 운영서버 (배치키 발급)
}

$site_cd            = get_subs_option('su_kcp_mid');
$ordr_idxx          = isset($_POST['ordr_idxx']) ? clean_xss_tags($_POST['ordr_idxx'], 1, 1) : '';
$good_mny           = isset($_POST['good_mny']) ? (int) $_POST['good_mny'] : '';
$good_name          = isset($_POST['good_name']) ? clean_xss_tags($_POST['good_name'], 1, 1) : '';
$pay_method         = "AUTH";
$Ret_URL            = G5_MSUBSCRIPTION_URL.'/kcp/order_mobile.php';

$param_opt_1 = isset($_REQUEST['param_opt_1']) ? clean_xss_tags($_REQUEST['param_opt_1'], 1, 1) : '';
$param_opt_2 = isset($_REQUEST['param_opt_2']) ? clean_xss_tags($_REQUEST['param_opt_2'], 1, 1) : '';
$param_opt_3 = isset($_REQUEST['param_opt_3']) ? clean_xss_tags($_REQUEST['param_opt_3'], 1, 1) : '';

// 임시주문테이블 확인
$sql = " select * from {$g5['g5_subscription_order_data_table']} where od_id = '$ordr_idxx' ";
$row = sql_fetch($sql);

$return_url = G5_SUBSCRIPTION_URL.'/orderform.php';

if (get_session('subs_direct')) {
    $return_url .= '?sw_direct=1';
}
    
if (!(isset($row['od_id']) && $row['od_id'])) {
    alert('해당 주문은 만료되었거나 더 이상 진행할수 없는 주문입니다.', G5_SUBSCRIPTION_URL.'/subscription_list.php');
}

if ($member['mb_id'] !== $row['mb_id']) {
    
    alert('해당 정기결제의 회원과 유효하지 않습니다.', $return_url);
}
        
$data = array(
    "site_cd"        => $site_cd,
    "ordr_idxx"      => $ordr_idxx,
    "good_mny"       => $good_mny,
    "good_name"      => $good_name,
    "pay_method"     => $pay_method,
    "Ret_URL"        => $Ret_URL,
    "user_agent"     => ""
);

$req_data = json_encode($data);

$header_data = array( "Content-Type: application/json", "charset=utf-8" );

// API REQ
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $req_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// API RES
$res_data  = curl_exec($ch); 

// RES JSON DATA Parsing
$json_res = json_decode($res_data, true);

$Code        = isset($json_res["Code"]) ? $json_res["Code"] : '';
$Message     = isset($json_res["Message"]) ? $json_res["Message"] : '';
$approvalKey = isset($json_res["approvalKey"]) ? $json_res["approvalKey"] : '';
$traceNo     = isset($json_res["traceNo"]) ? $json_res["traceNo"] : '';
$PayUrl      = isset($json_res["PayUrl"]) ? $json_res["PayUrl"] : '';

curl_close($ch); 

if ($Code !== '0000') {
    alert("거래등록 실패\nCode : {$Code}\nMessage : {$Message}");
    die('');
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>NHN KCP API 결제</title>
	<meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=yes, target-densitydpi=medium-dpi">  
    <script type="text/javascript">
        function goReq()
        {
            document.form_trade_reg.action = "order_mobile.php";
            document.form_trade_reg.submit();
        }
    </script>
</head>
<body onload="goReq();">
    <div class="wrap">
        <form name="form_trade_reg" method="post">
            <input type="hidden" name="site_cd"         value="<?php echo sanitize_input($site_cd); ?>" />
            <input type="hidden" name="ordr_idxx"       value="<?php echo sanitize_input($ordr_idxx); ?>" />
            <input type="hidden" name="good_mny"        value="<?php echo sanitize_input($good_mny); ?>" />
            <input type="hidden" name="good_name"       value="<?php echo sanitize_input($good_name); ?>" />
            <input type="hidden" name="pay_method"      value="<?php echo sanitize_input($pay_method); ?>" />
            <input type="hidden" name="Ret_URL"         value="<?php echo sanitize_input($Ret_URL); ?>" />
            <input type="hidden" name="approvalKey"     value="<?php echo sanitize_input($approvalKey); ?>" />
            <input type="hidden" name="traceNo"         value="<?php echo sanitize_input($traceNo); ?>" />
            <input type="hidden" name="PayUrl"          value="<?php echo sanitize_input($PayUrl); ?>" />
            <input type="hidden" name="param_opt_1"  value="<?php echo sanitize_input($param_opt_1); ?>"/>
            <input type="hidden" name="param_opt_2"  value="<?php echo sanitize_input($param_opt_2); ?>"/>
            <input type="hidden" name="param_opt_3"  value="<?php echo sanitize_input($param_opt_3); ?>"/>
        </form>
    </div>
</body>
</html>
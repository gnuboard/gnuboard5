<?php
define('IS_SUBSCRIPTION_ORDER_FORM', 1);
include_once('./_common.php');

if (!$is_member) {
    goto_url(G5_SHOP_URL);
}

include_once(G5_MSUBSCRIPTION_PATH . '/settle_kcp.inc.php');       // 환경설정 파일 include

$approvalKey    = isset($_POST["approvalKey"]) ? clean_xss_tags($_POST["approvalKey"], 1, 1) : '';
$PayUrl         = isset($_POST["PayUrl"])      ? clean_xss_tags($_POST["PayUrl"], 1, 1)      : '';
$Ret_URL        = isset($_POST["Ret_URL"])     ? clean_xss_tags($_POST["Ret_URL"], 1, 1)     : '';
$pay_method     = isset($_POST["pay_method"])  ? clean_xss_tags($_POST["pay_method"], 1, 1)  : '';
$ordr_idxx      = isset($_POST["ordr_idxx"])   ? clean_xss_tags($_POST["ordr_idxx"], 1, 1)   : '';
$good_name      = isset($_POST["good_name"])   ? clean_xss_tags($_POST["good_name"], 1, 1)   : '';
$good_mny       = isset($_POST["good_mny"])    ? (int) $_POST["good_mny"]    : '';

$res_cd         = isset($_POST["res_cd"])      ? preg_replace('/[^a-zA-Z0-9_]/', '', $_POST["res_cd"])      : '';
$tran_cd        = isset($_POST["tran_cd"])     ? $_POST["tran_cd"]     : '';
$enc_info       = isset($_POST["enc_info"])    ? $_POST["enc_info"]    : '';
$enc_data       = isset($_POST["enc_data"])    ? $_POST["enc_data"]    : '';
$card_mask_no   = isset($_POST["card_mask_no"])    ? mask_card_number($_POST["card_mask_no"])    : '';

$param_opt_1 = isset($_REQUEST['param_opt_1']) ? clean_xss_tags($_REQUEST['param_opt_1'], 1, 1) : '';
$param_opt_2 = isset($_REQUEST['param_opt_2']) ? clean_xss_tags($_REQUEST['param_opt_2'], 1, 1) : '';
$param_opt_3 = isset($_REQUEST['param_opt_3']) ? clean_xss_tags($_REQUEST['param_opt_3'], 1, 1) : '';

$return_url = G5_SUBSCRIPTION_URL.'/orderform.php';
if (get_session('subs_direct')) {
    $return_url .= '?sw_direct=1';
}
$order_action_url = G5_HTTPS_MSUBSCRIPTION_URL . '/orderformupdate.php';

// 임시주문테이블 확인
$sql = " select * from {$g5['g5_subscription_order_data_table']} where od_id = '$ordr_idxx' ";
$row = sql_fetch($sql);
    
if (!(isset($row['od_id']) && $row['od_id'])) {
    alert('해당 주문은 만료되었거나 더 이상 진행할수 없는 주문입니다.', G5_SUBSCRIPTION_URL.'/subscription_list.php');
}

if ($member['mb_id'] !== $row['mb_id']) {
    alert('해당 정기결제의 회원과 유효하지 않습니다.', $return_url);
}

?>

<!DOCTYPE html>
<html>
<head>
    <title>NHN KCP Mobile_AutoPayment API 결제</title>
	<meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=yes, target-densitydpi=medium-dpi">  
    <script type="text/javascript">
        /* kcp web 결제창 호츨 (변경불가) */
        function call_pay_form()
        {
            var v_frm = document.order_info;
            var PayUrl = v_frm.PayUrl.value;
            // 인코딩 방식에 따른 변경 -- Start
            if(v_frm.encoding_trans == undefined)
            {
                v_frm.action = PayUrl;
            }
            else
            {
                // encoding_trans "UTF-8" 인 경우
                if(v_frm.encoding_trans.value == "UTF-8")
                {
                    v_frm.action = PayUrl.substring(0,PayUrl.lastIndexOf("/"))  + "/jsp/encodingFilter/encodingFilter.jsp";
                    v_frm.PayUrl.value = PayUrl;
                }
                else
                {
                    v_frm.action = PayUrl;
                }
            }
            v_frm.submit();
        }
        
        /* kcp 통신을 통해 받은 암호화 정보 체크 후 결제 요청 (변경불가) */
        function chk_pay()
        {
            self.name = "tar_opener";
            var res_cd = "<?php echo sanitize_input($res_cd); ?>";
            if (res_cd != "" && res_cd != "0000")
            {   
                /* 결제인증 실패에 대한 처리 */
                if (res_cd == "3001")
                {
                    alert("사용자가 취소하였습니다.");
                }
                res_cd = "";
                location.href = "<?php echo $return_url; ?>"; // 주문 페이지로 이동
            }
            
            /* 결제인증 완료 후 결제승인 요청을 위한 비즈니스 로직 구현 */ 
            var pay_form = document.pay_form;
            if (pay_form.enc_info.value && pay_form.enc_data.value) {
                pay_form.submit();
                return false;
            } else {
                call_pay_form();
            }
        }
    </script>
</head>
<body onload="chk_pay();">
    <form name="order_info" method="post" >
        <input type="hidden" name="ordr_idxx" value="<?php echo sanitize_input($ordr_idxx); ?>" />
        <input type="hidden" name="good_name" value="<?php echo sanitize_input($good_name); ?>" />
        <input type="hidden" name="good_mny" value="<?php echo sanitize_input($good_mny); ?>" />
        <input type="hidden" name="kcp_group_id" value="<?php echo get_subs_option('su_kcp_group_id'); ?>" />
        <input type="hidden" name="shop_name"       value="<?php echo sanitize_input($g_conf_site_name); ?>">
        <input type="hidden" name="site_cd"         value="<?php echo get_subs_option('su_kcp_mid'); ?>">
        <input type="hidden" name="currency"        value="410"/>    
        <input type="hidden" name="pay_method"      value="<?php echo sanitize_input($pay_method); ?>">
        <input type="hidden" name="ActionResult"    value="batch">
        <!-- 리턴 URL (kcp와 통신후 요청할 수 있는 암호화 데이터를 전송 받을 가맹점의 주문페이지 URL) -->
        <input type="hidden" name="Ret_URL"         value="<?php echo sanitize_input($Ret_URL); ?>"> 
        <!-- 인증창 호출 시 한글깨질 경우 encoding 처리 추가 (**인코딩 네임은 대문자) -->
        <input type="hidden" name="encoding_trans"     value="UTF-8">

        <!-- 거래등록 응답값 -->
        <input type="hidden" id="approval"          name="approval_key"   value="<?php echo sanitize_input($approvalKey); ?>">
        <input type="hidden" name="PayUrl"          value="<?php echo sanitize_input($PayUrl); ?>">
        
        <!-- 추가 파라미터 -->
        <input type="hidden" name="param_opt_1" value="<?php echo sanitize_input($param_opt_1); ?>">
        <input type="hidden" name="param_opt_2" value="<?php echo sanitize_input($param_opt_2); ?>">
        <input type="hidden" name="param_opt_3" value="<?php echo sanitize_input($param_opt_3); ?>">
            
        <input type="hidden" name="batch_cardno_return_yn" value="Y" >
    </form>
    
    <?php
        // 제외할 필드
        $exclude = array('req_tx', 'res_cd', 'tran_cd', 'ordr_idxx', 'good_mny', 'good_name', 'buyr_name', 'buyr_tel1', 'buyr_tel2', 'buyr_mail', 'enc_info', 'enc_data', 'use_pay_method', 'rcvr_name', 'rcvr_tel1', 'rcvr_tel2', 'rcvr_mail', 'rcvr_zipx', 'rcvr_add1', 'rcvr_add2', 'param_opt_1', 'param_opt_2', 'param_opt_3');

        $data = isset($row['dt_data']) ? unserialize(base64_decode($row['dt_data'])) : array();

        echo '<form name="pay_form" method="post" action="' . $order_action_url . '" autocomplete="off">' . PHP_EOL;
        
        if ($data && is_array($data)) {
            echo make_order_field($data, $exclude);
        }

        echo '<input type="hidden" name="site_cd" value="' . sanitize_input($site_cd) . '">';
        echo '<input type="hidden" name="tran_cd" value="' . sanitize_input($tran_cd) . '">';
        echo '<input type="hidden" name="enc_info" value="' . sanitize_input($enc_info) . '">';
        echo '<input type="hidden" name="enc_data" value="' . sanitize_input($enc_data) . '">';
        echo '<input type="hidden" name="card_mask_no" value="' . sanitize_input($card_mask_no) . '">';
        
        echo '<input type="hidden" name="param_opt_1" value="' . sanitize_input($param_opt_1) . '">';
        echo '<input type="hidden" name="param_opt_2" value="' . sanitize_input($param_opt_2) . '">';
        echo '<input type="hidden" name="param_opt_3" value="' . sanitize_input($param_opt_3) . '">';

        echo '</form>' . PHP_EOL;
    ?>
</body>
</html>
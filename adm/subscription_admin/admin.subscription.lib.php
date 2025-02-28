<?php
if (!defined('_GNUBOARD_')) exit;

function subscription_pg_setting_check($is_print=false){
	global $g5, $config, $member;

	$msg = '';
	$pg_msg = '';
    
    $pg_service = get_subs_option('su_pg_service');
    
    // 테스트이면
	if (get_subs_option('su_card_test')) {
		if ($pg_service === 'kcp' && get_subs_option('su_kcp_mid') && get_subs_option('su_kcp_cert_info')){
			$pg_msg = 'NHN KCP';
		} else if ($pg_service === 'tosspayments' && get_subs_option('su_tosspayments_mid') && get_subs_option('su_tosspayments_mert_key')){
			$pg_msg = 'LG유플러스';
		} else if ($pg_service === 'inicis' && get_subs_option('su_inicis_mid') && get_subs_option('su_inicis_sign_key')){
			$pg_msg = 'KG이니시스';
		} else if ($pg_service === 'nicepay' && get_subs_option('su_nice_clientid') && get_subs_option('su_nice_secretkey')){
			$pg_msg = 'NICEPAY';
		}
	}

	if( $pg_msg ){
		$pg_test_conf_link = G5_SUBSCRIPTION_ADMIN_URL.'/configform.php#de_card_test1';
		$msg .= '<div class="admin_pg_notice od_test_caution">(주의!) '.$pg_msg.' 결제의 결제 설정이 현재 테스트결제 로 되어 있습니다.<br>테스트결제시 실제 결제가 되지 않으므로, 쇼핑몰 운영중이면 반드시 실결제로 설정하여 운영하셔야 합니다.<br>아래 링크를 클릭하여 실결제로 설정하여 운영해 주세요.<br><a href="'.$pg_test_conf_link.'" class="pg_test_conf_link">'.$pg_test_conf_link.'</a></div>';
	}
	
	if( $is_print ){
		echo $msg;
	} else{
		return $msg;
	}
}

function is_cancel_subscription_pg_order($pay){

    $is_od_pg_cancel = false;

    if ($pay['py_settle_case'] == '신용카드' || strtoupper($pay['py_settle_case']) === 'CARD') {
        $is_od_pg_cancel = true;
    }

    return $is_od_pg_cancel;
}
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

function get_subscription_pay_full_goods($pay_id, $is_cache=false) {
    global $g5;
    
    static $cache = array();

    $key = md5($pay_id);

    if( $is_cache && isset($cache[$key]) ){
        return $cache[$key];
    }
    
    $goods = array(
        'full_name' => '',
        'thumb' => '',
        );
    
    // 상품명만들기
    
    $sql = " select a.it_id, b.it_name from {$g5['g5_subscription_cart_table']} a, {$g5['g5_subscription_item_table']} b where a.it_id = b.it_id and a.od_id = '$pay_id' order by ct_id ";
        
    $result = sql_query($sql);
    
    $tmp = array();
    
    for($i=0; $row=sql_fetch_array($result); $i++) {
        
        $row['thumbnail'] = get_subscription_it_image($row['it_id'], 65, 65, true);
        
        // 대표 상품명과 대표 썸네일을 지정한다.
        if ($i === 0) {
            $goods['full_name'] = preg_replace ("/[ #\&\+\-%@=\/\\\:;,\.'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", addslashes($row['it_name']));
            $goods['thumb'] = $row['thumbnail'];
        }
        
        $tmp[$i] = $row;
    }
    
    $goods['data'] = $tmp;
    $total_tmp = count($tmp);
    
    if ($tmp && $total_tmp > 1) {
        $goods['full_name'] .= ' 외 '.((int)$total_tmp - 1).'건';
    }
    
    $cache[$key] = $goods;
    
    return $cache[$key];
}

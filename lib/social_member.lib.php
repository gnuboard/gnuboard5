<?php
if (!defined('_GNUBOARD_')) exit;

function is_social_member_type($member){

    global $config, $g5;

    if( !isset($member['mb_id']) || empty($member['mb_id']) ){
        return '';
    }

    //실제 회원인지 검색
    $mb = get_member($member['mb_id'], 'mb_id');

    if( !empty($mb['mb_id']) ){
        return 'is_member';
    }

    if($oauth_mb_no = get_session('ss_oauth_member_no')) {
        return 'is_social';
    }

    return '';
}

function oauth_goto_url($msg='', $url=''){

	if( !$url ){
		$url = G5_URL;
	}

	//새창인 경우에는
    if( defined('G5_OAUTH_USE_POPUP') && G5_OAUTH_USE_POPUP ){
        alert_opener_url( $msg, $url );
    } else {
        if( $msg ){
            alert( $msg, $url );
        } else {
            goto_url( $url );
        }
    }
}

function sociallogin_used_check($type=''){
    global $config;

    if( ! $config['cf_social_login_use'] ){
        oauth_goto_url('소셜 로그인을 사용하지 않습니다.');
    }
}
?>
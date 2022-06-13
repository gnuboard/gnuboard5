<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

if (!($w == '' || $w == 'u')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

if( ! $config['cf_social_login_use'] ){
    alert('소셜 로그인을 사용하지 않습니다.', G5_URL);
}

if( $is_member ){
    alert('이미 회원가입 하였습니다.', G5_URL);
}

$provider_name = social_get_request_provider();
$user_profile = social_session_exists_check();
if( ! $user_profile ){
    alert( "소셜로그인 을 하신 분만 접근할 수 있습니다.", G5_URL);
}

// 소셜 가입된 내역이 있는지 확인 상수 G5_SOCIAL_DELETE_DAY 관련
$is_exists_social_account = social_before_join_check(G5_URL);

$sm_id = $user_profile->sid;
$mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
$mb_password    = isset($_POST['mb_password']) ? trim($_POST['mb_password']) : '';
$mb_password_re = isset($_POST['mb_password_re']) ? trim($_POST['mb_password_re']) : '';
$mb_nick        = isset($_POST['mb_nick']) ? trim(strip_tags($_POST['mb_nick'])) : '';
$mb_email       = isset($_POST['mb_email']) ? trim($_POST['mb_email']) : '';
$mb_name        = isset($_POST['mb_name']) ? clean_xss_tags(trim(strip_tags($_POST['mb_name']))) : '';
$mb_hp          = isset($_POST['mb_hp']) ? trim($_POST['mb_hp']) : '';
$mb_email       = get_email_address($mb_email);

// 이름, 닉네임에 utf-8 이외의 문자가 포함됐다면 오류
// 서버환경에 따라 정상적으로 체크되지 않을 수 있음.
$tmp_mb_name = iconv('UTF-8', 'UTF-8//IGNORE', $mb_name);
if($tmp_mb_name != $mb_name) {
    $mb_name = $tmp_mb_name;
}
$tmp_mb_nick = iconv('UTF-8', 'UTF-8//IGNORE', $mb_nick);
if($tmp_mb_nick != $mb_nick) {
    $mb_nick = $tmp_mb_nick;
}

if( ! $mb_nick || ! $mb_name ){
    $tmp = explode('@', $mb_email);
    $mb_nick = $mb_nick ? $mb_nick : $tmp[0];
    $mb_name = $mb_name ? $mb_name : $tmp[0];
    $mb_nick = exist_mb_nick_recursive($mb_nick, '');
}

if( ! isset($mb_password) || ! $mb_password ){

    $mb_password = md5(pack('V*', rand(), rand(), rand(), rand()));

}

if ($msg = valid_mb_id($mb_id))         alert($msg, "", true, true);
if ($msg = empty_mb_name($mb_name))       alert($msg, "", true, true);
if ($msg = empty_mb_nick($mb_nick))     alert($msg, "", true, true);
if ($msg = empty_mb_email($mb_email))   alert($msg, "", true, true);
if ($msg = reserve_mb_id($mb_id))       alert($msg, "", true, true);
if ($msg = reserve_mb_nick($mb_nick))   alert($msg, "", true, true);
// 이름에 한글명 체크를 하지 않는다.
//if ($msg = valid_mb_name($mb_name))     alert($msg, "", true, true);
if ($msg = valid_mb_nick($mb_nick))     alert($msg, "", true, true);
if ($msg = valid_mb_email($mb_email))   alert($msg, "", true, true);
if ($msg = prohibit_mb_email($mb_email))alert($msg, "", true, true);

if ($msg = exist_mb_id($mb_id))     alert($msg);
if ($msg = exist_mb_nick($mb_nick, $mb_id))     alert($msg, "", true, true);
if ($msg = exist_mb_email($mb_email, $mb_id))   alert($msg, "", true, true);

if( $mb = get_member($mb_id) ){
    alert("이미 등록된 회원이 존재합니다.", G5_URL);
}

$data = array(
'mb_id' =>  $mb_id,
'mb_password'   =>  get_encrypt_string($mb_password),
'mb_nick'   =>  $mb_nick,
'mb_email'  =>  $mb_email,
'mb_name'   =>  $mb_name,
);

$mb_email_certify = G5_TIME_YMDHIS;

//메일인증을 사용한다면
if( defined('G5_SOCIAL_CERTIFY_MAIL') && G5_SOCIAL_CERTIFY_MAIL && $config['cf_use_email_certify'] ){
    $mb_email_certify = '';
}

//회원 메일 동의
$mb_mailling = (isset($_POST['mb_mailling']) && $_POST['mb_mailling']) ? 1 : 0;
//회원 정보 공개
$mb_open = (isset($_POST['mb_open']) && $_POST['mb_open']) ? 1 : 0;

//===============================================================
//  본인확인
//---------------------------------------------------------------
if($config['cf_cert_use']) {
    $mb_hp = hyphen_hp_number($mb_hp);
    if($config['cf_cert_use'] && get_session('ss_cert_type') && get_session('ss_cert_dupinfo')) {
        // 중복체크
        $sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
        $row = sql_fetch($sql);
        if (!empty($row['mb_id'])) {
            alert("입력하신 본인확인 정보로 가입된 내역이 존재합니다.");
        }
    }

    $sql_certify = '';
    $md5_cert_no = get_session('ss_cert_no');
    $cert_type = get_session('ss_cert_type');
    if ($config['cf_cert_use'] && $cert_type && $md5_cert_no) {
        // 해시값이 같은 경우에만 본인확인 값을 저장한다.
        if ($cert_type == 'ipin' && get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$md5_cert_no)) { // 아이핀일때 hash 값 체크 hp미포함
            $sql_certify .= " , mb_hp = '{$mb_hp}' ";
            $sql_certify .= " , mb_certify  = '{$cert_type}' ";
            $sql_certify .= " , mb_adult = '".get_session('ss_cert_adult')."' ";
            $sql_certify .= " , mb_birth = '".get_session('ss_cert_birth')."' ";
            $sql_certify .= " , mb_sex = '".get_session('ss_cert_sex')."' ";
            $sql_certify .= " , mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
            if($w == 'u')
                $sql_certify .= " , mb_name = '{$mb_name}' ";
        } else if($cert_type != 'ipin' && get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$mb_hp.$md5_cert_no)) { // 간편인증, 휴대폰일때 hash 값 체크 hp포함
            $sql_certify .= " , mb_hp = '{$mb_hp}' ";
            $sql_certify .= " , mb_certify  = '{$cert_type}' ";
            $sql_certify .= " , mb_adult = '".get_session('ss_cert_adult')."' ";
            $sql_certify .= " , mb_birth = '".get_session('ss_cert_birth')."' ";
            $sql_certify .= " , mb_sex = '".get_session('ss_cert_sex')."' ";
            $sql_certify .= " , mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
            if($w == 'u')
                $sql_certify .= " , mb_name = '{$mb_name}' ";
        }else {
            alert('본인인증된 정보와 개인정보가 일치하지않습니다. 다시시도 해주세요');
        }
    } else {
        if (get_session("ss_reg_mb_name") != $mb_name || get_session("ss_reg_mb_hp") != $mb_hp) {
            $sql_certify .= " , mb_hp = '{$mb_hp}' ";
            $sql_certify .= " , mb_certify = '' ";
            $sql_certify .= " , mb_adult = 0 ";
            $sql_certify .= " , mb_birth = '' ";
            $sql_certify .= " , mb_sex = '' ";
        }
    }
    //===============================================================
}
// 회원정보 입력
$sql = " insert into {$g5['member_table']}
            set mb_id = '{$mb_id}',
                mb_password = '".get_encrypt_string($mb_password)."',
                mb_name = '{$mb_name}',
                mb_nick = '{$mb_nick}',
                mb_nick_date = '".G5_TIME_YMD."',
                mb_email = '{$mb_email}',
                mb_email_certify = '".$mb_email_certify."',
                mb_today_login = '".G5_TIME_YMDHIS."',
                mb_datetime = '".G5_TIME_YMDHIS."',
                mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                mb_level = '{$config['cf_register_level']}',
                mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
                mb_mailling = '{$mb_mailling}',
                mb_sms = '0',
                mb_open = '{$mb_open}',
                mb_open_date = '".G5_TIME_YMD."'
                {$sql_certify} ";
$result = sql_query($sql, false);

if($result) {
  
    if($cert_type == 'ipin' && get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$md5_cert_no)) { // 아이핀일때 hash 값 체크 hp미포함)
        insert_member_cert_history($mb_id, $mb_name, $mb_hp, get_session('ss_cert_birth'), get_session('ss_cert_type') ); // 본인인증 후 정보 수정 시 내역 기록
    }else if($cert_type != 'ipin' && get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$mb_hp.$md5_cert_no)) { // 간편인증, 휴대폰일때 hash 값 체크 hp포함
        insert_member_cert_history($mb_id, $mb_name, $mb_hp, get_session('ss_cert_birth'), get_session('ss_cert_type') ); // 본인인증 후 정보 수정 시 내역 기록
    }
    // 회원가입 포인트 부여
    insert_point($mb_id, $config['cf_register_point'], '회원가입 축하', '@member', $mb_id, '회원가입');

    // 최고관리자님께 메일 발송
    if ($config['cf_email_mb_super_admin']) {
        $subject = '['.$config['cf_title'].'] '.$mb_nick .' 님께서 회원으로 가입하셨습니다.';

        ob_start();
        include_once (G5_BBS_PATH.'/register_form_update_mail2.php');
        $content = ob_get_contents();
        ob_end_clean();

        mailer($mb_nick, $mb_email, $config['cf_admin_email'], $subject, $content, 1);
    }

    $mb = get_member($mb_id);

    //소셜 로그인 계정 추가
    if( function_exists('social_login_success_after') ){
        social_login_success_after($mb, '', 'register');
    }

    set_session('ss_mb_reg', $mb['mb_id']);

    if( !empty($user_profile->photoURL) && ($config['cf_register_level'] >= $config['cf_icon_level']) ){  //회원 프로필 사진이 있고, 회원 아이콘를 올릴수 있는 조건이면
        
        // 회원아이콘
        $mb_dir = G5_DATA_PATH.'/member/'.substr($mb_id,0,2);
        @mkdir($mb_dir, G5_DIR_PERMISSION);
        @chmod($mb_dir, G5_DIR_PERMISSION);
        $dest_path = "$mb_dir/$mb_id.gif";
        
        social_profile_img_resize($dest_path, $user_profile->photoURL, $config['cf_member_icon_width'], $config['cf_member_icon_height'] );
        
        // 회원이미지
        if( is_dir(G5_DATA_PATH.'/member_image/') ) {
            $mb_dir = G5_DATA_PATH.'/member_image/'.substr($mb_id,0,2);
            @mkdir($mb_dir, G5_DIR_PERMISSION);
            @chmod($mb_dir, G5_DIR_PERMISSION);
            $dest_path = "$mb_dir/$mb_id.gif";
            
            social_profile_img_resize($dest_path, $user_profile->photoURL, $config['cf_member_img_width'], $config['cf_member_img_height'] );
        }
    }

    if( $mb_email_certify ){    //메일인증 사용 안하면

        //바로 로그인 처리
        set_session('ss_mb_id', $mb['mb_id']);
        if(function_exists('update_auth_session_token')) update_auth_session_token(G5_TIME_YMDHIS);

    } else {    // 메일인증을 사용한다면
        $subject = '['.$config['cf_title'].'] 인증확인 메일입니다.';

        // 어떠한 회원정보도 포함되지 않은 일회용 난수를 생성하여 인증에 사용
        $mb_md5 = md5(pack('V*', rand(), rand(), rand(), rand()));

        sql_query(" update {$g5['member_table']} set mb_email_certify2 = '$mb_md5' where mb_id = '$mb_id' ");

        $certify_href = G5_BBS_URL.'/email_certify.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5;

        ob_start();
        include_once (G5_BBS_PATH.'/register_form_update_mail3.php');
        $content = ob_get_contents();
        ob_end_clean();

        mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $mb_email, $subject, $content, 1);
    }

    // 신규회원 쿠폰발생
    if($w == '' && $default['de_member_reg_coupon_use'] && $default['de_member_reg_coupon_term'] > 0 && $default['de_member_reg_coupon_price'] > 0) {
        $j = 0;
        $create_coupon = false;

        do {
            $cp_id = get_coupon_id();

            $sql3 = " select count(*) as cnt from {$g5['g5_shop_coupon_table']} where cp_id = '$cp_id' ";
            $row3 = sql_fetch($sql3);

            if(!$row3['cnt']) {
                $create_coupon = true;
                break;
            } else {
                if($j > 20)
                    break;
            }
        } while(1);

        if($create_coupon) {
            $cp_subject = '신규 회원가입 축하 쿠폰';
            $cp_method = 2;
            $cp_target = '';
            $cp_start = G5_TIME_YMD;
            $cp_end = date("Y-m-d", (G5_SERVER_TIME + (86400 * ((int)$default['de_member_reg_coupon_term'] - 1))));
            $cp_type = 0;
            $cp_price = $default['de_member_reg_coupon_price'];
            $cp_trunc = 1;
            $cp_minimum = $default['de_member_reg_coupon_minimum'];
            $cp_maximum = 0;

            $sql = " INSERT INTO {$g5['g5_shop_coupon_table']}
                        ( cp_id, cp_subject, cp_method, cp_target, mb_id, cp_start, cp_end, cp_type, cp_price, cp_trunc, cp_minimum, cp_maximum, cp_datetime )
                    VALUES
                        ( '$cp_id', '$cp_subject', '$cp_method', '$cp_target', '$mb_id', '$cp_start', '$cp_end', '$cp_type', '$cp_price', '$cp_trunc', '$cp_minimum', '$cp_maximum', '".G5_TIME_YMDHIS."' ) ";

            $res = sql_query($sql, false);

            if($res)
                set_session('ss_member_reg_coupon', 1);
        }
    }

    // 사용자 코드 실행
    @include_once ($member_skin_path.'/register_form_update.tail.skin.php');

    goto_url(G5_HTTP_BBS_URL.'/register_result.php');

} else {

    alert('회원 가입 오류!', G5_URL );

}
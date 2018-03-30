<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

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
$mb_id = trim($_POST['mb_id']);
$mb_password    = trim($_POST['mb_password']);
$mb_password_re = trim($_POST['mb_password_re']);
$mb_nick        = trim(strip_tags($_POST['mb_nick']));
$mb_email       = trim($_POST['mb_email']);
$mb_name        = clean_xss_tags(trim(strip_tags($_POST['mb_name'])));
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
}

if( ! isset($mb_password) || ! $mb_password ){

    $mb_password = md5(pack('V*', rand(), rand(), rand(), rand()));

}

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
                mb_open_date = '".G5_TIME_YMD."' ";

$result = sql_query($sql, false);

if($result) {

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

    // 사용자 코드 실행
    @include_once ($member_skin_path.'/register_form_update.tail.skin.php');

    goto_url(G5_HTTP_BBS_URL.'/register_result.php');

} else {

    alert('회원 가입 오류!', G5_URL );

}
?>
<?php
$sub_menu = "100200";
include_once('./_common.php');
include_once(G5_LIB_PATH.'/mailer.lib.php');

$au_menu = isset($_POST['au_menu']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['au_menu']) : '';
$post_r = isset($_POST['r']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['r']) : '';
$post_w = isset($_POST['w']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['w']) : '';
$post_d = isset($_POST['d']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['d']) : '';

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

$mb = get_member($mb_id);
if (!$mb['mb_id'])
    alert('존재하는 회원아이디가 아닙니다.');

check_admin_token();

include_once(G5_CAPTCHA_PATH.'/captcha.lib.php');

if (!chk_captcha()) {
    alert('자동등록방지 숫자가 틀렸습니다.');
}

$sql = " insert into {$g5['auth_table']}
            set mb_id   = '$mb_id',
                au_menu = '$au_menu',
                au_auth = '{$post_r},{$post_w},{$post_d}' ";
$result = sql_query($sql, FALSE);
if (!$result) {
    $sql = " update {$g5['auth_table']}
                set au_auth = '{$post_r},{$post_w},{$post_d}'
              where mb_id   = '$mb_id'
                and au_menu = '$au_menu' ";
    sql_query($sql);
}

//sql_query(" OPTIMIZE TABLE `$g5['auth_table']` ");

// 세션을 체크하여 하루에 한번만 메일알림이 가게 합니다.
if( str_replace('-', '', G5_TIME_YMD) !== get_session('adm_auth_update') ){
    $site_url = preg_replace('/^www\./', '', strtolower($_SERVER['SERVER_NAME']));
    $to_email = 'gnuboard@'.$site_url;

    mailer($config['cf_admin_email_name'], $to_email, $config['cf_admin_email'], '['.$config['cf_title'].'] 관리권한설정 알림', '<p><b>['.$config['cf_title'].'] 관리권한설정 변경 안내</b></p><p style="padding-top:1em">회원 아이디 '.$mb['mb_id'].' 에 관리권한이 추가 되었습니다.</p><p style="padding-top:1em">'.G5_TIME_YMDHIS.'</p><p style="padding-top:1em"><a href="'.G5_URL.'" target="_blank">'.$config['cf_title'].'</a></p>', 1);

    set_session('adm_auth_update', str_replace('-', '', G5_TIME_YMD));
}

run_event('adm_auth_update', $mb);

goto_url('./auth_list.php?'.$qstr);
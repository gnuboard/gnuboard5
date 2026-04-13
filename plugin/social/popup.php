<?php
include_once('_common.php');

if( ! $config['cf_social_login_use'] ){
    alert('소셜 로그인 설정이 비활성화 되어 있습니다.');
    return;
}

if( ! G5_SOCIAL_USE_POPUP ){
    alert('새창 옵션이 비활성화 되어 있습니다.');
    return;
}

$provider_name = social_get_request_provider();

if( !$provider_name ){
    alert('서비스 이름이 넘어오지 않았습니다.');
}

// 소셜 계정 연결(mylink) CSRF 방어
if (isset($_REQUEST['mylink']) && !empty($_REQUEST['mylink'])) {
    if (!$is_member) {
        alert('로그인 후 이용해 주십시오.');
    }

    // 최초 진입(redirect_to_idp 없음) 시에만 Referer 검증 및 세션 토큰 설정
    if (!isset($_REQUEST['redirect_to_idp'])) {
        $referer = isset($_SERVER['HTTP_REFERER']) ? trim($_SERVER['HTTP_REFERER']) : '';
        $ref_host = $referer ? @parse_url($referer, PHP_URL_HOST) : '';
        $site_host = defined('G5_URL') ? @parse_url(G5_URL, PHP_URL_HOST) : '';

        if ($ref_host) $ref_host = preg_replace('/^www\./i', '', $ref_host);
        if ($site_host) $site_host = preg_replace('/^www\./i', '', $site_host);

        if (!$ref_host || !$site_host || strcasecmp($ref_host, $site_host) !== 0) {
            alert_close('올바른 방법으로 이용해 주십시오.');
        }

        set_session('ss_social_mylink_token', md5(uniqid(rand(), true)));
    }
}

if( isset( $_REQUEST["redirect_to_idp"] ) ){
    $content = social_check_login_before();

    $get_login_url = G5_BBS_URL."/login.php?url=".$urlencode;

    if( $content ){
        //팝업으로 띄웠다면 아래 코드를 실행
        ?>
        <script>
        if( window.opener ){
            (function(){
                var login_url = "<?php echo $get_login_url; ?>";

                window.opener.location.href = login_url+"&provider=<?php echo $provider_name; ?>";
                window.close();
            })();
        }
        </script>
        <?php
    }
} else {
    social_login_session_clear(1);
    social_return_from_provider_page( $provider_name, '', '', '', '' );
}
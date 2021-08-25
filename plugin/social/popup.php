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
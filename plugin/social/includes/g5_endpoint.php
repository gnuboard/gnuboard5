<?php
if (!defined('_GNUBOARD_')) exit;
//https://hybridauth.github.io/hybridauth/userguide/tuts/change-hybridauth-endpoint-url.html

class G5_Hybrid_Authentication {

    public static function hybridauth_endpoint() {

        require_once( G5_SOCIAL_LOGIN_PATH.'/Hybrid/Auth.php' );
        require_once( G5_SOCIAL_LOGIN_PATH.'/Hybrid/Endpoint.php' );
        require_once( G5_SOCIAL_LOGIN_PATH.'/includes/g5_endpoint_class.php' );

        if( defined('G5_SOCIAL_LOGIN_START_PARAM') && G5_SOCIAL_LOGIN_START_PARAM !== 'hauth.start' && isset($_REQUEST[G5_SOCIAL_LOGIN_START_PARAM]) ){
            $_REQUEST['hauth_start'] = preg_replace('/[^a-zA-Z0-9\-\._]/i', '', $_REQUEST[G5_SOCIAL_LOGIN_START_PARAM]);
        }

        if( defined('G5_SOCIAL_LOGIN_DONE_PARAM') && G5_SOCIAL_LOGIN_DONE_PARAM !== 'hauth.done' && isset($_REQUEST[G5_SOCIAL_LOGIN_DONE_PARAM]) ){
            $_REQUEST['hauth_done'] = preg_replace('/[^a-zA-Z0-9\-\._]/i', '', $_REQUEST[G5_SOCIAL_LOGIN_DONE_PARAM]);
        }

        /*
        $key = 'hauth.' . $action; // either `hauth_start` or `hauth_done`

        $_REQUEST[ $key ] = $provider; // provider will be something like `facebook` or `google`
        */

        G5_Hybrid_Endpoint::process();
    }

}

?>
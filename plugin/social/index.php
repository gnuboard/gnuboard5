<?php
/**
* HybridAuth
* http://hybridauth.sourceforge.net | http://github.com/hybridauth/hybridauth
* (c) 2009-2015, HybridAuth authors | http://hybridauth.sourceforge.net/licenses.html
*/

// ------------------------------------------------------------------------
//	HybridAuth End Point
// ------------------------------------------------------------------------

include_once('_common.php');

if( ! $config['cf_social_login_use']){
    die("소셜로그인을 사용하지 않습니다.");
}

require_once( "includes/g5_endpoint.php" );

error_reporting(0); // Turn off all error reporting

G5_Hybrid_Authentication::hybridauth_endpoint();
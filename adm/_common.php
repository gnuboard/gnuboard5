<?php
define('G5_IS_ADMIN', true);
include_once ('../common.php');
include_once(G5_ADMIN_PATH.'/admin.lib.php');

if( isset($token) ){
    $token = @htmlspecialchars(strip_tags($token), ENT_QUOTES);
}

if( ! function_exists('check_data_htaccess_file') ) {
    function check_data_htaccess_file() {
        $save_path = G5_DATA_PATH.'/.htaccess';
        if( file_exists($save_path) && is_writable($save_path) ) {
            $code = file_get_contents($save_path);
            $add_code = 'RedirectMatch 403 /session/.*';
            if( strpos($code, $add_code) === false ){
                $fp = fopen($save_path, "ab");
                flock( $fp, LOCK_EX );

                fwrite( $fp, "\n\n" );
                fwrite( $fp,  $add_code );
                fwrite( $fp, "\n\n" );

                flock( $fp, LOCK_UN );
                fclose($fp);
            }
        }
    }
    check_data_htaccess_file();
}

run_event('admin_common');
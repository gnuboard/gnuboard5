<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/json.lib.php');

$token_case = isset($_POST['token_case']) ? preg_replace('/[^0-9a-z_]/i', '', $_POST['token_case']) : '';

if( $token_case ){
    $token = _token();
    set_session('ss_'.$token_case.'_token', $token);
    die(json_encode(array('error'=>'', 'token'=>$token, 'url'=>'')));
}
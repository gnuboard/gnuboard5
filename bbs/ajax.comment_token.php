<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/json.lib.php');

$ss_name = 'ss_comment_token';

set_session($ss_name, '');

$token = _token();

set_session($ss_name, $token);

die(json_encode(array('token'=>$token)));
?>
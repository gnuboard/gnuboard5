<?php
include_once("_common.php");
include_once(dirname(__FILE__).'/kcaptcha_config.php');
include_once('captcha.lib.php');

while(true){
    $keystring='';
    for($i=0;$i<$length;$i++){
        $keystring.=$allowed_symbols{mt_rand(0,strlen($allowed_symbols)-1)};
    }
    if(!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/', $keystring)) break;
}

set_session("ss_captcha_count", 0);
set_session("ss_captcha_key", $keystring);
$captcha = new KCAPTCHA();
$captcha->setKeyString(get_session("ss_captcha_key"));
?>
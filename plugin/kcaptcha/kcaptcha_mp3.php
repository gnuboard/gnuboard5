<?php
include_once("_common.php");

function make_mp3()
{
    global $config;

    $number = get_session("ss_captcha_key");

    if ($number == "") return;
    $ip = md5(sha1($_SERVER['REMOTE_ADDR']));
    if( $number && function_exists('get_string_decrypt') ){
        $number = str_replace($ip, '', get_string_decrypt($number));
    }
    if ($number == get_session("ss_captcha_save")) return;

    $mp3s = array();
    for($i=0;$i<strlen($number);$i++){
        $file = G5_CAPTCHA_PATH.'/mp3/'.$config['cf_captcha_mp3'].'/'.$number[$i].'.mp3';
        $mp3s[] = $file;
    }

    $mp3_file = 'cache/kcaptcha-'.$ip.'_'.G5_SERVER_TIME.'.mp3';

    $contents = '';
    foreach ($mp3s as $mp3) {
        $contents .= file_get_contents($mp3);
    }

    file_put_contents(G5_DATA_PATH.'/'.$mp3_file, $contents);

    // 지난 캡챠 파일 삭제
    if (rand(0,99) == 0) {
        foreach (glob(G5_DATA_PATH.'/cache/kcaptcha-*.mp3') as $file) {
            if (filemtime($file) + 86400 < G5_SERVER_TIME) {
                @unlink($file);
            }
        }
    }

    if( $number && function_exists('get_string_encrypt') ){
        $number = get_string_encrypt($ip.$number);
    }
    set_session("ss_captcha_save", $number);

    return G5_DATA_URL.'/'.$mp3_file;
}

echo make_mp3();
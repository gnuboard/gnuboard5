<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if( ! function_exists('array_map_deep') ){
    // multi-dimensional array에 사용자지정 함수적용
    function array_map_deep($fn, $array)
    {
        if(is_array($array)) {
            foreach($array as $key => $value) {
                if(is_array($value)) {
                    $array[$key] = array_map_deep($fn, $value);
                } else {
                    $array[$key] = call_user_func($fn, $value);
                }
            }
        } else {
            $array = call_user_func($fn, $array);
        }

        return $array;
    }
}

if( ! function_exists('safe_install_string_check') ){
    function safe_install_string_check( $str, $is_json=false ) {
        $is_check = false;

        if(preg_match('#\);(passthru|eval|pcntl_exec|exec|system|popen|fopen|fsockopen|file|file_get_contents|readfile|unlink|include|include_once|require|require_once)\s?#i', $str)) {
            $is_check = true;
        }

        if(preg_match('#\$_(get|post|request)\s?\[.*?\]\s?\)#i', $str)){
            $is_check = true;
        }

        if($is_check){
            $msg = "입력한 값에 안전하지 않는 문자가 포함되어 있습니다. 설치를 중단합니다.";

            if($is_json){
                die(install_json_msg($msg));
            }

            die($msg);
        }

        return array_map_deep('stripslashes', $str);
    }
}

if( ! function_exists('install_json_msg') ){
    function install_json_msg($msg, $type='error'){

        $error_msg = ($type==='error') ? $msg : '';
        $success_msg = ($type==='success') ? $msg : '';
        $exists_msg = ($type==='exists') ? $msg : '';

        return json_encode(array('error'=>$error_msg, 'success'=>$success_msg, 'exists'=>$exists_msg));
    }
}
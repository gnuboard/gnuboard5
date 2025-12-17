<?php
if (!defined('_GNUBOARD_')) exit;

// 안전한 문자열 비교 함수
function safe_string_compare($str1, $str2) {
    if (strlen($str1) !== strlen($str2)) {
        return false;
    }
    $result = 0;
    for ($i = 0; $i < strlen($str1); $i++) {
        $result |= ord($str1[$i]) ^ ord($str2[$i]);
    }
    return $result === 0;
}

// form nonce 비밀키
function get_form_nonce_secret() {
    $keys = array(
        'ss' =>  isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '',
        'gns' => defined('G5_NONCE_SECRET') ? G5_NONCE_SECRET : '',
        'remote_addr' => $_SERVER['REMOTE_ADDR'],
        'ssid' => session_id(),
        'gtek' => defined('G5_TOKEN_ENCRYPTION_KEY') ? substr(G5_TOKEN_ENCRYPTION_KEY, 0, 10) : ''
    );
    
    return hash('sha256', run_replace('get_form_nonce_secret_keys', implode('', $keys)));
}

// 폼 nonce 생성
function generate_form_nonce($form_id, $timeoutSeconds=7200) {

    // HTTPS 강제
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $form_id)) {
        trigger_error('Invalid form_id', E_USER_ERROR);
        return false;
    }
    $secret = get_form_nonce_secret();
    $salt = bin2hex(openssl_random_pseudo_bytes(16));
    $maxTime = G5_SERVER_TIME + $timeoutSeconds;

    $hash = hash_hmac('sha256', $salt . $maxTime . $form_id, $secret);
    return $salt . '|' . $maxTime . '|' . $hash;
}

// 폼 nonce 검증
function verify_form_nonce($nonce, $form_id) {

    // HTTPS 강제
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
    if (!is_string($nonce) || !preg_match('/^[a-zA-Z0-9]+\|\d+\|[a-f0-9]+$/', $nonce)) {
        return false;
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $form_id)) {
        return false;
    }
    $secret = get_form_nonce_secret();
    $a = explode('|', $nonce);
    if (count($a) !== 3) {
        return false;
    }
    list($salt, $maxTime, $hash) = $a;
    if (G5_SERVER_TIME > (int)$maxTime || (int)$maxTime > G5_SERVER_TIME + 86400) {
        return false;
    }

    $computedHash = hash_hmac('sha256', $salt . $maxTime . $form_id, $secret);
    return safe_string_compare($hash, $computedHash);
}

function alert_verify_form_nonce($fnonce, $form_id) {
    if (!verify_form_nonce($fnonce, $form_id)) {
        $msg = '유효하지 않거나 만료된 입니다. 다시 시도해 주세요.';
        
        alert($msg);
    }
}
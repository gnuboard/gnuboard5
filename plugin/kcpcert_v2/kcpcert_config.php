<?php
if (!defined('_GNUBOARD_')) exit;

if (version_compare(PHP_VERSION, '7.0.0', '<')) {
    alert('KCP 휴대폰 본인확인 V2 모듈은 PHP 7.0 이상 환경에서만 동작합니다.', G5_URL);
}
if (!extension_loaded('openssl') || !function_exists('hash_pbkdf2') || !extension_loaded('curl')) {
    alert('KCP 휴대폰 본인확인 V2 모듈에 필요한 PHP 확장(openssl/curl/hash_pbkdf2)이 활성화되어 있지 않습니다.', G5_URL);
}

$web_siteid = '';

if ($config['cf_cert_use'] == 2) {
    $site_cd = 'SM'.trim($config['cf_cert_kcp_cd']);
    $kcp_enc_key  = isset($config['cf_cert_kcp_enckey']) ? $config['cf_cert_kcp_enckey'] : '';
    $cert_reg_url = 'https://cert.kcp.co.kr/api/reg/certDataReg.do';
    $cert_dec_url = 'https://cert.kcp.co.kr/api/query/getCertData.do';
} else if ($config['cf_cert_use'] == 1) {
    $site_cd      = 'AO7F3';
    $kcp_enc_key  = 'c2a22fa3ebe4698075bcac6b433d52e351c881b02fb83488d4283a43385b1f8e';
    $cert_reg_url = 'https://testcert.kcp.co.kr/api/reg/certDataReg.do';
    $cert_dec_url = 'https://testcert.kcp.co.kr/api/query/getCertData.do';
} else {
    $site_cd = $kcp_enc_key = $cert_reg_url = $cert_dec_url = '';
}

if (!$site_cd || !$kcp_enc_key) {
    alert('KCP 휴대폰 본인확인 V2 사이트코드 또는 ENC_KEY 가 설정되지 않았습니다.\n관리자 > 기본환경설정에서 입력해 주세요.', G5_URL);
}

require_once G5_KCPCERT_V2_PATH.'/lib/Crypto.php';
require_once G5_KCPCERT_V2_PATH.'/lib/kcp_api.php';

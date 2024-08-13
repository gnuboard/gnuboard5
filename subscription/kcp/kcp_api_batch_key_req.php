<?php

if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'kcp') {
    return;
}

include_once G5_SUBSCRIPTION_PATH.'/settle_kcp.inc.php';

/* ============================================================================== */
/* =   배치키 발급 요청 API                                                      = */
/* = -------------------------------------------------------------------------- = */

if (get_subs_option('su_card_test')) {
    $target_URL = 'https://stg-spl.kcp.co.kr/gw/enc/v1/payment'; // 개발서버
} else {
    $target_URL = 'https://spl.kcp.co.kr/gw/enc/v1/payment'; // 운영서버
}

/* ============================================================================== */
/* =  요청정보                                                                   = */
/* = -------------------------------------------------------------------------- = */
$tran_cd = $_POST['tran_cd']; // 요청 코드
// $site_cd            = $_POST[ "site_cd"  ]; // 사이트 코드
$site_cd = get_subs_option('su_kcp_mid'); // 사이트 코드

// 인증서 정보(직렬화)
$kcp_cert_info = '-----BEGIN CERTIFICATE-----MIIDgTCCAmmgAwIBAgIHBy4lYNG7ojANBgkqhkiG9w0BAQsFADBzMQswCQYDVQQGEwJLUjEOMAwGA1UECAwFU2VvdWwxEDAOBgNVBAcMB0d1cm8tZ3UxFTATBgNVBAoMDE5ITktDUCBDb3JwLjETMBEGA1UECwwKSVQgQ2VudGVyLjEWMBQGA1UEAwwNc3BsLmtjcC5jby5rcjAeFw0yMTA2MjkwMDM0MzdaFw0yNjA2MjgwMDM0MzdaMHAxCzAJBgNVBAYTAktSMQ4wDAYDVQQIDAVTZW91bDEQMA4GA1UEBwwHR3Vyby1ndTERMA8GA1UECgwITG9jYWxXZWIxETAPBgNVBAsMCERFVlBHV0VCMRkwFwYDVQQDDBAyMDIxMDYyOTEwMDAwMDI0MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAppkVQkU4SwNTYbIUaNDVhu2w1uvG4qip0U7h9n90cLfKymIRKDiebLhLIVFctuhTmgY7tkE7yQTNkD+jXHYufQ/qj06ukwf1BtqUVru9mqa7ysU298B6l9v0Fv8h3ztTYvfHEBmpB6AoZDBChMEua7Or/L3C2vYtU/6lWLjBT1xwXVLvNN/7XpQokuWq0rnjSRThcXrDpWMbqYYUt/CL7YHosfBazAXLoN5JvTd1O9C3FPxLxwcIAI9H8SbWIQKhap7JeA/IUP1Vk4K/o3Yiytl6Aqh3U1egHfEdWNqwpaiHPuM/jsDkVzuS9FV4RCdcBEsRPnAWHz10w8CX7e7zdwIDAQABox0wGzAOBgNVHQ8BAf8EBAMCB4AwCQYDVR0TBAIwADANBgkqhkiG9w0BAQsFAAOCAQEAg9lYy+dM/8Dnz4COc+XIjEwr4FeC9ExnWaaxH6GlWjJbB94O2L26arrjT2hGl9jUzwd+BdvTGdNCpEjOz3KEq8yJhcu5mFxMskLnHNo1lg5qtydIID6eSgew3vm6d7b3O6pYd+NHdHQsuMw5S5z1m+0TbBQkb6A9RKE1md5/Yw+NymDy+c4NaKsbxepw+HtSOnma/R7TErQ/8qVioIthEpwbqyjgIoGzgOdEFsF9mfkt/5k6rR0WX8xzcro5XSB3T+oecMS54j0+nHyoS96/llRLqFDBUfWn5Cay7pJNWXCnw4jIiBsTBa3q95RVRyMEcDgPwugMXPXGBwNoMOOpuQ==-----END CERTIFICATE-----';
$enc_data = $_POST['enc_data']; // 암호화 인증데이터
$enc_info = $_POST['enc_info']; // 암호화 인증데이터

$data = [
    'tran_cd' => $tran_cd,
    'site_cd' => $site_cd,
    'kcp_cert_info' => $kcp_cert_info,
    'enc_data' => $enc_data,
    'enc_info' => $enc_info,
];

$req_data = json_encode($data);

$header_data = ['Content-Type: application/json', 'charset=utf-8'];

// API REQ
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $req_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// API RES
$res_data = curl_exec($ch);

curl_close($ch);

$response = json_decode($res_data, true);
$res_cd = isset($response['res_cd']) ? $response['res_cd'] : '';

if ($res_cd != '0000') {
    $res_msg = $response['res_msg'];

    alert("$res_cd : $res_msg");
    exit;
}

// 응답예 )
// [res_msg] => 정상처리
// [card_cd] => CCSS
// [card_bin_type_02] => 0
// [card_bank_cd] => 0311
// [batch_key] => 24080***080**83*
// [card_name] => 삼성카드
// [van_tx_id] =>
// [card_bin_type_01] => 0
// [res_cd] => 0000
// [join_cd] =>

// 승인결과
// KCP는 카드번호 모름
$card_number = '';
$card_billkey = $response['batch_key'];
$card_name = $response['card_name'];

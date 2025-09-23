<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// pc자동결제 메뉴얼 https://developer.kcp.co.kr/page/document/webauto
// mobile 자동결제 메뉴얼 https://developer.kcp.co.kr/page/document/mobileauto

// 테스트이면
if (get_subs_option('su_card_test')) {
    // 사이트 코드
    set_subs_option('su_kcp_mid', 'A52Q7');
    set_subs_option('su_kcp_group_id', 'A52Q71000489');

    // 개발서버
    $g_conf_js_url = 'https://testspay.kcp.co.kr/plugin/kcp_spay_hub.js';
    
    // 개발서버 결제 API URL (배치키 결제, 삭제)
    $kcp_target_url = "https://stg-spl.kcp.co.kr/gw/hub/v1/payment";
    
    // 개발서버 NHN KCP 인증서 정보
    set_subs_option('su_kcp_cert_info', '-----BEGIN CERTIFICATE-----MIIDgTCCAmmgAwIBAgIHBy4lYNG7ojANBgkqhkiG9w0BAQsFADBzMQswCQYDVQQGEwJLUjEOMAwGA1UECAwFU2VvdWwxEDAOBgNVBAcMB0d1cm8tZ3UxFTATBgNVBAoMDE5ITktDUCBDb3JwLjETMBEGA1UECwwKSVQgQ2VudGVyLjEWMBQGA1UEAwwNc3BsLmtjcC5jby5rcjAeFw0yMTA2MjkwMDM0MzdaFw0yNjA2MjgwMDM0MzdaMHAxCzAJBgNVBAYTAktSMQ4wDAYDVQQIDAVTZW91bDEQMA4GA1UEBwwHR3Vyby1ndTERMA8GA1UECgwITG9jYWxXZWIxETAPBgNVBAsMCERFVlBHV0VCMRkwFwYDVQQDDBAyMDIxMDYyOTEwMDAwMDI0MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAppkVQkU4SwNTYbIUaNDVhu2w1uvG4qip0U7h9n90cLfKymIRKDiebLhLIVFctuhTmgY7tkE7yQTNkD+jXHYufQ/qj06ukwf1BtqUVru9mqa7ysU298B6l9v0Fv8h3ztTYvfHEBmpB6AoZDBChMEua7Or/L3C2vYtU/6lWLjBT1xwXVLvNN/7XpQokuWq0rnjSRThcXrDpWMbqYYUt/CL7YHosfBazAXLoN5JvTd1O9C3FPxLxwcIAI9H8SbWIQKhap7JeA/IUP1Vk4K/o3Yiytl6Aqh3U1egHfEdWNqwpaiHPuM/jsDkVzuS9FV4RCdcBEsRPnAWHz10w8CX7e7zdwIDAQABox0wGzAOBgNVHQ8BAf8EBAMCB4AwCQYDVR0TBAIwADANBgkqhkiG9w0BAQsFAAOCAQEAg9lYy+dM/8Dnz4COc+XIjEwr4FeC9ExnWaaxH6GlWjJbB94O2L26arrjT2hGl9jUzwd+BdvTGdNCpEjOz3KEq8yJhcu5mFxMskLnHNo1lg5qtydIID6eSgew3vm6d7b3O6pYd+NHdHQsuMw5S5z1m+0TbBQkb6A9RKE1md5/Yw+NymDy+c4NaKsbxepw+HtSOnma/R7TErQ/8qVioIthEpwbqyjgIoGzgOdEFsF9mfkt/5k6rR0WX8xzcro5XSB3T+oecMS54j0+nHyoS96/llRLqFDBUfWn5Cay7pJNWXCnw4jIiBsTBa3q95RVRyMEcDgPwugMXPXGBwNoMOOpuQ==-----END CERTIFICATE-----');
    
} else {
    // 실 사용이면
    set_subs_option('su_kcp_mid', 'SR'.get_subs_option('su_kcp_mid'));

    // 운영서버
    $g_conf_js_url = 'https://spay.kcp.co.kr/plugin/kcp_spay_hub.js';
    
    // 운영서버 결제 API URL (배치키 결제, 삭제)
    $kcp_target_url = "https://spl.kcp.co.kr/gw/hub/v1/payment";
    
}

if (get_subs_option('su_card_test')) {
} else {
    if (!preg_match('/^SR/', $g_conf_site_cd)) {
        alert('SR 로 시작하지 않는 KCP SITE CODE 는 지원하지 않습니다.');
    }
}

$g_conf_site_name = $default['de_admin_company_name'];

// https://developer.kcp.co.kr/page/refer/cardcode

$kcp_card_codes = array(
'CCKM' => 'KB국민카드',
'CCNH' => 'NH농협카드',
'CCSG' => '신세계한미',
'CCCT' => '씨티카드',
'CCHM' => '한미카드',
'CVSF' => '해외비자',
'CCAM' => '롯데아멕스카드',
'CCLO' => '롯데카드',
'CCBC' => 'BC카드',
'CCWR' => '우리카드',
'CCHN' => '하나카드',
'CCSS' => '삼성카드',
'CCKJ' => '광주카드',
'CCSU' => '수협카드',
'CCJB' => '전북카드',
'CCCJ' => '제주카드',
'CCLG' => '신한카드',
'CMCF' => '해외마스터',
'CJCF' => '해외JCB',
'CCKE' => '하나카드(외환)',
'CCDI' => '현대카드',
'CCUF' => '은련카드'
);
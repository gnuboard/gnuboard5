<?php

if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 테스트이면
if (get_subs_option('su_card_test')) {
    // 사이트 코드
    set_subs_option('su_kcp_mid', 'A52Q7');
    set_subs_option('su_kcpgroup_id', 'A52Q71000489');

    // 개발서버
    $g_conf_js_url = 'https://testspay.kcp.co.kr/plugin/kcp_spay_hub.js';
} else {
    // 실 사용이면
    set_subs_option('su_kcp_mid', 'SR'.get_subs_option('su_kcp_mid'));

    // 운영서버
    $g_conf_js_url = 'https://spay.kcp.co.kr/plugin/kcp_spay_hub.js';
}

$g_conf_home_dir = G5_SUBSCRIPTION_PATH.'/kcp';
$g_conf_key_dir = '';

$g_conf_site_cd = get_subs_option('su_kcp_mid');
$g_conf_site_key = $default['de_kcp_site_key'];

if (preg_match('/^T000/', $g_conf_site_cd) || get_subs_option('su_card_test')) {
} else {
    if (!preg_match('/^SR/', $g_conf_site_cd)) {
        alert('SR 로 시작하지 않는 KCP SITE CODE 는 지원하지 않습니다.');
    }
}

// KCP SITE KEY 입력 체크
// if (trim($default['de_kcp_site_key']) == '') {
//     alert('KCP SITE KEY를 입력해 주십시오.');
// }

$g_conf_site_name = $default['de_admin_company_name'];
// $g_conf_log_level = '3';           // 변경불가
// $g_conf_gw_port = '8090';        // 포트번호(변경불가)
// $module_type = '01';          // 변경불가;

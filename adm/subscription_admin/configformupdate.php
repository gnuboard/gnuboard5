<?php
$sub_menu = '600100';
include_once('./_common.php');

check_demo();

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$warning_msg = '';

$check_sanitize_keys = array(
    'su_pg_service',                //결제대행사
    'su_kcp_mid',                   // KCP SITE CODE
    'su_kcp_site_key',              // NHN KCP SITE KEY
    'su_kcp_group_id',              // NHN KCP 그룹아이디
    'su_kcp_cert_info',             // NHN KCP 인증서 ( NHNKCP 상점 관리자 > 기술관리센터 > 인증센터 > KCP PG-API > 발급하기 경로에서 개인키 + 인증서 발급이 가능 )
    'su_inicis_mid',                //KG이니시스 상점아이디
    'su_inicis_iniapi_key',         //KG이니시스 INIAPI KEY
    'su_inicis_iniapi_iv',          //KG이니시스 INIAPI IV
    'su_inicis_sign_key',           //KG이니시스 웹결제 사인키
    'su_nicepay_mid',             //나이스페이 mid
    'su_nicepay_key',            //나이스페이 키
    'su_tosspayments_mid',           // 토스페이먼츠 상점 아이디
    'su_tosspayments_api_clientkey',    // 토스페이먼츠 API 클라이언트키
    'su_tosspayments_api_secretkey',  // 토스페이먼츠 API 시크릿키
    'su_card_test',                 //결제 테스트
    'su_hope_date_use',             // 희망배송일사용
    'su_hope_date_after',            // 희망배송일지정
    'su_output_display_type',       // 정기구독 입력 출력 형식
    'su_auto_payment_lead_days',    // 배송일 이전 자동결제 설정일
    'su_chk_user_delivery',         // 사용자가 배송주기의 입력 사용 가능한지 체크
    'su_user_delivery_title',       // 사용자가 배송주기의 입력 사용 가능시 타이틀 지정
    'su_user_delivery_minimum',
    'su_user_select_title',
    'su_user_delivery_default_day',
    'api_holiday_data_go_key',      // https://www.data.go.kr/data/15012690/openapi.do 의 API 키 입력
    'cron_night_block',             // CRON 야간 시간대 제외
);

$inserts = array();

foreach ($check_sanitize_keys as $key) {
    $inserts[$key] = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
}

// 정기결제 폼 첫번째 안내문
if (isset($_POST['su_subscription_content_first'])) {
    $inserts['su_subscription_content_first'] = $_POST['su_subscription_content_first'];
}

// 정기결제 폼 마지막 안내문
if (isset($_POST['su_subscription_content_end'])) {
    $inserts['su_subscription_content_end'] = $_POST['su_subscription_content_end'];
}

// 주문서에 입력
$sql = "SELECT * FROM `{$g5['g5_subscription_config_table']}` LIMIT 1";
$exist = sql_fetch($sql);

$opts = array();
$opts_keys = array('opt_id', 'opt_chk', 'opt_input', 'opt_date_format', 'opt_etc', 'opt_print', 'opt_use');
$opt_ids = isset($_POST['opt_id']) ? $_POST['opt_id'] : array();

if ($opt_ids) {
    foreach ($opt_ids as $index => $value) {
        foreach ($opts_keys as $key) {
            $opts[$index][$key] = (isset($_POST[$key]) && isset($_POST[$key][$index])) ? $_POST[$key][$index] : '';
        }
    }
}

$uses = array();
$uses_keys = array('use_id', 'use_chk', 'use_input', 'use_print', 'num_use');
$use_ids = isset($_POST['use_id']) ? $_POST['use_id'] : array();

if ($use_ids) {
    foreach ($use_ids as $index => $value) {
        foreach ($uses_keys as $key) {

            if ($key === 'use_input' && isset($_POST['use_input'][$index]) && !$_POST['use_input'][$index]) {
                continue;
            }

            $uses[$index][$key] = (isset($_POST[$key]) && isset($_POST[$key][$index])) ? $_POST[$key][$index] : '';
        }
    }
}

$inserts['su_opt_settings'] = base64_encode(serialize($opts));
$inserts['su_use_settings'] = base64_encode(serialize($uses));

if (isset($exist['su_id']) && $exist['su_id']) {
    $valueSets = array();

    foreach ($inserts as $key => $value) {
        $valueSets[] = $key . " = '" . $value . "'";
    }

    $sql = "UPDATE `{$g5['g5_subscription_config_table']}` SET " . implode(', ', $valueSets);
    
} else {

    $columns = implode(', ', array_keys($inserts));
    $values = implode("', '", array_values($inserts));
    $sql = "INSERT INTO `{$g5['g5_subscription_config_table']}`($columns) VALUES ('$values')";
}

sql_query($sql);

run_event('subscription_admin_configformupdate');

if ($warning_msg) {
    alert($warning_msg, "./configform.php");
} else {
    goto_url("./configform.php");
}

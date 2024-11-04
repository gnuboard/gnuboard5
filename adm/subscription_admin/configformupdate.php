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
'su_kcp_group_id',              // NHN KCP 그룹아이디
'su_kcp_cert_info',             // NHN KCP 인증서 ( NHNKCP 상점 관리자 > 기술관리센터 > 인증센터 > KCP PG-API > 발급하기 경로에서 개인키 + 인증서 발급이 가능 )
'su_inicis_mid',                //KG이니시스 상점아이디
'su_inicis_iniapi_key',         //KG이니시스 INIAPI KEY
'su_inicis_iniapi_iv',          //KG이니시스 INIAPI IV
'su_inicis_sign_key',           //KG이니시스 웹결제 사인키
'su_nice_clientid',             //나이스페이 클라이언트 키
'su_nice_secretkey',            //나이스페이 비밀 키
'su_card_test',                 //결제 테스트
'su_cron_execute_hour',         // 매일 크론 실행 hour
);

$inserts = array();

foreach( $check_sanitize_keys as $key ){
    $inserts[$key] = isset($_POST[$key]) ? clean_xss_tags($_POST[$key], 1, 1) : '';
}

// 주문서에 입력

$sql = "select * from `{$g5['g5_subscription_config_table']}` limit 1";
$exist = sql_fetch($sql);

if (isset($exist['su_id']) && $exist['su_id']) {
    $valueSets = array();
    
    foreach($inserts as $key => $value) {
        $valueSets[] = $key . " = '" . $value . "'";
    }

    $sql = "UPDATE `{$g5['g5_subscription_config_table']}` SET ".implode(', ',$valueSets);
} else {
    $columns = implode(', ', array_keys($inserts));
    $values = implode("', '", array_values($inserts));
    $sql = "INSERT INTO `{$g5['g5_subscription_config_table']}`($columns) VALUES ('$values')";
}

sql_query($sql);

run_event('subscription_admin_configformupdate');

if( $warning_msg ){
    alert($warning_msg, "./configform.php");
} else {
    goto_url("./configform.php");
}
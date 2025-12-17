<?php
define('SUBSCRIPTION_INSTALL_PAGE', 1);
$sub_menu = "600000";
include_once("./_common.php");

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = "정기결제 프로그램 설치";

$setup = (isset($_POST['setup']) && $_POST['setup']) ? 1 : 0;

//정기결제 설정 정보 테이블이 있는지 검사한다.
// 정기결제 테이블은 중요한 정보가 있을수도 있으니, 수동으로 삭제해야 한다.
if (isset($g5['g5_subscription_cart_table']) && sql_query(" DESCRIBE {$g5['g5_subscription_cart_table']} ", false)) {
    alert('이미 정기결제가 설치되어 있습니다. 새로 설치하려면 테이블을 삭제해 주세요.');
    die('');
}

include_once(G5_ADMIN_PATH.'/admin.head.php');
?>

<div id="subscription_install">
    <ol>
        <li>정기결제 프로그램 설치가 시작되었습니다.</li>
        <li id="subscription_job_01">전체 테이블 생성중</li>
        <li id="subscription_job_02">DB설정 중</li>
        <li id="subscription_job_03"></li>
    </ol>

    <p><button type="button" id="subscription_btn_next" disabled class="btn_frmline" onclick="location.href='configform.php';">정기결제 기본설정</button></p>

</div>
<?php
flush(); usleep(50000);

// 테이블 생성 ------------------------------------
$file = implode("", file("./subscription.sql"));
$file = preg_replace('/^--.*$/m', '', $file);
$file = preg_replace('/`g5_subscription_([^`]+`)/', '`'.$g5['subscription_prefix'].'$1', $file);

$f = explode(";", $file);
for ($i=0; $i<count($f); $i++) {
    if (trim($f[$i]) == "") continue;
    if ($f[$i] = get_db_create_replace($f[$i])) {
        
        //echo $f[$i];
        
        //echo "<br>";
        
        sql_query($f[$i]) or die(sql_error_info());
    }
}
// 테이블 생성 ------------------------------------

echo "<script>document.getElementById('subscription_job_01').innerHTML='전체 테이블 생성 완료';</script>";
flush(); usleep(50000);

//-------------------------------------------------------------------------------------------------
// 정기결제 config 테이블 설정
$sql = " insert into `{$table_prefix}subscription_config` SET
    su_pg_service = 'inicis',
    su_kcp_mid = '',
    su_kcp_site_key = '',
    su_kcp_group_id = '',
    su_kcp_cert_info = '',
    su_inicis_mid = '',
    su_inicis_iniapi_key = '',
    su_inicis_iniapi_iv = '',
    su_inicis_sign_key = '',
    su_nice_clientid = '',
    su_nice_secretkey = '',
    su_tosspayments_mid = '',
    su_tosspayments_api_clientkey = '',
    su_tosspayments_api_secretkey = '',
    su_card_test = '1',
    su_hope_date_use = '1',
    su_hope_date_after = '2',
    su_output_display_type = '1',
    su_auto_payment_lead_days = '2',
    su_chk_user_delivery = '',
    su_user_delivery_title = '',
    su_user_delivery_minimum = '0',
    su_user_select_title = '',
    su_user_delivery_default_day = '0',
    api_holiday_data_go_key = '',
    cron_night_block = '',
    su_subscription_content_first = '',
    su_subscription_content_end = '',
    su_opt_settings = 'YToxOntpOjA7YTo3OntzOjY6Im9wdF9pZCI7czoxOiIxIjtzOjc6Im9wdF9jaGsiO3M6MDoiIjtzOjk6Im9wdF9pbnB1dCI7czoxOiIxIjtzOjE1OiJvcHRfZGF0ZV9mb3JtYXQiO3M6NDoid2VlayI7czo3OiJvcHRfZXRjIjtzOjA6IiI7czo5OiJvcHRfcHJpbnQiO3M6MDoiIjtzOjc6Im9wdF91c2UiO3M6MToiMSI7fX0=',
    su_use_settings = 'YToxOntpOjA7YTo1OntzOjY6InVzZV9pZCI7czoxOiIxIjtzOjc6InVzZV9jaGsiO3M6MDoiIjtzOjk6InVzZV9pbnB1dCI7czoxOiIxIjtzOjk6InVzZV9wcmludCI7czowOiIiO3M6NzoibnVtX3VzZSI7czoxOiIxIjt9fQ=='
    ";

sql_query($sql, true);

echo "<script>document.getElementById('subscription_job_02').innerHTML='DB설정 완료';</script>";
flush(); usleep(50000);
//-------------------------------------------------------------------------------------------------

echo "<script>document.getElementById('subscription_job_03').innerHTML='정기결제 기본 설정 변경 후 사용하세요.';</script>";
flush(); usleep(50000);
?>

<script>document.getElementById('subscription_btn_next').disabled = false;</script>
<script>document.getElementById('subscription_btn_next').focus();</script>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
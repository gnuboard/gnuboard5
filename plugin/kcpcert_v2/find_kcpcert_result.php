<?php
include_once('./_common.php');
include_once(G5_KCPCERT_V2_PATH.'/kcpcert_config.php');

$res_cd  = isset($_POST['res_cd'])  ? trim($_POST['res_cd'])  : '';
$res_msg = isset($_POST['res_msg']) ? trim($_POST['res_msg']) : '';

$reg_cert_key = get_session('ss_kcp_v2_reg_cert_key');
$ordr_idxx    = get_session('ss_kcp_v2_ordr_idxx');

set_session('ss_kcp_v2_reg_cert_key', '');
set_session('ss_kcp_v2_ordr_idxx',    '');
set_session('ss_kcp_v2_page_type',    '');

$g5['title'] = '휴대폰인증 결과';
include_once(G5_PATH.'/head.sub.php');

if ($res_cd === '') {
    alert_close('본인확인 응답값이 없습니다. 처음부터 다시 시도해 주세요.');
}

@insert_cert_history($member['mb_id'], 'kcp_v2', 'hp');

if ($res_cd === '9999') {
    alert_close('휴대폰 본인확인을 취소 하셨습니다.');
}

if ($res_cd !== '0000') {
    alert_close('코드 : '.$res_cd.' '.urldecode($res_msg));
}

if (!$reg_cert_key || !$ordr_idxx) {
    alert_close('본인확인 세션이 만료되었습니다. 처음부터 다시 시도해 주세요.');
}

$api  = new C_KCP_API_V2($site_cd, $kcp_enc_key, $cert_reg_url, $cert_dec_url);
$cert = $api->get_cert_data($reg_cert_key, $ordr_idxx);

if ($cert['res_cd'] !== '0000') {
    alert_close('본인확인 결과조회 실패 ('.$cert['res_cd'].' : '.$cert['res_msg'].')');
}

$phone_no  = trim($cert['phone_no']);
$user_name = trim($cert['user_name']);
$birth_day = trim($cert['birth_day']);
$sex_code  = trim($cert['sex_code']);
$ci        = trim($cert['ci']);
$di        = trim($cert['di']);

if (!$phone_no || !$user_name || !$birth_day || !$ci || !$di) {
    alert_close('정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.');
}

$phone_no   = hyphen_hp_number($phone_no);
$mb_dupinfo = $di;
$md5_ci     = md5($ci.$ci);
$sql_dupinfo = sql_real_escape_string($mb_dupinfo);
$sql_md5_ci  = sql_real_escape_string($md5_ci);

$row = sql_fetch("select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$sql_md5_ci}'");
if (empty($row['mb_id'])) {
    $row = sql_fetch("select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$sql_dupinfo}'");
    if (empty($row['mb_id'])) {
        alert_close('인증하신 정보로 가입된 회원정보가 없습니다.');
    }
} else {
    $mb_dupinfo = $md5_ci;
}

$cert_type   = 'hp';
$md5_cert_no = md5($reg_cert_key);
$hash_data   = md5($user_name.$cert_type.$birth_day.$phone_no.$md5_cert_no);

$adult_day = date('Ymd', strtotime('-19 years', G5_SERVER_TIME));
$adult = ((int)$birth_day <= (int)$adult_day) ? 1 : 0;

set_session('ss_cert_type',    $cert_type);
set_session('ss_cert_no',      $md5_cert_no);
set_session('ss_cert_hash',    $hash_data);
set_session('ss_cert_adult',   $adult);
set_session('ss_cert_birth',   $birth_day);
set_session('ss_cert_sex',     ($sex_code == '01' ? 'M' : 'F'));
set_session('ss_cert_dupinfo', $mb_dupinfo);
set_session('ss_cert_mb_id',   $row['mb_id']);
?>
<form name="mbFindForm" method="POST">
    <input type="hidden" name="mb_id" value="<?php echo $row['mb_id']; ?>">
</form>
<script>
jQuery(function($) {
    var $opener = window.opener;
    if (!$opener) { window.close(); return; }
    $opener.name = "parentPage";

    document.mbFindForm.target = "parentPage";
    document.mbFindForm.action = "<?php echo G5_BBS_URL.'/password_reset.php'; ?>";
    document.mbFindForm.submit();

    alert("본인인증이 완료되었습니다.");
    window.close();
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');

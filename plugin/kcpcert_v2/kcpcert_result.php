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

$phone_no   = trim($cert['phone_no']);
$user_name  = trim($cert['user_name']);
$birth_day  = trim($cert['birth_day']);
$sex_code   = trim($cert['sex_code']);
$ci         = trim($cert['ci']);
$di         = trim($cert['di']);

if (!$phone_no || !$user_name || !$birth_day || !$ci || !$di) {
    alert_close('정상적인 인증이 아닙니다. 올바른 방법으로 이용해 주세요.');
}

$phone_no   = hyphen_hp_number($phone_no);
$mb_dupinfo = md5($ci.$ci);
$sql_dupinfo = sql_real_escape_string($mb_dupinfo);

if (!empty($member['mb_certify']) && !empty($member['mb_dupinfo']) && strlen($member['mb_dupinfo']) != 64) {
    if ($member['mb_dupinfo'] != $mb_dupinfo) {
        alert_close('해당 계정은 이미 다른명의로 본인인증 되어있는 계정입니다.');
    }
}

$sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$sql_dupinfo}' ";
$row = sql_fetch($sql);
if (!empty($row['mb_id'])) {
    alert_close("입력하신 본인확인 정보로 가입된 내역이 존재합니다.\\n회원아이디 : ".$row['mb_id']);
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

$js_replace = array('\\' => '\\\\', '"' => '\\"', "'" => '\\u0027', '/' => '\\/', "\r" => '\\r', "\n" => '\\n', "\t" => '\\t', '<' => '\\u003C', '>' => '\\u003E', '&' => '\\u0026', "\xE2\x80\xA8" => '\\u2028', "\xE2\x80\xA9" => '\\u2029');
$js_cert_type = function_exists('get_js_safe_string') ? get_js_safe_string($cert_type) : '"'.strtr((string)$cert_type, $js_replace).'"';
$js_user_name = function_exists('get_js_safe_string') ? get_js_safe_string($user_name) : '"'.strtr((string)$user_name, $js_replace).'"';
$js_phone_no = function_exists('get_js_safe_string') ? get_js_safe_string($phone_no) : '"'.strtr((string)$phone_no, $js_replace).'"';
$js_md5_cert_no = function_exists('get_js_safe_string') ? get_js_safe_string($md5_cert_no) : '"'.strtr((string)$md5_cert_no, $js_replace).'"';
?>
<script>
jQuery(function($) {
    var $opener = window.opener;
    if (!$opener) { window.close(); return; }

    $opener.$("input[name=cert_type]").val(<?php echo $js_cert_type; ?>);
    $opener.$("input[name=mb_name]").val(<?php echo $js_user_name; ?>).attr("readonly", true);
    $opener.$("input[name=mb_hp]").val(<?php echo $js_phone_no; ?>).attr("readonly", true);
    $opener.$("input[name=cert_no]").val(<?php echo $js_md5_cert_no; ?>);

    alert("본인의 휴대폰번호로 확인 되었습니다.");

    if ($opener.$("form[name=fcertrefreshform]").length) {
        $opener.$("form[name=fcertrefreshform]").submit();
    }

    window.close();
});
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');

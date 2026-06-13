<?php
include_once('./_common.php');

setlocale(LC_CTYPE, 'ko_KR.UTF-8');

$page_type = isset($_GET['pageType']) ? $_GET['pageType'] : '';

switch ($page_type) {
    case 'register':
        $result_url = G5_KCPCERT_V2_URL.'/kcpcert_result.php';
        break;
    case 'find':
        $result_url = G5_KCPCERT_V2_URL.'/find_kcpcert_result.php';
        break;
    default:
        alert_close('잘못된 접근입니다.');
}

certify_count_check($member['mb_id'], 'hp');

include_once(G5_KCPCERT_V2_PATH.'/kcpcert_config.php');

$ordr_idxx = get_session('ss_uniqid');
if (!$ordr_idxx) $ordr_idxx = get_uniqid();

$api = new C_KCP_API_V2($site_cd, $kcp_enc_key, $cert_reg_url, $cert_dec_url);

$reg = $api->trade_reg($ordr_idxx, $result_url, $web_siteid);

if ($reg['res_cd'] !== '0000' || !$reg['call_url'] || !$reg['reg_cert_key']) {
    $err = '본인확인 거래등록에 실패했습니다.\n('.$reg['res_cd'].' : '.$reg['res_msg'].')';
    alert_close($err);
}

set_session('ss_kcp_v2_reg_cert_key', $reg['reg_cert_key']);
set_session('ss_kcp_v2_ordr_idxx',    $ordr_idxx);
set_session('ss_kcp_v2_page_type',    $page_type);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php if (is_mobile()) { ?>
<meta name="viewport" content="user-scalable=yes, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, width=device-width, target-densitydpi=medium-dpi">
<?php } ?>
<title>휴대폰 본인확인</title>
</head>
<body oncontextmenu="return false;" ondragstart="return false;" onselectstart="return false;">
<form name="form_auth" method="post" action="<?php echo htmlspecialchars($reg['call_url'], ENT_QUOTES); ?>">
    <input type="hidden" name="reg_cert_key" value="<?php echo htmlspecialchars($reg['reg_cert_key'], ENT_QUOTES); ?>">
    <input type="hidden" name="kcp_page_submit_yn" value="Y">
</form>
<script>
window.onload = function() {
    document.form_auth.submit();
};
</script>
</body>
</html>

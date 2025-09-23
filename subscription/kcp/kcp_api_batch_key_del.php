<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

include_once G5_SUBSCRIPTION_PATH.'/settle_kcp.inc.php';

/* ============================================================================== */
/* =   배치키 배치키 삭제 요청 API                                                      = */
/* = -------------------------------------------------------------------------- = */

$site_cd = get_subs_option('su_kcp_mid');
$group_id = get_subs_option('su_kcp_group_id');
$kcp_cert_info = get_subs_option('su_kcp_cert_info');

// 반드시 값이 존재해야 한다.
if (!($site_cd && $group_id && $kcp_cert_info && $batch_key)) {
    return;
}

$data = array(            
    "site_cd"        => $site_cd,
    "kcp_cert_info"  => $kcp_cert_info,              
    "batch_key"      => $batch_key,
    "group_id"       => $group_id,
    "pay_method"     => "BATCH",
    "tx_type"        => "10005010"
);

$req_data = json_encode($data);

$header_data = array( "Content-Type: application/json", "charset=utf-8" );

// API REQ
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $kcp_target_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $req_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// API RES
$res_data  = curl_exec($ch); 

// 응답데이터 예:
// {"res_msg":"정상처리","res_cd":"0000"}

curl_close($ch); 

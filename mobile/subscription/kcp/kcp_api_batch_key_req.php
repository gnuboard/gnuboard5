<?php

if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'kcp') {
    return;
}

include_once(G5_MSUBSCRIPTION_PATH.'/settle_kcp.inc.php');

/* ============================================================================== */
/* =   배치키 발급 요청 API                                                      = */
/* = -------------------------------------------------------------------------- = */


/* ============================================================================== */
/* =  요청정보                                                                   = */
/* = -------------------------------------------------------------------------- = */
$tran_cd = isset($_POST['tran_cd']) ? $_POST['tran_cd'] : ''; // 요청 코드
$post_site_cd = isset($_POST['site_cd']) ? $_POST['site_cd'] : ''; // 사이트 코드
$site_cd = get_subs_option('su_kcp_mid'); // 사이트 코드

if ($post_site_cd !== $site_cd) {
    alert("요청한 사이트코드가 유효하지 않습니다.");
    exit;
}

// 인증서 정보(직렬화)
$kcp_cert_info = get_subs_option('su_kcp_cert_info');

$enc_data = isset($_POST['enc_data']) ? $_POST['enc_data'] : ''; // 암호화 인증데이터
$enc_info = isset($_POST['enc_info']) ? $_POST['enc_info'] : ''; // 암호화 인증데이터

$data = array(
    'tran_cd' => $tran_cd,
    'site_cd' => $site_cd,
    'kcp_cert_info' => $kcp_cert_info,
    'enc_data' => $enc_data,
    'enc_info' => $enc_info,
);

$req_data = json_encode($data);

$header_data = array( "Content-Type: application/json", "charset=utf-8" );

// API REQ
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $target_URL);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header_data); 
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $req_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

// API RES
$res_data = curl_exec($ch);

curl_close($ch);

$response = json_decode($res_data, true);

$res_cd = isset($response['res_cd']) ? $response['res_cd'] : '';

if ($res_cd !== '0000') {
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
// [card_name] => **카드
// [van_tx_id] =>
// [card_bin_type_01] => 0
// [res_cd] => 0000
// [join_cd] =>

$card_cd = isset($response['card_cd']) ? $response['card_cd'] : '';
// 승인결과
// 카드번호 마스킹된 번호
$card_mask_number = isset($_POST['card_mask_no']) ? mask_card_number($_POST['card_mask_no']) : '****';
$card_billkey = $response['batch_key'];
// $card_name = $response['card_name'];
$card_name = ($card_cd && isset($kcp_card_codes[$card_cd])) ? $kcp_card_codes[$card_cd] : $response['card_name'];
// NHN_KCP는 tno값이 없다.
$tno = '';
$amount = $order_price;
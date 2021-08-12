<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G5_MSHOP_PATH.'/settle_inicis.inc.php');

$post_p_hash = isset($_POST['P_HASH']) ? $_POST['P_HASH'] : '';

// 세션비교
$hash = md5(get_session('P_TID').$default['de_inicis_mid'].get_session('P_AMT'));

if($hash !== $post_p_hash)
    alert('결제 정보가 일치하지 않습니다. 올바른 방법으로 이용해 주십시오.');

//최종결제요청 결과 성공 DB처리
$tno             = get_session('P_TID');
$amount          = get_session('P_AMT');
$app_time        = isset($_POST['P_AUTH_DT']) ? $_POST['P_AUTH_DT'] : '';
$pay_method      = isset($_POST['P_TYPE']) ? $_POST['P_TYPE'] : '';
$pay_type        = isset($PAY_METHOD[$pay_method]) ? $PAY_METHOD[$pay_method] : '';
$depositor       = isset($_POST['P_UNAME']) ? $_POST['P_UNAME'] : '';
$commid          = isset($_POST['P_HPP_CORP']) ? $_POST['P_HPP_CORP'] : '';
$mobile_no       = isset($_POST['P_APPL_NUM']) ? $_POST['P_APPL_NUM'] : '';
$app_no          = isset($_POST['P_AUTH_NO']) ? $_POST['P_AUTH_NO'] : '';
$card_name       = isset($_POST['P_CARD_ISSUER']) ? $_POST['P_CARD_ISSUER'] : '';

if ($default['de_escrow_use'] == 1) {
    $escw_yn         = 'Y';
}

$post_p_vact_bank = isset($_POST['P_VACT_BANK']) ? $_POST['P_VACT_BANK'] : '';
$post_p_vact_num = isset($_POST['P_VACT_NUM']) ? $_POST['P_VACT_NUM'] : '';
$post_p_vact_name = isset($_POST['P_VACT_NAME']) ? $_POST['P_VACT_NAME'] : '';

switch($pay_type) {
    case '계좌이체':
        $bank_name = $post_p_vact_bank;
        break;
    case '가상계좌':
        $bankname  = $post_p_vact_bank;
        $account   = $post_p_vact_num.' '.$post_p_vact_name;
        $app_no    = $post_p_vact_num;
        break;
    default:
        break;
}

// 세션 초기화
set_session('P_TID',  '');
set_session('P_AMT',  '');
set_session('P_HASH', '');
<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G5_MSHOP_PATH.'/settle_nicepay.inc.php');

// 세션비교
$hash = md5(get_session('NICE_TID').$default['de_nicepay_mid'].get_session('NICE_AMT'));

if($hash !== $post_nice_hash)
    alert('결제 정보가 일치하지 않습니다. 올바른 방법으로 이용해 주십시오.', $page_return_url);

//최종결제요청 결과 성공 DB처리
$tno             = get_session('NICE_TID');
$amount          = get_session('NICE_AMT');
$app_time        = isset($_POST['AuthDate']) ? "20".$_POST['AuthDate'] : '';
$pay_method      = isset($_POST['PayMethod']) ? $_POST['PayMethod'] : '';
$pay_type        = isset($PAY_METHOD[$pay_method]) ? $PAY_METHOD[$pay_method] : '';
$depositor       = isset($_POST['VbankAccountName']) ? $_POST['VbankAccountName'] : '';
$commid          = isset($_POST['Carrier']) ? $_POST['Carrier'] : '';
$mobile_no       = isset($_POST['DstAddr']) ? $_POST['DstAddr'] : '';
$app_no          = isset($_POST['AuthCode']) ? $_POST['AuthCode'] : '';
$card_name       = isset($_POST['CardName']) ? $_POST['CardName'] : '';

// 에스크로 사용여부 확인
if ($default['de_escrow_use'] == 1) {
    $escw_yn         = 'Y';
}

// 가상계좌, 계좌이체 관련 데이터 검증
$post_bank_name = isset($_POST['BankName']) ? $_POST['BankName'] : '';
$post_vbank_name = isset($_POST['VbankBankName']) ? $_POST['VbankBankName'] : '';
$post_vbank_num = isset($_POST['VbankNum']) ? $_POST['VbankNum'] : '';
$post_vbank_account_name = isset($_POST['VbankAccountName']) ? $_POST['VbankAccountName'] : '';

switch($pay_type) {
    case '계좌이체':
        $bank_name = $post_bank_name;
        break;
    case '가상계좌':
        $bankname  = $post_vbank_name;
        $account   = $post_vbank_num.' '.$post_vbank_account_name;
        $app_no    = $post_vbank_num;
        break;
    default:
        break;
}

// 세션 초기화
set_session('NICE_TID',  '');
set_session('NICE_AMT',  '');
set_session('NICE_HASH', '');
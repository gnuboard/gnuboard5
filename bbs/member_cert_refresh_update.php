<?php
define('G5_CERT_IN_PROG', true);
include_once('./_common.php');
global $g5;

if (!($w == '' || $w == 'u')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

if($w == '') {
    $mb_id = isset($_POST['mb_id']) ? trim($_POST['mb_id']) : '';
    $mb_name = isset($_POST['mb_name']) ? trim($_POST['mb_name']) : '';
    $mb_hp = isset($_POST['mb_hp']) ? trim($_POST['mb_hp']) : '';
} else
    alert('잘못된 접근입니다', G5_URL);

if(!$mb_id)
    alert('회원아이디 값이 없습니다. 올바른 방법으로 이용해 주십시오.');

//===============================================================
//  본인확인
//---------------------------------------------------------------
$mb_hp = hyphen_hp_number($mb_hp);
if($config['cf_cert_use'] && get_session('ss_cert_type') && get_session('ss_cert_dupinfo')) {
    // 중복체크
    $sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
    $row = sql_fetch($sql);
    if ($row['mb_id']) {
        alert("입력하신 본인확인 정보로 가입된 내역이 존재합니다.");
    }
}

$sql = '';
$sql_certify = '';
$md5_cert_no = get_session('ss_cert_no');
$cert_type = get_session('ss_cert_type');
if ($config['cf_cert_use'] && $cert_type && $md5_cert_no) {
    // 해시값이 같은 경우에만 본인확인 값을 저장한다.
    if (get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$md5_cert_no)) {
        $sql_certify .= " mb_hp = '{$mb_hp}' ";
        $sql_certify .= " , mb_certify  = '{$cert_type}' ";
        $sql_certify .= " , mb_adult = '".get_session('ss_cert_adult')."' ";
        $sql_certify .= " , mb_birth = '".get_session('ss_cert_birth')."' ";
        $sql_certify .= " , mb_sex = '".get_session('ss_cert_sex')."' ";
        $sql_certify .= " , mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
        $sql_certify .= " , mb_name = '{$mb_name}' ";
    } else {
        $sql_certify .= " mb_hp = '{$mb_hp}' ";
        $sql_certify .= " , mb_certify  = '' ";
        $sql_certify .= " , mb_adult = 0 ";
        $sql_certify .= " , mb_birth = '' ";
        $sql_certify .= " , mb_sex = '' ";
    }
} else {
    if (get_session("ss_reg_mb_name") != $mb_name || get_session("ss_reg_mb_hp") != $mb_hp) {
        $sql_certify .= " mb_hp = '{$mb_hp}' ";
        $sql_certify .= " , mb_certify = '' ";
        $sql_certify .= " , mb_adult = 0 ";
        $sql_certify .= " , mb_birth = '' ";
        $sql_certify .= " , mb_sex = '' ";
    }
}

$sql = "update {$g5['member_table']} set {$sql_certify} where mb_id = '{$mb_id}'";
sql_query($sql);

//===============================================================

goto_url(G5_URL);
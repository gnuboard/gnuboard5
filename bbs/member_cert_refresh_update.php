<?php
define('G5_CERT_IN_PROG', true);
include_once('./_common.php');
global $g5;

// 로그인 검증 (본인인증 갱신은 로그인 필수)
if (!$is_member) {
    alert('로그인 후 이용해 주십시오.', G5_BBS_URL.'/login.php');
}

// 본인인증 갱신은 갱신 화면에서 발급한 1회용 토큰을 가진 같은 출처의 POST만 허용한다. (KVE-2026-3201)
if (!isset($_SERVER['REQUEST_METHOD']) || strtoupper($_SERVER['REQUEST_METHOD']) !== 'POST') {
    alert('올바른 방법으로 이용해 주십시오.', G5_URL);
}
check_request_origin();
$cert_refresh_token = isset($_POST['token']) && is_string($_POST['token']) ? $_POST['token'] : '';
if (!g5_check_form_token('cert_refresh', $member['mb_id'], $cert_refresh_token)) {
    alert('올바른 방법으로 이용해 주십시오. 본인인증 화면을 다시 열어 주십시오.', G5_BBS_URL.'/member_cert_refresh.php');
}

// 갱신 화면과 같은 진입 조건을 다시 확인한다.
if (!$config['cf_cert_use']) {
    alert('본인인증을 이용 할 수 없습니다. 관리자에게 문의 하십시오.', G5_URL);
}
if (!empty($member['mb_certify']) && strlen($member['mb_dupinfo']) != 64) {
    alert('잘못된 접근입니다.', G5_URL);
}

if (!($w == '' || $w == 'u')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}
$url = urldecode($url);

if($w == '') {
    // 본인 계정만 수정 가능 (POST mb_id 무시, 세션 회원 ID 사용)
    $mb_id = $member['mb_id'];
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
    if (!empty($row['mb_id'])) {
        alert("입력하신 본인확인 정보로 가입된 내역이 존재합니다.");
    }
}

$sql = '';
$sql_certify = '';
$md5_cert_no = get_session('ss_cert_no');
$cert_type = get_session('ss_cert_type');
if ($config['cf_cert_use'] && $cert_type && $md5_cert_no) {
    // 해시값이 같은 경우에만 본인확인 값을 저장한다.
    if ($cert_type == 'ipin' && get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$md5_cert_no)) { // 아이핀일때 hash 값 체크 hp미포함
        $sql_certify .= " mb_hp = '{$mb_hp}' ";
        $sql_certify .= " , mb_certify  = '{$cert_type}' ";
        $sql_certify .= " , mb_adult = '".get_session('ss_cert_adult')."' ";
        $sql_certify .= " , mb_birth = '".get_session('ss_cert_birth')."' ";
        $sql_certify .= " , mb_sex = '".get_session('ss_cert_sex')."' ";
        $sql_certify .= " , mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
        $sql_certify .= " , mb_name = '{$mb_name}' ";
    } else if($cert_type != 'ipin' && get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$mb_hp.$md5_cert_no)) { // 간편인증, 휴대폰일때 hash 값 체크 hp포함
        $sql_certify .= " mb_hp = '{$mb_hp}' ";
        $sql_certify .= " , mb_certify  = '{$cert_type}' ";
        $sql_certify .= " , mb_adult = '".get_session('ss_cert_adult')."' ";
        $sql_certify .= " , mb_birth = '".get_session('ss_cert_birth')."' ";
        $sql_certify .= " , mb_sex = '".get_session('ss_cert_sex')."' ";
        $sql_certify .= " , mb_dupinfo = '".get_session('ss_cert_dupinfo')."' ";
        $sql_certify .= " , mb_name = '{$mb_name}' ";
    }else {
        alert('본인인증된 정보와 입력된 회원정보가 일치하지않습니다. 다시시도 해주세요');
    }
} else {
    // 유효한 본인인증 결과가 없으면 회원정보를 바꾸지 않는다. (KVE-2026-3201)
    alert('본인인증을 완료한 뒤 다시 시도해 주십시오.', G5_BBS_URL.'/member_cert_refresh.php');
}

$sql = "update {$g5['member_table']} set {$sql_certify} where mb_id = '{$mb_id}'";
$result = sql_query($sql, false);

if($result){
    if($cert_type == 'ipin' && get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$md5_cert_no)) { // 아이핀일때 hash 값 체크 hp미포함)
        insert_member_cert_history($mb_id, $mb_name, $mb_hp, get_session('ss_cert_birth'), get_session('ss_cert_type') ); // 본인인증 후 정보 수정 시 내역 기록
    }else if($cert_type != 'ipin' && get_session('ss_cert_hash') == md5($mb_name.$cert_type.get_session('ss_cert_birth').$mb_hp.$md5_cert_no)) { // 간편인증, 휴대폰일때 hash 값 체크 hp포함
        insert_member_cert_history($mb_id, $mb_name, $mb_hp, get_session('ss_cert_birth'), get_session('ss_cert_type') ); // 본인인증 후 정보 수정 시 내역 기록
    }
}

run_event('cert_refresh_update_after', $mb_id);

// 사용한 본인인증 결과는 다시 쓰지 못하게 비운다. (KVE-2026-3201)
foreach (array('ss_cert_type', 'ss_cert_no', 'ss_cert_hash', 'ss_cert_adult', 'ss_cert_birth', 'ss_cert_sex', 'ss_cert_dupinfo') as $ss_cert_name) {
    set_session($ss_cert_name, '');
}

//===============================================================

if ($url) {
    check_url_host($url, '', G5_URL, true);
    goto_url($url);
} else {
    goto_url(G5_URL);
}
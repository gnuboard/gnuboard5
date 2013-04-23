<?php
include_once('./_common.php');

$w        = trim($_POST['mb_hp']);
$reg_hp   = preg_replace("/[^0-9]/", "", trim($_POST['mb_hp'])); // 숫자외에 문자는 제거
$old_hp   = preg_replace("/[^0-9]/", "", trim($_POST['old_mb_hp']));

if ($w=='' || ($w=='u' && $reg_hp != $old_hp)) {
    // 본인인증체크
    //$kcpcert_no = trim($_POST['kcpcert_no']);
    $kcpcert_no = get_session('ss_kcpcert_no');
    if(!$kcpcert_no)
        die("{\"error\":\"휴대폰인증이 되지 않았습니다. 휴대폰인증을 해주세요.\"}");

    // 본인인증 hash 체크
    $reg_name = trim($_POST['mb_name']); 
    $reg_hash = md5($reg_hp.$reg_name.$kcpcert_no);
    $ss_hash  = get_session('ss_kcpcert_hash');
    if(get_session('ss_kcpcert_hash') != $reg_hash)
        die("{\"error\":\"이름 또는 휴대폰번호가 올바르지 않습니다. 정상적인 방법으로 이용해 주세요.\"}");

    /*
    // 성인인증을 사용한다면
    if($config['cf_adult_check']) {
        if(get_session('ss_adult_check') != 'Y')
            die('성인인증이 되지 않았습니다. 다음에 다시 가입해 주세요.');
    }
    */
}

die("{\"error\":\"\"}"); // 정상
?>
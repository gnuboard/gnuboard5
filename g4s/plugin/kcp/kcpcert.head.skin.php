<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 핸드폰번호 체크
if ($msg = valid_mb_hp($mb_hp))   alert($msg, "", true, true);
if ($msg = exist_mb_hp($mb_hp, $mb_id))   alert($msg, "", true, true);

$reg_hp = hyphen_hp_number($mb_hp);

/* ======================================================================================================= */
/* = 휴대폰인증 및 성인인증                                                                              = */
/* ======================================================================================================= */
if($w == '') {
    // 본인인증체크
    $kcpcert_no = get_session('ss_kcpcert_no');
    if(!$kcpcert_no)
        alert('휴대폰인증이 되지 않았습니다. 휴대폰인증을 해주세요.', '', true, true);

    // 본인인증 hash 체크
    $reg_hash = md5($reg_hp.$mb_name.$kcpcert_no);
    if(get_session('ss_kcpcert_hash') != $reg_hash)
        alert('이름 또는 휴대폰번호가 올바르지 않습니다. 정상적인 방법으로 이용해 주세요.', '', true, true);

} else if($w == 'u') {
    // 휴대폰번호 변경체크
    $old_hp = preg_replace("/[^0-9]/", "", $_POST['old_mb_hp']);

    if($old_hp !== $reg_hp) {
        // 본인인증체크
        $kcpcert_no = get_session('ss_kcpcert_no');
        if(!$kcpcert_no)
            alert('휴대폰번호가 변경됐습니다. 휴대폰인증을 해주세요.', '', true, true);

        // 본인인증 hash 체크
        $reg_hash = md5($reg_hp.$mb_name.$kcpcert_no);
        if(get_session('ss_kcpcert_hash') != $reg_hash)
            alert('이름 또는 휴대폰인증 정보가 올바르지 않습니다. 정상적인 방법으로 이용해 주세요.', '', true, true);
    }
}
/* ======================================================================================================= */
?>
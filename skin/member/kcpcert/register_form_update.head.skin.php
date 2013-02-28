<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 자신만의 코드를 넣어주세요.

// 핸드폰번호 중복체크
$reg_hp = preg_replace("/[^0-9]/", "", $mb_hp);
if(!$reg_hp)
    alert('핸드폰번호를 입력해 주세요.', "", true, true);

$hp_len = strlen($reg_hp);

if($hp_len == 10)
    $reg_mb_hp = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "\\1-\\2-\\3", $reg_hp);
else if($hp_len == 11)
    $reg_mb_hp = preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "\\1-\\2-\\3", $reg_hp);
else
    alert('핸드폰번호가 올바르지 않습니다.', "", true, true);

$sql2 = " select count(*) as cnt from {$g4['member_table']} where mb_hp = '$reg_mb_hp' and mb_id <> '$mb_id' ";
$row2 = sql_fetch($sql2);
if($row2['cnt'])
    alert('입력하신 핸드폰번호는 다른 회원님이 사용 중입니다.', "", true, true);

/* ======================================================================================================= */
/* = 휴대폰인증 및 성인인증                                                                              = */
/* ======================================================================================================= */
if($w == '') {
    // 본인인증체크
    $kcpcert_no = trim($_POST['kcpcert_no']);
    if(!$kcpcert_no)
        alert('휴대폰인증이 되지 않았습니다. 휴대폰인증을 해주세요.', "", true, true);

    // 본인인증 hash 체크
    $reg_hash = md5($reg_hp.$mb_name.$kcpcert_no);
    if(get_session('ss_kcpcert_hash') != $reg_hash)
        alert('휴대폰인증 정보가 올바르지 않습니다. 정상적인 방법으로 이용해 주세요.', "", true, true);

    // 성인인증을 사용한다면
    if($config['cf_adult_check']) {
        if(get_session('ss_adult_check') != 'Y')
            alert('성인인증이 되지 않았습니다. 다음에 다시 가입해 주세요.', "", true, true);
    }
} else if($w == 'u') {
    // 휴대폰번호 변경체크
    $old_hp = preg_replace("/[^0-9]/", "", $_POST['old_mb_hp']);

    if($old_hp != $reg_hp) {
        // 본인인증체크
        $kcpcert_no = trim($_POST['kcpcert_no']);
        if(!$kcpcert_no)
            alert('휴대폰번호가 변경됐습니다. 휴대폰인증을 해주세요.', "", true, true);

        // 본인인증 hash 체크
        $reg_hash = md5($reg_hp.$mb_name.$kcpcert_no);
        if(get_session('ss_kcpcert_hash') != $reg_hash)
            alert('휴대폰인증 정보가 올바르지 않습니다. 정상적인 방법으로 이용해 주세요.', "", true, true);
    }
}
/* ======================================================================================================= */
?>
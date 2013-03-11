<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 조회회수 쿠키에 저장
if(!(int)$pl_count = get_cookie('ck_passwordlost_count')) {
    set_cookie('ck_passwordlost_count', 1, 60*60*1);
} else {
    //if($pl_count > 2) {
    //    alert_close('아이디/패스워드 찾기를 기준 횟수 이상 시도하였습니다.');
    //} else {
    //    $pl_count++;
    //    set_cookie('ck_passwordlost_count', $pl_count, 60*60*1);
    //}
}

$mb_name = trim($_POST['mb_name']);
$mb_hp = preg_replace("/[^0-9]/", "", $_POST['mb_hp']);

if(!$mb_name)
    alert_close('회원 이름을 입력해 주세요.');

if(!$mb_hp)
    alert_close('핸드폰번호를 입력해 주세요.');

// 휴대폰인증체크
$kcpcert_no = trim($_POST['kcpcert_no']);
if(!$kcpcert_no)
    alert_close('휴대폰인증이 되지 않았습니다. 휴대폰인증을 해주세요.');

// 본인인증 hash 체크
$reg_hash = md5($mb_hp.$mb_name.$kcpcert_no);
if(get_session('ss_kcpcert_hash') != $reg_hash)
    alert_close('휴대폰인증 정보가 올바르지 않습니다. 정상적인 방법으로 이용해 주세요.');

$len = strlen($mb_hp);
if($len == 10)
    $s_mb_hp = preg_replace("/([0-9]{3})([0-9]{3})([0-9]{4})/", "\\1-\\2-\\3", $mb_hp);
else if($len == 11)
    $s_mb_hp = preg_replace("/([0-9]{3})([0-9]{4})([0-9]{4})/", "\\1-\\2-\\3", $mb_hp);

$sql = " select count(*) as cnt from {$g4['member_table']} where mb_hp = '$s_mb_hp' ";
$row = sql_fetch($sql);
if ($row['cnt'] > 1)
    alert('동일한 핸드폰번호가 2개 이상 존재합니다.\\n\\n관리자에게 문의하여 주십시오.');

$sql = " select mb_id from {$g4['member_table']} where mb_name = '$mb_name' and mb_hp = '$s_mb_hp' ";
$mb = sql_fetch($sql);
if (!$mb['mb_id'])
    alert('존재하지 않는 회원입니다.');
else if (is_admin($mb['mb_id']))
    alert('관리자 아이디는 접근 불가합니다.');

// 난수 발생
srand(time());
$randval = rand(4, 6);

$change_password = substr(md5(get_microtime()), 0, $randval);

$sql = " update {$g4['member_table']}
            set mb_password = '".sql_password($change_password)."'
            where mb_id = '{$mb['mb_id']}' ";
sql_query($sql);

$g4['title'] = '회원정보 찾기 결과';
include_once(G4_PATH.'/head.sub.php');
?>

<div id="find_info_result" class="new_win">
    <h1>회원정보 찾기 결과</h1>

    <div id="find_info_result_wrap">
        <p>
            회원님의 아이디와 변경된 패스워드입니다.<br>
            로그인 후 패스워드를 변경해 주세요.
        </p>
        <ul>
            <li><span>아이디</span> <?=$mb['mb_id']?></li>
            <li><span>패스워드</span> <strong><?=$change_password?></strong></li>
        </ul>
    </div>
    <div class="btn_win">
        <a href="javascript:window.close();" class="btn_cancel">확인</a>
    </div>

</div>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>
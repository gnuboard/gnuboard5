<?
die("이 프로그램은 더 이상 사용하지 않습니다. 그누보드 4.32.09 를 참고하세요.");
include_once("./_common.php");

/*
$wr_key = trim($_POST[wr_key]);
if (!($wr_key && $wr_key == get_session('ss_norobot_key'))) {
    alert("정상적인 접근이 아닌것 같습니다.");
}
*/

$key = get_session("captcha_keystring");
if (!($key && $key == $_POST[wr_key])) {
    session_unregister("captcha_keystring");
    alert_close("정상적인 접근이 아닌것 같습니다.");
}

$sql = " select mb_id, mb_nick, mb_password_a, mb_email from $g4[member_table] where mb_id = '$_POST[pass_mb_id]' ";
$mb = sql_fetch($sql);
if (!$mb[mb_id]) 
    alert("존재하지 않는 회원입니다.");
else if ($mb_password_a !== $mb[mb_password_a]) 
    alert("패스워드 분실 시 답변이 틀립니다.");
else if (is_admin($mb[mb_id])) 
    alert("관리자 아이디는 접근 불가합니다.");

$g4[title] = "패스워드 찾기 3단계";
include_once("$g4[path]/head.sub.php");

// 난수 발생
list($usec, $sec) = explode(" ", microtime()); 
$seed =  (float)$sec + ((float)$usec * 100000); 
srand($seed);
$randval = rand(4, 6); 

$change_password = substr(md5(get_microtime()), 0, $randval);
$sql = " update $g4[member_table]
            set mb_password = '".sql_password($change_password)."'
          where mb_id = '$mb[mb_id]' ";
sql_query($sql);

$member_skin_path = "$g4[path]/skin/member/$config[cf_member_skin]";
include_once("$member_skin_path/password_forget3.skin.php");

include_once("$g4[path]/tail.sub.php");
?>
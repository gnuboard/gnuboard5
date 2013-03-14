<?
include_once("./_common.php");
include_once("$g4[path]/lib/mailer.lib.php");

if ($member[mb_id]) 
{
    echo "<script type='text/javascript'>";
    echo "alert('이미 로그인중입니다.');";
    echo "window.close();";
    echo "opener.document.location.reload();";
    echo "</script>";
    exit;
}

$key = get_session("captcha_keystring");
if (!($key && $key == $_POST[wr_key])) {
    unset($_SESSION['captcha_keystring']);
    alert_close("정상적인 접근이 아닌것 같습니다.");
}

$email = trim($_POST['mb_email']);

if (!$email) 
    alert_close("메일주소 오류입니다.");

$sql = " select count(*) as cnt from $g4[member_table] where mb_email = '$email' ";
$row = sql_fetch($sql);
if ($row[cnt] > 1)
    alert("동일한 메일주소가 2개 이상 존재합니다.\\n\\n관리자에게 문의하여 주십시오.");

$sql = " select mb_no, mb_id, mb_name, mb_nick, mb_email, mb_datetime from $g4[member_table] where mb_email = '$email' ";
$mb = sql_fetch($sql);
if (!$mb[mb_id]) 
    alert("존재하지 않는 회원입니다.");
else if (is_admin($mb[mb_id])) 
    alert("관리자 아이디는 접근 불가합니다.");

// 난수 발생
srand(time());
$randval = rand(4, 6); 

$change_password = substr(md5(get_microtime()), 0, $randval);

$mb_lost_certify = sql_password($change_password);
$mb_datetime     = sql_password($mb[mb_datetime]);

// 회원테이블에 필드를 추가
sql_query(" ALTER TABLE `$g4[member_table]` ADD `mb_lost_certify` VARCHAR( 255 ) NOT NULL AFTER `mb_memo` ", false);

$sql = " update $g4[member_table]
            set mb_lost_certify = '$mb_lost_certify'
          where mb_id = '$mb[mb_id]' ";
sql_query($sql);

$href = "$g4[url]/$g4[bbs]/password_lost_certify.php?mb_no=$mb[mb_no]&mb_datetime=$mb_datetime&mb_lost_certify=$mb_lost_certify";

$subject = "요청하신 회원아이디/패스워드 정보입니다.";

$content = "";
$content .= "<div style='line-height:180%;'>";
$content .= "<p>요청하신 계정정보는 다음과 같습니다.</p>";
$content .= "<hr>";
$content .= "<ul>";
$content .= "<li>회원아이디 : $mb[mb_id]</li>";
$content .= "<li>변경 패스워드 : <span style='color:#ff3300; font:13px Verdana;'><strong>$change_password</strong></span></li>";
$content .= "<li>이름 : ".addslashes($mb[mb_name])."</li>";
$content .= "<li>별명 : ".addslashes($mb[mb_nick])."</li>";
$content .= "<li>이메일주소 : ".addslashes($mb[mb_email])."</li>";
$content .= "<li>요청일시 : $g4[time_ymdhis]</li>";
$content .= "<li>홈페이지 : $g4[url]</li>";
$content .= "</ul>";
$content .= "<hr>";
$content .= "<p><a href='$href' target='_blank'>$href</a></p>";
$content .= "<p>";
$content .= "1. 위의 링크를 클릭하십시오. 링크가 클릭되지 않는다면 링크를 브라우저의 주소창에 직접 복사해 넣으시기 바랍니다.<br />";
$content .= "2. 링크를 클릭하시면 패스워드가 변경 되었다는 인증 메세지가 출력됩니다.<br />";
$content .= "3. 홈페이지에서 회원아이디와 위에 적힌 변경 패스워드로 로그인 하십시오.<br />";
$content .= "4. 로그인 하신 후 새로운 패스워드로 변경하시면 됩니다.";
$content .= "</p>";
$content .= "<p>감사합니다.</p>";
$content .= "<p>[끝]</p>";
$content .= "</div>";

$admin = get_admin('super');
mailer($admin[mb_nick], $admin[mb_email], $mb[mb_email], $subject, $content, 1);

alert_close("$email 메일로 회원아이디와 패스워드를 인증할 수 있는 메일이 발송 되었습니다.\\n\\n메일을 확인하여 주십시오.");
?>
<?
$sub_menu = "100300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

if (!$config[cf_email_use])
    alert("환경설정에서 \'메일발송 사용\'에 체크하셔야 메일을 발송할 수 있습니다.");

include_once("$g4[path]/lib/mailer.lib.php");

$g4[title] = "메일 테스트";
include_once("./admin.head.php");

if ($mail) {
    check_token();

    $from_name  = "메일검사";
    $from_email = "mail@mail";

    $email = explode(",", $mail);
    for ($i=0; $i<count($email); $i++)
        mailer($from_name, $from_email, trim($email[$i]), "[메일검사] 제목", "<span style='font-size:9pt;'>[메일검사] 내용<p>이 내용이 제대로 보인다면 보내는 메일 서버에는 이상이 없는것입니다.<p>".date("Y-m-d H:i:s")."<p>이 메일 주소로는 회신되지 않습니다.</span>", 1);

    echo <<<HEREDOC
    <SCRIPT type="text/javascript">
        alert("{$mail} (으)로 메일을 발송 하였습니다.\\n\\n해당 주소로 메일이 왔는지 확인하여 주십시오.\\n\\n메일이 오지 않는다면 프로그램의 오류가 아닌 메일 서버(sendmail)의 오류일 가능성이 있습니다.\\n\\n이런 경우에는 웹 서버관리자에게 문의하여 주십시오.");
    </SCRIPT>
HEREDOC;
}

$token = get_token();
?>

<img src='<?=$g4[admin_path]?>/img/icon_title.gif'> <span class=title><?=$g4[title]?></span>
<p>

<form name=fsendmailtest method=post>
<input type=hidden name=token value='<?=$token?>'>
<p>고객님들께서 메일이 오지 않는다고 하면 사용하는 메뉴입니다.
<p>입력한 메일주소로 테스트 메일을 발송합니다.
<p>만약 [메일검사] 라는 내용으로 메일이 도착하지 않는다면 보내는 메일서버와 받는 메일 서버중 문제가 발생했을 가능성이 있습니다.
<p>메일을 보냈는데도 도착하지 않는다면 다른 여러곳으로도 메일을 발송하여 주십시오.
<p>여러곳으로 메일을 발송하시려면 , 로 메일을 구분하십시오.
<p>받는 메일주소 : <input type=text class=ed name=mail size=40 required itemname="E-mail" value="<?=$member[mb_email]?>">
<input type=submit value="  발  송  " class=btn1>
</form>

<?
include_once("./admin.tail.php");
?>

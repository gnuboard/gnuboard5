<?
$sub_menu = "200300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "w");

$html_title = "회원메일 발송";

check_demo();

check_token();

include_once("./admin.head.php");
include_once("$g4[path]/lib/mailer.lib.php");

$countgap = 10; // 몇건씩 보낼지 설정
$maxscreen = 500; // 몇건씩 화면에 보여줄건지?
$sleepsec = 200;  // 천분의 몇초간 쉴지 설정

echo "<span style='font-size:9pt;'>";
echo "<p>메일 발송중 ...<p><font color=crimson><b>[끝]</b></font> 이라는 단어가 나오기 전에는 중간에 중지하지 마세요.<p>";
echo "</span>";
?>

<span id="cont"></span>

<?
include_once("./admin.tail.php");
?>

<?
flush();
ob_flush();

$ma_id = trim($_POST[ma_id]); 
$select_member_list = addslashes(trim($_POST[ma_list])); 

//print_r2($_POST); EXIT;
$member_list = explode("\n", $select_member_list);

// 메일내용 가져오기
$sql = "select ma_subject, ma_content from $g4[mail_table] where ma_id = '$ma_id' ";
$ma = sql_fetch($sql);

$subject = $ma[ma_subject];

$cnt = 0;
for ($i=0; $i<count($member_list); $i++) 
{
    list($email, $mb_id, $name, $nick, $birth, $datetime) = explode("||", trim($member_list[$i]));

    $sw = preg_match("/[0-9a-zA-Z_]+(\.[0-9a-zA-Z_]+)*@[0-9a-zA-Z_]+(\.[0-9a-zA-Z_]+)*/", $email);
    // 올바른 메일 주소만
    if ($sw == true) 
    {
        $cnt++;

        $mb_md5 = md5($mb_id.$email.$datetime);

        $content = $ma[ma_content];
        $content = preg_replace("/{이름}/", $name, $content);
        $content = preg_replace("/{별명}/", $nick, $content);
        $content = preg_replace("/{회원아이디}/", $mb_id, $content);
        $content = preg_replace("/{이메일}/", $email, $content);
        $content = preg_replace("/{생일}/", (int)substr($birth,4,2).'월 '.(int)substr($birth,6,2).'일', $content);

        $content = $content . "<hr size=0><p><span style='font-size:9pt; font-familye:굴림'>▶ 더 이상 정보 수신을 원치 않으시면 [<a href='$g4[url]/$g4[bbs]/email_stop.php?mb_id=$mb_id&mb_md5=$mb_md5' target='_blank'>수신거부</a>] 해 주십시오.</span></p>";

        /*
        ob_start();
        include "$mail_skin/mail.skin.php";
        $content = ob_get_contents();
        ob_end_clean();
        */

        //mailer($default[de_subject], $default[de_admin_email], $email, $subject, $content, 1);
        mailer($config[cf_title], $member[mb_email], $email, $subject, $content, 1);

        echo "<script> document.all.cont.innerHTML += '$cnt. $email ($mb_id : $name)<br>'; </script>\n";
        //echo "+";
        flush();
        ob_flush();
        ob_end_flush();
        usleep($sleepsec);
        if ($cnt % $countgap == 0) 
        {
            echo "<script> document.all.cont.innerHTML += '<br>'; document.body.scrollTop += 1000; </script>\n";
        }

        // 화면을 지운다... 부하를 줄임
        if ($cnt % $maxscreen == 0)
            echo "<script> document.all.cont.innerHTML = ''; document.body.scrollTop += 1000; </script>\n";
    }
}
?>
<script> document.all.cont.innerHTML += "<br><br>총 <?=number_format($cnt)?>건 발송<br><br><font color=crimson><b>[끝]</b></font>"; document.body.scrollTop += 1000; </script>

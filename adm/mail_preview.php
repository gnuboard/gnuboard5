<?
$sub_menu = "200300";
include_once('./_common.php');
include_once(G4_LIB_PATH.'/mailer.lib.php');

auth_check($auth[$sub_menu], 'r');

$se = sql_fetch("select ma_subject, ma_content from {$g4['mail_table']} where ma_id = '{$ma_id}' ");

$subject = $se['ma_subject'];
$content = $se['ma_content'] . "<hr size=0><p><span style='font-size:9pt; font-family:굴림'>▶ 더 이상 정보 수신을 원치 않으시면 [<a href='".G4_BBS_URL."/email_stop.php?mb_id=***&amp;mb_md5=***' target='_blank'>수신거부</a>] 해 주십시오.</span></p>";
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>그누보드 메일발송 테스트</title>
</head>

<body>

<h1><?=$subject?></h1>

<p>
    <?=$se['ma_content']?>
</p>

<p>
    <strong>주의!</strong> 이 화면에 보여지는 디자인은 실제 내용이 발송되었을 때 디자인과 다를 수 있습니다.
</p>

</body>
</html>
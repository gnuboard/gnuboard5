<?
$sub_menu = "300300";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g4[title] = "다이얼로그관리";
include_once ("$g4[admin_path]/admin.head.php");

$dialog_id = "dialog_".$di_id;
$_COOKIE[$dialog_id] = "";

echo g4_dialog((int)$_GET[di_id]);

include_once ("$g4[admin_path]/admin.tail.php");
?>

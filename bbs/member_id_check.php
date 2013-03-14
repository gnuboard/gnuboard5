<?
include_once("./_common.php");

$g4[title] = "회원아이디 중복확인";
include_once("$g4[path]/head.sub.php");

$mb_id = trim($mb_id);

$mb = get_member($mb_id);
if ($mb[mb_id]) 
{
    echo "<script type=\"text/javascript\">";
    echo "alert(\"'{$mb_id}'은(는) 이미 가입된 회원아이디 이므로 사용하실 수 없습니다.\");";
    echo "parent.document.getElementById(\"mb_id_enabled\").value = -1;";
    echo "window.close();";
    echo "</script>";
} 
else 
{
    if (preg_match("/[\,]?{$mb_id}/i", $config[cf_prohibit_id]))
    {
        echo "<script type=\"text/javascript\">";
        echo "alert(\"'{$mb_id}'은(는) 예약어로 사용하실 수 없는 회원아이디입니다.\");";
        echo "parent.document.getElementById(\"mb_id_enabled\").value = -2;";
        echo "window.close();";
        echo "</script>";
    }
    else
    {
        echo "<script type=\"text/javascript\">";
        echo "alert(\"'{$mb_id}'은(는) 중복된 회원아이디가 없습니다.\\n\\n사용하셔도 좋습니다.\");";
        echo "parent.document.getElementById(\"mb_id_enabled\").value = 1;";
        echo "window.close();";
        echo "</script>";
    }
}

include_once("$g4[path]/tail.sub.php");
?>
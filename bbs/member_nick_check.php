<?
include_once("./_common.php");

$g4[title] = "별명 중복확인";
include_once("$g4[path]/head.sub.php");

$mb_nick = trim($mb_nick);

// 별명은 한글, 영문, 숫자만 가능
if (!check_string($mb_nick, _G4_HANGUL_ + _G4_ALPHABETIC_ + _G4_NUMERIC_)) {
    echo "<script type='text/javascript'>";
    echo "alert(\"별명은 공백없이 한글, 영문, 숫자만 입력 가능합니다.\");";
    echo "parent.document.getElementById('mb_nick_enabled').value = '';";
    echo "window.close();";
    echo "</script>";
    exit;
}

$mb = sql_fetch(" select mb_nick from $g4[member_table] where mb_nick = '$mb_nick' ");
if ($mb[mb_nick]) {
    echo "<script type='text/javascript'>";
    echo "alert(\"'{$mb_nick}'은(는) 이미 다른분께서 사용하고 있는 별명이므로 사용하실 수 없습니다.\");";
    echo "parent.document.getElementById('mb_nick_enabled').value = -1;";
    echo "window.close();";
    echo "</script>";
} else {
    if (preg_match("/[\,]?{$mb_nick}/i", $config[cf_prohibit_id])) {
        echo "<script type='text/javascript'>";
        echo "alert(\"'{$mb_nick}'은(는) 예약어로 사용하실 수 없는 별명입니다.\");";
        echo "parent.document.getElementById('mb_nick_enabled').value = -2;";
        echo "window.close();";
        echo "</script>";
    } else {
        echo "<script type='text/javascript'>";
        echo "alert(\"'{$mb_nick}'은(는) 별명으로 사용할 수 있습니다.\");";
        echo "parent.document.getElementById('mb_nick_enabled').value = 1;";
        echo "window.close();";
        echo "</script>";
    }
}

include_once("$g4[path]/tail.sub.php");
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>1</title>
</head>

<?php
$str = "내용물";


if (isset($_GET['width'])) {
    echo $str;
    echo "<noscript>".$str."</noscript>";
} else {
    echo "<script>\n";
    echo "location.href=\"${_SERVER['SCRIPT_NAME']}?${_SERVER['QUERY_STRING']}"
        . "width=\" + screen.width;\n";
    echo "</script>\n";
    echo "<noscript>";
    echo $str;
    echo "</noscript>";
    exit();
}
?>
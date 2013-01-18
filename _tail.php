<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (G4_IS_MOBILE)
    include_once($g4['path'].'/mobile.tail.php');
else
    include_once($g4['path'].'/tail.php');
?>
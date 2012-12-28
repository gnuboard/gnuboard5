<?php
include_once("./_common.php");
include_once("$g4[path]/plugin/tcaptcha/tcaptcha.lib.php");

$tcaptcha = new tcaptcha("kr");
$tcaptcha->run();
die("{\"tcaptcha\":\"{$tcaptcha->question}\",\"token\":\"{$tcaptcha->token}\"}");
?>
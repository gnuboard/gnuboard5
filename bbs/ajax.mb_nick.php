<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

$mb_nick = trim($_POST['reg_mb_nick']);
$mb_id   = trim($_POST['reg_mb_id']);

if ($msg = empty_mb_nick($mb_nick)) die($msg);
if ($msg = valid_mb_nick($mb_nick)) die($msg);
if ($msg = count_mb_nick($mb_nick)) die($msg);
if ($msg = exist_mb_nick($mb_nick, $mb_id)) die($msg);
?>
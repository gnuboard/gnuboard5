<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

$mb_id = trim($_POST['reg_mb_id']);

if ($msg = empty_mb_id($mb_id))     die($msg);
if ($msg = valid_mb_id($mb_id))     die($msg);
if ($msg = count_mb_id($mb_id))     die($msg);
if ($msg = exist_mb_id($mb_id))     die($msg);
if ($msg = reserve_mb_id($mb_id))   die($msg);
?>
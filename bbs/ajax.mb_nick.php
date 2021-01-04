<?php
include_once('./_common.php');
include_once(G5_LIB_PATH.'/register.lib.php');

$mb_nick = isset($_POST['reg_mb_nick']) ? trim($_POST['reg_mb_nick']) : '';
$mb_id   = isset($_POST['reg_mb_id']) ? trim($_POST['reg_mb_id']) : '';

set_session('ss_check_mb_nick', '');

if ($msg = empty_mb_nick($mb_nick)) die($msg);
if ($msg = valid_mb_nick($mb_nick)) die($msg);
if ($msg = count_mb_nick($mb_nick)) die($msg);
if ($msg = exist_mb_nick($mb_nick, $mb_id)) die($msg);
if ($msg = reserve_mb_nick($mb_nick)) die($msg);

set_session('ss_check_mb_nick', $mb_nick);
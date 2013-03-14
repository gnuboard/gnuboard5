<?
include_once('./_common.php');
include_once(G4_LIB_PATH.'/register.lib.php');

$mb_email = escape_trim($_POST['reg_mb_email']);
$mb_id    = escape_trim($_POST['reg_mb_id']);

if ($msg = empty_mb_email($mb_email)) die($msg);
if ($msg = valid_mb_email($mb_email)) die($msg);
if ($msg = prohibit_mb_email($mb_email)) die($msg);
if ($msg = exist_mb_email($mb_email, $mb_id)) die($msg);
?>
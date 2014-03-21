<?php
include_once('./_common.php');

if($type == 'mobile')
    $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$dir;
else
    $skin_dir = G5_PATH.'/'.G5_SKIN_DIR.'/shop/'.$dir;

echo get_list_skin_options("^list.[0-9]+\.skin\.php", $skin_dir, $sval);
?>
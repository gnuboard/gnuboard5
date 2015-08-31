<?php
include_once('./_common.php');

if($type == 'mobile') {
    if(preg_match('#^theme/(.+)$#', $dir, $match))
        $skin_dir = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1];
    else
        $skin_dir = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/shop/'.$dir;
} else {
    if(preg_match('#^theme/(.+)$#', $dir, $match))
        $skin_dir = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/shop/'.$match[1];
    else
        $skin_dir = G5_PATH.'/'.G5_SKIN_DIR.'/shop/'.$dir;
}

echo get_list_skin_options("^list.[0-9]+\.skin\.php", $skin_dir, $sval);
?>
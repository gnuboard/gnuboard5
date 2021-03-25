<?php
if (!defined('_GNUBOARD_')) exit;

// 현재 접속자수 출력
function connect($skin_dir='basic')
{
    global $config, $g5;

    // 회원, 방문객 카운트
    $sql = " select sum(IF(mb_id<>'',1,0)) as mb_cnt, count(*) as total_cnt from {$g5['login_table']}  where mb_id <> '{$config['cf_admin']}' ";
    $row = sql_fetch($sql);

    if(preg_match('#^theme/(.+)$#', $skin_dir, $match)) {
        if (G5_IS_MOBILE) {
            $connect_skin_path = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/connect/'.$match[1];
            if(!is_dir($connect_skin_path))
                $connect_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/connect/'.$match[1];
            $connect_skin_url = str_replace(G5_PATH, G5_URL, $connect_skin_path);
        } else {
            $connect_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/connect/'.$match[1];
            $connect_skin_url = str_replace(G5_PATH, G5_URL, $connect_skin_path);
        }
        $skin_dir = $match[1];
    } else {
        if(G5_IS_MOBILE) {
            $connect_skin_path = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/connect/'.$skin_dir;
            $connect_skin_url  = G5_MOBILE_URL.'/'.G5_SKIN_DIR.'/connect/'.$skin_dir;
        } else {
            $connect_skin_path = G5_SKIN_PATH.'/connect/'.$skin_dir;
            $connect_skin_url  = G5_SKIN_URL.'/connect/'.$skin_dir;
        }
    }

    ob_start();
    include_once ($connect_skin_path.'/connect.skin.php');
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
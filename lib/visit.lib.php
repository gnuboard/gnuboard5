<?php
if (!defined('_GNUBOARD_')) exit;

// 방문자수 출력
function visit($skin_dir='basic')
{
    global $config, $g5;

    // visit 배열변수에
    // $visit[1] = 오늘
    // $visit[2] = 어제
    // $visit[3] = 최대
    // $visit[4] = 전체
    // 숫자가 들어감
    preg_match("/오늘:(.*),어제:(.*),최대:(.*),전체:(.*)/", $config['cf_visit'], $visit);
    settype($visit[1], "integer");
    settype($visit[2], "integer");
    settype($visit[3], "integer");
    settype($visit[4], "integer");

    if(preg_match('#^theme/(.+)$#', $skin_dir, $match)) {
        if (G5_IS_MOBILE) {
            $visit_skin_path = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/visit/'.$match[1];
            if(!is_dir($visit_skin_path))
                $visit_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/visit/'.$match[1];
            $visit_skin_url = str_replace(G5_PATH, G5_URL, $visit_skin_path);
        } else {
            $visit_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/visit/'.$match[1];
            $visit_skin_url = str_replace(G5_PATH, G5_URL, $visit_skin_path);
        }
        $skin_dir = $match[1];
    } else {
        if(G5_IS_MOBILE) {
            $visit_skin_path = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/visit/'.$skin_dir;
            $visit_skin_url = G5_MOBILE_URL.'/'.G5_SKIN_DIR.'/visit/'.$skin_dir;
        } else {
            $visit_skin_path = G5_SKIN_PATH.'/visit/'.$skin_dir;
            $visit_skin_url = G5_SKIN_URL.'/visit/'.$skin_dir;
        }
    }

    ob_start();
    include_once ($visit_skin_path.'/visit.skin.php');
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}

// get_browser() 함수는 이미 있음
function get_brow($agent)
{
    $info = get_browscap_info($agent);

    return $info->Comment;
}

function get_os($agent)
{
    $info = get_browscap_info($agent);

    return $info->Platform;
}

function get_device($agent)
{
    $info = get_browscap_info($agent);

    return $info->Device_Type;
}
?>
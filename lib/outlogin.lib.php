<?
if (!defined('_GNUBOARD_')) exit;

// 외부로그인
function outlogin($skin_dir='basic')
{
    global $config, $member, $g4, $urlencode, $is_admin, $is_member;

    if (array_key_exists('mb_nick', $member)) {
        $nick  = cut_str($member['mb_nick'], $config['cf_cut_name']);
    }
    if (array_key_exists('mb_point', $member)) {
        $point = number_format($member['mb_point']);
    }

    $outlogin_skin_path = G4_SKIN_PATH.'/outlogin/'.$skin_dir;

    // 읽지 않은 쪽지가 있다면
    if ($is_member) {
        $sql = " select count(*) as cnt from {$g4['memo_table']} where me_recv_mb_id = '{$member['mb_id']}' and me_read_datetime = '0000-00-00 00:00:00' ";
        $row = sql_fetch($sql);
        $memo_not_read = $row['cnt'];

        $is_auth = false;
        $sql = " select count(*) as cnt from {$g4['auth_table']} where mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if ($row['cnt'])
            $is_auth = true;
    }

    if (G4_HTTPS_DOMAIN) {
        $outlogin_url = $_GET['url'];
        if ($outlogin_url) {
            if (preg_match("/^\.\.\//", $outlogin_url)) {
                $outlogin_url = urlencode(G4_URL."/".preg_replace("/^\.\.\//", "", $outlogin_url));
            }
            else {
                $purl = parse_url(G4_URL);
                if ($purl['path']) {
                    $path = urlencode($purl['path']);
                    $urlencode = preg_replace("/".$path."/", "", $urlencode);
                }
                $outlogin_url = $urlencode;
            }
        }
        else {
            $outlogin_url = G4_URL;
        }
    }
    else {
        $outlogin_url = $urlencode;
    }

    $outlogin_action_url = G4_HTTPS_BBS_URL.'/login_check.php';

    ob_start();
    if ($is_member)
        include_once ($outlogin_skin_path.'/outlogin.skin.2.php');
    else // 로그인 전이라면
        include_once ($outlogin_skin_path.'/outlogin.skin.1.php');
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
?>
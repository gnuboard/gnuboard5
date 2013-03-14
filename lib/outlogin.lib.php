<?
if (!defined('_GNUBOARD_')) exit;

// 외부로그인
function outlogin($skin_dir="basic")
{
    global $config, $member, $g4, $urlencode, $is_admin;

    $nick  = cut_str($member['mb_nick'], $config['cf_cut_name']);
    $point = number_format($member['mb_point']);

    $outlogin_skin_path = "$g4[path]/skin/outlogin/$skin_dir";

    // 읽지 않은 쪽지가 있다면
    if ($member['mb_id']) {
        $sql = " select count(*) as cnt from {$g4['memo_table']} where me_recv_mb_id = '{$member['mb_id']}' and me_read_datetime = '0000-00-00 00:00:00' ";
        $row = sql_fetch($sql);
        $memo_not_read = $row['cnt'];

        $is_auth = false;
        $sql = " select count(*) as cnt from $g4[auth_table] where mb_id = '$member[mb_id]' ";
        $row = sql_fetch($sql);
        if ($row['cnt']) 
            $is_auth = true;
    }

    ob_start();
    if ($member['mb_id'])
        include_once ("$outlogin_skin_path/outlogin.skin.2.php");
    else // 로그인 전이라면
        include_once ("$outlogin_skin_path/outlogin.skin.1.php");
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
?>
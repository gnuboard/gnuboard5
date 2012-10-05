<?
if (!defined('_GNUBOARD_')) exit;

// 현재 접속자수 출력
function connect($skin_dir="")
{
    global $config, $g4;

    // 회원, 방문객 카운트
    $sql = " select sum(IF(mb_id<>'',1,0)) as mb_cnt, count(*) as total_cnt from $g4[login_table]  where mb_id <> '$config[cf_admin]' ";
    $row = sql_fetch($sql);

    if ($skin_dir)
        $connect_skin_path = "$g4[path]/skin/connect/$skin_dir";
    else
        $connect_skin_path = "$g4[path]/skin/connect/$config[cf_connect_skin]";

    ob_start();
    include_once ("$connect_skin_path/connect.skin.php");
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
?>
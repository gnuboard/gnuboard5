<?
include_once("./_common.php");

if ($_COOKIE[ck_bn_id] != $bn_id)
{
    $sql = " update $g4[yc4_banner_table] set bn_hit = bn_hit + 1 where bn_id = '$bn_id' ";
    sql_query($sql);
    // 하루 동안 
    setcookie("ck_bn_id", $bn_id, 60*60*24, $default[de_cookie_dir], $default[de_cookie_domain]);
}

goto_url($url);
?>

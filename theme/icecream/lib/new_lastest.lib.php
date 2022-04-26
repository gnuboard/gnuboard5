<?php
if (!defined('_GNUBOARD_')) exit;


// board_new 게시판 최신글 추출
function new_latest($skin_dir='', $rows=20, $subject_len=40, $is_comment=false, $options='')
{
    global $g5;

    if (!$skin_dir) $skin_dir = 'basic';

    if(preg_match('#^theme/(.+)$#', $skin_dir, $match)) {
        if (G5_IS_MOBILE) {
            $latest_skin_path = G5_THEME_MOBILE_PATH.'/'.G5_SKIN_DIR.'/latest/'.$match[1];
            if(!is_dir($latest_skin_path))
                $latest_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/latest/'.$match[1];
            $latest_skin_url = str_replace(G5_PATH, G5_URL, $latest_skin_path);
        } else {
            $latest_skin_path = G5_THEME_PATH.'/'.G5_SKIN_DIR.'/latest/'.$match[1];
            $latest_skin_url = str_replace(G5_PATH, G5_URL, $latest_skin_path);
        }
        $skin_dir = $match[1];
    } else {
        if(G5_IS_MOBILE) {
            $latest_skin_path = G5_MOBILE_PATH.'/'.G5_SKIN_DIR.'/latest/'.$skin_dir;
            $latest_skin_url  = G5_MOBILE_URL.'/'.G5_SKIN_DIR.'/latest/'.$skin_dir;
        } else {
            $latest_skin_path = G5_SKIN_PATH.'/latest/'.$skin_dir;
            $latest_skin_url  = G5_SKIN_URL.'/latest/'.$skin_dir;
        }
    }



    $list = array();

    $sql_common = " from {$g5['board_new_table']} a, {$g5['board_table']} b where a.bo_table = b.bo_table and b.bo_use_search = 1 ";

    if($is_comment)
        $sql_common .= " and a.wr_id <> a.wr_parent ";
    else
        $sql_common .= " and a.wr_id = a.wr_parent ";

    $sql_order = " order by a.bn_id desc ";

    $sql = " select a.*, b.bo_subject {$sql_common} {$sql_order} limit {$rows} ";

    $result = sql_query($sql);
    for ($i=0; $row = sql_fetch_array($result); $i++) {
        $tmp_write_table = $g5['write_prefix'].$row['bo_table'];

        $row2 = sql_fetch(" select * from {$tmp_write_table} where wr_id = '{$row['wr_id']}' ");
        $list[$i] = $row2;


        // 당일인 경우 시간으로 표시함
        $datetime = substr($row2['wr_datetime'],0,10);

        $list[$i]['bo_table'] = $row['bo_table'];
        $list[$i]['href'] = short_url_clean(G5_BBS_URL.'/board.php?bo_table='.$row['bo_table'].'&amp;wr_id='.$row2['wr_parent'].$comment_link);
        $list[$i]['subject'] = conv_subject($list[$i]['wr_subject'], $subject_len, '…');
        $list[$i]['datetime'] = $datetime;
        $list[$i]['datetime2'] = $datetime2;
        $list[$i]['bo_subject'] = $row['bo_subject'];
        $list[$i]['wr_subject'] = $row2['wr_subject'];
    }



    ob_start();
    include $latest_skin_path.'/latest.skin.php';
    $content = ob_get_contents();
    ob_end_clean();

    return $content;

}
?>
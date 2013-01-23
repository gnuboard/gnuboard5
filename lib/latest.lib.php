<?
if (!defined('_GNUBOARD_')) exit;

// 최신글 추출
function latest($skin_dir='', $bo_table, $rows=10, $subject_len=40, $options='')
{
    global $g4;

    if (!$skin_dir) $skin_dir = 'basic';
    $latest_skin_path = skin_path().'/latest/'.$skin_dir;

    $cache_file = $g4['cache_latest_path']."/{$bo_table}_{$skin_dir}_{$rows}_{$subject_len}.php";
    if (!file_exists($cache_file)) {
        $list = array();

        $sql = " select * from $g4[board_table] where bo_table = '$bo_table'";
        $board = sql_fetch($sql);

        $tmp_write_table = $g4['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
        $sql = " select wr_id, ca_name, wr_subject, wr_comment, wr_hit, wr_datetime, wr_name, mb_id, wr_singo, wr_good, wr_nogood from $tmp_write_table where wr_is_comment = 0 ";
        if ($ca_name) {
            $sql .= " and ca_name in ('', '공지', '$ca_name') ";
        }
        $sql .= " order by wr_num limit 0, $rows ";
        $result = sql_query($sql);
        for ($i=0; $row = sql_fetch_array($result); $i++) {
            $list[$i] = get_list($row, $board, $latest_skin_path, $subject_len);
        }

        $handle = fopen($cache_file, "w");
        $cache_content = "<?php\nif (!defined('_GNUBOARD_')) exit;\n//".get_text($board['bo_subject'])." 최신글\n\$list = ".var_export($list, true)."?>";
        //$cache_content = all_trim($cache_content);
        fwrite($handle, $cache_content);
        fclose($handle);
    } 

    include_once($cache_file);

    ob_start();
    include "$latest_skin_path/latest.skin.php";
    $content = ob_get_contents();
    ob_end_clean();

    return $content;

    /*
    $latest_skin_path = skin_path().'/latest/'.$skin_dir;

    $list = array();

    $sql = " select * from {$g4['board_table']} where bo_table = '$bo_table'";
    $board = sql_fetch($sql);

    $tmp_write_table = $g4['write_prefix'] . $bo_table; // 게시판 테이블 전체이름
    //$sql = " select * from $tmp_write_table where wr_is_comment = 0 order by wr_id desc limit 0, $rows ";
    // 위의 코드 보다 속도가 빠름
    $sql = " select * from $tmp_write_table where wr_is_comment = 0 order by wr_num limit 0, $rows ";
    //explain($sql);
    $result = sql_query($sql);
    for ($i=0; $row = sql_fetch_array($result); $i++)
        $list[$i] = get_list($row, $board, $latest_skin_path, $subject_len);

    ob_start();
    include $latest_skin_path.'/latest.skin.php';
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
    */
}
?>
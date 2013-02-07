<?php
if (!defined('_GNUBOARD_')) exit;

// 최신글 추출
function latest($skin_dir='', $bo_table, $rows=10, $subject_len=40)
{
    global $g4;

    if (!$skin_dir) $skin_dir = 'basic';
    $latest_skin_path = G4_SKIN_PATH.'/latest/'.$skin_dir;
    $latest_skin_url  = G4_SKIN_URL.'/latest/'.$skin_dir;

    $cache_file = G4_DATA_PATH."/cache/latest-{$bo_table}-{$skin_dir}-{$rows}-{$subject_len}.php";
    if (!G4_USE_CACHE || !file_exists($cache_file)) {
        $list = array();

        $sql = " select * from {$g4['board_table']} where bo_table = '$bo_table'";
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
        $cache_content = "<?php\nif (!defined('_GNUBOARD_')) exit;\n\$bo_subject=\"".get_text($board['bo_subject'])."\";\n\$list=".var_export($list, true)."?>";
        fwrite($handle, $cache_content);
        fclose($handle);
    } 

    include_once($cache_file);

    ob_start();
    include "$latest_skin_path/latest.skin.php";
    $content = ob_get_contents();
    ob_end_clean();

    return $content;
}
?>

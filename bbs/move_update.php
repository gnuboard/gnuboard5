<?php
include_once('./_common.php');

$act = isset($act) ? strip_tags($act) : '';
$count_chk_bo_table = (isset($_POST['chk_bo_table']) && is_array($_POST['chk_bo_table'])) ? count($_POST['chk_bo_table']) : 0;

// 게시판 관리자 이상 복사, 이동 가능
if ($is_admin != 'board' && $is_admin != 'group' && $is_admin != 'super')
    alert_close('게시판 관리자 이상 접근이 가능합니다.');

if ($sw != 'move' && $sw != 'copy')
    alert('sw 값이 제대로 넘어오지 않았습니다.');

if(! $count_chk_bo_table)
    alert('게시물을 '.$act.'할 게시판을 한개 이상 선택해 주십시오.', $url);

// 원본 파일 디렉토리
$src_dir = G5_DATA_PATH.'/file/'.$bo_table;

$save = array();
$save_count_write = 0;
$save_count_comment = 0;
$cnt = 0;

$wr_id_list = isset($_POST['wr_id_list']) ? preg_replace('/[^0-9\,]/', '', $_POST['wr_id_list']) : '';

$sql = " select distinct wr_num from $write_table where wr_id in ({$wr_id_list}) order by wr_id ";
$result = sql_query($sql);
while ($row = sql_fetch_array($result))
{
    $save[$cnt]['wr_contents'] = array();

    $wr_num = $row['wr_num'];
    for ($i=0; $i<$count_chk_bo_table; $i++)
    {
        $move_bo_table = isset($_POST['chk_bo_table'][$i]) ? preg_replace('/[^a-z0-9_]/i', '', $_POST['chk_bo_table'][$i]) : '';

        // 취약점 18-0075 참고
        $sql = "select * from {$g5['board_table']} where bo_table = '".sql_real_escape_string($move_bo_table)."' ";
        $move_board = sql_fetch($sql);
        // 존재하지 않다면
        if( !$move_board['bo_table'] ) continue;

        $move_write_table = $g5['write_prefix'] . $move_bo_table;

        $src_dir = G5_DATA_PATH.'/file/'.$bo_table; // 원본 디렉토리
        $dst_dir = G5_DATA_PATH.'/file/'.$move_bo_table; // 복사본 디렉토리

        $count_write = 0;
        $count_comment = 0;

        // get_next_num 함수는 mysql 지연시 중복이 될수 있는 문제로 더 이상 사용하지 않습니다.
        // $next_wr_num = get_next_num($move_write_table);
        $next_wr_num = 0;

        $sql2 = " select * from $write_table where wr_num = '$wr_num' order by wr_parent, wr_is_comment, wr_comment desc, wr_id ";
        $result2 = sql_query($sql2);
        while ($row2 = sql_fetch_array($result2))
        {
            $save[$cnt]['wr_contents'][] = $row2['wr_content'];

            $nick = cut_str($member['mb_nick'], $config['cf_cut_name']);
            if (!$row2['wr_is_comment'] && $config['cf_use_copy_log']) {
                if(strstr($row2['wr_option'], 'html')) {
                    $log_tag1 = '<div class="content_'.$sw.'">';
                    $log_tag2 = '</div>';
                } else {
                    $log_tag1 = "\n";
                    $log_tag2 = '';
                }

                $row2['wr_content'] .= "\n".$log_tag1.'[이 게시물은 '.$nick.'님에 의해 '.G5_TIME_YMDHIS.' '.$board['bo_subject'].'에서 '.($sw == 'copy' ? '복사' : '이동').' 됨]'.$log_tag2;
            }

            // 게시글 추천, 비추천수
            $wr_good = $wr_nogood = 0;
            if ($sw == 'move' && $i == 0) {
                $wr_good = $row2['wr_good'];
                $wr_nogood = $row2['wr_nogood'];
            }

            $sql = " insert into $move_write_table
                        set wr_num = " . ($next_wr_num ? "'$next_wr_num'" : "(SELECT IFNULL(MIN(wr_num) - 1, -1) FROM $move_write_table sq) ") . ",
                             wr_reply = '{$row2['wr_reply']}',
                             wr_is_comment = '{$row2['wr_is_comment']}',
                             wr_comment = '{$row2['wr_comment']}',
                             wr_comment_reply = '{$row2['wr_comment_reply']}',
                             ca_name = '".addslashes($row2['ca_name'])."',
                             wr_option = '{$row2['wr_option']}',
                             wr_subject = '".addslashes($row2['wr_subject'])."',
                             wr_content = '".addslashes($row2['wr_content'])."',
                             wr_link1 = '".addslashes($row2['wr_link1'])."',
                             wr_link2 = '".addslashes($row2['wr_link2'])."',
                             wr_link1_hit = '{$row2['wr_link1_hit']}',
                             wr_link2_hit = '{$row2['wr_link2_hit']}',
                             wr_hit = '{$row2['wr_hit']}',
                             wr_good = '{$wr_good}',
                             wr_nogood = '{$wr_nogood}',
                             mb_id = '{$row2['mb_id']}',
                             wr_password = '{$row2['wr_password']}',
                             wr_name = '".addslashes($row2['wr_name'])."',
                             wr_email = '".addslashes($row2['wr_email'])."',
                             wr_homepage = '".addslashes($row2['wr_homepage'])."',
                             wr_datetime = '{$row2['wr_datetime']}',
                             wr_file = '{$row2['wr_file']}',
                             wr_last = '{$row2['wr_last']}',
                             wr_ip = '{$row2['wr_ip']}',
                             wr_1 = '".addslashes($row2['wr_1'])."',
                             wr_2 = '".addslashes($row2['wr_2'])."',
                             wr_3 = '".addslashes($row2['wr_3'])."',
                             wr_4 = '".addslashes($row2['wr_4'])."',
                             wr_5 = '".addslashes($row2['wr_5'])."',
                             wr_6 = '".addslashes($row2['wr_6'])."',
                             wr_7 = '".addslashes($row2['wr_7'])."',
                             wr_8 = '".addslashes($row2['wr_8'])."',
                             wr_9 = '".addslashes($row2['wr_9'])."',
                             wr_10 = '".addslashes($row2['wr_10'])."' ";
            sql_query($sql);

            $insert_id = sql_insert_id();
            
            if ($next_wr_num === 0) {
                $tmp = sql_fetch("select wr_num from $move_write_table where wr_id = '$insert_id'");
                $next_wr_num = $tmp['wr_num'];
            }

            // 코멘트가 아니라면
            if (!$row2['wr_is_comment'])
            {
                $save_parent = $insert_id;

                $sql3 = " select * from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' order by bf_no ";
                $result3 = sql_query($sql3);
                for ($k=0; $row3 = sql_fetch_array($result3); $k++)
                {
                    if ($row3['bf_file'])
                    {
                        // 원본파일을 복사하고 퍼미션을 변경
                        // 제이프로님 코드제안 적용

                        $copy_file_name = $row3['bf_file'];

                        if($bo_table === $move_bo_table){
                            if(preg_match('/_copy(\d+)?_(\d+)_/', $copy_file_name, $match)){

                                $number = isset($match[1]) ? (int) $match[1] : 0;
                                $replace_str = '_copy'.($number + 1).'_'.$insert_id.'_';
                                $copy_file_name = preg_replace('/_copy(\d+)?_(\d+)_/', $replace_str, $copy_file_name);
                            } else {
                                $copy_file_name = $row2['wr_id'].'_copy_'.$insert_id.'_'.$row3['bf_file'];
                            }
                        }

                        $is_exist_file = is_file($src_dir.'/'.$row3['bf_file']) && file_exists($src_dir.'/'.$row3['bf_file']);
                        if( $is_exist_file ){
                            @copy($src_dir.'/'.$row3['bf_file'], $dst_dir.'/'.$copy_file_name);
                            @chmod($dst_dir.'/'.$row3['bf_file'], G5_FILE_PERMISSION);
                        }

                        $row3 = run_replace('bbs_move_update_file', $row3, $copy_file_name, $bo_table, $move_bo_table, $insert_id);
                    }

                    $sql = " insert into {$g5['board_file_table']}
                                set bo_table = '$move_bo_table',
                                     wr_id = '$insert_id',
                                     bf_no = '{$row3['bf_no']}',
                                     bf_source = '".addslashes($row3['bf_source'])."',
                                     bf_file = '$copy_file_name',
                                     bf_download = '{$row3['bf_download']}',
                                     bf_content = '".addslashes($row3['bf_content'])."',
                                     bf_fileurl = '".addslashes($row3['bf_fileurl'])."',
                                     bf_thumburl = '".addslashes($row3['bf_thumburl'])."',
                                     bf_storage = '".addslashes($row3['bf_storage'])."',
                                     bf_filesize = '{$row3['bf_filesize']}',
                                     bf_width = '{$row3['bf_width']}',
                                     bf_height = '{$row3['bf_height']}',
                                     bf_type = '{$row3['bf_type']}',
                                     bf_datetime = '{$row3['bf_datetime']}' ";
                    sql_query($sql);

                    if ($sw == 'move' && $row3['bf_file'])
                        $save[$cnt]['bf_file'][$k] = $src_dir.'/'.$row3['bf_file'];
                }

                $count_write++;

                if ($sw == 'move' && $i == 0)
                {
                    // 스크랩 이동
                    sql_query(" update {$g5['scrap_table']} set bo_table = '$move_bo_table', wr_id = '$save_parent' where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' ");

                    // 최신글 이동
                    sql_query(" update {$g5['board_new_table']} set bo_table = '$move_bo_table', wr_id = '$save_parent', wr_parent = '$save_parent' where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' ");

                    // 추천데이터 이동
                    sql_query(" update {$g5['board_good_table']} set bo_table = '$move_bo_table', wr_id = '$save_parent' where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' ");
                }
            }
            else
            {
                $count_comment++;

                if ($sw == 'move')
                {
                    // 최신글 이동
                    sql_query(" update {$g5['board_new_table']} set bo_table = '$move_bo_table', wr_id = '$insert_id', wr_parent = '$save_parent' where bo_table = '$bo_table' and wr_id = '{$row2['wr_id']}' ");
                }
            }

            sql_query(" update $move_write_table set wr_parent = '$save_parent' where wr_id = '$insert_id' ");

            if ($sw == 'move')
                $save[$cnt]['wr_id'] = $row2['wr_parent'];

            $cnt++;

            run_event('bbs_move_copy', $row2, $move_bo_table, $insert_id, $next_wr_num, $sw);
        }

        sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write + '$count_write' where bo_table = '$move_bo_table' ");
        sql_query(" update {$g5['board_table']} set bo_count_comment = bo_count_comment + '$count_comment' where bo_table = '$move_bo_table' ");

        delete_cache_latest($move_bo_table);
    }

    $save_count_write += $count_write;
    $save_count_comment += $count_comment;
}

delete_cache_latest($bo_table);

if ($sw == 'move')
{
    for ($i=0; $i<count($save); $i++)
    {
        if( isset($save[$i]['bf_file']) && $save[$i]['bf_file'] ){
            for ($k=0; $k<count($save[$i]['bf_file']); $k++) {
                $del_file = run_replace('delete_file_path', clean_relative_paths($save[$i]['bf_file'][$k]), $save[$i]);

                if ( is_file($del_file) && file_exists($del_file) ){
                    @unlink($del_file);
                }
                
                // 썸네일 파일 삭제, 먼지손 님 코드 제안
                delete_board_thumbnail($bo_table, basename($save[$i]['bf_file'][$k]));
            }
        }
        
        for ($k=0; $k<count($save[$i]['wr_contents']); $k++){
            delete_editor_thumbnail($save[$i]['wr_contents'][$k]);
        }

        sql_query(" delete from $write_table where wr_parent = '{$save[$i]['wr_id']}' ");
        sql_query(" delete from {$g5['board_new_table']} where bo_table = '$bo_table' and wr_id = '{$save[$i]['wr_id']}' ");
        sql_query(" delete from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$save[$i]['wr_id']}' ");
    }

    // 공지사항이 이동되는 경우의 처리 begin
    $arr = array();
    $sql = " select bo_notice from {$g5['board_table']} where bo_table = '{$bo_table}' ";
    $row = sql_fetch($sql);
    $arr_notice = explode(',', $row['bo_notice']);
    for ($i=0; $i<count($arr_notice); $i++) {
        $move_id = (int)$arr_notice[$i];
        // 게시판에 wr_id 가 있다면 이동한게 아니므로 bo_notice 에 다시 넣음
        $row2 = sql_fetch(" select count(*) as cnt from $write_table where wr_id = '{$move_id}' ");
        if ($row2['cnt']) {
            $arr[] = $move_id;
        }
        $bo_notice = implode(',', $arr);
    }
    // 공지사항이 이동되는 경우의 처리 end

    sql_query(" update {$g5['board_table']} set bo_notice = '{$bo_notice}', bo_count_write = bo_count_write - '$save_count_write', bo_count_comment = bo_count_comment - '$save_count_comment' where bo_table = '$bo_table' ");
}

$msg = '해당 게시물을 선택한 게시판으로 '.$act.' 하였습니다.';
$opener_href  = get_pretty_url($bo_table,'','&amp;page='.$page.'&amp;'.$qstr);
$opener_href1 = str_replace('&amp;', '&', $opener_href);

run_event('bbs_move_update', $bo_table, $chk_bo_table, $wr_id_list, $opener_href);
?>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
<script>
alert("<?php echo $msg; ?>");
opener.document.location.href = "<?php echo $opener_href1; ?>";
window.close();
</script>
<noscript>
<p>
    <?php echo $msg; ?>
</p>
<a href="<?php echo $opener_href; ?>">돌아가기</a>
</noscript>
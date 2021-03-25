<?php
include_once('./_common.php');

//print_r2($_POST); exit;

if ($is_admin != 'super')
    alert("최고관리자만 접근이 가능합니다.");

$board = array('bo_table'=>'');
$save_bo_table = array();
$save_wr_id = array();
$count_chk_bn_id = (isset($_POST['chk_bn_id']) && is_array($_POST['chk_bn_id'])) ? count($_POST['chk_bn_id']) : 0;

for($i=0;$i<$count_chk_bn_id;$i++)
{
    // 실제 번호를 넘김
    $k = isset($_POST['chk_bn_id'][$i]) ? (int) $_POST['chk_bn_id'][$i] : 0;

    $bo_table = isset($_POST['bo_table'][$k]) ? preg_replace('/[^a-z0-9_]/i', '', $_POST['bo_table'][$k]) : '';
    $wr_id    = isset($_POST['wr_id'][$k]) ? preg_replace('/[^0-9]/i', '', $_POST['wr_id'][$k]) : 0;
    
    $count_write = $count_comment = 0;

    $save_bo_table[$i] = $bo_table;
	$save_wr_id[$i] = $wr_id;

    $write_table = $g5['write_prefix'].$bo_table;

    if ($board['bo_table'] != $bo_table)
        $board = sql_fetch(" select bo_subject, bo_write_point, bo_comment_point, bo_notice from {$g5['board_table']} where bo_table = '$bo_table' ");

    $write = get_write($write_table, $wr_id);
    if (!$write) continue;

    // 원글 삭제
    if ($write['wr_is_comment']==0)
    {
        $len = strlen($write['wr_reply']);
        if ($len < 0) $len = 0;
        $reply = substr($write['wr_reply'], 0, $len);

        // 나라오름님 수정 : 원글과 코멘트수가 정상적으로 업데이트 되지 않는 오류를 잡아 주셨습니다.
        $sql = " select wr_id, mb_id, wr_is_comment from $write_table where wr_parent = '{$write['wr_id']}' order by wr_id ";
        $result = sql_query($sql);
        while ($row = sql_fetch_array($result))
        {
            // 원글이라면
            if (!$row['wr_is_comment'])
            {
                if (!delete_point($row['mb_id'], $bo_table, $row['wr_id'], '쓰기'))
                    insert_point($row['mb_id'], $board['bo_write_point'] * (-1), "{$board['bo_subject']} {$row['wr_id']} 글삭제");

                // 업로드된 파일이 있다면 파일삭제
                $sql2 = " select * from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$row['wr_id']}' ";
                $result2 = sql_query($sql2);
                while ($row2 = sql_fetch_array($result2)){

                    $delete_file = run_replace('delete_file_path', G5_DATA_PATH.'/file/'.$bo_table.'/'.str_replace('../', '', $row2['bf_file']), $row2);
                    if( file_exists($delete_file) ){
                        @unlink(G5_DATA_PATH.'/file/'.$bo_table.'/'.$row2['bf_file']);
                    }
                    // 이미지파일이면 썸네일삭제
                    if(preg_match("/\.({$config['cf_image_extension']})$/i", $row2['bf_file'])) {
                        delete_board_thumbnail($bo_table, $row2['bf_file']);
                    }
                }
                // 파일테이블 행 삭제
                sql_query(" delete from {$g5['board_file_table']} where bo_table = '$bo_table' and wr_id = '{$row['wr_id']}' ");

                $count_write++;
            }
            else
            {
                // 댓글 포인트 삭제
                if (!delete_point($row['mb_id'], $bo_table, $row['wr_id'], '댓글'))
                    insert_point($row['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$write['wr_id']}-{$row['wr_id']} 댓글삭제");

                $count_comment++;
            }
        }

        if ($pressed == '선택내용삭제') {
            // 게시글 내용만 삭제
            sql_query(" update $write_table set wr_subject =  '".G5_TIME_YMDHIS." - 본인 요청으로 인한 삭제 (냉무) ☆', wr_content = '', wr_name='본인요청삭제☆' where wr_id = '{$write['wr_id']}' ");
        } else {
            // 게시글 삭제
            sql_query(" delete from $write_table where wr_parent = '{$write['wr_id']}' ");
        }

        // 최근게시물 삭제
        sql_query(" delete from {$g5['board_new_table']} where bo_table = '$bo_table' and wr_parent = '{$write['wr_id']}' ");

        // 스크랩 삭제
        sql_query(" delete from {$g5['scrap_table']} where bo_table = '$bo_table' and wr_id = '{$write['wr_id']}' ");

        // 공지사항 삭제
        $notice_array = explode(",", trim($board['bo_notice']));
        $bo_notice = "";
        $lf = '';
        for ($k=0; $k<count($notice_array); $k++) {
            if ((int)$write['wr_id'] != (int)$notice_array[$k])
                $bo_notice .= $lf.$notice_array[$k];

            if($bo_notice)
                $lf = ',';
        }
        $bo_notice = trim($bo_notice);
        sql_query(" update {$g5['board_table']} set bo_notice = '$bo_notice' where bo_table = '$bo_table' ");

        if ($pressed == '선택삭제') {
            // 글숫자 감소
            if ($count_write > 0 || $count_comment > 0) {
                sql_query(" update {$g5['board_table']} set bo_count_write = bo_count_write - '$count_write', bo_count_comment = bo_count_comment - '$count_comment' where bo_table = '$bo_table' ");
            }
        }
    }
    else // 코멘트 삭제
    {
        //--------------------------------------------------------------------
        // 코멘트 삭제시 답변 코멘트 까지 삭제되지는 않음
        //--------------------------------------------------------------------
        //print_r2($write);

        $comment_id = $wr_id;

        $len = strlen($write['wr_comment_reply']);
        if ($len < 0) $len = 0;
        $comment_reply = substr($write['wr_comment_reply'], 0, $len);

        // 코멘트 삭제
        if (!delete_point($write['mb_id'], $bo_table, $comment_id, '댓글')) {
            insert_point($write['mb_id'], $board['bo_comment_point'] * (-1), "{$board['bo_subject']} {$write['wr_parent']}-{$comment_id} 댓글삭제");
        }

        // 코멘트 삭제
        sql_query(" delete from $write_table where wr_id = '$comment_id' ");

        // 코멘트가 삭제되므로 해당 게시물에 대한 최근 시간을 다시 얻는다.
        $sql = " select max(wr_datetime) as wr_last from $write_table where wr_parent = '{$write['wr_parent']}' ";
        $row = sql_fetch($sql);

        // 원글의 코멘트 숫자를 감소
        sql_query(" update $write_table set wr_comment = wr_comment - 1, wr_last = '{$row['wr_last']}' where wr_id = '{$write['wr_parent']}' ");

        // 코멘트 숫자 감소
        sql_query(" update {$g5['board_table']} set bo_count_comment = bo_count_comment - 1 where bo_table = '$bo_table' ");

        // 새글 삭제
        sql_query(" delete from {$g5['board_new_table']} where bo_table = '$bo_table' and wr_id = '$comment_id' ");
    }
}

foreach ($save_bo_table as $key=>$value) {
    delete_cache_latest($value);
}

run_event('bbs_new_delete', $chk_bn_id, $save_bo_table, $save_wr_id);

goto_url("new.php?sfl=$sfl&stx=$stx&page=$page");
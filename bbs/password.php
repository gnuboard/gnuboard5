<?php
include_once('./_common.php');

$g5['title'] = '비밀번호 입력';

switch ($w) {
    case 'u' :
        $action = './write.php';
        $return_url = './board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
        break;
    case 'd' :
        set_session('ss_delete_token', $token = uniqid(time()));
        $action = './delete.php?token='.$token;
        $return_url = './board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
        break;
    case 'x' :
        set_session('ss_delete_comment_'.$comment_id.'_token', $token = uniqid(time()));
        $action = './delete_comment.php?token='.$token;
        $row = sql_fetch(" select wr_parent from $write_table where wr_id = '$comment_id' ");
        $return_url = './board.php?bo_table='.$bo_table.'&amp;wr_id='.$row['wr_parent'];
        break;
    case 's' :
        // 비밀번호 창에서 로그인 하는 경우 관리자 또는 자신의 글이면 바로 글보기로 감
        if ($is_admin || ($member['mb_id'] == $write['mb_id'] && $write['mb_id']))
            goto_url('./board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id);
        else {
            $action = './password_check.php';
            $return_url = './board.php?bo_table='.$bo_table;
        }
        break;
    case 'sc' :
        // 비밀번호 창에서 로그인 하는 경우 관리자 또는 자신의 글이면 바로 글보기로 감
        if ($is_admin || ($member['mb_id'] == $write['mb_id'] && $write['mb_id']))
            goto_url('./board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id);
        else {
            $action = './password_check.php';
            $return_url = './board.php?bo_table='.$bo_table.'&amp;wr_id='.$wr_id;
        }
        break;
    default :
        alert('w 값이 제대로 넘어오지 않았습니다.');
}

include_once(G5_PATH.'/head.sub.php');

//if ($board['bo_include_head']) { @include ($board['bo_include_head']); }
//if ($board['bo_content_head']) { echo stripslashes($board['bo_content_head']); }

/* 비밀글의 제목을 가져옴 지운아빠 2013-01-29 */
$sql = " select wr_subject from {$write_table}
                      where wr_num = '{$write['wr_num']}'
                      and wr_reply = ''
                      and wr_is_comment = 0 ";
$row = sql_fetch($sql);

$g5['title'] = get_text($row['wr_subject']);

include_once($member_skin_path.'/password.skin.php');

//if ($board['bo_content_tail']) { echo stripslashes($board['bo_content_tail']); }
//if ($board['bo_include_tail']) { @include ($board['bo_include_tail']); }

include_once(G5_PATH.'/tail.sub.php');
?>

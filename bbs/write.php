<?
include_once('./_common.php');

set_session('ss_bo_table', $bo_table);
set_session('ss_wr_id', $wr_id);

// 090713
if (!$board['bo_table']) {
    alert('존재하지 않는 게시판입니다.', $g4['path']);
}

if (!$bo_table) {
    alert('bo_table 값이 넘어오지 않았습니다.'.PHP_EOL.PHP_EOL.'write.php?bo_table=code 와 같은 방식으로 넘겨 주세요.', $g4['path']);
}

@include_once ($g4['path'].'/skin/board/write.head.skin.php');
@include_once ($board_skin_path.'/write.head.skin.php');

$notice_array = explode(',', trim($board['bo_notice']));

if (!($w == '' || $w == 'u' || $w == 'r')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

if (($w == 'u' || $w == 'r') && !$write['wr_id']) {
    alert("글이 존재하지 않습니다.\\n삭제되었거나 이동된 경우입니다.", $g4['path']);
}

if ($w == '') {
    if ($wr_id) {
        alert('글쓰기에는 \$wr_id 값을 사용하지 않습니다.', $g4['bbs_path'].'/board.php?bo_table='.$bo_table);
    }

    if ($member['mb_level'] < $board['bo_write_level']) {
        if ($member['mb_id']) {
            alert('글을 쓸 권한이 없습니다.');
        } else {
            alert("글을 쓸 권한이 없습니다.\\n회원이시라면 로그인 후 이용해 보십시오.", './login.php?'.$qstr.'&amp;url='.urlencode($_SERVER['PHP_SELF'].'?bo_table='.$bo_table));
        }
    }

    // 음수도 true 인것을 왜 이제야 알았을까?
    if ($is_member) {
        $tmp_point = ($member['mb_point'] > 0) ? $member['mb_point'] : 0;
        if ($tmp_point + $board['bo_write_point'] < 0 && !$is_admin) {
            alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 글쓰기('.number_format($board['bo_write_point']).')가 불가합니다.'.PHP_EOL.PHP_EOL.'포인트를 적립하신 후 다시 글쓰기 해 주십시오.');
        }
    }

    $title_msg = '글쓰기';
} else if ($w == 'u') {
    // 김선용 1.00 : 글쓰기 권한과 수정은 별도로 처리되어야 함
    //if ($member['mb_level'] < $board['bo_write_level']) {
    if($member['mb_id'] && $write['mb_id'] == $member['mb_id']) {
        ;
    } else if ($member['mb_level'] < $board['bo_write_level']) {
        if ($member['mb_id']) {
            alert('글을 수정할 권한이 없습니다.');
        } else {
            alert('글을 수정할 권한이 없습니다.'.PHP_EOL.PHP_EOL.'회원이시라면 로그인 후 이용해 보십시오.', './login.php?'.$qstr.'&amp;url='.urlencode($_SERVER['PHP_SELF'].'?bo_table='.$bo_table));
        }
    }

    $len = strlen($write['wr_reply']);
    if ($len < 0) $len = 0;
    $reply = substr($write['wr_reply'], 0, $len);

    // 원글만 구한다.
    $sql = " select count(*) as cnt from {$write_table}
                where wr_reply like '{$reply}%'
                and wr_id <> '{$write['wr_id']}'
                and wr_num = '{$write['wr_num']}'
                and wr_is_comment = 0 ";
    $row = sql_fetch($sql);
    if ($row['cnt'] && !$is_admin)
        alert('이 글과 관련된 답변글이 존재하므로 수정 할 수 없습니다.'.PHP_EOL.PHP_EOL.'답변글이 있는 원글은 수정할 수 없습니다.');

    // 코멘트 달린 원글의 수정 여부
    $sql = " select count(*) as cnt from {$write_table}
                where wr_parent = '{$wr_id}'
                and mb_id <> '{$member['mb_id']}'
                and wr_is_comment = 1 ";
    $row = sql_fetch($sql);
    if ($row['cnt'] >= $board['bo_count_modify'] && !$is_admin)
        alert('이 글과 관련된 코멘트가 존재하므로 수정 할 수 없습니다.'.PHP_EOL.PHP_EOL.'코멘트가 '.$board['bo_count_modify'].'건 이상 달린 원글은 수정할 수 없습니다.');

    $title_msg = '글수정';
} else if ($w == 'r') {
    if ($member['mb_level'] < $board['bo_reply_level']) {
        if ($member['mb_id'])
            alert('글을 답변할 권한이 없습니다.');
        else
            alert('글을 답변할 권한이 없습니다.'.PHP_EOL.PHP_EOL.'회원이시라면 로그인 후 이용해 보십시오.', './login.php?$qstr&amp;url='.urlencode($_SERVER['PHP_SELF'].'?bo_table='.$bo_table));
    }

    $tmp_point = isset($member['mb_point']) ? $member['mb_point'] : 0;
    if ($tmp_point + $board['bo_write_point'] < 0 && !$is_admin)
        alert('보유하신 포인트('.number_format($member['mb_point']).')가 없거나 모자라서 글답변('.number_format($board['bo_comment_point']).')가 불가합니다.'.PHP_EOL.PHP_EOL.'포인트를 적립하신 후 다시 글답변 해 주십시오.');

    //if (preg_match("/[^0-9]{0,1}{$wr_id}[\r]{0,1}/",$board['bo_notice']))
    if (in_array((int)$wr_id, $notice_array))
        alert('공지에는 답변 할 수 없습니다.');

    //----------
    // 4.06.13 : 비밀글을 타인이 열람할 수 있는 오류 수정 (헐랭이, 플록님께서 알려주셨습니다.)
    // 코멘트에는 원글의 답변이 불가하므로
    if ($write['wr_is_comment'])
        alert('정상적인 접근이 아닙니다.');

    // 비밀글인지를 검사
    if (strstr($write['wr_option'], 'secret')) {
        if ($write['mb_id']) {
            // 회원의 경우는 해당 글쓴 회원 및 관리자
            if (!($write['mb_id'] == $member['mb_id'] || $is_admin))
                alert('비밀글에는 자신 또는 관리자만 답변이 가능합니다.');
        } else {
            // 비회원의 경우는 비밀글에 답변이 불가함
            if (!$is_admin)
                alert('비회원의 비밀글에는 답변이 불가합니다.');
        }
    }
    //----------

    // 게시글 배열 참조
    $reply_array = &$write;

    // 최대 답변은 테이블에 잡아놓은 wr_reply 사이즈만큼만 가능합니다.
    if (strlen($reply_array['wr_reply']) == 10)
        alert('더 이상 답변하실 수 없습니다.'.PHP_EOL.PHP_EOL.'답변은 10단계 까지만 가능합니다.');

    $reply_len = strlen($reply_array['wr_reply']) + 1;
    if ($board['bo_reply_order']) {
        $begin_reply_char = 'A';
        $end_reply_char = 'Z';
        $reply_number = +1;
        $sql = " select MAX(SUBSTRING(wr_reply, {$reply_len}, 1)) as reply from {$write_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
    } else {
        $begin_reply_char = 'Z';
        $end_reply_char = 'A';
        $reply_number = -1;
        $sql = " select MIN(SUBSTRING(wr_reply, {$reply_len}, 1)) as reply from {$write_table} where wr_num = '{$reply_array['wr_num']}' and SUBSTRING(wr_reply, {$reply_len}, 1) <> '' ";
    }
    if ($reply_array['wr_reply']) $sql .= " and wr_reply like '{$reply_array['wr_reply']}%' ";
    $row = sql_fetch($sql);

    if (!$row['reply'])
        $reply_char = $begin_reply_char;
    else if ($row['reply'] == $end_reply_char) // A~Z은 26 입니다.
        alert('더 이상 답변하실 수 없습니다.'.PHP_EOL.PHP_EOL.'답변은 26개 까지만 가능합니다.');
    else
        $reply_char = chr(ord($row['reply']) + $reply_number);

    $reply = $reply_array['wr_reply'] . $reply_char;

    $title_msg = '글답변';
}

// 그룹접근 가능
if (!empty($group['gr_use_access'])) {
    if ($is_guest) {
        alert("접근 권한이 없습니다.\\n\\n회원이시라면 로그인 후 이용해 보십시오.", 'login.php?'.$qstr.'&amp;url='.urlencode($_SERVER['PHP_SELF'].'?bo_table='.$bo_table));
    }

    if ($is_admin == 'super' || $group['gr_admin'] == $member['mb_id'] || $board['bo_admin'] == $member['mb_id']) {
        ; // 통과
    } else {
        // 그룹접근
        $sql = " select gr_id from {$g4['group_member_table']} where gr_id = '{$board['gr_id']}' and mb_id = '{$member['mb_id']}' ";
        $row = sql_fetch($sql);
        if (!$row['gr_id'])
            alert('접근 권한이 없으므로 글쓰기가 불가합니다.'.PHP_EOL.PHP_EOL.'궁금하신 사항은 관리자에게 문의 바랍니다.');
    }
}

$g4['title'] = $board['bo_subject']." ".$title_msg;

$is_notice = false;
$notice_checked = '';
if ($is_admin && $w != 'r') {
    $is_notice = true;

    if ($w == 'u') {
        // 답변 수정시 공지 체크 없음
        if ($write['wr_reply']) {
            $is_notice = false;
        } else {
            if (in_array((int)$wr_id, $notice_array)) {
                $notice_checked = 'checked';
            }
        }
    }
}

$is_html = false;
if ($member['mb_level'] >= $board['bo_html_level'])
    $is_html = true;

$is_secret = $board['bo_use_secret'];

if ($board['bo_use_dhtml_editor'] && $member['mb_level'] >= $board['bo_html_level']) {
    define('_EDITOR_', true);
    $is_dhtml_editor = true;
} else {
    $is_dhtml_editor = false;
}

$is_mail = false;
if ($config['cf_email_use'] && $board['bo_use_email'])
    $is_mail = true;

$recv_email_checked = '';
if ($w == '' || strstr($write['wr_option'], 'mail'))
    $recv_email_checked = 'checked';

$is_name     = false;
$is_password = false;
$is_email    = false;
$is_homepage = false;
if ($is_guest || ($is_admin && $w == 'u' && $member['mb_id'] != $write['mb_id'])) {
    $is_name = true;
    $is_password = true;
    $is_email = true;
    $is_homepage = true;
}

$is_category = false;
if ($board['bo_use_category']) {
    $ca_name = "";
    if (isset($write['ca_name']))
        $ca_name = $write['ca_name'];
    $category_option = get_category_option($bo_table, $ca_name);
    $is_category = true;
}

$is_link = false;
if ($member['mb_level'] >= $board['bo_link_level']) {
    $is_link = true;
}

$is_file = false;
if ($member['mb_level'] >= $board['bo_upload_level']) {
    $is_file = true;
}

$is_file_content = false;
if ($board['bo_use_file_content']) {
    $is_file_content = true;
}

$name     = "";
$email    = "";
$homepage = "";
if ($w == "" || $w == "r") {
    if ($is_member) {
        if (isset($write['wr_name'])) {
            $name = get_text(cut_str($write['wr_name'],20));
        }
        $email = $member['mb_email'];
        $homepage = get_text($member['mb_homepage']);
    }
}

$html_checked   = "";
$html_value     = "";
$secret_checked = "";

if ($w == '') {
    $password_required = 'required';
} else if ($w == 'u') {
    $password_required = '';

    if (!$is_admin) {
        if (!($is_member && $member['mb_id'] == $write['mb_id'])) {
            if (sql_password($wr_password) != $write['wr_password']) {
                alert('패스워드가 틀립니다.');
            }
        }
    }

    $name = get_text(cut_str($write['wr_name'],20));
    $email = $write['wr_email'];
    $homepage = get_text($write['wr_homepage']);

    for ($i=1; $i<=$g4['link_count']; $i++) {
        $write['wr_link'.$i] = get_text($write['wr_link'.$i]);
        $link[$i] = $write['wr_link'.$i];
    }

    if (strstr($write['wr_option'], 'html1')) {
        $html_checked = 'checked';
        $html_value = 'html1';
    } else if (strstr($write['wr_option'], 'html2')) {
        $html_checked = 'checked';
        $html_value = 'html2';
    }

    if (strstr($write['wr_option'], 'secret')) {
        $secret_checked = 'checked';
    }

    $file = get_file($bo_table, $wr_id);
} else if ($w == 'r') {
    if (strstr($write['wr_option'], 'secret')) {
        $is_secret = true;
        $secret_checked = 'checked';
    }

    $password_required = "required";

    for ($i=1; $i<=$g4['link_count']; $i++) {
        $write['wr_link'.$i] = get_text($write['wr_link'.$i]);
    }
}

$subject = "";
if (isset($write['wr_subject'])) {
    $subject = preg_replace("/\"/", "&#034;", get_text(cut_str($write['wr_subject'], 255), 0));
}

$content = '';
if ($w == '') {
    $content = $board['bo_insert_content'];
} else if ($w == 'r') {
    if (!strstr($write['wr_option'], 'html')) {
        $content = "\\n\\n\\n &gt; "
                 ."\\n &gt; "
                 ."\\n &gt; ".preg_replace("/\n/", "\n> ", get_text($write['wr_content'], 0))
                 ."\\n &gt; "
                 ."\\n &gt; ";

    }
} else {
    $content = get_text($write['wr_content'], 0);
}

$upload_max_filesize = number_format($board['bo_upload_size']) . ' 바이트';

$width = $board['bo_table_width'];
if ($width <= 100)
    $width .= '%';

// 글자수 제한 설정값
if ($is_admin) {
    $write_min = $write_max = 0;
} else {
    $write_min = (int)$board['bo_write_min'];
    $write_max = (int)$board['bo_write_max'];
}

include_once($g4['path'].'/head.sub.php');
include_once('./board_head.php');

//--------------------------------------------------------------------------
// 가변 파일
$file_script = '';
$file_length = -1;
// 수정의 경우 파일업로드 필드가 가변적으로 늘어나야 하고 삭제 표시도 해주어야 합니다.
if ($w == 'u') {
    for ($i=0; $i<$file['count']; $i++) {
        $row = sql_fetch(" select bf_file, bf_content from {$g4['board_file_table']} where bo_table = '{$bo_table}' and wr_id = '{$wr_id}' and bf_no = '{$i}' ");
        if ($row['bf_file']) {
            $file_script .= 'add_file("<input type="checkbox" name="bf_file_del['.$i.']" value="1"><a href="'.$file[$i]['href'].'">'.$file[$i]['source'].'('.$file[$i]['size'].')</a> 파일 삭제';
            if ($is_file_content)
                //$file_script .= '<br><input type="text" class="ed" size="50" name="bf_content['.$i.']" value="'.$row['bf_content'].'" title="업로드 이미지 파일에 해당 되는 내용을 입력하세요.">';
                // 첨부파일설명에서 ' 또는 " 입력되면 오류나는 부분 수정
                $file_script .= '<br><input type="text" class="ed" size="50" name="bf_content['.$i.']" value="'.addslashes(get_text($row['bf_content'])).'" title="업로드 이미지 파일에 해당 되는 내용을 입력하세요.">';
            $file_script .= '\");'.PHP_EOL;
        }
        else
            $file_script .= 'add_file("");'.PHP_EOL;
    }
    $file_length = $file['count'] - 1;
}

if ($file_length < 0) {
    $file_script .= 'add_file("");'.PHP_EOL;
    $file_length = 0;
}
//--------------------------------------------------------------------------

if ($g4['https_url'])
    $action_url = "{$g4['https_url']}/{$g4['bbs']}/write_update.php";
else
    $action_url = "{$g4['bbs_path']}/write_update.php";

include_once ($board_skin_path.'/write.skin.php');

include_once('./board_tail.php');
include_once($g4['path'].'/tail.sub.php');

@include_once ($board_skin_path.'/write.tail.skin.php');
?>

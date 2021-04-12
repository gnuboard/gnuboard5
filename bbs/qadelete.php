<?php
include_once('./_common.php');

if($is_guest)
    alert('회원이시라면 로그인 후 이용해 주십시오.', G5_URL);

$token = isset($_REQUEST['token']) ? clean_xss_tags($_REQUEST['token'], 1, 1) : '';
$qa_id = isset($_REQUEST['qa_id']) ? (int) $_REQUEST['qa_id'] : 0;

$delete_token = get_session('ss_qa_delete_token');
set_session('ss_qa_delete_token', '');

//모든 회원의 토큰을 검사합니다.
if (!($token && $delete_token === $token))
    alert('토큰 에러로 삭제 불가합니다.');

$tmp_array = array();
if ($qa_id) // 건별삭제
    $tmp_array[0] = $qa_id;
else // 일괄삭제
    $tmp_array = (isset($_POST['chk_qa_id']) && is_array($_POST['chk_qa_id'])) ? $_POST['chk_qa_id'] : array();

$count = count($tmp_array);
if(!$count)
    alert('삭제할 게시글을 하나이상 선택해 주십시오.');

for($i=0; $i<$count; $i++) {
    $qa_id = (int) $tmp_array[$i];

    $sql = " select qa_id, mb_id, qa_type, qa_status, qa_parent, qa_content, qa_file1, qa_file2
                from {$g5['qa_content_table']}
                where qa_id = '$qa_id' ";
    $row = sql_fetch($sql);

    if(!$row['qa_id'])
        continue;

    // 자신의 글이 아니면 건너뜀
    if($is_admin != 'super' && $row['mb_id'] !== $member['mb_id'])
        continue;

    // 답변이 달린 글은 삭제못함
    if($is_admin != 'super' && !$row['qa_type'] && $row['qa_status'])
        continue;

    // 첨부파일 삭제
    for($k=1; $k<=2; $k++) {
        @unlink(G5_DATA_PATH.'/qa/'.clean_relative_paths($row['qa_file'.$k]));
        // 썸네일삭제
        if(preg_match("/\.({$config['cf_image_extension']})$/i", $row['qa_file'.$k])) {
            delete_qa_thumbnail($row['qa_file'.$k]);
        }
    }

    // 에디터 썸네일 삭제
    delete_editor_thumbnail($row['qa_content']);

    // 답변이 있는 질문글이라면 답변글 삭제
    if(!$row['qa_type'] && $row['qa_status']) {
        $row2 = sql_fetch(" select qa_content, qa_file1, qa_file2 from {$g5['qa_content_table']} where qa_parent = '$qa_id' ");
        // 첨부파일 삭제
        for($k=1; $k<=2; $k++) {
            @unlink(G5_DATA_PATH.'/qa/'.clean_relative_paths($row2['qa_file'.$k]));
            // 썸네일삭제
            if(preg_match("/\.({$config['cf_image_extension']})$/i", $row2['qa_file'.$k])) {
                delete_qa_thumbnail($row2['qa_file'.$k]);
            }
        }

        // 에디터 썸네일 삭제
        delete_editor_thumbnail($row2['qa_content']);

        sql_query(" delete from {$g5['qa_content_table']} where qa_type = '1' and qa_parent = '$qa_id' ");
    }

    // 답변글 삭제시 질문글의 상태변경
    if($row['qa_type']) {
        sql_query(" update {$g5['qa_content_table']} set qa_status = '0' where qa_id = '{$row['qa_parent']}' ");
    }

    // 글삭제
    sql_query(" delete from {$g5['qa_content_table']} where qa_id = '$qa_id' ");
}

goto_url(G5_BBS_URL.'/qalist.php'.preg_replace('/^&amp;/', '?', $qstr));
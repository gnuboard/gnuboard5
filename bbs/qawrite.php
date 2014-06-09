<?php
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

if($w != '' && $w != 'u' && $w != 'r') {
    alert('올바른 방법으로 이용해 주십시오.');
}

if($is_guest)
    alert('회원이시라면 로그인 후 이용해 보십시오.', './login.php?url='.urlencode(G5_BBS_URL.'/qalist.php'));

$qaconfig = get_qa_config();

$g5['title'] = $qaconfig['qa_title'];
include_once('./qahead.php');

$skin_file = $qa_skin_path.'/write.skin.php';

if(is_file($skin_file)) {
    /*==========================
    $w == a : 답변
    $w == r : 추가질문
    $w == u : 수정
    ==========================*/

    if($w == 'u' || $w == 'r') {
        $sql = " select * from {$g5['qa_content_table']} where qa_id = '$qa_id' ";
        if(!$is_admin) {
            $sql .= " and mb_id = '{$member['mb_id']}' ";
        }

        $write = sql_fetch($sql);

        if($w == 'u') {
            if(!$write['qa_id'])
                alert('게시글이 존재하지 않습니다.\\n삭제되었거나 자신의 글이 아닌 경우입니다.');

            if(!$is_admin) {
                if($write['qa_type'] == 0 && $write['qa_status'] == 1)
                    alert('답변이 등록된 문의글은 수정할 수 없습니다.');

                if($write['mb_id'] != $member['mb_id'])
                    alert('게시글을 수정할 권한이 없습니다.\\n\\n올바른 방법으로 이용해 주십시오.', G5_URL);
            }
        }
    }

    // 분류
    $category_option = '';
    if(trim($qaconfig['qa_category'])) {
        $category = explode('|', $qaconfig['qa_category']);
        for($i=0; $i<count($category); $i++) {
            $category_option .= option_selected($category[$i], $write['qa_category']);
        }
    } else {
        alert('1:1문의 설정에서 분류를 설정해 주십시오');
    }

    $is_dhtml_editor = false;
    // 모바일에서는 DHTML 에디터 사용불가
    if ($config['cf_editor'] && $qaconfig['qa_use_editor'] && !G5_IS_MOBILE) {
        $is_dhtml_editor = true;
    }

    // 추가질문에서는 제목을 공백으로
    if($w == 'r')
        $write['qa_subject'] = '';

    $content = '';
    if ($w == '') {
        $content = $qaconfig['qa_insert_content'];
    } else if($w == 'r') {
        if($is_dhtml_editor)
            $content = '<div><br><br><br>====== 이전 답변내용 =======<br></div>';
        else
            $content = "\n\n\n\n====== 이전 답변내용 =======\n";

        $content .= get_text($write['qa_content'], 0);
    } else {
        $content = get_text($write['qa_content'], 0);
    }

    $editor_html = editor_html('qa_content', $content, $is_dhtml_editor);
    $editor_js = '';
    $editor_js .= get_editor_js('qa_content', $is_dhtml_editor);
    $editor_js .= chk_editor_js('qa_content', $is_dhtml_editor);

    $upload_max_filesize = number_format($qaconfig['qa_upload_size']) . ' 바이트';

    $html_value = '';
    if ($write['qa_html']) {
        $html_checked = 'checked';
        $html_value = $write['qa_html'];

        if($w == 'r' && $write['qa_html'] == 1 && !$is_dhtml_editor)
            $html_value = 2;
    }

    $is_email = false;
    $req_email = '';
    if($qaconfig['qa_use_email']) {
        $is_email = true;

        if($qaconfig['qa_req_email'])
            $req_email = 'required';

        if($w == '' || $w == 'r')
            $write['qa_email'] = $member['mb_email'];

        if($w == 'u' && $is_admin && $write['qa_type'])
            $is_email = false;
    }

    $is_hp = false;
    $req_hp = '';
    if($qaconfig['qa_use_hp']) {
        $is_hp = true;

        if($qaconfig['qa_req_hp'])
            $req_hp = 'required';

        if($w == '' || $w == 'r')
            $write['qa_hp'] = $member['mb_hp'];

        if($w == 'u' && $is_admin && $write['qa_type'])
            $is_hp = false;
    }

    $list_href = G5_BBS_URL.'/qalist.php'.preg_replace('/^&amp;/', '?', $qstr);

    $action_url = https_url(G5_BBS_DIR).'/qawrite_update.php';

    include_once($skin_file);
} else {
    echo '<div>'.str_replace(G5_PATH.'/', '', $skin_file).'이 존재하지 않습니다.</div>';
}

include_once('./qatail.php');
?>
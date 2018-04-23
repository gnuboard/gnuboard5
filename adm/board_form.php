<?php
$sub_menu = "300100";
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check($auth[$sub_menu], 'w');

$sql = " select count(*) as cnt from {$g5['group_table']} ";
$row = sql_fetch($sql);
if (!$row['cnt'])
    alert('게시판그룹이 한개 이상 생성되어야 합니다.', './boardgroup_form.php');

$html_title = '게시판';

if (!isset($board['bo_device'])) {
    // 게시판 사용 필드 추가
    // both : pc, mobile 둘다 사용
    // pc : pc 전용 사용
    // mobile : mobile 전용 사용
    // none : 사용 안함
    sql_query(" ALTER TABLE  `{$g5['board_table']}` ADD  `bo_device` ENUM(  'both',  'pc',  'mobile' ) NOT NULL DEFAULT  'both' AFTER  `bo_subject` ", false);
}

if (!isset($board['bo_mobile_skin'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_mobile_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `bo_skin` ", false);
}

if (!isset($board['bo_gallery_width'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_gallery_width` INT NOT NULL AFTER `bo_gallery_cols`,  ADD `bo_gallery_height` INT NOT NULL DEFAULT '0' AFTER `bo_gallery_width`,  ADD `bo_mobile_gallery_width` INT NOT NULL DEFAULT '0' AFTER `bo_gallery_height`,  ADD `bo_mobile_gallery_height` INT NOT NULL DEFAULT '0' AFTER `bo_mobile_gallery_width` ", false);
}

if (!isset($board['bo_mobile_subject_len'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_mobile_subject_len` INT(11) NOT NULL DEFAULT '0' AFTER `bo_subject_len` ", false);
}

if (!isset($board['bo_mobile_page_rows'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_mobile_page_rows` INT(11) NOT NULL DEFAULT '0' AFTER `bo_page_rows` ", false);
}

if (!isset($board['bo_mobile_content_head'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_mobile_content_head` TEXT NOT NULL AFTER `bo_content_head`, ADD `bo_mobile_content_tail` TEXT NOT NULL AFTER `bo_content_tail`", false);
}

if (!isset($board['bo_use_cert'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_use_cert` ENUM('','cert','adult') NOT NULL DEFAULT '' AFTER `bo_use_email` ", false);
}

if (!isset($board['bo_use_sns'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_use_sns` TINYINT NOT NULL DEFAULT '0' AFTER `bo_use_cert` ", false);

    $result = sql_query(" select bo_table from `{$g5['board_table']}` ");
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        sql_query(" ALTER TABLE `{$g5['write_prefix']}{$row['bo_table']}`
                    ADD `wr_facebook_user` VARCHAR(255) NOT NULL DEFAULT '' AFTER `wr_ip`,
                    ADD `wr_twitter_user` VARCHAR(255) NOT NULL DEFAULT '' AFTER `wr_facebook_user` ", false);
    }
}

$sql = " SHOW COLUMNS FROM `{$g5['board_table']}` LIKE 'bo_use_cert' ";
$row = sql_fetch($sql);
if(strpos($row['Type'], 'hp-') === false) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` CHANGE `bo_use_cert` `bo_use_cert` ENUM('','cert','adult','hp-cert','hp-adult') NOT NULL DEFAULT '' ", false);
}

if (!isset($board['bo_use_list_file'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_use_list_file` TINYINT NOT NULL DEFAULT '0' AFTER `bo_use_list_view` ", false);

    $result = sql_query(" select bo_table from `{$g5['board_table']}` ");
    for ($i=0; $row=sql_fetch_array($result); $i++) {
        sql_query(" ALTER TABLE `{$g5['write_prefix']}{$row['bo_table']}`
                    ADD `wr_file` TINYINT NOT NULL DEFAULT '0' AFTER `wr_datetime` ", false);
    }
}

if (!isset($board['bo_mobile_subject'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_mobile_subject` VARCHAR(255) NOT NULL DEFAULT '' AFTER `bo_subject` ", false);
}

if (!isset($board['bo_use_captcha'])) {
    sql_query(" ALTER TABLE `{$g5['board_table']}` ADD `bo_use_captcha` TINYINT NOT NULL DEFAULT '0' AFTER `bo_use_sns` ");
}

$required = "";
$readonly = "";
if ($w == '') {

    $html_title .= ' 생성';

    $required = 'required';
    $required_valid = 'alnum_';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $board['bo_count_delete'] = 1;
    $board['bo_count_modify'] = 1;
    $board['bo_read_point'] = $config['cf_read_point'];
    $board['bo_write_point'] = $config['cf_write_point'];
    $board['bo_comment_point'] = $config['cf_comment_point'];
    $board['bo_download_point'] = $config['cf_download_point'];

    $board['bo_gallery_cols'] = 4;
    $board['bo_gallery_width'] = 202;
    $board['bo_gallery_height'] = 150;
    $board['bo_mobile_gallery_width'] = 125;
    $board['bo_mobile_gallery_height'] = 100;
    $board['bo_table_width'] = 100;
    $board['bo_page_rows'] = $config['cf_page_rows'];
    $board['bo_mobile_page_rows'] = $config['cf_page_rows'];
    $board['bo_subject_len'] = 60;
    $board['bo_mobile_subject_len'] = 30;
    $board['bo_new'] = 24;
    $board['bo_hot'] = 100;
    $board['bo_image_width'] = 600;
    $board['bo_upload_count'] = 2;
    $board['bo_upload_size'] = 1048576;
    $board['bo_reply_order'] = 1;
    $board['bo_use_search'] = 1;
    $board['bo_skin'] = 'basic';
    $board['bo_mobile_skin'] = 'basic';
    $board['gr_id'] = $gr_id;
    $board['bo_use_secret'] = 0;
    $board['bo_include_head'] = '_head.php';
    $board['bo_include_tail'] = '_tail.php';

} else if ($w == 'u') {

    $html_title .= ' 수정';

    if (!$board['bo_table'])
        alert('존재하지 않은 게시판 입니다.');

    if ($is_admin == 'group') {
        if ($member['mb_id'] != $group['gr_admin'])
            alert('그룹이 틀립니다.');
    }

    $readonly = 'readonly';

}

if ($is_admin != 'super') {
    $group = get_group($board['gr_id']);
    $is_admin = is_admin($member['mb_id']);
}

$g5['title'] = $html_title;
include_once ('./admin.head.php');

$pg_anchor = '<ul class="anchor">
    <li><a href="#anc_bo_basic">기본 설정</a></li>
    <li><a href="#anc_bo_auth">권한 설정</a></li>
    <li><a href="#anc_bo_function">기능 설정</a></li>
    <li><a href="#anc_bo_design">디자인/양식</a></li>
    <li><a href="#anc_bo_point">포인트 설정</a></li>
    <li><a href="#anc_bo_extra">여분필드</a></li>
</ul>';

?>

<form name="fboardform" id="fboardform" action="./board_form_update.php" onsubmit="return fboardform_submit(this)" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?php echo $w ?>">
<input type="hidden" name="sfl" value="<?php echo $sfl ?>">
<input type="hidden" name="stx" value="<?php echo $stx ?>">
<input type="hidden" name="sst" value="<?php echo $sst ?>">
<input type="hidden" name="sod" value="<?php echo $sod ?>">
<input type="hidden" name="page" value="<?php echo $page ?>">
<input type="hidden" name="token" value="">

<section id="anc_bo_basic">
    <h2 class="h2_frm">게시판 기본 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 기본 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_3">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bo_table">TABLE<?php echo $sound_only ?></label></th>
            <td colspan="2">
                <input type="text" name="bo_table" value="<?php echo $board['bo_table'] ?>" id="bo_table" <?php echo $required ?> <?php echo $readonly ?> class="frm_input <?php echo $reaonly ?> <?php echo $required ?> <?php echo $required_valid ?>" maxlength="20">
                <?php if ($w == '') { ?>
                    영문자, 숫자, _ 만 가능 (공백없이 20자 이내)
                <?php } else { ?>
                    <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $board['bo_table'] ?>" class="btn_frmline">게시판 바로가기</a>
                    <a href="./board_list.php?<?php echo $qstr;?>" class="btn_frmline">목록으로</a>
                <?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="gr_id">그룹<strong class="sound_only">필수</strong></label></th>
            <td colspan="2">
                <?php echo get_group_select('gr_id', $board['gr_id'], 'required'); ?>
                <?php if ($w=='u') { ?><a href="javascript:document.location.href='./board_list.php?sfl=a.gr_id&stx='+document.fboardform.gr_id.value;" class="btn_frmline">동일그룹 게시판목록</a><?php } ?>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_subject">게시판 제목<strong class="sound_only">필수</strong></label></th>
            <td colspan="2">
                <input type="text" name="bo_subject" value="<?php echo get_text($board['bo_subject']) ?>" id="bo_subject" required class="required frm_input" size="80" maxlength="120">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_mobile_subject">모바일 게시판 제목</label></th>
            <td colspan="2">
                <?php echo help("모바일에서 보여지는 게시판 제목이 다른 경우에 입력합니다. 입력이 없으면 기본 게시판 제목이 출력됩니다.") ?>
                <input type="text" name="bo_mobile_subject" value="<?php echo get_text($board['bo_mobile_subject']) ?>" id="bo_mobile_subject" class="frm_input" size="80" maxlength="120">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_device">접속기기</label></th>
            <td>
                <?php echo help("PC 와 모바일 사용을 구분합니다.") ?>
                <select id="bo_device" name="bo_device">
                    <option value="both"<?php echo get_selected($board['bo_device'], 'both'); ?>>PC와 모바일에서 모두 사용</option>
                    <option value="pc"<?php echo get_selected($board['bo_device'], 'pc'); ?>>PC 전용</option>
                    <option value="mobile"<?php echo get_selected($board['bo_device'], 'mobile'); ?>>모바일 전용</option>
                </select>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_device" value="1" id="chk_grp_device">
                <label for="chk_grp_device">그룹적용</label>
                <input type="checkbox" name="chk_all_device" value="1" id="chk_all_device">
                <label for="chk_all_device">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_category_list">분류</label></th>
            <td>
                <?php echo help('분류와 분류 사이는 | 로 구분하세요. (예: 질문|답변) 첫자로 #은 입력하지 마세요. (예: #질문|#답변 [X])'."\n".'분류명에 일부 특수문자 ()/ 는 사용할수 없습니다.'); ?>
                <input type="text" name="bo_category_list" value="<?php echo get_text($board['bo_category_list']) ?>" id="bo_category_list" class="frm_input" size="70">
                <input type="checkbox" name="bo_use_category" value="1" id="bo_use_category" <?php echo $board['bo_use_category']?'checked':''; ?>>
                <label for="bo_use_category">사용</label>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_category_list" value="1" id="chk_grp_category_list">
                <label for="chk_grp_category_list">그룹적용</label>
                <input type="checkbox" name="chk_all_category_list" value="1" id="chk_all_category_list">
                <label for="chk_all_category_list">전체적용</label>
            </td>
        </tr>
        <?php if ($w == 'u') { ?>
        <tr>
            <th scope="row"><label for="proc_count">카운트 조정</label></th>
            <td colspan="2">
                <?php echo help('현재 원글수 : '.number_format($board['bo_count_write']).', 현재 댓글수 : '.number_format($board['bo_count_comment'])."\n".'게시판 목록에서 글의 번호가 맞지 않을 경우에 체크하십시오.') ?>
                <input type="checkbox" name="proc_count" value="1" id="proc_count">
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</section>



<section id="anc_bo_auth">
    <h2 class="h2_frm">게시판 권한 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 권한 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_3">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bo_admin">게시판 관리자</label></th>
            <td>
                <input type="text" name="bo_admin" value="<?php echo $board['bo_admin'] ?>" id="bo_admin" class="frm_input" maxlength="20">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_admin" value="1" id="chk_grp_admin">
                <label for="chk_grp_admin">그룹적용</label>
                <input type="checkbox" name="chk_all_admin" value="1" id="chk_all_admin">
                <label for="chk_all_admin">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_list_level">목록보기 권한</label></th>
            <td>
                <?php echo help('권한 1은 비회원, 2 이상 회원입니다. 권한은 10 이 가장 높습니다.') ?>
                <?php echo get_member_level_select('bo_list_level', 1, 10, $board['bo_list_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_list_level" value="1" id="chk_grp_list_level">
                <label for="chk_grp_list_level">그룹적용</label>
                <input type="checkbox" name="chk_all_list_level" value="1" id="chk_all_list_level">
                <label for="chk_all_list_level">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_read_level">글읽기 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_read_level', 1, 10, $board['bo_read_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_read_level" value="1" id="chk_grp_read_level">
                <label for="chk_grp_read_level">그룹적용</label>
                <input type="checkbox" name="chk_all_read_level" value="1" id="chk_all_read_level">
                <label for="chk_all_read_level">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_write_level">글쓰기 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_write_level', 1, 10, $board['bo_write_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_write_level" value="1" id="chk_grp_write_level">
                <label for="chk_grp_write_level">그룹적용</label>
                <input type="checkbox" name="chk_all_write_level" value="1" id="chk_all_write_level">
                <label for="chk_all_write_level">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_reply_level">글답변 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_reply_level', 1, 10, $board['bo_reply_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_reply_level" value="1" id="chk_grp_reply_level">
                <label for="chk_grp_reply_level">그룹적용</label>
                <input type="checkbox" name="chk_all_reply_level" value="1" id="chk_all_reply_level">
                <label for="chk_all_reply_level">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_comment_level">댓글쓰기 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_comment_level', 1, 10, $board['bo_comment_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_comment_level" value="1" id="chk_grp_comment_level">
                <label for="chk_grp_comment_level">그룹적용</label>
                <input type="checkbox" name="chk_all_comment_level" value="1" id="chk_all_comment_level">
                <label for="chk_all_comment_level">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_link_level">링크 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_link_level', 1, 10, $board['bo_link_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_link_level" value="1" id="chk_grp_link_level">
                <label for="chk_grp_link_level">그룹적용</label>
                <input type="checkbox" name="chk_all_link_level" value="1" id="chk_all_link_level">
                <label for="chk_all_link_level">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_upload_level">업로드 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_upload_level', 1, 10, $board['bo_upload_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_upload_level" value="1" id="chk_grp_upload_level">
                <label for="chk_grp_upload_level">그룹적용</label>
                <input type="checkbox" name="chk_all_upload_level" value="1" id="chk_all_upload_level">
                <label for="chk_all_upload_level">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_download_level">다운로드 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_download_level', 1, 10, $board['bo_download_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_download_level" value="1" id="chk_grp_download_level">
                <label for="chk_grp_download_level">그룹적용</label>
                <input type="checkbox" name="chk_all_download_level" value="1" id="chk_all_download_level">
                <label for="chk_all_download_level">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_html_level">HTML 쓰기 권한</label></th>
            <td>
                <?php echo get_member_level_select('bo_html_level', 1, 10, $board['bo_html_level']) ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_html_level" value="1" id="chk_grp_html_level">
                <label for="chk_grp_html_level">그룹적용</label>
                <input type="checkbox" name="chk_all_html_level" value="1" id="chk_all_html_level">
                <label for="chk_all_html_level">전체적용</label>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>



<section id="anc_bo_function">
    <h2 class="h2_frm">게시판 기능 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 기능 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_3">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="bo_count_modify">원글 수정 불가<strong class="sound_only">필수</strong></label></th>
            <td>
                 <?php echo help('댓글의 수가 설정 수 이상이면 원글을 수정할 수 없습니다. 0으로 설정하시면 댓글 수에 관계없이 수정할 수있습니다.'); ?>
                댓글 <input type="text" name="bo_count_modify" value="<?php echo $board['bo_count_modify'] ?>" id="bo_count_modify" required class="required numeric frm_input" size="3">개 이상 달리면 수정불가
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_count_modify" value="1" id="chk_grp_count_modify">
                <label for="chk_grp_count_modify">그룹적용</label>
                <input type="checkbox" name="chk_all_count_modify" value="1" id="chk_all_count_modify">
                <label for="chk_all_count_modify">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_count_delete">원글 삭제 불가<strong class="sound_only">필수</strong></label></th>
            <td>
                댓글 <input type="text" name="bo_count_delete" value="<?php echo $board['bo_count_delete'] ?>" id="bo_count_delete" required class="required numeric frm_input" size="3">개 이상 달리면 삭제불가
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_count_delete" value="1" id="chk_grp_count_delete">
                <label for="chk_grp_count_delete">그룹적용</label>
                <input type="checkbox" name="chk_all_count_delete" value="1" id="chk_all_count_delete">
                <label for="chk_all_count_delete">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_sideview">글쓴이 사이드뷰</label></th>
            <td>
                <input type="checkbox" name="bo_use_sideview" value="1" id="bo_use_sideview" <?php echo $board['bo_use_sideview']?'checked':''; ?>>
                사용 (글쓴이 클릭시 나오는 레이어 메뉴)
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_sideview" value="1" id="chk_grp_use_sideview">
                <label for="chk_grp_use_sideview">그룹적용</label>
                <input type="checkbox" name="chk_all_use_sideview" value="1" id="chk_all_use_sideview">
                <label for="chk_all_use_sideview">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_secret">비밀글 사용</label></th>
            <td>
                <?php echo help('"체크박스"는 글작성시 비밀글 체크가 가능합니다. "무조건"은 작성되는 모든글을 비밀글로 작성합니다. (관리자는 체크박스로 출력합니다.) 스킨에 따라 적용되지 않을 수 있습니다.') ?>
                <select id="bo_use_secret" name="bo_use_secret">
                    <?php echo option_selected(0, $board['bo_use_secret'], "사용하지 않음"); ?>
                    <?php echo option_selected(1, $board['bo_use_secret'], "체크박스"); ?>
                    <?php echo option_selected(2, $board['bo_use_secret'], "무조건"); ?>
                </select>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_secret" value="1" id="chk_grp_use_secret">
                <label for="chk_grp_use_secret">그룹적용</label>
                <input type="checkbox" name="chk_all_use_secret" value="1" id="chk_all_use_secret">
                <label for="chk_all_use_secret">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_dhtml_editor">DHTML 에디터 사용</label></th>
            <td>
                <?php echo help('글작성시 내용을 DHTML 에디터 기능으로 사용할 것인지 설정합니다. 스킨에 따라 적용되지 않을 수 있습니다.') ?>
                <input type="checkbox" name="bo_use_dhtml_editor" value="1" <?php echo $board['bo_use_dhtml_editor']?'checked':''; ?> id="bo_use_dhtml_editor">
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_dhtml_editor" value="1" id="chk_grp_use_dhtml_editor">
                <label for="chk_grp_use_dhtml_editor">그룹적용</label>
                <input type="checkbox" name="chk_all_use_dhtml_editor" value="1" id="chk_all_use_dhtml_editor">
                <label for="chk_all_use_dhtml_editor">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_rss_view">RSS 보이기 사용</label></th>
            <td>
                <?php echo help('비회원 글읽기가 가능하고 RSS 보이기 사용에 체크가 되어야만 RSS 지원을 합니다.') ?>
                <input type="checkbox" name="bo_use_rss_view" value="1" <?php echo $board['bo_use_rss_view']?'checked':''; ?> id="bo_use_rss_view">
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_rss_view" value="1" id="chk_grp_use_rss_view">
                <label for="chk_grp_use_rss_view">그룹적용</label>
                <input type="checkbox" name="chk_all_use_rss_view" value="1" id="chk_all_use_rss_view">
                <label for="chk_all_use_rss_view">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_good">추천 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_good" value="1" <?php echo $board['bo_use_good']?'checked':''; ?> id="bo_use_good">
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_good" value="1" id="chk_grp_use_good">
                <label for="chk_grp_use_good">그룹적용</label>
                <input type="checkbox" name="chk_all_use_good" value="1" id="chk_all_use_good">
                <label for="chk_all_use_good">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_nogood">비추천 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_nogood" value="1" id="bo_use_nogood" <?php echo $board['bo_use_nogood']?'checked':''; ?>>
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_nogood" value="1" id="chk_grp_use_nogood">
                <label for="chk_grp_use_nogood">그룹적용</label>
                <input type="checkbox" name="chk_all_use_nogood" value="1" id="chk_all_use_nogood">
                <label for="chk_all_use_nogood">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_name">이름(실명) 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_name" value="1" id="bo_use_name" <?php echo $board['bo_use_name']?'checked':''; ?>>
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_name" value="1" id="chk_grp_use_name">
                <label for="chk_grp_use_name">그룹적용</label>

                <input type="checkbox" name="chk_all_use_name" value="1" id="chk_all_use_name">
                <label for="chk_all_use_name">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_signature">서명보이기 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_signature" value="1" id="bo_use_signature" <?php echo $board['bo_use_signature']?'checked':''; ?>>
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_signature" value="1" id="chk_grp_use_signature">
                <label for="chk_grp_use_signature">그룹적용</label>
                <input type="checkbox" name="chk_all_use_signature" value="1" id="chk_all_use_signature">
                <label for="chk_all_use_signature">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_ip_view">IP 보이기 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_ip_view" value="1" id="bo_use_ip_view" <?php echo $board['bo_use_ip_view']?'checked':''; ?>>
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_ip_view" value="1" id="chk_grp_use_ip_view">
                <label for="chk_grp_use_ip_view">그룹적용</label>
                <input type="checkbox" name="chk_all_use_ip_view" value="1" id="chk_all_use_ip_view">
                <label for="chk_all_use_ip_view">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_list_content">목록에서 내용 사용</label></th>
            <td>
                <?php echo help("목록에서 게시판 제목외에 내용도 읽어와야 할 경우에 설정하는 옵션입니다. 기본은 사용하지 않습니다."); ?>
                <input type="checkbox" name="bo_use_list_content" value="1" id="bo_use_list_content" <?php echo $board['bo_use_list_content']?'checked':''; ?>>
                사용 (사용시 속도가 느려질 수 있습니다.)
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_list_content" value="1" id="chk_grp_use_list_content">
                <label for="chk_grp_use_list_content">그룹적용</label>
                <input type="checkbox" name="chk_all_use_list_content" value="1" id="chk_all_use_list_content">
                <label for="chk_all_use_list_content">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_list_file">목록에서 파일 사용</label></th>
            <td>
                <?php echo help("목록에서 게시판 첨부파일을 읽어와야 할 경우에 설정하는 옵션입니다. 기본은 사용하지 않습니다."); ?>
                <input type="checkbox" name="bo_use_list_file" value="1" id="bo_use_list_file" <?php echo $board['bo_use_list_file']?'checked':''; ?>>
                사용 (사용시 속도가 느려질 수 있습니다.)
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_list_file" value="1" id="chk_grp_use_list_file">
                <label for="chk_grp_use_list_file">그룹적용</label>
                <input type="checkbox" name="chk_all_use_list_file" value="1" id="chk_all_use_list_file">
                <label for="chk_all_use_list_file">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_list_view">전체목록보이기 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_list_view" value="1" id="bo_use_list_view" <?php echo $board['bo_use_list_view']?'checked':''; ?>>
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_list_view" value="1" id="chk_grp_use_list_view">
                <label for="chk_grp_use_list_view">그룹적용</label>
                <input type="checkbox" name="chk_all_use_list_view" value="1" id="chk_all_use_list_view">
                <label for="chk_all_use_list_view">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_email">메일발송 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_email" value="1" id="bo_use_email" <?php echo $board['bo_use_email']?'checked':''; ?>>
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_email" value="1" id="chk_grp_use_email">
                <label for="chk_grp_use_email">그룹적용</label>
                <input type="checkbox" name="chk_all_use_email" value="1" id="chk_all_use_email">
                <label for="chk_all_use_email">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_cert">본인확인 사용</label></th>
            <td>
                <?php echo help("본인확인 여부에 따라 게시물을 조회 할 수 있도록 합니다."); ?>
                <select id="bo_use_cert" name="bo_use_cert">
                    <?php
                    echo option_selected("",  $board['bo_use_cert'], "사용안함");
                    if ($config['cf_cert_use']) {
                        echo option_selected("cert",  $board['bo_use_cert'], "본인확인된 회원전체");
                        echo option_selected("adult", $board['bo_use_cert'], "본인확인된 성인회원만");
                        echo option_selected("hp-cert",  $board['bo_use_cert'], "휴대폰 본인확인된 회원전체");
                        echo option_selected("hp-adult", $board['bo_use_cert'], "휴대폰 본인확인된 성인회원만");
                    }
                    ?>
                </select>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_cert" value="1" id="chk_grp_use_cert">
                <label for="chk_grp_use_cert">그룹적용</label>
                <input type="checkbox" name="chk_all_use_cert" value="1" id="chk_all_use_cert">
                <label for="chk_all_use_cert">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_upload_count">파일 업로드 개수<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('게시물 한건당 업로드 할 수 있는 파일의 최대 개수 (0 은 파일첨부 사용하지 않음)') ?>
                <input type="text" name="bo_upload_count" value="<?php echo $board['bo_upload_count'] ?>" id="bo_upload_count" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_upload_count" value="1" id="chk_grp_upload_count">
                <label for="chk_grp_upload_count">그룹적용</label>
                <input type="checkbox" name="chk_all_upload_count" value="1" id="chk_all_upload_count">
                <label for="chk_all_upload_count">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_upload_size">파일 업로드 용량<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('최대 '.ini_get("upload_max_filesize").' 이하 업로드 가능, 1 MB = 1,048,576 bytes') ?>
                업로드 파일 한개당 <input type="text" name="bo_upload_size" value="<?php echo $board['bo_upload_size'] ?>" id="bo_upload_size" required class="required numeric frm_input"  size="10"> bytes 이하
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_upload_size" value="1" id="chk_grp_upload_size">
                <label for="chk_grp_upload_size">그룹적용</label>
                <input type="checkbox" name="chk_all_upload_size" value="1" id="chk_all_upload_size">
                <label for="chk_all_upload_size">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_file_content">파일 설명 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_file_content" value="1" id="bo_use_file_content" <?php echo $board['bo_use_file_content']?'checked':''; ?>>사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_file_content" value="1" id="chk_grp_use_file_content">
                <label for="chk_grp_use_file_content">그룹적용</label>
                <input type="checkbox" name="chk_all_use_file_content" value="1" id="chk_all_use_file_content">
                <label for="chk_all_use_file_content">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_write_min">최소 글수 제한</label></th>
            <td>
                <?php echo help('글 입력시 최소 글자수를 설정. 0을 입력하거나 최고관리자, DHTML 에디터 사용시에는 검사하지 않음') ?>
                <input type="text" name="bo_write_min" value="<?php echo $board['bo_write_min'] ?>" id="bo_write_min" class="numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_write_min" value="1" id="chk_grp_write_min">
                <label for="chk_grp_write_min">그룹적용</label>
                <input type="checkbox" name="chk_all_write_min" value="1" id="chk_all_write_min">
                <label for="chk_all_write_min">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_write_max">최대 글수 제한</label></th>
            <td>
                <?php echo help('글 입력시 최대 글자수를 설정. 0을 입력하거나 최고관리자, DHTML 에디터 사용시에는 검사하지 않음') ?>
                <input type="text" name="bo_write_max" value="<?php echo $board['bo_write_max'] ?>" id="bo_write_max" class="numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_write_max" value="1" id="chk_grp_write_max">
                <label for="chk_grp_write_max">그룹적용</label>
                <input type="checkbox" name="chk_all_write_max" value="1" id="chk_all_write_max">
                <label for="chk_all_write_max">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_comment_min">최소 댓글수 제한</label></th>
            <td>
                <?php echo help('댓글 입력시 최소 글자수를 설정. 0을 입력하면 검사하지 않음') ?>
                <input type="text" name="bo_comment_min" value="<?php echo $board['bo_comment_min'] ?>" id="bo_comment_min" class="numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_comment_min" value="1" id="chk_grp_comment_min">
                <label for="chk_grp_comment_min">그룹적용</label>
                <input type="checkbox" name="chk_all_comment_min" value="1" id="chk_all_comment_min">
                <label for="chk_all_comment_min">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_comment_max">최대 댓글수 제한</label></th>
            <td>
                <?php echo help('댓글 입력시 최대 글자수를 설정. 0을 입력하면 검사하지 않음') ?>
                <input type="text" name="bo_comment_max" value="<?php echo $board['bo_comment_max'] ?>" id="bo_comment_max" class="numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_comment_max" value="1" id="chk_grp_comment_max">
                <label for="chk_grp_comment_max">그룹적용</label>
                <input type="checkbox" name="chk_all_comment_max" value="1" id="chk_all_comment_max">
                <label for="chk_all_comment_max">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_sns">SNS 사용</label></th>
            <td>
                <?php echo help("사용에 체크하시면 소셜네트워크서비스(SNS)에 글을 퍼가거나 댓글을 동시에 등록할수 있습니다.<br>기본환경설정의 SNS 설정을 하셔야 사용이 가능합니다.") ?>
                <input type="checkbox" name="bo_use_sns" value="1" id="bo_use_sns" <?php echo $board['bo_use_sns']?'checked':''; ?>>
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_sns" value="1" id="chk_grp_use_sns">
                <label for="chk_grp_use_sns">그룹적용</label>
                <input type="checkbox" name="chk_all_use_sns" value="1" id="chk_all_use_sns">
                <label for="chk_all_use_sns">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_search">전체 검색 사용</label></th>
            <td>
                <input type="checkbox" name="bo_use_search" value="1" id="bo_use_search" <?php echo $board['bo_use_search']?'checked':''; ?>>
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_search" value="1" id="chk_grp_use_search">
                <label for="chk_grp_use_search">그룹적용</label>
                <input type="checkbox" name="chk_all_use_search" value="1" id="chk_all_use_search">
                <label for="chk_all_use_search">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_order">출력 순서</label></th>
            <td>
                <?php echo help('숫자가 낮은 게시판 부터 메뉴나 검색시 우선 출력합니다.') ?>
                <input type="text" name="bo_order" value="<?php echo $board['bo_order'] ?>" id="bo_order" class="frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_order" value="1" id="chk_grp_order">
                <label for="chk_grp_order">그룹적용</label>
                <input type="checkbox" name="chk_all_order" value="1" id="chk_all_order">
                <label for="chk_all_order">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_use_captcha">캡챠 사용</label></th>
            <td>
                <?php echo help("체크하면 글 작성시 캡챠를 무조건 사용합니다.( 회원 + 비회원 모두 )<br>미 체크하면 비회원에게만 캡챠를 사용합니다.") ?>
                <input type="checkbox" name="bo_use_captcha" value="1" <?php echo $board['bo_use_captcha']?'checked':''; ?> id="bo_use_captcha">
                사용
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_use_captcha" value="1" id="chk_grp_use_captcha">
                <label for="chk_grp_use_captcha">그룹적용</label>
                <input type="checkbox" name="chk_all_use_captcha" value="1" id="chk_all_use_captcha">
                <label for="chk_all_use_captcha">전체적용</label>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>


<section id="anc_bo_design">
    <h2 class="h2_frm">게시판 디자인/양식</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 디자인/양식</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_3">
        </colgroup>
        <tbody>
            <tr>
            <th scope="row"><label for="bo_skin">스킨 디렉토리<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_skin_select('board', 'bo_skin', 'bo_skin', $board['bo_skin'], 'required'); ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_skin" value="1" id="chk_grp_skin">
                <label for="chk_grp_skin">그룹적용</label>
                <input type="checkbox" name="chk_all_skin" value="1" id="chk_all_skin">
                <label for="chk_all_skin">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_mobile_skin">모바일<br>스킨 디렉토리<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo get_mobile_skin_select('board', 'bo_mobile_skin', 'bo_mobile_skin', $board['bo_mobile_skin'], 'required'); ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_mobile_skin" value="1" id="chk_grp_mobile_skin">
                <label for="chk_grp_mobile_skin">그룹적용</label>
                <input type="checkbox" name="chk_all_mobile_skin" value="1" id="chk_all_mobile_skin">
                <label for="chk_all_mobile_skin">전체적용</label>
            </td>
        </tr>
        <?php if ($is_admin === 'super'){   // 슈퍼관리자인 경우에만 수정 가능 ?>
        <tr>
            <th scope="row"><label for="bo_include_head">상단 파일 경로</label></th>
            <td>
                <input type="text" name="bo_include_head" value="<?php echo $board['bo_include_head'] ?>" id="bo_include_head" class="frm_input" size="50">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_include_head" value="1" id="chk_grp_include_head">
                <label for="chk_grp_include_head">그룹적용</label>
                <input type="checkbox" name="chk_all_include_head" value="1" id="chk_all_include_head">
                <label for="chk_all_include_head">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_include_tail">하단 파일 경로</label></th>
            <td>
                <input type="text" name="bo_include_tail" value="<?php echo $board['bo_include_tail'] ?>" id="bo_include_tail" class="frm_input" size="50">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_include_tail" value="1" id="chk_grp_include_tail">
                <label for="chk_grp_include_tail">그룹적용</label>
                <input type="checkbox" name="chk_all_include_tail" value="1" id="chk_all_include_tail">
                <label for="chk_all_include_tail">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_content_head">상단 내용</label></th>
            <td>
                <?php echo editor_html("bo_content_head", get_text($board['bo_content_head'], 0)); ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_content_head" value="1" id="chk_grp_content_head">
                <label for="chk_grp_content_head">그룹적용</label>
                <input type="checkbox" name="chk_all_content_head" value="1" id="chk_all_content_head">
                <label for="chk_all_content_head">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_content_tail">하단 내용</label></th>
            <td>
                <?php echo editor_html("bo_content_tail", get_text($board['bo_content_tail'], 0)); ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_content_tail" value="1" id="chk_grp_content_tail">
                <label for="chk_grp_content_tail">그룹적용</label>
                <input type="checkbox" name="chk_all_content_tail" value="1" id="chk_all_content_tail">
                <label for="chk_all_content_tail">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_mobile_content_head">모바일 상단 내용</label></th>
            <td>
                <?php echo editor_html("bo_mobile_content_head", get_text($board['bo_mobile_content_head'], 0)); ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_mobile_content_head" value="1" id="chk_grp_mobile_content_head">
                <label for="chk_grp_mobile_content_head">그룹적용</label>
                <input type="checkbox" name="chk_all_mobile_content_head" value="1" id="chk_all_mobile_content_head">
                <label for="chk_all_mobile_content_head">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_mobile_content_tail">모바일 하단 내용</label></th>
            <td>
                <?php echo editor_html("bo_mobile_content_tail", get_text($board['bo_mobile_content_tail'], 0)); ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_mobile_content_tail" value="1" id="chk_grp_mobile_content_tail">
                <label for="chk_grp_mobile_content_tail">그룹적용</label>
                <input type="checkbox" name="chk_all_mobile_content_tail" value="1" id="chk_all_mobile_content_tail">
                <label for="chk_all_mobile_content_tail">전체적용</label>
            </td>
        </tr>
        <?php }     //end if $is_admin === 'super' ?>
         <tr>
            <th scope="row"><label for="bo_insert_content">글쓰기 기본 내용</label></th>
            <td>
                <textarea id="bo_insert_content" name="bo_insert_content" rows="5"><?php echo $board['bo_insert_content'] ?></textarea>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_insert_content" value="1" id="chk_grp_insert_content">
                <label for="chk_grp_insert_content">그룹적용</label>
                <input type="checkbox" name="chk_all_insert_content" value="1" id="chk_all_insert_content">
                <label for="chk_all_insert_content">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_subject_len">제목 길이<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('목록에서의 제목 글자수. 잘리는 글은 … 로 표시') ?>
                <input type="text" name="bo_subject_len" value="<?php echo $board['bo_subject_len'] ?>" id="bo_subject_len" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_subject_len" value="1" id="chk_grp_subject_len">
                <label for="chk_grp_subject_len">그룹적용</label>
                <input type="checkbox" name="chk_all_subject_len" value="1" id="chk_all_subject_len">
                <label for="chk_all_subject_len">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_mobile_subject_len">모바일 제목 길이<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('목록에서의 제목 글자수. 잘리는 글은 … 로 표시') ?>
                <input type="text" name="bo_mobile_subject_len" value="<?php echo $board['bo_mobile_subject_len'] ?>" id="bo_mobile_subject_len" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_mobile_subject_len" value="1" id="chk_grp_mobile_subject_len">
                <label for="chk_grp_mobile_subject_len">그룹적용</label>
                <input type="checkbox" name="chk_all_mobile_subject_len" value="1" id="chk_all_mobile_subject_len">
                <label for="chk_all_mobile_subject_len">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_page_rows">페이지당 목록 수<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_page_rows" value="<?php echo $board['bo_page_rows'] ?>" id="bo_page_rows" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_page_rows" value="1" id="chk_grp_page_rows">
                <label for="chk_grp_page_rows">그룹적용</label>
                <input type="checkbox" name="chk_all_page_rows" value="1" id="chk_all_page_rows">
                <label for="chk_all_page_rows">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_mobile_page_rows">모바일 페이지당 목록 수<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_mobile_page_rows" value="<?php echo $board['bo_mobile_page_rows'] ?>" id="bo_mobile_page_rows" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_mobile_page_rows" value="1" id="chk_grp_mobile_page_rows">
                <label for="chk_grp_mobile_page_rows">그룹적용</label>
                <input type="checkbox" name="chk_all_mobile_page_rows" value="1" id="chk_all_mobile_page_rows">
                <label for="chk_all_mobile_page_rows">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_gallery_cols">갤러리 이미지 수<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('갤러리 형식의 게시판 목록에서 이미지를 한줄에 몇장씩 보여 줄 것인지를 설정하는 값') ?>
                <?php echo get_member_level_select('bo_gallery_cols', 1, 10, $board['bo_gallery_cols']); ?>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_gallery_cols" value="1" id="chk_grp_gallery_cols">
                <label for="chk_grp_gallery_cols">그룹적용</label>
                <input type="checkbox" name="chk_all_gallery_cols" value="1" id="chk_all_gallery_cols">
                <label for="chk_all_gallery_cols">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_gallery_width">갤러리 이미지 폭<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('갤러리 형식의 게시판 목록에서 썸네일 이미지의 폭을 설정하는 값') ?>
                <input type="text" name="bo_gallery_width" value="<?php echo $board['bo_gallery_width'] ?>" id="bo_gallery_width" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_gallery_width" value="1" id="chk_grp_gallery_width">
                <label for="chk_grp_gallery_width">그룹적용</label>
                <input type="checkbox" name="chk_all_gallery_width" value="1" id="chk_all_gallery_width">
                <label for="chk_all_gallery_width">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_gallery_height">갤러리 이미지 높이<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('갤러리 형식의 게시판 목록에서 썸네일 이미지의 높이를 설정하는 값') ?>
                <input type="text" name="bo_gallery_height" value="<?php echo $board['bo_gallery_height'] ?>" id="bo_gallery_height" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_gallery_height" value="1" id="chk_grp_gallery_height">
                <label for="chk_grp_gallery_height">그룹적용</label>
                <input type="checkbox" name="chk_all_gallery_height" value="1" id="chk_all_gallery_height">
                <label for="chk_all_gallery_height">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_mobile_gallery_width">모바일<br>갤러리 이미지 폭<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('모바일로 접속시 갤러리 형식의 게시판 목록에서 썸네일 이미지의 폭을 설정하는 값') ?>
                <input type="text" name="bo_mobile_gallery_width" value="<?php echo $board['bo_mobile_gallery_width'] ?>" id="bo_mobile_gallery_width" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_mobile_gallery_width" value="1" id="chk_grp_mobile_gallery_width">
                <label for="chk_grp_mobile_gallery_width">그룹적용</label>
                <input type="checkbox" name="chk_all_mobile_gallery_width" value="1" id="chk_all_mobile_gallery_width">
                <label for="chk_all_mobile_gallery_width">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_mobile_gallery_height">모바일<br>갤러리 이미지 높이<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('모바일로 접속시 갤러리 형식의 게시판 목록에서 썸네일 이미지의 높이를 설정하는 값') ?>
                <input type="text" name="bo_mobile_gallery_height" value="<?php echo $board['bo_mobile_gallery_height'] ?>" id="bo_mobile_gallery_height" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_mobile_gallery_height" value="1" id="chk_grp_mobile_gallery_height">
                <label for="chk_grp_mobile_gallery_height">그룹적용</label>
                <input type="checkbox" name="chk_all_mobile_gallery_height" value="1" id="chk_all_mobile_gallery_height">
                <label for="chk_all_mobile_gallery_height">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_table_width">게시판 폭<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('100 이하는 %') ?>
                <input type="text" name="bo_table_width" value="<?php echo $board['bo_table_width'] ?>" id="bo_table_width" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_table_width" value="1" id="chk_grp_table_width">
                <label for="chk_grp_table_width">그룹적용</label>
                <input type="checkbox" name="chk_all_table_width" value="1" id="chk_all_table_width">
                <label for="chk_all_table_width">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_image_width">이미지 폭 크기<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('게시판에서 출력되는 이미지의 폭 크기') ?>
                <input type="text" name="bo_image_width" value="<?php echo $board['bo_image_width'] ?>" id="bo_image_width" required class="required numeric frm_input" size="4"> 픽셀
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_image_width" value="1" id="chk_grp_image_width">
                <label for="chk_grp_image_width">그룹적용</label>
                <input type="checkbox" name="chk_all_image_width" value="1" id="chk_all_image_width">
                <label for="chk_all_image_width">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_new">새글 아이콘<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('글 입력후 new 이미지를 출력하는 시간. 0을 입력하시면 아이콘을 출력하지 않습니다.') ?>
                <input type="text" name="bo_new" value="<?php echo $board['bo_new'] ?>" id="bo_new" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_new" value="1" id="chk_grp_new">
                <label for="chk_grp_new">그룹적용</label>
                <input type="checkbox" name="chk_all_new" value="1" id="chk_all_new">
                <label for="chk_all_new">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_hot">인기글 아이콘<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php echo help('조회수가 설정값 이상이면 hot 이미지 출력. 0을 입력하시면 아이콘을 출력하지 않습니다.') ?>
                <input type="text" name="bo_hot" value="<?php echo $board['bo_hot'] ?>" id="bo_hot" required class="required numeric frm_input" size="4">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_hot" value="1" id="chk_grp_hot">
                <label for="chk_grp_hot">그룹적용</label>
                <input type="checkbox" name="chk_all_hot" value="1" id="chk_all_hot">
                <label for="chk_all_hot">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_reply_order">답변 달기</label></th>
            <td>
                <select id="bo_reply_order" name="bo_reply_order">
                    <option value="1"<?php echo get_selected($board['bo_reply_order'], 1, true); ?>>나중에 쓴 답변 아래로 달기 (기본)
                    <option value="0"<?php echo get_selected($board['bo_reply_order'], 0); ?>>나중에 쓴 답변 위로 달기
                </select>
            </td>
            <td class="td_grpset">
                <input type="checkbox" id="chk_grp_reply_order" name="chk_grp_reply_order" value="1">
                <label for="chk_grp_reply_order">그룹적용</label>
                <input type="checkbox" id="chk_all_reply_order" name="chk_all_reply_order" value="1">
                <label for="chk_all_reply_order">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_sort_field">리스트 정렬 필드</label></th>
            <td>
                <?php echo help('리스트에서 기본으로 정렬에 사용할 필드를 선택합니다. "기본"으로 사용하지 않으시는 경우 속도가 느려질 수 있습니다.') ?>
                <select id="bo_sort_field" name="bo_sort_field">
                    <option value="" <?php echo get_selected($board['bo_sort_field'], ""); ?>>wr_num, wr_reply : 기본</option>
                    <option value="wr_datetime asc" <?php echo get_selected($board['bo_sort_field'], "wr_datetime asc"); ?>>wr_datetime asc : 날짜 이전것 부터</option>
                    <option value="wr_datetime desc" <?php echo get_selected($board['bo_sort_field'], "wr_datetime desc"); ?>>wr_datetime desc : 날짜 최근것 부터</option>
                    <option value="wr_hit asc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_hit asc, wr_num, wr_reply"); ?>>wr_hit asc : 조회수 낮은것 부터</option>
                    <option value="wr_hit desc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_hit desc, wr_num, wr_reply"); ?>>wr_hit desc : 조회수 높은것 부터</option>
                    <option value="wr_last asc" <?php echo get_selected($board['bo_sort_field'], "wr_last asc"); ?>>wr_last asc : 최근글 이전것 부터</option>
                    <option value="wr_last desc" <?php echo get_selected($board['bo_sort_field'], "wr_last desc"); ?>>wr_last desc : 최근글 최근것 부터</option>
                    <option value="wr_comment asc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_comment asc, wr_num, wr_reply"); ?>>wr_comment asc : 댓글수 낮은것 부터</option>
                    <option value="wr_comment desc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_comment desc, wr_num, wr_reply"); ?>>wr_comment desc : 댓글수 높은것 부터</option>
                    <option value="wr_good asc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_good asc, wr_num, wr_reply"); ?>>wr_good asc : 추천수 낮은것 부터</option>
                    <option value="wr_good desc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_good desc, wr_num, wr_reply"); ?>>wr_good desc : 추천수 높은것 부터</option>
                    <option value="wr_nogood asc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_nogood asc, wr_num, wr_reply"); ?>>wr_nogood asc : 비추천수 낮은것 부터</option>
                    <option value="wr_nogood desc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_nogood desc, wr_num, wr_reply"); ?>>wr_nogood desc : 비추천수 높은것 부터</option>
                    <option value="wr_subject asc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_subject asc, wr_num, wr_reply"); ?>>wr_subject asc : 제목 오름차순</option>
                    <option value="wr_subject desc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_subject desc, wr_num, wr_reply"); ?>>wr_subject desc : 제목 내림차순</option>
                    <option value="wr_name asc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_name asc, wr_num, wr_reply"); ?>>wr_name asc : 글쓴이 오름차순</option>
                    <option value="wr_name desc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "wr_name desc, wr_num, wr_reply"); ?>>wr_name desc : 글쓴이 내림차순</option>
                    <option value="ca_name asc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "ca_name asc, wr_num, wr_reply"); ?>>ca_name asc : 분류명 오름차순</option>
                    <option value="ca_name desc, wr_num, wr_reply" <?php echo get_selected($board['bo_sort_field'], "ca_name desc, wr_num, wr_reply"); ?>>ca_name desc : 분류명 내림차순</option>
                </select>
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_sort_field" value="1" id="chk_grp_sort_field">
                <label for="chk_grp_sort_field">그룹적용</label>
                <input type="checkbox" name="chk_all_sort_field" value="1" id="chk_all_sort_field">
                <label for="chk_all_sort_field">전체적용</label>
            </td>
        </tbody>
        </table>
    </div>
    <button type="button" class="get_theme_galc btn btn_02" >테마 이미지설정 가져오기</button>

</section>


<section id="anc_bo_point">
    <h2 class="h2_frm">게시판 포인트 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 포인트 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_3">
        </colgroup>
        <tbody>
        <tr>
            <th scope="row"><label for="chk_grp_point">기본값으로 설정</label></th>
            <td colspan="2">
                <?php echo help('환경설정에 입력된 포인트로 설정') ?>
                <input type="checkbox" name="chk_grp_point" id="chk_grp_point" onclick="set_point(this.form)">
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_read_point">글읽기 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_read_point" value="<?php echo $board['bo_read_point'] ?>" id="bo_read_point" required class="required frm_input" size="5">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_read_point" value="1" id="chk_grp_read_point">
                <label for="chk_grp_read_point">그룹적용</label>
                <input type="checkbox" name="chk_all_read_point" value="1" id="chk_all_read_point">
                <label for="chk_all_read_point">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_write_point">글쓰기 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_write_point" value="<?php echo $board['bo_write_point'] ?>" id="bo_write_point" required class="required frm_input" size="5">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_write_point" value="1" id="chk_grp_write_point">
                <label for="chk_grp_write_point">그룹적용</label>
                <input type="checkbox" name="chk_all_write_point" value="1" id="chk_all_write_point">
                <label for="chk_all_write_point">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_comment_point">댓글쓰기 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_comment_point" value="<?php echo $board['bo_comment_point'] ?>" id="bo_comment_point" required class="required frm_input" size="5">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_comment_point" value="1" id="chk_grp_comment_point">
                <label for="chk_grp_comment_point">그룹적용</label>
                <input type="checkbox" name="chk_all_comment_point" value="1" id="chk_all_comment_point">
                <label for="chk_all_comment_point">전체적용</label>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="bo_download_point">다운로드 포인트<strong class="sound_only">필수</strong></label></th>
            <td>
                <input type="text" name="bo_download_point" value="<?php echo $board['bo_download_point'] ?>" id="bo_download_point" required class="required frm_input" size="5">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_download_point" value="1" id="chk_grp_download_point">
                <label for="chk_grp_download_point">그룹적용</label>
                <input type="checkbox" name="chk_all_download_point" value="1" id="chk_all_download_point">
                <label for="chk_all_download_point">전체적용</label>
            </td>
        </tr>
        </tbody>
        </table>
    </div>
</section>

<section id="anc_bo_extra">
    <h2 class="h2_frm">게시판 여분필드 설정</h2>
    <?php echo $pg_anchor ?>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>게시판 여분필드 설정</caption>
        <colgroup>
            <col class="grid_4">
            <col>
            <col class="grid_3">
        </colgroup>
        <tbody>
        <?php for ($i=1; $i<=10; $i++) { ?>
        <tr>
            <th scope="row">여분필드<?php echo $i ?></th>
            <td class="td_extra">
                <label for="bo_<?php echo $i ?>_subj">여분필드 <?php echo $i ?> 제목</label>
                <input type="text" name="bo_<?php echo $i ?>_subj" id="bo_<?php echo $i ?>_subj" value="<?php echo get_text($board['bo_'.$i.'_subj']) ?>" class="frm_input">
                <label for="bo_<?php echo $i ?>">여분필드 <?php echo $i ?> 값</label>
                <input type="text" name="bo_<?php echo $i ?>" value="<?php echo get_text($board['bo_'.$i]) ?>" id="bo_<?php echo $i ?>" class="frm_input">
            </td>
            <td class="td_grpset">
                <input type="checkbox" name="chk_grp_<?php echo $i ?>" value="1" id="chk_grp_<?php echo $i ?>">
                <label for="chk_grp_<?php echo $i ?>">그룹적용</label>
                <input type="checkbox" name="chk_all_<?php echo $i ?>" value="1" id="chk_all_<?php echo $i ?>">
                <label for="chk_all_<?php echo $i ?>">전체적용</label>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        </table>
    </div>
</section>


<div class="btn_fixed_top">
    <?php if( $bo_table && $w ){ ?>
        <a href="./board_copy.php?bo_table=<?php echo $board['bo_table']; ?>" id="board_copy" target="win_board_copy" class=" btn_02 btn">게시판복사</a>
        <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=<?php echo $board['bo_table']; ?>" class=" btn_02 btn">게시판 바로가기</a>
        <a href="./board_thumbnail_delete.php?bo_table=<?php echo $board['bo_table'].'&amp;'.$qstr;?>" onclick="return delete_confirm2('게시판 썸네일 파일을 삭제하시겠습니까?');" class="btn_02 btn">게시판 썸네일 삭제</a>
    <?php } ?>
    <input type="submit" value="확인" class="btn_submi btn btn_01" accesskey="s">
</div>

</form>

<script>
$(function(){
    $("#board_copy").click(function(){
        window.open(this.href, "win_board_copy", "left=10,top=10,width=500,height=400");
        return false;
    });

    $(".get_theme_galc").on("click", function() {
        if(!confirm("현재 테마의 게시판 이미지 설정을 적용하시겠습니까?"))
            return false;

        $.ajax({
            type: "POST",
            url: "./theme_config_load.php",
            cache: false,
            async: false,
            data: { type: "board" },
            dataType: "json",
            success: function(data) {
                if(data.error) {
                    alert(data.error);
                    return false;
                }

                var field = Array('bo_gallery_cols', 'bo_gallery_width', 'bo_gallery_height', 'bo_mobile_gallery_width', 'bo_mobile_gallery_height', 'bo_image_width');
                var count = field.length;
                var key;

                for(i=0; i<count; i++) {
                    key = field[i];

                    if(data[key] != undefined && data[key] != "")
                        $("input[name="+key+"]").val(data[key]);
                }
            }
        });
    });
});

function board_copy(bo_table) {
    window.open("./board_copy.php?bo_table="+bo_table, "BoardCopy", "left=10,top=10,width=500,height=200");
}

function set_point(f) {
    if (f.chk_grp_point.checked) {
        f.bo_read_point.value = "<?php echo $config['cf_read_point'] ?>";
        f.bo_write_point.value = "<?php echo $config['cf_write_point'] ?>";
        f.bo_comment_point.value = "<?php echo $config['cf_comment_point'] ?>";
        f.bo_download_point.value = "<?php echo $config['cf_download_point'] ?>";
    } else {
        f.bo_read_point.value     = f.bo_read_point.defaultValue;
        f.bo_write_point.value    = f.bo_write_point.defaultValue;
        f.bo_comment_point.value  = f.bo_comment_point.defaultValue;
        f.bo_download_point.value = f.bo_download_point.defaultValue;
    }
}

function fboardform_submit(f)
{
    <?php echo get_editor_js("bo_content_head"); ?>
    <?php echo get_editor_js("bo_content_tail"); ?>
    <?php echo get_editor_js("bo_mobile_content_head"); ?>
    <?php echo get_editor_js("bo_mobile_content_tail"); ?>

    if (parseInt(f.bo_count_modify.value) < 0) {
        alert("원글 수정 불가 댓글수는 0 이상 입력하셔야 합니다.");
        f.bo_count_modify.focus();
        return false;
    }

    if (parseInt(f.bo_count_delete.value) < 1) {
        alert("원글 삭제 불가 댓글수는 1 이상 입력하셔야 합니다.");
        f.bo_count_delete.focus();
        return false;
    }

    return true;
}
</script>

<?php
include_once ('./admin.tail.php');
?>

<?
$sub_menu = "300100";
include_once('./_common.php');
include_once(G4_CKEDITOR_PATH.'/ckeditor.lib.php');

auth_check($auth[$sub_menu], 'w');

$sql = " select count(*) as cnt from {$g4['group_table']} ";
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
    sql_query(" ALTER TABLE  `{$g4['board_table']}` ADD  `bo_device` ENUM(  'both',  'pc',  'mobile' ) NOT NULL DEFAULT  'both' AFTER  `bo_subject` ", false);
}

if (!isset($board['bo_show_menu'])) {
    sql_query(" ALTER TABLE `{$g4['board_table']}`  ADD `bo_show_menu` TINYINT NOT NULL DEFAULT '0' AFTER `bo_order_search`,  ADD `bo_order` INT NOT NULL DEFAULT '0' AFTER `bo_show_menu` ", false);
}

if (!isset($board['bo_mobile_skin'])) {
    sql_query(" ALTER TABLE `{$g4['board_table']}`  ADD `bo_mobile_skin` VARCHAR(255) NOT NULL DEFAULT '' AFTER `bo_skin` ", false);
}

if (!isset($board['bo_gallery_width'])) {
    sql_query(" ALTER TABLE `{$g4['board_table']}`  ADD `bo_gallery_width` INT NOT NULL AFTER `bo_gallery_cols`,  ADD `bo_gallery_height` INT NOT NULL DEFAULT '0' AFTER `bo_gallery_width`,  ADD `bo_mobile_gallery_cols` INT NOT NULL DEFAULT '0' AFTER `bo_gallery_height`,  ADD `bo_mobile_gallery_width` INT NOT NULL DEFAULT '0' AFTER `bo_mobile_gallery_cols`,  ADD `bo_mobile_gallery_height` INT NOT NULL DEFAULT '0' AFTER `bo_mobile_gallery_width` ", false);
}

if ($w == '') {

    $html_title .= ' 생성';

    $bo_table_attr = 'class="required alnum_"';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $board['bo_count_delete'] = 1;
    $board['bo_count_modify'] = 1;
    $board['bo_read_point'] = $config['cf_read_point'];
    $board['bo_write_point'] = $config['cf_write_point'];
    $board['bo_comment_point'] = $config['cf_comment_point'];
    $board['bo_download_point'] = $config['cf_download_point'];

    $board['bo_gallery_cols'] = 4;
    $board['bo_gallery_width'] = 200;
    $board['bo_gallery_height'] = 150;
    $board['bo_mobile_gallery_cols'] = 2;
    $board['bo_mobile_gallery_width'] = 160;
    $board['bo_mobile_gallery_height'] = 120;
    $board['bo_table_width'] = 100;
    $board['bo_page_rows'] = $config['cf_page_rows'];
    $board['bo_subject_len'] = 60;
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
    $board['bo_show_menu'] = true;

} else if ($w == 'u') {

    $html_title .= ' 수정';

    if (!$board['bo_table'])
        alert('존재하지 않은 게시판 입니다.');

    if ($is_admin == 'group') {
        if ($member['mb_id'] != $group['gr_admin'])
            alert('그룹이 틀립니다.');
    }

    $bo_table_attr = 'readonly';

}

if ($is_admin != 'super') {
    $group = get_group($board['gr_id']);
    $is_admin = is_admin($member['mb_id']);
}

$g4['title'] = $html_title;
include_once ('./admin.head.php');

$pg_anchor = "<ul class=\"anchor\">
    <li><a href=\"#frm_basic\">기본 설정</a></li>
    <li><a href=\"#frm_auth\">권한 설정</a></li>
    <li><a href=\"#frm_function\">기능 설정</a></li>
    <li><a href=\"#frm_design\">디자인/양식</a></li>
    <li><a href=\"#frm_point\">포인트 설정</a></li>
    <li><a href=\"#frm_extra\">여분필드</a></li>
</ul>";
?>

<form name="fboardform" id="fboardform" action="./board_form_update.php" onsubmit="return fboardform_submit(this)" method="post" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">

<section id="frm_basic" class="cbox">
    <h2>게시판 기본 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_12">
        <col class="grid_3">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="bo_table">TABLE<?=$sound_only?></label></th>
        <td colspan="2">
            <input type="text" name="bo_table" value="<?=$board['bo_table'] ?>" id="bo_table" required class="required frm_input" maxlength="20" <?=$bo_table_attr?>>
            <? if ($w == '') { ?>
                영문자, 숫자, _ 만 가능 (공백없이 20자 이내)
            <? } else { ?>
                <a href="<?=G4_BBS_URL?>/board.php?bo_table=<?=$board['bo_table']?>" class="btn_frmline">게시판 바로가기</a>
                <a href="./board_list.php" class="btn_frmline">목록으로</a>
            <? } ?>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="gr_id">그룹<strong class="sound_only">필수</strong></label></th>
        <td colspan="2">
            <?=get_group_select('gr_id', $board['gr_id'], 'required');?>
            <!-- <? if ($w=='u') { ?><script>document.write("<a href='javascript:;' onclick=\"location.href='./board_list.php?sfl=a.gr_id&amp;stx='+document.getElementById('gr_id').value;\">동일 그룹 목록</a>");</script><?}?> -->
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_subject">게시판 제목<strong class="sound_only">필수</strong></label></th>
        <td colspan="2">
            <input type="text" name="bo_subject" value="<?=get_text($board['bo_subject'])?>" id="bo_subject" required class="required frm_input" size="80" maxlength="120">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_device">접속기기</label></th>
        <td>
            <?=help("PC 와 모바일 사용을 구분합니다.")?>
            <select id="bo_device" name="bo_device">
                <option value="both"<?=get_selected($board['bo_device'], 'both');?>>PC와 모바일에서 모두 사용</option>
                <option value="pc"<?=get_selected($board['bo_device'], 'pc');?>>PC 전용</option>
                <option value="mobile"<?=get_selected($board['bo_device'], 'mobile');?>>모바일 전용</option>
            </select>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use" value="1" id="chk_grp_use">
            <label for="chk_grp_use">그룹적용</label>
            <input type="checkbox" name="chk_all_use" value="1" id="chk_all_use">
            <label for="chk_all_use">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_category_list">분류</label></th>
        <td>
            <?=help('분류와 분류 사이는 | 로 구분하세요. (예: 질문|답변) 첫자로 #은 입력하지 마세요. (예: #질문|#답변 [X])')?>
            <input type="text" name="bo_category_list" value="<?=get_text($board['bo_category_list'])?>" id="bo_category_list" class="frm_input" size="70">
            <input type="checkbox" name="bo_use_category" value="1" id="bo_use_category" <?=$board['bo_use_category']?'checked':'';?>>
            <label for="bo_use_category">사용</label>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_category_list" value="1" id="chk_grp_category_list">
            <label for="chk_grp_category_list">그룹적용</label>
            <input type="checkbox" name="chk_all_category_list" value="1" id="chk_all_category_list">
            <label for="chk_all_category_list">전체적용</label>
        </td>
    </tr>
    <? if ($w == 'u') { ?>
    <tr>
        <th scope="row"><label for="proc_count">카운트 조정</label></th>
        <td colspan="2">
            <?=help('현재 원글수 : '.number_format($board['bo_count_write']).', 현재 댓글수 : '.number_format($board['bo_count_comment'])."\n".'게시판 목록에서 글의 번호가 맞지 않을 경우에 체크하십시오.')?>
            <input type="checkbox" name="proc_count" value="1" id="proc_count">
        </td>
    </tr>
    <? } ?>
    </tbody>
    </table>
</section>

<section id="frm_auth" class="cbox">
    <h2>게시판 권한 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_12">
        <col class="grid_3">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="bo_admin">게시판 관리자</label></th>
        <td>
            <input type="text" name="bo_admin" value="<?=$board['bo_admin']?>" id="bo_admin" class="frm_input" maxlength="20">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_admin" value="1" id="chk_grp_admin">
            <label for="chk_grp_admin">그룹적용</label>
            <input type="checkbox" name="chk_all_admin" value="1" id="chk_all_admin">
            <label for="chk_all_admin">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_list_level">목록보기 권한</label></th>
        <td>
            <?=help('권한 1은 비회원, 2 이상 회원입니다. 권한은 10 이 가장 높습니다.')?>
            <?=get_member_level_select('bo_list_level', 1, 10, $board['bo_list_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_list_level" value="1" id="chk_grp_list_level">
            <label for="chk_grp_list_level">그룹적용</label>
            <input type="checkbox" name="chk_all_list_level" value="1" id="chk_all_list_level">
            <label for="chk_all_list_level">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_read_level">글읽기 권한</label></th>
        <td>
            <?=get_member_level_select('bo_read_level', 1, 10, $board['bo_read_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_read_level" value="1" id="chk_grp_read_level">
            <label for="chk_grp_read_level">그룹적용</label>
            <input type="checkbox" name="chk_all_read_level" value="1" id="chk_all_read_level">
            <label for="chk_all_read_level">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_write_level">글쓰기 권한</label></th>
        <td>
            <?=get_member_level_select('bo_write_level', 1, 10, $board['bo_write_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_write_level" value="1" id="chk_grp_write_level">
            <label for="chk_grp_write_level">그룹적용</label>
            <input type="checkbox" name="chk_all_write_level" value="1" id="chk_all_write_level">
            <label for="chk_all_write_level">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_reply_level">글답변 권한</label></th>
        <td>
            <?=get_member_level_select('bo_reply_level', 1, 10, $board['bo_reply_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_reply_level" value="1" id="chk_grp_reply_level">
            <label for="chk_grp_reply_level">그룹적용</label>
            <input type="checkbox" name="chk_all_reply_level" value="1" id="chk_all_reply_level">
            <label for="chk_all_reply_level">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_comment_level">댓글쓰기 권한</label></th>
        <td>
            <?=get_member_level_select('bo_comment_level', 1, 10, $board['bo_comment_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_comment_level" value="1" id="chk_grp_comment_level">
            <label for="chk_grp_comment_level">그룹적용</label>
            <input type="checkbox" name="chk_all_comment_level" value="1" id="chk_all_comment_level">
            <label for="chk_all_comment_level">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_link_level">링크 권한</label></th>
        <td>
            <?=get_member_level_select('bo_link_level', 1, 10, $board['bo_link_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_link_level" value="1" id="chk_grp_link_level">
            <label for="chk_grp_link_level">그룹적용</label>
            <input type="checkbox" name="chk_all_link_level" value="1" id="chk_all_link_level">
            <label for="chk_all_link_level">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_upload_level">업로드 권한</label></th>
        <td>
            <?=get_member_level_select('bo_upload_level', 1, 10, $board['bo_upload_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_upload_level" value="1" id="chk_grp_upload_level">
            <label for="chk_grp_upload_level">그룹적용</label>
            <input type="checkbox" name="chk_all_upload_level" value="1" id="chk_all_upload_level">
            <label for="chk_all_upload_level">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_download_level">다운로드 권한</label></th>
        <td>
            <?=get_member_level_select('bo_download_level', 1, 10, $board['bo_download_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_download_level" value="1" id="chk_grp_download_level">
            <label for="chk_grp_download_level">그룹적용</label>
            <input type="checkbox" name="chk_all_download_level" value="1" id="chk_all_download_level">
            <label for="chk_all_download_level">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_html_level">HTML 쓰기 권한</label></th>
        <td>
            <?=get_member_level_select('bo_html_level', 1, 10, $board['bo_html_level']) ?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_html_level" value="1" id="chk_grp_html_level">
            <label for="chk_grp_html_level">그룹적용</label>
            <input type="checkbox" name="chk_all_html_level" value="1" id="chk_all_html_level">
            <label for="chk_all_html_level">전체적용</label>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_function" class="cbox">
    <h2>게시판 기능 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_12">
        <col class="grid_3">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="bo_count_modify">원글 수정 불가<strong class="sound_only">필수</strong></label></th>
        <td>
            댓글 <input type="text" name="bo_count_modify" value="<?=$board['bo_count_modify']?>" id="bo_count_modify" required class="required numeric frm_input" size="3">개 이상 달리면 수정불가
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_count_modify" value="1" id="chk_grp_count_modify">
            <label for="chk_grp_count_modify">그룹적용</label>
            <input type="checkbox" name="chk_all_count_modify" value="1" id="chk_all_count_modify">
            <label for="chk_all_count_modify">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_count_delete">원글 삭제 불가<strong class="sound_only">필수</strong></label></th>
        <td>
            댓글 <input type="text" name="bo_count_delete" value="<?=$board['bo_count_delete']?>" id="bo_count_delete" required class="required numeric frm_input" size="3">개 이상 달리면 삭제불가
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_count_delete" value="1" id="chk_grp_count_delete">
            <label for="chk_grp_count_delete">그룹적용</label>
            <input type="checkbox" name="chk_all_count_delete" value="1" id="chk_all_count_delete">
            <label for="chk_all_count_delete">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_sideview">글쓴이 사이드뷰</label></th>
        <td>
            <input type="checkbox" name="bo_use_sideview" value="1" id="bo_use_sideview" <?=$board['bo_use_sideview']?'checked':'';?>>사용 (글쓴이 클릭시 나오는 레이어 메뉴)
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_sideview" value="1" id="chk_grp_use_sideview">
            <label for="chk_grp_use_sideview">그룹적용</label>
            <input type="checkbox" name="chk_all_use_sideview" value="1" id="chk_all_use_sideview">
            <label for="chk_all_use_sideview">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_secret">비밀글 사용</label></th>
        <td>
            <?=help('"체크박스"는 글작성시 비밀글 체크가 가능합니다. "무조건"은 작성되는 모든글을 비밀글로 작성합니다. (관리자는 체크박스로 출력합니다.) 스킨에 따라 적용되지 않을 수 있습니다.')?>
            <select id="bo_use_secret" name="bo_use_secret">
                <?=option_selected(0, $board['bo_use_secret'], "사용하지 않음");?>
                <?=option_selected(1, $board['bo_use_secret'], "체크박스");?>
                <?=option_selected(2, $board['bo_use_secret'], "무조건");?>
            </select>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_secret" value="1" id="chk_grp_use_secret">
            <label for="chk_grp_use_secret">그룹적용</label>
            <input type="checkbox" name="chk_all_use_secret" value="1" id="chk_all_use_secret">
            <label for="chk_all_use_secret">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_dhtml_editor">DHTML 에디터 사용</label></th>
        <td>
            <?=help('글작성시 내용을 DHTML 에디터 기능으로 사용할 것인지 설정합니다. 스킨에 따라 적용되지 않을 수 있습니다.')?>
            <input type="checkbox" name="bo_use_dhtml_editor" value="1" <?=$board['bo_use_dhtml_editor']?'checked':'';?> id="bo_use_dhtml_editor">
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_dhtml_editor" value="1" id="chk_grp_use_dhtml_editor">
            <label for="chk_grp_use_dhtml_editor">그룹적용</label>
            <input type="checkbox" name="chk_all_use_dhtml_editor" value="1" id="chk_all_use_dhtml_editor">
            <label for="chk_all_use_dhtml_editor">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_rss_view">RSS 보이기 사용</label></th>
        <td>
            <?=help('비회원 글읽기가 가능하고 RSS 보이기 사용에 체크가 되어야만 RSS 지원을 합니다.')?>
            <input type="checkbox" name="bo_use_rss_view" value="1" <?=$board['bo_use_rss_view']?'checked':'';?> id="bo_use_rss_view">
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_rss_view" value="1" id="chk_grp_use_rss_view">
            <label for="chk_grp_use_rss_view">그룹적용</label>
            <input type="checkbox" name="chk_all_use_rss_view" value="1" id="chk_all_use_rss_view">
            <label for="chk_all_use_rss_view">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_good">추천 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_good" value="1" <?=$board['bo_use_good']?'checked':'';?> id="bo_use_good">
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_good" value="1" id="chk_grp_use_good">
            <label for="chk_grp_use_good">그룹적용</label>
            <input type="checkbox" name="chk_all_use_good" value="1" id="chk_all_use_good">
            <label for="chk_all_use_good">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_nogood">비추천 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_nogood" value="1" id="bo_use_nogood" <?=$board['bo_use_nogood']?'checked':'';?>>
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_nogood" value="1" id="chk_grp_use_nogood">
            <label for="chk_grp_use_nogood">그룹적용</label>
            <input type="checkbox" name="chk_all_use_nogood" value="1" id="chk_all_use_nogood">
            <label for="chk_all_use_nogood">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_name">이름(실명) 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_name" value="1" id="bo_use_name" <?=$board['bo_use_name']?'checked':'';?>>
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_name" value="1" id="chk_grp_use_name">
            <label for="chk_grp_use_name">그룹적용</label>

            <input type="checkbox" name="chk_all_use_name" value="1" id="chk_all_use_name">
            <label for="chk_all_use_name">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_signature">서명보이기 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_signature" value="1" id="bo_use_signature" <?=$board['bo_use_signature']?'checked':'';?>>
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_signature" value="1" id="chk_grp_use_signature">
            <label for="chk_grp_use_signature">그룹적용</label>
            <input type="checkbox" name="chk_all_use_signature" value="1" id="chk_all_use_signature">
            <label for="chk_all_use_signature">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_ip_view">IP 보이기 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_ip_view" value="1" id="bo_use_ip_view" <?=$board['bo_use_ip_view']?'checked':'';?>>
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_ip_view" value="1" id="chk_grp_use_ip_view">
            <label for="chk_grp_use_ip_view">그룹적용</label>
            <input type="checkbox" name="chk_all_use_ip_view" value="1" id="chk_all_use_ip_view">
            <label for="chk_all_use_ip_view">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_list_content">목록에서 내용 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_list_content" value="1" id="bo_use_list_content" <?=$board['bo_use_list_content']?'checked':'';?>>
            사용 (사용시 속도 느려질 수 있습니다.)
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_list_content" value="1" id="chk_grp_use_list_content">
            <label for="chk_grp_use_list_content">그룹적용</label>
            <input type="checkbox" name="chk_all_use_list_content" value="1" id="chk_all_use_list_content">
            <label for="chk_all_use_list_content">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_list_view">전체목록보이기 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_list_view" value="1" id="bo_use_list_view" <?=$board['bo_use_list_view']?'checked':'';?>>
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_list_view" value="1" id="chk_grp_use_list_view">
            <label for="chk_grp_use_list_view">그룹적용</label>
            <input type="checkbox" name="chk_all_use_list_view" value="1" id="chk_all_use_list_view">
            <label for="chk_all_use_list_view">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_email">메일발송 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_email" value="1" id="bo_use_email" <?=$board['bo_use_email']?'checked':'';?>>
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_email" value="1" id="chk_grp_use_email">
            <label for="chk_grp_use_email">그룹적용</label>
            <input type="checkbox" name="chk_all_use_email" value="1" id="chk_all_use_email">
            <label for="chk_all_use_email">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_upload_count">파일 업로드 갯수<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('게시물 한건당 업로드 할 수 있는 파일의 최대 개수 (0 이면 제한 없음)')?>
            <input type="text" name="bo_upload_count" value="<?=$board['bo_upload_count']?>" id="bo_upload_count" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_upload_count" value="1" id="chk_grp_upload_count">
            <label for="chk_grp_upload_count">그룹적용</label>
            <input type="checkbox" name="chk_all_upload_count" value="1" id="chk_all_upload_count">
            <label for="chk_all_upload_count">전체적용</label>
        </td>
    </tr>
    <?
    $upload_max_filesize = ini_get('upload_max_filesize');
    if (!preg_match("/([m|M])$/", $upload_max_filesize)) {
        $upload_max_filesize = (int)($upload_max_filesize / 1048576);
    }
    ?>
    <tr>
        <th scope="row"><label for="bo_upload_size">파일 업로드 용량<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('최대 '.ini_get("upload_max_filesize").' 이하 업로드 가능, 1 MB = 1,024,768 bytes')?>
            업로드 파일 한개당 <input type="text" name="bo_upload_size" value="<?=$board['bo_upload_size']?>" id="bo_upload_size" required class="required numeric frm_input"  size="10"> bytes 이하
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_upload_size" value="1" id="chk_grp_upload_size">
            <label for="chk_grp_upload_size">그룹적용</label>
            <input type="checkbox" name="chk_all_upload_size" value="1" id="chk_all_upload_size">
            <label for="chk_all_upload_size">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_file_content">파일 설명 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_file_content" value="1" id="bo_use_file_content" <?=$board['bo_use_file_content']?'checked':'';?>>사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_file_content" value="1" id="chk_grp_use_file_content">
            <label for="chk_grp_use_file_content">그룹적용</label>
            <input type="checkbox" name="chk_all_use_file_content" value="1" id="chk_all_use_file_content">
            <label for="chk_all_use_file_content">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_write_min">최소 글수 제한</label></th>
        <td>
            <?=help('글 입력시 최소 글자수를 설정. 0을 입력하면 검사하지 않음')?>
            <input type="text" name="bo_write_min" value="<?=$board['bo_write_min']?>" id="bo_write_min" class="numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_write_min" value="1" id="chk_grp_write_min">
            <label for="chk_grp_write_min">그룹적용</label>
            <input type="checkbox" name="chk_all_write_min" value="1" id="chk_all_write_min">
            <label for="chk_all_write_min">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_write_max">최대 글수 제한</label></th>
        <td>
            <?=help('글 입력시 최대 글자수를 설정. 0을 입력하면 검사하지 않음')?>
            <input type="text" name="bo_write_max" value="<?=$board['bo_write_max']?>" id="bo_write_max" class="numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_write_max" value="1" id="chk_grp_write_max">
            <label for="chk_grp_write_max">그룹적용</label>
            <input type="checkbox" name="chk_all_write_max" value="1" id="chk_all_write_max">
            <label for="chk_all_write_max">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_comment_min">최소 댓글수 제한</label></th>
        <td>
            <?=help('댓글 입력시 최소 글자수, 최대 글자수를 설정. 0을 입력하면 검사하지 않음')?>
            <input type="text" name="bo_comment_min" value="<?=$board['bo_comment_min']?>" id="bo_comment_min" class="numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_comment_min" value="1" id="chk_grp_comment_min">
            <label for="chk_grp_comment_min">그룹적용</label>
            <input type="checkbox" name="chk_all_comment_min" value="1" id="chk_all_comment_min">
            <label for="chk_all_comment_min">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_comment_max">최대 댓글수 제한</label></th>
        <td>
            <?=help('댓글 입력시 최소 글자수, 최대 글자수를 설정. 0을 입력하면 검사하지 않음')?>
            <input type="text" name="bo_comment_max" value="<?=$board['bo_comment_max']?>" id="bo_comment_max" class="numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_comment_max" value="1" id="chk_grp_comment_max">
            <label for="chk_grp_comment_max">그룹적용</label>
            <input type="checkbox" name="chk_all_comment_max" value="1" id="chk_all_comment_max">
            <label for="chk_all_comment_max">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_use_search">전체 검색 사용</label></th>
        <td>
            <input type="checkbox" name="bo_use_search" value="1" id="bo_use_search" <?=$board['bo_use_search']?'checked':'';?>>
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_use_search" value="1" id="chk_grp_use_search">
            <label for="chk_grp_use_search">그룹적용</label>
            <input type="checkbox" name="chk_all_use_search" value="1" id="chk_all_use_search">
            <label for="chk_all_use_search">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_show_menu">메뉴보이기</label></th>
        <td>
            <?=help("사용에 체크하시면 게시판 제목을 메뉴에 출력합니다.")?>
            <input type="checkbox" name="bo_show_menu" value="1" id="bo_show_menu" <?=$board['bo_show_menu']?'checked':'';?>>
            사용
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_show_menu" value="1" id="chk_grp_show_menu">
            <label for="chk_grp_show_menu">그룹적용</label>
            <input type="checkbox" name="chk_all_show_menu" value="1" id="chk_all_show_menu">
            <label for="chk_all_show_menu">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_order">출력 순서</label></th>
        <td>
            <?=help('숫자가 낮은 게시판 부터 메뉴나 검색시 우선 출력합니다.')?>
            <input type="text" name="bo_order" value="<?=$board['bo_order']?>" id="bo_order" class="frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_order" value="1" id="chk_grp_order">
            <label for="chk_grp_order">그룹적용</label>
            <input type="checkbox" name="chk_all_order" value="1" id="chk_all_order">
            <label for="chk_all_order">전체적용</label>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_design" class="cbox">
    <h2>게시판 디자인/양식</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_12">
        <col class="grid_3">
    </colgroup>
    <tbody>
        <tr>
        <th scope="row"><label for="bo_skin">스킨 디렉토리<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=get_skin_select('board', 'bo_skin', 'bo_skin', $board['bo_skin'], 'required');?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_skin" value="1" id="chk_grp_skin">
            <label for="chk_grp_skin">그룹적용</label>
            <input type="checkbox" name="chk_all_skin" value="1" id="chk_all_skin">
            <label for="chk_all_skin">전체적용</label>
        </td>
    </tr>
    <tbody>
        <tr>
        <th scope="row"><label for="bo_mobile_skin">모바일<br>스킨 디렉토리<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=get_mobile_skin_select('board', 'bo_mobile_skin', 'bo_mobile_skin', $board['bo_mobile_skin'], 'required');?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_mobile_skin" value="1" id="chk_grp_mobile_skin">
            <label for="chk_grp_mobile_skin">그룹적용</label>
            <input type="checkbox" name="chk_all_mobile_skin" value="1" id="chk_all_mobile_skin">
            <label for="chk_all_mobile_skin">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_include_head">상단 파일 경로</label></th>
        <td>
            <input type="text" name="bo_include_head" value="<?=$board['bo_include_head']?>" id="bo_include_head" class="frm_input" size="50">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_include_head" value="1" id="chk_grp_include_head">
            <label for="chk_grp_include_head">그룹적용</label>
            <input type="checkbox" name="chk_all_include_head" value="1" id="chk_all_include_head">
            <label for="chk_all_include_head">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_include_tail">하단 파일 경로</label></th>
        <td>
            <input type="text" name="bo_include_tail" value="<?=$board['bo_include_tail']?>" id="bo_include_tail" class="frm_input" size="50">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_include_tail" value="1" id="chk_grp_include_tail">
            <label for="chk_grp_include_tail">그룹적용</label>
            <input type="checkbox" name="chk_all_include_tail" value="1" id="chk_all_include_tail">
            <label for="chk_all_include_tail">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_content_head">상단 내용</label></th>
        <td>
            <?=editor_html("bo_content_head", $board['bo_content_head']);?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_content_head" value="1" id="chk_grp_content_head">
            <label for="chk_grp_content_head">그룹적용</label>
            <input type="checkbox" name="chk_all_content_head" value="1" id="chk_all_content_head">
            <label for="chk_all_content_head">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_content_tail">하단 내용</label></th>
        <td>
            <?=editor_html("bo_content_tail", $board['bo_content_tail']);?>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_content_tail" value="1" id="chk_grp_content_tail">
            <label for="chk_grp_content_tail">그룹적용</label>
            <input type="checkbox" name="chk_all_content_tail" value="1" id="chk_all_content_tail">
            <label for="chk_all_content_tail">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_insert_content">글쓰기 기본 내용</label></th>
        <td>
            <textarea id="bo_insert_content" name="bo_insert_content" rows="5"><?=$board['bo_insert_content'] ?></textarea>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_insert_content" value="1" id="chk_grp_insert_content">
            <label for="chk_grp_insert_content">그룹적용</label>
            <input type="checkbox" name="chk_all_insert_content" value="1" id="chk_all_insert_content">
            <label for="chk_all_insert_content">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_subject_len">제목 길이<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('목록에서의 제목 글자수. 잘리는 글은 … 로 표시')?>
            <input type="text" name="bo_subject_len" value="<?=$board['bo_subject_len']?>" id="bo_subject_len" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_subject_len" value="1" id="chk_grp_subject_len">
            <label for="chk_grp_subject_len">그룹적용</label>
            <input type="checkbox" name="chk_all_subject_len" value="1" id="chk_all_subject_len">
            <label for="chk_all_subject_len">전체적용</label>
        </td>
    </tr>
        <tr>
        <th scope="row"><label for="bo_page_rows">페이지당 목록 수<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="bo_page_rows" value="<?=$board['bo_page_rows']?>" id="bo_page_rows" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_page_rows" value="1" id="chk_grp_page_rows">
            <label for="chk_grp_page_rows">그룹적용</label>
            <input type="checkbox" name="chk_all_page_rows" value="1" id="chk_all_page_rows">
            <label for="chk_all_page_rows">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_gallery_cols">갤러리 이미지 수<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('갤러리 형식의 게시판 목록에서 이미지를 한줄에 몇장씩 보여 줄 것인지를 설정하는 값')?>
            <input type="text" name="bo_gallery_cols" value="<?=$board['bo_gallery_cols']?>" id="bo_gallery_cols" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_gallery_cols" value="1" id="chk_grp_gallery_cols">
            <label for="chk_grp_gallery_cols">그룹적용</label>
            <input type="checkbox" name="chk_all_gallery_cols" value="1" id="chk_all_gallery_cols">
            <label for="chk_all_gallery_cols">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_gallery_width">갤러리 이미지 폭<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('갤러리 형식의 게시판 목록에서 썸네일 이미지의 폭을 설정하는 값')?>
            <input type="text" name="bo_gallery_width" value="<?=$board['bo_gallery_width']?>" id="bo_gallery_width" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_gallery_width" value="1" id="chk_grp_gallery_width">
            <label for="chk_grp_gallery_width">그룹적용</label>
            <input type="checkbox" name="chk_all_gallery_width" value="1" id="chk_all_gallery_width">
            <label for="chk_all_gallery_width">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_gallery_height">갤러리 이미지 높이<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('갤러리 형식의 게시판 목록에서 썸네일 이미지의 높이를 설정하는 값')?>
            <input type="text" name="bo_gallery_height" value="<?=$board['bo_gallery_height']?>" id="bo_gallery_height" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_gallery_height" value="1" id="chk_grp_gallery_height">
            <label for="chk_grp_gallery_height">그룹적용</label>
            <input type="checkbox" name="chk_all_gallery_height" value="1" id="chk_all_gallery_height">
            <label for="chk_all_gallery_height">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_mobile_gallery_cols">모바일<br>갤러리 이미지 수<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('모바일로 접속시 갤러리 형식의 게시판 목록에서 이미지를 한줄에 몇장씩 보여 줄 것인지를 설정하는 값')?>
            <input type="text" name="bo_mobile_gallery_cols" value="<?=$board['bo_mobile_gallery_cols']?>" id="bo_mobile_gallery_cols" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="mobile_chk_grp_gallery_cols" id="mobile_chk_grp_gallery_cols" value="1">
            <label for="mobile_chk_grp_gallery_cols">그룹적용</label>
            <input type="checkbox" name="mobile_chk_all_gallery_cols" id="mobile_chk_all_gallery_cols" value="1">
            <label for="mobile_chk_all_gallery_cols">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_mobile_gallery_width">모바일<br>갤러리 이미지 폭<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('모바일로 접속시 갤러리 형식의 게시판 목록에서 썸네일 이미지의 폭을 설정하는 값')?>
            <input type="text" name="bo_mobile_gallery_width" value="<?=$board['bo_mobile_gallery_width']?>" id="bo_mobile_gallery_width" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="mobile_chk_grp_gallery_width" value="1" id="mobile_chk_grp_gallery_width">
            <label for="mobile_chk_grp_gallery_width">그룹적용</label>
            <input type="checkbox" name="mobile_chk_all_gallery_width" value="1" id="mobile_chk_all_gallery_width">
            <label for="mobile_chk_all_gallery_width">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_mobile_gallery_height">모바일<br>갤러리 이미지 높이<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('모바일로 접속시 갤러리 형식의 게시판 목록에서 썸네일 이미지의 높이를 설정하는 값')?>
            <input type="text" name="bo_mobile_gallery_height" value="<?=$board['bo_mobile_gallery_height']?>" id="bo_mobile_gallery_height" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="mobile_chk_grp_gallery_height" value="1" id="mobile_chk_grp_gallery_height">
            <label for="mobile_chk_grp_gallery_height">그룹적용</label>
            <input type="checkbox" name="mobile_chk_all_gallery_height" value="1" id="mobile_chk_all_gallery_height">
            <label for="mobile_chk_all_gallery_height">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_table_width">게시판 폭<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('100 이하는 %')?>
            <input type="text" name="bo_table_width" value="<?=$board['bo_table_width']?>" id="bo_table_width" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_table_width" value="1" id="chk_grp_table_width">
            <label for="chk_grp_table_width">그룹적용</label>
            <input type="checkbox" name="chk_all_table_width" value="1" id="chk_all_table_width">
            <label for="chk_all_table_width">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_image_width">이미지 폭 크기<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('게시판에서 출력되는 이미지의 폭 크기')?>
            <input type="text" name="bo_image_width" value="<?=$board['bo_image_width']?>" id="bo_image_width" required class="required numeric frm_input" size="4"> 픽셀
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_image_width" value="1" id="chk_grp_image_width">
            <label for="chk_grp_image_width">그룹적용</label>
            <input type="checkbox" name="chk_all_image_width" value="1" id="chk_all_image_width">
            <label for="chk_all_image_width">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_new">새글 아이콘<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('글 입력후 new 이미지를 출력하는 시간')?>
            <input type="text" name="bo_new" value="<?=$board['bo_new']?>" id="bo_new" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_new" value="1" id="chk_grp_new">
            <label for="chk_grp_new">그룹적용</label>
            <input type="checkbox" name="chk_all_new" value="1" id="chk_all_new">
            <label for="chk_all_new">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_hot">인기글 아이콘<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('조회수가 설정값 이상이면 hot 이미지 출력')?>
            <input type="text" name="bo_hot" value="<?=$board['bo_hot']?>" id="bo_hot" required class="required numeric frm_input" size="4">
        </td>
        <td class="group_setting">
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
                <option value="1"<?=get_selected($board['bo_reply_order'], 1, true);?>>나중에 쓴 답변 아래로 달기 (기본)
                <option value="0"<?=get_selected($board['bo_reply_order'], 0);?>>나중에 쓴 답변 위로 달기
            </select>
        </td>
        <td class="group_setting">
            <input type="checkbox" id="chk_grp_reply_order" name="chk_grp_reply_order" value="1">
            <label for="chk_grp_reply_order">그룹적용</label>
            <input type="checkbox" id="chk_all_reply_order" name="chk_all_reply_order" value="1">
            <label for="chk_all_reply_order">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_sort_field">리스트 정렬 필드</label></th>
        <td>
            <?=help('리스트에서 기본으로 정렬에 사용할 필드를 선택합니다. "기본"으로 사용하지 않으시는 경우 속도가 느려질 수 있습니다.')?>
            <select id="bo_sort_field" name="bo_sort_field">
                <option value="" <?=get_selected($board['bo_sort_field'], "");?>>wr_num, wr_reply : 기본</option>
                <option value="wr_datetime asc" <?=get_selected($board['bo_sort_field'], "wr_datetime asc");?>>wr_datetime asc : 날짜 이전것 부터</option>
                <option value="wr_datetime desc" <?=get_selected($board['bo_sort_field'], "wr_datetime desc");?>>wr_datetime desc : 날짜 최근것 부터</option>
                <option value="wr_hit asc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_hit asc, wr_num, wr_reply");?>>wr_hit asc : 조회수 낮은것 부터</option>
                <option value="wr_hit desc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_hit desc, wr_num, wr_reply");?>>wr_hit desc : 조회수 높은것 부터</option>
                <option value="wr_last asc" <?=get_selected($board['bo_sort_field'], "wr_last asc");?>>wr_last asc : 최근글 이전것 부터</option>
                <option value="wr_last desc" <?=get_selected($board['bo_sort_field'], "wr_last desc");?>>wr_last desc : 최근글 최근것 부터</option>
                <option value="wr_comment asc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_comment asc, wr_num, wr_reply");?>>wr_comment asc : 댓글수 낮은것 부터</option>
                <option value="wr_comment desc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_comment desc, wr_num, wr_reply");?>>wr_comment desc : 댓글수 높은것 부터</option>
                <option value="wr_good asc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_good asc, wr_num, wr_reply");?>>wr_good asc : 추천수 낮은것 부터</option>
                <option value="wr_good desc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_good desc, wr_num, wr_reply");?>>wr_good desc : 추천수 높은것 부터</option>
                <option value="wr_nogood asc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_nogood asc, wr_num, wr_reply");?>>wr_nogood asc : 비추천수 낮은것 부터</option>
                <option value="wr_nogood desc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_nogood desc, wr_num, wr_reply");?>>wr_nogood desc : 비추천수 높은것 부터</option>
                <option value="wr_subject asc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_subject asc, wr_num, wr_reply");?>>wr_subject asc : 제목 내림차순</option>
                <option value="wr_subject desc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_subject desc, wr_num, wr_reply");?>>wr_subject desc : 제목 오름차순</option>
                <option value="wr_name asc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_name asc, wr_num, wr_reply");?>>wr_name asc : 글쓴이 내림차순</option>
                <option value="wr_name desc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "wr_name desc, wr_num, wr_reply");?>>wr_name desc : 글쓴이 오름차순</option>
                <option value="ca_name asc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "ca_name asc, wr_num, wr_reply");?>>ca_name asc : 분류명 내림차순</option>
                <option value="ca_name desc, wr_num, wr_reply" <?=get_selected($board['bo_sort_field'], "ca_name desc, wr_num, wr_reply");?>>ca_name desc : 분류명 오름차순</option>
            </select>
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_sort_field" value="1" id="chk_grp_sort_field">
            <label for="chk_grp_sort_field">그룹적용</label>
            <input type="checkbox" name="chk_all_sort_field" value="1" id="chk_all_sort_field">
            <label for="chk_all_sort_field">전체적용</label>
        </td>
    </tbody>
    </table>
</section>

<section id="frm_point" class="cbox">
    <h2>게시판 포인트 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_12">
        <col class="grid_3">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="chk_grp_point">기본값으로 설정</label></th>
        <td colspan="2">
            <?=help('환경설정에 입력된 포인트로 설정')?>
            <input type="checkbox" name="chk_grp_point" id="chk_grp_point" onclick="set_point(this.form)">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_read_point">글읽기 포인트<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="bo_read_point" value="<?=$board['bo_read_point']?>" id="bo_read_point" required class="required frm_input" size="5">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_read_point" value="1" id="chk_grp_read_point">
            <label for="chk_grp_read_point">그룹적용</label>
            <input type="checkbox" name="chk_all_read_point" value="1" id="chk_all_read_point">
            <label for="chk_all_read_point">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_write_point">글쓰기 포인트<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="bo_write_point" value="<?=$board['bo_write_point']?>" id="bo_write_point" required class="required frm_input" size="5">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_write_point" value="1" id="chk_grp_write_point">
            <label for="chk_grp_write_point">그룹적용</label>
            <input type="checkbox" name="chk_all_write_point" value="1" id="chk_all_write_point">
            <label for="chk_all_write_point">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_comment_point">댓글쓰기 포인트<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="bo_comment_point" value="<?=$board['bo_comment_point']?>" id="bo_comment_point" required class="required frm_input" size="5">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_comment_point" value="1" id="chk_grp_comment_point">
            <label for="chk_grp_comment_point">그룹적용</label>
            <input type="checkbox" name="chk_all_comment_point" value="1" id="chk_all_comment_point">
            <label for="chk_all_comment_point">전체적용</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="bo_download_point">다운로드 포인트<strong class="sound_only">필수</strong></label></th>
        <td>
            <input type="text" name="bo_download_point" value="<?=$board['bo_download_point']?>" id="bo_download_point" required class="required frm_input" size="5">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_download_point" value="1" id="chk_grp_download_point">
            <label for="chk_grp_download_point">그룹적용</label>
            <input type="checkbox" name="chk_all_download_point" value="1" id="chk_all_download_point">
            <label for="chk_all_download_point">전체적용</label>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_extra" class="cbox">
    <h2>게시판 여분필드 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_12">
        <col class="grid_3">
    </colgroup>
    <tbody>
    <? for ($i=1; $i<=10; $i++) { ?>
    <tr>
        <th scope="row">여분필드<?=$i?></th>
        <td>
            <label for="bo_<?=$i?>_subj">여분필드 <?=$i?> 제목</label>
            <input type="text" name="bo_<?=$i?>_subj" id="bo_<?=$i?>_subj" value="<?=get_text($board['bo_'.$i.'_subj'])?>" class="frm_input">
            <label for="bo_<?=$i?>">여분필드 <?=$i?> 값</label>
            <input type="text" name="bo_<?=$i?>" value="<?=get_text($board['bo_'.$i])?>" id="bo_<?=$i?>" class="frm_input">
        </td>
        <td class="group_setting">
            <input type="checkbox" name="chk_grp_<?=$i?>" value="1" id="chk_grp_<?=$i?>">
            <label for="chk_grp_<?=$i?>">그룹적용</label>
            <input type="checkbox" name="chk_all_<?=$i?>" value="1" id="chk_all_<?=$i?>">
            <label for="chk_all_<?=$i?>">전체적용</label>
        </td>
    </tr>
    <? } ?>
    </tbody>
    </table>
</section>

<fieldset id="admin_confirm">
    <legend>XSS 혹은 CSRF 방지</legend>
    <p>관리자 권한을 탈취당하는 경우를 대비하여 패스워드를 다시 한번 확인합니다.</p>
    <label for="admin_password">관리자 패스워드<strong class="sound_only">필수</strong></label>
    <input type="password" name="admin_password" id="admin_password" required class="required frm_input">
</fieldset>

<div class="btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
    <a href="./board_list.php?<?=$qstr?>">목록</a>
    <? if ($w == 'u') { ?><a href="./board_copy.php?bo_table=<?=$bo_table?>" id="board_copy" target="win_board_copy">게시판복사</a><?}?>
</div>

</form>

<script>
$(function(){
    $("#board_copy").click(function(){
        window.open(this.href, "win_board_copy", "left=10,top=10,width=500,height=400");
        return false;
    });
});

function board_copy(bo_table) {
    window.open("./board_copy.php?bo_table="+bo_table, "BoardCopy", "left=10,top=10,width=500,height=200");
}

function set_point(f) {
    if (f.chk_grp_point.checked) {
        f.bo_read_point.value = "<?=$config['cf_read_point']?>";
        f.bo_write_point.value = "<?=$config['cf_write_point']?>";
        f.bo_comment_point.value = "<?=$config['cf_comment_point']?>";
        f.bo_download_point.value = "<?=$config['cf_download_point']?>";
    } else {
        f.bo_read_point.value     = f.bo_read_point.defaultValue;
        f.bo_write_point.value    = f.bo_write_point.defaultValue;
        f.bo_comment_point.value  = f.bo_comment_point.defaultValue;
        f.bo_download_point.value = f.bo_download_point.defaultValue;
    }
}

function fboardform_submit(f)
{
    <?=get_editor_js("bo_content_head");?>
    <?=get_editor_js("bo_content_tail");?>

    if (parseInt(f.bo_count_modify.value) < 1) {
        alert("원글 수정 불가 댓글수는 1 이상 입력하셔야 합니다.");
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

<?
include_once ('./admin.tail.php');
?>

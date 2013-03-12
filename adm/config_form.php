<?
$sub_menu = "100100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

if (!isset($config['cf_include_index'])) {
    sql_query(" ALTER TABLE `{$g4['config_table']}`
                    ADD `cf_include_index` VARCHAR(255) NOT NULL AFTER `cf_admin`,
                    ADD `cf_include_head` VARCHAR(255) NOT NULL AFTER `cf_include_index`,
                    ADD `cf_include_tail` VARCHAR(255) NOT NULL AFTER `cf_include_head`,
                    ADD `cf_add_script` TEXT NOT NULL AFTER `cf_include_tail` ", true);
}

if (!isset($config['cf_mobile_new_skin'])) {
    sql_query(" ALTER TABLE `{$g4['config_table']}`
                    ADD `cf_mobile_new_skin` VARCHAR(255) NOT NULL AFTER `cf_memo_send_point`,
                    ADD `cf_mobile_search_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_new_skin`,
                    ADD `cf_mobile_connect_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_search_skin`,
                    ADD `cf_mobile_member_skin` VARCHAR(255) NOT NULL AFTER `cf_mobile_connect_skin` ", true);
}

if(!isset($config['cf_kcpcert_site_cd'])) {
    sql_query(" ALTER TABLE `{$g4['config_table']}`
                    ADD `cf_kcpcert_site_cd` VARCHAR(255) NOT NULL DEFAULT '' AFTER `cf_memo_send_point` ", TRUE);
}

if(!isset($config['cf_adult_check'])) {
    sql_query(" ALTER TABLE `{$g4['config_table']}`
                    ADD `cf_adult_check` TINYINT(4) NOT NULL DEFAULT '0' AFTER `cf_kcpcert_site_cd` ", TRUE);
}

if(!isset($config['cf_mobile_pages'])) {
    sql_query(" ALTER TABLE `{$g4['config_table']}`
                    ADD `cf_mobile_pages` INT(11) NOT NULL DEFAULT '0' AFTER `cf_write_pages` ", TRUE);
    sql_query(" UPDATE `{$g4['config_table']}` SET cf_mobile_pages = '5' ", TRUE);
}

$g4['title'] = '환경설정';
include_once ('./admin.head.php');

$pg_anchor = "
<ul class=\"anchor\">
    <li><a href=\"#frm_basic\">기본환경</a></li>
    <li><a href=\"#frm_board\">게시판기본</a></li>
    <li><a href=\"#frm_join\">회원가입</a></li>
    <li><a href=\"#frm_mail\">기본메일환경</a></li>
    <li><a href=\"#frm_article_mail\">글작성메일</a></li>
    <li><a href=\"#frm_join_mail\">가입메일</a></li>
    <li><a href=\"#frm_vote_mail\">투표메일</a></li>
    <li><a href=\"#frm_extra\">여분필드</a></li>
</ul>";
?>

<form name="fconfigform" id="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" name="token" value="<?=$token?>" id="token">

<section id="frm_basic" class="cbox">
    <h2>홈페이지 기본환경 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_6">
        <col class="grid_3">
        <col class="grid_6">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cf_title">홈페이지 제목<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="cf_title" value="<?=$config['cf_title']?>" id="cf_title" required class="required frm_input" size="40"></td>
        <th scope="row"><label for="cf_admin">최고관리자<strong class="sound_only">필수</strong></label></th>
        <td><?=get_member_id_select('cf_admin', 10, $config['cf_admin'], 'required')?></td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_include_index">초기화면 파일 경로</label></th>
        <td colspan="3">
            <?=help('입력이 없으면 index.php가 초기화면 파일의 기본 경로로 설정됩니다.')?>
            <input type="text" name="cf_include_index" value="<?=$config['cf_include_index']?>" id="cf_include_index" class="frm_input" size="50">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_include_head">상단 파일 경로</label></th>
        <td colspan="3">
            <?=help('입력이 없으면 head.php가 상단 파일의 기본 경로로 설정됩니다.')?>
            <input type="text" name="cf_include_head" value="<?=$config['cf_include_head']?>" id="cf_include_head" class="frm_input" size="50">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_include_tail">하단 파일 경로</label></th>
        <td colspan="3">
            <?=help('입력이 없으면 tail.php가 상단 파일의 기본 경로로 설정됩니다.')?>
            <input type="text" name="cf_include_tail" value="<?=$config['cf_include_tail']?>" id="cf_include_tail" class="frm_input" size="50">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_add_script">추가 script, css</label></th>
        <td colspan="3">
            <?=help('HTML의 </HEAD> 태그위로 추가될 JavaScript와 css 코드를 설정합니다.')?>
            <textarea name="cf_add_script" id="cf_add_script"><?=$config['cf_add_script']?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_use_point">포인트 사용</label></th>
        <td colspan="3"><input type="checkbox" name="cf_use_point" value="1" id="cf_use_point" <?=$config['cf_use_point']?'checked':'';?>> 사용</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_login_point">로그인시 포인트<strong class="sound_only">필수</strong></label></th>
        <td>
            <?=help('회원에게 하루에 한번만 부여')?>
            <input type="text" name="cf_login_point" value="<?=$config['cf_login_point']?>" id="cf_login_point" required class="required frm_input" size="2"> 점
        </td>
        <th scope="row"><label for="cf_memo_send_point">쪽지보낼시 차감 포인트<strong class="sound_only">필수</strong></label></th>
        <td>
             <?=help('양수로 입력하십시오. 0점은 쪽지 보낼시 포인트를 차감하지 않습니다.')?>
            <input type="text" name="cf_memo_send_point" value="<?=$config['cf_memo_send_point']?>" id="cf_memo_send_point" required class="required frm_input" size="2"> 점
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_cut_name">이름(별명) 표시</label></th>
        <td colspan="3">
            <?=help('영숫자 2글자 = 한글 1글자')?>
            <input type="text" name="cf_cut_name" value="<?=$config['cf_cut_name']?>" id="cf_cut_name" class="frm_input" size="2"> 자리만 표시
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_nick_modify">별명 수정</label></th>
        <td>수정하면 <input type="text" name="cf_nick_modify" value="<?=$config['cf_nick_modify']?>" id="cf_nick_modify" class="frm_input" size="1"> 일 동안 바꿀 수 없음</td>
        <th scope="row"><label for="cf_open_modify">정보공개 수정</label></th>
        <td>수정하면 <input type="text" name="cf_open_modify" value="<?=$config['cf_open_modify']?>" id="cf_open_modify" class="frm_input" size="1"> 일 동안 바꿀 수 없음</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_new_del">최근게시물 삭제</label></th>
        <td>
            <?=help('설정일이 지난 최근게시물 자동 삭제')?>
            <input type="text" name="cf_new_del" value="<?=$config['cf_new_del']?>" id="cf_new_del" class="frm_input" size="2"> 일
        </td>
        <th scope="row"><label for="cf_memo_del">쪽지 삭제</label></th>
        <td>
            <?=help('설정일이 지난 쪽지 자동 삭제')?>
            <input type="text" name="cf_memo_del" value="<?=$config['cf_memo_del']?>" id="cf_memo_del" class="frm_input" size="2"> 일
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_visit_del">접속자로그 삭제</label></th>
        <td>
            <?=help('설정일이 지난 접속자 로그 자동 삭제')?>
            <input type="text" name="cf_visit_del" value="<?=$config['cf_visit_del']?>" id="cf_visit_del" class="frm_input" size="2"> 일
        </td>
        <th scope="row"><label for="cf_popular_del">인기검색어 삭제</label></th>
        <td>
            <?=help('설정일이 지난 인기검색어 자동 삭제')?>
            <input type="text" name="cf_popular_del" value="<?=$config['cf_popular_del']?>" id="cf_popular_del" class="frm_input" size="2"> 일
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_login_minutes">현재 접속자</label></th>
        <td colspan="3">
            <?=help('설정값 이내의 접속자를 현재 접속자로 인정')?>
            <input type="text" name="cf_login_minutes" value="<?=$config['cf_login_minutes']?>" id="cf_login_minutes" class="frm_input" size="2"> 분
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_page_rows">한페이지당 라인수</label></th>
        <td>
            <?=help('목록(리스트) 한페이지당 라인수')?>
            <input type="text" name="cf_page_rows" value="<?=$config['cf_page_rows']?>" id="cf_page_rows" class="frm_input" size="2"> 라인
        </td>
        <th scope="row"><label for="cf_new_rows">최근게시물 라인수</label></th>
        <td colspan="3">
            <?=help('목록 한페이지당 라인수')?>
            <input type="text" name="cf_new_rows" value="<?=$config['cf_new_rows']?>" id="cf_new_rows" class="frm_input" size="2"> 라인
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_write_pages">페이지 표시 수<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="cf_write_pages" value="<?=$config['cf_write_pages']?>" id="cf_write_pages" required class="required numeric frm_input" size="3"> 페이지씩 표시</td>
        <th scope="row"><label for="cf_mobile_pages">모바일 페이지 표시 수<strong class="sound_only">필수</strong></label></th>
        <td colspan="3"><input type="text" name="cf_mobile_pages" value="<?=$config['cf_mobile_pages']?>" id="cf_mobile_pages" required class="required numeric frm_input" size="3"> 페이지씩 표시</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_new_skin">최근게시물 스킨<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="cf_new_skin" id="cf_new_skin" required class="required">
            <?
            $arr = get_skin_dir('new');
            for ($i=0; $i<count($arr); $i++) {
                echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_new_skin'], $arr[$i]).">".$arr[$i]."</option>\n";
            }
            ?>
            </select>
        </td>
        <th scope="row"><label for="cf_mobile_new_skin">모바일<br>최근게시물 스킨<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="cf_mobile_new_skin" id="cf_mobile_new_skin" required class="required">
            <?
            $arr = get_skin_dir('new', G4_MOBILE_PATH.'/'.G4_SKIN_DIR);
            for ($i=0; $i<count($arr); $i++) {
                echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_mobile_new_skin'], $arr[$i]).">".$arr[$i]."</option>\n";
            }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_search_skin">검색 스킨<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="cf_search_skin" id="cf_search_skin" required class="required">
            <?
            $arr = get_skin_dir('search');
            for ($i=0; $i<count($arr); $i++) {
                echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_search_skin'], $arr[$i]).">".$arr[$i]."</option>\n";
            }
            ?>
            </select>
        </td>
        <th scope="row"><label for="cf_mobile_search_skin">모바일 검색 스킨<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="cf_mobile_search_skin" id="cf_mobile_search_skin" required class="required">
            <?
            $arr = get_skin_dir('search', G4_MOBILE_PATH.'/'.G4_SKIN_DIR);
            for ($i=0; $i<count($arr); $i++) {
                echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_mobile_search_skin'], $arr[$i]).">".$arr[$i]."</option>\n";
            }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_connect_skin">접속자 스킨<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="cf_connect_skin" id="cf_connect_skin" required class="required">
            <?
            $arr = get_skin_dir('connect');
            for ($i=0; $i<count($arr); $i++) {
                echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_connect_skin'], $arr[$i]).">".$arr[$i]."</option>\n";
            }
            ?>
            </select>
        </td>
        <th scope="row"><label for="cf_mobile_connect_skin">모바일 접속자 스킨<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="cf_mobile_connect_skin" id="cf_mobile_connect_skin" required class="required">
            <?
            $arr = get_skin_dir('connect', G4_MOBILE_PATH.'/'.G4_SKIN_DIR);
            for ($i=0; $i<count($arr); $i++) {
                echo "<option value=\"".$arr[$i]."\"".get_selected($config['cf_mobile_connect_skin'], $arr[$i]).">".$arr[$i]."</option>\n";
            }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_use_copy_log">복사, 이동시 로그</label></th>
        <td colspan="3">
            <?=help('게시물 아래에 누구로 부터 복사, 이동됨 표시')?>
            <input type="checkbox" name="cf_use_copy_log" value="1" id="cf_use_copy_log" <?=$config['cf_use_copy_log']?'checked':'';?>> 남김
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_possible_ip">접근가능 IP</label></th>
        <td>
            <?=help('입력된 IP의 컴퓨터만 접근할 수 있습니다.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
            <textarea name="cf_possible_ip" id="cf_possible_ip"><?=$config['cf_possible_ip']?> </textarea>
        </td>
        <th scope="row"><label for="cf_intercept_ip">접근차단 IP</label></th>
        <td>
            <?=help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
            <textarea name="cf_intercept_ip" id="cf_intercept_ip"><?=$config['cf_intercept_ip']?> </textarea>
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_board" class="cbox">
    <h2>게시판 기본 설정</h2>
    <?=$pg_anchor?>
    <p>각 게시판 관리에서 개별적으로 설정 가능합니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_6">
        <col class="grid_3">
        <col class="grid_6">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cf_delay_sec">글쓰기 간격<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="cf_delay_sec" value="<?=$config['cf_delay_sec']?>" id="cf_delay_sec" required class="required numeric frm_input" size="3"> 초 지난후 가능</td>
        <th scope="row"><label for="cf_link_target">새창 링크</label></th>
        <td>
            <?=help('글내용중 자동 링크되는 타켓을 지정합니다.')?>
            <select id="cf_link_target" name="cf_link_target">
                <option value="_blank"<?=get_selected($config['cf_link_target'], '_blank')?>>_blank</option>
                <option value="_self"<?=get_selected($config['cf_link_target'], '_self')?>>_self</option>
                <option value="_top"<?=get_selected($config['cf_link_target'], '_top')?>>_top</option>
                <option value="_new"<?=get_selected($config['cf_link_target'], '_new')?>>_new</option>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_read_point">글읽기 포인트<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" name="cf_read_point" value="<?=$config['cf_read_point']?>" id="cf_read_point" required class="required frm_input" size="3"> 점</td>
        <th scope="row"><label for="cf_write_point">글쓰기 포인트</label></th>
        <td><input type="text" name="cf_write_point" value="<?=$config['cf_write_point']?>" id="cf_write_point" required class="required frm_input" size="3"> 점</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_comment_point">댓글쓰기 포인트</label></th>
        <td><input type="text" name="cf_comment_point" value="<?=$config['cf_comment_point']?>" id="cf_comment_point" required class="required frm_input" size="3"> 점</td>
        <th scope="row"><label for="cf_download_point">다운로드 포인트</label></th>
        <td><input type="text" name="cf_download_point" value="<?=$config['cf_download_point']?>" id="cf_download_point" required class="required frm_input" size="3"> 점</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_search_part">검색 단위</label></th>
        <td colspan="3"><input type="text" name="cf_search_part" value="<?=$config['cf_search_part']?>" id="cf_search_part" class="frm_input" size="4"> 건 단위로 검색</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_image_extension">이미지 업로드 확장자</label></th>
        <td colspan="3">
            <?=help('게시판 글작성시 이미지 파일 업로드 가능 확장자. | 로 구분')?>
            <input type="text" name="cf_image_extension" value="<?=$config['cf_image_extension']?>" id="cf_image_extension" class="frm_input" size="70">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_flash_extension">플래쉬 업로드 확장자</label></th>
        <td colspan="3">
            <?=help('게시판 글작성시 플래쉬 파일 업로드 가능 확장자. | 로 구분')?>
            <input type="text" name="cf_flash_extension" value="<?=$config['cf_flash_extension']?>" id="cf_flash_extension" class="frm_input" size="70">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_movie_extension">동영상 업로드 확장자</label></th>
        <td colspan="3">
            <?=help('게시판 글작성시 동영상 파일 업로드 가능 확장자. | 로 구분')?>
            <input type="text" name="cf_movie_extension" value="<?=$config['cf_movie_extension']?>" id="cf_movie_extension" class="frm_input" size="70">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_filter">단어 필터링</label></th>
        <td colspan="3">
            <?=help('입력된 단어가 포함된 내용은 게시할 수 없습니다. 단어와 단어 사이는 ,로 구분합니다.')?>
            <textarea name="cf_filter" id="cf_filter" rows="7"><?=$config['cf_filter']?> </textarea>
         </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_join" class="cbox">
    <h2>회원가입 설정</h2>
    <?=$pg_anchor?>
    <p>회원가입 시 사용할 스킨과 입력 받을 정보 등을 설정할 수 있습니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_6">
        <col class="grid_3">
        <col class="grid_6">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cf_member_skin">회원 스킨<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="cf_member_skin" id="cf_member_skin" required class="required">
            <?
            $arr = get_skin_dir('member');
            for ($i=0; $i<count($arr); $i++) {
                echo '<option value="'.$arr[$i].'"'.get_selected($config['cf_member_skin'], $arr[$i]).'>'.$arr[$i].'</option>'."\n";
            }
            ?>
            </select>
        </td>
        <th scope="row"><label for="cf_mobile_member_skin">모바일<br>회원 스킨<strong class="sound_only">필수</strong></label></th>
        <td>
            <select name="cf_mobile_member_skin" id="cf_mobile_member_skin" required class="required">
            <?
            $arr = get_skin_dir('member', G4_MOBILE_PATH.'/'.G4_SKIN_DIR);
            for ($i=0; $i<count($arr); $i++) {
                echo '<option value="'.$arr[$i].'"'.get_selected($config['cf_member_skin'], $arr[$i]).'>'.$arr[$i].'</option>'."\n";
            }
            ?>
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row">홈페이지 입력</th>
        <td>
            <input type="checkbox" name="cf_use_homepage" value="1" id="cf_use_homepage" <?=$config['cf_use_homepage']?'checked':'';?>> <label for="cf_use_homepage">보이기</label>
            <input type="checkbox" name="cf_req_homepage" value="1" id="cf_req_homepage" <?=$config['cf_req_homepage']?'checked':'';?>> <label for="cf_req_homepage">필수입력</label>
        </td>
        <th scope="row">주소 입력</th>
        <td>
            <input type="checkbox" name="cf_use_addr" value="1" id="cf_use_addr" <?=$config['cf_use_addr']?'checked':'';?>> <label for="cf_use_addr">보이기</label>
            <input type="checkbox" name="cf_req_addr" value="1" id="cf_req_addr" <?=$config['cf_req_addr']?'checked':'';?>> <label for="cf_req_addr">필수입력</label>
        </td>
    </tr>
    <tr>
        <th scope="row">전화번호 입력</th>
        <td>
            <input type="checkbox" name="cf_use_tel" value="1" id="cf_use_tel" <?=$config['cf_use_tel']?'checked':'';?>> <label for="cf_use_tel">보이기</label>
            <input type="checkbox" name="cf_req_tel" value="1" id="cf_req_tel" <?=$config['cf_req_tel']?'checked':'';?>> <label for="cf_req_tel">필수입력</label>
        </td>
        <th scope="row">핸드폰 입력</th>
        <td>
            <input type="checkbox" name="cf_use_hp" value="1" id="cf_use_hp" <?=$config['cf_use_hp']?'checked':'';?>> <label for="cf_use_hp">보이기</label>
            <input type="checkbox" name="cf_req_hp" value="1" id="cf_req_hp" <?=$config['cf_req_hp']?'checked':'';?>> <label for="cf_req_hp">필수입력</label>
        </td>
    </tr>
    <tr>
        <th scope="row">서명 입력</th>
        <td>
            <input type="checkbox" name="cf_use_signature" value="1" id="cf_use_signature" <?=$config['cf_use_signature']?'checked':'';?>> <label for="cf_use_signature">보이기</label>
            <input type="checkbox" name="cf_req_signature" value="1" id="cf_req_signature" <?=$config['cf_req_signature']?'checked':'';?>> <label for="cf_req_signature">필수입력</label>
        </td>
        <th scope="row">자기소개 입력</th>
        <td>
            <input type="checkbox" name="cf_use_profile" value="1" id="cf_use_profile" <?=$config['cf_use_profile']?'checked':'';?>> <label for="cf_use_profile">보이기</label>
            <input type="checkbox" name="cf_req_profile" value="1" id="cf_req_profile" <?=$config['cf_req_profile']?'checked':'';?>> <label for="cf_req_profile">필수입력</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_register_level">회원가입시 권한</label></th>
        <td><?=get_member_level_select('cf_register_level', 1, 9, $config['cf_register_level']) ?></td>
        <th scope="row"><label for="cf_register_point">회원가입시 포인트</label></th>
        <td><input type="text" name="cf_register_point" value="<?=$config['cf_register_point']?>" id="cf_register_point" class="frm_input" size="5"> 점</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_kcpcert_site_cd">KCP 사이트코드</label></th>
        <td><input type="text" name="cf_kcpcert_site_cd" value="<?=$config['cf_kcpcert_site_cd']?>" id="cf_kcpcert_site_cd" class="frm_input" size="10"></td>
        <th scope="row"><label for='cf_adult_check'>성인인증 사용</label></th>
        <td><input type="checkbox" name="cf_adult_check" value="1" id="cf_adult_check" <?=$config['cf_adult_check']?'checked':'';?>> 사용</td>
    </tr>
    <tr>
        <th scope="row" id="th310"><label for='cf_leave_day'>회원탈퇴후 삭제일</label></th>
        <td colspan="3"><input type="text" name="cf_leave_day" value="<?=$config['cf_leave_day']?>" id="cf_leave_day" class="frm_input" size="2"> 일 후 자동 삭제</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_use_member_icon">회원아이콘 사용</label></th>
        <td>
            <?=help('게시물에 게시자 별명 대신 아이콘 사용')?>
            <select id="cf_use_member_icon" name="cf_use_member_icon">
                <option value="0"<?=get_selected($config['cf_use_member_icon'], '0')?>>미사용
                <option value="1"<?=get_selected($config['cf_use_member_icon'], '1')?>>아이콘만 표시
                <option value="2"<?=get_selected($config['cf_use_member_icon'], '2')?>>아이콘+이름 표시
            </select>
        </td>
        <th scope="row"><label for="cf_icon_level">아이콘 업로드 권한</label></th>
        <td><?=get_member_level_select('cf_icon_level', 1, 9, $config['cf_icon_level']) ?> 이상</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_member_icon_size">회원아이콘 용량</label></th>
        <td><input type="text" name="cf_member_icon_size" value="<?=$config['cf_member_icon_size']?>" id="cf_member_icon_size" class="frm_input" size="10"> 바이트 이하</td>
        <th scope="row">회원아이콘 사이즈</th>
        <td>
            <label for="cf_member_icon_width">가로</label>
            <input type="text" name="cf_member_icon_width" value="<?=$config['cf_member_icon_width']?>" id="cf_member_icon_width" class="frm_input" size="2">
            <label for="cf_member_icon_height">세로</label>
            <input type="text" name="cf_member_icon_height" value="<?=$config['cf_member_icon_height']?>" id="cf_member_icon_height" class="frm_input" size="2">
            픽셀 이하
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_use_recommend">추천인제도 사용</label></th>
        <td><input type="checkbox" name="cf_use_recommend" value="1" id="cf_use_recommend" <?=$config['cf_use_recommend']?'checked':'';?>> 사용</td>
        <th scope="row"><label for="cf_recommend_point">추천인 포인트</label></th>
        <td><input type="text" name="cf_recommend_point" value="<?=$config['cf_recommend_point']?>" id="cf_recommend_point" class="frm_input"> 점</td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_prohibit_id">아이디,별명 금지단어</label></th>
        <td>
            <?=help('회원아이디, 별명으로 사용할 수 없는 단어를 정합니다. 쉼표 (,) 로 구분')?>
            <textarea name="cf_prohibit_id" id="cf_prohibit_id" rows="5"><?=$config['cf_prohibit_id']?></textarea>
        </td>
        <th scope="row"><label for="cf_prohibit_email">입력 금지 메일</label></th>
        <td>
            <?=help('hotmail.com과 같은 메일 주소는 사용하지 못하도록 못합니다. 엔터로 구분')?>
            <textarea name="cf_prohibit_email" id="cf_prohibit_email" rows="5"><?=$config['cf_prohibit_email']?></textarea>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_stipulation">회원가입약관</label></th>
        <td colspan="3"><textarea name="cf_stipulation" id="cf_stipulation" rows="10"><?=$config['cf_stipulation']?></textarea></td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_privacy">개인정보취급방침</label></th>
        <td colspan="3"><textarea id="cf_privacy" name="cf_privacy" rows="10"><?=$config['cf_privacy']?> </textarea></td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_mail" class="cbox">
    <h2>기본 메일환경 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cf_email_use">메일발송 사용</label></th>
        <td>
            <?=help('체크하지 않으면 메일발송을 아예 사용하지 않습니다. 메일 테스트도 불가합니다.')?>
            <input type="checkbox" name="cf_email_use" value="1" id="cf_email_use" <?=$config['cf_email_use']?'checked':'';?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_use_email_certify">메일인증 사용</label></th>
        <td>
            <?=help('메일에 배달된 인증 주소를 클릭하여야 회원으로 인정합니다.');?>
            <input type="checkbox" name="cf_use_email_certify" value="1" id="cf_use_email_certify" <?=$config['cf_use_email_certify']?'checked':'';?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_formmail_is_member">폼메일 사용 여부</label></th>
        <td>
            <?=help('체크하지 않으면 비회원도 사용 할 수 있습니다.')?>
            <input type="checkbox" name="cf_formmail_is_member" value="1" id="cf_formmail_is_member" <?=$config['cf_formmail_is_member']?'checked':'';?>> 회원만 사용
        </td>
    </tr>
    </table>
</section>

<section id="frm_article_mail" class="cbox">
    <h2>게시판 글 작성 시 메일 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cf_email_wr_super_admin">최고관리자</label></th>
        <td>
            <?=help('최고관리자에게 메일을 발송합니다.')?>
            <input type="checkbox" name="cf_email_wr_super_admin" value="1" id="cf_email_wr_super_admin" <?=$config['cf_email_wr_super_admin']?'checked':'';?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_email_wr_group_admin">그룹관리자</label></th>
        <td>
            <?=help('그룹관리자에게 메일을 발송합니다.')?>
            <input type="checkbox" name="cf_email_wr_group_admin" value="1" id="cf_email_wr_group_admin" <?=$config['cf_email_wr_group_admin']?'checked':'';?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_email_wr_board_admin">게시판관리자</label></th>
        <td>
            <?=help('게시판관리자에게 메일을 발송합니다.')?>
            <input type="checkbox" name="cf_email_wr_board_admin" value="1" id="cf_email_wr_board_admin" <?=$config['cf_email_wr_board_admin']?'checked':'';?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_email_wr_write">원글작성자</label></th>
        <td>
            <?=help('게시자님께 메일을 발송합니다.')?>
            <input type="checkbox" name="cf_email_wr_write" value="1" id="cf_email_wr_write" <?=$config['cf_email_wr_write']?'checked':'';?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_email_wr_comment_all">댓글작성자</label></th>
        <td>
            <?=help('원글에 댓글이 올라오는 경우 댓글 쓴 모든 분들께 메일을 발송합니다.')?>
            <input type="checkbox" name="cf_email_wr_comment_all" value="1" id="cf_email_wr_comment_all" <?=$config['cf_email_wr_comment_all']?'checked':'';?>> 사용
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_join_mail" class="cbox">
    <h2>회원가입 시 메일 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cf_email_mb_super_admin">최고관리자 메일발송</label></th>
        <td>
            <?=help('최고관리자에게 메일을 발송합니다.')?>
            <input type="checkbox" name="cf_email_mb_super_admin" value="1" id="cf_email_mb_super_admin" <?=$config['cf_email_mb_super_admin']?'checked':'';?>> 사용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_email_mb_member">회원님께 메일발송</label></th>
        <td>
            <?=help('회원가입한 회원님께 메일을 발송합니다.')?>
            <input type="checkbox" name="cf_email_mb_member" value="1" id="cf_email_mb_member" <?=$config['cf_email_mb_member']?'checked':'';?>> 사용
        </td>
    </tr>
    </tbody>
    </table>
</section>


<section id="frm_vote_mail" class="cbox">
    <h2>투표 기타의견 작성시 메일 설정</h2>
    <?=$pg_anchor?>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cf_email_po_super_admin">최고관리자 메일발송</label></th>
        <td>
            <?=help('최고관리자에게 메일을 발송합니다.')?>
            <input type="checkbox" name="cf_email_po_super_admin" value="1" id="cf_email_po_super_admin" <?=$config['cf_email_po_super_admin']?'checked':'';?>> 사용
        </td>
    </tr>
    </tbody>
    </table>
</section>

<section id="frm_extra" class="cbox">
    <h2>여분필드 기본 설정</h2>
    <?=$pg_anchor?>
    <p>각 게시판 관리에서 개별적으로 설정 가능합니다.</p>

    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_15">
    </colgroup>
    <tbody>
    <? for ($i=1; $i<=10; $i++) { ?>
    <tr>
        <th scope="row">여분필드<?=$i?></th>
        <td>
            <label for="cf_<?=$i?>_subj">여분필드<?=$i?> 제목</label>
            <input type="text" name="cf_<?=$i?>_subj" value="<?=get_text($config['cf_'.$i.'_subj'])?>" id="cf_<?=$i?>_subj" class="frm_input" size="30">
            <label for="cf_<?=$i?>">여분필드<?=$i?> 값</label>
            <input type="text" name="cf_<?=$i?>" value="<?=$config['cf_'.$i]?>" id="cf_<?=$i?>" class="frm_input" size="30">
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
</div>

</form>

<script>
function fconfigform_submit(f)
{
    f.action = "./config_form_update.php";
    return true;
}
</script>

<?
include_once ('./admin.tail.php');
?>

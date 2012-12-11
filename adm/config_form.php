<?
$sub_menu = "100100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'r');

$token = get_token();

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

// 쪽지보낼시 차감 포인트 필드 추가 : 061218
sql_query(" ALTER TABLE '{$g4['config_table']}' ADD 'cf_memo_send_point' INT NOT NULL AFTER 'cf_login_point' ", FALSE);

// 개인정보보호정책 필드 추가 : 061121
$sql = " ALTER TABLE '{$g4['config_table']}' ADD 'cf_privacy' TEXT NOT NULL AFTER 'cf_stipulation' ";
sql_query($sql, FALSE);
if (!trim($config['cf_privacy'])) {
    $config['cf_privacy'] = '해당 홈페이지에 맞는 개인정보취급방침을 입력합니다.';
}

$g4['title'] = '환경설정';
include_once ('./admin.head.php');
?>

<form id="fconfigform" name="fconfigform" method="post" onsubmit="return fconfigform_submit(this);">
<input type="hidden" id="token" name="token" value="<?=$token?>">

<section id="config_basic">
<h2><span></span>기본환경설정</h2>
<table class="frm_tbl">
<caption>홈페이지 기본환경 설정</caption>
<tbody>
<tr>
    <th scope="row"><label for="cf_title">홈페이지 제목</label></th>
    <td><input type="text" id="cf_title" name="cf_title" required value="<?=$config['cf_title']?>"></td>
    <th scope="row"><label for="cf_admin">최고관리자</label></th>
    <td><?=get_member_id_select('cf_admin', 10, $config['cf_admin'], 'required')?></td>
</tr>
<tr>
    <th scope="row"><label for="cf_use_point">포인트 사용</label></th>
    <td colspan="3"><input type="checkbox" id="cf_use_point" name="cf_use_point" value="1" <?=$config[cf_use_point]?'checked':'';?>> 사용</td>
</tr>
<tr>
    <th scope="row"><label for="cf_login_point">로그인시 포인트</label></th>
    <td>
        <?=help('회원에게 하루에 한번만 부여')?>
        <input type="text" id="cf_login_point" name="cf_login_point" required value="<?=$config[cf_login_point]?>"> 점
    </td>
    <th scope="row"><label for="cf_memo_send_point">쪽지보낼시 차감 포인트</label></th>
    <td>
         <?=help('양수로 입력하십시오.<br>0으로 입력하시면 쪽지보낼시 포인트를 차감하지 않습니다.')?>
        <input type="text" id="cf_memo_send_point" name="cf_memo_send_point" required value="<?=$config[cf_memo_send_point]?>"> 점
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_cut_name">이름(별명) 표시</label></th>
    <td colspan="3">
        <?=help('영숫자 2글자 = 한글 1글자')?>
        <input type="text" id="cf_cut_name" name="cf_cut_name" value="<?=$config[cf_cut_name]?>"> 자리만 표시
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_nick_modify">별명 수정</label></th>
    <td>수정한 후 <input type="text" id="cf_nick_modify" name="cf_nick_modify" value="<?=$config[cf_nick_modify]?>"> 일 동안 바꿀 수 없음</td>
    <th scope="row"><label for="cf_open_modify">정보공개 수정</label></th>
    <td>수정한 후 <input type="text" id="cf_open_modify" name="cf_open_modify" value="<?=$config[cf_open_modify]?>"> 일 동안 바꿀 수 없음</td>
</tr>
<tr>
    <th scope="row"><label for="cf_new_del">최근게시물 삭제</label></th>
    <td>
        <?=help('설정일이 지난 최근게시물 자동 삭제')?>
        <input type="text" id="cf_new_del" name="cf_new_del" value="<?=$config[cf_new_del]?>"> 일
    </td>
    <th scope="row"><label for="cf_memo_del">쪽지 삭제</label></th>
    <td>
        <?=help('설정일이 지난 쪽지 자동 삭제')?>
        <input type="text" id="cf_memo_del" name="cf_memo_del" value="<?=$config[cf_memo_del]?>"> 일
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_visit_del">접속자로그 삭제</label></th>
    <td>
        <?=help('설정일이 지난 접속자 로그 자동 삭제')?>
        <input type="text" id="cf_visit_del" name="cf_visit_del" value="<?=$config[cf_visit_del]?>"> 일
    </td>
    <th scope="row"><label for="cf_popular_del">인기검색어 삭제</label></th>
    <td>
        <?=help('설정일이 지난 인기검색어 자동 삭제')?>
        <input type="text" id="cf_popular_del" name="cf_popular_del" value="<?=$config[cf_popular_del]?>"> 일
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_login_minutes">현재 접속자</label></th>
    <td>
        <?=help('설정값 이내의 접속자를 현재 접속자로 인정')?>
        <input type="text" id="cf_login_minutes" name="cf_login_minutes" value="<?=$config[cf_login_minutes]?>"> 분
    </td>
    <th scope="row"><label for="cf_page_rows">한페이지당 라인수</label></th>
    <td>
        <?=help('목록(리스트) 한페이지당 라인수')?>
        <input type="text" id="cf_page_rows" name="cf_page_rows" value="<?=$config[cf_page_rows]?>"> 라인
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_new_skin">최근게시물 스킨</label></th>
    <td><select id="cf_new_skin" name="cf_new_skin" required >
        <?
        $arr = get_skin_dir('new');
        for ($i=0; $i<count($arr); $i++) {
            echo '<option value="'.$arr[$i].'">'.$arr[$i].'</option>'.PHP_EOL;
        }
        ?></select>
        <script> document.getElementById('cf_new_skin').value="<?=$config['cf_new_skin']?>";</script>
    </td>
    <th scope="row"><label for="cf_new_rows">최근게시물 라인수</label></th>
    <td>
        <?=help('목록 한페이지당 라인수')?>
        <input type="text" id="cf_new_rows" name="cf_new_rows" value="<?=$config[cf_new_rows]?>"> 라인
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_search_skin">검색 스킨</label></th>
    <td colspan="3"><select id="cf_search_skin" name="cf_search_skin" required>
        <?
        $arr = get_skin_dir("search");
        for ($i=0; $i<count($arr); $i++) {
            echo '<option value="'.$arr[$i].'">'.$arr[$i].'</option>'.PHP_EOL;
        }
        ?></select>
        <script> document.getElementById('cf_search_skin').value="<?=$config['cf_search_skin']?>";</script>
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_connect_skin">접속자 스킨</label></th>
    <td colspan="3"><select id="cf_connect_skin" name="cf_connect_skin" required >
        <?
        $arr = get_skin_dir('connect');
        for ($i=0; $i<count($arr); $i++) {
            echo '<option value="'.$arr[$i].'">'.$arr[$i].'</option>'.PHP_EOL;
        }
        ?></select>
        <script> document.getElementById('cf_connect_skin').value="<?=$config['cf_connect_skin']?>";</script>
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_use_copy_log">복사, 이동시 로그</label></th>
    <td colspan="3">
        <?=help('게시물 아래에 누구로 부터 복사, 이동됨 표시')?>
        <input type="checkbox" id="cf_use_copy_log" name="cf_use_copy_log" value="1" <?=$config[cf_use_copy_log]?'checked':'';?>> 남김
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_possible_ip">접근가능 IP</label></th>
    <td>
        <?=help('입력된 IP의 컴퓨터만 접근할 수 있습니다. 123.123.+ 도 입력 가능. (엔터로 구분)')?>
        <textarea id="cf_possible_ip" name="cf_possible_ip"><?=$config['cf_possible_ip']?> </textarea>
    </td>
    <th scope="row"><label for="cf_intercept_ip">접근차단 IP</label></th>
    <td>
        <?=help('입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)')?>
        <textarea id="cf_intercept_ip" name="cf_intercept_ip"><?=$config['cf_intercept_ip']?> </textarea>
    </td>
</tr>
</tbody>
</table>
</section>

<section id="config_board">
<h2><span></span>게시판 설정</h2>
<table class="frm_tbl">
<caption>게시판 공통 설정, 게시판 관리에서 개별 설정 가능</caption>
<tbody>
<tr>
    <th scope="row"><label for="cf_read_point">글읽기 포인트</label></th>
    <td><input type="text" id="cf_read_point" name="cf_read_point" required value="<?=$config[cf_read_point]?>"> 점</td>
    <th scope="row"><label for="cf_write_point">글쓰기 포인트</label></th>
    <td><input type="text" id="cf_write_point" name="cf_write_point" required value="<?=$config[cf_write_point]?>"> 점</td>
</tr>
<tr>
    <th scope="row"><label for="cf_comment_point">코멘트쓰기 포인트</label></th>
    <td><input type="text" id="cf_comment_point" name="cf_comment_point" required value="<?=$config[cf_comment_point]?>"> 점</td>
    <th scope="row"><label for="cf_download_point">다운로드 포인트</label></th>
    <td><input type="text" id="cf_download_point" name="cf_download_point" required value="<?=$config[cf_download_point]?>"> 점</td>
</tr>
<tr>
    <th scope="row"><label for="cf_link_target">LINK TARGET</label></th>
    <td>
        <?=help('게시판 내용중 자동으로 링크되는 창의 타켓을 지정합니다. _self, _top, _blank, _new 를 주로 지정합니다.')?>
        <input type="text" id="cf_link_target" name="cf_link_target" value="<?=$config['cf_link_target']?>">
    </td>
    <th scope="row"><label for="cf_search_part">검색 단위</label></th>
    <td><input type="text" id="cf_search_part" name="cf_search_part" value="<?=$config[cf_search_part]?>"> 건 단위로 검색</td>
</tr>
<tr>
    <th scope="row"><label for="cf_search_bgcolor">검색 배경 색상</label></th>
    <td><input type="text" id="cf_search_bgcolor" name="cf_search_bgcolor" required value="<?=$config['cf_search_bgcolor']?>"></td>
    <th scope="row"><label for="cf_search_color">검색 글자 색상</label></th>
    <td><input type="text" id="cf_search_color" name="cf_search_color" required value="<?=$config['cf_search_color']?>"></td>
</tr>
<tr>
    <th scope="row"><label for="cf_delay_sec">글쓰기 간격</label></th>
    <td><input type="text" id="cf_delay_sec" name="cf_delay_sec" required value="<?=$config[cf_delay_sec]?>"> 초 지난후 가능</td>
    <th scope="row"><label for="cf_write_pages">페이지 표시 수</label></th>
    <td><input type="text" id="cf_write_pages" name="cf_write_pages" required value="<?=$config[cf_write_pages]?>"> 페이지씩 표시</td>
</tr>
<tr>
    <th scope="row"><label for="cf_image_extension">이미지 업로드 확장자</label></th>
    <td colspan="3">
        <?=help('게시판 글작성시 이미지 파일 업로드 가능 확장자. | 로 구분')?>
        <input type="text" id="cf_image_extension" name="cf_image_extension" value="<?=$config['cf_image_extension']?>">
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_flash_extension">플래쉬 업로드 확장자</label></th>
    <td colspan="3">
        <?=help('게시판 글작성시 플래쉬 파일 업로드 가능 확장자. | 로 구분')?>
        <input type="text" id="cf_flash_extension" name="cf_flash_extension" value="<?=$config['cf_flash_extension']?>">
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_movie_extension">동영상 업로드 확장자</label></th>
    <td colspan="3">
        <?=help('게시판 글작성시 동영상 파일 업로드 가능 확장자. | 로 구분')?>
        <input type="text" id="cf_movie_extension" name="cf_movie_extension" value="<?=$config['cf_movie_extension']?>">
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_filter">단어 필터링</label></th>
    <td colspan="3">
        <?=help('입력된 단어가 포함된 내용은 게시할 수 없습니다. 단어와 단어 사이는 ,로 구분합니다.')?>
        <textarea id="cf_filter" name="cf_filter" rows="7"><?=$config['cf_filter']?> </textarea>
     </td>
</tr>
</tbody>
</table>
</section>

<section id="config_mb_join">
<h2><span></span>회원가입 설정</h2>
<table class="frm_tbl">
<caption>회원가입스킨 및 입력받을 정보 설정</caption>
<tbody>
<tr>
    <th scope="row"><label for="cf_member_skin">회원 스킨</label></th>
    <td colspan="3">
        <select id="cf_member_skin" name="cf_member_skin" required>
        <?
        $arr = get_skin_dir('member');
        for ($i=0; $i<count($arr); $i++) {
            echo '<option value="'.$arr[$i].'">'.$arr[$i].'</option>'.PHP_EOL;
        }
        ?>
        </select>
        <script> document.getElementById('cf_member_skin').value="<?=$config['cf_member_skin']?>";</script>
    </td>
</tr>
<tr>
    <th scope="row">홈페이지 입력</th>
    <td>
        <input type="checkbox" id="cf_use_homepage" name="cf_use_homepage" value="1" <?=$config[cf_use_homepage]?'checked':'';?>> <label for="cf_use_homepage">보이기</label>
        <input type="checkbox" id="cf_req_homepage" name="cf_req_homepage" value="1" <?=$config[cf_req_homepage]?'checked':'';?>> <label for="cf_req_homepage">필수입력</label>
    </td>
    <th scope="row">주소 입력</th>
    <td>
        <input type="checkbox" id="cf_use_addr" name="cf_use_addr" value="1" <?=$config[cf_use_addr]?'checked':'';?>> <label for="cf_use_addr">보이기</label>
        <input type="checkbox" id="cf_req_addr" name="cf_req_addr" value="1" <?=$config[cf_req_addr]?'checked':'';?>> <label for="cf_req_addr">필수입력</label>
    </td>
</tr>
<tr>
    <th scope="row">전화번호 입력</th>
    <td>
        <input type="checkbox" id="cf_use_tel" name="cf_use_tel" value="1" <?=$config[cf_use_tel]?'checked':'';?>> <label for="cf_use_tel">보이기</label>
        <input type="checkbox" id="cf_req_tel" name="cf_req_tel" value="1" <?=$config[cf_req_tel]?'checked':'';?>> <label for="cf_req_tel">필수입력</label>
    </td>
    <th scope="row">핸드폰 입력</th>
    <td>
        <input type="checkbox" id="cf_use_hp" name="cf_use_hp" value="1" <?=$config[cf_use_hp]?'checked':'';?>> <label for="cf_use_hp">보이기</label>
        <input type="checkbox" id="cf_req_hp" name="cf_req_hp" value="1" <?=$config[cf_req_hp]?'checked':'';?>> <label for="cf_req_hp">필수입력</label>
    </td>
</tr>
<tr>
    <th scope="row">서명 입력</th>
    <td>
        <input type="checkbox" id="cf_use_signature" name="cf_use_signature" value="1" <?=$config[cf_use_signature]?'checked':'';?>> <label for="cf_use_signature">보이기</label>
        <input type="checkbox" id="cf_req_signature" name="cf_req_signature" value="1" <?=$config[cf_req_signature]?'checked':'';?>> <label for="cf_req_signature">필수입력</label>
    </td>
    <th scope="row">자기소개 입력</th>
    <td>
        <input type="checkbox" id="cf_use_profile" name="cf_use_profile" value="1" <?=$config[cf_use_profile]?'checked':'';?>> <label for="cf_use_profile">보이기</label>
        <input type="checkbox" id="cf_req_profile" name="cf_req_profile" value="1" <?=$config[cf_req_profile]?'checked':'';?>> <label for="cf_req_profile">필수입력</label>
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_register_level">회원가입시 권한</label></th>
    <td><?=get_member_level_select('cf_register_level', 1, 9, $config[cf_register_level]) ?></td>
    <th scope="row"><label for="cf_register_point">회원가입시 포인트</label></th>
    <td><input type="text" id="cf_register_point" name="cf_register_point" value="<?=$config[cf_register_point]?>"> 점</td>
</tr>
<tr>
    <th scope='row' id="th310"><label for='cf_leave_day'>회원탈퇴후 삭제일</label></th>
    <td colspan="3"><input type="text" id="cf_leave_day" name="cf_leave_day" value="<?=$config[cf_leave_day]?>"> 일 후 자동 삭제</td>
</tr>
<tr>
    <th scope="row"><label for="cf_use_member_icon">회원아이콘 사용</label></th>
    <td>
        <select id="cf_use_member_icon" name="cf_use_member_icon">
            <option value="0">미사용
            <option value="1">아이콘만 표시
            <option value="2">아이콘+이름 표시
        </select>
        <?=help('게시물에 게시자 별명 대신 아이콘 사용')?>
        <script> document.getElementById('cf_use_member_icon').value="<?=$config[cf_use_member_icon]?>";</script>
    </td>
    <th scope="row"><label for="cf_icon_level">아이콘 업로드 권한</label></th>
    <td><?=get_member_level_select('cf_icon_level', 1, 9, $config[cf_icon_level]) ?> 이상</td>
</tr>
<tr>
    <th scope="row"><label for="cf_member_icon_size">회원아이콘 용량</label></th>
    <td><input type="text" id="cf_member_icon_size" name="cf_member_icon_size" value="<?=$config[cf_member_icon_size]?>"> 바이트 이하</td>
    <th scope="row"><label for="cf_member_icon_width">회원아이콘 사이즈</label></th>
    <td>
        가로 <input type="text" id="cf_member_icon_width" name="cf_member_icon_width" value="<?=$config[cf_member_icon_width]?>" title="가로">
        세로 <input type="text" id="cf_member_icon_height" name="cf_member_icon_height" value="<?=$config[cf_member_icon_height]?>" title="세로"> 픽셀 이하
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_use_recommend">추천인제도 사용</label></th>
    <td><input type="checkbox" id="cf_use_recommend" name="cf_use_recommend" value="1" <?=$config[cf_use_recommend]?'checked':'';?>> 사용</td>
    <th scope="row"><label for="cf_recommend_point">추천인 포인트</label></th>
    <td><input type="text" id="cf_recommend_point" name="cf_recommend_point" value="<?=$config[cf_recommend_point]?>"> 점</td>
</tr>
<tr>
    <th scope="row"><label for="cf_prohibit_id">아이디,별명 금지단어</label></th>
    <td>
        <?=help('입력된 단어가 포함된 내용은 회원아이디, 별명으로 사용할 수 없습니다. 단어와 단어 사이는 , 로 구분합니다..')?>
        <textarea id="cf_prohibit_id" name="cf_prohibit_id" rows="5"><?=$config['cf_prohibit_id']?></textarea>
    </td>
    <th scope="row"><label for="cf_prohibit_email">입력 금지 메일</label></th>
    <td>
        <?=help('hanmail.net과 같은 메일 주소는 입력을 못합니다. 엔터로 구분합니다.')?>
        <textarea id="cf_prohibit_email" name="cf_prohibit_email" rows="5"><?=$config['cf_prohibit_email']?></textarea>
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_stipulation">회원가입약관</label></th>
    <td colspan="3"><textarea id="cf_stipulation" name="cf_stipulation" rows="10"><?=$config['cf_stipulation']?></textarea></td>
</tr>
<tr>
    <th scope="row"><label for="cf_privacy">개인정보취급방침</label></th>
    <td colspan="3"><textarea id="cf_privacy" name="cf_privacy" rows="10"><?=$config['cf_privacy']?> </textarea></td>
</tr>
</tbody>
</table>
</section>

<section id="config_mail">
<h2><span></span>메일 설정</h2>
<table class="frm_tbl">
<caption>기본 메일환경 설정</caption>
<tbody>
<tr>
    <th scope="row"><label for="cf_email_use">메일발송 사용</label></th>
    <td>
        <?=help('체크하지 않으면 메일발송을 아예 사용하지 않습니다. 메일 테스트도 불가합니다.')?>
        <input type="checkbox" id="cf_email_use" name="cf_email_use" value="1" <?=$config[cf_email_use]?'checked':'';?>> 사용
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_use_email_certify">메일인증 사용</label></th>
    <td>
        <?=help('메일에 배달된 인증 주소를 클릭하여야 회원으로 인정합니다.');?>
        <input type="checkbox" id="cf_use_email_certify" name="cf_use_email_certify" value="1" <?=$config[cf_use_email_certify]?'checked':'';?>> 사용
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_formmail_is_member">폼메일 사용 여부</label></th>
    <td>
        <?=help('체크하지 않으면 비회원도 사용 할 수 있습니다.')?>
        <input type="checkbox" id="cf_formmail_is_member" name="cf_formmail_is_member" value="1" <?=$config[cf_formmail_is_member]?'checked':'';?>> 회원만 사용
    </td>
</tr>
</table>

<table class="frm_tbl">
<caption>게시판 글 작성 시 메일 설정</caption>
<tbody>
<tr>
    <th scope="row"><label for="cf_email_wr_super_admin">최고관리자 메일발송</label></th>
    <td>
        <?=help('최고관리자에게 메일을 발송합니다.')?>
        <input type="checkbox" id="cf_email_wr_super_admin" name="cf_email_wr_super_admin" value="1" <?=$config[cf_email_wr_super_admin]?'checked':'';?>> 사용
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_email_wr_group_admin">그룹관리자 메일발송</label></th>
    <td>
        <?=help('그룹관리자에게 메일을 발송합니다.')?>
        <input type="checkbox" id="cf_email_wr_group_admin" name="cf_email_wr_group_admin" value="1" <?=$config[cf_email_wr_group_admin]?'checked':'';?>> 사용
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_email_wr_board_admin">게시판관리자 메일발송</label></th>
    <td>
        <?=help('게시판관리자에게 메일을 발송합니다.')?>
        <input type="checkbox" id="cf_email_wr_board_admin" name="cf_email_wr_board_admin" value="1" <?=$config[cf_email_wr_board_admin]?'checked':'';?>> 사용
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_email_wr_write">원글 메일발송</label></th>
    <td>
        <?=help('게시자님께 메일을 발송합니다.')?>
        <input type="checkbox" id="cf_email_wr_write" name="cf_email_wr_write" value="1" <?=$config[cf_email_wr_write]?'checked':'';?>> 사용
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_email_wr_comment_all">코멘트 메일발송</label></th>
    <td>
        <?=help('원글에 코멘트가 올라오는 경우 코멘트 쓴 모든 분들께 메일을 발송합니다.')?>
        <input type="checkbox" id="cf_email_wr_comment_all" name="cf_email_wr_comment_all" value="1" <?=$config[cf_email_wr_comment_all]?'checked':'';?>> 사용
    </td>
</tr>
</tbody>
</table>

<table class="frm_tbl">
<caption>회원가입 시 메일 설정</caption>
<tbody>
<tr>
    <th scope="row"><label for="cf_email_mb_super_admin">최고관리자 메일발송</label></th>
    <td>
        <?=help('최고관리자에게 메일을 발송합니다.')?>
        <input type="checkbox" id="cf_email_mb_super_admin" name="cf_email_mb_super_admin" value="1" <?=$config[cf_email_mb_super_admin]?'checked':'';?>> 사용
    </td>
</tr>
<tr>
    <th scope="row"><label for="cf_email_mb_member">회원님께 메일발송</label></th>
    <td>
        <?=help('회원가입한 회원님께 메일을 발송합니다.')?>
        <input type="checkbox" id="cf_email_mb_member" name="cf_email_mb_member" value="1" <?=$config[cf_email_mb_member]?'checked':'';?>> 사용
    </td>
</tr>
</tbody>
</table>

<table class="frm_tbl">
<caption>투표 기타의견 작성시 메일 설정</caption>
<tbody>
<tr>
    <th scope="row"><label for="cf_email_po_super_admin">최고관리자 메일발송</label></th>
    <td>
        <?=help('최고관리자에게 메일을 발송합니다.')?>
        <input type="checkbox" id="cf_email_po_super_admin" name="cf_email_po_super_admin" value="1" <?=$config[cf_email_po_super_admin]?'checked':'';?>> 사용
    </td>
</tr>
</tbody>
</table>
</section>

<section id="config_extra">
<h2><span></span>여분필드 설정</h2>
<table class="frm_tbl">
<caption>여분필드의 기본 내용 설정, 게시판 관리에서 개별 설정 가능</caption>
<tbody>
<? for ($i=1; $i<=10; $i++) { ?>
<tr>
    <th scope="row">여분필드</th>
    <td>
        <label for="cf_<?=$i?>_subj">여분필드<?=$i?>제목</label>
        <input type="text" id="cf_<?=$i?>_subj" name="cf_<?=$i?>_subj" value="<?=get_text($config['cf_'.$i.'_subj'])?>">
        <label for="cf_<?=$i?>">여분필드<?=$i?>설명</label>
        <input type="text" id="cf_<?=$i?>" name="cf_<?=$i?>" value="<?=$config['cf_'.$i]?>">
    </td>
</tr>
<? } ?>
</tbody>
</table>
</section>

<fieldset id="admin_confirm">
    <legend><span></span>XSS 혹은 CSRF 방지</legend>
    <p>관리자 권한을 탈취당하는 경우를 대비하여 패스워드를 다시 한번 확인합니다.</p>
    <label for="admin_password">관리자 패스워드</label>
    <input type="password" id="admin_password" name="admin_password" required>
</fieldset>

<div class="btn_confirm">
    <input type="submit" accesskey="s" value="확인">
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

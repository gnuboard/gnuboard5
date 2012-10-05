<?
$sub_menu = "100100";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$token = get_token();

if ($is_admin != "super")
    alert("최고관리자만 접근 가능합니다.");

// 쪽지보낼시 차감 포인트 필드 추가 : 061218
sql_query(" ALTER TABLE `$g4[config_table]` ADD `cf_memo_send_point` INT NOT NULL AFTER `cf_login_point` ", FALSE);

// 개인정보보호정책 필드 추가 : 061121
$sql = " ALTER TABLE `$g4[config_table]` ADD `cf_privacy` TEXT NOT NULL AFTER `cf_stipulation` ";
sql_query($sql, FALSE);
if (!trim($config[cf_privacy])) {
    $config[cf_privacy] = "해당 홈페이지에 맞는 개인정보취급방침을 입력합니다.";
}

$g4['title'] = "기본환경설정";
include_once ("./admin.head.php");
?>

<form name='fconfigform' method='post' onsubmit="return fconfigform_submit(this);">
<input type=hidden name=token value='<?=$token?>'>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<colgroup width=20% class='col1 pad1 bold right'>
<colgroup width=30% class='col2 pad2'>
<colgroup width=20% class='col1 pad1 bold right'>
<colgroup width=30% class='col2 pad2'>
<tr class='ht'>
    <td colspan=4 align=left><?=subtitle("기본 설정")?></td>
</tr>
<tr><td colspan=4 class=line1></td></tr>
<tr class='ht'>
    <td>홈페이지 제목</td>
    <td>
        <input type=text class=ed name='cf_title' size='30' required itemname='홈페이지 제목' value='<?=$config[cf_title]?>'>
    </td>
    <td>최고관리자</td>
    <td><?=get_member_id_select("cf_admin", 10, $config[cf_admin], "required itemname='최고 관리자'")?></td>
</tr>
<tr class='ht'>
    <td>포인트 사용</td>
    <td colspan=3><input type='checkbox' name='cf_use_point' value='1' <?=$config[cf_use_point]?'checked':'';?>> 사용</td>
</tr>
<tr class='ht'>
    <td>로그인시 포인트</td>
    <td><input type=text class=ed name='cf_login_point' size='5' required itemname='로그인시 포인트' value='<?=$config[cf_login_point]?>'> 점
        <?=help("회원에게 하루에 한번만 부여")?></td>
    <td>쪽지보낼시 차감 포인트</td>
    <td><input type=text class=ed name='cf_memo_send_point' size='5' required itemname='쪽지전송시 차감 포인트' value='<?=$config[cf_memo_send_point]?>'> 점
        <?=help("양수로 입력하십시오.<br>0으로 입력하시면 쪽지보낼시 포인트를 차감하지 않습니다.")?></td>
</tr>
<tr class='ht'>
    <td>이름(별명) 표시</td>
    <td colspan=3><input type=text class=ed name='cf_cut_name' value='<?=$config[cf_cut_name]?>' size=2> 자리만 표시
        <?=help("영숫자 2글자 = 한글 1글자")?></td>
</tr>
<tr class='ht'>
    <td>별명 수정</td>
    <td>수정한 후 <input type=text class=ed name='cf_nick_modify' value='<?=$config[cf_nick_modify]?>' size=2> 일 동안 바꿀 수 없음</td>
    <td>정보공개 수정</td>
    <td>수정한 후 <input type=text class=ed name='cf_open_modify' value='<?=$config[cf_open_modify]?>' size=2> 일 동안 바꿀 수 없음</td>
</tr>
<tr class='ht'>
    <td>최근게시물 삭제</td>
    <td><input type=text class=ed name='cf_new_del' value='<?=$config[cf_new_del]?>' size=5> 일
        <?=help("설정일이 지난 최근게시물 자동 삭제")?></td>
    <td>쪽지 삭제</td>
    <td><input type=text class=ed name='cf_memo_del' value='<?=$config[cf_memo_del]?>' size=5> 일
        <?=help("설정일이 지난 쪽지 자동 삭제")?></td>
</tr>
<tr class='ht'>
    <td>접속자로그 삭제</td>
    <td><input type=text class=ed name='cf_visit_del' value='<?=$config[cf_visit_del]?>' size=5> 일
        <?=help("설정일이 지난 접속자 로그 자동 삭제")?></td>
    <td>인기검색어 삭제</td>
    <td><input type=text class=ed name='cf_popular_del' value='<?=$config[cf_popular_del]?>' size=5> 일
        <?=help("설정일이 지난 인기검색어 자동 삭제")?></td>
</tr>
<tr class='ht'>
    <td>현재 접속자</td>
    <td><input type=text class=ed name='cf_login_minutes' value='<?=$config[cf_login_minutes]?>' size=5> 분
        <?=help("설정값 이내의 접속자를 현재 접속자로 인정")?></td>
    <td>한페이지당 라인수</td>
    <td><input type=text class=ed name='cf_page_rows' value='<?=$config[cf_page_rows]?>' size=5> 라인
        <?=help("목록(리스트) 한페이지당 라인수")?></td>
</tr>
<tr class='ht'>
    <td>최근게시물 스킨</td>
    <td><select id=cf_new_skin name=cf_new_skin required itemname="최근게시물 스킨">
        <?
        $arr = get_skin_dir("new");
        for ($i=0; $i<count($arr); $i++) {
            echo "<option value='$arr[$i]'>$arr[$i]</option>\n";
        }
        ?></select>
        <script type="text/javascript"> document.getElementById('cf_new_skin').value="<?=$config[cf_new_skin]?>";</script>
    </td>
    <td>최근게시물 라인수</td>
    <td><input type=text class=ed name='cf_new_rows' value='<?=$config[cf_new_rows]?>' size=5> 라인
        <?=help("목록 한페이지당 라인수")?></td>
</tr>
<tr class='ht'>
    <td>검색 스킨</td>
    <td colspan=3><select id=cf_search_skin name=cf_search_skin required itemname="검색 스킨">
        <?
        $arr = get_skin_dir("search");
        for ($i=0; $i<count($arr); $i++) {
            echo "<option value='$arr[$i]'>$arr[$i]</option>\n";
        }
        ?></select>
        <script type="text/javascript"> document.getElementById('cf_search_skin').value="<?=$config[cf_search_skin]?>";</script>
    </td>
</tr>
<tr class='ht'>
    <td>접속자 스킨</td>
    <td colspan=3><select id=cf_connect_skin name=cf_connect_skin required itemname="접속자 스킨">
        <?
        $arr = get_skin_dir("connect");
        for ($i=0; $i<count($arr); $i++) {
            echo "<option value='$arr[$i]'>$arr[$i]</option>\n";
        }
        ?></select>
        <script type="text/javascript"> document.getElementById('cf_connect_skin').value="<?=$config[cf_connect_skin]?>";</script>
    </td>
</tr>
<tr class='ht'>
    <td>복사, 이동시 로그</td>
    <td colspan=3><input type='checkbox' name='cf_use_copy_log' value='1' <?=$config[cf_use_copy_log]?'checked':'';?>> 남김
        <?=help("게시물 아래에 누구로 부터 복사, 이동됨 표시")?></td>
    <!-- <td>자동등록방지 사용</td>
    <td><input type='checkbox' name='cf_use_norobot' value='1' <?=$config[cf_use_norobot]?'checked':'';?>> 사용
        <?=help("자동 회원가입과 글쓰기를 방지")?></td> -->
</tr>
<tr class='ht'>
    <td>접근가능 IP</td>
    <td valign=top><textarea class=ed name='cf_possible_ip' rows='5' style='width:99%;'><?=$config[cf_possible_ip]?> </textarea><br>입력된 IP의 컴퓨터만 접근할 수 있음.<br>123.123.+ 도 입력 가능. (엔터로 구분)</td>
    <td>접근차단 IP</td>
    <td valign=top><textarea class=ed name='cf_intercept_ip' rows='5' style='width:99%;'><?=$config[cf_intercept_ip]?> </textarea><br>입력된 IP의 컴퓨터는 접근할 수 없음.<br>123.123.+ 도 입력 가능. (엔터로 구분)</td>
</tr>
<tr><td colspan=4 class=line2></td></tr>
<tr><td colspan=4 class=ht></td></tr>


<tr>
    <td colspan=4 align=left><?=subtitle("게시판 설정")?></td>
</tr>
<tr><td colspan=4 class=line1></td></tr>
<tr class='ht'>
    <td>글읽기 포인트</td>
    <td><input type=text class=ed name='cf_read_point' size='10' required itemname='글읽기 포인트' value='<?=$config[cf_read_point]?>'> 점</td>
    <td>글쓰기 포인트</td>
    <td><input type=text class=ed name='cf_write_point' size='10' required itemname='글쓰기 포인트' value='<?=$config[cf_write_point]?>'> 점</td>
</tr>
<tr class='ht'>
    <td>코멘트쓰기 포인트</td>
    <td><input type=text class=ed name='cf_comment_point' size='10' required itemname='답변, 코멘트쓰기 포인트' value='<?=$config[cf_comment_point]?>'> 점</td>
    <td>다운로드 포인트</td>
    <td><input type=text class=ed name='cf_download_point' size='10' required itemname='다운로드받기 포인트' value='<?=$config[cf_download_point]?>'> 점</td>
</tr>
<tr class='ht'>
    <td>LINK TARGET</td>
    <td><input type=text class=ed name='cf_link_target' size='10' value='<?=$config[cf_link_target]?>'> 
        <?=help("게시판 내용중 자동으로 링크되는 창의 타켓을 지정합니다.\n\n_self, _top, _blank, _new 를 주로 지정합니다.")?></td>
    <td>검색 단위</td>
    <td><input type=text class=ed name='cf_search_part' size='10' itemname='검색 단위' value='<?=$config[cf_search_part]?>'> 건 단위로 검색</td>
</tr>
<tr class='ht'>
    <td>검색 배경 색상</td>
    <td><input type=text class=ed name='cf_search_bgcolor' size='10' required itemname='검색 배경 색상' value='<?=$config[cf_search_bgcolor]?>'></td>
    <td>검색 글자 색상</td>
    <td><input type=text class=ed name='cf_search_color' size='10' required itemname='검색 글자 색상' value='<?=$config[cf_search_color]?>'></td>
</tr>
<tr class='ht'>
    <td>새로운 글쓰기</td>
    <td><input type=text class=ed name='cf_delay_sec' size='10' required itemname='새로운 글쓰기' value='<?=$config[cf_delay_sec]?>'> 초 지난후 가능</td>
    <td>페이지 표시 수</td>
    <td><input type=text class=ed name='cf_write_pages' size='10' required itemname='페이지 표시 수' value='<?=$config[cf_write_pages]?>'> 페이지씩 표시</td>
</tr>
<tr class='ht'>
    <td>이미지 업로드 확장자</td>
    <td colspan=3><input type=text class=ed name='cf_image_extension' size='80' itemname='이미지 업로드 확장자' value='<?=$config[cf_image_extension]?>'>
        <?=help("게시판 글작성시 이미지 파일 업로드 가능 확장자. | 로 구분")?></td>
</tr>
<tr class='ht'>
    <td>플래쉬 업로드 확장자</td>
    <td colspan=3><input type=text class=ed name='cf_flash_extension' size='80' itemname='플래쉬 업로드 확장자' value='<?=$config[cf_flash_extension]?>'>
        <?=help("게시판 글작성시 플래쉬 파일 업로드 가능 확장자. | 로 구분")?></td>
</tr>
<tr class='ht'>
    <td>동영상 업로드 확장자</td>
    <td colspan=3><input type=text class=ed name='cf_movie_extension' size='80' itemname='동영상 업로드 확장자' value='<?=$config[cf_movie_extension]?>'>
        <?=help("게시판 글작성시 동영상 파일 업로드 가능 확장자. | 로 구분")?></td>
</tr>
<tr class='ht'>
    <td>단어 필터링
        <?=help("입력된 단어가 포함된 내용은 게시할 수 없습니다.\n\n단어와 단어 사이는 ,로 구분합니다.")?></td>
    <td colspan=3><textarea class=ed name='cf_filter' rows='7' style='width:99%;'><?=$config[cf_filter]?> </textarea></td>
</tr>
<tr><td colspan=4 class=line2></td></tr>
<tr><td colspan=4 class=ht></td></tr>


<tr class='ht'>
    <td colspan=4 align=left><?=subtitle("회원가입 설정")?></td>
</tr>
<tr><td colspan=4 class=line1></td></tr>
<tr class='ht'>
    <td>회원 스킨</td>
    <td colspan=3><select id=cf_member_skin name=cf_member_skin required itemname="회원가입 스킨">
        <?
        $arr = get_skin_dir("member");
        for ($i=0; $i<count($arr); $i++) {
            echo "<option value='$arr[$i]'>$arr[$i]</option>\n";
        }
        ?></select>
        <script type="text/javascript"> document.getElementById('cf_member_skin').value="<?=$config[cf_member_skin]?>";</script>
    </td>
</tr>
<tr class='ht'>
    <td>홈페이지 입력</td>
    <td>
        <input type='checkbox' name='cf_use_homepage' value='1' <?=$config[cf_use_homepage]?'checked':'';?>> 보이기
        <input type='checkbox' name='cf_req_homepage' value='1' <?=$config[cf_req_homepage]?'checked':'';?>> 필수입력
    </td>
    <td>주소 입력</td>
    <td>
        <input type='checkbox' name='cf_use_addr' value='1' <?=$config[cf_use_addr]?'checked':'';?>> 보이기
        <input type='checkbox' name='cf_req_addr' value='1' <?=$config[cf_req_addr]?'checked':'';?>> 필수입력
    </td>
</tr>
<tr class='ht'>
    <td>전화번호 입력</td>
    <td>
        <input type='checkbox' name='cf_use_tel' value='1' <?=$config[cf_use_tel]?'checked':'';?>> 보이기
        <input type='checkbox' name='cf_req_tel' value='1' <?=$config[cf_req_tel]?'checked':'';?>> 필수입력
    </td>
    <td>핸드폰 입력</td>
    <td>
        <input type='checkbox' name='cf_use_hp' value='1' <?=$config[cf_use_hp]?'checked':'';?>> 보이기
        <input type='checkbox' name='cf_req_hp' value='1' <?=$config[cf_req_hp]?'checked':'';?>> 필수입력
    </td>
</tr>
<tr class='ht'>
    <td>서명 입력</td>
    <td>
        <input type='checkbox' name='cf_use_signature' value='1' <?=$config[cf_use_signature]?'checked':'';?>> 보이기
        <input type='checkbox' name='cf_req_signature' value='1' <?=$config[cf_req_signature]?'checked':'';?>> 필수입력
    </td>
    <td>자기소개 입력</td>
    <td>
        <input type='checkbox' name='cf_use_profile' value='1' <?=$config[cf_use_profile]?'checked':'';?>> 보이기
        <input type='checkbox' name='cf_req_profile' value='1' <?=$config[cf_req_profile]?'checked':'';?>> 필수입력
    </td>
</tr>
<tr class='ht'>
    <td>회원가입시 권한</td>
    <td><? echo get_member_level_select('cf_register_level', 1, 9, $config[cf_register_level]) ?></td>
    <td>회원가입시 포인트</td>
    <td><input type=text class=ed name='cf_register_point' size='5' value='<?=$config[cf_register_point]?>'> 점</td>
</tr>
<tr class='ht'>
    <!-- <td>주민등록번호</td>
    <td><input type='checkbox' name='cf_use_jumin' value='1' <?=$config[cf_use_jumin]?'checked':'';?>> 사용
        <?=help("주민등록번호는 암호화하여 저장하므로 회원정보 DB가 유출되어도 알 수 없습니다.")?></td> -->
    <td>회원탈퇴후 삭제일</td>
    <td colspan="3"><input type=text class=ed name='cf_leave_day' size='5' value='<?=$config[cf_leave_day]?>'> 일 후 자동 삭제</td>
</tr>
<tr class='ht'>
    <td>회원아이콘 사용</td>
    <td>
        <select name='cf_use_member_icon'> 
        <option value='0'>미사용
        <option value='1'>아이콘만 표시
        <option value='2'>아이콘+이름 표시
        </select>
        <?=help("게시물에 게시자 별명 대신 아이콘 사용")?>
    </td>
    <script type='text/javascript'> document.fconfigform.cf_use_member_icon.value = '<?=$config[cf_use_member_icon]?>'; </script>
    <td>아이콘 업로드 권한</td>
    <td colspan=3><? echo get_member_level_select('cf_icon_level', 1, 9, $config[cf_icon_level]) ?> 이상</td>
</tr>
<tr class='ht'>
    <td>회원아이콘 용량</td>
    <td><input type=text class=ed name='cf_member_icon_size' size='5' value='<?=$config[cf_member_icon_size]?>'> 바이트 이하</td>
    <td>회원아이콘 사이즈</td>
    <td>폭 <input type=text class=ed name='cf_member_icon_width' size='5' value='<?=$config[cf_member_icon_width]?>'> 픽셀 , 높이 <input type=text class=ed name='cf_member_icon_height' size='5' value='<?=$config[cf_member_icon_height]?>'> 픽셀 이하</td>
</tr>
<tr class='ht'>
    <td>추천인제도 사용</td>
    <td><input type='checkbox' name='cf_use_recommend' value='1' <?=$config[cf_use_recommend]?'checked':'';?>> 사용</td>
    <td>추천인 포인트</td>
    <td><input type=text class=ed name='cf_recommend_point' size='5' value='<?=$config[cf_recommend_point]?>'> 점</td>
</tr>
<tr class='ht'>
    <td>아이디,별명 금지단어
        <?=help("입력된 단어가 포함된 내용은 회원아이디, 별명으로 사용할 수 없습니다.\n\n단어와 단어 사이는 , 로 구분합니다.")?></td>
    <td valign=top><textarea class=ed name='cf_prohibit_id' rows='5' style='width:99%;'><?=$config[cf_prohibit_id]?> </textarea></td>
    <td>입력 금지 메일
        <?=help("hanmail.net과 같은 메일 주소는 입력을 못합니다.\n\n엔터로 구분합니다.")?></td>
    <td valign=top><textarea class=ed name='cf_prohibit_email' rows='5' style='width:99%;'><?=$config[cf_prohibit_email]?> </textarea><br></td>
</tr>
<tr class='ht'>
    <td>회원가입약관</td>
    <td valign=top colspan=3><textarea class=ed name='cf_stipulation' rows='10' style='width:99%;'><?=$config[cf_stipulation]?> </textarea></td>
</tr>
<tr class='ht'>
    <td>개인정보취급방침</td>
    <td valign=top colspan=3><textarea class=ed name='cf_privacy' rows='10' style='width:99%;'><?=$config[cf_privacy]?> </textarea></td>
</tr>
<tr><td colspan=4 class=line2></td></tr>
<tr><td colspan=4 class=ht></td></tr>


<tr class='ht'>
    <td colspan=4 align=left><?=subtitle("메일 설정")?></td>
</tr>
<tr><td colspan=4 class=line1></td></tr>
<tr class='ht'>
    <td>메일발송 사용</td>
    <td colspan=3><input type=checkbox name=cf_email_use value='1' <?=$config[cf_email_use]?'checked':'';?>> 사용 (체크하지 않으면 메일발송을 아예 사용하지 않습니다. 메일 테스트도 불가합니다.)</td>
</tr>
<tr class='ht'>
    <td>메일인증 사용</td>
    <td><input type='checkbox' name='cf_use_email_certify' value='1' <?=$config[cf_use_email_certify]?'checked':'';?>> 사용
        <?=help("메일에 배달된 인증 주소를 클릭하여야 회원으로 인정합니다.");?></td>
</tr>
<tr class='ht'>
    <td>폼메일 사용 여부</td>
    <td><input type='checkbox' name='cf_formmail_is_member' value='1' <?=$config[cf_formmail_is_member]?'checked':'';?>> 회원만 사용
        <?=help("체크하지 않으면 비회원도 사용 할 수 있습니다.")?></td>
</tr>
<tr class='ht'>
    <td><span class=title>게시판 글 작성시</span></td>
</tr>
<tr class='ht'>
    <td>최고관리자 메일발송</td>
    <td colspan=3><input type=checkbox name=cf_email_wr_super_admin value='1' <?=$config[cf_email_wr_super_admin]?'checked':'';?>> 사용 (최고관리자에게 메일을 발송합니다.)</td>
</tr>
<tr class='ht'>
    <td>그룹관리자 메일발송</td>
    <td colspan=3><input type=checkbox name=cf_email_wr_group_admin value='1' <?=$config[cf_email_wr_group_admin]?'checked':'';?>> 사용 (그룹관리자에게 메일을 발송합니다.)</td>
</tr>
<tr class='ht'>
    <td>게시판관리자 메일발송</td>
    <td colspan=3><input type=checkbox name=cf_email_wr_board_admin value='1' <?=$config[cf_email_wr_board_admin]?'checked':'';?>> 사용 (게시판관리자에게 메일을 발송합니다.)</td>
</tr>
<tr class='ht'>
    <td>원글 메일발송</td>
    <td colspan=3><input type=checkbox name=cf_email_wr_write value='1' <?=$config[cf_email_wr_write]?'checked':'';?>> 사용 (게시자님께 메일을 발송합니다.)</td>
</tr>
<tr class='ht'>
    <td>코멘트 메일발송</td>
    <td colspan=3><input type=checkbox name=cf_email_wr_comment_all value='1' <?=$config[cf_email_wr_comment_all]?'checked':'';?>> 사용 (원글에 코멘트가 올라오는 경우 코멘트 쓴 모든 분들께 메일을 발송합니다.)</td>
</tr>
<tr class='ht'>
    <td><span class=title>회원 가입시</span></td>
</tr>
<tr class='ht'>
    <td>최고관리자 메일발송</td>
    <td colspan=3><input type=checkbox name=cf_email_mb_super_admin value='1' <?=$config[cf_email_mb_super_admin]?'checked':'';?>> 사용 (최고관리자에게 메일을 발송합니다.)</td>
</tr>
<tr class='ht'>
    <td>회원님께 메일발송</td>
    <td colspan=3><input type=checkbox name=cf_email_mb_member value='1' <?=$config[cf_email_mb_member]?'checked':'';?>> 사용 (회원가입한 회원님께 메일을 발송합니다.)</td>
</tr>
<tr class='ht'>
    <td><span class=title>투표 기타의견 작성시</span></td>
</tr>
<tr class='ht'>
    <td>최고관리자 메일발송</td>
    <td colspan=3><input type=checkbox name=cf_email_po_super_admin value='1' <?=$config[cf_email_po_super_admin]?'checked':'';?>> 사용 (최고관리자에게 메일을 발송합니다.)</td>
</tr>
<tr><td colspan=4 class=line2></td></tr>
<tr><td colspan=4 class=ht></td></tr>


<tr class='ht'>
    <td colspan=4 align=left><?=subtitle("여분 필드")?></td>
</tr>
<tr><td colspan=4 class=line1></td></tr>
<? for ($i=1; $i<=10; $i=$i+2) { $k=$i+1; ?>
<tr class='ht'>
    <td><input type=text class=ed name='cf_<?=$i?>_subj' value='<?=get_text($config["cf_{$i}_subj"])?>' title='여분필드 <?=$i?> 제목' style='text-align:right;font-weight:bold;' size=15></td>
    <td><input type='text' class=ed style='width:99%;' name=cf_<?=$i?> value='<?=$config["cf_$i"]?>' title='여분필드 <?=$i?> 설정값'></td>
    <td><input type=text class=ed name='cf_<?=$k?>_subj' value='<?=get_text($config["cf_{$k}_subj"])?>' title='여분필드 <?=$k?> 제목' style='text-align:right;font-weight:bold;' size=15></td>
    <td><input type='text' class=ed style='width:99%;' name=cf_<?=$k?> value='<?=$config["cf_$k"]?>' title='여분필드 <?=$k?> 설정값'></td>
</tr>
<? } ?>
<tr><td colspan=4 class=line2></td></tr>
<tr><td colspan=4 class=ht></td></tr>


<tr class='ht'>
    <td colspan=4 align=left>
        <?=subtitle("XSS / CSRF 방지")?>
    </td>
</tr>
<tr><td colspan=4 class=line1></td></tr>
<tr class='ht'>
    <td>
        관리자 패스워드
    </td>
    <td colspan=3>
        <input class='ed' type='password' name='admin_password' itemname="관리자 패스워드" required>
        <?=help("관리자 권한을 빼앗길 것에 대비하여 로그인한 관리자의 패스워드를 한번 더 묻는것 입니다.");?>
    </td>
</tr>
<tr><td colspan=4 class=line2></td></tr>
<tr><td colspan=4 class=ht></td></tr>
</table>

<p align=center>
    <input type=submit class=btn1 accesskey='s' value='  확  인  '>
</form>

<script type="text/javascript">
function fconfigform_submit(f)
{
    f.action = "./config_form_update.php";
    return true;
}
</script>

<?
include_once ("./admin.tail.php");
?>

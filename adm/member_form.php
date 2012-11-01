<?
$sub_menu = "200100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$token = get_token();

if ($w == '')
{
    $required_mb_id = 'required minlength="3" alphanumericunderline id="회원아이디" name="회원아이디"';
    $required_mb_password = 'required id="패스워드" name="패스워드"';

    $mb[mb_mailling] = 1;
    $mb[mb_open] = 1;
    $mb[mb_level] = $config[cf_register_level];
    $html_title = '등록';
}
else if ($w == 'u')
{
    $mb = get_member($mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.'); 

    if ($is_admin != 'super' && $mb[mb_level] >= $member[mb_level])
        alert('자신보다 권한이 높거나 같은 회원은 수정할 수 없습니다.');

    $required_mb_id = 'readonly';
    $required_mb_password = '';
    $html_title = '수정';

    $mb['mb_email'] = get_text($mb['mb_email']);
    $mb['mb_homepage'] = get_text($mb['mb_homepage']);
    $mb['mb_password_q'] = get_text($mb['mb_password_q']);
    $mb['mb_password_a'] = get_text($mb['mb_password_a']);
    $mb['mb_birth'] = get_text($mb['mb_birth']);
    $mb['mb_tel'] = get_text($mb['mb_tel']);
    $mb['mb_hp'] = get_text($mb['mb_hp']);
    $mb['mb_addr1'] = get_text($mb['mb_addr1']);
    $mb['mb_addr2'] = get_text($mb['mb_addr2']);
    $mb['mb_signature'] = get_text($mb['mb_signature']);
    $mb['mb_recommend'] = get_text($mb['mb_recommend']);
    $mb['mb_profile'] = get_text($mb['mb_profile']);
    $mb['mb_1'] = get_text($mb['mb_1']);
    $mb['mb_2'] = get_text($mb['mb_2']);
    $mb['mb_3'] = get_text($mb['mb_3']);
    $mb['mb_4'] = get_text($mb['mb_4']);
    $mb['mb_5'] = get_text($mb['mb_5']);
    $mb['mb_6'] = get_text($mb['mb_6']);
    $mb['mb_7'] = get_text($mb['mb_7']);
    $mb['mb_8'] = get_text($mb['mb_8']);
    $mb['mb_9'] = get_text($mb['mb_9']);
    $mb['mb_10'] = get_text($mb['mb_10']);
}
else
    alert('제대로 된 값이 넘어오지 않았습니다.');

if ($mb[mb_mailling]) $mailling_checked = 'checked'; // 메일 수신
if ($mb[mb_sms]) $sms_checked = 'checked'; // SMS 수신
if ($mb[mb_open]) $open_checked = 'checked'; // 정보 공개

$g4['title'] = '회원정보 ' . $html_title;
include_once("./admin.head.php");
?>

<h2><span></span>회원정보 입력</h2>

<form id="fmember" name="fmember" method="post" onsubmit="return fmember_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" id="w" name="w" value="<?=$w?>">
<input type="hidden" id="sfl" name="sfl" value="<?=$sfl?>">
<input type="hidden" id="stx" name="stx" value="<?=$stx?>">
<input type="hidden" id="sst" name="sst" value="<?=$sst?>">
<input type="hidden" id="sod" name="sod" value="<?=$sod?>">
<input type="hidden" id="page" name="page" value="<?=$page?>">
<input type="hidden" id="token" name="token" value="<?=$token?>">

<table>
<caption>
회원의 사이트 기본정보 <?=$html_title?>
</caption>
<tbody>
<tr>
    <th scope="row" id="th101"><label for="mb_id">아이디</label></th>
    <td headers="th101">
        <input type="text" id="mb_id" name="mb_id" maxlength="20" minlength="2" <?=$required_mb_id?> value='<?=$mb['mb_id'] ?>'>
        <?if ($w=="u"){?><a href='./boardgroupmember_form.php?mb_id=<?=$mb['mb_id']?>'>접근가능그룹보기</a><?}?>
    </td>
</tr>
<tr>
    <th scope="row" id="th102"><label for="mb_password">패스워드</label></th>
    <td headers="th102"><input type="password" id="mb_password" name="mb_password" maxlength="20" <?=$required_mb_password?>></td>
</tr>
<tr>
    <th scope="row" id="th103"><label for="mb_level">회원 권한</label></th>
    <td headers="th103"><?=get_member_level_select("mb_level", 1, $member[mb_level], $mb[mb_level])?></td>
</tr>
<tr>
    <th scope="row" id="th104">포인트</th>
    <td headers="th104"><a href="./point_list.php?sfl=mb_id&amp;stx=<?=$mb['mb_id']?>"><?=number_format($mb[mb_point])?></a> 점</td>
</tr>
</tbody>
</table>

<table>
<caption>
회원의 개인정보를 <?=$html_title?>
</caption>
<tbody>
<tr>
    <th scope="row" id="th201"><label for="mb_name">이름(실명)</label></th>
    <td headers="th201"><input type="text" id="mb_name" name="mb_name" maxlength="20" minlength="2" required value="<?=$mb['mb_name']?>"></td>
</tr>
<tr>
    <th scope="row" id="th202"><label for="mb_nick">별명</label></th>
    <td headers="th202"><input type="text" id="mb_nick" name="mb_nick" maxlength="20" minlength="2" required value="<?=$mb['mb_nick']?>"></td>
</tr>
<tr>
    <th scope="row" id="th203"><label for="mb_birth">생년월일</label></th>
    <td headers="th203"><input type="text" id="mb_birth" name="mb_birth" maxlength="8" value='<? echo $mb['mb_birth'] ?>'></td>
</tr>
<tr>
    <th scope="row" id="th204"><label for="mb_sex">성별</label></th>
    <td headers="th204">
        <select id="mb_sex" name="mb_sex">
            <option value=''>선택</option>
            <option value="F">여자</option>
            <option value="M">남자</option>
        </select>
        <script> document.fmember.mb_sex.value = "<?=$mb['mb_sex']?>"; </script>
    </td>
</tr>
<tr>
    <th scope="row" id="th205"><label for="mb_email">E-mail</label></th>
    <td headers="th205"><input type="text" id="mb_email" name="mb_email" maxlength="100" required id="e-mail" name="e-mail" value="<?=$mb['mb_email'] ?>"></td>
</tr>
<tr>
    <th scope="row" id="th206"><label for="mb_homepage">홈페이지</label></th>
    <td headers="th206"><input type="text" id="mb_homepage" name="mb_homepage" maxlength="255" value="<?=$mb['mb_homepage']?>"></td>
</tr>
<tr>
    <th scope="row" id="th207"><label for="mb_tel">전화번호</label></th>
    <td headers="th207"><input type="text" id="mb_tel" name="mb_tel" maxlength="20" value="<?=$mb['mb_tel']?>"></td>
</tr>
<tr>
    <th scope="row" id="th208"><label for="mb_hp">핸드폰번호</label></th>
    <td headers="th208"><input type="text" id="mb_hp" name="mb_hp" maxlength="20" value="<?=$mb['mb_hp']?>"></td>
</tr>
<tr>
    <th scope="row" id="th209">주소</th>
    <td headers="th209">
        <input type="text" id="mb_zip1" name="mb_zip1" maxlength="3" readonly value="<?=$mb[mb_zip1]?>" title="우편번호 앞자리"> -
        <input type="text" id="mb_zip2" name="mb_zip2" maxlength="3" readonly value="<?=$mb[mb_zip2]?>" title="우편번호 뒷자리">
        <a href="javascript:;" onclick="win_zip('fmember', 'mb_zip1', 'mb_zip2', 'mb_addr1', 'mb_addr2');">우편번호 검색</a>
        <input type="text" id="mb_addr1" name="mb_addr1" readonly value="<?=$mb['mb_addr1'] ?>" title="기본주소">
        <label for="mb_addr2" class="visibility_hidden_label">상세주소</label>
        <input type="text" id="mb_addr2" name="mb_addr2" value="<?=$mb['mb_addr2']?>"> 상세주소 입력
    </td>
</tr>
<tr>
    <th scope="row" id="th210"><label for="mb_signature">서명</label></th>
    <td headers="th210"><textarea id="mb_signature" name="mb_signature"><? echo $mb['mb_signature'] ?></textarea></td>
</tr>
<tr>
    <th scope="row" id="th211"><label for="mb_icon">회원아이콘</label></th>
    <td headers="th211">
        <input type="file" id="mb_icon" name="mb_icon">
        이미지 크기는 <?=$config[cf_member_icon_width]?>x<?=$config[cf_member_icon_height]?>으로 해주세요.
        <?
        $mb_dir = substr($mb['mb_id'],0,2);
        $icon_file = $g4['path'].'/data/member/'.$mb_dir.'/'.$mb['mb_id'].'.gif';
        if (file_exists($icon_file)) {
            echo '<img src="$icon_file">';
            echo '<input type="checkbox" id="del_mb_icon" name="del_mb_icon" value="1">삭제';
        }
        ?>
    </td>
</tr>
<tr>
    <th scope="row" id="th212"><label for="mb_profile">자기 소개</label></th>
    <td headers="th212"><textarea id="mb_profile" name="mb_profile"><? echo $mb['mb_profile'] ?></textarea></td>
</tr>
<tr>
    <th scope="row" id="th213"><label for="mb_memo">메모</label></th>
    <td headers="th213"><textarea id="mb_memo" name="mb_memo"><? echo $mb[mb_memo] ?></textarea></td>
</tr>
</tbody>
</table>

<table>
<caption>
회원의 정보 수신 및 공개 여부 <?=$html_title?>
</caption>
<tbody>
<tr>
    <th scope="row" id="th301"><label for="mb_mailing">메일 수신</label></th>
    <td headers="th301"><input type="checkbox" id="mb_mailling" name="mb_mailling" value="1" <?=$mailling_checked?>> 정보 메일을 받음</td>
</tr>
<tr>
    <th scope="row" id="th302"><label for="mb_sms">SMS 수신</label></th>
    <td headers="th302"><input type="checkbox" id="mb_sms" name="mb_sms" value="1" <?=$sms_checked?>> 문자메세지를 받음</td>
</tr>
<tr>
    <th scope="row" id="th302"><label for="mb_open">정보 공개</label></th>
    <td headers="th302"><input type="checkbox" id="mb_open" name="mb_open" value="1" <?=$open_checked?>> 타인에게 자신의 정보를 공개</td>
</tr></tbody>
</table>

<table>
<caption>회원의 사이트 이용상태 확인 (혹은 <?=$html_title?>)</caption>
<tbody>
<? if ($w == "u") { ?>
<tr>
    <th scope="row" id="th401">회원가입일</th>
    <td headers="th401"><?=$mb[mb_datetime]?></td>
</tr>
<tr>
    <th scope="row" id="th402">최근접속일</th>
    <td headers="th402"><?=$mb[mb_today_login]?></td>
</tr>
<tr>
    <th scope="row" id="th403">IP</th>
    <td headers="th403"><?=$mb[mb_ip]?></td>
</tr>
<? if ($config[cf_use_email_certify]) { ?>
<tr>
    <th scope="row" id="th404"><? if ($mb['mb_email_certify'] == "0000-00-00 00:00:00") {?><label for="passive_certify">인증일시</label><?}else{?>인증일시<?}?></th>
    <td headers="th404">
        <?=$mb['mb_email_certify']?>
        <? if ($mb['mb_email_certify'] == "0000-00-00 00:00:00") { echo '<input type="checkbox" id="passive_certify" name="passive_certify">수동인증'; } ?>
    </td>
</tr>
<? } ?>
<? } ?>
<? if ($config[cf_use_recommend]) { // 추천인 사용 ?>
<tr>
    <th scope="row" id="th405">추천인</th>
    <td headers="th405"><?=($mb['mb_recommend'] ? get_text($mb['mb_recommend']) : "없음"); // 081022 : CSRF 보안 결함으로 인한 코드 수정 ?></td>
</tr>
<? } ?>
<tr>
    <th scope="row" id="th406"><label for="mb_leave_date">탈퇴일자</label></th>
    <td headers="th406"><input type="text" id="mb_leave_date" name="mb_leave_date" maxlength="8" value="<?=$mb['mb_leave_date']?>"></td>
</tr>
<tr>
    <th scope="row" id="th407"><label for="mb_intercept_date">접근차단일자</label></th>
    <td headers="th407">
        <input type="text" id="mb_intercept_date" name="mb_intercept_date" maxlength="8" value="<?=$mb['mb_intercept_date']?>">
        <input type="checkbox" id="mb_intercept_today" name="mb_intercept_today" value='<? echo date("Ymd"); ?>' onclick='if (this.form.mb_intercept_date.value==this.form.mb_intercept_date.defaultValue) { this.form.mb_intercept_date.value="this".value; } else { this.form.mb_intercept_date.value="this".form.mb_intercept_date.defaultValue; } '>
        <label for="mb_intercept_today">오늘</label>
    </td>
</tr></tbody>
</table>

<table>
<caption>
회원과 관련되어 미리 정의된 추가사항 <?=$html_title?>
</caption>
<tbody>
<? for ($i=1; $i<=10; $i=$i+2) { $k=$i+1; ?>
<tr>
    <th scope="row" id="th5<?=$i?>"><label for="mb_<?=$i?>">여분 필드 <?=$i?></label></th>
    <td headers="th5<?=$i?>"><input type="text" id="mb_<?=$i?>" name="mb_<?=$i?>" maxlength="255" value="<?=$mb['mb_{$i}']?>"></td>
    <th scope="row" id="th5<?=$k?>"><label for="mb_<?=$k?>">여분 필드 <?=$k?></label></th>
    <td headers="th5<?=$k?>"><input type="text" id="mb_<?=$k?>" name="mb_<?=$k?>" maxlength="255" value="<?=$mb['mb_{$k}']?>"></td>
</tr>
<? } ?>
</tbody>
</table>


<fieldset>
<legend><span></span>XSS/CSRF 방지 관리자 패스워드 확인</legend>
<p>관리자 권한을 빼앗길 것에 대비하여 로그인한 관리자의 패스워드를 한번 더 묻는것 입니다.</p>
<label for="admin_password">관리자 패스워드</label>
<input type="password" id="admin_password" name="admin_password" name="관리자 패스워드" required>
<input type="submit" accesskey="s" value="확인">
<input type="button" value="목록" onclick="document.location.href='./member_list.php?<?=$qstr?>';">
<? if ($w != '') { ?>
<input type="button" value="삭제" onclick="del('./member_delete.php?<?=$qstr?>&amp;w=d&amp;mb_id=<?=$mb['mb_id']?>&amp;url=<?=$_SERVER['PHP_SELF']?>');"> 
<? } ?>
</fieldset>

</form>

<script type='text/javascript'>
if (document.fmember.w.value == "")
    document.fmember.mb_id.focus();
else if (document.fmember.w.value == "u")
    document.fmember.mb_password.focus();

if (typeof(document.fmember.mb_level) != "undefined") 
    document.fmember.mb_level.value   = "<?=$mb[mb_level]?>"; 

function fmember_submit(f)
{
    if (!f.mb_icon.value.match(/\.(gif|jp[e]g|png)$/i) && f.mb_icon.value) {
        alert('아이콘이 이미지 파일이 아닙니다. (bmp 제외)');
        return false;
    }

    f.action = './member_form_update.php';
    return true;
}
</script>

<?
include_once("./admin.tail.php");
?>

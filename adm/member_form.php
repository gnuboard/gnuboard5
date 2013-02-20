<?
$sub_menu = "200100";
include_once('./_common.php');

auth_check($auth[$sub_menu], 'w');

$token = get_token();

if ($w == '')
{
    $required_mb_id = 'class="required minlength=3 alnum_"';
    $required_mb_password = 'class="required"';
    $sound_only = '<strong class="sound_only">필수</strong>';

    $mb['mb_mailling'] = 1;
    $mb['mb_open'] = 1;
    $mb['mb_level'] = $config['cf_register_level'];
    $html_title = '추가';
}
else if ($w == 'u')
{
    $mb = get_member($mb_id);
    if (!$mb['mb_id'])
        alert('존재하지 않는 회원자료입니다.');

    if ($is_admin != 'super' && $mb['mb_level'] >= $member['mb_level'])
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

$mailling_no_checked = '';
$sms_no_checked = '';
$open_no_checked = '';
if ($mb['mb_mailling'] == 1) {
    $mailling_checked = 'checked="checked"'; //메일수신
} else {
    $mailing_checked = '';
    $mailling_no_checked = 'checked="checked"';
}

if ($mb['mb_sms']) {
    $sms_checked = 'checked="checked"'; // SMS 수신
} else {
    $sms_checked = '';
    $sms_no_checked = 'checked="checked"';
}

if ($mb['mb_open']) {
    $open_checked = 'checked="checked"'; // 정보 공개
} else {
    $open_checked = '';
    $open_no_checked = 'checked="checked"';
}

if ($mb['mb_intercept_date']) $g4['title'] = "차단된 ";
else $g4['title'] .= "";
$g4['title'] .= '회원 '.$html_title;
include_once('./admin.head.php');
?>

<form id="fmember" name="fmember" method="post" action="./member_form_update.php" onsubmit="return fmember_submit(this);" enctype="multipart/form-data">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="sfl" value="<?=$sfl?>">
<input type="hidden" name="stx" value="<?=$stx?>">
<input type="hidden" name="sst" value="<?=$sst?>">
<input type="hidden" name="sod" value="<?=$sod?>">
<input type="hidden" name="page" value="<?=$page?>">
<input type="hidden" name="token" value="<?=$token?>">

<div class="cbox">
    <table class="frm_tbl">
    <colgroup>
        <col class="grid_3">
        <col class="grid_6">
        <col class="grid_3">
        <col class="grid_6">
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="mb_id">아이디<?=$sound_only?></label></th>
        <td>
            <input type="text" id="mb_id" name="mb_id" maxlength="20" class="frm_input" <?=$required_mb_id?> value="<?=$mb['mb_id']?>" size="15">
            <?if ($w=='u'){?><a href="./boardgroupmember_form.php?mb_id=<?=$mb['mb_id']?>">접근가능그룹보기</a><?}?>
        </td>
        <th scope="row"><label for="mb_password">패스워드<?=$sound_only?></label></th>
        <td><input type="password" id="mb_password" name="mb_password" class="frm_input" maxlength="20" <?=$required_mb_password?> size="15"></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_name">이름(실명)<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="mb_name" name="mb_name" maxlength="20" class="required hangul frm_input minlength=2" required value="<?=$mb['mb_name']?>" size="15"></td>
        <th scope="row"><label for="mb_nick">별명<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="mb_nick" name="mb_nick" maxlength="20" class="required frm_input minlength=2" required value="<?=$mb['mb_nick']?>" size="15"></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_level">회원 권한</label></th>
        <td><?=get_member_level_select('mb_level', 1, $member['mb_level'], $mb['mb_level'])?></td>
        <th scope="row">포인트</th>
        <td><a href="./point_list.php?sfl=mb_id&amp;stx=<?=$mb['mb_id']?>" target="_blank"><?=number_format($mb['mb_point'])?></a> 점</td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_email">E-mail<strong class="sound_only">필수</strong></label></th>
        <td><input type="text" id="mb_email" name="mb_email" maxlength="100" class="required frm_input email" required value="<?=$mb['mb_email']?>" size="30"></td>
        <th scope="row"><label for="mb_homepage">홈페이지</label></th>
        <td><input type="text" id="mb_homepage" name="mb_homepage" class="frm_input" maxlength="255" value="<?=$mb['mb_homepage']?>" size="15"></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_tel">전화번호</label></th>
        <td><input type="text" id="mb_tel" name="mb_tel" class="frm_input" maxlength="20" value="<?=$mb['mb_tel']?>" size="15"></td>
        <th scope="row"><label for="mb_hp">핸드폰번호</label></th>
        <td><input type="text" id="mb_hp" name="mb_hp" class="frm_input" maxlength="20" value="<?=$mb['mb_hp']?>" size="15"></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_zip1">주소</label></th>
        <td colspan="3" style="line-height:2em">
            <input type="text" id="mb_zip1" name="mb_zip1" class="frm_input readonly" maxlength="3" readonly value="<?=$mb['mb_zip1']?>" title="우편번호 앞자리" size="3"> -
            <input type="text" id="mb_zip2" name="mb_zip2" class="frm_input readonly" maxlength="3" readonly value="<?=$mb['mb_zip2']?>" title="우편번호 뒷자리" size="3">
            <a href="<?=G4_BBS_URL.'/zip.php?frm_name=fmember&amp;frm_zip1=mb_zip1&amp;frm_zip2=mb_zip2&amp;frm_addr1=mb_addr1&amp;frm_addr2=mb_addr2'?>" class="win_zip_find btn_frmline">우편번호 검색</a><br>
            <input type="text" id="mb_addr1" name="mb_addr1" class="frm_input readonly" readonly value='<?=$mb['mb_addr1']?>' title="행정기본주소" size="50"><br>
            <input type="text" id="mb_addr2" name="mb_addr2" class="frm_input" value="<?=$mb['mb_addr2']?>" title="상세주소" size="50"> 상세주소 입력
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_icon">회원아이콘</label></th>
        <td colspan="3">
            <?=help('이미지 크기는 <strong>넓이 '.$config['cf_member_icon_width'].'픽셀 높이 '.$config['cf_member_icon_height'].'픽셀</strong>로 해주세요.')?>
            <input type="file" id="mb_icon" name="mb_icon">
            <?
            $mb_dir = substr($mb['mb_id'],0,2);
            $icon_file = G4_DATA_PATH.'/member/'.$mb_dir.'/'.$mb['mb_id'].'.gif';
            if (file_exists($icon_file)) {
                $icon_url = G4_DATA_URL.'/member/'.$mb_dir.'/'.$mb['mb_id'].'.gif';
                echo '<img src="'.$icon_url.'">';
                echo '<input type="checkbox" id="del_mb_icon" name="del_mb_icon" value="1">삭제';
            }
            ?>
        </td>
    </tr>
    <tr>
        <th scope="row">메일 수신</th>
        <td>
            <input type="radio" id="mb_mailling_yes" name="mb_mailling" value="1" <?=$mailling_checked?>>
            <label for="mb_mailling_yes">예</label>
            <input type="radio" id="mb_mailling_no" name="mb_mailling" value="0" <?=$mailling_no_checked?>>
            <label for="mb_mailling_no">아니오</label>
        </td>
        <th scope="row"><label for="mb_sms_yes">SMS 수신</label></th>
        <td>
            <input type="radio" id="mb_sms_yes" name="mb_sms" value="1" <?=$sms_checked?>>
            <label for="mb_sms_yes">예</label>
            <input type="radio" id="mb_sms_no" name="mb_sms" value="0" <?=$sms_no_checked?>>
            <label for="mb_sms_no">아니오</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_open">정보 공개</label></th>
        <td colspan="3">
            <input type="radio" id="mb_open" name="mb_open" value="1" <?=$open_checked?>>
            <label for="mb_open">예</label>
            <input type="radio" id="mb_open_no" name="mb_open" value="0" <?=$open_no_checked?>>
            <label for="mb_open_no">아니오</label>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_signature">서명</label></th>
        <td colspan="3"><textarea id="mb_signature" name="mb_signature"><?=$mb['mb_signature']?></textarea></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_profile">자기 소개</label></th>
        <td colspan="3"><textarea id="mb_profile" name="mb_profile"><?=$mb['mb_profile']?></textarea></td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_memo">메모</label></th>
        <td colspan="3"><textarea id="mb_memo" name="mb_memo"><?=$mb['mb_memo']?></textarea></td>
    </tr>

    <? if ($w == 'u') { ?>
    <tr>
        <th scope="row">회원가입일</th>
        <td><?=$mb['mb_datetime']?></td>
        <th scope="row">최근접속일</th>
        <td><?=$mb['mb_today_login']?></td>
    </tr>
    <tr>
        <th scope="row">IP</th>
        <td colspan="3"><?=$mb['mb_ip']?></td>
    </tr>
    <? if ($config['cf_use_email_certify']) { ?>
    <tr>
        <th scope="row">인증일시</th>
        <td colspan="3">
            <? if ($mb['mb_email_certify'] == '0000-00-00 00:00:00') { ?>
            <?=help('회원님이 메일을 수신할 수 없는 경우 등에 직접 인증처리를 하실 수 있습니다.')?>
            <?=$mb['mb_email_certify']?>
            <input type="checkbox" id="passive_certify" name="passive_certify">
            <label>수동인증</label>
            <? } else { ?>
            <?=$mb['mb_email_certify']?>
            <? } ?>
        </td>
    </tr>
    <? } ?>
    <? } ?>

    <? if ($config['cf_use_recommend']) { // 추천인 사용 ?>
    <tr>
        <th scope="row">추천인</th>
        <td colspan="3"><?=($mb['mb_recommend'] ? get_text($mb['mb_recommend']) : '없음'); // 081022 : CSRF 보안 결함으로 인한 코드 수정 ?></td>
    </tr>
    <? } ?>

    <tr>
        <th scope="row">탈퇴일자</th>
        <td>
            <input type="text" name="mb_leave_date" class="frm_input" maxlength="8" value="<?=$mb['mb_leave_date']?>">
            <input type="checkbox" value="<?=date("Ymd"); ?>" onclick="if (this.form.mb_leave_date.value==this.form.mb_leave_date.defaultValue) { this.form.mb_leave_date.value=this.value; } else { this.form.mb_leave_date.value=this.form.mb_leave_date.defaultValue; }" title="탈퇴일을 오늘로 지정"> 오늘
        </td>
        <th scope="row">접근차단일자</th>
        <td>
            <input type="text" name="mb_intercept_date" class="frm_input" maxlength="8" value="<?=$mb['mb_intercept_date']?>">
            <input type="checkbox" value="<?=date("Ymd"); ?>" onclick="if (this.form.mb_intercept_date.value==this.form.mb_intercept_date.defaultValue) { this.form.mb_intercept_date.value=this.value; } else { this.form.mb_intercept_date.value=this.form.mb_intercept_date.defaultValue; }" title="접근차단일을 오늘로 지정"> 오늘
        </td>
    </tr>

    <? for ($i=1; $i<=10; $i++) { ?>
    <tr>
        <th scope="row"><label for="mb_<?=$i?>">여분 필드 <?=$i?></label></th>
        <td colspan="3"><input type="text" id="mb_<?=$i?>" name="mb_<?=$i?>" class="frm_input" maxlength="255" value="<?=$mb['mb_'.$i]?>" size="30"></td>
    </tr>
    <? } ?>

    </tbody>
    </table>

</div>

<fieldset id="admin_confirm">
    <legend>XSS 혹은 CSRF 방지</legend>
    <p>관리자 권한을 탈취 당하는 경우를 대비하여 관리자의 패스워드를 다시 한번 확인합니다.</p>
    <label for="admin_password">관리자 패스워드<strong class="sound_only">필수</strong></label>
    <input type="password" id="admin_password" name="admin_password" class="required frm_input" required>
</fieldset>

<div class="btn_confirm">
    <input type="submit" class="btn_submit" accesskey='s' value="확인">
    <a href="./member_list.php?<?=$qstr?>">목록</a>
</div>
</form>

<script>
function fmember_submit(f)
{
    if (!f.mb_icon.value.match(/\.(gif|jp['e']g|png)$/i) && f.mb_icon.value) {
        alert('아이콘이 이미지 파일이 아닙니다. (bmp 제외)');
        return false;
    }

    return true;
}
</script>

<?
include_once('./admin.tail.php');
?>

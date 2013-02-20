<?
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<script src="<?=G4_JS_URL?>/jquery.register_form.js"></script>

<form id="fregisterform" name="fregisterform" method="post" action="<?=$register_action_url?>" onsubmit="return fregisterform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="url" value="<?=$urlencode?>">
<input type="hidden" name="agree" value="<?=$agree?>">
<input type="hidden" name="agree2" value="<?=$agree2?>">
<? if (isset($member['mb_sex'])) { ?><input type="hidden" name="mb_sex" value="<?=$member['mb_sex']?>"><? } ?>
<? if (isset($member['mb_nick_date']) && $member['mb_nick_date'] <= date("Y-m-d", G4_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) { // 별명수정일이 지나지 않았다면 ?>
<input type="hidden" name="mb_nick_default" value="<?=$member['mb_nick']?>">
<input type="hidden" name="mb_nick" value="<?=$member['mb_nick']?>">
<? } ?>

<table class="frm_tbl">
<caption>사이트 이용정보 입력</caption>
<tr>
    <th scope="row"><label for="reg_mb_id">아이디<strong class="sound_only">필수</strong></label></th>
    <td>
        <input type="text" id="reg_mb_id" name="mb_id" class="frm_input minlength_3 <?=$required?> <?=$readonly?>" value="<?=$member['mb_id']?>" maxlength="20" <?=$required?> <?=$readonly?>>
        <span id="msg_mb_id"></span>
        <span class="frm_info">영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.</span>
    </td>
</tr>
<tr>
    <th scope="row"><label for="reg_mb_password">패스워드<strong class="sound_only">필수</strong></label></th>
    <td><input type="password" id="reg_mb_password" name="mb_password" class="frm_input minlength_3 <?=$required?>" maxlength="20" <?=$required?>></td>
</tr>
<tr>
    <th scope="row"><label for="reg_mb_password_re">패스워드 확인<strong class="sound_only">필수</strong></label></th>
    <td><input type="password" id="reg_mb_password_re" name="mb_password_re" class="frm_input minlength_3 <?=$required?>" maxlength="20" <?=$required?>></td>
</tr>
</table>

<table class="frm_tbl">
<caption>개인정보 입력</caption>
<tr>
    <th scope="row"><label for="reg_mb_name">이름<strong class="sound_only">필수</strong></label></th>
    <td>
        <input id="reg_mb_name" name="mb_name" class="frm_input hangul nospace <?=$required?> <?=$readonly?>" value="<?=$member['mb_name']?>" size="10" <?=$required?> <?=$readonly?>>
        <? if ($w=='') { echo "<span class=\"frm_info\">공백없이 한글만 입력하세요.</span>"; } ?>
    </td>
</tr>
<? if ($req_nick) { ?>
<tr>
    <th scope="row"><label for="reg_mb_nick">별명<strong class="sound_only">필수</strong></label></th>
    <td>
        <input type="hidden" name="mb_nick_default" value="<?=isset($member['mb_nick'])?$member['mb_nick']:'';?>">
        <input type="text" id="reg_mb_nick" name="mb_nick" class="frm_input required nospace" maxlength="20" size="10" value="<?=isset($member['mb_nick'])?$member['mb_nick']:'';?>" required>
        <span id="msg_mb_nick"></span>
        <span class="frm_info">
            공백없이 한글,영문,숫자만 입력 가능 (한글2자, 영문4자 이상)<br>
            별명을 바꾸시면 앞으로 <?=(int)$config['cf_nick_modify']?>일 이내에는 변경 할 수 없습니다.
        </span>
    </td>
</tr>
<? } ?>

<tr>
    <th scope="row"><label for="reg_mb_email">E-mail<? if ($config['cf_use_email_certify']) {?><strong class="sound_only">필수</strong><?}?></label></th>
    <td>
        <input type="hidden" name="old_email" value="<?=$member['mb_email']?>">
        <input type="text" id="reg_mb_email" name="mb_email" class="frm_input email <?=$config['cf_use_email_certify']?"required":"";?>" maxlength="100" size="50" value='<?=isset($member['mb_email'])?$member['mb_email']:'';?>' <?=$config['cf_use_email_certify']?"required":"";?>>
        <? if ($config['cf_use_email_certify']) { ?>
        <span class="frm_info">
            <? if ($w=='') { echo "E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; } ?>
            <? if ($w=='u') { echo "E-mail 주소를 변경하시면 다시 인증하셔야 합니다."; } ?>
        </span>
        <? } ?>
    </td>
</tr>

<? if ($config['cf_use_homepage']) { ?>
<tr>
    <th scope="row"><label for="reg_mb_homepage">홈페이지<? if ($config['cf_req_homepage']){?><strong class="sound_only">필수</strong><?}?></label></th>
    <td><input type="text" id="reg_mb_homepage" name="mb_homepage" class="frm_input <?=$config['cf_req_homepage']?"required":"";?>" maxlength="255" size="50" <?=$config['cf_req_homepage']?"required":"";?> value="<?=$member['mb_homepage']?>"></td>
</tr>
<? } ?>

<? if ($config['cf_use_tel']) { ?>
<tr>
    <th scope="row"><label for="reg_mb_tel">전화번호<? if ($config['cf_req_tel']) {?><strong class="sound_only">필수</strong><?}?></label></th>
    <td><input type="text" id="reg_mb_tel" name="mb_tel" class="frm_input <?=$config['cf_req_tel']?"required":"";?>" maxlength="20" <?=$config['cf_req_tel']?"required":"";?> value="<?=$member['mb_tel']?>"></td>
</tr>
<? } ?>

<? if ($config['cf_use_hp']) { ?>
<tr>
    <th scope="row"><label for="reg_mb_hp">핸드폰번호<? if ($config['cf_req_hp']) {?><strong class="sound_only">필수</strong><?}?></label></th>
    <td><input type="text" id="reg_mb_hp" name="mb_hp" class="frm_input <?=$config['cf_req_hp']?"required":"";?>" maxlength="20" <?=$config['cf_req_hp']?"required":"";?> value="<?=$member[mb_hp]?>"></td>
</tr>
<? } ?>

<? if ($config['cf_use_addr']) {
    $zip_href = G4_BBS_URL.'/zip.php?frm_name=fregisterform&amp;frm_zip1=mb_zip1&amp;frm_zip2=mb_zip2&amp;frm_addr1=mb_addr1&amp;frm_addr2=mb_addr2';
?>
<tr>
    <th scope="row">
        주소
        <? if ($config['cf_req_addr']) {?><strong class="sound_only">필수</strong><? } ?>
    </th>
    <td>
        <input type="text" id="reg_mb_zip1" name="mb_zip1" class="frm_input <?=$config['cf_req_addr']?"required":"";?>" size="2" maxlength="3" <?=$config['cf_req_addr']?"required":"";?> value="<?=$member['mb_zip1']?>" title="우편번호 앞자리">
         -
        <input type="text" id="reg_mb_zip2" name="mb_zip2" class="frm_input <?=$config['cf_req_addr']?"required":"";?>" size="2" maxlength="3" <?=$config['cf_req_addr']?"required":"";?> value="<?=$member['mb_zip2']?>" title="우편번호 뒷자리">
        <a href="<? echo $zip_href; ?>" id="reg_zip_find" class="btn_frmline win_zip_find" target="_blank">주소찾기</a>
        <input type="text" id="reg_mb_addr1" name="mb_addr1" class="frm_input frm_address <?=$config['cf_req_addr']?"required":"";?>" size="50" <?=$config['cf_req_addr']?"required":"";?> value="<?=$member['mb_addr1']?>" title="행정구역주소">
        <input type="text" id="reg_mb_addr2" name="mb_addr2" class="frm_input frm_address <?=$config['cf_req_addr']?"required":"";?>" size="50" <?=$config['cf_req_addr']?"required":"";?> value="<?=$member['mb_addr2']?>" title="상세주소">
    </td>
</tr>
<? } ?>
</table>

<table class="frm_tbl">
<caption>기타 개인설정</caption>
<? if ($config['cf_use_signature']) { ?>
<tr>
    <th scope="row"><label for="reg_mb_signature">서명<? if ($config['cf_req_signature']){?><strong class="sound_only">필수</strong><?}?></label></th>
    <td><textarea id="reg_mb_signature" name="mb_signature" class="<?=$config['cf_req_signature']?"required":"";?>" <?=$config['cf_req_signature']?"required":"";?>><?=$member['mb_signature']?></textarea></td>
</tr>
<? } ?>

<? if ($config['cf_use_profile']) { ?>
<tr>
    <th scope="row"><label for="reg_mb_profile">자기소개</label></th>
    <td><textarea id="reg_mb_profile" name="mb_profile" class="<?=$config['cf_req_profile']?"required":"";?>" <?=$config['cf_req_profile']?"required":"";?>><?=$member['mb_profile']?></textarea></td>
</tr>
<? } ?>

<? if ($member['mb_level'] >= $config['cf_icon_level']) { ?>
<tr>
    <th scope="row"><label for="reg_mb_icon">회원아이콘</label></th>
    <td>
        <input type="file" id="reg_mb_icon" name="mb_icon" class="frm_input">
        <? if ($w == 'u' && file_exists($mb_icon)) { ?>
        <input type="checkbox" id="del_mb_icon" name="del_mb_icon" value="1">
        <label for="del_mb_icon">삭제</label>
        <? } ?>
        <span class="frm_info">
            이미지 크기는 가로 <?=$config['cf_member_icon_width']?>픽셀, 세로 <?=$config['cf_member_icon_height']?>픽셀 이하로 해주세요.<br>
            gif만 가능하며 용량 <?=number_format($config['cf_member_icon_size'])?>바이트 이하만 등록됩니다.
        </span>
    </td>
</tr>
<? } ?>

<tr>
    <th scope="row"><label for="reg_mb_mailling">메일링서비스</label></th>
    <td>
        <input type="checkbox" id="reg_mb_mailling" name="mb_mailling" value="1" <?=($w=='' || $member['mb_mailling'])?'checked':'';?>>
        정보 메일을 받겠습니다.
    </td>
</tr>

<? if ($config['cf_use_hp']) { ?>
<tr>
    <th scope="row"><label for="reg_mb_sms">SMS 수신여부</label></th>
    <td>
        <input type="checkbox" id="reg_mb_sms" name="mb_sms" value="1" <?=($w=='' || $member['mb_sms'])?'checked':'';?>>
        핸드폰 문자메세지를 받겠습니다.
    </td>
</tr>
<? } ?>

<? if (isset($member['mb_open_date']) && $member['mb_open_date'] <= date("Y-m-d", G4_SERVER_TIME - ($config['cf_open_modify'] * 86400)) || empty($member['mb_open_date'])) { // 정보공개 수정일이 지났다면 수정가능 ?>
<tr>
    <th scope="row"><label for="reg_mb_open">정보공개</label></th>
    <td>
        <input type="hidden" name="mb_open_default" value="<?=$member['mb_open']?>">
        <input type="checkbox" id="reg_mb_open" name="mb_open" value="1" <?=($w=='' || $member['mb_open'])?'checked':'';?>>
        다른분들이 나의 정보를 볼 수 있도록 합니다.
        <span class="frm_info">
            정보공개를 바꾸시면 앞으로 <?=(int)$config['cf_open_modify']?>일 이내에는 변경이 안됩니다.
        </span>
    </td>
</tr>
<? } else { ?>
<tr>
    <th scope="row">정보공개</th>
    <td>
        <input type="hidden" name="mb_open" value="<?=$member['mb_open']?>">
        <span class="frm_info">
            정보공개는 수정후 <?=(int)$config['cf_open_modify']?>일 이내, <?=date("Y년 m월 j일", isset($member['mb_open_date']) ? strtotime("{$member['mb_open_date']} 00:00:00")+$config['cf_open_modify']*86400:G4_SERVER_TIME+$config['cf_open_modify']*86400);?> 까지는 변경이 안됩니다.<br>
            이렇게 하는 이유는 잦은 정보공개 수정으로 인하여 쪽지를 보낸 후 받지 않는 경우를 막기 위해서 입니다.
        </span>
    </td>
</tr>
<? } ?>

<? if ($w == "" && $config['cf_use_recommend']) { ?>
<tr>
    <th scope="row"><label for="reg_mb_recommend">추천인아이디</label></th>
    <td><input type="text" id="reg_mb_recommend" name="mb_recommend" class="frm_input"></td>
</tr>
<? } ?>

<tr>
    <th scope="row">자동등록방지</th>
    <td><?=$captcha_html?></td>
</tr>
</table>

<div class="btn_confirm">
    <input type="submit" class="btn_submit" value="<?=$w==''?'회원가입':'정보수정';?>" accesskey="s">
    <a href="<?=$g4['path']?>/" class="btn_cancel">취소</a>
</div>
</form>

<script>
$(function() {
    $("#reg_zip_find").css("display", "inline-block");
    $("#reg_mb_zip1, #reg_mb_zip2, #reg_mb_addr1").attr("readonly", true);
});

// submit 최종 폼체크
function fregisterform_submit(f)
{
    // 회원아이디 검사
    if (f.w.value == "") {
        var msg = reg_mb_id_check();
        if (msg) {
            alert(msg);
            f.mb_id.select();
            return false;
        }
    }

    if (f.w.value == '') {
        if (f.mb_password.value.length < 3) {
            alert('패스워드를 3글자 이상 입력하십시오.');
            f.mb_password.focus();
            return false;
        }
    }

    if (f.mb_password.value != f.mb_password_re.value) {
        alert('패스워드가 같지 않습니다.');
        f.mb_password_re.focus();
        return false;
    }

    if (f.mb_password.value.length > 0) {
        if (f.mb_password_re.value.length < 3) {
            alert('패스워드를 3글자 이상 입력하십시오.');
            f.mb_password_re.focus();
            return false;
        }
    }

    // 이름 검사
    if (f.w.value=='') {
        if (f.mb_name.value.length < 1) {
            alert('이름을 입력하십시오.');
            f.mb_name.focus();
            return false;
        }

        var pattern = /([^가-힣\x20])/i;
        if (pattern.test(f.mb_name.value)) {
            alert('이름은 한글로 입력하십시오.');
            f.mb_name.select();
            return false;
        }
    }

    // 별명 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
        var msg = reg_mb_nick_check();
        if (msg) {
            alert(msg);
            f.reg_mb_nick.select();
            return false;
        }
    }

    // E-mail 검사
    if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
        var msg = reg_mb_email_check();
        if (msg) {
            alert(msg);
            f.reg_mb_email.select();
            return false;
        }
    }

    if (typeof f.mb_icon != 'undefined') {
        if (f.mb_icon.value) {
            if (!f.mb_icon.value.toLowerCase().match(/.(gif)$/i)) {
                alert('회원아이콘이 gif 파일이 아닙니다.');
                f.mb_icon.focus();
                return false;
            }
        }
    }

    if (typeof(f.mb_recommend) != 'undefined') {
        if (f.mb_id.value == f.mb_recommend.value) {
            alert('본인을 추천할 수 없습니다.');
            f.mb_recommend.focus();
            return false;
        }
    }

    <? echo chk_captcha_js(); ?>

    return true;
}
</script>

<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<script>
var member_skin_path = "<?=$member_skin_path?>";
</script>
<script src="<?=$member_skin_path?>/ajax_register_form.jquery.js"></script>
<script src="<?=$g4[path]?>/js/md5.js"></script>
<script src="<?=$g4[path]?>/js/sideview.js"></script>

<form id="fregisterform" name="fregisterform" method="post" onsubmit="return fregisterform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type="hidden" name="w" value="<?=$w?>">
<input type="hidden" name="url" value="<?=$urlencode?>">
<input type="hidden" name="mb_jumin" value="<?=$jumin?>">
<input type="hidden" id="mb_id_enabled" name="mb_id_enabled" value="">
<input type="hidden" id="mb_nick_enabled" name="mb_nick_enabled" value="">
<input type="hidden" id="mb_email_enabled" name="mb_email_enabled" value="">
<!-- <input type="hidden" name="token" value="<?=$token?>"> -->
<? if ($member[mb_sex]) { ?><input type="hidden" name="mb_sex" value="<?=$member[mb_sex]?>"><? } ?>
<? if ($member[mb_nick_date] <= date("Y-m-d", $g4[server_time] - ($config[cf_nick_modify] * 86400))) { // 별명수정일이 지나지 않았다면 ?>
<input type="hidden" name="mb_nick_default" value="<?=$member[mb_nick]?>">
<input type="hidden" name="mb_nick" value="<?=$member[mb_nick]?>">
<? } ?>

<table>
<caption>사이트 이용정보 입력</caption>
<tr>
    <th scope="row"><label for="reg_mb_id">아이디</label></th>
    <td>
        <input type="text" id="reg_mb_id" name="mb_id" value="<?=$member[mb_id]?>" maxlength="20" <? if ($w=='u') { echo "readonly style='background-color:#dddddd;'"; } ?> <? if ($w=='') { echo "onblur='reg_mb_id_check();'"; } ?>>
        <span id="msg_mb_id"></span>
        <span>영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.</span>
    </td>
</tr>
<tr>
    <th scope="row"><label for="mb_password">패스워드</label></th>
    <td><input type="password" id="reg_mb_password" name="mb_password" maxlength="20" <?=($w=="")?"required":"";?>></td>
</tr>
<tr>
    <th scope="row"><label for="reg_mb_password_re">패스워드 확인</label></th>
    <td><input type="password" id="reg_mb_password_re" name="mb_password_re" maxlength="20" <?=($w=="")?"required":"";?>></td>
</tr>
</table>

<table>
<caption>개인정보 입력</caption>
<tr>
    <th scope="row"><label for="reg_mb_name">이름</label></th>
    <td>
        <input id="reg_mb_name" name="mb_name" value="<?=$member[mb_name]?>" <?=$member[mb_name]?"readonly2":"";?>>
        <? if ($w=='') { echo "(공백없이 한글만 입력 가능)"; } ?>
    </td>
</tr>
<? if ($member[mb_nick_date] <= date("Y-m-d", $g4[server_time] - ($config[cf_nick_modify] * 86400))) { // 별명수정일이 지났다면 수정가능 ?>
<tr>
    <th scope="row"><label for="reg_mb_nick">별명</label></th>
    <td>
        <input type="hidden" name="mb_nick_default" value="<?=$member[mb_nick]?>">
        <input type="text" id="reg_mb_nick" name="mb_nick" maxlength="20" value="<?=$member[mb_nick]?>" onblur="reg_mb_nick_check();">
        <span id="msg_mb_nick"></span>
        <br>공백없이 한글,영문,숫자만 입력 가능 (한글2자, 영문4자 이상)
        <br>별명을 바꾸시면 앞으로 <?=(int)$config[cf_nick_modify]?>일 이내에는 변경 할 수 없습니다.
    </td>
</tr>
<? } ?>

<tr>
    <th scope="row"><label for="reg_mb_email">E-mail</label></th>
    <td>
        <input type="hidden" name="old_email" value="<?=$member[mb_email]?>">
        <input type="text" id="reg_mb_email" name="mb_email" maxlength="100" value='<?=$member[mb_email]?>' onblur="reg_mb_email_check()">
        <span id="msg_mb_email"></span>
        <? if ($config[cf_use_email_certify]) { ?>
            <? if ($w=='') { echo "<br>e-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; } ?>
            <? if ($w=='u') { echo "<br>e-mail 주소를 변경하시면 다시 인증하셔야 합니다."; } ?>
        <? } ?>
    </td>
</tr>

<? if ($w=="") { ?>
<tr>
    <th scope="row"><label for="reg_mb_birth">생년월일</label></th>
    <td><input type="text" id="reg_mb_birth" name="mb_birth" maxlength="8" required value="<?=$member[mb_birth]?>"></td>
</tr>
<? } ?>

<? if (!$member[mb_sex]) { ?>
<tr>
    <th scope="row"><label for="reg_mb_sex">성별</label></th>
    <td>
        <select id="reg_mb_sex" name="mb_sex" required>
            <option value="">선택하세요</option>
            <option value="F">여자</option>
            <option value="M">남자</option>
        </select>
        <script>//document.getElementById('mb_sex').value='<?=$member[mb_sex]?>';</script>
    </td>
</tr>
<? } ?>

<? if ($config[cf_use_homepage]) { ?>
<tr>
    <th scope="row"><label for="reg_mb_homepage">홈페이지</label></th>
    <td><input type="text" id="reg_mb_homepage" name="mb_homepage" maxlength="255" <?=$config[cf_req_homepage]?'required':'';?> value="<?=$member[mb_homepage]?>"></td>
</tr>
<? } ?>

<? if ($config[cf_use_tel]) { ?>
<tr>
    <th scope="row"><label for="reg_mb_tel">전화번호</label></th>
    <td><input type="text" id="reg_mb_tel" name="mb_tel" maxlength="20" <?=$config[cf_req_tel]?'required':'';?> value="<?=$member[mb_tel]?>"></td>
</tr>
<? } ?>

<? if ($config[cf_use_hp]) { ?>
<tr>
    <th scope="row"><label for="reg_mb_hp">핸드폰번호</label></th>
    <td><input type="text" id="reg_mb_hp" name="mb_hp" maxlength="20" <?=$config[cf_req_hp]?'required':'';?> value="<?=$member[mb_hp]?>"></td>
</tr>
<? } ?>

<? if ($config[cf_use_addr]) { ?>
<tr>
    <th scope="row">주소</th>
    <td>
        <input type="text" id="reg_mb_zip1" name="mb_zip1" maxlength="3" readonly <?=$config[cf_req_addr]?'required':'';?> value="<?=$member[mb_zip1]?>" title="우편번호 앞자리">
         -
        <input type="text" id="reg_mb_zip2" name="mb_zip2" maxlength="3" readonly <?=$config[cf_req_addr]?'required':'';?> value="<?=$member[mb_zip2]?>" title="우편번호 뒷자리">
        <a href="javascript:;" onclick="win_zip('fregisterform', 'mb_zip1', 'mb_zip2', 'mb_addr1', 'mb_addr2');">주소찾기</a>
        <input type="text" id="reg_mb_addr1" name="mb_addr1" readonly <?=$config[cf_req_addr]?'required':'';?> value="<?=$member[mb_addr1]?>" title="행정구역주소">
        <input type="text" id="reg_mb_addr2" name="mb_addr2" <?=$config[cf_req_addr]?'required':'';?> value="<?=$member[mb_addr2]?>" title="상세주소">
    </td>
</tr>
<? } ?>
</table>

<table>
<caption>기타 개인설정</caption>
<? if ($config[cf_use_signature]) { ?>
<tr>
    <th scope="row"><label for="reg_mb_signature">서명</label></th>
    <td><textarea id="reg_mb_signature" name="mb_signature" <?=$config[cf_req_signature]?'required':'';?>><?=$member[mb_signature]?></textarea></td>
</tr>
<? } ?>

<? if ($config[cf_use_profile]) { ?>
<tr>
    <th scope="row"><label for="reg_mb_profile">자기소개</label></th>
    <td><textarea id="reg_mb_profile" name="mb_profile" <?=$config[cf_req_profile]?'required':'';?>><?=$member[mb_profile]?></textarea></td>
</tr>
<? } ?>

<? if ($member[mb_level] >= $config[cf_icon_level]) { ?>
<tr>
    <th scope="row"><label for="reg_mb_icon">회원아이콘</label></th>
    <td>
        <input type="file" id="reg_mb_icon" name="mb_icon">
        이미지 크기는 가로 <?=$config[cf_member_icon_width]?>픽셀, 세로 <?=$config[cf_member_icon_height]?>픽셀 이하로 해주세요.<br>
        gif만 가능 / 용량:<?=number_format($config[cf_member_icon_size])?>바이트 이하만 등록됩니다.)
        <? if ($w == 'u' && file_exists($mb_icon)) { ?>
        <input type="checkbox" id="del_mb_icon" name="del_mb_icon" value="1"> 삭제
        <? } ?>
    </td>
</tr>
<? } ?>

<tr>
    <th scope="row"><label for="reg_mb_mailling">메일링서비스</label></th>
    <td><input type="checkbox" id="reg_mb_mailling" name="mb_mailling" value="1" <?=($w=='' || $member[mb_mailling])?'checked':'';?>>정보 메일을 받겠습니다.</td>
</tr>
<tr>
    <th scope="row"><label for="reg_mb_sms">SMS 수신여부</label></th>
    <td><input type="checkbox" id="reg_mb_sms" name="mb_sms" value="1" <?=($w=='' || $member[mb_sms])?'checked':'';?>>핸드폰 문자메세지를 받겠습니다.</td>
</tr>

<? if ($member[mb_open_date] <= date("Y-m-d", $g4[server_time] - ($config[cf_open_modify] * 86400))) { // 정보공개 수정일이 지났다면 수정가능 ?>
<tr>
    <th scope="row"><label for="reg_mb_open">정보공개</label></th>
    <td>
        <input type="hidden" name="mb_open_default" value="<?=$member[mb_open]?>">
        <input type="checkbox" id="reg_mb_open" name="mb_open" value="1" <?=($w=='' || $member[mb_open])?'checked':'';?>>다른분들이 나의 정보를 볼 수 있도록 합니다.<br>
        정보공개를 바꾸시면 앞으로 <?=(int)$config[cf_open_modify]?>일 이내에는 변경이 안됩니다.
    </td>
</tr>
<? } else { ?>
<tr>
    <th scope="row">정보공개</th>
    <td>
        <input type="hidden" name="mb_open" value="<?=$member[mb_open]?>">
        정보공개는 수정후 <?=(int)$config[cf_open_modify]?>일 이내, <?=date("Y년 m월 j일", strtotime("$member[mb_open_date] 00:00:00") + ($config[cf_open_modify] * 86400))?> 까지는 변경이 안됩니다.<br>
        이렇게 하는 이유는 잦은 정보공개 수정으로 인하여 쪽지를 보낸 후 받지 않는 경우를 막기 위해서 입니다.
    </td>
</tr>
<? } ?>

<? if ($w == "" && $config[cf_use_recommend]) { ?>
<tr>
    <th scope="row"><label for="reg_mb_recommend">추천인아이디</label></th>
    <td><input type="text" id="reg_mb_recommend" name="mb_recommend"></td>
</tr>
<? } ?>
</table>

<fieldset>
    <legend>자동등록방지</legend>
    <img id="kcaptcha_image">
    <input type="text" id="wr_key" name="wr_key" required>
    왼쪽의 글자를 입력하세요.
</fieldset>

<div class="btn_confirm">
    <input type="submit" value="회원가입" accesskey="s">
</div>
</form>

<script src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script>
// submit 최종 폼체크
function fregisterform_submit(f) 
{
    // 회원아이디 검사
    if (f.w.value == "") {

        reg_mb_id_check();

        if (document.getElementById('mb_id_enabled').value!='000') {
            alert('회원아이디를 입력하지 않았거나 입력에 오류가 있습니다.');
            document.getElementById('reg_mb_id').select();
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

    /*
    if (f.mb_password_q.value.length < 1) {
        alert('패스워드 분실시 질문을 선택하거나 입력하십시오.');
        f.mb_password_q.focus();
        return false;
    }

    if (f.mb_password_a.value.length < 1) {
        alert('패스워드 분실시 답변을 입력하십시오.');
        f.mb_password_a.focus();
        return false;
    }
    */

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
            f.mb_name.focus();
            return false;
        }
    }

    // 별명 검사
    if ((f.w.value == "") ||
        (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {

        reg_mb_nick_check();

        if (document.getElementById('mb_nick_enabled').value!='000') {
            alert('별명을 입력하지 않았거나 입력에 오류가 있습니다.');
            document.getElementById('reg_mb_nick').select();
            return false;
        }
    }

    // E-mail 검사
    if ((f.w.value == "") ||
        (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {

        reg_mb_email_check();

        if (document.getElementById('mb_email_enabled').value!='000') {
            alert('E-mail을 입력하지 않았거나 입력에 오류가 있습니다.');
            document.getElementById('reg_mb_email').select();
            return false;
        }

        // 사용할 수 없는 E-mail 도메인
        var domain = prohibit_email_check(f.mb_email.value);
        if (domain) {
            alert("'"+domain+"'은(는) 사용하실 수 없는 메일입니다.");
            document.getElementById('reg_mb_email').focus();
            return false;
        }
    }

    if (typeof(f.mb_birth) != 'undefined') {
        if (f.mb_birth.value.length < 1) {
            alert('달력 버튼을 클릭하여 생일을 입력하여 주십시오.');
            //f.mb_birth.focus();
            return false;
        }

        var todays = <?=date("Ymd", $g4['server_time']);?>;
        // 오늘날짜에서 생일을 빼고 거기서 140000 을 뺀다.
        // 결과가 0 이상의 양수이면 만 14세가 지난것임
        var n = todays - parseInt(f.mb_birth.value) - 140000;
        if (n < 0) {
            alert("만 14세가 지나지 않은 어린이는 정보통신망 이용촉진 및 정보보호 등에 관한 법률\n\n제 31조 1항의 규정에 의하여 법정대리인의 동의를 얻어야 하므로\n\n법정대리인의 이름과 연락처를 '자기소개'란에 별도로 입력하시기 바랍니다.");
            return false;
        }
    }

    if (typeof(f.mb_sex) != 'undefined') {
        if (f.mb_sex.value == '') {
            alert('성별을 선택하여 주십시오.');
            f.mb_sex.focus();
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

    if (!check_kcaptcha(f.wr_key)) {
        return false;
    }

    <?
    if ($g4[https_url])
        echo "f.action = '$g4[https_url]/$g4[bbs]/register_form_update.php';";
    else
        echo "f.action = './register_form_update.php';";
    ?>

    // 보안인증관련 코드로 반드시 포함되어야 합니다.
    set_cookie("<?=md5($token)?>", "<?=base64_encode($token)?>", 1, "<?=$g4['cookie_domain']?>");

    return true;
}

// 금지 메일 도메인 검사
function prohibit_email_check(email)
{
    email = email.toLowerCase();

    var prohibit_email = "<?=trim(strtolower(preg_replace("/(\r\n|\r|\n)/", ",", $config[cf_prohibit_email])));?>";
    var s = prohibit_email.split(",");
    var tmp = email.split("@");
    var domain = tmp[tmp.length - 1]; // 메일 도메인만 얻는다

    for (i=0; i<s.length; i++) {
        if (s[i] == domain)
            return domain;
    }
    return "";
}
</script>

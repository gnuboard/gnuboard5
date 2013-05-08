<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<?
//==============================================================================
// jquery date picker
//------------------------------------------------------------------------------
// 참고) ie 에서는 년, 월 select box 를 두번씩 클릭해야 하는 오류가 있습니다.
//------------------------------------------------------------------------------
// jquery-ui.css 의 테마를 변경해서 사용할 수 있습니다.
// base, black-tie, blitzer, cupertino, dark-hive, dot-luv, eggplant, excite-bike, flick, hot-sneaks, humanity, le-frog, mint-choc, overcast, pepper-grinder, redmond, smoothness, south-street, start, sunny, swanky-purse, trontastic, ui-darkness, ui-lightness, vader
// 아래 css 는 date picker 의 화면을 맞추는 코드입니다.
?>

<link type="text/css" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/themes/base/jquery-ui.css" rel="stylesheet" />
<style type="text/css">
<!--
.ui-datepicker { font:12px dotum; }
.ui-datepicker select.ui-datepicker-month, 
.ui-datepicker select.ui-datepicker-year { width: 70px;}
.ui-datepicker-trigger { margin:0 0 -5px 2px; }
-->
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.4/jquery-ui.min.js"></script>
<script type="text/javascript">
/* Korean initialisation for the jQuery calendar extension. */
/* Written by DaeKwon Kang (ncrash.dk@gmail.com). */
jQuery(function($){
	$.datepicker.regional['ko'] = {
		closeText: '닫기',
		prevText: '이전달',
		nextText: '다음달',
		currentText: '오늘',
		monthNames: ['1월(JAN)','2월(FEB)','3월(MAR)','4월(APR)','5월(MAY)','6월(JUN)',
		'7월(JUL)','8월(AUG)','9월(SEP)','10월(OCT)','11월(NOV)','12월(DEC)'],
		monthNamesShort: ['1월','2월','3월','4월','5월','6월',
		'7월','8월','9월','10월','11월','12월'],
		dayNames: ['일','월','화','수','목','금','토'],
		dayNamesShort: ['일','월','화','수','목','금','토'],
		dayNamesMin: ['일','월','화','수','목','금','토'],
		weekHeader: 'Wk',
		dateFormat: 'yymmdd',
		firstDay: 0,
		isRTL: false,
		showMonthAfterYear: true,
		yearSuffix: ''};
	$.datepicker.setDefaults($.datepicker.regional['ko']);

    $('#mb_birth').datepicker({
        showOn: 'button',
		buttonImage: '<?=$g4[path]?>/img/calendar.gif',
		buttonImageOnly: true,
        buttonText: "달력",
        changeMonth: true,
		changeYear: true,
        showButtonPanel: true,
        yearRange: 'c-99:c+99',
        maxDate: '+0d'
    }); 
});
</script>
<?
//==============================================================================
?>

<style type="text/css">
<!--
.m_title    { BACKGROUND-COLOR: #F7F7F7; PADDING-LEFT: 15px; PADDING-top: 5px; PADDING-BOTTOM: 5px; }
.m_padding  { PADDING-LEFT: 15px; PADDING-BOTTOM: 5px; PADDING-TOP: 5px; }
.m_padding2 { PADDING-LEFT: 0px; PADDING-top: 5px; PADDING-BOTTOM: 0px; }
.m_padding3 { PADDING-LEFT: 0px; PADDING-top: 5px; PADDING-BOTTOM: 5px; }
.m_text     { BORDER: #D3D3D3 1px solid; HEIGHT: 18px; BACKGROUND-COLOR: #ffffff; }
.m_text2    { BORDER: #D3D3D3 1px solid; HEIGHT: 18px; BACKGROUND-COLOR: #dddddd; }
.m_textarea { BORDER: #D3D3D3 1px solid; BACKGROUND-COLOR: #ffffff; WIDTH: 100%; WORD-BREAK: break-all; }
.w_message  { font-family:돋움; font-size:9pt; color:#4B4B4B; }
.w_norobot  { font-family:돋움; font-size:9pt; color:#BB4681; }
.w_hand     { cursor:pointer; }
.mb_id      { ime-mode:disabled; }
-->
</style>

<script language="javascript" src="<?=$g4[path]?>/js/sideview.js"></script>

<table width=600 cellspacing=0 cellspacing=0 align=center>
<form name=fregisterform method=post onsubmit="return fregisterform_submit(this);" enctype="multipart/form-data" autocomplete="off">
<input type=hidden name=w                value="<?=$w?>">
<input type=hidden name=url              value="<?=$urlencode?>">
<input type=hidden name=mb_jumin         value="<?=$jumin?>">
<input type=hidden name=mb_id_enabled    value="" id="mb_id_enabled">
<input type=hidden name=mb_nick_enabled  value="" id="mb_nick_enabled">
<input type=hidden name=mb_email_enabled value="" id="mb_email_enabled">
<tr><td>


<img src="<?=$member_skin_path?>/img/join_form_title.gif" width="624" height="72">

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100%>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>아이디</TD>
            <TD class=m_padding>
                <INPUT class="m_text mb_id" maxLength=20 size=20 name="mb_id" minlength="3" alphanumericunderline itemname="아이디" required value="<?=$member[mb_id]?>" <?= ($w == '') ? 'required' : "readonly style='background-color:#dddddd;'";?> onchange="fregisterform.mb_id_enabled.value='';">
                &nbsp;<? if ($w == "") { ?><a href="javascript:mb_id_check();"><img width="70" height="20" src="<?=$member_skin_path?>/img/join_check_btn.gif" border=0 align=absmiddle></a><? } ?>
                <table height=25 cellspacing=0 cellpadding=0 border=0>
                <tr><td><font color="#66A2C8">※ 영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.</font></td></tr>
                </table>
            </TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>패스워드</TD>
            <TD class=m_padding><INPUT class=m_text type=password name="mb_password" size=20 maxlength=20 <?=($w=="")?"required":"";?> itemname="패스워드"></TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>패스워드 확인</TD>
            <TD class=m_padding><INPUT class=m_text type=password name="mb_password_re" size=20 maxlength=20 <?=($w=="")?"required":"";?> itemname="패스워드 확인"></TD>
        </TR>
        <!--
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>패스워드 분실시 질문</TD>
            <TD class=m_padding>
                <select name=mb_password_q_select onchange="this.form.mb_password_q.value=this.value;">
                    <option value="">선택하십시오.</option>
                    <option value="내가 좋아하는 캐릭터는?">내가 좋아하는 캐릭터는?</option>
                    <option value="타인이 모르는 자신만의 신체비밀이 있다면?">타인이 모르는 자신만의 신체비밀이 있다면?</option>
                    <option value="자신의 인생 좌우명은?">자신의 인생 좌우명은?</option>
                    <option value="초등학교 때 기억에 남는 짝꿍 이름은?">초등학교 때 기억에 남는 짝꿍 이름은?</option>
                    <option value="유년시절 가장 생각나는 친구 이름은?">유년시절 가장 생각나는 친구 이름은?</option>
                    <option value="가장 기억에 남는 선생님 성함은?">가장 기억에 남는 선생님 성함은?</option>
                    <option value="친구들에게 공개하지 않은 어릴 적 별명이 있다면?">친구들에게 공개하지 않은 어릴 적 별명이 있다면?</option>
                    <option value="추억하고 싶은 날짜가 있다면?(예:1994/04/20)">추억하고 싶은 날짜가 있다면?(예:1994/04/20)</option>
                    <option value="다시 태어나면 되고 싶은 것은?">다시 태어나면 되고 싶은 것은?</option>
                    <option value="가장 감명깊게 본 영화는?">가장 감명깊게 본 영화는?</option>
                    <option value="읽은 책 중에서 좋아하는 구절이 있다면?">읽은 책 중에서 좋아하는 구절이 있다면?</option>
                    <option value="기억에 남는 추억의 장소는?">기억에 남는 추억의 장소는?</option>
                    <option value="인상 깊게 읽은 책 이름은?">인상 깊게 읽은 책 이름은?</option>
                    <option value="자신의 보물 제1호는?">자신의 보물 제1호는?</option>
                    <option value="받았던 선물 중 기억에 남는 독특한 선물은?">받았던 선물 중 기억에 남는 독특한 선물은?</option>
                    <option value="자신이 두번째로 존경하는 인물은?">자신이 두번째로 존경하는 인물은?</option>
                    <option value="아버지의 성함은?">아버지의 성함은?</option>
                    <option value="어머니의 성함은?">어머니의 성함은?</option>
                    <option value="가장 여행하고 싶은 나라는?">가장 여행하고 싶은 나라는?</option>
                </select>

                <table width="350" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td class=m_padding2><input class=m_text type=text name="mb_password_q" size=55 required itemname="패스워드 분실시 질문" value="<?=$member[mb_password_q]?>"></td>
                </tr>
                </table>
            </TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>패스워드 분실시 답변</TD>
            <TD class=m_padding><input class=m_text type=text name='mb_password_a' size=38 required itemname='패스워드 분실시 답변' value='<?=$member[mb_password_a]?>'></TD>
        </TR>
        -->
        </TABLE>
    </td>
</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td height="1" bgcolor="#ffffff"></td>
</tr>
</table>

<table width="100%" cellspacing="0" cellpadding="0">
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100%>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>이름</TD>
            <TD class=m_padding>
                <!-- 한글만 입력받을 경우 <INPUT name=mb_name itemname="이름" required minlength="2" nospace hangul value="<?=$member[mb_name]?>" <?=$member[mb_name]?"readonly class=m_text2":"class=m_text";?>> -->
                <INPUT name=mb_name itemname="이름" required minlength="2" nospace value="<?=$member[mb_name]?>" <?=$member[mb_name]?"readonly class=m_text2":"class=m_text";?>>
            </TD>
        </TR>

        <? if ($member[mb_nick_date] <= date("Y-m-d", $g4[server_time] - ($config[cf_nick_modify] * 86400))) { // 별명수정일이 지났다면 수정가능 ?>
        <input type=hidden name=mb_nick_default value='<?=$member[mb_nick]?>'>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>별명</TD>
            <TD class='m_padding lh'>
                <input class=m_text type=text name='mb_nick' maxlength=20 minlength="2" required itemname="별명" value='<?=$member[mb_nick]?>' onchange="fregisterform.mb_nick_enabled.value='';">
                &nbsp;<a href="javascript:mb_nick_check();"><img width="70" height="20" src="<?=$member_skin_path?>/img/join_check_btn.gif" border=0 align=absmiddle></a>
                <br>별명을 바꾸시면 앞으로 <?=(int)$config[cf_nick_modify]?>일 이내에는 변경이 안됩니다.
            </TD>
        </TR>
        <? } else { ?>
        <input type=hidden name="mb_nick" value="<?=$member[mb_nick]?>">
        <? } ?>

        <input type=hidden name='old_email' value='<?=$member[mb_email]?>'>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>E-mail</TD>
            <TD class='m_padding lh'>
                <INPUT class=m_text type=text name='mb_email' size=38 maxlength=100 email required itemname='E-mail' value='<?=$member[mb_email]?>'>
                &nbsp;<a href="javascript:mb_email_check();"><img width="70" height="20" src="<?=$member_skin_path?>/img/join_check_btn.gif" border=0 align=absmiddle></a>
                <? if ($config[cf_use_email_certify]) { ?>
                    <? if ($w=='') { echo "<br>E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; } ?>
                    <? if ($w=='u') { echo "<br>E-mail 주소를 변경하시면 다시 인증하셔야 합니다."; } ?>
                <? } ?>
            </TD>
        </TR>

        <? if ($w=="") { ?>
            <TR bgcolor="#FFFFFF">
                <TD class=m_title>생년월일</TD>
                <TD class=m_padding><input class=m_text type=text id=mb_birth name='mb_birth' size=8 maxlength=8 minlength=8 required numeric itemname='생년월일' value='<?=$member[mb_birth]?>' readonly title='옆의 달력 아이콘을 클릭하여 날짜를 입력하세요.'></TD>
            </TR>
        <? } else { ?>
            <input type=hidden name=mb_birth value='<?=$member[mb_birth]?>'>
        <? } ?>

        <? if ($member[mb_sex]) { ?>
            <input type=hidden name=mb_sex value='<?=$member[mb_sex]?>'>
        <? } else { ?>
            <TR bgcolor="#FFFFFF">
                <TD class=m_title>성별</TD>
                <TD class=m_padding>
                    <select id=mb_sex name=mb_sex required itemname='성별'>
                    <option value=''>선택하세요
                    <option value='F'>여자
                    <option value='M'>남자
                    </select>
                    <script language="JavaScript">//document.getElementById('mb_sex').value='<?=$member[mb_sex]?>';</script>
                    </td>
            </TR>
        <? } ?>

        <? if ($config[cf_use_homepage]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>홈페이지</TD>
            <TD class=m_padding><input class=m_text type=text name='mb_homepage' size=38 maxlength=255 <?=$config[cf_req_homepage]?'required':'';?> itemname='홈페이지' value='<?=$member[mb_homepage]?>'></TD>
        </TR>
        <? } ?>

        <? if ($config[cf_use_tel]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>전화번호</TD>
            <TD class=m_padding><input class=m_text type=text name='mb_tel' size=21 maxlength=20 <?=$config[cf_req_tel]?'required':'';?> itemname='전화번호' value='<?=$member[mb_tel]?>'></TD>
        </TR>
        <? } ?>

        <? if ($config[cf_use_hp]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>핸드폰번호</TD>
            <TD class=m_padding><input class=m_text type=text name='mb_hp' size=21 maxlength=20 <?=$config[cf_req_hp]?'required':'';?> itemname='핸드폰번호' value='<?=$member[mb_hp]?>'></TD>
        </TR>
        <? } ?>

        <? if ($config[cf_use_addr]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD class=m_title>주소</TD>
            <TD valign="middle" class=m_padding>
                <table width="330" border="0" cellspacing="0" cellpadding="0">
                <tr>
                    <td height="25"><input class=m_text type=text name='mb_zip1' size=4 maxlength=3 readonly <?=$config[cf_req_addr]?'required':'';?> itemname='우편번호 앞자리' value='<?=$member[mb_zip1]?>'>
                         - 
                        <input class=m_text type=text name='mb_zip2' size=4 maxlength=3 readonly <?=$config[cf_req_addr]?'required':'';?> itemname='우편번호 뒷자리' value='<?=$member[mb_zip2]?>'>
                        &nbsp;<a href="javascript:;" onclick="win_zip('fregisterform', 'mb_zip1', 'mb_zip2', 'mb_addr1', 'mb_addr2');"><img width="91" height="20" src="<?=$member_skin_path?>/img/post_search_btn.gif" border=0 align=absmiddle></a></td>
                </tr>
                <tr>
                    <td height="25" colspan="2"><input class=m_text type=text name='mb_addr1' size=60 readonly <?=$config[cf_req_addr]?'required':'';?> itemname='주소' value='<?=$member[mb_addr1]?>'></td>
                </tr>
                <tr>
                    <td height="25" colspan="2"><input class=m_text type=text name='mb_addr2' size=60 <?=$config[cf_req_addr]?'required':'';?> itemname='상세주소' value='<?=$member[mb_addr2]?>'></td>
                </tr>
                </table>
            </TD>
        </TR>
        <? } ?>

        </TABLE>
    </td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td height="1" bgcolor="#ffffff"></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100%>

        <? if ($config[cf_use_signature]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>서명</TD>
            <TD class=m_padding><textarea name=mb_signature class=m_textarea rows=3 style='width:95%;' <?=$config[cf_req_signature]?'required':'';?> itemname='서명'><?=$member[mb_signature]?></textarea></TD>
        </TR>
        <? } ?>

        <? if ($config[cf_use_profile]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>자기소개</TD>
            <TD class=m_padding><textarea name=mb_profile class=m_textarea rows=3 style='width:95%;' <?=$config[cf_req_profile]?'required':'';?> itemname='자기 소개'><?=$member[mb_profile]?></textarea></TD>
        </TR>
        <? } ?>

        <? if ($member[mb_level] >= $config[cf_icon_level]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>회원아이콘</TD>
            <TD class=m_padding><INPUT class=m_text type=file name='mb_icon' size=30>
                <table width="350" border="0" cellspacing="0" cellpadding="0">
                    <tr>
                        <td class=m_padding3>* 이미지 크기는 가로(<?=$config[cf_member_icon_width]?>픽셀)x세로(<?=$config[cf_member_icon_height]?>픽셀) 이하로 해주세요.<br>&nbsp;&nbsp;(gif만 가능 / 용량:<?=number_format($config[cf_member_icon_size])?>바이트 이하만 등록됩니다.)
                            <? if ($w == "u" && file_exists($mb_icon)) { ?>
                                <br><img src='<?=$mb_icon?>' align=absmiddle> <input type=checkbox name='del_mb_icon' value='1'>삭제
                            <? } ?>
                        </td>
                    </tr>
                </table></TD>
        </TR>
        <? } ?>

        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>메일링서비스</TD>
            <TD class=m_padding><input type=checkbox name=mb_mailling value='1' <?=($w=='' || $member[mb_mailling])?'checked':'';?>>정보 메일을 받겠습니다.</TD>
        </TR>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>SMS 수신여부</TD>
            <TD class=m_padding><input type=checkbox name=mb_sms value='1' <?=($w=='' || $member[mb_sms])?'checked':'';?>>핸드폰 문자메세지를 받겠습니다.</TD>
        </TR>

        <? if ($member[mb_open_date] <= date("Y-m-d", $g4[server_time] - ($config[cf_open_modify] * 86400))) { // 정보공개 수정일이 지났다면 수정가능 ?>
        <input type=hidden name=mb_open_default value='<?=$member[mb_open]?>'>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>정보공개</TD>
            <TD class=m_padding><input type=checkbox name=mb_open value='1' <?=($w=='' || $member[mb_open])?'checked':'';?>>다른분들이 나의 정보를 볼 수 있도록 합니다.
                <br>&nbsp;&nbsp;&nbsp;&nbsp; 정보공개를 바꾸시면 앞으로 <?=(int)$config[cf_open_modify]?>일 이내에는 변경이 안됩니다.</td>
        </TR>
        <? } else { ?>
        <input type=hidden name="mb_open" value="<?=$member[mb_open]?>">
        <? } ?>

        <? if ($w == "" && $config[cf_use_recommend]) { ?>
        <TR bgcolor="#FFFFFF">
            <TD width="160" class=m_title>추천인아이디</TD>
            <TD class=m_padding><input type=text name=mb_recommend class=m_text></TD>
        </TR>
        <? } ?>

        </TABLE>
    </td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td height="1" bgcolor="#ffffff"></td>
</tr>
</table>

<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
    <td bgcolor="#CCCCCC">
        <TABLE cellSpacing=1 cellPadding=0 width=100%>
        <TR bgcolor="#FFFFFF">
            <td width="160" height="28" class=m_title>  
                <img id='kcaptcha_image' />
            </td>
            <td class=m_padding>
                <input type=input class=ed size=10 name=wr_key itemname="자동등록방지" required>&nbsp;&nbsp;왼쪽의 글자를 입력하세요.
            </td>
        </tr>
        </table>
    </td>
</tr>
</table>


<p align=center>
    <INPUT type=image width="66" height="20" src="<?=$member_skin_path?>/img/join_ok_btn.gif" border=0 accesskey='s'>

</td></tr>
</form>
</table>


<script type="text/javascript" src="<?="$g4[path]/js/jquery.kcaptcha.js"?>"></script>
<script type="text/javascript">
$(function() {
    // 폼의 첫번째 입력박스에 포커스 주기 
    $("#fregisterform :input[type=text]:visible:enabled:first").focus(); 
});

// submit 최종 폼체크
function fregisterform_submit(f)
{
    if (f.w.value == "") {
        if (f.mb_id_enabled.value == "") {
            alert("회원아이디 중복확인을 해주십시오.");
            f.mb_id.focus();
            return false;
        } else if (f.mb_id_enabled.value == -1) {
            alert("'"+f.mb_id.value+"'은(는) 이미 가입된 회원아이디이므로 사용하실 수 없습니다.");
            f.mb_id.focus();
            return false;
        }
    }

    if ((f.w.value == "" && f.mb_nick_enabled.value == "") || 
        (f.w.value == "u" && f.mb_nick_enabled.value == "" && f.mb_nick.defaultValue != f.mb_nick.value)) {
        alert("별명 중복확인을 해주십시오.");
        f.mb_nick.focus();
        return false;
    } else if (f.mb_nick_enabled.value == -1) {
        alert("'"+f.mb_nick.value+"'은(는) 이미 등록된 별명이므로 사용하실 수 없습니다.");
        f.mb_nick.focus();
        return false;
    }

    var id = prohibit_id_check(f.mb_id.value);
    if (id) {
        alert("'"+id+"'은(는) 사용하실 수 없는 회원아이디입니다.");
        f.mb_id.focus();
        return false;
    }

    if (f.mb_password.value != f.mb_password_re.value) {
        alert("패스워드가 같지 않습니다.");
        f.mb_password_re.focus();
        return false;
    }

    /*
    // 사용할 수 없는 별명을 사용하고자 하는 경우에는 이 주석을 제거하십시오.
    if (!prohibit_id_check(f.mb_nick.value))
    {
        alert("'"+f.mb_nick.value + "'은(는) 사용하실 수 없는 별명입니다.");
        f.mb_nick.focus();
        return;
    }
    */

    var domain = prohibit_email_check(f.mb_email.value);
    if (domain) {
        alert("'"+domain+"'은(는) 사용하실 수 없는 메일입니다.");
        f.mb_email.focus();
        return false;
    }

    if ((f.w.value == "" && f.mb_email_enabled.value == "") || 
        (f.w.value == "u" && f.mb_email_enabled.value == "" && f.mb_email.defaultValue != f.mb_email.value)) {
        alert("E-mail 중복확인을 해주십시오.");
        f.mb_email.focus();
        return false;
    } else if (f.mb_email_enabled.value == -1) {
        alert("'"+f.mb_email.value+"'은(는) 이미 다른 회원이 사용하는 E-mail이므로 사용하실 수 없습니다.");
        return false;
    }

    if (typeof f.mb_icon != "undefined") 
    {
        if (f.mb_icon.value) 
        {
            if (!f.mb_icon.value.toLowerCase().match(/.(gif)$/i)) 
            {
                alert("회원아이콘이 gif 파일이 아닙니다.");
                f.mb_icon.focus();
                return false;
            }
        }
    }

    if (typeof(f.mb_recommend) != 'undefined')
    {
        if (f.mb_id.value == f.mb_recommend.value)
        {
            alert("본인을 추천할 수 없습니다.");
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

    return true;
}

// 회원아이디 검사
function mb_id_check()
{
    var f = document.fregisterform;

    if (f.mb_id.value == "") {
        alert("회원 아이디를 입력하세요.");
        f.mb_id.focus();
        return;
    }

    var id = prohibit_id_check(f.mb_id.value);
    if (id) {
        alert("'"+id + "'은(는) 사용하실 수 없는 회원아이디입니다.");
        f.mb_id.focus();
        return;
    }

    win_open(g4_path+"/"+g4_bbs+"/member_id_check.php?mb_id="+document.fregisterform.mb_id.value, "hiddenframe");
}

// 별명 검사
function mb_nick_check()
{
    var f = document.fregisterform;

    if (f.mb_nick.value == "") {
        alert("별명을 입력하세요.");
        f.mb_nick.focus();
        return;
    }

    var id = prohibit_id_check(f.mb_nick.value);
    if (id) {
        alert("'"+id + "'은(는) 사용하실 수 없는 별명입니다.");
        f.mb_nick.focus();
        return;
    }

    if (f.mb_nick.defaultValue == f.mb_nick.value && f.mb_nick.value != "") {
        alert("별명이 바뀌지 않았으므로 중복확인 하실 필요가 없습니다.");
        return;
    }

    if (g4_charset.toUpperCase() == 'EUC-KR') 
        win_open(g4_path+"/"+g4_bbs+"/member_nick_check.php?mb_nick="+document.fregisterform.mb_nick.value, "hiddenframe"); 
    else 
        win_open(g4_path+"/"+g4_bbs+"/member_nick_check.php?mb_nick="+encodeURIComponent(document.fregisterform.mb_nick.value), "hiddenframe"); 
}

// E-mail 검사
function mb_email_check()
{
    if (document.fregisterform.mb_email.value == "") {
        alert("E-mail을 입력하세요.");
        return;
    }

    win_open(g4_path+"/"+g4_bbs+"/member_email_check.php?mb_email="+document.fregisterform.mb_email.value, "hiddenframe");
}

function mb_id_change()
{
    if (document.fregisterform.mb_id.value != document.fregisterform.mb_id.defaultValue)
        document.fregisterform.mb_id_enabled.value = "";
}

// 금지 아이디, 별명 검사
function prohibit_id_check(id)
{
    id = id.toLowerCase();

    var prohibit_id = "<?=trim(strtolower($config[cf_prohibit_id]))?>";
    var s = prohibit_id.split(",");

    for (i=0; i<s.length; i++) {
        if (s[i] == id)
            return id;
    }
    return "";
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

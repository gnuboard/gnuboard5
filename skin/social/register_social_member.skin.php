<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if( ! $config['cf_social_login_use']) {     //소셜 로그인을 사용하지 않으면
    return;
}

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.get_social_skin_url().'/style.css">', 10);
?>

<!-- 회원정보 입력/수정 시작 { -->
<div class="mbskin">
    
    <div class="social_info_guide">
        회원님은 소셜 계정을 통하여 로그인하셨습니다.<br>
        회원님의 아이디, 패스워드, 이메일, 닉네임 정보를 입력하시면, 앞으로는 소셜계정으로도 로그인을 계속 하실 수 있으며, 또한 입력하신 회원 아이디와 패스워드로도 이 홈페이지에 로그인이 가능해집니다.<br>
        또한 이 정보를 입력하신 후에 개인정보 수정페이지에서 메일수신여부, 쪽지수신여부 등을 계속하여 설정하실 수 있습니다.
    </div>

    <?php
    if( $is_exists_id ){    //아이디 중복 메시지
        echo '<p class="bg-warning bg-warning1">등록할 아이디가 중복되었습니다.다른 아이디를 입력해 주세요.</p>'.PHP_EOL;
    }
    if( $is_exists_email ){     //이메일 중복 메시지
        echo '<p class="bg-warning bg-warning2">등록할 이메일이 중복되었습니다.다른 이메일을 입력해 주세요.</p>'.PHP_EOL;
    }
    if( $is_exists_name ){     //닉네임 중복 메시지
        echo '<p class="bg-warning bg-warning3">등록할 닉네임이 중복되었습니다.다른 닉네임을 입력해 주세요.</p>'.PHP_EOL;
    }
    ?>

    <script src="<?php echo G5_JS_URL ?>/jquery.register_form.js"></script>
    
    <!-- 새로가입 시작 -->
    <form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url; ?>" onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="url" value="<?php echo $urlencode; ?>">
    <input type="hidden" name="mb_name" value="<?php echo $user_nick; ?>" >
    <input type="hidden" name="provider" value="<?php echo $provider_name;?>" >
    <input type="hidden" name="action" value="register">

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>사이트 이용정보 입력</caption>
        <tbody>
        <tr>
            <th scope="row"><label for="reg_mb_id">아이디<strong class="sound_only">필수</strong></label></th>
            <td>
                <span class="frm_info">영문자, 숫자, _ 만 입력 가능. 최소 3자이상 입력하세요.</span>
                <input type="text" name="mb_id" value="<?php echo $user_id; ?>" id="reg_mb_id" <?php echo $required; ?> <?php echo $readonly; ?> class="frm_input <?php echo $required; ?> <?php echo $readonly; ?>" minlength="3" maxlength="20">
                <span id="msg_mb_id"></span>
            </td>
        </tr>
        <tr>
            <th scope="row"><label for="reg_mb_password">비밀번호<strong class="sound_only">필수</strong></label></th>
            <td><input type="password" name="mb_password" id="reg_mb_password" <?php echo $required; ?> class="frm_input <?php echo $required; ?>" minlength="3" maxlength="20"></td>
        </tr>
        <tr>
            <th scope="row"><label for="reg_mb_password_re">비밀번호 확인<strong class="sound_only">필수</strong></label></th>
            <td><input type="password" name="mb_password_re" id="reg_mb_password_re" <?php echo $required; ?> class="frm_input <?php echo $required; ?>" minlength="3" maxlength="20"></td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption>개인정보 입력</caption>
        <tbody>
        <tr>
            <th scope="row"><label for="reg_mb_nick">닉네임<strong class="sound_only">필수</strong></label></th>
            <td>
                <span class="frm_info">
                    공백없이 한글,영문,숫자만 입력 가능 (한글2자, 영문4자 이상)<br>
                    닉네임을 바꾸시면 앞으로 <?php echo (int)$config['cf_nick_modify'] ?>일 이내에는 변경 할 수 없습니다.
                </span>
                <input type="hidden" name="mb_nick_default" value="<?php echo isset($user_nick)?get_text($user_nick):''; ?>">
                <input type="text" name="mb_nick" value="<?php echo isset($user_nick)?get_text($user_nick):''; ?>" id="reg_mb_nick" required class="frm_input required nospace" size="10" maxlength="20">
                <span id="msg_mb_nick"></span>
            </td>
        </tr>

        <tr>
            <th scope="row"><label for="reg_mb_email">E-mail<strong class="sound_only">필수</strong></label></th>
            <td>
                <?php if ($config['cf_use_email_certify']) {  ?>
                <span class="frm_info">
                    <?php if ($w=='') { echo "E-mail 로 발송된 내용을 확인한 후 인증하셔야 회원가입이 완료됩니다."; }  ?>
                    <?php if ($w=='u') { echo "E-mail 주소를 변경하시면 다시 인증하셔야 합니다."; }  ?>
                </span>
                <?php } ?>
                <input type="text" name="mb_email" value="<?php echo isset($user_email)?$user_email:''; ?>" id="reg_mb_email" required class="frm_input email required" size="70" maxlength="100">
            </td>
        </tr>

        </tbody>
        </table>
    </div>

    <div class="btn_confirm">
        <input type="submit" value="<?php echo $w==''?'회원가입':'정보수정'; ?>" id="btn_submit" class="btn_submit" accesskey="s">
        <a href="<?php echo G5_URL ?>" class="btn_cancel">취소</a>
    </div>
    </form>
    <!-- 새로가입 끝 -->

    <!-- 기존 계정 연결 시작 -->
    <div id="sns-link-pnl" class="mw">

        <div class="mw-fg">
            <form method="post" action="<?php echo $login_action_url; ?>" onsubmit="return social_obj.flogin_submit(this);">
            <input type="hidden" name="url" value="<?php echo $login_url; ?>">
            <input type="hidden" name="provider" value="<?php echo $provider_name; ?>">
            <input type="hidden" name="action" value="link">

            <div class="mw-title">기존 계정에 연결하기</div>

            <div class="mw-desc">
                SIR 기존 아이디에 SNS 아이디를 연결합니다.<br>
                SNS 아이디로 로그인 하시면 SIR 아이디로 로그인 됩니다.
            </div>

            <button type="button" class="mw-close">
                <i class="fa fa-close"></i>
                <span class="txt">닫기</span>
            </button>

            <div id="login_fs">
                <label for="login_id" class="login_id">아이디<strong class="sound_only"> 필수</strong></label>
                <span class="lg_id"><input type="text" name="mb_id" id="login_id" class="frm_input required" size="20" maxLength="20" ></span>
                <label for="login_pw" class="login_pw">비밀번호<strong class="sound_only"> 필수</strong></label>
                <span class="lg_pw"><input type="password" name="mb_password" id="login_pw" class="frm_input required" size="20" maxLength="20"></span>
                <br>
                <input type="submit" value="연결하기" class="login_submit">
            </div>

            </form>
        </div>
    </div>
    <!-- 기존 계정 연결 끝 -->

    <script>

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

        if (f.w.value == "") {
            if (f.mb_password.value.length < 3) {
                alert("비밀번호를 3글자 이상 입력하십시오.");
                f.mb_password.focus();
                return false;
            }
        }

        if (f.mb_password.value != f.mb_password_re.value) {
            alert("비밀번호가 같지 않습니다.");
            f.mb_password_re.focus();
            return false;
        }

        if (f.mb_password.value.length > 0) {
            if (f.mb_password_re.value.length < 3) {
                alert("비밀번호를 3글자 이상 입력하십시오.");
                f.mb_password_re.focus();
                return false;
            }
        }

        // 닉네임 검사
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

        document.getElementById("btn_submit").disabled = "disabled";

        return true;
    }

    function flogin_submit(f)
    {
        var mb_id = $.trim($(f).find("input[name=mb_id]").val()),
            mb_password = $.trim($(f).find("input[name=mb_password]").val());

        if(!mb_id || !mb_password){
            return false;
        }

        return true;
    }
    </script>

</div>
<!-- } 회원정보 입력/수정 끝 -->
<?
include_once('./_common.php');
include_once(G4_GCAPTCHA_PATH.'/gcaptcha.lib.php');
include_once(G4_LIB_PATH.'/register.lib.php');
include_once(G4_LIB_PATH.'/mailer.lib.php');

// 리퍼러 체크
referer_check();

if (!($w == '' || $w == 'u')) {
    alert('w 값이 제대로 넘어오지 않았습니다.');
}

if ($w == 'u' && $is_admin == 'super') {
    if (file_exists(G4_PATH.'/DEMO'))
        alert('데모 화면에서는 하실(보실) 수 없는 작업입니다.');
}

if (!chk_captcha()) {
    alert('스팸방지에 입력한 숫자가 틀렸습니다.');
}

$mb_id          = escape_trim($_POST['mb_id']);
$mb_password    = escape_trim($_POST['mb_password']);
$mb_password_re = escape_trim($_POST['mb_password_re']);
$mb_name        = escape_trim($_POST['mb_name']);
$mb_nick        = escape_trim($_POST['mb_nick']);
$mb_email       = escape_trim($_POST['mb_email']);
$mb_sex         = isset($_POST['mb_sex'])       ? escape_trim($_POST['mb_sex'])         : "";
$mb_birth       = isset($_POST['mb_birth'])     ? escape_trim($_POST['mb_birth'])       : "";
$mb_homepage    = isset($_POST['mb_homepage'])  ? escape_trim($_POST['mb_homepage'])    : "";
$mb_tel         = isset($_POST['mb_tel'])       ? escape_trim($_POST['mb_tel'])         : "";
$mb_hp          = isset($_POST['mb_hp'])        ? escape_trim($_POST['mb_hp'])          : "";
$mb_zip1        = isset($_POST['mb_zip1'])      ? escape_trim($_POST['mb_zip1'])        : "";
$mb_zip2        = isset($_POST['mb_zip2'])      ? escape_trim($_POST['mb_zip2'])        : "";
$mb_addr1       = isset($_POST['mb_addr1'])     ? escape_trim($_POST['mb_addr1'])       : "";
$mb_addr2       = isset($_POST['mb_addr2'])     ? escape_trim($_POST['mb_addr2'])       : "";
$mb_signature   = isset($_POST['mb_signature']) ? escape_trim($_POST['mb_signature'])   : "";
$mb_profile     = isset($_POST['mb_profile'])   ? escape_trim($_POST['mb_profile'])     : "";
$mb_recommend   = isset($_POST['mb_recommend']) ? escape_trim($_POST['mb_recommend'])   : "";
$mb_mailling    = isset($_POST['mb_mailling'])  ? escape_trim($_POST['mb_mailling'])    : "";
$mb_sms         = isset($_POST['mb_sms'])       ? escape_trim($_POST['mb_sms'])         : "";
$mb_1           = isset($_POST['mb_1'])         ? escape_trim($_POST['mb_1'])           : "";
$mb_2           = isset($_POST['mb_2'])         ? escape_trim($_POST['mb_2'])           : "";
$mb_3           = isset($_POST['mb_3'])         ? escape_trim($_POST['mb_3'])           : "";
$mb_4           = isset($_POST['mb_4'])         ? escape_trim($_POST['mb_4'])           : "";
$mb_5           = isset($_POST['mb_5'])         ? escape_trim($_POST['mb_5'])           : "";
$mb_6           = isset($_POST['mb_6'])         ? escape_trim($_POST['mb_6'])           : "";
$mb_7           = isset($_POST['mb_7'])         ? escape_trim($_POST['mb_7'])           : "";
$mb_8           = isset($_POST['mb_8'])         ? escape_trim($_POST['mb_8'])           : "";
$mb_9           = isset($_POST['mb_9'])         ? escape_trim($_POST['mb_9'])           : "";
$mb_10          = isset($_POST['mb_10'])        ? escape_trim($_POST['mb_10'])          : "";

if ($w == '' || $w == 'u') {

    if ($msg = empty_mb_id($mb_id))         alert($msg, "", true, true); // alert($msg, $url, $error, $post);

    if ($w == '' && !$mb_password)
        alert('패스워드가 넘어오지 않았습니다.');
    if($w == '' && $mb_password != $mb_password_re)
        alert('패스워드가 일치하지 않습니다.');

    if ($msg = empty_mb_name($mb_id))       alert($msg, "", true, true);
    if ($msg = empty_mb_nick($mb_nick))     alert($msg, "", true, true);
    if ($msg = empty_mb_email($mb_email))   alert($msg, "", true, true);
    if ($msg = reserve_mb_id($mb_id))       alert($msg, "", true, true);
    if ($msg = reserve_mb_nick($mb_nick))   alert($msg, "", true, true);
    if ($msg = valid_mb_name($mb_name))     alert($msg, "", true, true);
    if ($msg = valid_mb_nick($mb_nick))     alert($msg, "", true, true);
    if ($msg = valid_mb_email($mb_email))   alert($msg, "", true, true);
    if ($msg = prohibit_mb_email($mb_email))alert($msg, "", true, true);

    if ($w=='') {
        if ($msg = exist_mb_id($mb_id))     alert($msg);

        if (strtolower($mb_id) == strtolower($mb_recommend)) {
            alert('본인을 추천할 수 없습니다.');
        }
    } else {
        // 자바스크립트로 정보변경이 가능한 버그 수정
        // 별명수정일이 지나지 않았다면
        if ($member['mb_nick_date'] > date("Y-m-d", G4_SERVER_TIME - ($config['cf_nick_modify'] * 86400)))
            $mb_nick = $member['mb_nick'];
        // 회원정보의 메일을 이전 메일로 옮기고 아래에서 비교함
        $old_email = $member['mb_email'];
    }

    if ($msg = exist_mb_nick($mb_nick, $mb_id))     alert($msg);
    if ($msg = exist_mb_email($mb_email, $mb_id))   alert($msg);
}

$mb_dir = G4_DATA_PATH.'/member/'.substr($mb_id,0,2);

// 아이콘 삭제
if (isset($_POST['del_mb_icon'])) {
    @unlink($mb_dir.'/'.$mb_id.'.gif');
}

$msg = "";

// 아이콘 업로드
$mb_icon = '';
if (isset($_FILES['mb_icon']) && is_uploaded_file($_FILES['mb_icon']['tmp_name'])) {
    if (preg_match("/(\.gif)$/i", $_FILES['mb_icon']['name'])) {
        // 아이콘 용량이 설정값보다 이하만 업로드 가능
        if ($_FILES['mb_icon']['size'] <= $config['cf_member_icon_size']) {
            @mkdir($mb_dir, 0707);
            @chmod($mb_dir, 0707);
            $dest_path = $mb_dir.'/'.$mb_id.'.gif';
            move_uploaded_file($_FILES['mb_icon']['tmp_name'], $dest_path);
            chmod($dest_path, 0606);
            if (file_exists($dest_path)) {
                //=================================================================\
                // 090714
                // gif 파일에 악성코드를 심어 업로드 하는 경우를 방지
                // 에러메세지는 출력하지 않는다.
                //-----------------------------------------------------------------
                $size = getimagesize($dest_path);
                if ($size[2] != 1) // gif 파일이 아니면 올라간 이미지를 삭제한다.
                    @unlink($dest_path);
                else
                // 아이콘의 폭 또는 높이가 설정값 보다 크다면 이미 업로드 된 아이콘 삭제
                if ($size[0] > $config['cf_member_icon_width'] || $size[1] > $config['cf_member_icon_height'])
                    @unlink($dest_path);
                //=================================================================\
            }
        }

    } else {
        $msg .= $_FILES['mb_icon']['name'].'은(는) gif 파일이 아닙니다.';
    }
}


// 관리자님 회원정보
$admin = get_admin('super');


if ($w == '') {

    $sql = " insert into {$g4['member_table']}
                set mb_id = '{$mb_id}',
                     mb_password = '".sql_password($mb_password)."',
                     mb_name = '{$mb_name}',
                     mb_sex = '{$mb_sex}',
                     mb_birth = '{$mb_birth}',
                     mb_nick = '{$mb_nick}',
                     mb_nick_date = '".G4_TIME_YMD."',
                     mb_email = '{$mb_email}',
                     mb_homepage = '{$mb_homepage}',
                     mb_tel = '{$mb_tel}',
                     mb_hp = '{$mb_hp}',
                     mb_zip1 = '{$mb_zip1}',
                     mb_zip2 = '{$mb_zip2}',
                     mb_addr1 = '{$mb_addr1}',
                     mb_addr2 = '{$mb_addr2}',
                     mb_signature = '{$mb_signature}',
                     mb_profile = '{$mb_profile}',
                     mb_today_login = '".G4_TIME_YMDHIS."',
                     mb_datetime = '".G4_TIME_YMDHIS."',
                     mb_ip = '{$_SERVER['REMOTE_ADDR']}',
                     mb_level = '{$config['cf_register_level']}',
                     mb_recommend = '{$mb_recommend}',
                     mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
                     mb_mailling = '{$mb_mailling}',
                     mb_sms = '{$mb_sms}',
                     mb_open = '{$mb_open}',
                     mb_open_date = '".G4_TIME_YMD."',
                     mb_1 = '{$mb_1}',
                     mb_2 = '{$mb_2}',
                     mb_3 = '{$mb_3}',
                     mb_4 = '{$mb_4}',
                     mb_5 = '{$mb_5}',
                     mb_6 = '{$mb_6}',
                     mb_7 = '{$mb_7}',
                     mb_8 = '{$mb_8}',
                     mb_9 = '{$mb_9}',
                     mb_10 = '{$mb_10}' ";
    // 이메일 인증을 사용하지 않는다면 이메일 인증시간을 바로 넣는다
    if (!$config['cf_use_email_certify'])
        $sql .= " , mb_email_certify = '".G4_TIME_YMDHIS."' ";
    sql_query($sql);

    // 회원가입 포인트 부여
    insert_point($mb_id, $config['cf_register_point'], '회원가입 축하', '@member', $mb_id, '회원가입');

    // 추천인에게 포인트 부여
    if ($config['cf_use_recommend'] && $mb_recommend)
        insert_point($mb_recommend, $config['cf_recommend_point'], $mb_id.'의 추천인', '@member', $mb_recommend, $mb_id.' 추천');

    // 회원님께 메일 발송
    if ($config['cf_email_mb_member']) {
        $subject = '회원가입을 축하드립니다.';

        $mb_md5 = md5($mb_id.$mb_email.G4_TIME_YMDHIS);
        $certify_href = G4_BBS_URL.'/email_certify.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5;

        ob_start();
        include_once ('./register_form_update_mail1.php');
        $content = ob_get_contents();
        ob_end_clean();

        mailer($admin['mb_nick'], $admin['mb_email'], $mb_email, $subject, $content, 1);
    }

    // 최고관리자님께 메일 발송
    if ($config['cf_email_mb_super_admin']) {
        $subject = $mb_nick .' 님께서 회원으로 가입하셨습니다.';

        ob_start();
        include_once ('./register_form_update_mail2.php');
        $content = ob_get_contents();
        ob_end_clean();

        mailer($mb_nick, $mb_email, $admin['mb_email'], $subject, $content, 1);
    }

    // 메일인증 사용하지 않는 경우에만 로그인
    if (!$config['cf_use_email_certify'])
        set_session('ss_mb_id', $mb_id);

    set_session('ss_mb_reg', $mb_id);

} else if ($w == 'u') {

    if (!trim($_SESSION['ss_mb_id']))
        alert('로그인 되어 있지 않습니다.');

    if ($_SESSION['ss_mb_id'] != $mb_id)
        alert("로그인된 정보와 수정하려는 정보가 틀리므로 수정할 수 없습니다.\\n만약 올바르지 않은 방법을 사용하신다면 바로 중지하여 주십시오.");

    $sql_password = "";
    if ($mb_password)
        $sql_password = " , mb_password = '".sql_password($mb_password)."' ";

    $sql_icon = "";
    if ($mb_icon)
        $sql_icon = " , mb_icon = '{$mb_icon}' ";

    $sql_nick_date = "";
    if ($mb_nick_default != $mb_nick)
        $sql_nick_date =  " , mb_nick_date = '".G4_TIME_YMD."' ";

    $sql_open_date = "";
    if ($mb_open_default != $mb_open)
        $sql_open_date =  " , mb_open_date = '".G4_TIME_YMD."' ";

    $sql_sex = "";
    if (isset($mb_sex))
        $sql_sex = " , mb_sex = '{$mb_sex}' ";

    // 이전 메일주소와 수정한 메일주소가 틀리다면 인증을 다시 해야하므로 값을 삭제
    $sql_email_certify = '';
    if ($old_email != $mb_email && $config['cf_use_email_certify'])
        $sql_email_certify = " , mb_email_certify = '' ";

                // set mb_name         = '$mb_name', 제거
    $sql = " update {$g4['member_table']}
                set mb_nick = '{$mb_nick}',
                    mb_mailling = '{$mb_mailling}',
                    mb_sms = '{$mb_sms}',
                    mb_open = '{$mb_open}',
                    mb_email = '{$mb_email}',
                    mb_homepage = '{$mb_homepage}',
                    mb_tel = '{$mb_tel}',
                    mb_hp = '{$mb_hp}',
                    mb_zip1 = '{$mb_zip1}',
                    mb_zip2 = '{$mb_zip2}',
                    mb_addr1 = '{$mb_addr1}',
                    mb_addr2 = '{$mb_addr2}',
                    mb_signature = '{$mb_signature}',
                    mb_profile = '{$mb_profile}',
                    mb_1 = '{$mb_1}',
                    mb_2 = '{$mb_2}',
                    mb_3 = '{$mb_3}',
                    mb_4 = '{$mb_4}',
                    mb_5 = '{$mb_5}',
                    mb_6 = '{$mb_6}',
                    mb_7 = '{$mb_7}',
                    mb_8 = '{$mb_8}',
                    mb_9 = '{$mb_9}',
                    mb_10 = '{$mb_10}'
                    {$sql_password}
                    {$sql_icon}
                    {$sql_nick_date}
                    {$sql_open_date}
                    {$sql_sex}
                    {$sql_email_certify}
              where mb_id = '$mb_id' ";
    sql_query($sql);

    // 인증메일 발송
    if ($old_email != $mb_email && $config['cf_use_email_certify']) {
        $subject = '인증확인 메일입니다.';

        $mb_md5 = md5($mb_id.$mb_email.$member['mb_datetime']);
        $certify_href = G4_BBS_URL.'/email_certify.php?mb_id='.$mb_id.'&amp;mb_md5='.$mb_md5;

        ob_start();
        include_once ('./register_form_update_mail3.php');
        $content = ob_get_contents();
        ob_end_clean();

        mailer($admin['mb_nick'], $admin['mb_email'], $mb_email, $subject, $content, 1);
    }
}


// 사용자 코드 실행
@include_once ($member_skin_path.'/register_update.skin.php');


if ($msg)
    echo '<script>alert(\''.$msg.'\');</script>';

if ($w == "") {
    goto_url(G4_BBS_URL.'/register_result.php');
} else if ($w == 'u') {
    $row  = sql_fetch(" select mb_password from {$g4['member_table']} where mb_id = '{$member[mb_id]}' ");
    $tmp_password = $row['mb_password'];

    if ($old_email != $mb_email && $config['cf_use_email_certify']) {
        set_session("ss_mb_id", "");
        alert('회원 정보가 수정 되었습니다.\n\nE-mail 주소가 변경되었으므로 다시 인증하셔야 합니다.', G4_URL);
    } else {
        alert('회원 정보가 수정 되었습니다.', G4_URL);
        /*
        echo '
        <html><title>회원정보수정</title><meta http-equiv="Content-Type" content="text/html; charset=$g4[charset]"></html><body>
        <form name="fregisterupdate" method="post" action="'.$g4['bbs_url'].'/register_form.php">
        <input type="hidden" name="w" value="u">
        <input type="hidden" name="mb_id" value="'.$mb_id.'">
        <input type="hidden" name="mb_password" value="'.$tmp_password.'">
        <input type="hidden" name="is_update" value="1">
        </form>
        <script>
        alert("회원 정보가 수정 되었습니다.");
        document.fregisterupdate.submit();
        </script>
        </body>
        </html>';
        */
    }
}
?>

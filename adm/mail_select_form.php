<?php
$sub_menu = "200300";
require_once './_common.php';

if (!$config['cf_email_use']) {
    alert('환경설정에서 \'메일발송 사용\'에 체크하셔야 메일을 발송할 수 있습니다.');
}

auth_check_menu($auth, $sub_menu, 'r');

$ma_id = isset($_GET['ma_id']) ? (int) $_GET['ma_id'] : 0;

$sql = " select * from {$g5['mail_table']} where ma_id = '$ma_id' ";
$ma = sql_fetch($sql);
if (!$ma['ma_id']) {
    alert('보내실 내용을 선택하여 주십시오.');
}

// 전체회원수
$sql = " select COUNT(*) as cnt from {$g5['member_table']} ";
$row = sql_fetch($sql);
$tot_cnt = $row['cnt'];

// 탈퇴대기회원수
$sql = " select COUNT(*) as cnt from {$g5['member_table']} where mb_leave_date <> '' ";
$row = sql_fetch($sql);
$finish_cnt = $row['cnt'];

$last_option = explode('||', $ma['ma_last_option']);
for ($i = 0; $i < count($last_option); $i++) {
    $option = explode('=', $last_option[$i]);
    // 동적변수
    $var = isset($option[0]) ? $option[0] : '';
    if (isset($option[1])) {
        $$var = $option[1];
    }
}

if (!isset($mb_id1)) {
    $mb_id1 = 1;
}
if (!isset($mb_level_from)) {
    $mb_level_from = 1;
}
if (!isset($mb_level_to)) {
    $mb_level_to = 10;
}
if (!isset($mb_mailling)) {
    $mb_mailling = 1;
}

$mb_id1_from = isset($mb_id1_from) ? clean_xss_tags($mb_id1_from, 1, 1, 30) : '';
$mb_id1_to = isset($mb_id1_to) ? clean_xss_tags($mb_id1_to, 1, 1, 30) : '';
$mb_email = isset($mb_email) ? clean_xss_tags($mb_email, 1, 1, 100) : '';

$g5['title'] = '회원메일발송';
require_once './admin.head.php';
?>

<div class="local_ov01 local_ov">
    전체회원 <?php echo number_format($tot_cnt) ?>명 , 탈퇴대기회원 <?php echo number_format($finish_cnt) ?>명, 정상회원 <?php echo number_format($tot_cnt - $finish_cnt) ?>명 중 메일 발송 대상 선택
</div>

<form name="frmsendmailselectform" id="frmsendmailselectform" action="./mail_select_list.php" method="post" autocomplete="off">
    <input type="hidden" name="ma_id" value="<?php echo $ma_id ?>">

    <div class="tbl_frm01 tbl_wrap">
        <table>
            <caption><?php echo $g5['title']; ?> 대상선택</caption>
            <tbody>
                <tr>
                    <th scope="row">회원 ID</th>
                    <td>
                        <input type="radio" name="mb_id1" value="1" id="mb_id1_all" <?php echo $mb_id1 ? "checked" : ""; ?>> <label for="mb_id1_all">전체</label>
                        <input type="radio" name="mb_id1" value="0" id="mb_id1_section" <?php echo !$mb_id1 ? "checked" : ""; ?>> <label for="mb_id1_section">구간</label>
                        <input type="text" name="mb_id1_from" value="<?php echo get_sanitize_input($mb_id1_from); ?>" id="mb_id1_from" title="시작구간" class="frm_input"> 에서
                        <input type="text" name="mb_id1_to" value="<?php echo get_sanitize_input($mb_id1_to); ?>" id="mb_id1_to" title="종료구간" class="frm_input"> 까지
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_email">E-mail</label></th>
                    <td>
                        <?php echo help("메일 주소에 단어 포함 (예 : @" . preg_replace('#^(www[^\.]*\.){1}#', '', $_SERVER['HTTP_HOST']) . ")") ?>
                        <input type="text" name="mb_email" value="<?php echo get_sanitize_input($mb_email); ?>" id="mb_email" class="frm_input" size="50">
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="mb_mailling">메일링</label></th>
                    <td>
                        <select name="mb_mailling" id="mb_mailling">
                            <option value="1">수신동의한 회원만
                            <option value="">전체
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row">권한</th>
                    <td>
                        <label for="mb_level_from" class="sound_only">최소권한</label>
                        <select name="mb_level_from" id="mb_level_from">
                            <?php for ($i = 1; $i <= 10; $i++) { ?>
                                <option value="<?php echo $i ?>"><?php echo $i ?></option>
                            <?php } ?>
                        </select> 에서
                        <label for="mb_level_to" class="sound_only">최대권한</label>
                        <select name="mb_level_to" id="mb_level_to">
                            <?php for ($i = 1; $i <= 10; $i++) { ?>
                                <option value="<?php echo $i ?>" <?php echo $i == 10 ? " selected" : ""; ?>><?php echo $i ?></option>
                            <?php } ?>
                        </select> 까지
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="gr_id">게시판그룹회원</label></th>
                    <td>
                        <select name="gr_id" id="gr_id">
                            <option value=''>전체</option>
                            <?php
                            $sql = " select gr_id, gr_subject from {$g5['group_table']} order by gr_subject ";
                            $result = sql_query($sql);
                            for ($i = 0; $row = sql_fetch_array($result); $i++) {
                                echo '<option value="' . $row['gr_id'] . '">' . $row['gr_subject'] . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="확인" class="btn_submit">
        <a href="./mail_list.php">목록 </a>
    </div>
</form>

<?php
require_once './admin.tail.php';

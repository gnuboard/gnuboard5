<?
$sub_menu = "200300";
include_once('./_common.php');

if (!$config['cf_email_use'])
    alert('환경설정에서 \'메일발송 사용\'에 체크하셔야 메일을 발송할 수 있습니다.');

auth_check($auth[$sub_menu], 'r');

$sql = " select * from {$g4['mail_table']} where ma_id = '$ma_id' ";
$ma = sql_fetch($sql);
if (!$ma['ma_id'])
    alert('보내실 내용을 선택하여 주십시오.');

// 전체회원수
$sql = " select COUNT(*) as cnt from {$g4['member_table']} ";
$row = sql_fetch($sql);
$tot_cnt = $row['cnt'];

// 탈퇴대기회원수
$sql = " select COUNT(*) as cnt from {$g4['member_table']} where mb_leave_date <> '' ";
$row = sql_fetch($sql);
$finish_cnt = $row['cnt'];

$last_option = explode('||', $ma['ma_last_option']);
for ($i=0; $i<count($last_option); $i++) {
    $option = explode('=', $last_option[$i]);
    // 동적변수
    $var = $option[0];
    $$var = $option[1];
}

if (!isset($mb_id1)) $mb_id1 = 1;
if (!isset($mb_level_from)) $mb_level_from = 1;
if (!isset($mb_level_to)) $mb_level_to = 10;
if (!isset($mb_mailling)) $mb_mailling = 1;

$g4['title'] = '회원메일발송';
include_once('./admin.head.php');
?>

<section class="cbox">
    <h2>메일발송대상 선택</h2>
    <p>
        전체회원 <?=number_format($tot_cnt)?>명 , 탈퇴대기회원 <?=number_format($finish_cnt)?>명, 정상회원 <?=number_format($tot_cnt - $finish_cnt)?>명 중 메일 발송 대상 선택
    </p>

    <form id="frmsendmailselectform" name="frmsendmailselectform" method="post" action="./mail_select_list.php" autocomplete="off">
    <input type="hidden" name="ma_id" value='<?=$ma_id?>'>

    <table class="frm_tbl">
    <tbody>
    <tr>
        <th scope="row">회원 ID</th>
        <td>
            <input type="radio" id="mb_id1_all" name="mb_id1" value="1" <?=$mb_id1?"checked":"";?>> <label for="mb_id1_all">전체</label>
            <input type="radio" id="mb_id1_section" name="mb_id1" value="0" <?=!$mb_id1?"checked":"";?>> <label for="mb_id1_section">구간</label>
            <input type="text" id="mb_id1_from" name="mb_id1_from" class="frm_input" value="<?=$mb_id1_from?>" title="시작구간"> 에서
            <input type="text" id="mb_id1_to" name="mb_id1_to" class="frm_input" value="<?=$mb_id1_to?>" title="종료구간"> 까지
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_email">E-mail</label></th>
        <td>
            <?=help("메일 주소에 단어 포함 (예 : @sir.co.kr)")?>
            <input type="text" id="mb_email" name="mb_email" class="frm_input" value="<?=$mb_email?>" size="50">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_mailling">메일링</label></th>
        <td>
            <select id="mb_mailling" name="mb_mailling">
                <option value="1">수신동의한 회원만
                <option value="">전체
            </select>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="mb_level_from">권한</label></th>
        <td>
            <select id="mb_level_from" name="mb_level_from" title="최소권한">
            <? for ($i=1; $i<=10; $i++) { ?>
                <option value="<? echo $i ?>"><? echo $i ?></option>
            <? } ?>
            </select> 에서
            <select id="mb_level_to" name="mb_level_to" title="최대권한">
            <? for ($i=1; $i<=10; $i++) { ?>
                <option value="<? echo $i ?>"><? echo $i ?></option>
            <? } ?>
            </select> 까지
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="gr_id">게시판그룹회원</label></th>
        <td>
            <select id="gr_id" name="gr_id">
                <option value=''>전체</option>
                <?
                $sql = " select gr_id, gr_subject from {$g4['group_table']} order by gr_subject ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++)
                {
                    echo '<option value="'.$row['gr_id'].'">'.$row['gr_subject'].'</option>';
                }
                ?>
            </select>
        </td>
    </tr>
    </tbody>
    </table>

    <div class="btn_confirm">
        <input type="submit" class="btn_submit" value="확인">
        <a href="./mail_list.php">목록 </a>
    </div>
    </form>
</section>

<?
include_once('./admin.tail.php');
?>

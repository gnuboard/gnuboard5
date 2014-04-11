<?php
$sub_menu = "900100";
include_once("./_common.php");

auth_check($auth[$sub_menu], "r");

$g5['title'] = "SMS 기본설정";

if (!$config['cf_icode_server_ip'])   $config['cf_icode_server_ip'] = '211.172.232.124';
if (!$config['cf_icode_server_port']) $config['cf_icode_server_port'] = '7295';

if ($config['cf_icode_id'] && $config['cf_icode_pw'])
{
    $res = get_sock('http://www.icodekorea.com/res/userinfo.php?userid='.$config['cf_icode_id'].'&userpw='.$config['cf_icode_pw']);
    $res = explode(';', $res);
    $userinfo = array(
        'code'      => $res[0], // 결과코드
        'coin'      => $res[1], // 고객 잔액 (충전제만 해당)
        'gpay'      => $res[2], // 고객의 건수 별 차감액 표시 (충전제만 해당)
        'payment'   => $res[3]  // 요금제 표시, A:충전제, C:정액제
    );
}

if (!$config['cf_icode_id'])
    $config['cf_icode_id'] = 'sir_';

if (!$sms5['cf_skin'])
    $sms5['cf_skin'] = 'basic';

include_once(G5_ADMIN_PATH.'/admin.head.php');

?>
<?php if (!$config['cf_icode_pw']) { ?>
<div class="local_desc01 local_desc">
    <p>
        SMS 기능을 사용하시려면 먼저 아이코드에 서비스 신청을 하셔야 합니다.<br>
        <a href="http://icodekorea.com/res/join_company_fix_a.php?sellid=sir2" target="_blank">아이코드 서비스 신청하기</a>
    </p>
</div>
<?php } ?>

<?php
if ($config['cf_sms_use'] == 'icode') { // 아이코드 사용
?>
<form name="fconfig" method="post" action="./config_update.php" enctype="multipart/form-data" >
<input type="hidden" name="cf_icode_server_ip" value="<?php echo $config['cf_icode_server_ip']?>">
<input type="hidden" name="cf_sms_use" value="<?php echo $config['cf_sms_use']?>">
<div class="tbl_frm01 tbl_wrap">
    <table>
    <caption><?php echo $g5['title']; ?></caption>
    <colgroup>
        <col class="grid_4">
        <col>
    </colgroup>
    <tbody>
    <tr>
        <th scope="row"><label for="cf_icode_id">아이코드 회원아이디<strong class="sound_only"> 필수</strong></label></th>
        <td>
            <?php echo help("아이코드에서 사용하시는 회원아이디를 입력합니다."); ?>
            <input type="text" name="cf_icode_id" value="<?php echo $config['cf_icode_id']; ?>" id="cf_icode_id" required class="frm_input required">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_icode_pw">아이코드 비밀번호<strong class="sound_only"> 필수</strong></label></th>
        <td>
            <?php echo help("아이코드에서 사용하시는 비밀번호를 입력합니다."); ?>
            <input type="password" name="cf_icode_pw" value="<?php echo $config['cf_icode_pw']; ?>" id="cf_icode_pw" required class="frm_input required">
            <?php if (!$config['cf_icode_pw']) { ?>현재 비밀번호가 입력되어 있지 않습니다.<?php } ?>
        </td>
    </tr>
    <tr>
        <th scope="row">요금제</th>
        <td>
            <?php
                if ($userinfo['payment'] == 'A') {
                   echo '충전제';
                    echo '<input type="hidden" name="cf_icode_server_port" value="7295">';
                } else if ($userinfo['payment'] == 'C') {
                    echo '정액제';
                    echo '<input type="hidden" name="cf_icode_server_port" value="7296">';
                } else {
                    echo '가입해주세요.';
                    echo '<input type="hidden" name="cf_icode_server_port" value="7295">';
                }
            ?>
        </td>
    </tr>
    <?php if ($userinfo['payment'] == 'A') { ?>
    <tr>
        <th scope="row">충전 잔액</th>
        <td>
            <?php echo number_format($userinfo['coin'])?> 원
            <input type="button" value="충전하기" class="btn_frmline" onclick="window.open('http://icodekorea.com/company/credit_card_input.php?icode_id=<?php echo $config['cf_icode_id']?>&icode_passwd=<?php echo $config['cf_icode_pw']?>','icode_payment','width=650,height=500')">
        </td>
    </tr>
    <tr>
        <th scope="row">건별 금액</th>
        <td><?php echo number_format($userinfo['gpay'])?> 원</td>
    </tr>
    <?php } ?>
    <tr>
        <th scope="row"><label for="cf_phone">회신번호<strong class="sound_only"> 필수</strong></label></th>
        <td>
            <?php echo help("회신받을 휴대폰 번호를 입력하세요. '-' 를 꼭 입력하세요.<br>예) 010-123-4567"); ?>
            <input type="text" name="cf_phone" value="<?php echo $sms5['cf_phone']; ?>" id="cf_phone" required class="frm_input required" size="12">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_member">회원간 문자전송</label></th>
        <td>
            <?php echo help("허용에 체크하면 회원끼리 문자전송이 가능합니다.");?>
            <input type="checkbox" name="cf_member" id="cf_member" <?php if ($sms5['cf_member']) echo 'checked'?>> 허용
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_level">문자전송가능 레벨</label></th>
        <td>
            <?php echo help("문자전송을 허용할 회원레벨을 선택해주세요.");?>
            <select name="cf_level" id="cf_level">
                <?php for ($i=1; $i<=10; $i++) { ?>
                <option value='<?php echo $i?>' <?php if ($sms5['cf_level'] == $i) echo 'selected';?> > <?php echo $i?> </option>
                <?php } ?>
            </select>
            레벨 이상
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_point">문자전송 차감 포인트<strong class="sound_only"> 필수</strong></label></th>
        <td>
            <?php echo help("회원이 문자를 전송할시에 차감할 포인트를 입력해주세요. 0이면 포인트를 차감하지 않습니다.");?>
            <input type="text" name="cf_point" value="<?php echo $sms5['cf_point']; ?>" id="cf_point" required class="frm_input required" size="5">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_day_count">문자전송 하루제한 갯수<strong class="sound_only"> 필수</strong></label></th>
        <td>
            <?php echo help("회원이 하루에 보낼수 있는 문자 갯수를 입력해주세요. 0이면 제한하지 않습니다.");?>
            <input type="text" name="cf_day_count" value="<?php echo $sms5['cf_day_count']; ?>" id="cf_day_count" required class="frm_input required" size="5">
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="cf_skin">스킨 디렉토리<strong class="sound_only">필수</strong></label></th>
        <td>
            <?php echo get_sms5_skin_select('skin', 'cf_skin', 'cf_skin', $sms5['cf_skin'], 'required'); ?>
        </td>
    </tr>
    </tbody>
    </table>
</div>

<div class="btn_confirm01 btn_confirm">
    <input type="submit" value="확인" class="btn_submit" accesskey="s">
</div>
</form>

<?php } else { ?>

<section>
    <h2 class="h2_frm">SMS 문자전송 서비스를 사용할 수 없습니다.</h2>
    <div class="local_desc01 local_desc">
        <p>
            SMS 를 사용하지 않고 있기 때문에, 문자 전송을 할 수 없습니다.<br>
            SMS 사용 설정은 <a href="../config_form.php#anc_cf_sms" class="btn_frmline">환경설정 &gt; 기본환경설정 &gt; SMS설정</a> 에서 SMS 사용을 아이코드로 변경해 주셔야 사용하실수 있습니다.
        </p>
    </div>
</section>

<?php } ?>

<?php
include_once(G5_ADMIN_PATH.'/admin.tail.php');
?>
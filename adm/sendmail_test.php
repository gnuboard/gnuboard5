<?php
$sub_menu = '100300';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

if (!$config['cf_email_use'])
    alert('환경설정에서 \'메일발송 사용\'에 체크하셔야 메일을 발송할 수 있습니다.');

include_once(G5_LIB_PATH.'/mailer.lib.php');

$token = get_token();

$g5['title'] = '메일 테스트';
include_once('./admin.head.php');

if (isset($_POST['email'])) {
    check_admin_token();

    $_POST['email'] = strip_tags($_POST['email']);
    $email = explode(',', $_POST['email']);

    $sent_email   = array(); // 발송 요청 성공
    $failed_email = array(); // 발송 실패 (메일 서버에서 거부/오류)

    for ($i=0; $i<count($email); $i++){

        if (!preg_match("/([0-9a-zA-Z_-]+)@([0-9a-zA-Z_-]+)\.([0-9a-zA-Z_-]+)/", $email[$i])) continue;

        $to = trim($email[$i]);
        $send_result = mailer($config['cf_admin_email_name'], $config['cf_admin_email'], $to, '[메일검사] 제목', '<span style="font-size:9pt;">[메일검사] 내용<p>이 내용이 제대로 보인다면 보내는 메일 서버에는 이상이 없는것입니다.<p>'.G5_TIME_YMDHIS.'<p>이 메일 주소로는 회신되지 않습니다.</span>', 1);

        if ($send_result) {
            $sent_email[] = $to;
        } else {
            $failed_email[] = $to;
        }
    }

    if( $sent_email || $failed_email ){
        echo '<section>';
        echo '<h2>결과 메시지</h2>';

        if( $sent_email ){
            echo '<div class="local_desc01 local_desc"><p>';
            echo '다음 '.count($sent_email).'개의 메일 주소로 테스트 메일 <strong>발송 요청이 성공</strong>했습니다. <strong>수신함 도착 여부는 별도 확인이 필요합니다.</strong>';
            echo '</p></div>';
            echo '<ul>';
            for ($i=0;$i<count($sent_email);$i++) {
                echo '<li>'.get_text($sent_email[$i]).'</li>';
            }
            echo '</ul>';
            echo '<div class="local_desc02 local_desc"><p>';
            echo '<strong>발송 요청 성공은 사이트가 메일 서버에 메일을 넘겼다는 의미이며, 받은편지함 도착을 보장하지는 않습니다.</strong><br>';
            echo '메일이 보이지 않는다면 아래 항목을 순서대로 확인해 주십시오.';
            echo '</p></div>';
            echo '<ol>';
            echo '<li>받은편지함뿐 아니라 <strong>스팸함, 정크메일함, 프로모션함</strong>을 확인합니다.</li>';
            echo '<li>네이버, 지메일, 회사메일 등 <strong>서로 다른 메일 주소</strong>로 다시 테스트합니다.</li>';
            echo '<li>관리자 메일 주소가 사이트 도메인과 같은지 확인합니다. 도메인이 다르면 스팸으로 분류될 가능성이 높습니다.</li>';
            echo '<li>도메인 메일을 사용한다면 <strong>SPF, DKIM, DMARC</strong> 설정을 확인합니다.</li>';
            echo '<li>계속 도착하지 않으면 서버 개발자에게 메일 발송 로그를 확인해주세요.</li>';
            echo '</ol>';
        }

        if( $failed_email ){
            echo '<div class="local_desc01 local_desc" style="color:#d9534f"><p>';
            echo '다음 '.count($failed_email).'개의 메일 주소는 <strong>발송에 실패</strong>했습니다.';
            echo '</p></div>';
            echo '<ul>';
            for ($i=0;$i<count($failed_email);$i++) {
                echo '<li>'.get_text($failed_email[$i]).'</li>';
            }
            echo '</ul>';
            echo '<div class="local_desc02 local_desc"><p>';
            echo '발송 실패는 받는 사람의 문제가 아니라 <strong>보내는 서버(메일 발송) 설정 문제</strong>일 가능성이 높습니다.<br>';
            echo 'SMTP 정보(주소/포트/인증)가 올바른지, 서버에서 메일 발송(sendmail/mail 함수)이 허용되어 있는지 확인하시고, 웹 서버 관리자(호스팅 업체)에게 문의해 주십시오.<br>';
            echo '자세한 오류 내용은 서버의 PHP error_log 에 기록됩니다.<br>';
            echo '</p></div>';
        }

        echo '</section>';
    }
}
?>

<section>
    <h2>테스트 메일 발송</h2>
    <div class="local_desc02 local_desc">
        <p>
            사이트에서 메일 서버로 메일을 전달할 수 있는지 확인합니다. 이 테스트는 받은편지함 도착을 보장하지 않습니다.<br>
            아래 입력칸에 테스트 메일을 발송하실 메일 주소를 입력하시면, [메일검사] 라는 제목으로 테스트 메일을 발송합니다.<br>
            보내는 메일주소 : <?php echo get_sanitize_input($config['cf_admin_email']); ?><br>
            <?php if (function_exists('domain_mail_host') && $config['cf_admin_email'] && stripos($config['cf_admin_email'], domain_mail_host()) === false) { ?>
            <?php echo '외부메일설정이나 기타 설정을 하지 않았다면, 도메인과 다른 헤더로 여겨 스팸이나 차단될 가능성이 있습니다.<br>외부메일설정이나 기타 설정을 하지 않았다면, 기본환경설정에서 관리자 메일 주소를 name'.domain_mail_host().' 과 같은 도메인 형식으로 설정할것을 권장합니다.'; ?>
            <?php } ?>
        </p>
    </div>
    <form name="fsendmailtest" method="post">
    <input type="hidden" name="token" value="<?php echo $token; ?>">
    <fieldset id="fsendmailtest">
        <legend>테스트메일 발송</legend>
        <label for="email">받는 메일주소<strong class="sound_only"> 필수</strong></label>
        <input type="text" name="email" value="<?php echo $member['mb_email'] ?>" id="email" required class="required email frm_input" size="80">
        <input type="submit" value="발송" class="btn_submit">
    </fieldset>
    </form>
    <div class="local_desc02 local_desc">
        <p>
            테스트 결과가 발송 요청 성공으로 표시되어도 수신 메일 서버의 스팸 정책에 따라 도착하지 않을 수 있습니다.<br>
            정확한 확인을 위해 여러 메일 서비스로 테스트하고, 도착하지 않으면 스팸함과 도메인 인증(SPF, DKIM, DMARC)을 확인해 주십시오.<br>
        </p>
    </div>
</section>

<?php
include_once('./admin.tail.php');

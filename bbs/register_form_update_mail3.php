<?php
// E-mail 수정시 인증 메일 (회원님께 발송)
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
$mail_site_url = g5_security_mail_base_url();
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>회원 인증 메일</title>
</head>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">
            회원 인증 메일입니다.
        </h1>
        <span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">
            <?php if ($mail_site_url) { ?>
            <a href="<?php echo htmlspecialchars($mail_site_url, ENT_QUOTES, 'UTF-8') ?>" target="_blank"><?php echo $config['cf_title'] ?></a>
            <?php } else { echo $config['cf_title']; } ?>
        </span>
        <p style="margin:20px 0 0;padding:30px 30px 50px;min-height:200px;height:auto !important;height:200px;border-bottom:1px solid #eee">
            <?php if($w == 'u') { ?>
            <b><?php echo $mb_name ?></b> 님의 E-mail 주소가 변경되었습니다.<br><br>
            <?php } ?>

            아래의 주소를 클릭하시면 인증이 완료됩니다.<br>
            <?php if (!empty($config['cf_email_certify_minutes'])) { ?>인증 링크는 발송 후 <?php echo (int) $config['cf_email_certify_minutes']; ?>분 동안 유효합니다.<br><?php } ?>
            <a href="<?php echo $certify_href ?>" target="_blank"><b><?php echo $certify_href ?></b></a><br><br>

            회원님의 성원에 보답하고자 더욱 더 열심히 하겠습니다.<br>
            감사합니다.
        </p>
        <a href="<?php echo G5_BBS_URL ?>/login.php" target="_blank" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center"><?php echo $config['cf_title'] ?> 로그인</a>
    </div>
</div>

</body>
</html>

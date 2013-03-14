<?
// 회원가입축하 메일 (회원님께 발송)
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>회원가입 축하 메일</title>
</head>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">
            회원가입을 축하합니다.
        </h1>
        <span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">
            <a href="<?=G4_URL?>" target="_blank"><?=$config['cf_title']?></a>
        </span>
        <p style="margin:20px 0 0;padding:30px 30px 50px;min-height:200px;height:auto !important;height:200px;border-bottom:1px solid #eee">
            <b><?=$mb_name?></b> 님의 회원가입을 진심으로 축하합니다.<br>
            회원님의 성원에 보답하고자 더욱 더 열심히 하겠습니다.<br>
            감사합니다.
        </p>
        <? if ($config['cf_use_email_certify']) { ?>
        <p>아래의 주소를 클릭하시면 회원가입이 완료됩니다.<br><a href="<?=$certify_href?>"><?=$certify_href?></a></p>
        <? } ?>
        <a href="<?=$link_url?>" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">사이트에서 게시물 확인하기</a>
    </div>
</div>

</body>
</html>

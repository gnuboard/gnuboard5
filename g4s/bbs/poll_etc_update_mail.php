<?php
// 설문조사 기타의견 입력시 관리자께 보내는 메일을 수정하고 싶으시다면 이 파일을 수정하십시오. 
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가 
?>

<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title>설문조사 기타의견 메일</title>
</head>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">
            <?php echo $subject ?>
        </h1>
        <span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">
            작성자 <?php echo $name ?> (<?php echo $mb_id ?>)
        </span>
        <p style="margin:20px 0 0;padding:30px 30px 50px;min-height:200px;height:auto !important;height:200px;border-bottom:1px solid #eee">
            <?php echo $content ?>
        </p>
    </div>
</div>

</body>
</html>

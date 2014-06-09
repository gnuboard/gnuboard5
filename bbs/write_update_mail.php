<?php
// 게시물 입력시 게시자, 관리자에게 드리는 메일을 수정하고 싶으시다면 이 파일을 수정하십시오.
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $wr_subject ?> 메일</title>
</head>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="padding:30px 30px 0;background:#f7f7f7;color:#555;font-size:1.4em">
            <?php echo $wr_subject ?>
        </h1>
        <span style="display:block;padding:10px 30px 30px;background:#f7f7f7;text-align:right">
            작성자 <?php echo $wr_name ?>
        </span>
        <div style="margin:20px 0 0;padding:30px 30px 50px;min-height:200px;height:auto !important;height:200px;border-bottom:1px solid #eee">
            <?php echo $wr_content ?>
        </div>
        <a href="<?php echo $link_url ?>" style="display:block;padding:30px 0;background:#484848;color:#fff;text-decoration:none;text-align:center">사이트에서 게시물 확인하기</a>
    </div>
</div>

</body>
</html>

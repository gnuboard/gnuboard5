<?php //추천상품 ?>
<!doctype html>
<html lang="ko">
<head>
<meta charset="utf-8">
<title><?php echo $config['cf_title']; ?> - <?php echo $from_name; ?> 의 상품 추천 메일</title>
</head>

<?php
$cont_st = 'margin:0 auto 20px;width:94%;border:0';
$caption_st = 'padding:0 0 5px;font-weight:bold';
$th_st = 'padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;background:#f5f6fa;text-align:left';
$td_st = 'padding:5px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9';
$empty_st = 'padding:30px;border-top:1px solid #e9e9e9;border-bottom:1px solid #e9e9e9;text-align:center';
$ft_a_st = 'display:block;padding:30px 0;background:#484848;color:#fff;text-align:center;text-decoration:none';
?>

<body>

<div style="margin:30px auto;width:600px;border:10px solid #f7f7f7">
    <div style="border:1px solid #dedede">
        <h1 style="margin:0 0 20px;padding:30px 30px 20px;background:#f7f7f7;color:#555;font-size:1.4em">
            <?php echo $config['cf_title'];?> - 상품 추천 메일
        </h1>

        <p style="<?php echo $cont_st; ?>"><b><?php echo $from_name; ?></b> 님께서 추천하신 상품입니다.</p>

        <div style="margin:0 0 10px;text-align:center">
            <a href="<?php echo shop_item_url($it_id); ?>" target="_blank" style="display:inline-block;margin:0 0 10px"><?php echo $it_mimg; ?></a><br>
            <?php echo $it_name; ?>
        </div>

        <p style="<?php echo $cont_st; ?>">
            <br>
            <strong><?php echo $subject; ?></strong>
        </p>

        <p style="<?php echo $cont_st; ?>"><?php echo $content; ?></p>

        <a href="<?php echo shop_item_url($it_id); ?>" target="_blank" style="<?php echo $ft_a_st; ?>">상품 자세히 보기</a>

    </div>
</div>

</body>
</html>

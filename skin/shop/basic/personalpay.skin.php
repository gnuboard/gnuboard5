<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<link rel="stylesheet" href="<?php echo G5_SHOP_SKIN_URL; ?>/style.css">

<?php
for ($i=1; $row=sql_fetch_array($result); $i++) {
    if ($list_mod >= 2) { // 1줄 이미지 : 2개 이상
        if ($i%$list_mod == 0) $sct_last = ' sct_last'; // 줄 마지막
        else if ($i%$list_mod == 1) $sct_last = ' sct_clear'; // 줄 첫번째
        else $sct_last = '';
    } else { // 1줄 이미지 : 1개
        $sct_last = ' sct_clear';
    }

    if ($i == 1) {
        echo "<ul class=\"sct sct_10\">\n";
    }

    $href = G5_SHOP_URL.'/personalpayform.php?pp_id='.$row['pp_id'].'&amp;page='.$page;
?>
    <li class="sct_li<?php echo $sct_last; ?>" style="width:<?php echo $img_width; ?>px">
        <a href="<?php echo $href; ?>" class="sct_a">
            <span class="sct_img"><img src="<?php echo G5_SHOP_SKIN_URL; ?>/img/personal.jpg" alt=""></span>
            <b><?php echo get_text($row['pp_name']).'님 개인결제'; ?></b>
            <span class="sct_cost"><?php echo display_price($row['pp_price']); ?></span>
        </a>
    </li>
<?php
}

if ($i > 1) echo "</ul>\n";

if($i == 1) echo "<p class=\"sct_noitem\">등록된 개인결제가 없습니다.</p>\n";
?>
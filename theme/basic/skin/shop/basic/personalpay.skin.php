<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

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
        echo "<ul class=\"sct sct_pv\">\n";
    }

    $href = G5_SHOP_URL.'/personalpayform.php?pp_id='.$row['pp_id'].'&amp;page='.$page;
?>
    <li class="sct_li<?php echo $sct_last; ?>" style="width:<?php echo $img_width; ?>px">
        <div class="sct_img"><a href="<?php echo $href; ?>" class="sct_a"><img src="<?php echo G5_SHOP_SKIN_URL; ?>/img/personal.jpg" alt=""></a></div>
        <div class="sct_txt"><a href="<?php echo $href; ?>" class="sct_a"><?php echo get_text($row['pp_name']).'님 개인결제'; ?></a></div>
        <div class="sct_cost"><?php echo display_price($row['pp_price']); ?></div>
    </li>
<?php
}

if ($i > 1) echo "</ul>\n";

if($i == 1) echo "<p class=\"sct_noitem\">등록된 개인결제가 없습니다.</p>\n";
?>
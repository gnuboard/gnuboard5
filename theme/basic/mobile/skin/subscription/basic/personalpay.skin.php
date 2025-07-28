<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 개인결제진열 시작 { -->
<?php
$li_width = intval(100 / $list_mod);
$li_width_style = ' style="width:'.$li_width.'%;"';

for ($i=0; $row=sql_fetch_array($result); $i++) {
    if ($i == 0) {
        echo "<ul id=\"sct_wrap\" class=\"sct sct_pv\">\n";
    }

    if($i % $list_mod == 0)
        $li_clear = ' sct_clear';
    else
        $li_clear = '';

    $href = G5_SHOP_URL.'/personalpayform.php?pp_id='.$row['pp_id'].'&amp;page='.$page;
?>
    <li class="sct_li<?php echo $li_clear; ?>"<?php echo $li_width_style; ?>>
        <div class="sct_img"><a href="<?php echo $href; ?>" class="sct_a"><img src="<?php echo G5_MSHOP_SKIN_URL; ?>/img/personal.jpg" alt=""></a></div>
        <div class="sct_txt"><a href="<?php echo $href; ?>" class="sct_a"><?php echo get_text($row['pp_name']).'님 개인결제'; ?></a></div>
        <div class="sct_cost"><?php echo display_price($row['pp_price']); ?></div>
    </li>
<?php
}

if ($i > 0) echo "</ul>\n";

if($i == 0) echo "<p class=\"sct_noitem\">등록된 개인결제가 없습니다.</p>\n";
?>
<!-- } 개인결제진열 끝 -->

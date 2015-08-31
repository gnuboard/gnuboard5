<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_MSHOP_SKIN_URL.'/style.css">', 0);
?>

<?php
$max_width = $max_height = 0;
$bn_first_class = ' class="bn_first"';
$bn_slide_btn = '';
$bn_sl = ' class="bn_sl"';

for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ($i==0) echo '<div id="main_bn" class="swipe">'.PHP_EOL.'<ul class="slide-wrap bn_img">'.PHP_EOL;
    //print_r2($row);
    // 테두리 있는지
    $bn_border  = ($row['bn_border']) ? ' class="sbn_border"' : '';;
    // 새창 띄우기인지
    $bn_new_win = ($row['bn_new_win']) ? ' target="_blank"' : '';

    $bimg = G5_DATA_PATH.'/banner/'.$row['bn_id'];
    if (file_exists($bimg))
    {
        $banner = '';
        $size = getimagesize($bimg);

        if($size[2] < 1 || $size[2] > 16)
            continue;

        if($max_width < $size[0])
            $max_width = $size[0];

        if($max_height < $size[1])
            $max_height = $size[1];

        echo '<li'.$bn_first_class.'>'.PHP_EOL;
        if ($row['bn_url'][0] == '#')
            $banner .= '<a href="'.$row['bn_url'].'">';
        else if ($row['bn_url'] && $row['bn_url'] != 'http://') {
            $banner .= '<a href="'.G5_SHOP_URL.'/bannerhit.php?bn_id='.$row['bn_id'].'&amp;url='.urlencode($row['bn_url']).'"'.$bn_new_win.'>';
        }
        echo $banner.'<img src="'.G5_DATA_URL.'/banner/'.$row['bn_id'].'" width="'.$size[0].'" alt="'.$row['bn_alt'].'"'.$bn_border.'>';
        if($banner)
            echo '</a>'.PHP_EOL;
        echo '</li>'.PHP_EOL;

        $bn_first_class = '';
        $bn_slide_btn .= '<button type="button"'.$bn_sl.'>'.$row['bn_alt'].' 배너이미지</button>'.PHP_EOL;
        $bn_sl = '';
    }
}

if ($i > 0) {
    echo '</ul>'.PHP_EOL;
    echo '<div class="bn_silde_btn">'.$bn_slide_btn.'</div>'.PHP_EOL;
    echo '</div>'.PHP_EOL;
?>

<script>
$(function() {
    $("#main_bn").bannerSlide({
        wrap: ".slide-wrap",
        slides: ".slide-wrap > li",
        buttons: ".bn_silde_btn > button",
        btnActive: "bn_sl",
        startSlide: 0,
        auto: 0
    });
});
</script>

<?php
}
?>
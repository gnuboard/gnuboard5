<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);

$mshop_categories = get_shop_category_array(true);
?>

<!-- 쇼핑몰 카테고리 시작 { -->
<nav id="gnb">
    <h2>쇼핑몰 카테고리</h2>
    <ul id="gnb_1dul">
        <?php
        // 1단계 분류 판매 가능한 것만
        $gnb_zindex = 999; // gnb_1dli z-index 값 설정용
        $i = 0;
        foreach($mshop_categories as $cate1) {
            if( empty($cate1) ) continue;

            $row = $cate1['text'];
            $gnb_zindex -= 1; // html 구조에서 앞선 gnb_1dli 에 더 높은 z-index 값 부여
            // 2단계 분류 판매 가능한 것만
            $count = ((int) count($cate1)) - 1;
        ?>
        <li class="gnb_1dli" style="z-index:<?php echo $gnb_zindex; ?>">
            <a href="<?php echo $row['url']; ?>" class="gnb_1da"><?php echo $row['ca_name']; ?><?php if ($count) echo '<i class="fa fa-angle-right" aria-hidden="true"></i>'; ?></a>
            <?php
            $j=0;
            foreach($cate1 as $key=>$cate2) {
            if( empty($cate2) || $key === 'text' ) continue;
            
            $row2 = $cate2['text'];
            if ($j==0) echo '<ul class="gnb_2dul" style="z-index:'.$gnb_zindex.'">';
            ?>
                <li class="gnb_2dli"><a href="<?php echo $row2['url']; ?>" class="gnb_2da"><?php echo $row2['ca_name']; ?></a></li>
            <?php $j++; }   //end for
            if ($j>0) echo '</ul>';
            ?>
        </li>
        <?php $i++; }   //end for ?>
    </ul>
</nav>
<!-- } 쇼핑몰 카테고리 끝 -->
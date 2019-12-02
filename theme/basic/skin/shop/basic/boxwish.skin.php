<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 위시리스트 간략 보기 시작 { -->
<aside id="wish" class="side-wish">
    <h2 class="s_h2">위시리스트 <span><?php echo get_wishlist_datas_count(); ?></span></h2>
    <ul>
    <?php
    $wishlist_datas = get_wishlist_datas($member['mb_id'], true);
    $i = 0;
    foreach( (array) $wishlist_datas as $row )
    {
        if( !$row['it_id'] ) continue;
        
        $item = get_shop_item($row['it_id'], true);
        
        if( !$item['it_id'] ) continue;

        echo '<li>';
        $it_name = get_text($item['it_name']);

        // 이미지로 할 경우
        $it_img = get_it_image($row['it_id'], 65, 65, true);
        echo '<div class="prd_img">'.$it_img.'</div>';
		echo '<div class="prd_cnt">';
        echo '<a href="'.shop_item_url($row['it_id']).'" class="prd_name">'.$it_name.'</a>';
        echo '<div class="prd_price">'.display_price(get_price($item), $item['it_tel_inq']).'</div>';
		echo '</div>'.PHP_EOL;
        echo '</li>';
        $i++;
    }   //end foreach

    if ($i==0)
        echo '<li class="li_empty">위시리스트 없음</li>'.PHP_EOL;
	?>
    </ul>
</aside>
<!-- } 위시리스트 간략 보기 끝 -->

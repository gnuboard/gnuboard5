<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 장바구니 간략 보기 시작 { -->
<aside id="sbsk" class="op_area">
    <h2>장바구니</h2>
    <form name="skin_frmcartlist" id="skin_sod_bsk_list" method="post" action="<?php echo G5_SHOP_URL.'/cartupdate.php'; ?>">
    <ul>
    <?php
    $cart_datas = get_boxcart_datas(true);
    $i = 0;
    foreach($cart_datas as $row)
    {
        if( !$row['it_id'] ) continue;

        echo '<li>';
        $it_name = get_text($row['it_name']);
        // 이미지로 할 경우
        $it_img = get_it_image($row['it_id'], 60, 60, true);
        echo '<a href="'.G5_SHOP_URL.'/cart.php">'.$it_name.'</a>';
         echo '<div class="prd_img">'.$it_img.'</div>';
        echo '</li>';

        echo '<input type="hidden" name="act" value="buy" >';
        echo '<input type="hidden" name="ct_chk['.$i.']" value="1" >';
        echo '<input type="hidden" name="it_id['.$i.']" value="'.$row['it_id'].'">';
        echo '<input type="hidden" name="it_name['.$i.']"  value="'.$it_name.'">';

        $i++;
    }   //end foreach

    if ($i==0)
        echo '<li class="li_empty">장바구니 상품 없음</li>'.PHP_EOL;
    ?>
    </ul>
    <?php if($i){ ?><button type="submit" class="btn02 btn_buy"><i class="fa fa-credit-card" aria-hidden="true"></i> 바로구매</button><?php } ?>
    <a href="<?php echo G5_SHOP_URL; ?>/cart.php" class="btn01 go_cart">장바구니 바로가기</a>
    </form>
</aside>
<!-- } 장바구니 간략 보기 끝 -->


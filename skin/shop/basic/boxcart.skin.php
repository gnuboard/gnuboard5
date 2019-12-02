<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
$cart_action_url = G5_SHOP_URL.'/cartupdate.php';
?>

<!-- 장바구니 간략 보기 시작 { -->
<aside id="sbsk" class="sbsk">
    <h2 class="s_h2">장바구니 <span class="cart-count"><?php echo get_boxcart_datas_count(); ?></span></h2>
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
        $it_img = get_it_image($row['it_id'], 65, 65, true);
		echo '<div class="prd_img">'.$it_img.'</div>';
		echo '<div class="prd_cnt">';
        echo '<a href="'.G5_SHOP_URL.'/cart.php" class="prd_name">'.$it_name.'</a>';
        echo '<span class="prd_cost">';
		echo number_format($row['ct_price']).PHP_EOL;
        echo '</span>'.PHP_EOL;
		echo '</div>';
		echo '<button class="cart_del" type="button" data-it_id="'.$row['it_id'].'"><i class="fa fa-trash-o" aria-hidden="true"></i><span class="sound_only">삭제</span></button>'.PHP_EOL;
        echo '</li>';

        echo '<input type="hidden" name="act" value="buy">';
        echo '<input type="hidden" name="ct_chk['.$i.']" value="1">';
        echo '<input type="hidden" name="it_id['.$i.']" value="'.$row['it_id'].'">';
        echo '<input type="hidden" name="it_name['.$i.']" value="'.$it_name.'">';

        $i++;
    }   //end foreach

    if ($i==0)
        echo '<li class="li_empty">장바구니 상품 없음</li>'.PHP_EOL;
    ?>
    </ul>
    <?php if($i){ ?><div class="btn_buy"><button type="submit" class="btn_submit">구매하기</button></div><?php } ?>
    <a href="<?php echo G5_SHOP_URL; ?>/cart.php" class="go_cart">전체보기</a>
    </form>
</aside>
<script>
jQuery(function ($) {
    $("#sbsk").on("click", ".cart_del", function(e) {
        e.preventDefault();

        var it_id = $(this).data("it_id");
        var $wrap = $(this).closest("li");

        $.ajax({
            url: g5_shop_url+"/ajax.action.php",
            type: "POST",
            data: {
                "it_id" : it_id,
                "action" : "cart_delete"
            },
            dataType: "json",
            async: true,
            cache: false,
            success: function(data, textStatus) {
                if(data.error != "") {
                    alert(data.error);
                    return false;
                }

                $wrap.remove();
            }
        });
    });
});
</script>
<!-- } 장바구니 간략 보기 끝 -->


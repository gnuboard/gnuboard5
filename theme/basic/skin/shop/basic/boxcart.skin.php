<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 장바구니 간략 보기 시작 { -->
<aside id="sbsk">
    <h2>장바구니</h2>

    <ul>
    <?php
    $hsql  = " select it_id, it_name from {$g5['g5_shop_cart_table']} ";
    $hsql .= " where od_id = '".get_session('ss_cart_id')."' group by it_id ";
    $hresult = sql_query($hsql);
    for ($i=0; $row=sql_fetch_array($hresult); $i++)
    {
        echo '<li>';
        $it_name = get_text($row['it_name']);
        // 이미지로 할 경우
        //$it_name = get_it_image($row['it_id'], 50, 50, true);
        echo '<a href="'.G5_SHOP_URL.'/cart.php">'.$it_name.'</a>';
        echo '</li>';
    }

    if ($i==0)
        echo '<li id="sbsk_empty">장바구니 상품 없음</li>'.PHP_EOL;
?>
    </ul>

</aside>
<!-- } 장바구니 간략 보기 끝 -->


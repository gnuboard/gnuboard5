<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!-- 장바구니 간략 보기 시작 { -->
<aside id="sbsk">
    <h2>장바구니</h2>

    <ul>
    <?php
    $hsql  = " select a.it_id, a.it_name, a.ct_qty from {$g4['shop_cart_table']} a left join {$g4['shop_item_table']} b on ( a.it_id = b.it_id ) ";
    $hsql .= " where a.uq_id = '".get_session('ss_uq_id')."' and a.ct_num  = '0' order by a.ct_id ";
    $hresult = sql_query($hsql);
    for ($i=0; $row=sql_fetch_array($hresult); $i++)
    {
        echo '<li>';
        $it_name = get_text($row['it_name']);
        // 이미지로 할 경우
        //$it_name = get_it_image($row['it_id'], 50, 50, true);
        echo '<a href="'.G4_SHOP_URL.'/cart.php">'.$it_name.'</a>';
        echo '</li>';
    }

    if ($i==0)
        echo '<li id="sbsk_empty">장바구니 상품 없음</li>'.PHP_EOL;
?>
    </ul>

</aside>
<!-- } 장바구니 간략 보기 끝 -->


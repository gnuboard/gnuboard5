<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 쇼핑몰 카테고리 시작 { -->
<nav id="gnb">
    <h2>쇼핑몰 카테고리</h2>
    <ul id="gnb_1dul">
        <?php
        // 1단계 분류 판매 가능한 것만
        $hsql = " select ca_id, ca_name from {$g5['g5_shop_category_table']} where length(ca_id) = '2' and ca_use = '1' order by ca_order, ca_id ";
        $hresult = sql_query($hsql);
        $gnb_zindex = 999; // gnb_1dli z-index 값 설정용
        for ($i=0; $row=sql_fetch_array($hresult); $i++)
        {
            $gnb_zindex -= 1; // html 구조에서 앞선 gnb_1dli 에 더 높은 z-index 값 부여
            // 2단계 분류 판매 가능한 것만
            $sql2 = " select ca_id, ca_name from {$g5['g5_shop_category_table']} where LENGTH(ca_id) = '4' and SUBSTRING(ca_id,1,2) = '{$row['ca_id']}' and ca_use = '1' order by ca_order, ca_id ";
            $result2 = sql_query($sql2);
            $count = mysql_num_rows($result2);
        ?>
        <li class="gnb_1dli" style="z-index:<?php echo $gnb_zindex; ?>">
            <a href="<?php echo G5_SHOP_URL.'/list.php?ca_id='.$row['ca_id']; ?>" class="gnb_1da<?php if ($count) echo ' gnb_1dam'; ?>"><?php echo $row['ca_name']; ?></a>
            <?php
            for ($j=0; $row2=sql_fetch_array($result2); $j++)
            {
            if ($j==0) echo '<ul class="gnb_2dul" style="z-index:'.$gnb_zindex.'">';
            ?>
                <li class="gnb_2dli"><a href="<?php echo G5_SHOP_URL; ?>/list.php?ca_id=<?php echo $row2['ca_id']; ?>" class="gnb_2da"><?php echo $row2['ca_name']; ?></a></li>
            <?php }
            if ($j>0) echo '</ul>';
            ?>
        </li>
        <?php } ?>
    </ul>
</nav>
<!-- } 쇼핑몰 카테고리 끝 -->
<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<nav id="gnb">
    <h2>쇼핑몰 카테고리</h2>
    <ul id="gnb_1dul">
        <?php
        // 1단계 분류 판매 가능한 것만
        $hsql = " select ca_id, ca_name from {$g4['shop_category_table']}
                  where length(ca_id) = '2'
                    and ca_use = '1'
                  order by ca_id ";
        $hresult = sql_query($hsql);
        $hnum = @mysql_num_rows($hresult);
        $gnb_zindex = 10000; // gnb_1dli z-index 값 설정용
        for ($i=0; $row=sql_fetch_array($hresult); $i++)
        {
            $gnb_zindex -= 1; // html 구조에서 앞선 gnb_1dli 에 더 높은 z-index 값 부여
        ?>
        <li class="gnb_1dli" style="z-index:<?php echo $gnb_zindex; ?>">
            <a href="<?php echo G4_SHOP_URL.'/list.php?ca_id='.$row['ca_id']; ?>" class="gnb_1da"><?php echo $row['ca_name']; ?></a>
            <ul class="gnb_2dul">
            <?php
            // 2단계 분류 판매 가능한 것만
             $sql2 = " select ca_id, ca_name from {$g4['shop_category_table']}
               where LENGTH(ca_id) = '4'
                 and SUBSTRING(ca_id,1,2) = '{$row['ca_id']}'
                 and ca_use = '1'
               order by ca_id ";
            $result2 = sql_query($sql2);
            $hnum2 = @mysql_num_rows($result2);
            for ($j=0; $row2=sql_fetch_array($result2); $j++)
            {
            ?>
                <li class="gnb_2dli"><a href="<?php echo G4_SHOP_URL; ?>/list.php?ca_id=<?php echo $row2['ca_id']; ?>" class="gnb_2da"><?php echo $row2['ca_name']; ?></a></li>
            <?php } ?>
            </ul>
        </li>
        <?php } ?>
    </ul>
</nav>

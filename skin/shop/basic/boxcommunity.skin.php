<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 쇼핑몰 커뮤니티 시작 { -->
<aside id="scomm">
    <h2>쇼핑몰 커뮤니티</h2>

    <ul>

    <?php
    $hsql = " select bo_table, bo_subject from {$g5['board_table']} order by gr_id, bo_table ";
    $hresult = sql_query($hsql);
    for ($i=0; $row=sql_fetch_array($hresult); $i++)
    {
        echo '<li><a href="'.G5_BBS_URL.'/board.php?bo_table='.$row['bo_table'].'">'.$row['bo_subject'].'</a></li>'.PHP_EOL;
    }

    if ($i==0)
        echo '<li id="scomm_empty">커뮤니티 준비 중</li>'.PHP_EOL;
    ?>
    </ul>

</aside>
<!-- } 쇼핑몰 커뮤니티 끝 -->
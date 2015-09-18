<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<!-- 쇼핑몰 이벤트 시작 { -->
<aside id="sev">
    <h2>쇼핑몰 이벤트</h2>

    <ul>
    <?php
    $hsql = " select ev_id, ev_subject, ev_subject_strong from {$g5['g5_shop_event_table']} where ev_use = '1' order by ev_id desc ";
    $hresult = sql_query($hsql);
    for ($i=0; $row=sql_fetch_array($hresult); $i++)
    {

        echo '<li>';
        $href = G5_SHOP_URL.'/event.php?ev_id='.$row['ev_id'];

        $event_img = G5_DATA_PATH.'/event/'.$row['ev_id'].'_m'; // 이벤트 이미지

        if (file_exists($event_img)) { // 이벤트 이미지가 있다면 이미지 출력
            echo '<a href="'.$href.'" class="sev_img"><img src="'.G5_DATA_URL.'/event/'.$row['ev_id'].'_m" alt="'.$row['ev_subject'].'"></a>'.PHP_EOL;
        } else { // 없다면 텍스트 출력
            echo '<a href="'.$href.'" class="sev_text">';
            if ($row['ev_subject_strong']) echo '<strong>';
            echo $row['ev_subject'];
            if ($row['ev_subject_strong']) echo '</strong>';
            echo '</a>'.PHP_EOL;
        }
        echo '</li>'.PHP_EOL;

    }

    if ($i==0)
        echo '<li id="sev_empty">이벤트 준비 중</li>'.PHP_EOL;
    ?>
    </ul>

</aside>
<!-- } 쇼핑몰 이벤트 끝 -->
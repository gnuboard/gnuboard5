<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="'.G5_SHOP_SKIN_URL.'/style.css">', 0);
?>

<?php
for ($i=0; $row=sql_fetch_array($result); $i++)
{

    if ($i==0) echo '<aside id="sbn_side" class="sbn"><h2>쇼핑몰 배너</h2><ul>'.PHP_EOL;
    //print_r2($row);
    // 테두리 있는지
    $bn_border  = ($row['bn_border']) ? ' sbn_border' : '';;
    // 새창 띄우기인지
    $bn_new_win = ($row['bn_new_win']) ? ' target="_blank"' : '';

    $bimg = G5_DATA_PATH.'/banner/'.$row['bn_id'];
    if (file_exists($bimg))
    {
        $size = getimagesize($bimg);
        echo '<li>'.PHP_EOL;
        if ($row['bn_url'][0] == '#')
            echo '<a href="'.$row['bn_url'].'">';
        else if ($row['bn_url'] && $row['bn_url'] != 'http://') {
            echo '<a href="'.G5_SHOP_URL.'/bannerhit.php?bn_id='.$row['bn_id'].'&amp;url='.urlencode($row['bn_url']).'"'.$bn_new_win.'>';
        }
        echo '<img src="'.G5_DATA_URL.'/banner/'.$row['bn_id'].'" alt="'.$row['bn_alt'].'" width="'.$size[0].'" height="'.$size[1].'" class="'.$bn_border.'">';
        if($row['bn_url'])
            echo '</a>'.PHP_EOL;
        echo '</li>'.PHP_EOL;
    }
}
if ($i>0) echo '</ul></aside>'.PHP_EOL;
?>
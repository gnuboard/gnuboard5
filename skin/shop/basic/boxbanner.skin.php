<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 배너 출력
$sql = " select * from {$g5['g5_shop_banner_table']} where '".G5_TIME_YMDHIS."' between bn_begin_time and bn_end_time and bn_position = '$position' order by bn_order, bn_id desc ";
$result = sql_query($sql);
?>

<?php
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ($i==0) echo '<ul>'.PHP_EOL;
    //print_r2($row);
    // 테두리 있는지
    $bn_border  = $row['bn_border'];
    // 새창 띄우기인지
    $bn_new_win = ($row['bn_new_win']) ? ' target="'.$row['bn_new_win'].'"' : '';

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
        echo '<img src="'.G5_DATA_URL.'/banner/'.$row['bn_id'].'" alt="'.$row['bn_alt'].'" width="'.$size[0].'" height="'.$size[1].'"></a>'.PHP_EOL;
        echo '</li>'.PHP_EOL;
    }
}
if ($i>0) echo '</ul>'.PHP_EOL;
?>
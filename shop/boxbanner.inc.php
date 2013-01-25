<?
// 배너 출력
$sql = " select * from $g4[yc4_banner_table] 
          where '$g4[time_ymdhis]' between bn_begin_time and bn_end_time 
            and bn_position = '$position'
          order by bn_order, bn_id desc ";
$result = sql_query($sql);
?>
<table width=100% cellpadding=0 cellspacing=0>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    //print_r2($row);
    // 테두리 있는지
    $bn_border  = $row[bn_border];
    // 새창 띄우기인지
    $bn_new_win = ($row[bn_new_win]) ? "target=".$row[bn_new_win] : "";

    $bimg = "$g4[path]/data/banner/$row[bn_id]";
    if (file_exists($bimg)) 
    {
        $size = getimagesize($bimg);
        echo "<tr><td>";
        if ($row[bn_url][0] == "#")
            echo "<a href='$row[bn_url]'>";
        else if ($row[bn_url] && $row[bn_url] != "http://") {
            echo "<a href='$g4[shop_path]/bannerhit.php?bn_id={$row[bn_id]}&url=".urlencode($row[bn_url])."' $bn_new_win>";
        }
        echo "<img src='$bimg' border='{$bn_border}' alt='{$row[bn_alt]}' width='$size[0]' height='$size[1]'></a>";
        echo "</td></tr>\n";
    }
}
?>
</table>
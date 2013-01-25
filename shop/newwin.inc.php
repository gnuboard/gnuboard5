<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

$sql = " select * from $g4[yc4_new_win_table] 
          where '$g4[time_ymdhis]' between nw_begin_time and nw_end_time
          order by nw_id asc ";
$result = sql_query($sql);
for ($i=0; $row_nw=sql_fetch_array($result); $i++) 
{
    // 이미 체크 되었다면 Continue
    if ($_COOKIE["ck_notice_{$row_nw[nw_id]}"]) 
        continue;
?>
    <script language="JavaScript">
    var opt = "scrollbars=yes,width=<?=$row_nw[nw_width]+20?>,height=<?=($row_nw[nw_height]+5)?>,top=<?=$row_nw[nw_top]?>,left=<?=$row_nw[nw_left]?>";
    popup_window("<?=$g4[shop_path]?>/newwinpop.php?nw_id=<?=$row_nw[nw_id]?>", "WINDOW_<?=$row_nw[nw_id]?>", opt);
    </script>
<? } ?>

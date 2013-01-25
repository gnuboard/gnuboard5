<?
include_once("./_common.php");

$sql = " select * from $g4[yc4_new_win_table] where nw_id = '$nw_id' ";
$nw = sql_fetch($sql);

$g4[title] = $nw[nw_subject];

$nw[nw_subject] = get_text($nw[nw_subject]);
$nw[nw_content] = conv_content($nw[nw_content], 1);

include_once("$g4[path]/head.sub.php");
?>

<script language="JavaScript">
    function div_close_<? echo $nw[nw_id] ?>() 
    {
        if (check_notice_<? echo $nw[nw_id] ?>.checked == true) {
              set_cookie("ck_notice_<? echo $nw[nw_id] ?>", "1" , <? echo $nw[nw_disable_hours] ?>);
        }
        window.close();
    }
</script>

<div id="div_notice_<? echo $nw[nw_id] ?>">
<table width="<? echo $nw[nw_width] ?>" height="<? echo $nw[nw_height] ?>" cellpadding="0" cellspacing="0">
<tr>	
    <td valign=top><?=conv_content($nw[nw_content], $nw[nw_content_html]);?></td>
</tr>
<tr>
    <td height=30 align=center><input type=checkbox id='check_notice_<?=$nw[nw_id]?>' name='check_notice_<?=$nw[nw_id]?>' value='1' onclick="div_close_<? echo $nw[nw_id] ?>();"><font color="<? echo $nw[nw_font_color] ?>">&nbsp;<label for='check_notice_<?=$nw[nw_id]?>'>오늘 하루 이창을 열지 않습니다.</label><!-- <? echo $nw[nw_disable_hours] ?> 시간동안 이창을 다시 띄우지 않겠습니다. --></font></td>
</tr>	
</table>
</div>

<?
include_once("$g4[path]/tail.sub.php");
?>
<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if ($ca_id)
{
    $str = $bar = "";
    $len = strlen($ca_id) / 2;
    for ($i=1; $i<=$len; $i++)
    {
        $code = substr($ca_id,0,$i*2);

        $sql = " select ca_name from {$g4['shop_category_table']} where ca_id = '$code' ";
        $row = sql_fetch($sql);

        $style = "";
        if ($ca_id == $code)
            $style = "style='font-weight:bold;'";

        $str .= $bar . "<a href='./list.php?ca_id=$code' $style>{$row['ca_name']}</a>";
        $bar = " > ";
    }
}
else
    $str = $g4['title'];

//if ($it_id) $str .= " > $it[it_name]";
?>

<!-- 네비게이션 -->
<table width='100%' cellpadding=0 cellspacing=0 align=center>
<tr><td height=2></td>
<tr><td height=20 valign=top style='padding-left:2px;'><img src='<?=$g4[shop_img_path]?>/navi_icon.gif' align=absmiddle> 
    현재위치 : <a href='<?=$g4[path]?>/'>Home</a> > <?=$str?></td></tr>
<tr><td height=1 bgcolor=#EFEFEF></td></tr>
</table>

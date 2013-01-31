<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<table width=100% cellpadding=2 cellspacing=0>
<tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ( ($i>0) && (($i%$list_mod)==0) )
    {
        echo "</tr>\n\n";
        echo "<tr><td colspan='$list_mod' background='".G4_SHOP_IMG_URL."/line_h.gif' height=1></td></tr>\n\n";
        echo "<tr>\n";
    }

    echo "
    <td width='{$td_width}%' align=center valign=top>
        <br>
        <table width=98% cellpadding=2 cellspacing=0>
        <tr><td align=center>".get_it_image($row['it_id'], $img_width , $img_height, $row['it_id'])."</td></tr>
        <tr><td align=center>".it_name_icon($row)."</td></tr>";

    if ($row[it_cust_amount] && !$row[it_gallery])
        echo "<tr><td align=center><strike>".display_amount($row[it_cust_amount])."</strike></td></tr>";

    echo "<tr><td align=center>";

    if (!$row[it_gallery])
        echo "<span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span>";

    echo "</td></tr></table></td>";
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "    <td>&nbsp;</td>\n";
?>
</tr>
</table>

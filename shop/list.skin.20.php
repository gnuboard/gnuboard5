<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width=100% cellpadding=2 cellspacing=0 border=0>
<tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++)
{
    if ( ($i>0) && (($i%$list_mod)==0) )
    {
        echo "</tr>\n\n";
        echo "<tr><td colspan=" . ($list_mod + $list_mod - 1) . " height=1></td></tr>\n\n";
        echo "<tr>\n";
    }

    $image = get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id]);

    echo "
    <td width='$td_width%' align=center valign=top>
        <table width=100% cellpadding=2 cellspacing=0 border=0>
        <tr>
            <td align=center width=40>&nbsp;$image</td>
            <td>".it_name_icon($row)."</td>
            <td width=80 align=right>";
    
    if (!$row[it_gallery])
        echo "<span class=amount>" . display_amount(get_amount($row), $row[it_tel_inq]) . "</span>";

    echo "</td></td>
        </tr>
        </table>
    </td>";

    // 세로줄        
    if (($i%$list_mod) != ($list_mod-1))
        echo "<td width=10 align=center></td>";
}
// 나머지 td 를 colspan 으로 채운다.
$cnt = $list_mod - ($i % $list_mod);
$cnt = $cnt + $cnt - 1;
echo "<td colspan=$cnt>&nbsp;</td>\n";
?>
</tr>
</table>

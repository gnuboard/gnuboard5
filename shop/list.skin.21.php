<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width=100% cellpadding=4 cellspacing=1>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) {
    // 가로줄 
    if ($i>0)
        echo "<tr><td colspan=5 background='$g4[shop_img_path]/line_h.gif' height=1></td></tr>\n";

    echo "
    <tr>
        <td>
            <table width=100% cellpadding=0 cellspacing=0>
            <tr>
                <td width='".($img_width+10)."' align=center>".get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id])."</td>
                <td>".it_name_icon($row)."</td>
            </tr>
            </table>
        </td>
        <td align=center>$row[it_maker]</td>
        <td align=right>";

        if (!$row[it_gallery])
        {
            echo "<span class=amount>".display_amount(get_amount($row), $row[it_tel_inq])."</span>";
            echo "<br>".display_point($row[it_point]);
        }

        echo "</td>";
        echo "<td align=center>";

        echo "<a href='./wishupdate.php?it_id=$row[it_id]'><img src='$g4[shop_img_path]/btn_wish2.gif' border=0 alt='보관함'></a>";

    echo "</td></tr>";
}
mysql_free_result($result);

if ($i == 0)
    echo "<tr><td colspan=5 align=center><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
<tr><td colspan=5 background='<?="$g4[shop_img_path]/line_h.gif"?>' height=1></td></tr>
</table>

<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    if ($i > 0 && $i % $list_mod == 0) 
        echo "</tr>\n\n<tr>\n";

    $href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' class=item>";

    // 고객선호도
    $star = "";
    if ($score = get_star_image($row[it_id]))
        $star = "<img src='$g4[shop_img_path]/star{$score}.gif' border=0>";

    $sql2 = " select * from $g4[shop_item_table] where it_id = '$row[it_id]' ";
    $row2 = sql_fetch($sql2);

    // 특정상품아이콘
    $icon = "";
    if ($row2[it_type1]) $icon .= " <img src='$g4[shop_img_path]/icon_type1.gif' border=0 align=absmiddle>"; 
    if ($row2[it_type2]) $icon .= " <img src='$g4[shop_img_path]/icon_type2.gif' border=0 align=absmiddle>"; 
    if ($row2[it_type3]) $icon .= " <img src='$g4[shop_img_path]/icon_type3.gif' border=0 align=absmiddle>"; 
    if ($row2[it_type4]) $icon .= " <img src='$g4[shop_img_path]/icon_type4.gif' border=0 align=absmiddle>"; 
    if ($row2[it_type5]) $icon .= " <img src='$g4[shop_img_path]/icon_type5.gif' border=0 align=absmiddle>"; 
?>
    <td width="<?=$td_width?>%" align=center valign=top>
        <table width=98% cellpadding=1 cellspacing=0 border=0>
        <tr><td height=5></td></tr>
        <tr><td align=center><?=$href?><?=get_it_image($row[it_id]."_s", $img_width, $img_height)?></a></td></tr>
        <tr><td align=center><?=$href?><?=stripslashes($row[it_name])?></a></td></tr>
        <!--시중가격<tr><td align=center><strike><?=display_amount($row[it_cust_amount])?></strike></td></tr>-->
        <tr><td align=center><span class=amount><?=display_amount(get_amount($row), $row[it_tel_inq])?></span></td></tr>
        <tr><td align=center><?=$star?></td></tr>
        <tr><td align=center><?=$row2[it_maker]?></td></tr>
        <tr><td align=center><?=$icon?></td></tr>
        <tr><td align=center><?=number_format($row2[it_point])?> 점</td></tr>
        <?
        $large_image = get_large_image($row[it_id]."_l1", $row[it_id]);
        if ($large_image) 
            echo "<tr><td align=center>$large_image</td></tr>";
        ?>
        </table></td>
<?
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "    <td>&nbsp;</td>\n";
?>
</tr>
</table>

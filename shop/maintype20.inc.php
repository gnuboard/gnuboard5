<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    if ($i > 0 && $i % $list_mod == 0)
        echo "</tr><tr>";

    $href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' class=item>";
?>
    <td width="<? echo $td_width ?>%" align=left valign=top>
        <table width=98% cellpadding=2 cellspacing=0 border=0>
        <tr>
            <td rowspan=2 width='' align=center>&nbsp;<?=$href?><?=get_it_image($row[it_id]."_s", $img_width, $img_height)?></a>&nbsp;</td>
            <td width=50%><?=$href?><?=stripslashes($row[it_name])?></a></td>
            <td width=30% align=right valign=bottom><span class=amount><?=display_amount(get_amount($row), $row[it_tel_inq])?></span>&nbsp;</td>
        </tr>
        <tr>
            <td colspan=2><?=$row[it_basic]?>&nbsp;</td>
        </tr>
        </table></td>
<?
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "<td>&nbsp;</td>\n";
?>
</tr>
</table>

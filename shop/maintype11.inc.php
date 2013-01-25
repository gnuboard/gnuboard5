<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

/*
** maintype1.inc.php 에 이미지 크게 보기 기능만 추가
*/
?>

<script language="JavaScript" src="<?=$g4[path]?>/js/shop.js"></script>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
<?
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    if ($i > 0 && $i % $list_mod == 0) 
        echo "</tr>\n\n<tr>\n";

    $href = "<a href='$g4[shop_path]/item.php?it_id=$row[it_id]' class=item>";
?>
    <td width="<?=$td_width?>%" align=center valign=top>
        <table width=98% cellpadding=1 cellspacing=0 border=0>
        <tr><td height=5></td></tr>
        <tr><td align=center><?=$href?><?=get_it_image($row[it_id]."_s", $img_width, $img_height)?></a></td></tr>
        <tr><td align=center><?=$href?><?=stripslashes($row[it_name])?></a></td></tr>
        <!--시중가격<tr><td align=center><strike><?=display_amount($row[it_cust_amount])?></strike></td></tr>-->
        <tr><td align=center><span class=amount><?=display_amount(get_amount($row), $row[it_tel_inq])?></span></td></tr>
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

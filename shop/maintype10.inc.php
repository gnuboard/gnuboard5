<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
<?php
for ($i=0; $row=sql_fetch_array($result); $i++) {
    if ($i > 0 && $i % $list_mod == 0) {
        echo "</tr>\n\n<tr>\n";
    }

    $href = "<a href='".G4_SHOP_URL."/item.php?it_id={$row['it_id']}' class=item>";
?>
    <td width="<?php echo $td_width; ?>%" align=center valign=top>
        <table width=98% cellpadding=1 cellspacing=0 border=0>
        <tr><td height=5></td></tr>
        <tr><td align=center><?php echo $href; ?><?php echo get_it_image($row['it_id']."_s", $img_width, $img_height); ?></a></td></tr>
        <tr><td align=center><?php echo $href; ?><?php echo stripslashes($row['it_name']); ?></a></td></tr>
        <!--시중가격<tr><td align=center><strike><?php echo display_amount($row[it_cust_amount]); ?></strike></td></tr>-->
        <tr><td align=center><span class=amount><?php echo display_amount(get_amount($row), $row['it_tel_inq']); ?></span></td></tr>
        </table></td>
<?php
/*
// 이미지 오른쪽에 구분선을 두는 경우 (이미지로 대체 가능)
    if ($i%$list_mod!=$list_mod-1)
        echo "<td width=1 bgcolor=#eeeeee></td>";
*/
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "<td>&nbsp;</td>\n";
?>
</tr>
</table>

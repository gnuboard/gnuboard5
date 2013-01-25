<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<table width=100% cellpadding=4 cellspacing=1>
<?
$btn_img = "<img src='$g4[shop_img_path]/btn_cart_in.gif' border=0 alt='장바구니 담기'>";
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    // 가로줄 
    if ($i>0)
        echo "<tr><td colspan=5 background='$g4[shop_img_path]/line_h.gif' height=1></td></tr>\n";

    $onclick_str = "";

    // 옵션이 있는 상품은 선택할 수 없음
    if (preg_match("/;|\\r/", trim($row['it_opt1']).trim($row['it_opt2']).trim($row['it_opt3']).trim($row['it_opt4']).trim($row['it_opt5']).trim($row['it_opt6']))) {
        $onclick_str = "옵션이 있는 상품이므로 바로 장바구니에 담을 수 없습니다.";
    }

    $it_amount = get_amount($row);

    echo "
    <form name='flistskin7_$i' method='post' action='./cartupdate.php'>
    <input type='hidden' name='sw_direct' value='0'>
    <input type='hidden' name='it_id' value='$row[it_id]'>
    <input type='hidden' name='it_name' value='".stripslashes($row[it_name])."'>
    <input type='hidden' name='it_amount' value='$it_amount'>
    <input type='hidden' name='it_point' value='$row[it_point]'>
    <input type='hidden' name='ct_qty' value='1'>
    <tr>
        <td>
            <table width=100% cellpadding=0 cellspacing=0>
            <tr>
                <td width='".($img_width+20)."' align=center>".get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id])."</td>
                <td>
                    <a href='./item.php?it_id=$row[it_id]' class=item>".it_name_icon($row)."</a><br>
                    ".get_text($row['it_basic'])."
                </td>
            </tr>
            </table>
        </td>
        <td align=center>$row[it_maker]</td>
        <td align=right>";

    if (!$row['it_gallery']) {
        echo "
            <span class=amount>".display_amount($it_amount, $row['it_tel_inq'])."</span>
            <br>".display_point($row['it_point'])."</td>";
    }

    echo "<td align=center>";

    if (!$row['it_gallery']) {
        if ($onclick_str)
            echo "<a href=\"javascript:alert('$onclick_str'); location.href='./item.php?it_id=$row[it_id]';\">$btn_img</a>";
        else
            echo "<a href=\"javascript:document.flistskin7_$i.submit();\">$btn_img</a>";
    }

    echo "</td></tr></form>";
}

if ($i == 0)
    echo "<tr><td colspan=5 align=center><span class=point>자료가 한건도 없습니다.</span></td></tr>\n";
?>
<tr><td colspan=5 background='<?="$g4[shop_img_path]/line_h.gif"?>' height=1></td></tr>
</table>

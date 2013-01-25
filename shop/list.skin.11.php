<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 

/* 
** 1.00.03
** 여러가지 상품을 선택하여 한꺼번에 바로구매 및 장바구니에 담기할 수 있는 페이지 입니다.
** 그러나 옵션이 있는 상품, 경매 또는 공동구매가 진행중인 상품은 선택할 수 없습니다.
*/
?>

<table width=100% cellpadding=2 cellspacing=0>
<form name=flist3 method=post action="./cartupdate.php">
<input type=hidden name=w value="multi">
<input type=hidden name=sw_direct value="">
<tr>
<?
for ($i=0; $row=mysql_fetch_array($result); $i++) {
    if ( ($i>0) && (($i%$list_mod)==0) ) {
        echo "</tr>\n\n";
        echo "<tr><td colspan='$list_mod' height=1></td></tr>\n\n";
        echo "<tr>\n";
    }

    echo "
    <td width='{$td_width}%' align=center valign=top>
        <br>
        <table width=100% cellpadding=2 cellspacing=0>
        <tr><td align=center>".get_it_image($row[it_id]."_s", $img_width , $img_height, $row[it_id])."</td></tr>
        <tr><td align=center>".it_name_icon($row)."</td></tr>";
    
    if ($row[it_cust_amount] && !$row[it_gallery])
    {
        echo "<tr><td align=center><strike>".display_amount($row[it_cust_amount], $row[it_tel_inq])."</strike></td></tr>";
    }

    $onclick_str = "";

    // 옵션이 있는 상품은 선택할 수 없음
    if (preg_match("/;|\\r/", trim($row[it_opt1]).trim($row[it_opt2]).trim($row[it_opt3]).trim($row[it_opt4]).trim($row[it_opt5]).trim($row[it_opt6]))) {
        $onclick_str = "옵션이 있는 상품은 선택하실 수 없습니다.";
    }

    if ($onclick_str) {
         $onclick_str = "onclick=\"alert('$onclick_str'); this.checked=false;\"";
    } else {
         $onclick_str = "onclick=\"document.flist3.elements['ct_qty[$i]'].value = this.checked ? '1' : '0';\"";
    }

    $it_amount = get_amount($row);

    echo "<tr><td align=center>\n";
    echo "<input type=hidden name=it_name[$i] value='".stripslashes($row[it_name])."'>\n";
    echo "<input type=hidden name=it_amount[$i] value='$it_amount'>\n";
    echo "<input type=hidden name=it_point[$i] value='$row[it_point]'>\n";
    echo "<input type=hidden name=ct_qty[$i] value='0'>";
    echo "<input type=hidden name=it_id[$i] value='$row[it_id]' ".$onclick_str.">\n";
    echo "<input type=checkbox name=chk[$i] ".$onclick_str.">\n";
    
    if (!$row[it_gallery])
        echo "<span class=amount>".display_amount($it_amount)."</span>";

    echo "</td></tr>";
    echo "</table></td>";
}

// 나머지 td 를 채운다.
if (($cnt = $i%$list_mod) != 0)
    for ($k=$cnt; $k<$list_mod; $k++)
        echo "    <td>&nbsp;</td>\n";
$length = $i;
?>
</tr>
<tr>
    <td colspan="<?=$list_mod?>" align=right height=40 valign=bottom>
        <a href="javascript:flist3_check('buy');"><img src="<?=$g4[shop_img_path]?>/btn_buy.gif" border=0 alt="구매하기"></a>
        &nbsp;
        <a href="javascript:flist3_check('cart');"><img src="<?=$g4[shop_img_path]?>/btn_cart_in.gif" border=0 alt="장바구니에 담기"></a>
        &nbsp;
    </td>
</tr>
</form>
</table>

<script language="JavaScript">
function flist3_check(act)
{
    var f = document.flist3;

    if (act == 'buy') // 바로 구매
        f.sw_direct.value = '1';
    else  // 장바구니에 담기
        f.sw_direct.value = '0';

    checked = false;
    for (i=0; i<<? echo $length ?>; i++) 
    {
        if (f.elements['chk['+i+']'].checked) 
        {
            checked = true;
            break;
        }
    }

    if (checked == false) {
        alert("상품을 한개 이상 선택하여 주십시오.");
        return;
    }

    f.submit();
}
</script>

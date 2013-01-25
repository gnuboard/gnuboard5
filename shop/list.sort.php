<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가 
?>

<br>
<table width=98% cellpadding=0 cellspacing=0 align=center>
<tr>
    <td width=50%>총 <span class=point><b><? echo number_format($total_count) ?></b></span>개의 상품이 있습니다.</td>
    <td width=50% align=right style='padding-top:3px; padding-bottom:3px;'>
        <select id=it_sort name=sort onchange="if (this.value=='') return; document.location = '<? echo "$_SERVER[PHP_SELF]?ca_id=$ca_id&skin=$skin&ev_id=$ev_id&sort=" ?>'+this.value;" class=small>
            <option value=''>출력 순서
            <option value=''>---------
            <option value='it_amount asc'>낮은가격순
            <option value='it_amount desc'>높은가격순
            <option value='it_name asc'>상품명순
            <option value='it_type1 desc'>히트상품
            <option value='it_type2 desc'>추천상품
            <option value='it_type3 desc'>최신상품
            <option value='it_type4 desc'>인기상품
            <option value='it_type5 desc'>할인상품
        </select>
    </td>
</tr>
<tr><td colspan="2" background='<? echo "$g4[shop_img_path]/line_h.gif" ?>' height=1></td></tr>
</table>

<script language='JavaScript'>
document.getElementById('it_sort').value="<?=$sort?>";
</script>

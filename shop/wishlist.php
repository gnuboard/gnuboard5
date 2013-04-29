<?php
include_once('./_common.php');

if (!$is_member)
    goto_url(G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL.'/mypage.php'));

$g4['title'] = "보관함";
include_once('./_head.php');
?>

<img src="<?php echoG4_SHOP_URL; ?>/img/top_wishlist.gif" border="0"><p>

<form name=fwishlist method=post action="" style="padding:0px;">
<input type=hidden name=act       value="multi">
<input type=hidden name=sw_direct value=''>
<input type=hidden name=prog      value='wish'>
<table width=100% align=center cellpadding=0 cellspacing=0>
<tr><td colspan=6 height=2 class=c1></td></tr>
<tr align=center height=28 class=c2>
    <td colspan=2>상품명</td>
    <td width=120>보관일시</td>
    <td width=100>상품체크</td>
    <td width=50>삭제</td>
</tr>
<tr><td colspan=6 height=1 class=c1></td></tr>
<?php
$sql = " select *
           from {$g4['shop_wish_table']} a,
                {$g4['shop_item_table']} b
          where a.mb_id = '{$member['mb_id']}'
            and a.it_id  = b.it_id
          order by a.wi_id desc ";
$result = sql_query($sql);
for ($i=0; $row = mysql_fetch_array($result); $i++) {

    $out_cd = "";
    for($k=1; $k<=6; $k++){
        $opt = trim($row["it_opt{$k}"]);
        if(preg_match("/\n/", $opt)||preg_match("/;/" , $opt)) {
            $out_cd = "no";
            break;
        }
    }

    $it_amount = get_amount($row);

    if ($row['it_tel_inq']) $out_cd = "tel_inq";

    if ($i > 0)
        echo "<tr><td colspan=\"20\" height=\"1\" background=\"".G4_SHOP_URL."/dot_line.gif\"></td></tr>\n";

    $image = get_it_image($row['it_id']."_s", 50, 50, $row['it_id']);

    $s_del = "<a href=\"./wishupdate.php?w=d&wi_id={$row['wi_id']}\"><img src=\"".G4_SHOP_URL."/img/btn_del.gif\" border=\"0\" align=\"absmiddle\" alt=\"삭제\"></a>";

    echo "<tr>\n";
    echo "<td align=center style='padding-top:5px; padding-bottom:5px;'>$image</td>\n";
    echo "<td><a href=\"./item.php?it_id={$row['it_id']}\">".stripslashes($row['it_name'])."</a></td>\n";
    echo "<td align=center>{$row['wi_time']}</td>\n";
    echo "<td align=center>";
    // 품절검사
    $it_stock_qty = get_it_stock_qty($row['it_id']);
    if($it_stock_qty <= 0)
    {
        echo "<img src=\"".G4_SHOP_URL."/img/icon_pumjul.gif\" border=\"0\" align=\"absmiddle\">";
        echo "<input type=hidden name=it_id[$i] >";
    } else { //품절이 아니면 체크할수 있도록한다
        echo "<input type=checkbox name=it_id[$i]     value='$row[it_id]' onclick=\"out_cd_check(this, '$out_cd');\">";
    }
    echo "<input type=hidden   name=it_name[$i]   value='{$row['it_name']}'>";
    echo "<input type=hidden   name=it_amount[$i] value='$it_amount'>";
    echo "<input type=hidden   name=it_point[$i]  value='{$row['it_point']}'>";
    echo "<input type=hidden   name=ct_qty[$i]    value='1'>";
    echo "</td>\n";
    echo "<td align=center>$s_del</td>\n";
    echo "</tr>\n";
}

if ($i == 0)
    echo "<tr><td colspan=20 align=center height=100><span class=point>보관함이 비었습니다.</span></td></tr>\n";
?>
</tr>
<tr><td colspan=6 height=1 class=c1></td></tr>
</table><br>
</form>

<div align=right>
    <a href="javascript:fwishlist_check(document.fwishlist,'');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn_cart_in.gif' border=0></a>
    <a href="javascript:fwishlist_check(document.fwishlist,'direct_buy');"><img src='<?php echo G4_SHOP_URL; ?>/img/btn_buy.gif' border=0></a>&nbsp;
</div>

<script language="JavaScript">
<!--
	function out_cd_check(fld, out_cd)
    {
        if (out_cd == 'no'){
            alert("옵션이 있는 상품입니다.\n\n상품을 클릭하여 상품페이지에서 옵션을 선택한 후 주문하십시오.");
            fld.checked = false;
            return;
        }

        if (out_cd == 'tel_inq'){
            alert("이 상품은 전화로 문의해 주십시오.\n\n장바구니에 담아 구입하실 수 없습니다.");
            fld.checked = false;
            return;
        }
	}

	function fwishlist_check(f, act)
    {
        var k = 0;
		var length = f.elements.length;

        for(i=0; i<length; i++) {
            if (f.elements[i].checked) {
                k++;
            }
        }

        if(k == 0)
        {
            alert("상품을 하나 이상 체크 하십시오");
            return;
        }

        if (act == "direct_buy")
        {
            f.sw_direct.value = 1;
        }
        else
        {
            f.sw_direct.value = 0;
        }

        f.action="./cartupdate.php";

        f.submit();
    }
//-->
</script>

<?php
include_once('./_tail.php');
?>
<?
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
include_once(G4_LIB_PATH.'/thumbnail.lib.php');

/*
    $s_page 는 cart.php 일때 수량의 수정, 물품의 삭제를 위한 변수이다.
               orderinquiryview.php 일때 배송상태등을 나타내는 변수이다.
*/

if($sw_direct != 1)
    $sw_direct = 0;

if ($s_page == 'orderinquiryview.php')
    $colspan = 7;
else
    $colspan = 6;

if($s_page == 'cart.php')
    $colspan2 = 3;
else
    $colspan2 = 4;
?>

<style type="text/css">
<!--
.view-options { cursor: pointer; }
.edit-options { cursor: pointer; }
.options-list { display: none; }
.coupon-link { cursor: pointer; }
-->
</style>

<form name="frmcartlist" method="post" style="padding:0px;">
<table width="98%" cellpadding="0" cellspacing="0" align="center">
<colgroup width="80">
<colgroup width="">
<?php if($s_page == 'orderform.php') { ?>
<colgroup width="80">
<?php } ?>
<colgroup width="80">
<colgroup width="80">
<colgroup width="80">
<?php if($s_page == 'orderinquiryview.php') echo '<colgroup width=80>'; ?>
<?php if ($s_page == 'cart.php' || $s_page == 'orderinquiryview.php') echo '<colgroup width="50">'; ?>
<tr><td colspan="<? echo $colspan; ?>" height="2" class="c1"></td></tr>
<tr align="center" height="28" class="c2">
    <td colspan="2">상품명</td>
    <?php if($s_page == 'orderform.php') { ?>
    <td>쿠폰</td>
    <?php } ?>
    <?php if($s_page == 'orderinquiryview.php') { ?>
    <td>쿠폰할인</td>
    <? } ?>
    <td>수량</td>
    <td>주문금액</td>
    <td>포인트</td>
<?
if ($s_page == 'cart.php')
    echo '<td><input type="checkbox" name="all_chk" value="1" /></td>';
else if ($s_page == 'orderinquiryview.php')
    echo '<td>상태</td>';
?>
</tr>
<tr><td colspan="<? echo $colspan; ?>" height="1" class="c1"></td></tr>
<?
$tot_point = 0;
$tot_sell_amount = 0;
$tot_cancel_amount = 0;

// 장바구니 자료 쿼리
if($is_member && ($s_page == "cart.php" || $s_page == "orderform.php"))
    $sql_where = " ( a.uq_id = '$s_uq_id' or a.mb_id = '{$member['mb_id']}' ) ";
else
    $sql_where = " a.uq_id = '$s_uq_id' ";

$sql = " select a.ct_id,
                a.uq_id,
                a.it_id,
                a.it_name,
                a.is_option,
                a.it_amount,
                a.ct_point,
                a.ct_qty,
                a.ct_status,
                a.ct_send_cost_pay,
                b.ca_id,
                b.ca_id2,
                b.ca_id3,
                b.it_option_use,
                b.it_supplement_use,
                b.it_nocoupon,
                b.it_notax
          from {$g4['shop_cart_table']} as a left join {$g4['shop_item_table']} as b on ( a.it_id = b.it_id )
            where $sql_where
              and a.ct_parent = '0'
              and a.ct_direct = '$sw_direct' ";

if($act == "selectedbuy")
    $sql .= " and a.ct_selected = '1' ";

if($s_page == "cart.php" || $s_page == "orderform.php")
    $sql .= " and a.ct_status = '쇼핑' ";

$sql .= " order by a.ct_id ";

$result = sql_query($sql);

$goods_count = 0;
$itemlist = array();
for ($i=0; $row=mysql_fetch_array($result); $i++)
{
    // 합계금액등 계산
    $sql = " select SUM((ct_amount + it_amount) * ct_qty) as sum_amount,
                    SUM(cp_amount) as dc_amount,
                    SUM(ct_point * ct_qty) as sum_point,
                    SUM(ct_qty) as sum_qty
                from {$g4['shop_cart_table']}
                  where ct_id = '{$row['ct_id']}'
                    or ct_parent = '{$row['ct_id']}' ";
    $sum = sql_fetch($sql);

    // 상품관련정보 $itemlist 에 저장해 배송비계산, 주문폼에서 사용
    $itemlist[$i]['ct_id'] = $row['ct_id'];
    $itemlist[$i]['it_id'] = $row['it_id'];
    $itemlist[$i]['it_name'] = $row['it_name'];
    $itemlist[$i]['amount'] = $sum['sum_amount'];
    $itemlist[$i]['qty'] = $sum['sum_qty'];
    $itemlist[$i]['pay'] = $row['ct_send_cost_pay'];
    $itemlist[$i]['notax'] = $row['it_notax'];
    $goods_count++;

    // 선택, 추가 옵션개수
    $opt_count = $spl_count = 0;
    if($row['it_option_use']) {
        $sql2 = " select COUNT(*) as cnt from {$g4['shop_option_table']} where it_id = '{$row['it_id']}' ";
        $row2 = sql_fetch($sql2);
        $opt_count = (int)$row2['cnt'];
    }
    $spl_count = 0;
    if($row['it_supplement_use']) {
        $sql2 = " select COUNT(*) as cnt from {$g4['shop_supplement_table']} where it_id = '{$row['it_id']}' ";
        $row2 = sql_fetch($sql2);
        $spl_count = (int)$row2['cnt'];
    }

    if ($i==0) { // 계속쇼핑
        $continue_ca_id = $row['ca_id'];
    }

    if ($s_page == "cart.php" || $s_page == "orderinquiryview.php") { // 링크를 붙이고
        $a1 = "<a href='./item.php?it_id={$row['it_id']}'>";
        $a2 = "</a>";
        $image = get_it_image($row['it_id'], 50, 50, $row['it_id']);
    } else { // 붙이지 않고
        $a1 = "";
        $a2 = "";
        $image = get_it_image($row['it_id'], 50, 50);
    }

    $it_name = $a1 . stripslashes($row['it_name']) . $a2 . '<br />';
    if($opt_count > 0 || $spl_count > 0) {
        $it_name .= "\n".'<span class="view-options">선택사항보기</span>'."\n";
        if($s_page == "cart.php") {
            $it_name .= '&nbsp;&nbsp;<span id="cartitem-'.$row['it_id']. '" class="edit-options">선택사항/수량변경</span>';
        }

        $it_name .= '<br />'."\n".'<span class="options-list">' . print_cart_options($row['uq_id'], $row['it_id'], $sw_direct) . '</span>';
    }

    // 주문금액, 포인트
    $sell_amount = $sum['sum_amount'];
    $point = $sum['sum_point'];

    if ($i > 0)
        echo '<tr><td colspan="'.$colspan.'" height="1" bgcolor="#E7E9E9"></td></tr>'."\n";

    echo "<tr>\n";
    echo "<td align=\"left\" style=\"padding:5px;\">$image</td>\n";
    echo "<td><input type=\"hidden\" name=\"ct_id[$i]\"    value=\"{$row['ct_id']}\">\n";
    echo "<input type=\"hidden\" name=\"it_id[$i]\"    value=\"{$row['it_id']}\">\n";
    echo "<input type=\"hidden\" name=\"cp_id[$i]\"    value=\"\">\n";
    echo "<input type=\"hidden\" name=\"ch_amount[$i]\"    value=\"0\">\n";
    echo "<input type=\"hidden\" name=\"it_name[$i]\"  value=\"".get_text($row['it_name'])."\">\n";
    // 복합과세 사용한다면 쿠폰 적용 시 사용하기 위해 필드 생성
    if($default['de_compound_tax_use']) {
        $ct_tax_mny = 0;
        $ct_vat_mny = 0;
        $ct_free_mny = 0;

        // 과세, 면세금액
        if($row['it_notax']) { // 면세상품
            $ct_free_mny = (int)$sum['sum_amount'];
        } else { // 과세상품
            $ct_tax_mny = round((int)$sum['sum_amount'] / 1.1);
            $ct_vat_mny = (int)$sum['sum_amount'] - $ct_tax_mny;
        }
        echo "<input type=\"hidden\" name=\"tax_mny[$i]\" value=\"$ct_tax_mny\" />\n";
        echo "<input type=\"hidden\" name=\"vat_mny[$i]\" value=\"$ct_vat_mny\" />\n";
        echo "<input type=\"hidden\" name=\"free_mny[$i]\" value=\"$ct_free_mny\" />\n";
    }
    echo $it_name;
    echo "</td>\n";

    if($s_page == 'orderform.php') { // 쿠폰표시
        $coupon = '<span class="coupon-apply">없음</span>';
        if(!$row['it_nocoupon']) {
            if($is_member) {
                // 상품에 쿠폰 있는지 체크
                $sql3 = " select cp_id, ca_id
                            from {$g4['shop_coupon_table']}
                            where cp_use = '1'
                              and cp_type = '0'
                              and cp_start <= '{$g4['time_ymd']}'
                              and cp_end >= '{$g4['time_ymd']}'
                              and ( it_id  = '{$row['it_id']}' or cp_target = '2' )
                              and mb_id in ( '{$member['mb_id']}', '전체회원' )
                              and ca_id in ( '{$row['ca_id']}', '{$row['ca_id2']}', '{$row['ca_id3']}', '전체카테고리' ) ";
                $result3 = sql_query($sql3);

                $cnt = 0;
                for($k=0; $row3=sql_fetch_array($result3); $k++) {
                    // 쿠폰제외카테고리체크
                    $sql4 = " select ca_nocoupon
                                from {$g4['shop_category_table']}
                                where ca_id = '{$row3['ca_id']}' ";
                    $row4 = sql_fetch($sql4);
                    if($row4['ca_nocoupon']) {
                        continue;
                    }

                    // 쿠폰사용여부체크
                    $sql4 = " select uq_id
                                from {$g4['shop_coupon_history_table']}
                                where cp_id = '{$row3['cp_id']}'
                                  and it_id = '{$row['it_id']}'
                                  and mb_id = '{$member['mb_id']}' ";
                    $row4 = sql_fetch($sql4);

                    if(!$row4['uq_id'] || $row4['uq_id'] == $s_uq_id) {
                        $cnt++;
                    }
                }

                if($cnt) {
                    $coupon = '<span class="coupon-apply coupon-link">쿠폰적용</span>';
                }
            }
        }
        echo "<td align=\"center\">$coupon</td>\n";
    }

     if($s_page == 'orderinquiryview.php') { // 할인금액 표시
        $dc_amount = $sum['dc_amount'];
        echo "<td align=\"center\">".number_format($sum['dc_amount'])."</td>\n";
    }

    // 수량, 입력(수량)
    if ($s_page == "cart.php") {
        if($opt_count > 0 || $spl_count > 0) {
            echo "<td align=\"center\"><input type=\"text\" id=\"ct_qty_{$i}\" name=\"ct_qty[$i]\" value=\"{$row['ct_qty']}\" class=\"has-option\" size=\"4\" maxlength=\"4\" readonly=\"readonly\" class=\"ed\" style=\"text-align:right;\" autocomplete=\"off\"></td>\n";
        } else {
            echo "<td align=\"center\"><input type=\"text\" id=\"ct_qty_{$i}\" name=\"ct_qty[$i]\" value=\"{$row['ct_qty']}\" size=\"4\" maxlength=\"4\" class=\"ed\" style=\"text-align:right;\" autocomplete=\"off\"></td>\n";
        }
    } else {
        echo "<td align=\"center\">{$row['ct_qty']}</td>\n";
    }

    echo "<td align=\"right\"><span class=\"sell_amount\">" . number_format($sell_amount - $dc_amount) . "</span></td>\n";
    echo "<td align=\"right\">" . number_format($point) . "&nbsp;</td>\n";

    if ($s_page == "cart.php")
        //echo "<td align=center><a href='./cartupdate.php?w=d&it_id={$row['it_id']}'><img src='{$g4['shop_img_path']}/btn_del.gif' border='0' align=absmiddle alt='삭제'></a></td>";
        echo '<td align="center"><input type="checkbox" name="ct_chk['.$i.']" value="'.$row['ct_id'].'" /></td>'."\n";
    else if ($s_page == "orderinquiryview.php")
    {
        switch($row['ct_status'])
        {
            case '주문' : $icon = "<img src=\"".G4_SHOP_IMG_URL."/status01.gif\">"; break;
            case '준비' : $icon = "<img src=\"".G4_SHOP_IMG_URL."/status02.gif\">"; break;
            case '배송' : $icon = "<img src=\"".G4_SHOP_IMG_URL."/status03.gif\">"; break;
            case '완료' : $icon = "<img src=\"".G4_SHOP_IMG_URL."/status04.gif\">"; break;
            default     : $icon = $row['ct_status']; break;
        }
        echo "<td align=\"center\">$icon</td>\n";
    }

    echo "</tr>\n";
    echo "<tr><td colspan=\"$colspan\" class=\"dotline\"></td></tr>\n";

    //$tot_point       += $point;
    //$tot_sell_amount += $sell_amount;

    if ($row['ct_status'] == '취소' || $row['ct_status'] == '반품' || $row['ct_status'] == '품절') {
        $tot_cancel_amount += $sell_amount;
    }
    else {
        $tot_point       += $point;
        $tot_sell_amount += $sell_amount;
        $item_dc_amount += $dc_amount; // 총 할인금액
    }
}

if ($i == 0) {
    echo "<tr>\n";
    echo "<td colspan=\"$colspan\" align=\"center\" height=\"100\"><span class=\"textpoint\">장바구니가 비어 있습니다.</span></td>\n";
    echo "</tr>";
} else {
    // 배송비가 넘어왔다면
    if ($_POST['od_send_cost']) {
        $send_cost = (int)$_POST['od_send_cost'];
    } else {
        // 배송비 계산
        if ($default['de_send_cost_case'] == "없음" || $default['de_send_cost_case'] == "착불")
            $send_cost = 0;
        else if($default['de_send_cost_case'] == "상한") {
            // 배송비 상한 : 여러단계의 배송비 적용 가능
            $send_cost_limit = explode(";", $default['de_send_cost_limit']);
            $send_cost_list  = explode(";", $default['de_send_cost_list']);
            $send_cost = 0;
            for ($k=0; $k<count($send_cost_limit); $k++) {
                // 총판매금액이 배송비 상한가 보다 작다면
                if ($tot_sell_amount < $send_cost_limit[$k]) {
                    $send_cost = $send_cost_list[$k];
                    break;
                }
            }
        } else if($default['de_send_cost_case'] == "개별배송") {
            $send_cost = 0;

            for($i=0; $i<$goods_count; $i++) {
                if($itemlist[$i]['pay'] == "착불") {
                    $send_cost += 0;
                } else {
                    $sql = " select it_sc_type, it_sc_basic, it_sc_method, it_sc_condition
                                from {$g4['shop_item_table']}
                                where it_id = '{$itemlist[$i]['it_id']}' ";
                    $row = sql_fetch($sql);

                    if($row['it_sc_type'] == 1) { // 조건부무료
                        if($itemlist[$i]['amount'] >= $row['it_sc_condition']) {
                            $send_cost += 0;
                        } else {
                            $send_cost += $row['it_sc_basic'];
                        }
                    } else if($row['it_sc_type'] == 2) { // 유료
                        $send_cost += $row['it_sc_basic'];
                    } else if($row['it_sc_type'] == 3) { // 수량별부과
                        $qty = ceil($itemlist[$i]['qty'] / $row['it_sc_condition']);
                        $send_cost += ($row['it_sc_basic'] * $qty);
                    } else {
                        $send_cost += 0;
                    }
                }
            }
        }

        // 이미 주문된 내역을 보여주는것이므로 주문서에서 얻는다.
        $sql = "select od_send_cost, od_send_coupon, od_send_cost_area, od_coupon_amount from {$g4['shop_order_table']} where od_id = '$od_id' ";
        $row = sql_fetch($sql);
        if ($row['od_send_cost'] > 0)
            $send_cost = $row['od_send_cost'] - $row['od_send_coupon'];

        if($row['od_coupon_amount'] > 0)
            $od_coupon_amount = $row['od_coupon_amount'];

        if($row['od_send_cost_area'])
            $od_send_cost_area = $row['od_send_cost_area'];
    }

    // 배송비가 0 보다 크다면 (있다면)
    if ($send_cost > 0)
    {
        echo "<tr><td colspan=\"$colspan\" height=\"1\" bgcolor=\"#E7E9E9\"></td></tr>\n";
        echo "<tr>\n";
        echo "<td height=\"28\" colspan=\"$colspan2\" align=\"right\">배송비 : </td>\n";
        echo "<td align=\"right\"><span id=\"send_cost\">" . number_format($send_cost) . "</span></td>\n";
        echo "<td>&nbsp;</td>\n";
        if ($s_page == "cart.php" || $s_page == "orderinquiryview.php")
           echo "<td>&nbsp;</td>\n";
        echo "</tr>\n";
    }

    // 추가배송비가 0 보다 크다면 (있다면)
    if ($od_send_cost_area > 0)
    {
        echo "<tr><td colspan=\"$colspan\" height=\"1\" bgcolor=\"#E7E9E9\"></td></tr>\n";
        echo "<tr>\n";
        echo "<td height=\"28\" colspan=\"$colspan2\" align=\"right\">추가배송비 : </td>\n";
        echo "<td align=\"right\"><span id=\"send_cost\">" . number_format($od_send_cost_area) . "</span></td>\n";
        echo "<td>&nbsp;</td>\n";
        if ($s_page == "cart.php" || $s_page == "orderinquiryview.php")
           echo "<td>&nbsp;</td>\n";
        echo "</tr>\n";
    }

    // 결제할인금액 0 보다 크다면 (있다면)
    if ($od_coupon_amount > 0)
    {
        echo "<tr><td colspan=\"$colspan\" height=\"1\" bgcolor=\"#E7E9E9\"></td></tr>\n";
        echo "<tr>\n";
        echo "<td height=\"28\" colspan=\"$colspan2\" align=\"right\">결제할인 : </td>\n";
        echo "<td align=\"right\"><span id=\"send_cost\">" . number_format($od_coupon_amount) . "</span></td>\n";
        echo "<td>&nbsp;</td>\n";
        if ($s_page == "cart.php" || $s_page == "orderinquiryview.php")
           echo "<td>&nbsp;</td>\n";
        echo "</tr>\n";
    }

    // 총계 = 주문상품금액합계 - 할인금액합계 + 배송비 + 추가배송비
    $tot_amount = $tot_sell_amount - $item_dc_amount + $send_cost - $od_coupon_amount + $od_send_cost_area;

    echo "<tr><td colspan=\"$colspan\" height=\"1\" bgcolor=\"#94D7E7\"></td></tr>\n";
    echo "<tr align=\"center\" height=\"28\" bgcolor=\"#E7F3F7\">\n";
    echo "<td colspan=\"$colspan2\" align=\"right\"><b>총계 : </b></td>\n";
    echo "<td align=\"right\"><span id=\"tot_amount\" class=amount><b>" . number_format($tot_amount) . "</b></span></td>\n";
    echo "<td align=\"right\">" . number_format($tot_point) . "&nbsp;</td>\n";
    if ($s_page == "cart.php" || $s_page == "orderinquiryview.php")
        echo "<td> &nbsp;</td>\n";
    echo "</tr>\n";
    echo "<input type=\"hidden\" name=\"act\" value=\"\">\n";
    echo "<input type=\"hidden\" name=\"records\" value=\"$i\">\n";
}
?>
<tr><td colspan="<? echo $colspan; ?>" height="2" bgcolor="#94D7E7"></td></tr>
<tr>
    <td colspan="<? echo $colspan; ?>" align="center">
    <?
    if ($s_page == "cart.php") {
        if ($i == 0) {
            echo "<br><a href=\"{$g4['path']}\"><img src=\"".G4_SHOP_IMG_URL."/btn_shopping.gif\" border=\"0\"></a>";
        } else {
            echo "
            <br><input type=\"hidden\" name=\"url\" value=\"./orderform.php\">
            <a href=\"javascript:form_check('buy')\"><img src=\"".G4_SHOP_IMG_URL."/btn_buy.gif\" border=\"0\" alt=\"구매하기\"></a>&nbsp;
            <a href=\"javascript:form_check('selectedbuy')\"><img src=\"".G4_SHOP_IMG_URL."/btn_buy1.gif\" border=\"0\" alt=\"선택주문\"></a>&nbsp;
            <a href=\"javascript:form_check('allupdate')\"><img src=\"".G4_SHOP_IMG_URL."/btn_cart_quan.gif\" border=\"0\" alt=\"장바구니 수량 변경\"></a>&nbsp;
            <a href=\"javascript:form_check('alldelete');\"><img src=\"".G4_SHOP_IMG_URL."/btn_cart_out.gif\" border=\"0\" alt=\"장바구니 비우기\"></a>&nbsp;
            <a href=\"javascript:form_check('seldelete');\"><img src=\"".G4_SHOP_IMG_URL."/btn_cart_out1.gif\" border=\"0\" alt=\"선택삭제\"></a>&nbsp;
            <a href=\"./list.php?ca_id=$continue_ca_id\"><img src=\"".G4_SHOP_IMG_URL."/btn_shopping.gif\" border=\"0\" alt=\"계속쇼핑하기\"></a>";
        }
    }
    ?>
    </td>
</tr>
</table>
</form>

<script>
$(function() {
    // 선택사항보기
    $(".view-options").click(function() {
        $(this).closest("tr").find(".options-list").toggle();
    });

    // 선택사항/수량변경
    $(".edit-options").click(function() {
        var it_id = $(this).closest("tr").find("input[name^=it_id]").val();
        window.open("./cartoption.php?it_id="+it_id+"&sw_direct=<? echo $sw_direct; ?>", "optionform", "width=700, height=700, left=100, top=50, scrollbars=yes");
    });

    // 수량 input click
    $("input[name^=ct_qty]").click(function() {
        if($(this).hasClass("has-option")) {
            var it_id = $(this).closest("tr").find("input[name^=it_id]").val();
            window.open("./cartoption.php?it_id="+it_id+"&sw_direct=<? echo $sw_direct; ?>", "optionform", "width=700, height=700, left=100, top=50, scrollbars=yes");
        }

        return false;
    });
});
</script>

<? if ($s_page == "cart.php") { ?>
    <script>
    <? if ($i != 0) { ?>
        $(function() {
            $("input[name=all_chk]").click(function() {
                if($(this).is(":checked")) {
                    $("input[name^=ct_chk]").attr("checked", true);
                } else {
                    $("input[name^=ct_chk]").attr("checked", false);
                }
            });
        });

        function form_check(act) {
            var f = document.frmcartlist;
            var cnt = f.records.value;

            if (act == "buy")
            {
                f.act.value = act;

                <?
                if (get_session('ss_mb_id')) // 회원인 겨우
                {
                    echo "f.action = \"./orderform.php\";";
                    echo "f.submit();";
                }
                else
                    echo "document.location.href = \"".G4_BBS_URL."/login.php?url=".urlencode(G4_SHOP_URL.'/orderform.php')."\";";
                ?>
            } else if (act == "selectedbuy")
            {
                var cnt = 0;
                var inputs = f.getElementsByTagName("input");
                for(i=0; i<inputs.length; i++) {
                    if(inputs[i].type == "checkbox" && inputs[i].name.search("ct_chk") > - 1 && inputs[i].checked) {
                        cnt++;
                    }
                }

                if(!cnt) {
                    alert("주문하실 상품을 1개 이상 선택해 주세요.");
                    return false;
                }

                f.act.value = act;
                f.action = "./cartupdate.php";
                f.submit();
            }
            else if (act == "alldelete")
            {
                f.act.value = act;
                f.action = "./cartupdate.php";
                f.submit();
            }
            else if(act == "seldelete")
            {
                f.act.value = act;
                f.action = "./cartupdate.php";
                f.submit();
            }
            else if (act == "allupdate")
            {
                for (i=0; i<cnt; i++)
                {
                    //if (f.elements("ct_qty[" + i + "]").value == "")
                    if (document.getElementById("ct_qty_"+i).value == "")
                    {
                        alert("수량을 입력해 주십시오.");
                        //f.elements("ct_qty[" + i + "]").focus();
                        document.getElementById("ct_qty_"+i).focus();
                        return;
                    }
                    //else if (isNaN(f.elements("ct_qty[" + i + "]").value))
                    else if (isNaN(document.getElementById("ct_qty_"+i).value))
                    {
                        alert("수량을 숫자로 입력해 주십시오.");
                        //f.elements("ct_qty[" + i + "]").focus();
                        document.getElementById("ct_qty_"+i).focus();
                        return;
                    }
                    //else if (f.elements("ct_qty[" + i + "]").value < 1)
                    else if (document.getElementById("ct_qty_"+i).value < 1)
                    {
                        alert("수량은 1 이상 입력해 주십시오.");
                        //f.elements("ct_qty[" + i + "]").focus();
                        document.getElementById("ct_qty_"+i).focus();
                        return;
                    }
                }
                f.act.value = act;
                f.action = "./cartupdate.php";
                f.submit();
            }

            return true;
        }
    <? } ?>
    </script>
<? } ?>

<? if ($s_page == "cart.php") { ?>
<br><br>
<table align="center" cellpadding="0" cellspacing="0">
    <tr><td><img src="<? echo G4_SHOP_IMG_URL; ?>/info_box01.gif"></td></tr>
    <tr><td background="<? echo G4_SHOP_IMG_URL; ?>/info_box03.gif" style="line-height:180%; padding-left:20px">
        · <FONT COLOR="#FF8200">상품 주문하기</FONT> : 주문서를 작성하시려면 '주문하기' 버튼을 누르세요.<BR>
        · <FONT COLOR="#FF8200">상품 수량변경</FONT> : 주문수량을 변경하시려면 원하시는 수량을 입력하신 후 '수량변경' 버튼을 누르세요.<BR>
        · <FONT COLOR="#FF8200">상품 삭제하기</FONT> : 모든 주문내용을 삭제하시려면 '삭제하기' 버튼을 누르세요.<BR>
        · <FONT COLOR="#FF8200">쇼핑 계속하기</FONT> : 쇼핑하시던 페이지로 돌아가시려면 '쇼핑 계속하기' 버튼을 누르세요.
        </td></tr>
    <tr><td><img src="<? echo G4_SHOP_IMG_URL; ?>/info_box02.gif"></td></tr>
</table><br><br>
<? } ?>

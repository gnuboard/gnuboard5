<?
$sub_menu = "400400";
include_once("./_common.php");

// 메세지
$html_title = "주문 내역 수정";
$alt_msg1   = "주문번호 오류입니다.";
$mb_guest   = "비회원";
$hours = 6; // 설정 시간이 지난 주문서 없는 장바구니 자료 삭제

$cart_title1 = "쇼핑";
$cart_title2 = "완료";
$cart_title3 = "주문번호";
$cart_title4 = "배송완료";

auth_check($auth[$sub_menu], "w");

$g4[title] = $html_title;
include_once("$g4[admin_path]/admin.head.php");

//------------------------------------------------------------------------------
// 설정 시간이 지난 주문서 없는 장바구니 자료 삭제
//------------------------------------------------------------------------------
if (!isset($cart_not_delete)) {
    if (!$hours) $hours = 6;
    $beforehours = date("Y-m-d H:i:s", ( $g4[server_time] - (60 * 60 * $hours) ) );
    $sql = " delete from $g4[yc4_cart_table] where ct_status = '$cart_title1' and ct_time <= '$beforehours' ";
    sql_query($sql);
}
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// 주문완료 포인트
//      설정일이 지난 포인트 부여되지 않은 배송완료된 장바구니 자료에 포인트 부여
//      설정일이 0 이면 주문서 완료 설정 시점에서 포인트를 바로 부여합니다.
//------------------------------------------------------------------------------
if (!isset($order_not_point)) {
    $beforedays = date("Y-m-d H:i:s", ( time() - (60 * 60 * 24 * (int)$default[de_point_days]) ) );
    $sql = " select * from $g4[yc4_cart_table] 
               where ct_status = '$cart_title2' 
                 and ct_point_use = '0' 
                 and ct_time <= '$beforedays' ";
    $result = sql_query($sql);
    for ($i=0; $row=sql_fetch_array($result); $i++) 
    {
        // 회원 ID 를 얻는다.
        $tmp_row = sql_fetch("select od_id, mb_id from $g4[yc4_order_table] where on_uid = '$row[on_uid]' ");

        // 회원이면서 포인트가 0보다 크다면
        if ($tmp_row[mb_id] && $row[ct_point] > 0)
        {
            $po_point = $row[ct_point] * $row[ct_qty];
            $po_content = "$cart_title3 $tmp_row[od_id] ($row[ct_id]) $cart_title4";
            insert_point($tmp_row[mb_id], $po_point, $po_content, "@delivery", $tmp_row[mb_id], "$tmp_row[od_id],$row[on_uid],$row[ct_id]");
        }

        sql_query("update $g4[yc4_cart_table] set ct_point_use = '1' where ct_id = '$row[ct_id]' ");
    }
}
//------------------------------------------------------------------------------


//------------------------------------------------------------------------------
// 주문서 정보
//------------------------------------------------------------------------------
$sql = " select * from $g4[yc4_order_table] where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od[od_id]) {
    alert($alt_msg1);
}

if ($od[mb_id] == "") {
    $od[mb_id] = $mb_guest;
}
//------------------------------------------------------------------------------


$qstr = "sort1=$sort1&sort2=$sort2&sel_field=$sel_field&search=$search&page=$page";

// PG사를 KCP 사용하면서 테스트 상점아이디라면
if ($default[de_card_test]) {
    // 로그인 아이디 / 비번 
    // 일반 : test1234 / test12345
    // 에스크로 : escrow / escrow913
    $g4[yc4_cardpg][kcp] = "http://testadmin8.kcp.co.kr"; 
}

$sql = " select a.ct_id,
                a.it_id,
                a.ct_qty,
                a.ct_amount,
                a.ct_point,
                a.ct_status,
                a.ct_time,
                a.ct_point_use,
                a.ct_stock_use,
                a.it_opt1,
                a.it_opt2,
                a.it_opt3,
                a.it_opt4,
                a.it_opt5,
                a.it_opt6,
                b.it_name
           from $g4[yc4_cart_table] a, $g4[yc4_item_table] b
          where a.on_uid = '$od[on_uid]'
            and a.it_id  = b.it_id
          order by a.ct_id ";
$result = sql_query($sql);
?>

<p>
<table width=100% cellpadding=0 cellspacing=0>
	<tr>
        <td><?=subtitle("주문상품")?></td>
        <td align=right>
        <? if ($default[de_hope_date_use]) { ?>
            희망배송일은
            <b><?=$od[od_hope_date]?> (<?=get_yoil($od[od_hope_date])?>)</b> 입니다.
        <? } ?>
        </td>
    </tr>
</table>


<form name=frmorderform method=post action='' style="margin:0px;">
<input type=hidden name=ct_status value=''>
<input type=hidden name=on_uid    value='<? echo $od[on_uid] ?>'>
<input type=hidden name=od_id     value='<? echo $od_id ?>'>
<input type=hidden name=mb_id     value='<? echo $od[mb_id] ?>'>
<input type=hidden name=od_email  value='<? echo $od[od_email] ?>'>
<input type=hidden name=sort1 value="<? echo $sort1 ?>">
<input type=hidden name=sort2 value="<? echo $sort2 ?>">
<input type=hidden name=sel_field  value="<? echo $sel_field ?>">
<input type=hidden name=search     value="<? echo $search ?>">
<input type=hidden name=page       value="<? echo $page ?>">
<table width=100% cellpadding=0 cellspacing=0 border=0>
<colgroup width=50>
<colgroup width=''>
<colgroup width=40>
<colgroup width=50>
<colgroup width=70>
<colgroup width=70>
<colgroup width=70>
<colgroup width=50>
<colgroup width=50>
<colgroup width=50>
<tr><td colspan=10 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
    <td>전체<br><input type=checkbox onclick='select_all();'></td>
    <td>상품명</td>
    <td>상태</td>
    <td>수량</td>
    <td>판매가</td>
    <td>소계</td>
    <td>포인트</td>
    <td>포인트<br>반영</td>
    <td>재고<br>반영</td>
</tr>
<tr><td colspan=10 height=1 bgcolor=#CCCCCC></td></tr>
<?
$image_rate = 2.5;
for ($i=0; $row=sql_fetch_array($result); $i++) 
{
    $it_name = "<a href='./itemform.php?w=u&it_id=$row[it_id]'>".stripslashes($row[it_name])."</a><br>";
    $it_name .= print_item_options($row[it_id], $row[it_opt1], $row[it_opt2], $row[it_opt3], $row[it_opt4], $row[it_opt5], $row[it_opt6]);

    $ct_amount[소계] = $row[ct_amount] * $row[ct_qty];
    $ct_point[소계] = $row[ct_point] * $row[ct_qty];
    if ($row[ct_status]=='주문' || $row[ct_status]=='준비' || $row[ct_status]=='배송' || $row[ct_status]=='완료')
        $t_ct_amount[정상] += $row[ct_amount] * $row[ct_qty];
    else if ($row[ct_status]=='취소' || $row[ct_status]=='반품' || $row[ct_status]=='품절')
        $t_ct_amount[취소] += $row[ct_amount] * $row[ct_qty];
    
    $image = get_it_image("$row[it_id]_s", (int)($default[de_simg_width] / $image_rate), (int)($default[de_simg_height] / $image_rate), $row[it_id]);

    $list = $i%2;
    echo "
    <tr class='list$list'>
        <td align=center title='$row[ct_id]'><input type=hidden name=ct_id[$i] value='$row[ct_id]'><input type=checkbox id='ct_chk_{$i}' name='ct_chk[{$i}]' value='1'></td>
        <td style='padding-top:5px; padding-bottom:5px;'><table width='100%'><tr><td width=40 align=center>$image</td><td>$it_name</td></tr></table></td>
        <td align=center>$row[ct_status]</td>
        <td align=center>$row[ct_qty]</td>
        <td align=right>".number_format($row[ct_amount])."</td>
        <td align=right>".number_format($ct_amount[소계])."</td>
        <td align=right>".number_format($ct_point[소계])."</td>
        <td align=center>".get_yn($row[ct_point_use])."</td>
        <td align=center>".get_yn($row[ct_stock_use])."</td>";
    echo "</tr><tr><td colspan=8 height=1 bgcolor=F5F5F5></td></tr>";

    $t_ct_amount[합계] += $ct_amount[소계];
    $t_ct_point[합계] += $ct_point[소계];
}
?>
<tr><td colspan=10 height=1 bgcolor=#CCCCCC></td></tr>
<tr bgcolor=#ffffff class=ht>
    <td colspan=3>&nbsp;&nbsp;&nbsp;
        <a href="javascript:form_submit('주문')">주문</a> |
        <a href="javascript:form_submit('준비')">상품준비중</a> |
        <a href="javascript:form_submit('배송')">배송중</a> |
        <a href="javascript:form_submit('완료')">완료</a> |
        <a href="javascript:form_submit('취소')">취소</a> |
        <a href="javascript:form_submit('반품')">반품</a> |
        <a href="javascript:form_submit('품절')">품절</a>
        <?=help("한 주문에 여러가지의 상품주문이 있을 수 있습니다.\n\n상품을 체크하여 해당되는 상태로 설정할 수 있습니다.");?>
    </td>
    <td colspan=3>주문일시 : <?=substr($od[od_time],0,16)?> (<?=get_yoil($od[od_time]);?>)</td>
    <td colspan=3 align=right>                  
        <input type=hidden name="chk_cnt" value="<? echo $i ?>">
        <b>주문합계 : <? echo number_format($t_ct_amount[합계]); ?>원</B></td>
	    <? //echo number_format($t_ct_point[합계]); ?>
</tr>
</form>
</table>
<br>
<br>

<?=subtitle("주문결제")?>

<?
// 주문금액 = 상품구입금액 + 배송비
$amount[정상] = $t_ct_amount[정상] + $od[od_send_cost];

// 입금액 = 무통장(가상계좌, 계좌이체 포함) + 신용카드 + 휴대폰 + 포인트
$amount[입금] = $od[od_receipt_bank] + $od[od_receipt_card] + $od[od_receipt_hp] + $od[od_receipt_point];

// 미수금 = (주문금액 - DC + 환불액) - (입금액 - 신용카드승인취소)
$amount[미수] = ($amount[정상] - $od[od_dc_amount] + $od[od_refund_amount]) - ($amount[입금] - $od[od_cancel_card]);

// 결제방법
$s_receipt_way = $od[od_settle_case];

if ($od[od_receipt_point] > 0)
    $s_receipt_way .= "+포인트";
?>


<table width=100% cellpadding=0 cellspacing=0 border=0>
<!-- on_uid : <? echo $od[on_uid] ?> -->
<tr><td colspan=8 height=2 bgcolor=#0E87F9></td></tr>
<tr align=center class=ht>
	<td>주문번호</td>
	<td>결제방법</td>
	<td>주문총액</td>
	<td>포인트결제액</td>
	<td>결제액(포인트포함)</td>
	<td>DC</td>
	<td>환불액</td>
	<td>주문취소</td>
</tr>
<tr><td colspan=8 height=1 bgcolor=#CCCCCC></td></tr>
<tr align=center class=ht>
    <td><? echo $od[od_id] ?></td>
	<td><? echo $s_receipt_way ?></td>
	<td><? echo display_amount($amount[정상]) ?></td>
	<td><? echo display_point($od[od_receipt_point]); ?></td>
	<td><? echo number_format($amount[입금]); ?>원</td>
    <td><? echo display_amount($od[od_dc_amount]); ?></td>
    <td><? echo display_amount($od[od_refund_amount]); ?></td>
	<td><? echo number_format($t_ct_amount[취소]) ?>원</td>
</tr>
<tr><td colspan=8 height=1 bgcolor=#CCCCCC></td></tr>
<tr><td colspan=8 align=right class=ht><b><font color=#FF6600><b>미수금 : <? echo display_amount($amount[미수]) ?></b></font></b></td></tr>
</table>


<p>
<form name=frmorderreceiptform method=post action="./orderreceiptupdate.php" autocomplete=off style="margin:0px;">
<input type=hidden name=od_id     value="<?=$od_id?>">
<input type=hidden name=sort1     value="<?=$sort1?>">
<input type=hidden name=sort2     value="<?=$sort2?>">
<input type=hidden name=sel_field value="<?=$sel_field?>">
<input type=hidden name=search    value="<?=$search?>">
<input type=hidden name=page      value="<?=$page?>">
<input type=hidden name=od_name   value="<?=$od[od_name]?>">
<input type=hidden name=od_hp     value="<?=$od[od_hp]?>">
<table border=0 cellpadding=0 cellspacing=0 width=100%>
<tr>
    <td width=49% valign=top>

        <?=subtitle("결제상세정보")?>
        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <colgroup width=110>
        <colgroup width='' bgcolor=#ffffff>
		<tr><td colspan=2 height=1 bgcolor=0E87F9></td></tr>

        <? if ($od[od_settle_case] == '무통장' || $od[od_settle_case] == '가상계좌' || $od[od_settle_case] == '계좌이체') { ?>
            <? 
            if ($od[od_settle_case] == '무통장' || $od[od_settle_case] == '가상계좌') 
            { 
                echo "<tr class=ht>";
                echo "<td>계좌번호</td>";
                echo "<td>".$od[od_bank_account]."</td>";
                echo "</tr>";
            }
            ?>
            <tr class=ht>
                <td><?=$od[od_settle_case]?> 입금액</td>
                <td><?=display_amount($od[od_receipt_bank]);?></td>
            </tr>
            <tr class=ht>
                <td>입금자</td>
                <td><? echo $od[od_deposit_name] ?></td>
            </tr>
            <tr class=ht>
                <td>입금확인일시</td>
                <td>
                <?
                    if ($od[od_bank_time] == 0) {
                        echo "입금 확인일시를 체크해 주세요.";
                    } else {
                        echo $od[od_bank_time].' ('.get_yoil($od[od_bank_time]).')';
                    }
                ?>
                </td>
            </tr>
            <tr><td colspan=2 height=1 bgcolor=#84C718></td></tr>
        <? } ?>

        <? if ($od[od_settle_case] == '휴대폰') { ?>
            <tr class=ht>
                <td>휴대폰번호</td>
                <td><?=$od[od_escrow2]?></td>
                </tr>
            <tr class=ht>
                <td><?=$od[od_settle_case]?> 결제액</td>
                <td><?=display_amount($od[od_receipt_hp]);?></td>
            </tr>
            <tr class=ht>
                <td>결제 확인일시</td>
                <td>
                <?
                    if ($od[od_hp_time] == 0) {
                        echo "결제 확인일시를 체크해 주세요.";
                    } else {
                        echo $od[od_hp_time].' ('.get_yoil($od[od_hp_time]).')';
                    }
                ?>
                </td>
            </tr>
            <tr><td colspan=2 height=1 bgcolor=#84C718></td></tr>
        <? } ?>

        <? if ($od[od_settle_case] == '신용카드') { ?>
        <tr class=ht>
            <td bgcolor=#F8FFED>신용카드 입금액</td>
            <td>
            <?
                if ($od[od_card_time] == "0000-00-00 00:00:00")
                    echo "0원";
                else
                    echo display_amount($od[od_receipt_card]);
            ?>
            </td>
        </tr>
		<tr class=ht>
			<td bgcolor=#F8FFED>카드 승인일시</td>
			<td>
            <?
                if ($od[od_card_time] == "0000-00-00 00:00:00")
                    echo "신용카드 결제 일시 정보가 없습니다.";
                else
                {
                    echo "" . substr($od[od_card_time], 0, 20);
                }
            ?>
			</td>
		</tr>
        <tr class=ht>
            <td bgcolor=#F8FFED>카드 승인취소</td>
            <td><? echo display_amount($od[od_cancel_card]); ?></td>
        </tr>
        <tr><td colspan=2 height=1 bgcolor=#84C718></td></tr>
        <? } ?>

        <tr class=ht>
            <td>포인트</td>
            <td><? echo display_point($od[od_receipt_point]); ?></td>
        </tr>
        <tr class=ht>
            <td>DC</td>
            <td><? echo display_amount($od[od_dc_amount]); ?></td>
        </tr>
        <tr class=ht>
            <td>환불액</td>
            <td><? echo display_amount($od[od_refund_amount]); ?></td>
        </tr>
        <tr><td colspan=2 height=1 bgcolor=#84C718></td></tr>

        <?
        $sql = " select dl_company, dl_url, dl_tel from $g4[yc4_delivery_table] where dl_id = '$od[dl_id]' ";
        $dl = sql_fetch($sql);
        ?>
        <tr class=ht>
            <td>배송회사</td>
			<td>
	        <?
            if ($od[dl_id] > 0) {
                // get 으로 날리는 경우 운송장번호를 넘김
                if (strpos($dl[dl_url], "=")) $invoice = $od[od_invoice];
                echo "<a href='$dl[dl_url]{$invoice}' target=_new>$dl[dl_company]</a> &nbsp;&nbsp;(고객센터 : $dl[dl_tel]) ";
            } else
                echo "배송회사를 선택해 주세요.";
			?>
			</td>
        </tr>
        <tr class=ht>
            <td>운송장번호</td>
            <td><? echo $od[od_invoice] ?>&nbsp;</td>
        </tr>
        <tr class=ht>
            <td>배송일시</td>
            <td><? echo $od[od_invoice_time] ?>&nbsp;</td>
        </tr>
        <tr class=ht>
            <td>주문자 배송비</td>
            <!-- <td><? echo number_format($od[od_send_cost]) ?>원</td> -->
            <td><input type=text name='od_send_cost' value='<?=$od[od_send_cost]?>' class=ed size=10 style='text-align:right;'>원
                <?=help("주문취소시 배송비는 취소되지 않으므로 이 배송비를 0으로 설정하여 미수금을 맞추십시오.");?></td>
        </tr>
        <?
        if ($amount[미수] == 0) {
            if ($od[od_receipt_bank]) {
                echo "<tr class=ht><td>현금영수증</td><td>";
                if ($od["od_cash"]) 
                    echo "<a href=\"javascript:;\" onclick=\"window.open('https://admin.kcp.co.kr/Modules/Service/Cash/Cash_Bill_Common_View.jsp?cash_no=$od[od_cash_no]', 'taxsave_receipt', 'width=360,height=647,scrollbars=0,menus=0');\">현금영수증 확인하기</a>";
                else
                    echo "<a href=\"javascript:;\" onclick=\"window.open('$g4[shop_path]/taxsave_kcp.php?od_id=$od_id&on_uid=$od[on_uid]', 'taxsave', 'width=550,height=400,scrollbars=1,menus=0');\">현금영수증을 발급하시려면 클릭하십시오.</a>";
                echo "</td></tr>";
            }
        }
        ?>
		<tr><td colspan=2 height=1 bgcolor=CCCCCC></td></tr>
        </table>
    </td>
    <td width=1%> </td>
    <td width=50% valign=top align=center>

        <?=subtitle("결제상세정보 수정")?>
        <table width=100% cellpadding=0 cellspacing=0 border=0>
        <colgroup width=110>
        <colgroup width='' bgcolor=#ffffff>
        <tr><td colspan=2 height=1 bgcolor=#0E87F9></td></tr>
        <? if ($od[od_settle_case] == '무통장' || $od[od_settle_case] == '가상계좌' || $od[od_settle_case] == '계좌이체') { ?>
            <?
            // 주문서
            $sql = " select * from $g4[yc4_order_table] where od_id = '$od_id' ";
            $result = sql_query($sql);
            $od = sql_fetch_array($result);

            if ($od['od_settle_case'] == '무통장')
            {
                // 은행계좌를 배열로 만든후
                $str = explode("\n", $default[de_bank_account]);
                $bank_account = "\n<select name=od_bank_account>\n";
                $bank_account .= "<option value=''>------------ 선택하십시오 ------------\n";
                for ($i=0; $i<count($str); $i++) {
                    $str[$i] = str_replace("\r", "", $str[$i]);
                    $bank_account .= "<option value='$str[$i]'>$str[$i] \n";
                }
                $bank_account .= "</select> ";
            }
            else if ($od['od_settle_case'] == '가상계좌')
                $bank_account = $od[od_bank_account] . "<input type='hidden' name='od_bank_account' value='$od[od_bank_account]'>";
            else if ($od['od_settle_case'] == '계좌이체')
                $bank_account = $od['od_settle_case'];
            ?>

            <?
            if ($od[od_settle_case] == '무통장' || $od[od_settle_case] == '가상계좌')
            {
                echo "<tr class=ht>";
                echo "<td>계좌번호</td>";
                echo "<td>$bank_account</td>";
                echo "</tr>";
            }

            if ($od[od_settle_case] == '무통장')
                echo "<script> document.frmorderreceiptform.od_bank_account.value = '".str_replace("\r", "", $od[od_bank_account])."'; </script>";
            ?>
            <tr class=ht>
                <td><?=$od[od_settle_case]?> 입금액</td>
                <td>
                    <input type=text class=ed name=od_receipt_bank size=10 
                        value='<? echo $od[od_receipt_bank] ?>'>원
                    <?
                    if ($od['od_settle_case'] == '계좌이체' || $od['od_settle_case'] == '가상계좌')
                    {
                        $pg_url = $g4['yc4_cardpg'][$default['de_card_pg']];
                        echo "&nbsp;<a href='$pg_url' target=_new>결제대행사</a>";
                    }
                    ?>
                </td>
            </tr>
            <tr class=ht>
                <td>입금자명</td>
                <td>
                    <input type=text class=ed name=od_deposit_name 
                        value='<? echo $od[od_deposit_name] ?>'>
                    <? if ($default[de_sms_use3]) { ?>
                        <input type=checkbox name=od_sms_ipgum_check> SMS 문자전송
                    <? } ?>
                </td>
            </tr>
            <tr class=ht>
                <td>입금 확인일시</td>
                <td>
                    <input type=text class=ed name=od_bank_time maxlength=19 value='<? echo is_null_time($od[od_bank_time]) ? "" : $od[od_bank_time]; ?>'>
                    <input type=checkbox name=od_bank_chk
                        value="<? echo date("Y-m-d H:i:s", $g4['server_time']); ?>"
                        onclick="if (this.checked == true) this.form.od_bank_time.value=this.form.od_bank_chk.value; else this.form.od_bank_time.value = this.form.od_bank_time.defaultValue;">현재 시간
                </td>
            </tr>
            <tr><td colspan=2 height=1 bgcolor=#84C718></td></tr>
        <? } ?>

        <? if ($od[od_settle_case] == '휴대폰') { ?>
            <tr class=ht>
                <td>휴대폰번호</td>
                <td><?=$od[od_escrow2]?></td>
            </tr>
            <tr class=ht>
                <td><?=$od[od_settle_case]?> 결제액</td>
                <td>
                    <input type=text class=ed name=od_receipt_hp size=10 value='<? echo $od[od_receipt_hp] ?>'>원
                    <?
                    $pg_url = $g4['yc4_cardpg'][$default['de_card_pg']];
                    echo "&nbsp;<a href='$pg_url' target=_new>결제대행사</a>";
                    ?>
                </td>
            </tr>
            <tr class=ht>
                <td>휴대폰 결제일시</td>
                <td>
                    <input type=text class=ed name=od_hp_time size=19 maxlength=19 value='<? echo is_null_time($od[od_hp_time]) ? "" : $od[od_hp_time]; ?>'>
                    <input type=checkbox name=od_card_chk
                        value="<? echo date("Y-m-d H:i:s", $g4['server_time']); ?>"
                        onclick="if (this.checked == true) this.form.od_hp_time.value=this.form.od_card_chk.value; else this.form.od_hp_time.value = this.form.od_hp_time.defaultValue;">현재 시간
                </td>
            </tr>
            <tr><td colspan=2 height=1 bgcolor=#84C718></td></tr>
        <? } ?>

        <? if ($od[od_settle_case] == '신용카드') { ?>
        <tr class=ht>
            <td bgcolor=#F8FFED>신용카드 결제액</td>
            <td>
                <input type=text class=ed name=od_receipt_card size=10 
                    value='<? echo $od[od_receipt_card] ?>'>원
                &nbsp;
                <? 
                $card_url = $g4[yc4_cardpg][$default[de_card_pg]];
                ?>
                <a href='<? echo $card_url ?>' target=_new>결제대행사</a>
            </td>
        </tr>
        <tr class=ht>
            <td bgcolor=#F8FFED>카드 승인일시</td>
            <td>
                <input type=text class=ed name=od_card_time size=19 maxlength=19 value='<? echo is_null_time($od[od_card_time]) ? "" : $od[od_card_time]; ?>'>
                <input type=checkbox name=od_card_chk
                    value="<? echo date("Y-m-d H:i:s", $g4['server_time']); ?>"
                    onclick="if (this.checked == true) this.form.od_card_time.value=this.form.od_card_chk.value; else this.form.od_card_time.value = this.form.od_card_time.defaultValue;">현재 시간
            </td>
        </tr>
        <tr class=ht>
            <td bgcolor=#F8FFED>카드 승인취소</td>
            <td>
                <input type=text class=ed name=od_cancel_card size=10 value='<? echo $od[od_cancel_card] ?>'>원
            </td>
        </tr>
        <tr><td colspan=2 height=1 bgcolor=#84C718></td></tr>
        <? } ?>

        <tr class=ht>
            <td>포인트 결제액</td>
            <td>
                <input type=text class=ed name=od_receipt_point size=10 value='<? echo $od[od_receipt_point] ?>'>점
            </td>
        </tr>
        <tr class=ht>
            <td>DC</td>
            <td>
                <input type=text class=ed name=od_dc_amount size=10 value='<? echo $od[od_dc_amount] ?>'>원
            </td>
        </tr>
        <tr class=ht>
            <td>환불액</td>
            <td>
                <input type=text class=ed name=od_refund_amount size=10 value='<? echo $od[od_refund_amount] ?>'>원
                <?=help("카드승인취소를 입력한 경우에는 중복하여 입력하면 미수금이 틀려집니다.", 0, -100);?>
            </td>
        </tr>
        <tr><td colspan=2 height=1 bgcolor=#84C718></td></tr>

        <tr class=ht>
            <td>배송회사</td>
            <td>
                <select name=dl_id>
                    <option value=''>배송시 선택하세요.
                <?
                $sql = "select * from $g4[yc4_delivery_table] order by dl_order desc, dl_id desc ";
                $result = sql_query($sql);
                for ($i=0; $row=sql_fetch_array($result); $i++)
                    echo "<option value='$row[dl_id]'>$row[dl_company]\n";
                mysql_free_result($result);
                ?>
                </select>
        </tr>
        <tr class=ht>
            <td>운송장번호</td>
            <td><input type=text class=ed name=od_invoice 
                value='<? echo $od[od_invoice] ?>'>
                <? if ($default[de_sms_use4]) { ?>
                    <input type=checkbox name=od_sms_baesong_check> SMS 문자전송
                <? } ?>
            </td>
        </tr>
        <tr class=ht>
            <td>배송일시</td>
            <td>
                <input type=text class=ed name=od_invoice_time maxlength=19 value='<? echo is_null_time($od[od_invoice_time]) ? "" : $od[od_invoice_time]; ?>'>
                <input type=checkbox name=od_invoice_chk
                    value="<? echo date("Y-m-d H:i:s", $g4['server_time']); ?>"
                    onclick="if (this.checked == true) this.form.od_invoice_time.value=this.form.od_invoice_chk.value; else this.form.od_invoice_time.value = this.form.od_invoice_time.defaultValue;">현재 시간
            </td>
        </tr>
        <tr class=ht>
            <td>메일발송</td>
            <td>
                <input type=checkbox name=od_send_mail value='1'>예
                <?=help("주문자님께 입금, 배송내역을 메일로 발송합니다.\n\n메일발송후 상점메모에 메일발송 시간을 남겨 놓습니다.");?>
            </td>
        </tr>
		<tr><td colspan=2 height=1 bgcolor=CCCCCC></td></tr>
        </table>

        <?
        if ($od[dl_id] > 0)
            echo "<script language='javascript'> document.frmorderreceiptform.dl_id.value = '$od[dl_id]' </script>";
        ?>

        <br>
        <input type=submit class=btn1 value='결제/배송내역 수정'>&nbsp;
        <input type=button class=btn1 value='  목  록  ' onclick="document.location.href='./orderlist.php?<?=$qstr?>';">
    </td>
</tr>
</table>
</form>

<?=subtitle("상점메모")?>
<form name=frmorderform2 method=post action="./orderformupdate.php" style="margin:0px;">
<table width=100% cellpadding=0 cellspacing=0 border=0>
<input type=hidden name=od_id     value="<?=$od_id?>">
<input type=hidden name=sort1     value="<?=$sort1?>">
<input type=hidden name=sort2     value="<?=$sort2?>">
<input type=hidden name=sel_field value="<?=$sel_field?>">
<input type=hidden name=search    value="<?=$search?>">
<input type=hidden name=page      value="<?=$page?>">
<tr>
	<td width=90%>
        <textarea name="od_shop_memo" rows=8 style='width:99%;' class=ed><? echo stripslashes($od[od_shop_memo]) ?></textarea>
	</td>
    <td width=10%>
        <input type=submit class=btn1 value='메모 수정'>
        <br>
        <?=help("이 주문에 대해 일어난 내용을 메모하는곳입니다.\n\n위에서 메일발송한 내역도 이곳에 저장합니다.", -150);?>
    </td>
</tr>
</table>

<p><?=subtitle("주소정보")?>
<table width=100% cellpadding=0 cellspacing=0 border=0>
<tr>
    <td width=49% valign=top bgcolor=#ffffff>
        <table width=100% cellpadding=0 cellspacing=0 border=0 valign=top>
        <colgroup width=80>
        <colgroup width='' bgcolor=#ffffff>
        <tr class=ht>
            <td colspan=4 bgcolor=#ffffff align=left><B>주문하신 분</B></td>
        </tr>
		<tr><td colspan=2 height=1 bgcolor=CCCCCC></td></tr>
        <tr class=ht>
            <td>이름</td>
            <td><input type=text class=ed name=od_name value='<?=$od[od_name]?>' required itemname='주문하신 분 이름'></td>
		</tr>
        <tr class=ht>
            <td>전화번호</td>
            <td><input type=text class=ed name=od_tel value='<?=$od[od_tel]?>' required itemname='주문하신 분 전화번호'></td>
		</tr>
		<tr class=ht>
            <td>핸드폰</td>
            <td><input type=text class=ed name=od_hp value='<?=$od[od_hp]?>'></td>
        </tr>
        <tr class=ht>
            <td>주소</td>
            <td>
                <input type=text class=ed name=od_zip1 size=4 readonly required itemname='우편번호 앞자리' value='<?=$od[od_zip1]?>'> - 
                <input type=text class=ed name=od_zip2 size=4 readonly required itemname='우편번호 뒷자리' value='<?=$od[od_zip2]?>'>
                &nbsp;<a href="javascript:;" onclick="win_zip('frmorderform2', 'od_zip1', 'od_zip2', 'od_addr1', 'od_addr2');"><img src="<?=$g4[shop_admin_path]?>/img/btn_zip_find.gif" border=0 align=absmiddle></a><br>
                <input type=text class=ed name=od_addr1 size=50 readonly required itemname='주소' value='<?=$od[od_addr1]?>'><br>
                <input type=text class=ed name=od_addr2 size=50 required itemname='상세주소' value='<?=$od[od_addr2]?>'></td>
        </tr>
		<tr class=ht>
            <td>E-mail</td>
            <td><input type=text class=ed name=od_email size=30 email required itemname='주문하신 분 E-mail' value='<?=$od[od_email]?>'></td>
        </tr>
		<tr class=ht>
            <td>IP Address</td>
            <td><?=$od[od_ip]?></td>
        </tr>
		<tr><td colspan=2 height=1 bgcolor=CCCCCC></td></tr>
        </table>
    </td>
    <td width=2%></td>
    <td width=49% valign=top align=center>
		<table width=100% cellpadding=0 cellspacing=0>
        <colgroup width=80>
        <colgroup width='' bgcolor=#ffffff>
        <tr class=ht>
            <td colspan=4 bgcolor=#ffffff align=left><B>받으시는 분</B></td>
        </tr>
		<tr><td colspan=2 height=1 bgcolor=CCCCCC></td></tr>
        <tr class=ht>
            <td>이름</td>
            <td><input type=text class=ed name=od_b_name value='<?=$od[od_b_name]?>' required itemname='받으시는 분 이름'></td>
        </tr>
        <tr class=ht>
            <td>전화번호</td>
            <td><input type=text class=ed name=od_b_tel value='<?=$od[od_b_tel]?>' required itemname='받으시는 분 전화번호'></td>
		</tr>
		<tr class=ht>
            <td>핸드폰</td>
            <td><input type=text class=ed name=od_b_hp value='<?=$od[od_b_hp]?>'></td>
        </tr>
        <tr class=ht>
            <td>주소</td>
            <td>
                <input type=text class=ed name=od_b_zip1 size=4 readonly required itemname='우편번호 앞자리' value='<?=$od[od_b_zip1]?>'> - 
                <input type=text class=ed name=od_b_zip2 size=4 readonly required itemname='우편번호 뒷자리' value='<?=$od[od_b_zip2]?>'>
                &nbsp;<a href="javascript:;" onclick="win_zip('frmorderform2', 'od_b_zip1', 'od_b_zip2', 'od_b_addr1', 'od_b_addr2');"><img src="<?=$g4[shop_admin_path]?>/img/btn_zip_find.gif" border=0 align=absmiddle></a><br>
                <input type=text class=ed name=od_b_addr1 size=50 readonly required itemname='주소' value='<?=$od[od_b_addr1]?>'><br>
                <input type=text class=ed name=od_b_addr2 size=50 required itemname='상세주소' value='<?=$od[od_b_addr2]?>'></td>
        </tr>

        <? if ($default[de_hope_date_use]) { ?>
        <tr class=ht>
            <td>희망배송일</td>
            <td>
                <input type=text class=ed name=od_hope_date value='<?=$od[od_hope_date]?>' maxlength=10 minlength=10 required itemname='희망배송일'>
                (<?=get_yoil($od[od_hope_date])?>)</td>
		</tr>
        <? } ?>
        
        <tr class=ht>
            <td>전하는 말씀</td>
            <td colspan=3><?=nl2br($od[od_memo])?></td>
        </tr>
		<tr><td colspan=2 height=1 bgcolor=CCCCCC></td></tr>
		</table>
    </td>
</tr>
</table>

<p align=center>
    <input type=submit class=btn1 value='주소정보 수정'>&nbsp;
    <input type=button class=btn1 value='  목  록  ' accesskey='l' onclick="document.location.href='./orderlist.php?<?=$qstr?>';">&nbsp;
    <input type=button class=btn1 value='주문서 삭제' onclick="del('<?="./orderdelete.php?od_id=$od[od_id]&on_uid=$od[on_uid]&mb_id=$od[mb_id]&$qstr"?>');">
</form>

<script language='javascript'>
var select_all_sw = false;
var visible_sw = false;

// 전체선택, 전체해제
function select_all()
{
    var f = document.frmorderform;

    for (i=0; i<f.chk_cnt.value; i++)
    {
        if (select_all_sw == false)
            document.getElementById('ct_chk_'+i).checked = true;
        else
            document.getElementById('ct_chk_'+i).checked = false;
    }

    if (select_all_sw == false)
        select_all_sw = true;
    else
        select_all_sw = false;
}

function form_submit(status)
{
    var f = document.frmorderform;
    var check = false;

    for (i=0; i<f.chk_cnt.value; i++) {
        if (document.getElementById('ct_chk_'+i).checked == true) check = true;
    }
    
    if (check == false) {
        alert("처리할 자료를 하나 이상 선택해 주십시오.");
        return;
    }

    if (confirm("\'" + status + "\'을(를) 선택하셨습니다.\n\n이대로 처리 하시겠습니까?") == true) {
        f.ct_status.value = status;
        f.action = "./ordercartupdate.php";
        f.submit();
    }

    return;
}
</script>

<?
include_once("$g4[admin_path]/admin.tail.php");
?>
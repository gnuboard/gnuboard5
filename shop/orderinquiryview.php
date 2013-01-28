<?
include_once("./_common.php");

// 불법접속을 할 수 없도록 세션에 아무값이나 저장하여 hidden 으로 넘겨서 다음 페이지에서 비교함
$token = md5(uniqid(rand(), true));
set_session("ss_token", $token);

if (!$is_member) {
    if (get_session("ss_inquiry_uniqid") != $_GET['od_id'])
        alert("직접 링크로는 주문서 조회가 불가합니다.\\n\\n주문조회 화면을 통하여 조회하시기 바랍니다.");
}

$sql = "select * from {$g4['yc4_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);
if (!$od['od_id']) {
    alert('조회하실 주문서가 없습니다.', $g4['path']);
}

// 결제방법
$settle_case = $od['od_settle_case'];

$g4['title'] = "주문상세내역 : 주문번호 - $od_id";
include_once('./_head.php');
?>

<img src="<?=G4_SHOP_IMG_URL?>/top_orderinquiryview.gif" border=0><p>

<?
$s_uq_id = $od['od_id'];
$sw_direct = get_session('ss_inquiry_direct');
$s_page = 'orderinquiryview.php';
include './cartsub.inc.php';
?>

<script>
var openwin = window.open( './kcp/proc_win.html', 'proc_win', '' );
if(openwin != null) {
    openwin.close();
}
</script>

<br>
<div align=right><img src='<?=G4_SHOP_IMG_URL?>/status01.gif' align=absmiddle> : 주문대기, <img src='<?=G4_SHOP_IMG_URL?>/status02.gif' align=absmiddle> : 상품준비중, <img src='<?=G4_SHOP_IMG_URL?>/status03.gif' align=absmiddle> : 배송중, <img src='<?=G4_SHOP_IMG_URL?>/status04.gif' align=absmiddle> : 배송완료</div>

<table width=98% cellpadding=0 cellspacing=7 align=center>
<tr><td colspan=2>
    <img src='<?=G4_SHOP_IMG_URL?>/my_icon.gif' align=absmiddle> <B>주문번호 : <FONT COLOR="#D60B69"><?=$od[od_id]?></FONT></B></td></tr>
<tr><td colspan=2 height=2 bgcolor=#94A9E7></td></tr>
<tr><td align=center bgcolor=#F3F2FF><img src='<?=G4_SHOP_IMG_URL?>/t_data02.gif'></td>
    <td style='padding:20px'>
        <table cellpadding=4 cellspacing=0>
        <colgroup width=120>
        <colgroup width=''>
        <tr><td>· 주문일시</td><td>: <b><? echo $od[od_time] ?></b></td></tr>
        <tr><td>· 이 름</td><td>: <? echo $od[od_name] ?></td></tr>
        <tr><td>· 전화번호</td><td>: <? echo $od[od_tel] ?></td></tr>
        <tr><td>· 핸드폰</td><td>: <? echo $od[od_hp] ?></td></tr>
        <tr><td>· 주 소</td><td>: <?=sprintf("(%s-%s)&nbsp;%s %s", $od[od_zip1], $od[od_zip2], $od[od_addr1], $od[od_addr2])?></td></tr>
        <tr><td>· E-mail</td><td>: <? echo $od[od_email] ?></td></tr>
    </table></td></tr>
<tr><td colspan=2 height=1 bgcolor=#738AC6></td></tr>
<tr><td align=center bgcolor=#F3F2FF><img src='<?=G4_SHOP_IMG_URL?>/t_data03.gif'></td>
    <td style='padding:20px'>
        <table cellpadding=4 cellspacing=0>
        <colgroup width=120>
        <colgroup width=''>
        <tr><td>· 이 름</td><td>: <? echo $od[od_b_name] ?></td></tr>
        <tr><td>· 전화번호</td><td>: <? echo $od[od_b_tel] ?></td></tr>
        <tr><td>· 핸드폰</td><td>: <? echo $od[od_b_hp] ?></td></tr>
        <tr><td>· 주 소</td><td>: <?=sprintf("(%s-%s)&nbsp;%s %s", $od[od_b_zip1], $od[od_b_zip2], $od[od_b_addr1], $od[od_b_addr2])?></td></tr>
        <?
        // 희망배송일을 사용한다면
        if ($default[de_hope_date_use])
        {
            echo "<tr>";
            echo "<td>· 희망배송일</td>";
            echo "<td>: ".substr($od[od_hope_date],0,10)." (".get_yoil($od[od_hope_date]).")</td>";
            echo "</tr>";
        }

        if ($od[od_memo]) {
            echo "<tr>";
            echo "<td>· 전하실 말씀</td>";
            echo "<td>".conv_content($od[od_memo], 0)."</td>";
            echo "</tr>";
        }
        ?>
        </table></td></tr>
<tr><td colspan=2 height=1 bgcolor=#738AC6></td></tr>

<?
// 배송회사 정보
$dl = sql_fetch(" select * from $g4[yc4_delivery_table] where dl_id = '$od[dl_id]' ");

if ($od[od_invoice] || !$od[misu])
{
    echo "<tr><td align=center bgcolor='#F3F2FF'><img src='".G4_SHOP_IMG_URL."/t_data05.gif'></td>";
    echo "<td style='padding:20px'>";
    if (is_array($dl))
    {
        // get 으로 날리는 경우 운송장번호를 넘김
        if (strpos($dl[dl_url], "=")) $invoice = $od[od_invoice];
        echo "<table cellpadding=4 cellspacing=0>";
        echo "<colgroup width=120><colgroup width=''>";
        echo "<tr><td>· 배송회사</td><td>: $dl[dl_company] &nbsp;&nbsp;[<a href='$dl[dl_url]{$invoice}' target=_new>배송조회하기</a>]</td></tr>";
        echo "<tr><td>· 운송장번호</td><td>: $od[od_invoice]</td></tr>";
        echo "<tr><td>· 배송일시</td><td>: $od[od_invoice_time]</td></tr>";
        echo "<tr><td>· 고객센터 전화</td><td>: $dl[dl_tel]</td></tr>";
        echo "</table>";
    }
    else
    {
        echo "<span class=leading>&nbsp;&nbsp;아직 배송하지 않았거나 배송정보를 입력하지 못하였습니다.</span>";
    }
    echo "</td></tr>";
}
?><p>

<?
$misu = true;

if ($od['od_amount'] == $od['od_receipt_amount']) {
    $wanbul = " (완불)";
    $misu = false; // 미수금 없음
}

$misu_amount = $od['od_amount'] - $od['od_receipt_amount'];

echo "<tr>";
echo "<td align=center bgcolor=#FFEFFD height=60><img src='".G4_SHOP_IMG_URL."/t_data04.gif'></td>";
echo "<td style='padding:20px'>";

if ($od['od_settle_case'] == '신용카드')
{
    echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
    echo "<colgroup width=\"120\"><colgroup width=\"\">";
    echo "<tr><td>· 결제방식</td><td>: 신용카드 결제</td></tr>";

    if ($od['od_receipt_amount'])
    {
        echo "<tr><td>· 결제금액</td><td class=\"amount\">: " . display_amount($od['od_receipt_amount']) . "</td></tr>";
        echo "<tr><td>· 승인일시</td><td>: {$od['od_receipt_time']}</td>";
        echo "<tr><td>· 영수증</td><td>: <a href=\"javascript:;\" onclick=\"window.open('https://admin8.kcp.co.kr/assist/bill.BillAction.do?cmd=card_bill&c_trade_no={$od['tno']}', 'winreceipt', 'width=620,height=670');\">영수증 출력</a></td></tr>";
    }

    echo "</table><br>";
}
else if ($od['od_settle_case'] == '휴대폰')
{
    echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
    echo "<colgroup width=\"120\"><colgroup width=\"\">";
    echo "<tr><td>· 결제방식</td><td>: 휴대폰 결제</td></tr>";

    if ($od['od_receipt_amount'])
    {
        echo "<tr><td>· 결제금액</td><td class=\"amount\">: " . display_amount($od['od_receipt_amount']) . "</td></tr>";
        echo "<tr><td>· 승인일시</td><td>: {$od['od_receipt_time']}</td>";
        echo "<tr><td>· 휴대폰번호</td><td>: {$od['od_bank_account']}</td></tr>";
        echo "<tr><td>· 영수증</td><td>: <a href=\"javascript:;\" onclick=\"window.open('https://admin8.kcp.co.kr/assist/bill.BillAction.do?cmd=mcash_bill&h_trade_no={$od['tno']}', 'winreceipt', 'width=370,height=550')\">영수증 출력</a></td></tr>";
    }

    echo "</table><br>";
}
else
{
    echo "<table cellpadding=\"4\" cellspacing=\"0\">";
    echo "<colgroup width=\"120\"><colgroup width=\"\">";
    echo "<tr><td>· 결제방식</td><td>: {$od['od_settle_case']}</td></tr>";

    if ($od['od_receipt_amount'])
    {
        echo "<tr><td>· 입금액</td><td>: " . display_amount($od['od_receipt_amount']) . "</td></tr>";
        echo "<tr><td>· 입금확인일시</td><td>: {$od['od_receipt_time']}</td></tr>";
    }
    else
    {
        echo "<tr><td>· 입금액</td><td>: 아직 입금되지 않았거나 입금정보를 입력하지 못하였습니다.</td></tr>";
    }

    if ($od['od_settle_case'] != '계좌이체')
        echo "<tr><td>· 계좌번호</td><td>: {$od['od_bank_account']}</td></tr>";

    echo "<tr><td>· 입금자명</td><td>: {$od['od_deposit_name']}</td></tr>";

    if ($od['tno'])
        echo "<tr><td>· KCP 거래번호</td><td>: {$od['tno']}</td></tr>";

    echo "</table><br>";
}

if ($od['od_receipt_point'] > 0)
{
    echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
    echo "<colgroup width=\"120\"><colgroup width=\"\">";
    echo "<tr><td>· 포인트사용</td><td>: " . display_point($od['od_receipt_point']) . "</td></tr>";
    echo "</table>";
}

if ($od['od_refund_amount'] > 0)
{
    echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
    echo "<colgroup width=\"120\"><colgroup width=\"\">";
    echo "<tr><td>· 환불 금액</td><td>: " . display_amount($od['od_refund_amount']) . "</td></tr>";
    echo "</table>";
}

// 취소한 내역이 없다면
if ($tot_cancel_amount == 0) {
    if ($od['od_amount'] > 0 && $od['od_receipt_amount'] == 0) {
        echo "<br><form method=\"post\" action=\"./orderinquirycancel.php\" style=\"margin:0;\">";
        echo "<input type=\"hidden\" name=\"od_id\"  value=\"{$od['od_id']}\">";
        echo "<input type=\"hidden\" name=\"token\"  value=\"$token\">";
        echo "<br><table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
        echo "<colgroup width=\"120\"><colgroup width=\"\">";
        echo "<tr><td>· 주문취소</td><td>: <a href=\"javascript:;\" onclick=\"document.getElementById('_ordercancel').style.display='block';\">위의 주문을 취소합니다.</a></td></tr>";
        echo "<tr id=\"_ordercancel\" style=\"display:none;\"><td>· 취소사유</td><td>: <input type=text name=\"cancel_memo\" size=\"40\" maxlength=\"100\" required itemname=\"취소사유\"></textarea> <input type=\"submit\" value=\"확인\"></td></tr>";
        echo "</table></form>";
    } else if ($od['od_invoice'] == "") {
        echo "<br><table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
        echo "<colgroup width=\"120\"><colgroup width=\"\">";
        echo "<tr><td style=\"color:blue;\">· 이 주문은 직접 취소가 불가하므로 상점에 전화 연락 후 취소해 주십시오.</td></tr>";
        echo "</table>";
    }
} else {
    $misu_amount = $misu_amount - $send_cost;

    echo "<br><table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
    echo "<colgroup width=\"120\"><colgroup width=\"\">";
    echo "<tr><td style=\"color:red;\">· 주문 취소, 반품, 품절된 내역이 있습니다.</td></tr>";
    echo "</table>";
}


// 현금영수증 발급을 사용하는 경우에만
if ($default['de_taxsave_use']) {
    // 미수금이 없고 현금일 경우에만 현금영수증을 발급 할 수 있습니다.
    if ($misu_amount == 0 && $od['od_receipt_amount']) {
        if ($default['de_card_pg'] == 'kcp') {
            echo "<br />";
            echo "<table cellpadding=\"4\" cellspacing=\"0\" width=\"100%\">";
            echo "<colgroup width=\"120\"><colgroup width=\"\">";
            echo "<tr><td>· 현금영수증</td><td>: ";
            if ($od['od_cash_authno'])
                echo "<a href=\"javascript:;\" onclick=\"window.open('https://admin.kcp.co.kr/Modules/Service/Cash/Cash_Bill_Common_View.jsp?cash_no={$od['od_cash_authno']}', 'taxsave_receipt', 'width=360,height=647,scrollbars=0,menus=0');\">현금영수증 확인하기</a>";
            else
                echo "<a href=\"javascript:;\" onclick=\"window.open('taxsave_kcp.php?od_id=$od_id', 'taxsave', 'width=550,height=400,scrollbars=1,menus=0');\">현금영수증을 발급하시려면 클릭하십시오.</a>";
            echo "</td></tr>";
            echo "</table>";
        }
    }
}
?>
</td></tr>
<tr><td colspan=2 height=1 bgcolor=#94A9E7></td></tr>
<tr>
    <td colspan=2 align=right bgcolor=#E7EBF7 height=70>
        <b>결제 합계</b> <? echo $wanbul ?> : <b><? echo display_amount($od['od_receipt_amount']) ?></b></span>&nbsp;&nbsp;<br>
        <?
        if ($od[od_dc_amount] > 0) {
            echo "<br>DC : ". display_amount($od['od_dc_amount']) . "&nbsp;&nbsp;";
        }

        if ($misu_amount > 0) {
            echo "<br><font color=crimson><b>아직 결제하지 않으신 금액 : ".display_amount($misu_amount)."</b></font>&nbsp;&nbsp;";
        }
        ?></td></tr>
<tr><td colspan=2 height=2 bgcolor=#94A9E7></td></tr>
</table>
<br><br>

<?
include_once("./_tail.php");
?>
<?
include_once("./_common.php");

$html_title = "결제 결과";
include_once("./_head.php");

if (get_session('ss_temp_on_uid') != $on_uid)
    alert("정상적인 방법으로 확인하실 수 있습니다.", $g4[path]);


$sql = " select * from $g4[shop_card_history_table] where on_uid = '$on_uid' ";
$cd = sql_fetch($sql);
if ($cd[cd_id] == "")
    alert("값이 제대로 전달되지 않았습니다.");

/*
// 포인트 결제를 했다면 실제 포인트 결제한 것으로 수정합니다.
$sql = " select od_id, on_uid, od_receipt_point, od_temp_point from $g4[shop_order_table] where on_uid = '$on_uid' ";
$row = sql_fetch($sql);
if ($row[od_receipt_point] == 0 && $row[od_temp_point] != 0)
{
    sql_query(" update $g4[shop_order_table] set od_receipt_point = od_temp_point where on_uid = '$on_uid' ");
    insert_point($member[mb_id], (-1) * $row[od_temp_point], "주문번호:$row[od_id] 결제", "@order", $member[mb_id], "$row[od_id],$row[on_uid]");
}
*/

$sql = " select * from $g4[shop_order_table] where on_uid = '$on_uid' ";
$od = sql_fetch($sql);
    
// 이곳에서 정상 결제되었다는 메일도 같이 발송합니다.
@extract($od);
$tmp_on_uid = $on_uid;

if ($od[od_settle_case] == '가상계좌')
    $od_receipt_bank = $od_temp_bank;

include_once("ordermail1.inc.php");
include_once("ordermail2.inc.php");

if ($od[od_settle_case] == '가상계좌')
{
    $msg_settle_amount = '결제하실 금액';
    $settle_amount = $od[od_temp_bank];
    $msg_trade_time = '처리일시';
}
else
{
    $msg_settle_amount = '결제금액';
    $settle_amount = $cd[cd_amount];
    $msg_trade_time = '결제일시';
}
?>

<img src="<?=$g4[shop_img_path]?>/top_orderconfirm.gif" border="0"><p>

<table width=500 align=center cellpadding=0 cellspacing=0>
<tr><td align=center height=50><!-- 결제를 정상적으로 처리하였습니다. --></td></tr>
<tr><td height=2 bgcolor=#94a9e7></td></tr>
<tr><td bgcolor=#e7ebf7 height=28 align=center><?=$od['od_settle_case']?> 결제 내역</td></tr>
<tr>
    <td style='padding-left:10px'>
        <table cellpadding=5>
        <tr><td> · 주문번호</td><td>: <?=$cd[od_id]?></td></tr>
        <? if ($od[od_settle_case] == '신용카드') { ?><tr><td width=100> · 승인번호</td><td><font color="#7f3ca2">: <b><?=$cd[cd_app_no]?></b></font></td></tr><? } ?>
        <tr><td> · <?=$msg_settle_amount?></td><td>: <span class=amount><?=display_amount($settle_amount)?></span></td></tr>
        <? if ($od[od_settle_case] == '가상계좌') { ?><tr><td width=100> · 계좌번호</td><td><font color="#7f3ca2">: <b><?=$od[od_bank_account]?></b></font></td></tr><? } ?>
        <? if ($od[od_settle_case] == '휴대폰') { ?><tr><td width=100> · 휴대폰번호</td><td>: <?=$od[od_escrow2]?></td></tr><? } ?>
        <tr><td> · <?=$msg_trade_time?></td><td>: <?=$cd[cd_trade_ymd]?> <?=$cd[cd_trade_hms]?></td></tr>
        </table>
    </td></tr>
<tr><td height=2 bgcolor=#94a9e7></td></tr>
</table><br><br>

<? if ($od[od_settle_case] == '가상계좌' && $default[de_card_test] && $is_admin) { ?>
<center>
<div style="width:500px">
<fieldset>
<legend>모의입금처리</legend>
<p>관리자가 가상계좌 테스트를 한 경우에만 보입니다.</p>
<form method="post" action="http://devadmin.kcp.co.kr/Modules/Noti/TEST_Vcnt_Noti_Proc.jsp" target="_blank">
<input type="text" name="e_trade_no" value="<?=$od[od_escrow1]?>" size="80"><br />
<input type="text" name="deposit_no" value="<?=preg_replace("/^(.*\s)/", "", $od[od_bank_account])?>" size="80"><br />
<input type="text" name="req_name" value="<?=$od[od_name]?>" size="80"><br />
<input type="text" name="noti_url" value="<?=$g4[shop_url]?>/settle_kcp_common.php" size="80"><br /><br />
<input type="submit" value="입금통보 테스트">
</form>
</fieldset>
</div>
</center>
<? } ?>

<p align=center>
    <a href='<?="$g4[shop_path]/orderinquiryview.php?od_id=$od[od_id]&on_uid=$od[on_uid]";?>'><img src='<?=$g4[shop_img_path]?>/btn_confirm.gif' border=0></a>
<!-- <a href="javascript:;" onclick="window.open('http://admin.kcp.co.kr/Modules/Sale/Card/ADSA_CARD_BILL_Receipt.jsp?c_trade_no=<?=$_POST[trace_no]?>', 'winreceipt', 'width=620,height=670')">영수증 출력</a> -->

<?
include_once("./_tail.php");
?>
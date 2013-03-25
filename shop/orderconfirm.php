<?
include_once('./_common.php');

// 장바구니가 비어있는가?
$tmp_uq_id = get_session('ss_temp_uq_id');
if (get_cart_count($tmp_uq_id) == 0)// 장바구니에 담기
    alert("장바구니가 비어 있습니다.\\n\\n이미 주문하셨거나 장바구니에 담긴 상품이 없는 경우입니다.", "./cart.php");

set_session("ss_uq_id_inquiry", $tmp_uq_id);

$sql = " select * from {$g4['yc4_order_table']} where uq_id = '$tmp_uq_id' ";
$od = sql_fetch($sql);

//print_r2($od);

$g4['title'] = '주문 및 결제완료';

include_once('./_head.php');

// 상품명만들기
$sql = " select a.it_id, b.it_name
           from {$g4['yc4_cart_table']} a, {$g4['yc4_item_table']} b
          where a.it_id = b.it_id
            and a.uq_id = '$tmp_uq_id'
          order by ct_id
          limit 1 ";
$row = sql_fetch($sql);
?>

<img src="<?=G4_SHOP_URL?>/img/top_orderreceipt.gif" border=0><p>

<?
$s_page = '';
$s_uq_id = $tmp_uq_id;
$od_id = $od['od_id'];
include_once('./cartsub.inc.php');
?>

<br>
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<tr>
    <td><img src='<?=G4_SHOP_URL?>/img/my_icon.gif' align=absmiddle> <B>주문번호 : <FONT COLOR="#D60B69"><?=$od['od_id']?></FONT></B></td>
</tr>
</table>

<!-- 주문하시는 분 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#F3F2FF align=center><img src='<?=G4_SHOP_URL?>/img/t_data01.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=120>
        <colgroup width=''>
        <tr height=25>
            <td>이름</td>
            <td><? echo $od['od_name'] ?></td>
        </tr>
        <tr height=25>
            <td>전화번호</td>
            <td><? echo $od['od_tel'] ?></td>
        </tr>
        <tr height=25>
            <td>핸드폰</td>
            <td><? echo $od['od_hp'] ?></td>
        </tr>
        <tr height=25>
            <td>주소</td>
            <td><? echo sprintf("(%s-%s) %s %s", $od['od_zip1'], $od['od_zip2'], $od['od_addr1'], $od['od_addr2']); ?></td>
        </tr>
        <tr height=25>
            <td>E-mail</td>
            <td><? echo $od['od_email'] ?></td>
        </tr>

        <? if ($default['de_hope_date_use']) { // 희망배송일 사용한다면 ?>
        <tr height=25>
            <td>희망배송일</td>
            <td><?=$od['od_hope_date']?> (<?=get_yoil($od['od_hope_date'])?>)</td>
        </tr>
        <? } ?>
        </table>
    </td>
</tr>
</table>


<!-- 받으시는 분 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#F3F2FF align=center><img src='<?=G4_SHOP_URL?>/img/t_data03.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=120>
        <colgroup width=''>
        <tr height=25>
            <td>이름</td>
            <td><? echo $od['od_b_name']; ?></td>
        </tr>
        <tr height=25>
            <td>전화번호</td>
            <td><? echo $od['od_b_tel'] ?></td>
        </tr>
        <tr height=25>
            <td>핸드폰</td>
            <td><? echo $od['od_b_hp'] ?>&nbsp;</td>
        </tr>
        <tr height=25>
            <td>주소</td>
            <td><? echo sprintf("(%s-%s) %s %s", $od['od_b_zip1'], $od['od_b_zip2'], $od['od_b_addr1'], $od['od_b_addr2']); ?></td>
        </tr>
        <tr height=25>
            <td>전하실말씀</td>
            <td><? echo nl2br(htmlspecialchars2($od['od_memo'])); ?>&nbsp;</td>
        </tr>
        </table>
    </td>
</tr>
</table>

<!-- 결제 정보 -->
<table width=100% align=center cellpadding=0 cellspacing=10 border=0>
<colgroup width=140>
<colgroup width=''>
<tr>
    <td bgcolor=#FFEFFD align=center height=50><img src='<?=G4_SHOP_URL?>/img/t_data04.gif'></td>
    <td bgcolor=#FAFAFA style='padding-left:10px'>
        <table cellpadding=3>
        <colgroup width=120>
        <colgroup width=''>

        <? if ($od['od_temp_point'] > 0) { ?>
        <tr height=25>
            <td>포인트사용</td>
            <td><? echo display_point($od['od_temp_point']) ?></td>
        </tr>
        <? } ?>

        <? if ($od['od_temp_bank'] > 0) { ?>
            <tr height=25>
                <td><?=$od['od_settle_case']?></td>
                <td><? echo display_amount($od['od_temp_bank']) ?>  (결제하실 금액)</td>
            </tr>
            <? if ($od['od_settle_case'] == '무통장') { ?>
                <tr height=25>
                    <td>계좌번호</td>
                    <td><? echo $od['od_bank_account']; ?></td>
                </tr>
            <? } ?>
        <tr height=25>
            <td>입금자 이름</td>
            <td><? echo $od['od_deposit_name']; ?></td>
        </tr>
        <? } ?>

        <? if ($od['od_temp_card'] > 0) { ?>
        <tr height=25>
            <td>신용카드</td>
            <td><? echo display_amount($od['od_temp_card']) ?> (결제하실 금액)</td>
        </tr>
        <? } ?>

        <? if ($od['od_temp_hp'] > 0) { ?>
        <tr height=25>
            <td>휴대폰결제</td>
            <td><? echo display_amount($od['od_temp_hp']) ?> (결제하실 금액)</td>
        </tr>
        <? } ?>

        </table>
    </td>
</tr>
</table>

<?
// 파일이 존재한다면 ...
if (file_exists("./settle_{$default['de_card_pg']}.inc.php"))
{
    $settle_case = $od['od_settle_case'];
    if ($settle_case == '')
    {
        echo "*** 결제방법 없음 오류 ***";
    }
    else if ($settle_case == '무통장')
    {
        echo "<p align=center><a href='./orderinquiryview.php?od_id={$od['od_id']}&uq_id={$od['uq_id']}'><img src='".G4_SHOP_URL."/img/btn_order_end.gif' border=0></a>";
    }
    else
    {
        if ($settle_case == '신용카드')
            $settle_amount = $od['od_temp_card'];
        else if ($settle_case == '휴대폰')
            $settle_amount = $od['od_temp_hp'];
        else
            $settle_amount = $od['od_temp_bank'];

        include "./settle_{$default['de_card_pg']}.inc.php";
        //echo "<p align=center><input type='image' src='$g4[shop_img_path]/btn_settle.gif' border=0 onclick='OpenWindow();'>";
        echo "<p align=left>&nbsp; &middot; 결제가 제대로 되지 않은 경우 [<a href='./orderinquiryview.php?od_id={$od['od_id']}&uq_id={$od['uq_id']}'><u>주문상세조회 페이지</u></a>] 에서 다시 결제하실 수 있습니다.</p>";
    }
}
else
{
    if ($od['od_temp_card']) {
        include "./ordercard{$default['de_card_pg']}.inc.php";
        echo "<p align=center><input type='image' src='".G4_SHOP_URL."/img/btn_card.gif' border=0 onclick='OpenWindow();'></p>";
        echo "<p align=left>&nbsp; &middot; 결제가 제대로 되지 않은 경우 <a href='./orderinquiryview.php?od_id={$od['od_id']}&uq_id={$od['uq_id']}'><u>주문상세조회 페이지</u></a>에서 다시 결제하실 수 있습니다.</p>";
    } else if ($od['od_temp_bank'] && $od['od_bank_account'] == "계좌이체")  {
        include "./orderiche{$default['de_card_pg']}.inc.php";
        echo "<p align=center><input type='image' src='".G4_SHOP_URL."/img/btn_iche.gif' border=0 onclick='OpenWindow();'></p>";
        echo "<p align=left>&nbsp; &middot; 결제가 제대로 되지 않은 경우 [<a href='./orderinquiryview.php?od_id={$od['od_id']}&uq_id={$od['uq_id']}'><u>주문상세조회 페이지</u></a>] 에서 다시 결제하실 수 있습니다.</p>";
    } else {
        echo "<p align=center><a href='".G4_SHOP_URL."'><img src='".G4_SHOP_URL."/img/btn_order_end.gif' border=0></a>";
    }
}
?>
<br><br>

<?
include_once('./_tail.php');
?>
<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);

if(!$od['od_id'])
    alert_close('주문정보가 존해하지 않습니다.');

if($od['od_pg'] == 'inicis' && $od['od_settle_case'] == '계좌이체')
    alert_close('KG이니시스는 신용카드만 부분취소가 가능합니다.');

if($od['od_settle_case'] == '계좌이체' && substr($od['od_receipt_time'], 0, 10) >= G5_TIME_YMD)
        alert_close('실시간 계좌이체건의 부분취소 요청은 결제일 익일에 가능합니다.');

if($od['od_receipt_price'] - $od['od_refund_price'] <= 0)
    alert_close('부분취소 처리할 금액이 없습니다.');

$g5['title'] = $od['od_settle_case'].' 부분취소';
include_once(G5_PATH.'/head.sub.php');

// 취소가능금액
$od_misu = abs($od['od_misu']);
?>

<form name="forderpartcancel" method="post" action="./orderpartcancelupdate.php" onsubmit="return form_check(this);">
<input type="hidden" name="od_id" value="<?php echo $od_id; ?>">

<div class="new_win">
    <h1><?php echo $od['od_settle_case']; ?> 부분취소</h1>

    <div class="tbl_frm01 tbl_wrap">
        <table>
        <caption><?php echo $g5['title']; ?> 입력</caption>
        <colgroup>
            <col class="grid_4">
            <col>
        </colgroup>
        <tbody>
        <tr>
            <th scope="row">취소가능 금액</th>
            <td><?php echo display_price($od_misu); ?></td>
        </tr>
        </tr>
        <tr>
            <th scope="row"><label for="mod_tax_mny">과세 취소금액</label></th>
            <td><input type="text" name="mod_tax_mny" value="" id="mod_tax_mny" class="frm_input"> 원</td>
        </tr>
        <tr>
            <th scope="row"><label for="mod_free_mny">비과세 취소금액</label></th>
            <td><input type="text" name="mod_free_mny" value="" id="mod_free_mny" class="frm_input"> 원</td>
        </tr>
        <tr>
            <th scope="row"><label for="mod_memo">요청사유</label></th>
            <td><input type="text" name="mod_memo" id="mod_memo" required class="required frm_input" size="50"></td>
        </tr>
        </tbody>
        </table>
    </div>

    <div class="btn_confirm01 btn_confirm">
        <input type="submit" value="확인" class="btn_submit" accesskey="s">
        <button type="button" onclick="self.close();">닫기</button>
    </div>
</div>
</form>

<script>
function form_check(f)
{
    var max_mny = parseInt(<?php echo $od_misu; ?>);
    var tax_mny = parseInt(f.mod_tax_mny.value.replace("/[^0-9]/g", ""));
    var free_mny = 0;
    if(typeof f.mod_free.mny.value != "undefined")
        free_mny = parseInt(f.mod_free_mny.value.replace("/[^0-9]/g", ""));

    if(!tax_mny && !free_mny) {
        alert("과세 취소금액 또는 비과세 취소금액을 입력해 주십시오.");
        return false;
    }

    if((tax_mny && free_mny) && (tax_mny + free_mny) > max_mny) {
        alert("과세, 비과세 취소금액의 합을 "+number_format(String(max_mny))+"원 이하로 입력해 주십시오.");
        return false;
    }

    if(tax_mny && tax_mny > max_mny) {
        alert("과세 취소금액을 "+number_format(String(max_mny))+"원 이하로 입력해 주십시오.");
        return false;
    }

    if(free_mny && free_mny > max_mny) {
        alert("비과세 취소금액을 "+number_format(String(max_mny))+"원 이하로 입력해 주십시오.");
        return false;
    }

    return true;
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
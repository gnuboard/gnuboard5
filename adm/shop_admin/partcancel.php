<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check($auth[$sub_menu], "w");

$g4['title'] = $settle_method.' 부분취소 요청';
include_once(G4_PATH.'/head.sub.php');

$sql = " select *
            from {$g4['shop_order_table']}
            where od_id = '{$_GET['od_id']}' ";
$od = sql_fetch($sql);

if(!$od['od_id'])
    alert_close('주문정보가 존재하지 않습니다.');

if($od['od_settle_case'] != '신용카드' && $od['od_settle_case'] != '계좌이체')
    alert_close('부분취소는 신용카드 또는 실시간 계좌이체 결제건에 대해서만 요청할 수 있습니다.');

if($od['od_settle_case'] == '계좌이체' && substr(0, 10, $od['od_time']) == G4_TIME_YMD)
    alert_close('실시간 계좌이체건의 부분취소 요청은 결제일 익일에 가능합니다.');

$mod_type = 'RN07';
if($od['od_settle_case'] == '계좌이체')
    $mod_type = 'STPA';

$available_cancel = $od['od_receipt_amount'] - $od['od_cancel_card'];

// 복합과세 사용시 각각 취소가능 금액
if($default['de_tax_flag_use']) {
    $sql = " select ct_price, io_price, io_type, ct_qty, ct_notax, cp_amount
                from {$g4['shop_cart_table']}
                where uq_id = '{$od['uq_id']}'
                  and ct_status IN ( '주문', '준비', '배송', '완료' ) ";
    $result = sql_query($sql);

    $tot_tax_mny = 0;
    $tot_free_mny = 0;

    for($i=0; $row=sql_fetch_array($result); $i++) {
        if($row['ct_notax']) {
            if($row['io_type']) {
                $tot_free += ($row['io_priece'] * $row['ct_qty']);
            } else {
                $tot_free += (($row['ct_price'] + $row['io_price']) * $row['ct_qty']);
            }

            $tot_free -= $row['cp_amount'];
        } else {
            if($row['io_type']) {
                $tot_tax += ($row['io_priece'] * $row['ct_qty']);
            } else {
                $tot_tax += (($row['ct_price'] + $row['io_price']) * $row['ct_qty']);
            }

            $tot_tax -= $row['cp_amount'];
        }
    }

    //$tot_tax -= ($od['od_send_cost'] + $od['od_send_cost2'] - $od['od_coupon'] - $od['od_send_coupon'] - $od['od_receipt_point']);
}
?>

<div>
    <form name="fcardpartcancel" method="post" action="./partcancelupdate.php" onsubmit="return form_check(this);">
    <input type="hidden" name="od_id" value="<?php echo $od['od_id']; ?>">
    <input type="hidden" name="req_tx" value="mod">
    <input type="hidden" name="tno" value="<?php echo $od['od_tno']; ?>">
    <input type="hidden" name="mod_type" value="<?php echo $mod_type; ?>">
    <input type="hidden" name="rem_mny" value="<?php echo $available_cancel; ?>">
    <p>
        <?php echo $g4['title']; ?>
    </p>
    <?php if($default['de_tax_flag_use']) { ?>
    <p>
        과세 취소가능금액 : <?php echo number_format($tot_tax); ?> 원<br>
        비과세 취소가능금액 : <?php echo number_format($tot_free); ?> 원
    </p>
    <p>
        <label for="tax_mny">과세 취소요청금액</label>
        <input type="text" name="tax_mny" id="tax_mny" size="20"> 원
    </p>
    <p>
        <label for="mod_free_mny">비과세 취소요청금액</label>
        <input type="text" name="mod_free_mny" id="mod_free_mny" size="20"> 원
    </p>
    <?php } else { ?>
    <p>
        취소가능금액 : <?php echo number_format($available_cancel); ?> 원
    </p>
    <p>
        <label for="mod_mny">취소요청금액</label>
        <input type="text" name="mod_mny" id="mod_mny" size="20" required class="required"> 원
    </p>
    <?php } ?>
    <p>
        <label for="mod_desc">취소요청사유</label>
        <input type="text" name="mod_desc" id="mod_desc" size="50" required class="required">
    </p>
    <p>
        <input type="submit" value="확인">
        <a href="javascript:;" onclick="window.close();">창닫기</a>
    </p>
    </form>
</div>

<script>
function form_check(f)
{
    <?php if($default['de_tax_flag_use']) { ?>
    var tax_mny = f.tax_mny.value;
    var free_mny = f.mod_free_mny.value;

    if(tax_mny == "" && free_mny == "") {
        alert("과세 또는 비과세 취소요청금액을입력해 주십시오.");
        return false;
    }
    <?php } ?>

    return true;
}
</script>

<?php
include_once(G4_PATH.'/tail.sub.php');
?>
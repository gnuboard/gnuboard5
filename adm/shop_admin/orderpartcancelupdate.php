<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$tax_mny = isset($_POST['mod_tax_mny']) ? preg_replace('/[^0-9]/', '', $_POST['mod_tax_mny']) : 0;
$free_mny = isset($_POST['mod_free_mny']) ? preg_replace('/[^0-9]/', '', $_POST['mod_free_mny']) : 0;

if(!$tax_mny && !$free_mny)
    alert('과세 취소금액 또는 비과세 취소금액을 입력해 주십시오.');

if(!trim($mod_memo))
    alert('요청사유를 입력해 주십시오.');

// 주문정보
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);

if(! (isset($od['od_id']) && $od['od_id']))
    alert_close('주문정보가 존재하지 않습니다.');

if($od['od_settle_case'] == '계좌이체' && substr($od['od_receipt_time'], 0, 10) >= G5_TIME_YMD)
    alert_close('실시간 계좌이체건의 부분취소 요청은 결제일 익일에 가능합니다.');

// 금액비교
$od_misu = abs($od['od_misu']);

if(($tax_mny && $free_mny) && ($tax_mny + $free_mny) > $od_misu)
    alert('과세, 비과세 취소금액의 합을 '.display_price($od_misu).' 이하로 입력해 주십시오.');

if($tax_mny && $tax_mny > $od_misu)
    alert('과세 취소금액을 '.display_price($od_misu).' 이하로 입력해 주십시오.');

if($free_mny && $free_mny > $od_misu)
    alert('비과세 취소금액을 '.display_price($od_misu).' 이하로 입력해 주십시오.');

// PG사별 부분취소 실행
include_once(G5_SHOP_PATH.'/'.strtolower($od['od_pg']).'/orderpartcancel.inc.php');

include_once(G5_PATH.'/head.sub.php');
?>

<script>
alert("<?php echo $od['od_settle_case']; ?> 부분취소 처리됐습니다.");
opener.document.location.reload();
self.close();
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
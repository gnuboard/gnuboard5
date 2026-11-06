<?php
$sub_menu = '400400';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

check_admin_token();

$od_id = isset($_POST['od_id']) ? safe_replace_regex($_POST['od_id'], 'od_id') : '';
$mod_memo = isset($_POST['mod_memo']) ? trim(clean_xss_tags($_POST['mod_memo'], 1, 1)) : '';
$amounts = array();
foreach (array('mod_tax_mny', 'mod_free_mny') as $field) {
    $value = isset($_POST[$field]) ? $_POST[$field] : '';
    if (!is_string($value) || ($value !== '' && !preg_match('/^(?:[0-9]+|[0-9]{1,3}(?:,[0-9]{3})+)$/D', $value)))
        alert('취소금액은 양의 정수로 입력해 주십시오.');
    $amounts[] = (int)str_replace(',', '', $value);
}
list($tax_mny, $free_mny) = $amounts;
if (!$tax_mny && !$free_mny)
    alert('과세 취소금액 또는 비과세 취소금액을 입력해 주십시오.');

if(!trim($mod_memo))
    alert('요청사유를 입력해 주십시오.');

if (!$od_id) alert_close('주문정보가 존재하지 않습니다.');
require_once(G5_LIB_PATH.'/shop_order_access.lib.php');
shop_order_state_lock('partial_refund', $od_id);

// 잠금 획득 이후의 주문 금액으로 검사한다.
$sql = " select * from {$g5['g5_shop_order_table']} where od_id = '$od_id' ";
$od = sql_fetch($sql);

if(! (isset($od['od_id']) && $od['od_id']))
    alert_close('주문정보가 존재하지 않습니다.');

if($od['od_settle_case'] == '계좌이체' && substr($od['od_receipt_time'], 0, 10) >= G5_TIME_YMD)
    alert_close('실시간 계좌이체건의 부분취소 요청은 결제일 익일에 가능합니다.');

if ($od['od_pg'] === 'KAKAOPAY') {
    alert_close('SIRK 전용 카카오페이의 자동 취소 지원이 종료되었습니다. 이니시스 상점관리자에서 실제 취소 후 주문 상태와 환불금액을 확인해 주십시오.');
}

if (!isset($_POST['refund_before']) || !is_string($_POST['refund_before'])
    || $_POST['refund_before'] !== (string)(int)$od['od_refund_price'])
    alert_close('환불 기록이 변경됐습니다. 주문을 다시 열어 확인해 주십시오.');

$limit = shop_order_partial_refund_limit($od);
if ($tax_mny + $free_mny > $limit)
    alert('취소금액의 합을 '.display_price($limit).' 이하로 입력해 주십시오.');

// PG사별 부분취소 실행
include_once(G5_SHOP_PATH.'/'.strtolower($od['od_pg']).'/orderpartcancel.inc.php');

include_once(G5_PATH.'/head.sub.php');
?>

<script>
alert("<?php echo get_text($od['od_settle_case']); ?> 부분취소 처리됐습니다.");
opener.document.location.reload();
self.close();
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');

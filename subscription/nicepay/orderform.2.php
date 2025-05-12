<?php
if (!defined('_GNUBOARD_')) {
    exit;
} // 개별 페이지 접근 불가

// 정기결제 PG를 NHN_KCP 로 사용하지 않으면
if (get_subs_option('su_pg_service') !== 'nicepay') {
    return;
}
?>
<input type="hidden" name="PayMethod" value="">
<input type="hidden" name="good_mny"    value="<?php echo $tot_price; ?>">
<input type="hidden" name="oid"         value="<?php echo $od_id; ?>">
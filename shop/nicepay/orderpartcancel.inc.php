<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'nicepay') return;

include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$od_id = $od['od_id'];
$tno = $od['od_tno'];
$partialCancelCode = 1;
$cancel_msg = $mod_memo;    //취소사유
$cancelAmt = (int)$tax_mny + (int)$free_mny;

include G5_SHOP_PATH.'/nicepay/cancel_process.php';

$pg_res_cd = '';
$pg_res_msg = '';
$is_save_history = true;

if (isset($result['ResultCode'])) {
    // nicepay 의 경우 
    if ($result['ResultCode'] === '2001' || $result['ResultCode'] === '2211') {
        
        $add_memo_sql = '';

        if ($is_save_history) {
            // 환불금액기록
            $mod_history = G5_TIME_YMDHIS.' '.$member['mb_id'].' 부분취소 ('.$cancelAmt.') 처리, 잔액 ('.$result['RemainAmt'].")\n";
            $add_memo_sql = ", od_shop_memo = concat(od_shop_memo, \"$mod_history\") ";
        }

        $sql = " update {$g5['g5_shop_order_table']}
                    set od_refund_price = od_refund_price + '$cancelAmt'
                        $add_memo_sql
                    where od_id = '{$od['od_id']}'
                      and od_tno = '$tno' ";
        sql_query($sql);

        // 미수금 등의 정보 업데이트
        $info = get_order_info($od_id);

        $sql = " update {$g5['g5_shop_order_table']}
                    set od_misu     = '{$info['od_misu']}',
                        od_tax_mny  = '{$info['od_tax_mny']}',
                        od_vat_mny  = '{$info['od_vat_mny']}',
                        od_free_mny = '{$info['od_free_mny']}'
                    where od_id = '$od_id' ";
        sql_query($sql);

    } else {
        $pg_res_cd = $result['ResultCode'];
        $pg_res_msg = $result['ResultMsg'];
    }
} else {
    $pg_res_cd = '';
    $pg_res_msg = 'curl 로 데이터를 받지 못했습니다.';
}

if ($pg_res_msg) {
    alert('결제 부분취소 요청이 실패하였습니다.\\n\\n'.$pg_res_cd.' : '.$pg_res_msg);
}
<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'KAKAOPAY') return;

include_once(G5_SHOP_PATH.'/settle_kakaopay.inc.php');

$vat_mny       = round((int)$tax_mny / 1.1);

$currency      = 'WON';
$oldtid        = $od['od_tno'];
$price         = (int)$tax_mny + (int)$free_mny;
$confirm_price = (int)$od['od_receipt_price'] - (int)$od['od_refund_price'] - $price;
$buyeremail    = $od['od_email'];
$tax           = (int)$tax_mny - $vat_mny;
$taxfree       = (int)$free_mny;

$args = array(
    'key' => isset($default['de_kakaopay_iniapi_key']) ? $default['de_kakaopay_iniapi_key'] : '',
    'mid' => $default['de_kakaopay_mid'],
    'paymethod' => get_type_inicis_paymethod($od['od_settle_case']),
    'tid' => $od['od_tno'],
    'msg' => $od['od_id'].' '.$mod_memo,
    'price' => $price,
    'confirmPrice' => $confirm_price,
    'tax' => $tax,
    'taxFree' => $taxfree
);

$response = inicis_tid_cancel($args, true);     // KG 이니시스 부분취소일 경우 inicis_tid_cancel 함수 2번째 인자값을 true로
$result = json_decode($response, true);

if (isset($result['resultCode']) && $result['resultCode'] == '00') {
     // 환불금액기록
    $tno      = $result['prtcTid'];
    $re_price = $result['prtcPrice'];

    $sql = " update {$g5['g5_shop_order_table']}
                set od_refund_price = od_refund_price + '$re_price',
                    od_shop_memo = concat(od_shop_memo, \"$mod_memo\")
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
     if (isset($result['resultCode'])){
         alert($result['resultMsg'].' 코드 : '.$result['resultCode']);
     } else {
         alert('curl 오류로 부분환불에 실패했습니다.');
     }
 }
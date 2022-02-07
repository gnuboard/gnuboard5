<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'nicepay') return;

include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

$vat_mny        = round((int)$tax_mny / 1.1);

$oldtid        = $od['od_tno'];
$price         = (int)$tax_mny + (int)$free_mny;
// $cancelAmt     = (int)$od['od_receipt_price'] - (int)$od['od_refund_price'] - $price;
$buyeremail    = $od['od_email'];   
$tax           = (int)$tax_mny - $vat_mny;
$taxfree       = (int)$free_mny;

$nicepay->m_ActionType          = "CLO";                                    // 동작구분 : CLO (취소)
$nicepay->m_CancelAmt           = $price;                                   // 취소금액 설정
$nicepay->m_TID                 = $oldtid;                                  // 이전 TID값 적용
$nicepay->m_Moid                = $od['od_id'];                             // 주문번호
$nicepay->m_CancelIP            = $_SERVER['REMOTE_ADDR'];                  // 취소자 IP
$nicepay->m_CancelMsg           = "가맹점 부분취소";                    // 취소메시지
$nicepay->m_PartialCancelCode   = 1;                                        // 부분취소 ( 0 : 전체취소, 1 : 부분취소 )

// 관리자 취소 패스워드가 존재하는 경우
if($default['de_nicepay_admin_key']) {
    $nicepay->m_CancelPwd           = $default['de_nicepay_admin_key'];
}

// 나이스페이 결제 프로세스 진행
$nicepay->startAction();

// 결과값 확인
$resultCode = $nicepay->m_ResultData['ResultCode'];

// 2001, 2211 성공 확인
if($resultCode == "2001" || $resultCode == "2211") {
    // 환불금액기록
    $tno      = $nicepay->m_ResultData['TID'];
    $re_price = $nicepay->m_ResultData['CancelAmt'];

    $sql = " update {$g5['g5_shop_order_table']}
                set od_refund_price = od_refund_price + $re_price,
                od_shop_memo = concat(od_shop_memo, \"$mod_memo\")
                where od_id = '{$od['od_id']}'
                and od_tno = '$oldtid' ";

    sql_query($sql);

    // 미수금 등의 정보 업데이트
    $info = get_order_info($od_id);

    $sql = " update {$g5['g5_shop_order_table']}
                set od_misu     = {$info['od_misu']},
                od_tax_mny  = '{$info['od_tax_mny']}',
                od_vat_mny  = '{$info['od_vat_mny']}',
                od_free_mny = '{$info['od_free_mny']}'
                where od_id = '$od_id' ";

    sql_query($sql);
} else {
    // 실패시 오류코드 반환
    alert(auto_convert_charset($nicepay->m_ResultData["ResultMsg"], 'UTF-8'). ' 코드 : '.auto_convert_charset($nicepay->m_ResultData["ResultCode"], 'UTF-8'));
    // alert(iconv_utf8($nicepay->m_ResultData["ResultMsg"]).' 코드 : '.$nicepay->m_ResultData["ResultCode"]);
}
?>
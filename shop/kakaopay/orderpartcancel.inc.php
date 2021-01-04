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

/***********************
 * 3. 재승인 정보 설정 *
 ***********************/
$inipay->SetField("type",          "repay");                         // 고정 (절대 수정 불가)
$inipay->SetField("pgid",          "INIphpRPAY");                    // 고정 (절대 수정 불가)
$inipay->SetField("subpgip",       "203.238.3.10");                  // 고정
$inipay->SetField("mid",           $default['de_kakaopay_mid']);       // 상점아이디
$inipay->SetField("admin",         $default['de_kakaopay_cancelpwd']); //비대칭 사용키 키패스워드
$inipay->SetField("oldtid",        $oldtid);                         // 취소할 거래의 거래아이디
$inipay->SetField("currency",      $currency);                       // 화폐단위
$inipay->SetField("price",         $price);                          // 취소금액
$inipay->SetField("confirm_price", $confirm_price);                  // 승인요청금액
$inipay->SetField("buyeremail",    $buyeremail);                     // 구매자 이메일 주소
$inipay->SetField("tax",           $tax);                            // 부가세금액
$inipay->SetField("taxfree",       $taxfree);                        // 비과세금액

/******************
 * 4. 재승인 요청 *
 ******************/
$inipay->startAction();


/*******************************************************************
 * 5. 재승인 결과                                                  *
 *                                                                 *
 * 신거래번호 : $inipay->getResult('TID')                                     *
 * 결과코드 : $inipay->getResult('ResultCode') ("00"이면 재승인 성공)         *
 * 결과내용 : $inipay->getResult('ResultMsg') (재승인결과에 대한 설명)        *
 * 원거래 번호 : $inipay->getResult('PRTC_TID')                                *
 * 최종결제 금액 : $inipay->getResult('PRTC_Remains')                              *
 * 부분취소 금액 : $inipay->getResult('PRTC_Price')                          *
 * 부분취소,재승인 구분값 : $inipay->getResult('PRTC_Type')              *
 *                          ("0" : 재승인, "1" : 부분취소)         *
 * 부분취소(재승인) 요청횟수 : $inipay->getResult('PRTC_Cnt')           *
 *******************************************************************/

 if($inipay->getResult('ResultCode') == '00') {
     // 환불금액기록
    $tno      = $inipay->getResult('PRTC_TID');
    $re_price = $inipay->getResult('PRTC_Price');

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
     alert(iconv_utf8($inipay->GetResult("ResultMsg")).' 코드 : '.$inipay->GetResult("ResultCode"));
 }
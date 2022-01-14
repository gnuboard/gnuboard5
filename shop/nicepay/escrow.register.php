<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'nicepay') return;

include_once(G5_SHOP_PATH.'/settle_nicepay.inc.php');

// 배송비 지급형태에 대한 자료가 없음
// $dlv_charge = 'SH'; // 배송비 지급형태 (SH : 판매자부담, BH : 구매자부담)

$sendName   = $od['od_name'];
$sendPost   = $od['od_zip1'].$od['od_zip2'];
$sendAddr1  = $od['od_addr1'];
$sendAddr2  = $od['od_addr2'];
$sendTel    = $od['od_tel'];
$recvName   = $od['od_b_name'];
$recvPost   = $od['od_b_zip1'].$od['od_b_zip2'];
$recvAddr   = $od['od_b_addr1'].($od['od_b_addr2'] ? ' ' : '').$od['od_b_addr2'];
$recvTel    = $od['od_b_tel'];
$price      = $od['od_receipt_price'];


$nicepay->m_ActionType      = "PYO";
$nicepay->m_ReqType         = "03";                         // 요청 유형 (03: 배송등록)
$nicepay->m_TID             = $od['od_tno'];                // 거래번호
$nicepay->m_DeliveryCoNm    = $escrow_corp;                 // 배송 업체명
$nicepay->m_BuyerAddr       = $recvAddr;                    // 배송지 주소
$nicepay->m_InvoiceNum      = $escrow_numb;                 // 송장 번호
$nicepay->m_RegisterName    = $recvName;                    // 등록자 이름
$nicepay->m_ConfirmMail     = 1;                          // 구매결정 메일발송 여부 (1: 발송, 2: 미발송)
$nicepay->m_MailIP          = $_SERVER['REMOTE_ADDR'];      // 상점서버 IP
$nicepay->m_UserIp          = $od['od_ip'];                 // 회원사고객 IP
$nicepay->m_PayMethod       = "ESCROW";                     // 결제 수단 (ESCROW 고정)

$nicepay->startAction();

$resultData = $nicepay->m_ResultData;

$tid = $nicepay->m_TID;                                     // 거래 번호
$resultCode = $resultData['ResultCode'];                    // 결과코드
$resultMsg  = $resultData['ResultMsg'];                     // 결과 메시지
$dlv_date   = $resultData['ProcessDate'];                   // 거래 날짜
$dlv_time   = $resultData['ProcessTime'];                   // 거래 시간
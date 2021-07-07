<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'inicis') return;

include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

$oid        = $od['od_id'];
$EscrowType = 'I';
$invoice    = $escrow_numb;
$dlv_charge = 'SH'; // 배송비 지급형태 (SH : 판매자부담, BH : 구매자부담)
$sendName   = iconv_euckr($od['od_name']);
$sendPost   = $od['od_zip1'].$od['od_zip2'];
$sendAddr1  = iconv_euckr($od['od_addr1']);
$sendAddr2  = iconv_euckr($od['od_addr2']);
$sendTel    = $od['od_tel'];
$recvName   = iconv_euckr($od['od_b_name']);
$recvPost   = $od['od_b_zip1'].$od['od_b_zip2'];
$recvAddr   = iconv_euckr($od['od_b_addr1'].($od['od_b_addr2'] ? ' ' : '').$od['od_b_addr2']);
$recvTel    = $od['od_b_tel'];
$price      = $od['od_receipt_price'];

// 택배회사 코드
$exCode = array(
    '대한통운'         => 'korex',
    '아주택배'         => 'ajutb',
    'KT로지스'         => 'ktlogistics',
    '현대택배'         => 'hyundai',
    'CJ대한통운'       => 'cjgls',
    '한진택배'         => 'hanjin',
    '트라넷'           => 'tranet',
    '하나로택배'       => 'Hanaro',
    '사가와익스프레스' => 'Sagawa',
    'SEDEX'            => 'sedex',
    'KGB택배'          => 'kgbls',
    '로젠택배'         => 'kgb',
    'KG옐로우캡택배'   => 'yellow',
    '삼성HTH'          => 'hth',
    '동부택배'         => 'dongbu',
    '우체국'           => 'EPOST',
    '우편등기'         => 'registpost',
    '기타택배'         => '9999'
);

$dlv_exName = $escrow_corp;
$dlv_exCode = $exCode[$dlv_exName];
if(!$dlv_exCode)
    $dlv_exCode = '9999';

/*********************
 * 3. 지불 정보 설정 *
 *********************/
$inipay->SetField("tid",            $escrow_tno);                    // 거래아이디
$inipay->SetField("mid",            $default['de_inicis_mid']);      // 상점아이디
/**************************************************************************************************
 * admin 은 키패스워드 변수명입니다. 수정하시면 안됩니다. 1111의 부분만 수정해서 사용하시기 바랍니다.
 * 키패스워드는 상점관리자 페이지(https://iniweb.inicis.com)의 비밀번호가 아닙니다. 주의해 주시기 바랍니다.
 * 키패스워드는 숫자 4자리로만 구성됩니다. 이 값은 키파일 발급시 결정됩니다.
 * 키패스워드 값을 확인하시려면 상점측에 발급된 키파일 안의 readme.txt 파일을 참조해 주십시오.
 **************************************************************************************************/
$inipay->SetField("admin",          $default['de_inicis_admin_key']); // 키패스워드(상점아이디에 따라 변경)
$inipay->SetField("type",           "escrow");                        // 고정 (절대 수정 불가)
$inipay->SetField("escrowtype",     "dlv");                           // 고정 (절대 수정 불가)
$inipay->SetField("dlv_ip",         getenv("REMOTE_ADDR"));           // 고정

$inipay->SetField("oid",            $oid);
$inipay->SetField("soid",           "1");
//$inipay->SetField("dlv_date",     $dlv_date);
//$inipay->SetField("dlv_time",     $dlv_time);
$inipay->SetField("dlv_report",     $EscrowType);
$inipay->SetField("dlv_invoice",    $invoice);
$inipay->SetField("dlv_name",       $member['mb_id']);

$inipay->SetField("dlv_excode",     $dlv_exCode);
$inipay->SetField("dlv_exname",     $dlv_exName);
$inipay->SetField("dlv_charge",     $dlv_charge);

$inipay->SetField("dlv_invoiceday", G5_TIME_YMDHIS);
$inipay->SetField("dlv_sendname",   $sendName);
$inipay->SetField("dlv_sendpost",   $sendPost);
$inipay->SetField("dlv_sendaddr1",  $sendAddr1);
$inipay->SetField("dlv_sendaddr2",  $sendAddr2);
$inipay->SetField("dlv_sendtel",    $sendTel);

$inipay->SetField("dlv_recvname",   $recvName);
$inipay->SetField("dlv_recvpost",   $recvPost);
$inipay->SetField("dlv_recvaddr",   $recvAddr);
$inipay->SetField("dlv_recvtel",    $recvTel);

$inipay->SetField("dlv_goodscode",  $goodsCode);
$inipay->SetField("dlv_goods",      $goods);
$inipay->SetField("dlv_goodscnt",   $goodCnt);
$inipay->SetField("price",          $price);
$inipay->SetField("dlv_reserved1",  $reserved1);
$inipay->SetField("dlv_reserved2",  $reserved2);
$inipay->SetField("dlv_reserved3",  $reserved3);

$inipay->SetField("pgn",            $pgn);

/*********************
 * 3. 배송 등록 요청 *
 *********************/
$inipay->startAction();


/**********************
 * 4. 배송 등록  결과 *
 **********************/

 $tid        = $inipay->GetResult("tid");                    // 거래번호
 $resultCode = $inipay->GetResult("ResultCode");     // 결과코드 ("00"이면 지불 성공)
 $resultMsg  = $inipay->GetResult("ResultMsg");          // 결과내용 (지불결과에 대한 설명)
 $dlv_date   = $inipay->GetResult("DLV_Date");
 $dlv_time   = $inipay->GetResult("DLV_Time");
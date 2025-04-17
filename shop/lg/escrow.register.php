<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'lg') return;

if($default['de_card_test']) {
    $mid = 'tsi_'.$config['cf_lg_mid'];
    $service_url = "https://pgweb.tosspayments.com:9091/pg/wmp/mertadmin/jsp/escrow/rcvdlvinfo.jsp";
} else {
    $mid = 'si_'.$config['cf_lg_mid'];
    $service_url = "https://pgweb.tosspayments.com/pg/wmp/mertadmin/jsp/escrow/rcvdlvinfo.jsp";
}

// 택배사코드
$dlvcomcode = array(
    '대한통운'          => 'KE',
    '로젠택배'          => 'LG',
    '아주택배'          => 'AJ',
    'KG옐로우캡택배'    => 'YC',
    '우체국'            => 'PO',
    '이젠택배'          => 'EZ',
    '트라넷'            => 'TN',
    '한진택배'          => 'HJ',
    '현대택배'          => 'HD',
    '동부택배'          => 'FE',
    'Bell Express'      => 'BE',
    'CJ대한통운'        => 'CJ',
    'HTH'               => 'SS',
    'KGB택배'           => 'KB',
    'KT로지스택배'      => 'KT',
    'SC로지스택배'      => 'SC',
    '일양로지스'        => 'IY',
    '이노지스택배'      => 'IN',
    '하나로택배'        => 'HN',
    '대신택배'          => 'DS',
    '우편등기'          => 'RP'
);

// 발송정보
$oid            = $od['od_id'];                         // 주문번호
$productid      = '';                                   // 상품ID
$dlvtype        = '03';                                 // 등록내용구분
$rcvdate        = '';               				    // 실수령일자
$rcvname        = '';               				    // 실수령인명
$rcvrelation    = '';                       		    // 관계
$dlvdate        = date("YmdHi", G5_SERVER_TIME);        // 발송일자
$dlvcompcode    = $dlvcomcode[$escrow_corp];            // 배송회사코드
$dlvcomp        = str_replace(' ', '||', $escrow_corp); // 배송회사명
$dlvno          = str_replace(' ', '||', $escrow_numb); // 운송장번호
$dlvworker      = '';                                   // 배송자명
$dlvworkertel   = '';                                   // 배송자전화번호

$mertkey        = $config['cf_lg_mert_key'];            // 각 상점의 테스트용 상점키와 서비스용 상점키

$hashdate;                                              // 인증키
$datasize       = 1;                                    // 여러건 전송일대 상점셋팅

$hashdata       = md5($mid.$oid.$dlvdate.$dlvcompcode.$dlvno.$mertkey);


// LG유플러스의 배송결과등록페이지를 호출하여 배송정보등록함
/*
*	아래 URL 을 호출시 파라메터의 값에 공백이 발생하면 해당 URL이 비정상적으로 호출됩니다.
*	배송사명등을 파라메터로 등록시 공백을 "||" 으로 변경하여 주시기 바랍니다.
*/
$str_url = $service_url."?mid=$mid&oid=$oid&productid=$productid&orderdate=$orderdate&dlvtype=$dlvtype&rcvdate=$rcvdate&rcvname=$rcvname&rcvrelation=$rcvrelation&dlvdate=$dlvdate&dlvcompcode=$dlvcompcode&dlvno=$dlvno&dlvworker=$dlvworker&dlvworkertel=$dlvworkertel&hashdata=$hashdata";

/*
$ch = curl_init();

curl_setopt ($ch, CURLOPT_URL, $str_url);
curl_setopt ($ch, CURLOPT_COOKIEJAR, COOKIE_FILE_PATH);
curl_setopt ($ch, CURLOPT_COOKIEFILE, COOKIE_FILE_PATH);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

$fp = curl_exec ($ch);

if(curl_errno($ch)){
    // 연결실패시 DB 처리 로직 추가
}else{
    if(trim($fp)=="OK"){
        // 정상처리되었을때 DB 처리
    }else{
        // 비정상처리 되었을때 DB 처리
    }
}
curl_close($ch);
*/
/*
*	fopen 방식
*	php 4.3 버전 이전에서 사용가능
*/

$fp = @fopen($str_url,"r");

if(!$fp)
{
    // 연결실패시 DB 처리 로직 추가
}
else
{
    // 해당 페이지 return값 읽기
    while(!feof($fp))
    {
            $res .= fgets($fp,3000);
    }

    if(trim($res) == "OK")
    {
        // 정상처리되었을때 DB 처리
    }
    else
    {
        // 비정상처리 되었을때 DB 처리
    }
}
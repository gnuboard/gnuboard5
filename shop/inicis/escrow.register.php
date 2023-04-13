<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if($od['od_pg'] != 'inicis') return;

include_once(G5_SHOP_PATH.'/settle_inicis.inc.php');

// 택배회사 코드, https://manual.inicis.com/iniweb/code.html 에서 조회
$exCode = array(
    '대한통운'         => 'korex',
    '아주택배'         => 'ajutb',
    'KT로지스'         => 'ktlogistics',
    '롯데택배(구.현대)'  => 'hyundai',
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
    '경동택배'         => 'kdexp',
    '천일택배'         => 'chunil',
    '대신택배'         => 'daesin',
    '일양로지스'        => 'ilyang',
    '호남택배'         => 'honam',
    '편의점택배'        => 'cvsnet',
    '합동택배'         => 'hdexp',
    '기타택배'         => '9999'
);

//step1. 요청을 위한 파라미터 설정
// 가맹점관리자 > 상점정보 > 계약정보 > 부가정보 > INIAPI key 생성조회
if (function_exists('get_inicis_iniapi_key')) {
    $key = get_inicis_iniapi_key();
} else {
    $key = ! $default['de_card_test'] ? $default['de_inicis_iniapi_key'] : "ItEQKi3rY7uvDS8l";
}

$dlv_exName         = $escrow_corp;
$type        		= "Dlv";												    //"Dlv" 고정	
$mid         		= $default['de_inicis_mid'];					
$clientIp    		= $_SERVER['SERVER_ADDR'];                                  // 가맹점 요청 서버IP, 상점 임의 설정 가능 (상점측 서버 구분을 위함)
$timestamp   		= date("YmdHis");
$tid         		= $escrow_tno;				                                //에스크로 결제 승인TID	
$oid		 		= $od['od_id'];
$price		 		= $od['od_receipt_price'];
$report		 		= "I";													    //에스크로 등록형태 ["I":등록, "U":변경]	
$invoice			= $escrow_numb;											    //운송장번호
$registName			= $member['mb_id'];							
$exCode		 		= isset($exCode[$dlv_exName]) ? $exCode[$dlv_exName] : '';	//택배사코드 참고(https://manual.inicis.com/code/#gls)
$exName		 		= $dlv_exName;							
$charge		 		= "SH";														//배송비 지급형태 ("SH":판매자부담, "BH":구매자부담)	
$invoiceDay		 	= G5_TIME_YMDHIS;									        //배송등록 확인일자 (String 으로 timestamp 사용 가능)
$sendName		 	= $od['od_name'];
$sendTel		 	= $od['od_tel'];
$sendPost		 	= $od['od_zip1'].$od['od_zip2'];
$sendAddr1		 	= $od['od_addr1'].' '.$od['od_addr2'];
$recvName		 	= $od['od_b_name'];
$recvTel		 	= $od['od_b_tel'];
$recvPost		 	= $od['od_b_zip1'].$od['od_b_zip2'];
$recvAddr		 	= $od['od_b_addr1'].($od['od_b_addr2'] ? ' ' : '').$od['od_b_addr2'];

if(!$exCode)
    $exCode = '9999';

// hash => INIAPIKey + type + timestamp + clientIp + mid + oid + tid + price
$plainText = (string)$key.(string)$type.(string)$timestamp.(string)$clientIp.(string)$mid.(string)$oid.(string)$tid.(string)$price;

// hash 암호화
$hashData = hash("sha512", $plainText); 

//step2. key=value 로 post 요청

$data = array(
    'type' => $type,
    'mid' => $mid,
    'clientIp' => $clientIp,
    'timestamp' => $timestamp,
    'tid' => $tid,
    'oid' => $oid,
    'price' => $price,
    'report' => $report,
    'invoice' => $invoice,
    'registName' => $registName,
    'exCode' => $exCode,
    'exName' => $exName,
    'charge' => $charge,
    'invoiceDay' => $invoiceDay,
    'sendName' => $sendName,
    'sendTel' => $sendTel,
    'sendPost' => $sendPost,
    'sendAddr1' => $sendAddr1,
    'recvName' => $recvName,
    'recvTel' => $recvTel,
    'recvPost' => $recvPost,
    'recvAddr' => $recvAddr,
    'hashData'=> $hashData
);

// Request URL
$url = "https://iniapi.inicis.com/api/v1/escrow";  

$ch = curl_init();                                                      // curl 초기화
curl_setopt($ch, CURLOPT_URL, $url);                                    // 전송 URL 지정하기
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);                         // 요청 결과를 문자열로 반환 
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);                           // connection timeout 10초 
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));          // POST data
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);                            // (※ 로컬 테스트에서만 사용) 원격 서버의 인증서가 유효한지 검사 안함
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=utf-8'));   // 전송헤더 설정
curl_setopt($ch, CURLOPT_POST, 1);                                      // post 전송 
 
$response = curl_exec($ch);
curl_close($ch);

//step3. 요청 결과
$ini_result = json_decode($response, true);

 
/**********************
 * 4. 배송 등록  결과 *
 **********************/

$resultCode = $ini_result['resultCode'];        // 결과코드 ("00"이면 지불 성공)
$resultMsg  = $ini_result['resultMsg'];          // 결과내용 (지불결과에 대한 설명)
$dlv_date   = $ini_result['resultDate'];
$dlv_time   = $ini_result['resultTime'];
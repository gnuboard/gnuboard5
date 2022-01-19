<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!$config['cf_cert_use'] || $config['cf_cert_hp'] != 'kcb')
    alert('기본환경설정에서 KCB 휴대폰본인확인 서비스로 설정해 주십시오.');

// key 디렉토리 체크
require_once('./key_dir_check.php');

/**************************************************************************
 * okname 생년월일 본인 확인서비스 파라미터
 **************************************************************************/
$memId = $config['cf_cert_kcb_cd'];                 // 회원사코드
if(!$memId)
    alert('기본환경설정에서 KCB 회원사ID를 입력해 주십시오.');

$inTpBit = '0';                                     // 입력구분코드(고정값 '0' : KCB팝업에서 개인정보 입력)
$name = 'x';                                        // 성명 (고정값 'x')
$birthday = 'x';                                    // 생년월일 (고정값 'x')
$gender = 'x';                                      // 성별 (고정값 'x')
$ntvFrnrTpCd = 'x';                                 // 내외국인구분 (고정값 'x')
$mblTelCmmCd = 'x';                                 // 이동통신사코드 (고정값 'x')
$mbphnNo = 'x';                                     // 휴대폰번호 (고정값 'x')

$svcTxSeqno = get_uniqid();                         // 거래번호. 동일문자열을 두번 사용할 수 없음. ( 20자리의 문자열. 0-9,A-Z,a-z 사용.)

$clientIp = $_SERVER['SERVER_ADDR'];                // 회원사 IP,   $_SERVER["SERVER_ADDR"] 사용가능.
//$clientDomain = $_SERVER['HTTP_HOST'];              // 회원사 도메인, $_SERVER["HTTP_HOST"] 사용가능.
$p = @parse_url($_SERVER['HTTP_HOST']);
if(isset($p['host']) && $p['host'])
    $clientDomain = $p['host'];
else
    $clientDomain = $_SERVER['SERVER_NAME'];
unset($p);

$clientDomain = escapeshellarg($clientDomain);


$rsv1 = '0';                                        // 예약 항목
$rsv2 = '0';                                        // 예약 항목
$rsv3 = '0';                                        // 예약 항목

$hsCertMsrCd = '10';                                // 인증수단코드 2byte  (10:핸드폰)
$hsCertRqstCausCd = '00';                           // 인증요청사유코드 2byte  (00:회원가입, 01:성인인증, 02:회원정보수정, 03:비밀번호찾기, 04:상품구매, 99:기타)

$returnMsg = 'x';                                   // 리턴메시지 (고정값 'x')

//okname 실행 정보
// ########################################################################
// # 모듈 경로 지정 및 권한 부여 (절대경로)
// ########################################################################
// 실행모듈
if(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {
    if(PHP_INT_MAX == 2147483647) // 32-bit
        $exe = G5_OKNAME_PATH.'/bin/okname';
    else
        $exe = G5_OKNAME_PATH.'/bin/okname_x64';
} else {
    if(PHP_INT_MAX == 2147483647) // 32-bit
        $exe = G5_OKNAME_PATH.'/bin/okname.exe';
    else
        $exe = G5_OKNAME_PATH.'/bin/oknamex64.exe';
}

$logPath = G5_OKNAME_PATH.'/log';                   // 로그파일을 남기는 경우 로그파일이 생성될 경로 option에 'L'이 포함된 경우에만 생성
$targetId = '';                                     // 타겟ID (팝업오픈 스크립트의 window.name 과 동일하게 설정

if($config['cf_cert_use'] == 2) {
    // 실서비스일 경우
    $endPointURL = 'http://safe.ok-name.co.kr/KcbWebService/OkNameService';
    $commonSvlUrl = 'https://safe.ok-name.co.kr/CommonSvl';
    $endPointUrl = 'http://safe.ok-name.co.kr/KcbWebService/OkNameService';
} else {
    // 테스트일 경우
    $endPointURL = 'http://tsafe.ok-name.co.kr:29080/KcbWebService/OkNameService';
    $commonSvlUrl = 'https://tsafe.ok-name.co.kr:2443/CommonSvl';
    $endPointUrl = 'http://tsafe.ok-name.co.kr:29080/KcbWebService/OkNameService';
}

// ########################################################################
// # 리턴 URL 설정
// ########################################################################
if(!empty($resultPage))
$returnUrl = escapeshellarg(G5_OKNAME_URL.$resultPage);          // 본인인증 완료후 리턴될 URL (도메인 포함 full path);
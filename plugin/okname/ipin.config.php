<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if(!$config['cf_cert_use'] || $config['cf_cert_ipin'] != 'kcb')
    alert('기본환경설정에서 KCB 아이핀 본인확인 서비스로 설정해 주십시오.');

// key 디렉토리 체크
require_once('./key_dir_check.php');

$exe = '';
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

if($config['cf_cert_use'] == 2) {
    // 실서비스일 경우
    $cpCode = $config['cf_cert_kcb_cd'];
    $idpUrl = 'https://ipin.ok-name.co.kr/tis/ti/POTI90B_SendCertInfo.jsp';
    $EndPointURL = 'http://www.ok-name.co.kr/KcbWebService/OkNameService'; // 운영 서버
    $kcbForm_action = 'https://ipin.ok-name.co.kr/tis/ti/POTI01A_LoginRP.jsp';
} else {
    // 테스트일 경우
    $cpCode = 'P00000000000';
    $idpUrl = 'https://tmpin.ok-name.co.kr:5443/tis/ti/POTI90B_SendCertInfo.jsp';
    $EndPointURL = 'http://twww.ok-name.co.kr:8888/KcbWebService/OkNameService'; //EndPointURL, 테스트 서버
    $kcbForm_action = 'https://tmpin.ok-name.co.kr:5443/tis/ti/POTI01A_LoginRP.jsp';
}

$idpCode   = 'V';
$returnUrl = G5_OKNAME_URL.'/ipin2.php';    // 아이핀 인증을 마치고 돌아올 페이지 주소
$keypath = G5_OKNAME_PATH.'/key/okname.key';    // 키파일이 생성될 위치. 웹서버에 해당파일을 생성할 권한 필요.
$memid = $cpCode;   // 회원사코드
$reserved1 = '0';   //reserved1
$reserved2 = '0';   //reserved2
$logpath = G5_OKNAME_PATH.'/log';   // 로그파일을 남기는 경우 로그파일이 생성될 경로 option에 'L'이 포함된 경우에만 생성;
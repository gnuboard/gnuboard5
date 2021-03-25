<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 금일 인증시도 회수 체크
certify_count_check($member['mb_id'], 'hp');

// 서버상 bin 폴더 이전까지 경로
$home_dir = G5_KCPCERT_PATH; // ct_cli 절대경로 ( bin 전까지 )

// DI 를 위한 중복확인 식별 아이디
//web_siteid 값이 없으면 KCP 에서 지정한 값으로 설정됨
$web_siteid = '';

if ($config['cf_cert_use'] == 2) { // 실서비스
    $site_cd = 'SM'.$config['cf_cert_kcp_cd'];
    $cert_url = 'https://cert.kcp.co.kr/kcp_cert/cert_view.jsp';
} else if ($config['cf_cert_use'] == 1) { // 테스트사용
    $site_cd = 'S6186';
    $cert_url = 'https://testcert.kcp.co.kr/kcp_cert/cert_view.jsp';
} else { // 사용안함
    $site_cd = '';
    $cert_url = '';
}

if(!$site_cd)
    alert('KCP 휴대폰 본인확인 서비스 사이트코드가 없습니다.\\관리자 > 기본환경설정에 KCP 사이트코드를 입력해 주십시오.', G5_URL);

// KCP 인증 라이브러리
require G5_KCPCERT_PATH.'/lib/ct_cli_lib.php';

/* ============================================================================== */
/* =   null 값을 처리하는 메소드                                                = */
/* = -------------------------------------------------------------------------- = */
function f_get_parm_str( $val )
{
    if ( $val == null ) $val = "";
    if ( $val == ""   ) $val = "";
    return  $val;
}

//!!중요 해당 함수는 year, month, day 변수가 null 일 경우 00 으로 치환합니다
function f_get_parm_int( $val )
{
    $ret_val = "";

    if ( $val == null ) $val = "00";
    if ( $val == ""   ) $val = "00";

    $ret_val = strlen($val) == 1? ("0" . $val) : $val;

    return  $ret_val;
}
/* ============================================================================== */;
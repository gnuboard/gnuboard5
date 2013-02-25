<?
include_once('./_common.php');

$home_dir = G4_BBS_PATH.'/kcp'; // ct_cli 절대경로 ( bin 전까지 )

$web_siteid = ''; // 사이트 식별코드

$test_use = true;

if($test_use) { // 테스트
    $site_cd = 'S6186';
    $cert_url = 'https://testcert.kcp.co.kr/kcp_cert/cert_view.jsp';
} else { // 실인증
    $site_cd = '';
    $cert_url = 'https://cert.kcp.co.kr/kcp_cert/cert_view.jsp';
}

// KCP 인증 라이브러리
require G4_BBS_PATH.'/kcp/lib/ct_cli_lib.php';

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
/* ============================================================================== */
?>
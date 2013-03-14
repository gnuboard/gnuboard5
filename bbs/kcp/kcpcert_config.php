<?
include_once('./_common.php');

// /home/kcpcert_enc ( 서버상 bin 폴더 이전까지 경로)
$home_dir = G4_BBS_PATH.'/kcp'; // ct_cli 절대경로 ( bin 전까지 )

// DI 를 위한 중복확인 식별 아이디
//web_siteid 값이 없으면 KCP 에서 지정한 값으로 설정됨
$web_siteid = '';

if($config['cf_kcpcert_site_cd'] && $config['cf_kcpcert_site_cd'] != 'S6186') { // 실인증
    $site_cd = $config['cf_kcpcert_site_cd'];
    $cert_url = 'https://cert.kcp.co.kr/kcp_cert/cert_view.jsp';
} else { // 테스트인증
    $site_cd = 'S6186';
    $cert_url = 'https://testcert.kcp.co.kr/kcp_cert/cert_view.jsp';
}

if(!$site_cd)
    alert('KCP 휴대폰인증 사이트코드가 없습니다.\\관리자 > 기본환경설정에 사이트코드를 입력해 주십시오.', G4_URL);

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
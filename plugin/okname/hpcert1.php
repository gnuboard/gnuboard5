<?php
include_once('./_common.php');

$check_arrays = array('exe', 'svcTxSeqno', 'name', 'birthday', 'gender', 'ntvFrnrTpCd', 'mblTelCmmCd', 'mbphnNo', 'rsv1', 'rsv2', 'rsv3', 'returnMsg', 'returnUrl', 'inTpBit', 'hsCertMsrCd', 'hsCertRqstCausCd', 'memId', 'clientIp', 'clientDomain', 'endPointURL', 'logPath');

foreach($check_arrays as $key){
    if( isset($_REQUEST[$key]) && $_REQUEST[$key] ){
        die('bad request');
    }

    $$key = '';
}

// 금일 인증시도 회수 체크
certify_count_check($member['mb_id'], 'hp');

switch($_GET['pageType']){		
    case "register":
        $resultPage = "/hpcert2.php";
        break;
    case "find":
        $resultPage = "/find_hpcert2.php";
        break;
    default:
        alert_close('잘못된 접근입니다.');
}
// KISA 취약점 내용(KVE-2018-0291) hpcert1.php의 $cmd 함수에 대한 인자 값은 hpcert_config.php 파일에서 설정되나, 이를 다른 페이지에서 포함한 뒤 호출할 시 임의 값 설정 가능
// 이에 include_once 를 require 로 수정함
require('./hpcert.config.php');
/**************************************************************************
okname 실행
**************************************************************************/
$option = "Q";

$cmd = "$exe $svcTxSeqno \"$name\" $birthday $gender $ntvFrnrTpCd $mblTelCmmCd $mbphnNo $rsv1 $rsv2 $rsv3 \"$returnMsg\" $returnUrl $inTpBit $hsCertMsrCd $hsCertRqstCausCd $memId $clientIp $clientDomain $endPointURL $logPath $option";

//cmd 실행
exec($cmd, $out, $ret);

if($ret == 127) {
    alert_close('모듈실행 파일이 존재하지 않습니다.\\n\\n'.basename($exe).' 파일이 '.G5_PLUGIN_DIR.'/'.G5_OKNAME_DIR.'/bin 안에 있어야 합니다.');
}

if($ret == 126) {
    alert_close('모듈실행 파일의 실행권한이 없습니다.\\n\\nchmod 755 '.basename($exe).' 과 같이 실행권한을 부여해 주십시오.');
}

if($ret == -1) {
    alert_close('모듈실행 파일의 실행권한이 없습니다.\\n\\ncmd.exe의 IUSER 실행권한이 있는지 확인하여 주십시오.');
}

/**************************************************************************
okname 응답 정보
**************************************************************************/
$retcode = "";										// 결과코드
$retmsg = "";										// 결과메시지
$e_rqstData = "";									// 암호화된요청데이터

if ($ret == 0) {//성공일 경우 변수를 결과에서 얻음
    $retcode = $out[0];
    $retmsg  = $out[1];
    $e_rqstData = $out[2];
}
else {
    if($ret <=200)
        $retcode=sprintf("B%03d", $ret);
    else
        $retcode=sprintf("S%03d", $ret);
}

$g5['title'] = 'KCB 휴대폰 본인확인';
include_once(G5_PATH.'/head.sub.php');
?>

<script>
function request(){
    //window.name = "<?php echo $targetId; ?>";

    document.form1.action = "<?php echo $commonSvlUrl; ?>";
    document.form1.method = "post";

    document.form1.submit();
}
</script>

<form name="form1">
<!-- 인증 요청 정보 -->
<!--// 필수 항목 -->
<input type="hidden" name="tc" value="kcb.oknm.online.safehscert.popup.cmd.P901_CertChoiceCmd"> <!-- 변경불가-->
<input type="hidden" name="rqst_data"				value="<?php echo $e_rqstData; ?>">		    <!-- 요청데이터 -->
<input type="hidden" name="target_id"				value="<?php echo $targetId; ?>">		    <!-- 타겟ID -->
<!-- 필수 항목 //-->
</form>

<?php
if ($retcode == "B000") {
    //인증요청
    echo ("<script>request();</script>");
} else {
    //요청 실패 페이지로 리턴
    echo ("<script>alert(\"$retcode\"); self.close();</script>");
}

include_once(G5_PATH.'/tail.sub.php');
<?php
include_once('./_common.php');

$check_arrays = array('exe', 'keypath', 'memId', 'endPointURL', 'endPointUrl', 'logPath');

foreach($check_arrays as $key){
    if( isset($_REQUEST[$key]) && $_REQUEST[$key] ){
        die('bad request');
    }

    $$key = '';
}

// KISA 취약점 내용(KVE-2018-0291) hpcert1.php의 $cmd 함수에 대한 인자 값은 hpcert_config.php 파일에서 설정되나, 이를 다른 페이지에서 포함한 뒤 호출할 시 임의 값 설정 가능
// 이에 include_once 를 require 로 수정함
require('./hpcert.config.php');
/**************************************************************************
    파일명 : safe_hs_cert3.php

    생년월일 본인 확인서비스 결과 화면(return url)
**************************************************************************/

/* 공통 리턴 항목 */
//$idcfMbrComCd           =   $_REQUEST['idcf_mbr_com_cd'];      // 고객사코드
$idcfMbrComCd           =   $memId;
$hsCertSvcTxSeqno       =   isset($_REQUEST['hs_cert_svc_tx_seqno']) ? $_REQUEST['hs_cert_svc_tx_seqno'] : ''; // 거래번호
$rqstSiteNm             =   isset($_REQUEST['rqst_site_nm']) ? $_REQUEST['rqst_site_nm'] : '';         // 접속도메인
$hsCertRqstCausCd       =   isset($_REQUEST['hs_cert_rqst_caus_cd']) ? $_REQUEST['hs_cert_rqst_caus_cd'] : ''; // 인증요청사유코드 2byte  (00:회원가입, 01:성인인증, 02:회원정보수정, 03:비밀번호찾기, 04:상품구매, 99:기타)

$resultCd               =   isset($_REQUEST['result_cd']) ? $_REQUEST['result_cd'] : '';            // 결과코드
$resultMsg              =   isset($_REQUEST['result_msg']) ? $_REQUEST['result_msg'] : '';           // 결과메세지
$certDtTm               =   isset($_REQUEST['cert_dt_tm']) ? $_REQUEST['cert_dt_tm'] : '';           // 인증일시

if($resultCd != 'B000') {
    alert_close('휴대폰 본인확인 중 오류가 발생했습니다. 오류코드 : '.$resultCd.'\\n\\n문의는 코리아크레딧뷰로 고객센터 02-708-1000 로 해주십시오.');
}

/**************************************************************************
 * 모듈 호출    ; 생년월일 본인 확인서비스 결과 데이터를 복호화한다.
 **************************************************************************/
$encInfo = isset($_REQUEST['encInfo']) ? $_REQUEST['encInfo'] : '';
if(preg_match('~[^0-9a-zA-Z+/=]~', $encInfo, $match)) {echo "입력 값 확인이 필요합니다"; exit;}

//KCB서버 공개키
$WEBPUBKEY = isset($_REQUEST['WEBPUBKEY']) ? trim($_REQUEST['WEBPUBKEY']) : '';
if(preg_match('~[^0-9a-zA-Z+/=]~', $WEBPUBKEY, $match)) {echo "입력 값 확인이 필요합니다"; exit;}

//KCB서버 서명값
$WEBSIGNATURE = isset($_REQUEST['WEBSIGNATURE']) ? trim($_REQUEST['WEBSIGNATURE']) : '';
if(preg_match('~[^0-9a-zA-Z+/=]~', $WEBSIGNATURE, $match)) {echo "입력 값 확인이 필요합니다"; exit;}

// ########################################################################
// # 암호화키 파일 설정 (절대경로) - 파일은 주어진 파일명으로 자동 생성 됨
// ########################################################################
$keypath = G5_OKNAME_PATH.'/key/safecert_'.$idcfMbrComCd.'.key';

$cpubkey = $WEBPUBKEY;    //server publickey
$csig = $WEBSIGNATURE;    //server signature

// ########################################################################
// # 로그 경로 지정 및 권한 부여 (절대경로)
// # 옵션값에 'L'을 추가하는 경우에만 로그가 생성됨.
// ########################################################################
$option = 'SU';

// 명령어
$cmd = "$exe $keypath $idcfMbrComCd $endPointUrl $WEBPUBKEY $WEBSIGNATURE $encInfo $logPath $option";

// 실행
exec($cmd, $out, $ret);

// 인증내역기록
@insert_cert_history($member['mb_id'], 'kcb', 'hp');

if($ret == 0) {
    // 결과라인에서 값을 추출
    foreach($out as $a => $b) {
        if($a < 17) {
            $field[$a] = $b;
        }
    }
    $resultCd = $field[0];
}
else {
    if($ret <=200)
        $resultCd=sprintf("B%03d", $ret);
    else
        $resultCd=sprintf("S%03d", $ret);
}

/*
echo "처리결과코드      :$resultCd  <br/>";
echo "처리결과메시지    :$field[1]  <br/>";
echo "거래일련번호      :$field[2]  <br/>";
echo "인증일시          :$field[3]  <br/>";
echo "DI                :$field[4]  <br/>";
echo "CI                :$field[5]  <br/>";
echo "성명              :$field[7]  <br/>";
echo "생년월일          :$field[8]  <br/>";
echo "성별              :$field[9]  <br/>";
echo "내외국인구분      :$field[10] <br/>";
echo "통신사코드        :$field[11] <br/>";
echo "휴대폰번호        :$field[12] <br/>";
echo "리턴메시지        :$field[16] <br/>";
*/

// 인증결과처리
$mb_name = $field[7];
$req_num = $field[12];
$mb_birth = $field[8];
$mb_dupinfo = $field[4];
$ci = $field[5];

$phone_no = hyphen_hp_number($req_num);
$md5_ci = md5($ci.$ci);

$row = sql_fetch("select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$md5_ci}'"); // ci데이터로 찾음
if (empty($row['mb_id'])) { // ci로 등록된 계정이 없다면
    $row = sql_fetch("select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$mb_dupinfo}'"); // di데이터로 찾음
    if(empty($row['mb_id'])) {
        alert_close("인증하신 정보로 가입된 회원정보가 없습니다.");
        exit;
    }
}else{
    $mb_dupinfo = $md5_ci;
}

$md5_cert_no = md5($cert_no);
$hash_data   = md5($user_name.$cert_type.$birth_day.$phone_no.$md5_cert_no);

// 성인인증결과
$adult_day = date("Ymd", strtotime("-19 years", G5_SERVER_TIME));
$adult = ((int)$birth_day <= (int)$adult_day) ? 1 : 0;

set_session("ss_cert_type",    $cert_type);
set_session("ss_cert_no",      $md5_cert_no);
set_session("ss_cert_hash",    $hash_data);
set_session("ss_cert_adult",   $adult);
set_session("ss_cert_birth",   $birth_day);
set_session('ss_cert_sex',     ($field[9] == 1 ? 'M' : 'F'));
set_session('ss_cert_dupinfo', $mb_dupinfo);
set_session('ss_cert_mb_id', $row['mb_id']);

$g5['title'] = 'KCB 휴대폰 본인확인';
include_once(G5_PATH.'/head.sub.php');
?>
<form name="mbFindForm" method="POST">
    <input type="hidden" name="mb_id" value="<?php echo $row["mb_id"]; ?>">    
</form>
<script>
    jQuery(function($) {
        
        var $opener = window.opener;
        var is_mobile = false;        
        $opener.name="parentPage";

        if (typeof g5_is_mobile != "undefined" && g5_is_mobile ) {
            $opener = window.parent;
            is_mobile = true;
        } else {
            $opener = window.opener;
        }
            
        document.mbFindForm.target = "parentPage";
        document.mbFindForm.action = "<?php echo G5_BBS_URL.'/password_reset.php'?>";
        document.mbFindForm.submit();

        alert("본인인증이 완료되었습니다.");
        window.close();        
    });
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
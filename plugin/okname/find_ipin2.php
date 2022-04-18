<?php
include_once('./_common.php');

$check_arrays = array('exe', 'keypath', 'memid', 'EndPointURL', 'cpubkey', 'csig', 'encdata', 'logpath', 'option');

foreach($check_arrays as $key){
    if( isset($_REQUEST[$key]) && $_REQUEST[$key] ){
        die('bad request');
    }

    $$key = '';
}

require('./ipin.config.php');

//아이핀팝업에서 조회한 PERSONALINFO이다.
@$encPsnlInfo = isset($_REQUEST["encPsnlInfo"]) ? $_REQUEST["encPsnlInfo"] : '';
if(preg_match('~[^0-9a-zA-Z+/=]~', $encPsnlInfo, $match)) {echo "입력 값 확인이 필요합니다"; exit;}

//KCB서버 공개키
@$WEBPUBKEY = isset($_REQUEST["WEBPUBKEY"]) ? trim($_REQUEST["WEBPUBKEY"]) : '';
if(preg_match('~[^0-9a-zA-Z+/=]~', $WEBPUBKEY, $match)) {echo "입력 값 확인이 필요합니다"; exit;}

//KCB서버 서명값
@$WEBSIGNATURE = isset($_REQUEST["WEBSIGNATURE"]) ? trim($_REQUEST["WEBSIGNATURE"]) : '';
if(preg_match('~[^0-9a-zA-Z+/=]~', $WEBSIGNATURE, $match)) {echo "입력 값 확인이 필요합니다"; exit;}

//아이핀 서버와 통신을 위한 키파일 생성
// 파라미터 정의
$cpubkey = $WEBPUBKEY;    //server publickey
$csig = $WEBSIGNATURE;    //server signature
$encdata = $encPsnlInfo;  //PERSONALINFO
$option = "SU";

// 명령어
$cmd = "$exe $keypath $memid $EndPointURL $cpubkey $csig $encdata $logpath $option";

// 실행
exec($cmd, $out, $ret);

// 인증내역기록
@insert_cert_history($member['mb_id'], 'kcb', 'ipin');

if($ret != 0) {
    if($ret <=200)
        $resultCd=sprintf("B%03d", $ret);
    else
        $resultCd=sprintf("S%03d", $ret);

    alert_close('아이핀 본인확인 중 오류가 발생했습니다. 오류코드 : '.$resultCd.'\\n\\n문의는 코리아크레딧뷰로 고객센터 02-708-1000 로 해주십시오.');
}

// 결과라인에서 값을 추출
foreach($out as $a => $b) {
    if($a < 13) {
        $field[$a] = $b;
    }
}

/*
$field_name_IPIN_DEC = array(
    "dupInfo        ",	// 0
    "coinfo1        ",	// 1
    "coinfo2        ",	// 2
    "ciupdate       ",	// 3
    "virtualNo      ",	// 4
    "cpCode         ",	// 5
    "realName       ",	// 6
    "cpRequestNumber",	// 7
    "age            ",	// 8
    "sex            ",	// 9
    "nationalInfo   ",	// 10
    "birthDate      ",	// 11
    "authInfo       ",	// 12
);
*/

$mb_name = $field[6];
$req_num = $field[7];
$mb_birth = $field[11];
$mb_dupinfo = $field[0];
if(!empty($field[1])) { // 아이핀은 리턴받는 ci 데이터가 두가지인걸로 보아 개인별로 받는 곳이 다를 수도 있을것 같아서 추가함 2021-09-13 hjkim7153
    $ci = $field[1];
}else if(!empty($field[2])) {
    $ci = $field[2];
}else{
    alert_close('아이핀 본인확인 중 오류가 발생했습니다. (ci 정보 없음) 오류코드 : '.$resultCd.'\\n\\n문의는 코리아크레딧뷰로 고객센터 02-708-1000 로 해주십시오.');
}
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

$g5['title'] = 'KCB 아이핀 본인확인';
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
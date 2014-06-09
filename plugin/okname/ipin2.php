<?php
include_once('./_common.php');
include_once('./ipin.config.php');

//아이핀팝업에서 조회한 PERSONALINFO이다.
@$encPsnlInfo = $_POST["encPsnlInfo"];
if(preg_match('~[^0-9a-zA-Z+/=]~', $encPsnlInfo, $match)) {echo "입력 값 확인이 필요합니다"; exit;}

//KCB서버 공개키
@$WEBPUBKEY = trim($_POST["WEBPUBKEY"]);
if(preg_match('~[^0-9a-zA-Z+/=]~', $WEBPUBKEY, $match)) {echo "입력 값 확인이 필요합니다"; exit;}

//KCB서버 서명값
@$WEBSIGNATURE = trim($_POST["WEBSIGNATURE"]);
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

// 중복정보 체크
$sql = " select mb_id from {$g5['member_table']} where mb_id <> '{$member['mb_id']}' and mb_dupinfo = '{$mb_dupinfo}' ";
$row = sql_fetch($sql);
if ($row['mb_id']) {
    alert_close("입력하신 본인확인 정보로 가입된 내역이 존재합니다.\\n회원아이디 : ".$row['mb_id']);
}

// hash 데이터
$cert_type = 'ipin';
$md5_cert_no = md5($req_num);
$hash_data   = md5($mb_name.$cert_type.$mb_birth.$md5_cert_no);

// 성인인증결과
$adult = $field[8] > 5 ? 1 : 0;

set_session('ss_cert_type',    $cert_type);
set_session('ss_cert_no',      $md5_cert_no);
set_session('ss_cert_hash',    $hash_data);
set_session('ss_cert_adult',   $adult);
set_session('ss_cert_birth',   $mb_birth);
set_session('ss_cert_sex',     ($field[9] == 1 ? 'M' : 'F'));
set_session('ss_cert_dupinfo', $mb_dupinfo);

$g5['title'] = 'KCB 아이핀 본인확인';
include_once(G5_PATH.'/head.sub.php');
?>

<script>
$(function() {
    var $opener = window.opener;

    $opener.$("input[name=cert_type]").val("<?php echo $cert_type; ?>");
    $opener.$("input[name=mb_name]").val("<?php echo $mb_name; ?>").attr("readonly", true);
    $opener.$("input[name=cert_no]").val("<?php echo $md5_cert_no; ?>");

    window.close();
});
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
<?php
include_once('./_common.php');

$sql = "select MAX(cr_id) as max_cr_id from {$g5['cert_history_table']} limit 1";
$res = sql_fetch($sql);
$max_cr_id = $res['max_cr_id'];
if(empty($max_cr_id)) $max_cr_id = 0;

if($config['cf_cert_use'] == 2) { // 실서비스 일때
    $mid = 'SRA'.$config['cf_cert_kg_mid']; // 부여받은 MID(상점ID) 입력(영업담당자 문의)
    $apiKey = $config['cf_cert_kg_cd'];   // 부여받은 MID 에 대한 apiKey
    $mTxId ='SIR_'.$max_cr_id;
    certify_count_check($member['mb_id'], 'simple'); // 금일 인증시도 횟수 체크
} else { // 테스트 일때
    $mid = "INIiasTest";
    $apiKey = "TGdxb2l3enJDWFRTbTgvREU3MGYwUT09";
    $mTxId ='test_'.$max_cr_id;
}
$reqSvcCd ='01';

// 등록가맹점 확인
$plainText1 = hash("sha256",(string)$mid.(string)$mTxId.(string)$apiKey);
$authHash = $plainText1;

//$flgFixedUser = (!empty($member['mb_id']) && !empty($member['mb_name']) && !empty($member['mb_hp']) && !empty($member['mb_birth']))?  'Y' : 'N';  // 특정사용자 고정시 : Y 세팅및 아래 해시 데이터 생성
$flgFixedUser = 'N'; // 특정사용자 구분하지 않기로 하여 수정

// php8버전 값체크 경고 때문에 필수값이 아닌 값이 없을수 있는 선택값들은 초기화해주어야함
$userName = '';
$userPhone = '';
$userBirth = '';
$userHash = '';	

if($flgFixedUser == 'Y') {
    $userName = $member['mb_name'];            // 사용자 이름
    $userPhone = preg_replace("/-/","" , $member['mb_hp']);   // 사용자 전화번호 하이픈만 제거
    $userBirth = $member['mb_birth'];           // 사용자 생년월일

    $plainText2 = hash("sha256",(string)$userName.(string)$mid.(string)$userPhone.(string)$mTxId.(string)$userBirth.(string)$reqSvcCd);
    $userHash = $plainText2; 
}

switch($_GET['pageType']) {		
    case "register":
        $resultPage = "/ini_result.php";
        break;
    case "find":
        $resultPage = "/ini_find_result.php";
        break;
    default:
        alert_close('잘못된 접근입니다.');
}

$resultUrl = G5_INICERT_URL . $resultPage;
$g5['title'] = 'KG이니시스 간편인증';
include_once(G5_PATH.'/head.sub.php'); 	
?>
    <form name="saForm">
        <input type="hidden" name="mid" value="<?php echo $mid ?>"> <!-- 필수 값 -->
        <input type="hidden" name="reqSvcCd" value="<?php echo $reqSvcCd ?>"> <!-- 필수 값 -->
        <input type="hidden" name="mTxId" value="<?php echo $mTxId ?>"> <!-- 필수 값 -->

        <input type="hidden" name="authHash" value="<?php echo $authHash ?>"> <!-- 필수 값 -->
        <input type="hidden" name="flgFixedUser" value="<?php echo $flgFixedUser ?>"> <!-- 필수 값 Y/N 특정사용자 인증 요청 여부 -->
        <input type="hidden" name="userName" value="<?php echo $userName ?>">
        <input type="hidden" name="userPhone" value="<?php echo $userPhone ?>">
        <input type="hidden" name="userBirth" value="<?php echo $userBirth ?>">
        <input type="hidden" name="userHash" value="<?php echo $userHash ?>">
        <input type="hidden" name="mbId" value="<?php echo $member['mb_id'] ?>">
        <input type="hidden" name="directAgency" value="<?php echo isset($_GET['directAgency']) ? clean_xss_tags($_GET['directAgency'], 1, 1) : ''; ?>">

        <input type="hidden" name="successUrl" value="<?php echo $resultUrl; ?>"> <!-- 필수 값 -->
        <input type="hidden" name="failUrl" value="<?php echo $resultUrl; ?>"> <!-- 필수 값 -->
        <!-- successUrl / failUrl 은 분리 하여 이용가능!-->
    </form> 
    <script>
            document.saForm.setAttribute("target", "_self");
            document.saForm.setAttribute("post", "post");
            document.saForm.setAttribute("action", "https://sa.inicis.com/auth");
            document.saForm.submit();
    </script>
<?php
include_once(G5_PATH.'/tail.sub.php');
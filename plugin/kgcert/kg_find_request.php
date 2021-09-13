<?php
	include_once('./_common.php');
	global $g5;

    $sql = "select MAX(cr_id) as max_cr_id from {$g5['cert_history_table']} limit 1";
    $res = sql_fetch($sql);
	$max_cr_id = $res['max_cr_id'];
	if(empty($max_cr_id)) $max_cr_id = 0;
	
	if($config['cf_cert_use'] == 2) { // 테스트 일때
		$mid = "INIiasTest";
		$apiKey = "TGdxb2l3enJDWFRTbTgvREU3MGYwUT09";
		$mTxId ='test_'.$max_cr_id;
	} else {
		$mid = 'SRA'.$config['cf_cert_kg_mid']; // 부여받은 MID(상점ID) 입력(영업담당자 문의)
		$apiKey = $config['cf_cert_kg_cd'];   // 부여받은 MID 에 대한 apiKey
		$mTxId ='SIR_'.$max_cr_id.$type;
		certify_count_check($member['mb_id'], 'sa'); // 금일 인증시도 횟수 체크
	}	
	$reqSvcCd ='01';

	// 등록가맹점 확인
	$plainText1 = hash("sha256",(string)$mid.(string)$mTxId.(string)$apiKey);
	$authHash = $plainText1;

	$flgFixedUser = (!empty($member['mb_id']) && !empty($member['mb_name']) && !empty($member['mb_hp']) && !empty($member['mb_birth']))?  'Y' : 'N';  // 특정사용자 고정시 : Y 세팅및 아래 해시 데이터 생성

	if($flgFixedUser == 'Y') {
		$userName = $member['mb_name'];            // 사용자 이름
		$userPhone = preg_replace("/-/","" , $member['mb_hp']);   // 사용자 전화번호 하이픈만 제거
		$userBirth = $member['mb_birth'];           // 사용자 생년월일

		$plainText2 = hash("sha256",(string)$userName.(string)$mid.(string)$userPhone.(string)$mTxId.(string)$userBirth.(string)$reqSvcCd);
		$userHash = $plainText2; 
	}

	$g5['title'] = 'KG이니시스 통합인증';
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
			<input type="hidden" name="directAgency" value="<?php echo $_POST['directAgency']; ?>">

			<input type="hidden" name="successUrl" value="<?php echo G5_KGCERT_URL; ?>/kg_find_result.php"> <!-- 필수 값 -->
			<input type="hidden" name="failUrl" value="<?php echo G5_KGCERT_URL; ?>/kg_find_result.php"> <!-- 필수 값 -->
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

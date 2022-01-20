<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
		<title>팝빌 SDK PHP 5.X Example.</title>
	</head>
<?php
    /**
    * 이미지가 첨부된 1건의 친구톡 전송을 팝빌에 접수합니다.
    * - 친구톡은 심야 전송(20:00~08:00)이 제한됩니다.
    * - 이미지 전송규격 / jpg 포맷, 용량 최대 500KByte, 이미지 높이/너비 비율 1.333 이하, 1/2 이상
    * - 전송실패시 사전에 지정한 변수 'altSendType' 값으로 대체문자를 전송할 수 있고, 이 경우 문자(SMS/LMS) 요금이 과금됩니다.
    * - https://docs.popbill.com/kakao/php/api#SendFMS
    */

    include 'common.php';

    // 팝빌 회원 사업자번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 팝빌회원 아이디
    $testUserID = 'testkorea';

    // 팝빌에 등록된 카카오톡채널 아이디, ListPlusFriend API - plusFriendID 확인
    $plusFriendID = '@팝빌';

    // 팝빌에 사전 등록된 발신번호
    $sender = '07043042991';

    // 친구톡 내용, 최대 400자
    $content = '친구톡 내용';

    // 대체문자 내용
    $altContent = '대체문자 내용';

    // 대체문자 유형, 공백-미전송, A-대체문자내용 전송, C-친구톡내용 전송
    $altSendType = 'A';

    // 광고전송여부
    $adsYN = True;

    // 전송요청번호
    // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
    // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회원별로 중복되지 않도록 할당.
    $requestNum = '';

    // 수신자 정보
    $receivers[] = array(
        // 수신번호
        'rcv' => '010111222',
        // 수신자명
        'rcvnm' => '수신자명'
    );

    // 버튼배열, 최대 5개
    $buttons[] = array(
        // 버튼 표시명
        'n' => '웹링크',
        // 버튼 유형, WL-웹링크, AL-앱링크, MD-메시지 전달, BK-봇키워드
        't' => 'WL',
        // [앱링크] iOS, [웹링크] Mobile
        'u1' => 'http://www.popbill.com',
        // [앱링크] Android, [웹링크] PC URL
        'u2' => 'http://www.popbill.com',
    );

    // 예약전송일시, yyyyMMddHHmmss
    $reserveDT = null;

    // 친구톡 이미지 전송규격
    // - 전송 포맷 : JPG 파일(.jpg, jpeg)
    // - 용량 제한 : 최대 500Byte
    // - 이미지 가로/세로 비율 : 1.5 미만 (가로 500px 이상)
    $files = array('./test0001.jpg');

    // 첨부 이미지 링크 URL
    $imageURL = 'http://popbill.com';

    try {
        $receiptNum = $KakaoService->SendFMS($testCorpNum, $plusFriendID, $sender,
            $content, $altContent, $altSendType, $adsYN, $receivers, $buttons, $reserveDT, $files, $imageURL, $testUserID, $requestNum);
    } catch(PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
	<body>
		<div id="content">
			<p class="heading1">Response</p>
			<br/>
			<fieldset class="fieldset1">
				<legend>친구톡(이미지) 1건 전송</legend>
				<ul>
					<?php
						if ( isset($receiptNum) ) {
					?>
							<li>receiptNum(접수번호) : <?php echo $receiptNum?></li>
					<?php
						} else {
					?>
							<li>Response.code : <?php echo $code ?> </li>
							<li>Response.message : <?php echo $message ?></li>
					<?php
						}
					?>
				</ul>
			</fieldset>
		 </div>
	</body>
</html>

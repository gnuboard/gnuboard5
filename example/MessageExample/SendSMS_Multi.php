<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 최대 90byte의 단문(SMS) 메시지 다수건 전송을 팝빌에 접수합니다. (최대 1,000건)
     * - 모든 수신자에게 동일한 내용을 전송하거나(동보전송), 수신자마다 개별 내용을 전송할 수 있습니다(대량전송).
     * - https://docs.popbill.com/message/php/api#SendSMS
     */

    include 'common.php';

    // 팝빌 회원 사업자번호, "-" 제외 10자리
    $testCorpNum = '1234567890';

    // 예약전송일시(yyyyMMddHHmmss) null인 경우 즉시전송
    $reserveDT = null;

    // 광고문자 전송여부
    $adsYN = false;

    // 전송요청번호
    // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
    // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회원별로 중복되지 않도록 할당.
    $requestNum = '';

    // 문자전송정보 최대 1000건까지 호출가능
    for ($i = 0; $i < 100; $i++ ) {
        $Messages[] = array(
            'snd' => '07043042991',		// 발신번호
            'sndnm' => '발신자명',			// 발신자명
            'rcv' => '010111222',			// 수신번호
            'rcvnm' => '수신자성명'.$i,	// 수신자성명
            'msg'	=> '개별 메시지 내용'	 // 개별 메시지 내용
        );
    }

    try {
        $receiptNum = $MessagingService->SendSMS($testCorpNum,'','', $Messages, $reserveDT, $adsYN, '', '', '', $requestNum);
    }
    catch (PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
    <body>
        <div id="content">
            <p class="heading1">Response</p>
            <br/>
            <fieldset class="fieldset1">
                <legend>단문문자 100건 전송</legend>
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

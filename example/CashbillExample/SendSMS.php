<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 현금영수증과 관련된 안내 SMS(단문) 문자를 재전송하는 함수로, 팝빌 사이트 [문자·팩스] > [문자] > [전송내역] 메뉴에서 전송결과를 확인 할 수 있습니다.
     * - 알림문자 전송시 포인트가 차감됩니다. (전송실패시 환불처리)
     * - https://docs.popbill.com/cashbill/php/api#SendSMS
     */

    include 'common.php';

    // 팝빌 회원 사업자번호, "-" 제외 10자리
    $testCorpNum = '1234567890';

    // 문서번호
    $mgtKey = '20210701-001';

    // 발신번호
    $sender = '07043042991';

    // 수신자번호
    $receiver = '010111222';

    // 메시지 내용, 90byte 초과시 길이가 조정되어 전송됨.
    $contents = '메시지 전송 내용입니다. 메세지의 길이가 90Byte를 초과하는 경우에는 메시지의 길이가 조정되어 전송되오니 참고하여 테스트하시기 바랍니다, 링크허브 문자 API 테스트 메시지 ';

    try {
        $result = $CashbillService->SendSMS($testCorpNum, $mgtKey, $sender, $receiver, $contents);
        $code = $result->code;
        $message = $result->message;
    }
    catch(PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
    <body>
        <div id="content">
            <p class="heading1">Response</p>
            <br/>
            <fieldset class="fieldset1">
                <legend>알림문자 전송</legend>
                <ul>
                    <li>Response.code : <?php echo $code ?></li>
                    <li>Response.message : <?php echo $message ?></li>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

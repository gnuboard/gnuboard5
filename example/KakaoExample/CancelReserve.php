<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 카카오톡 전송요청시 발급받은 접수번호(receiptNum)로 예약전송건을 취소합니다.
     * - 예약취소는 예약전송시간 10분전까지만 가능합니다.
     * - https://docs.popbill.com/kakao/php/api#CancelReserve
     */

    include 'common.php';

    // 팝빌 회원 사업자번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 전송 요청시 발급받은 카카오톡 접수번호
    $ReceiptNum = '021062710242700001';

    try {
        $result = $KakaoService->CancelReserve($testCorpNum ,$ReceiptNum);
        $code = $result->code;
        $message = $result->message;
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
                <legend>예약전송 취소 </legend>
                <ul>
                    <li>Response.code : <?php echo $code ?></li>
                    <li>Response.message : <?php echo $message ?></li>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

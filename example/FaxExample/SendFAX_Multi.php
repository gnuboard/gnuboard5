<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 동일한 팩스파일을 다수의 수신자에게 전송하기 위해 팝빌에 접수합니다. (최대 전송파일 개수 : 20개) (최대 1,000건)
     * - 팩스전송 문서 파일포맷 안내 : https://docs.popbill.com/fax/format?lang=php
     * - https://docs.popbill.com/fax/php/api#SendFAX
     */

    include 'common.php';

    // 팝빌 회원 사업자번호
    $testCorpNum = '1234567890';

    // 팝빌 회원 아이디
    $testUserID = 'testkorea';

    // 팩스전송 발신번호
    $Sender = '07043042991';

    // 팩스전송 발신자명
    $SenderName = '발신자명';

    // 팩스 수신정보 배열, 최대 1000건
    $Receivers[] = array(
        // 팩스 수신번호
        'rcv' => '070111222',
        // 팩스 수신자명
        'rcvnm' => '팝빌담당자'
    );

    $Receivers[] = array(
        // 팩스 수신번호
        'rcv' => '070333444',
        // 팩스 수신자명
        'rcvnm' => '수신담당자'
    );

    // 팩스전송파일, 해당파일에 읽기 권한이 설정되어 있어야 함. 최대 20개.
    $Files = array('./test.pdf');

    // 예약전송일시(yyyyMMddHHmmss) ex)20151212230000, null인경우 즉시전송
    $reserveDT = null;

    // 광고팩스 전송여부
    $adsYN = false;

    // 팩스 제목
    $title = '팩스 동보전송 제목';

    // 전송요청번호
    // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
    // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회원별로 중복되지 않도록 할당.
    $requestNum = '';

    try {
        $receiptNum = $FaxService->SendFAX($testCorpNum, $Sender, $Receivers, $Files,
            $reserveDT, $testUserID, $SenderName, $adsYN, $title, $requestNum);
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
                <legend>팩스 전송 - 대량</legend>
                <ul>
                    <?php
                        if ( isset($receiptNum) ) {
                    ?>
                            <li>receiptNum (팩스접수번호) : <?php echo $receiptNum ?></li>
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

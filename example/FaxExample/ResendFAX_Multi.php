<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 동일한 팩스파일을 다수의 수신자에게 전송하기 위해 팝빌에 접수합니다. (최대 전송파일 개수: 20개) (최대 1,000건)
    * - 발신/수신 정보 미입력시 기존과 동일한 정보로 팩스가 전송되고, 접수일 기준 최대 60일이 경과되지 않는 건만 재전송이 가능합니다.
     * - https://docs.popbill.com/fax/php/api#ResendFAX
     */

    include 'common.php';

    // 팝빌 회원 사업자번호
    $testCorpNum = '1234567890';

    // 팝빌 회원 아이디
    $testUserID = 'testkorea';

    // 팩스 접수번호
    $ReceiptNum = '018062617421600001';

    // 팩스전송 발신번호, 공백처리시 기존전송정보로 재전송
    $Sender = '07043042991';

    // 팩스전송 발신자명, 공백처리시 기존전송정보로 재전송
    $SenderName = '발신자명';

    // 팩스 수신정보, NULL로 처리하는 경우 기존전송정보로 재전송
    //$Receivers = NULL;


    // 팩스 수신정보가 기존전송정보와 다르게 동보전송하는 경우 아래의 코드 참조
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


    // 예약전송일시(yyyyMMddHHmmss) ex)20151212230000, null인경우 즉시전송
    $reserveDT = null;

    // 팩스 제목
    $title = '팩스 재전송 제목';

    // 재전송 팩스의 전송요청번호
    // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
    // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회원별로 중복되지 않도록 할당.
    // 재전송 팩스의 전송상태확인(GetSendDetailRN) / 예약전송취소(CancelReserveRN) 에 이용됩니다.
    $requestNum = '';

    try {
        $receiptNum = $FaxService->ResendFAX($testCorpNum, $ReceiptNum, $Sender,
            $SenderName, $Receivers, $reserveDT, $testUserID, $title, $requestNum);
    } catch (PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
<body>
<div id="content">
    <p class="heading1">Response</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>팩스 재전송</legend>
        <ul>
            <?php
            if (isset($receiptNum)) {
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

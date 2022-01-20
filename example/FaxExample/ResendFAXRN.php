<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 파트너가 할당한 전송요청 번호를 통해 팩스 1건을 재전송합니다.
     * - 발신/수신 정보 미입력시 기존과 동일한 정보로 팩스가 전송되고, 접수일 기준 최대 60일이 경과되지 않는 건만 재전송이 가능합니다.
     * - https://docs.popbill.com/fax/php/api#ResendFAXRN
     */

    include 'common.php';

    // 팝빌 회원 사업자번호
    $testCorpNum = '1234567890';

    // 팝빌 회원 아이디
    $testUserID = 'testkorea';

    // 팩스전송 발신번호, 공백처리시 기존전송정보로 재전송
    $Sender = '07043042991';

    // 팩스전송 발신자명, 공백처리시 기존전송정보로 재전송
    $SenderName = '발신자명';

    // 팩스 수신정보 배열, NULL로 처리하는 경우 기존전송정보로 재전송
    $Receivers = NULL;

    /*
    // 팩스 수신정보가 기존전송정보와 다를경우 아래의 코드 참조
      $Receivers[] = array(
      // 팩스 수신번호
          'rcv' => '070111222',

      // 수신자명
          'rcvnm' => '팝빌담당자'
      );
    */

    // 예약전송일시(yyyyMMddHHmmss) ex) 20151212230000, null인경우 즉시전송
    $reserveDT = null;

    // 팩스 제목
    $title = '팩스 재전송 제목';

    // 원본 팩스 전송시 할당한 전송요청번호(requestNum)
    $originalFAXrequestNum = '';

    // 재전송 팩스의 전송요청번호
    // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
    // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회원별로 중복되지 않도록 할당.
    // 재전송 팩스의 전송상태확인(GetSendDetailRN) / 예약전송취소(CancelReserveRN) 에 이용됩니다.
    $requestNum = '';

    try {
        $receiptNum = $FaxService->ResendFAXRN($testCorpNum, $requestNum, $Sender,
            $SenderName, $Receivers, $originalFAXrequestNum, $reserveDT, $testUserID, $title);
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
        <legend>팩스 재전송 요청</legend>
        <ul>
            <?php
            if (isset($receiptNum)) {
                ?>
                <li>receiptNum(팩스접수번호) : <?php echo $receiptNum ?></li>
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

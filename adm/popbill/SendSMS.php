<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Popbill Messaging Response</title>
  </head>
<?php

    include './common.php';

    // 팝빌 회원 사업자번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 예약전송일시(yyyyMMddHHmmss) ex) 20151212230000, null인 경우 즉시전송
    $reserveDT = null;

    // 광고문자 전송여부
    $adsYN = false;

    // 전송요청번호
    // 파트너가 전송 건에 대해 관리번호를 구성하여 관리하는 경우 사용.
    // 1~36자리로 구성. 영문, 숫자, 하이픈(-), 언더바(_)를 조합하여 팝빌 회원별로 중복되지 않도록 할당.
    $requestNum = '';

    $Messages[] = array(
        'snd' => '07043042991',  // 발신번호
        'sndnm' => '발신자명', // 발신자명
        'rcv' => '010111222',  // 수신번호
        'rcvnm' => '수신자성명',  // 수신자성명
        'msg'	=> '안녕하세요.'  // 개별 메시지 내용
    );

    try {
        $receiptNum = $MessagingService->SendSMS($testCorpNum, '', '', $Messages, $reserveDT, $adsYN, '', '', '', $requestNum);
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
      <legend>단문문자 1건 전송</legend>
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
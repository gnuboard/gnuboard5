<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 1건의 [임시저장] 현금영수증을 [발행]합니다.
     */

    include 'common.php';

    // 팝빌 회원 사업자번호, '-' 제외 10자리
    $testCorpNum = '1234567890';

    // 팝빌회원 아이디
    $testUserID = 'testkorea';

    // 문서번호
    $mgtKey = '20211215-TEST100';

    // 메모
    $memo = '현금영수증 발행메모';

    // 발행안내 메일제목
    // 공백처리시 기본양식으로 전송
    $emailSubject = '';

    try {
        $result = $CashbillService->Issue($testCorpNum, $mgtKey, $memo, $testUserID, $emailSubject);
        $code = $result->code;
        $message = $result->message;
        $confirmNum = $result->confirmNum;
        $tradeDate = $result->tradeDate;
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
                <legend>현금영수증 발행</legend>
                <ul>
                    <li>Response.code : <?php echo $code ?></li>
                    <li>Response.message : <?php echo $message ?></li>
                    <?php
                      if ( isset($confirmNum) ) {
                    ?>
                      <li>Response.confirmNum : <?php echo $confirmNum ?></li>
                      <li>Response.tradeDate : <?php echo $tradeDate ?></li>
                    <?php
                      }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

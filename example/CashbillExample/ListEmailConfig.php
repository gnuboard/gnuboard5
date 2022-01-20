<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 현금영수증 관련 메일 항목에 대한 발송설정을 확인합니다.
     * - https://docs.popbill.com/cashbill/php/api#ListEmailConfig
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    try {
        $result = $CashbillService->ListEmailConfig($testCorpNum);
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
                <legend>알림메일 전송목록 조회</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                            for( $i = 0; $i < Count($result); $i++ ){
                                if ($result[$i]->emailType == "CSH_ISSUE") {
                    ?>
                                    <li>CSH_ISSUE(고객에게 현금영수증이 발행 되었음을 알려주는 메일 전송 여부) : <?php echo $result[$i]->sendYN ? 'true' : 'false' ?></li>
              <?php
                                }
                                if ($result[$i]->emailType == "CSH_CANCEL") {
                        ?>
                                    <li>CSH_CANCEL(고객에게 현금영수증이 발행취소 되었음을 알려주는 메일 전송 여부) : <?php echo $result[$i]->sendYN ? 'true' : 'false' ?></li>
                        <?php
                                }
                            }
                        }
                        ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

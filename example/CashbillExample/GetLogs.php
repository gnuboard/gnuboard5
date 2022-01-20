<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 현금영수증의 상태에 대한 변경이력을 확인합니다.
     * - https://docs.popbill.com/cashbill/php/api#GetLogs
     */

    include 'common.php';

    // 팝빌회원, 사업자번호
    $testCorpNum = '1234567890';

    // 문서번호
    $mgtKey = '20210701-001';

    try {
        $result = $CashbillService->GetLogs($testCorpNum, $mgtKey);
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
                <legend>현금영수증 상태변경 이력</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                            for ($i = 0; $i < Count($result) ; $i++) {
                    ?>
                                <fieldset class ="fieldset2">
                                <legend>현금영수증 상태변경 이력 [<?php echo $i+1 ?>] </legend>
                                    <ul>
                                        <li>docLogType(로그타입) : <?php echo $result[$i]->docLogType ?></li>
                                        <li>log(이력정보) : <?php echo $result[$i]->log ?></li>
                                        <li>procType(처리형태) : <?php echo $result[$i]->procType ?></li>
                                        <li>procMemo(처리메모) : <?php echo $result[$i]->procMemo ?></li>
                                        <li>regDT(등록일시) : <?php echo $result[$i]->regDT ?></li>
                                        <li>ip(아이피) : <?php echo $result[$i]->ip ?></li>
                                    </ul>
                                </fieldset>
                    <?php
                            }
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

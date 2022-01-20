<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 현금영수증 인쇄(공급받는자) URL을 반환합니다.
     * - URL 보안정책에 따라 반환된 URL은 30초의 유효시간을 갖습니다.
     */

    include 'common.php';

    // 팝빌 회원 사업자 번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 문서번호
    $mgtKey = '20210101-001';

    try {
        $url = $CashbillService->GetEPrintURL($testCorpNum, $mgtKey);
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
                <legend>현금영수증 인쇄 URL - 공급받는자 </legend>
                <ul>
                    <?php
                        if ( isset($url) ) {
                    ?>
                        <li>url : <?php echo $url ?></li>
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

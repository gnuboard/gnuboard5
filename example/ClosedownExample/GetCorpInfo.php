<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 연동회원의 회사정보를 확인합니다.
     * - https://docs.popbill.com/closedown/php/api#GetCorpInfo
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    try {
        $result = $ClosedownService->GetCorpInfo($testCorpNum);
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
                <legend>회사정보 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                        <li>ceoname(대표자 성명) : <?php echo $result->ceoname ?></li>
                        <li>corpName(상호) : <?php echo $result->corpName ?></li>
                        <li>addr(주소) : <?php echo $result->addr ?></li>
                        <li>bizType(업태) : <?php echo $result->bizType ?></li>
                        <li>bizClass(종목) : <?php echo $result->bizClass ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

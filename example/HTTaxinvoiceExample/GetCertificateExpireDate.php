<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 홈택스연동 인증을 위해 팝빌에 등록된 인증서 만료일자를 확인합니다.
     * - https://docs.popbill.com/httaxinvoice/php/api#GetCertificateExpireDate
     */

    include 'common.php';

    // 팝빌 회원 사업자 번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    try {
        $ExpireDate = $HTTaxinvoiceService->GetCertificateExpireDate($testCorpNum);
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
                <legend>홈택스연동 공인인증서 만료일시 확인</legend>
                <ul>
                    <?php
                        if ( isset ( $ExpireDate ) ) {
                    ?>
                            <li>ExpireDate(만료일시) : <?php echo $ExpireDate ?></li>
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

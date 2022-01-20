<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /*
     * 계좌조회 정액제 서비스 신청 페이지의 팝업 URL을 반환합니다.
     * - 반환되는 URL은 보안정책상 30초의 유효시간을 갖으며, 이 시간 초과후에는 URL을 사용해도 정상적인 페이지에 접근할 수 없습니다.
     * - https://docs.popbill.com/easyfinbank/php/api#GetFlatRatePopUpURL
     */

    include 'common.php';

    // 팝빌 회원 사업자 번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    try {
        $url = $EasyFinBankService->GetFlatRatePopUpURL($testCorpNum);
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
                <legend>정액제 서비스 신청 URL</legend>
                <ul>
                    <?php
                        if ( isset ( $url ) ) {
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

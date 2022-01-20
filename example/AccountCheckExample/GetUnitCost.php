<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 예금주 성명 조회시 과금되는 포인트 단가를 확인합니다.
     * - https://docs.popbill.com/accountcheck/php/api#GetUnitCost
     */

    include 'common.php';

    // 팝빌회원 사업자번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 팝빌회원 아이디
    $testUserID = 'testkorea';

    // 서비스 유형, 성명 / 실명 중 택 1
    $serviceType = "성명";

    try {
        $unitCost = $AccountCheckService->GetUnitCost($testCorpNum, $serviceType, $testUserID);
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
                <legend>예금주조회 단가확인</legend>
                <ul>
                    <?php
                        if ( isset($unitCost) ) {
                    ?>
                        <li>unitCost : <?php echo $unitCost ?></li>
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

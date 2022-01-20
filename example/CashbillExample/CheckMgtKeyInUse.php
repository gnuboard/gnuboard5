<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 파트너가 현금영수증 관리 목적으로 할당하는 문서번호 사용여부를 확인합니다.
     * - https://docs.popbill.com/cashbill/php/api#CheckMgtKeyInUse
     */

    include 'common.php';

    // 팝빌회원 사업자번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 문서번호, 최대 24자리, 영문, 숫자 '-', '_'를 조합하여 사업자별로 중복되지 않도록 구성
    $mgtKey = '20210701-001';

    try {
        $result = $CashbillService->CheckMgtKeyInUse($testCorpNum, $mgtKey);
        $result ? $result = '사용중' : $result = '미사용중';
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
                <legend>문서번호 사용여부 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>

                    <?php
                        } else {
                    ?>
                        <li>연동문서번호 사용여부 : <?php echo $result ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

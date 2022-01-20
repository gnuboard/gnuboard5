<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 전자명세서의 1건의 상태 및 요약정보 확인합니다.
     * - https://docs.popbill.com/statement/php/api#GetInfo
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-' 제외 10자리
    $testCorpNum = '1234567890';

    // 명세서 코드 - 121(거래명세서), 122(청구서), 123(견적서) 124(발주서), 125(입금표), 126(영수증)
    $itemCode = '121';

    // 문서번호
    $mgtKey = '20210704-001';

    try {
        $result = $StatementService->GetInfo($testCorpNum, $itemCode, $mgtKey);
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
                <legend>전자명세서 요약 및 상태정보 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                            <li> itemKey(아이템키) : <?php echo $result->itemKey ?></li>
                            <li> itemCode(명세서 코드) : <?php echo $result->itemCode ?></li>
                            <li> stateCode(상태코드) : <?php echo $result->stateCode ?></li>
                            <li> taxType(세금형태) : <?php echo $result->taxType ?></li>
                            <li> purposeType(영수/청구) : <?php echo $result->purposeType ?></li>
                            <li> writeDate(작성일자) : <?php echo $result->writeDate ?></li>
                            <li> senderCorpName(발신자 상호) : <?php echo $result->senderCorpName ?></li>
                            <li> senderCorpNum(발신자 사업자번호) : <?php echo $result->senderCorpNum ?></li>
                            <li> senderPrintYN(발신자 인쇄여부) : <?php echo $result->senderPrintYN ?></li>
                            <li> receiverCorpName(수신자 상호) : <?php echo $result->receiverCorpName ?></li>
                            <li> receiverCorpNum(수신자 사업자번호) : <?php echo $result->receiverCorpNum ?></li>
                            <li> receiverPrintYN(수신자 인쇄여부) : <?php echo $result->receiverPrintYN ?></li>
                            <li> supplyCostTotal(공급가액 합계) : <?php echo $result->supplyCostTotal ?></li>
                            <li> taxTotal(세액 합계) : <?php echo $result->taxTotal ?></li>
                            <li> issueDT(발행일시) : <?php echo $result->issueDT ?></li>
                            <li> stateDT(상태 변경일시) : <?php echo $result->stateDT ?></li>
                            <li> openYN(메일 개봉 여부) : <?php echo $result->openYN ?></li>
                            <li> openDT(개봉 일시) : <?php echo $result->openDT ?></li>
                            <li> stateMemo(상태메모) : <?php echo $result->stateMemo ?></li>
                            <li> regDT(등록일시) : <?php echo $result->regDT ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

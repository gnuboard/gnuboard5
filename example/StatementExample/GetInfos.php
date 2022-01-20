<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 다수건의 전자명세서 상태 및 요약정보 확인합니다. (1회 호출 시 최대 1,000건 확인 가능)
     * - https://docs.popbill.com/statement/php/api#GetInfos
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 명세서 코드 - 121(거래명세서), 122(청구서), 123(견적서) 124(발주서), 125(입금표), 126(영수증)
    $itemCode = '121';

    // 조회할 전자명세서 문서번호 배열, 최대 1000건
    $MgtKeyList = array(
        '20210703-001',
        '20210703-002',
        '20210703-003'
    );

    try {
        $result = $StatementService->GetInfos($testCorpNum, $itemCode, $MgtKeyList);
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
                <legend>전자명세서 상태/요약정보 확인 - 대량</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                            for ($i = 0; $i < Count($result); $i++) {
                    ?>
                            <fieldset class="fieldset2">
                                <legend> 전자명세서 상태/요약정보[<?php echo $i+1?>]</legend>
                                <ul>
                                    <li> itemKey(아이템키) : <?php echo $result[$i]->itemKey ?></li>
                                    <li> itemCode(명세서 코드) : <?php echo $result[$i]->itemCode ?></li>
                                    <li> stateCode(상태코드) : <?php echo $result[$i]->stateCode ?></li>
                                    <li> taxType(세금형태) : <?php echo $result[$i]->taxType ?></li>
                                    <li> purposeType(영수/청구) : <?php echo $result[$i]->purposeType ?></li>
                                    <li> writeDate(작성일자) : <?php echo $result[$i]->writeDate ?></li>
                                    <li> senderCorpName(발신자 상호) : <?php echo $result[$i]->senderCorpName ?></li>
                                    <li> senderCorpNum(발신자 사업자번호) : <?php echo $result[$i]->senderCorpNum ?></li>
                                    <li> senderPrintYN(발신자 인쇄여부) : <?php echo $result[$i]->senderPrintYN ?></li>
                                    <li> receiverCorpName(수신자 상호) : <?php echo $result[$i]->receiverCorpName ?></li>
                                    <li> receiverCorpNum(수신자 사업자번호) : <?php echo $result[$i]->receiverCorpNum ?></li>
                                    <li> receiverPrintYN(수신자 인쇄여부) : <?php echo $result[$i]->receiverPrintYN ?></li>
                                    <li> supplyCostTotal(공급가액 합계) : <?php echo $result[$i]->supplyCostTotal ?></li>
                                    <li> taxTotal(세액 합계) : <?php echo $result[$i]->taxTotal ?></li>
                                    <li> issueDT(발행일시) : <?php echo $result[$i]->issueDT ?></li>
                                    <li> stateDT(상태 변경일시) : <?php echo $result[$i]->stateDT ?></li>
                                    <li> openYN(메일 개봉 여부) : <?php echo $result[$i]->openYN ?></li>
                                    <li> openDT(개봉 일시) : <?php echo $result[$i]->openDT ?></li>
                                    <li> stateMemo(상태메모) : <?php echo $result[$i]->stateMemo ?></li>
                                    <li> regDT(등록일시) : <?php echo $result[$i]->regDT ?></li>
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

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 현금영수증 1건의 상태 및 요약정보를 확인합니다.
     * - https://docs.popbill.com/cashbill/php/api#GetInfo
     */

    include 'common.php';

    // 팝빌회원 사업자번호
    $testCorpNum = '1234567890';

    // 문서번호
    $mgtKey = '20210701-001';

    try {
        $result = $CashbillService->GetInfo($testCorpNum, $mgtKey);
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
                <legend>현금영수증 요약/상태정보 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                            {
                    ?>
                                <li> itemKey (팝빌번호) : <?php echo $result->itemKey ?></li>
                                <li> mgtKey (문서번호) : <?php echo $result->mgtKey ?></li>
                                <li> tradeDate (거래일자) : <?php echo $result->tradeDate ?></li>
                                <li> tradeType (문서형태) : <?php echo $result->tradeType ?></li>
                                <li> tradeUsage (거래구분) : <?php echo $result->tradeUsage ?></li>
                                <li> tradeOpt (거래유형) : <?php echo $result->tradeOpt ?></li>
                                <li> taxationType (과세형태) : <?php echo $result->taxationType ?></li>
                                <li> totalAmount (거래금액) : <?php echo $result->totalAmount ?></li>
                                <li> issueDT (발행일시) : <?php echo $result->issueDT ?></li>
                                <li> regDT (등록일시) : <?php echo $result->regDT ?></li>
                                <li> stateMemo (상태메모) : <?php echo $result->stateMemo ?></li>
                                <li> stateCode (상태코드) : <?php echo $result->stateCode ?></li>
                                <li> stateDT (상태변경일시) : <?php echo $result->stateDT ?></li>
                                <li> identityNum (식별번호) : <?php echo $result->identityNum ?></li>
                                <li> itemName (주문상품명) : <?php echo $result->itemName ?></li>
                                <li> customerName (주문자)명) : <?php echo $result->customerName ?></li>
                                <li> confirmNum (국세청승인번호) : <?php echo $result->confirmNum ?></li>
                                <li> orgConfirmNum (원본 현금영수증 국세청승인번호) : <?php echo $result->orgConfirmNum ?></li>
                                <li> orgTradeDate (원본 현금영수증 거래일자) : <?php echo $result->orgTradeDate ?></li>
                                <li> ntssendDT (국세청 전송일시) : <?php echo $result->ntssendDT ?></li>
                                <li> ntsresultDT (국세청 처리결과 수신일시) : <?php echo $result->ntsresultDT ?></li>
                                <li> ntsresultCode (국세청 처리결과 상태코드) : <?php echo $result->ntsresultCode ?></li>
                                <li> ntsresultMessage (국세청 처리결과 메시지) : <?php echo $result->ntsresultMessage ?></li>
                                <li> printYN (인쇄여부) : <?php echo $result->printYN ?></li>
                    <?php
                            }
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

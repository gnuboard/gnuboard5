<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 세금계산서 1건의 상태 및 요약정보를 확인합니다.
     * - https://docs.popbill.com/taxinvoice/php/api#GetInfo
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 발행유형, ENumMgtKeyType::SELL:매출, ENumMgtKeyType::BUY:매입, ENumMgtKeyType::TRUSTEE:위수탁
    $mgtKeyType = ENumMgtKeyType::SELL;

    // 조회할 세금계산서 문서번호
    $mgtKey = '20210701-001';

    try {
        $result = $TaxinvoiceService->GetInfo($testCorpNum, $mgtKeyType, $mgtKey);
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
                <legend>세금계산서 상태 및 요약 정보 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                            <li>itemKey (팝빌번호) : <?php echo $result->itemKey ?></li>
                            <li>stateCode (상태코드) : <?php echo $result->stateCode ?></li>
                            <li>taxType (과세형태) : <?php echo $result->taxType ?></li>
                            <li>purposeType (영수/청구) : <?php echo $result->purposeType ?></li>
                            <li>modifyCode (수정 사유코드) : <?php echo $result->modifyCode ?></li>
                            <li>issueType (발행형태) : <?php echo $result->issueType ?></li>
                            <li>lateIssueYN (지연발행 여부) : <?php echo $result->lateIssueYN ? 'true' : 'false' ?></li>
                            <li>interOPYN (연동문서 여부) : <?php echo $result->interOPYN ? 'true' : 'false' ?></li>
                            <li>writeDate (작성일자) : <?php echo $result->writeDate ?></li>
                            <li>invoicerCorpName (공급자 상호) : <?php echo $result->invoicerCorpName ?></li>
                            <li>invoicerCorpNum (공급자 사업자번호) : <?php echo $result->invoicerCorpNum ?></li>
                            <li>invoicerMgtKey (공급자 문서번호) : <?php echo $result->invoicerMgtKey ?></li>
                            <li>invoicerPrintYN (공급자 인쇄여부) : <?php echo $result->invoicerPrintYN ? 'true' : 'false' ?></li>
                            <li>invoiceeCorpName (공급받는자 상호) : <?php echo $result->invoiceeCorpName ?></li>
                            <li>invoiceeCorpNum (공급받는자 사업자번호) : <?php echo $result->invoiceeCorpNum ?></li>
                            <li>invoiceeMgtKey (공급받는자 문서번호) : <?php echo $result->invoiceeMgtKey ?></li>
                            <li>invoiceePrintYN (공급받는자 인쇄여부) : <?php echo $result->invoiceePrintYN ? 'true' : 'false' ?></li>
                            <li>closeDownState (공급받는자 휴폐업상태) : <?php echo $result->closeDownState ?></li>
                            <li>closeDownStateDate (공급받는자 휴폐업일자) : <?php echo $result->closeDownStateDate ?></li>
                            <li>supplyCostTotal (공급가액 합계): <?php echo $result->supplyCostTotal ?></li>
                            <li>taxTotal (세액 합계) : <?php echo $result->taxTotal ?></li>
                            <li>issueDT (발행일시) : <?php echo $result->issueDT ?></li>
                            <li>stateDT (상태변경일시) : <?php echo $result->stateDT ?></li>
                            <li>openYN (개봉 여부) : <?php echo $result->openYN ? 'true' : 'false' ?></li>
                            <li>openDT (개봉 일시) : <?php echo $result->openDT ?></li>
                            <li>ntsresult (국세청 전송결과) : <?php echo $result->ntsresult ?></li>
                            <li>ntsconfirmNum (국세청승인번호) : <?php echo $result->ntsconfirmNum ?></li>
                            <li>ntssendDT (국세청 전송일시) : <?php echo $result->ntssendDT ?></li>
                            <li>ntsresultDT (국세청 결과 수신일시) : <?php echo $result->ntsresultDT ?></li>
                            <li>ntssendErrCode (전송실패 사유코드) : <?php echo $result->ntssendErrCode ?></li>
                            <li>stateMemo (상태메모) : <?php echo $result->stateMemo ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

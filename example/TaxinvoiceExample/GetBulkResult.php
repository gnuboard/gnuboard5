<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
    <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 접수시 기재한 SubmitID를 사용하여 세금계산서 접수결과를 확인합니다.
     * - https://docs.popbill.com/taxinvoice/php/api#GetBulkResult
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 초대량 발행 접수시 기재한 제출아이디
    $submitID = 'PHPTEST021';
    
    // 팝빌회원 아이디
    $testUserID = 'testkorea';

    try {
        $result = $TaxinvoiceService->GetBulkResult($testCorpNum, $submitID, $testUserID);
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
                <legend>초대량 접수결과 확인</legend>
                <ul>
                    <?php
                    if ( isset($code) ) {
                    ?>
                    <li>Response.code : <?php echo $code ?> </li>
                    <li>Response.message : <?php echo $message ?></li>
                    <?php
                    } else {
                    ?>
                        <li>code (응답코드) : <?php echo $result->code ?> </li>
                        <li>message (응답메시지) : <?php echo $result->message ?> </li>
                        <li>submitID (제출아이디) : <?php echo $result->submitID ?> </li>
                        <li>submitCount (세금계산서 접수 건수) : <?php echo $result->submitCount ?> </li>
                        <li>successCount (세금계산서 발행 성공 건수) : <?php echo $result->successCount ?> </li>
                        <li>failCount (세금계산서 발행 실패 건수) : <?php echo $result->failCount ?> </li>
                        <li>txState (접수상태코드) : <?php echo $result->txState ?> </li>
                        <li>txResultCode (접수 결과코드) : <?php echo $result->txResultCode ?> </li>
                        <li>txStartDT (발행처리 시작일시) : <?php echo $result->txStartDT ?> </li>
                        <li>txEndDT (발행처리 완료일시) : <?php echo $result->txEndDT ?> </li>
                        <li>receiptDT(접수일시) : <?php echo $result->receiptDT ?> </li>
                        <li>receiptID(접수아이디) : <?php echo $result->receiptID ?> </li>
                        <?php
                        for ( $i = 0; $i < Count($result->issueResult); $i++ ) {
                        ?>
                        <fieldset class="fieldset2">
                            <legend> issueResult(발행 결과) [<?php echo $i+1?>]</legend>
                            <ul>
                                <li>invoicerMgtKey (공급자 문서번호) : <?php if(isset($result->issueResult[$i]->invoicerMgtKey)) echo $result->issueResult[$i]->invoicerMgtKey ?></li>
                                <li>trusteeMgtKey (수탁자 문서번호) : <?php if(isset($result->issueResult[$i]->trusteeMgtKey)) echo $result->issueResult[$i]->trusteeMgtKey ?></li>
                                <li>code (응답코드) : <?php echo $result->issueResult[$i]->code ?></li>
                                <li>ntsconfirmNum (국세청승인번호) : <?php echo $result->issueResult[$i]->ntsconfirmNum ?></li>
                                <li>issueDT (발행일시) : <?php echo $result->issueResult[$i]->issueDT ?></li>
                            </ul>
                        </fieldset>
                        <?php
                        }
                    }?>
                </ul>
            </fieldset>
        </div>
    </body>
</html>

<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 국세청 승인번호를 통해 수집한 전자세금계산서 1건의 상세정보를 반환합니다.
     * - https://docs.popbill.com/httaxinvoice/php/api#GetTaxinvoice
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    //국세청 승인번호
    $NTSConfirmNum = '202112264100020300002e07';

    try {
        $result = $HTTaxinvoiceService->GetTaxinvoice($testCorpNum, $NTSConfirmNum);
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
                <legend>세금계산서 상세정보 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                    <li>ntsconfirmNum (국세청승인번호) : <?php echo $result->ntsconfirmNum ?></li>
                    <li>writeDate (작성일자) : <?php echo $result->writeDate ?></li>
                    <li>issueDT (발행일시) : <?php echo $result->issueDT ?></li>
                    <li>invoiceType (전자세금계산서 종류) : <?php echo $result->invoiceType ?></li>
                    <li>taxType (과세형태) : <?php echo $result->taxType ?></li>
                    <li>taxTotal (세액 합계) : <?php echo $result->taxTotal ?></li>
                    <li>supplyCostTotal (공급가액 합계) : <?php echo $result->supplyCostTotal ?></li>
                    <li>totalAmount (합계금액) : <?php echo $result->totalAmount ?></li>
                    <li>purposeType (영수/청구) : <?php echo $result->purposeType ?></li>
                    <li>serialNum (일련번호) : <?php echo $result->serialNum ?></li>
                    <li>cash (현금) : <?php echo $result->cash ?></li>
                    <li>chkBill (수표) : <?php echo $result->chkBill ?></li>
                    <li>credit (외상) : <?php echo $result->credit ?></li>
                    <li>note (어음) : <?php echo $result->note ?></li>
                    <li>remark1 (비고1) : <?php echo $result->remark1 ?></li>
                    <li>remark2 (비고2) : <?php echo $result->remark2 ?></li>
                    <li>remark3 (비고3) : <?php echo $result->remark3 ?></li>
                    <li>modifyCode (수정 사유코드) : <?php echo $result->modifyCode ?></li>
                    <li>orgNTSConfirmNum (원본 전자세금계산서 국세청 승인번호) : <?php echo $result->orgNTSConfirmNum ?></li>
                    <li>invoicerCorpNum (공급자 사업자번호) : <?php echo $result->invoicerCorpNum ?></li>
                    <li>invoicerTaxRegID (공급자 종사업장번호) : <?php echo $result->invoicerTaxRegID ?></li>
                    <li>invoicerCorpName (공급자 상호) : <?php echo $result->invoicerCorpName ?></li>
                    <li>invoicerCEOName (공급자 대표자성명) : <?php echo $result->invoicerCEOName ?></li>
                    <li>invoicerAddr (공급자 주소) : <?php echo $result->invoicerAddr ?></li>
                    <li>invoicerBizType (공급자 업태) : <?php echo $result->invoicerBizType ?></li>
                    <li>invoicerBizClass (공급자 종목) : <?php echo $result->invoicerBizClass ?></li>
                    <li>invoicerContactName (공급자 담당자 성명) : <?php echo $result->invoicerContactName ?></li>
                    <li>invoicerTEL (공급자 담당자 연락처) : <?php echo $result->invoicerTEL ?></li>
                    <li>invoicerEmail (공급자 담당자 이메일) : <?php echo $result->invoicerEmail ?></li>
                    <li>invoiceeCorpNum (공급받는자 사업자번호) : <?php echo $result->invoiceeCorpNum ?></li>
                    <li>invoiceeType (공급받는자 구분) : <?php echo $result->invoiceeCorpNum ?></li>
                    <li>invoiceeTaxRegID (공급받는자 종사업장번호) : <?php echo $result->invoiceeCorpNum ?></li>
                    <li>invoiceeCorpName (공급받는자 상호) : <?php echo $result->invoiceeCorpNum ?></li>
                    <li>invoiceeCEOName (공급받는자 대표자 성명) : <?php echo $result->invoiceeCEOName ?></li>
                    <li>invoiceeAddr (공급받는자 주소) : <?php echo $result->invoiceeAddr ?></li>
                    <li>invoiceeBizType (공급받는자 업태) : <?php echo $result->invoiceeBizType ?></li>
                    <li>invoiceeBizClass (공급받는자 종목) : <?php echo $result->invoiceeBizClass ?></li>
                    <li>invoiceeContactName1 (공급받는자 담당자 성명) : <?php echo $result->invoiceeContactName1 ?></li>
                    <li>invoiceeTEL1 (공급받는자 담당자 연락처) : <?php echo $result->invoiceeTEL1 ?></li>
                    <li>invoiceeEmail1 (공급받는자 담당자 이메일) : <?php echo $result->invoiceeEmail1 ?></li>
                    <li>trusteeCorpNum (수탁자 사업자번호) : <?php echo $result->trusteeCorpNum ?></li>
                    <li>trusteeTaxRegID (수탁자 종사업장번호) : <?php echo $result->trusteeTaxRegID ?></li>
                    <li>trusteeCorpName (수탁자 상호) : <?php echo $result->trusteeCorpName ?></li>
                    <li>trusteeCEOName (수탁자 대표자성명) : <?php echo $result->trusteeCEOName ?></li>
                    <li>trusteeAddr (수탁자 주소) : <?php echo $result->trusteeAddr ?></li>
                    <li>trusteeBizType (수탁자 업태) : <?php echo $result->trusteeBizType ?></li>
                    <li>trusteeBizClass (수탁자 종목) : <?php echo $result->trusteeBizClass ?></li>
                    <li>trusteeContactName (수탁자 담당자 성명) : <?php echo $result->trusteeContactName ?></li>
                    <li>trusteeTEL (수탁자 담당자 연락처) : <?php echo $result->trusteeTEL ?></li>
                    <li>trusteeEmail (수탁자 담당자 이메일) : <?php echo $result->trusteeEmail ?></li>
            <?php
            for ( $i = 0; $i < Count($result->detailList); $i++ ){
                ?>
                    <fieldset class="fieldset2">
                        <legend> detailList[<?php echo $i+1 ?>] </legend>
                        <ul>
                            <li> serialNum (일련번호) : <?php echo $result->detailList[$i]->serialNum ?> </li>
                            <li> purchaseDT (거래일자) : <?php echo $result->detailList[$i]->purchaseDT ?> </li>
                            <li> itemName (품명) : <?php echo $result->detailList[$i]->itemName ?> </li>
                            <li> spec (규격) : <?php echo $result->detailList[$i]->spec ?> </li>
                            <li> qty (수량) : <?php echo $result->detailList[$i]->qty ?> </li>
                            <li> unitCost (단가) : <?php echo $result->detailList[$i]->unitCost ?> </li>
                            <li> supplyCost (공급가액) : <?php echo $result->detailList[$i]->supplyCost ?> </li>
                            <li> tax (세액) : <?php echo $result->detailList[$i]->tax ?> </li>
                            <li> remark (비고) : <?php echo $result->detailList[$i]->remark ?> </li>
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

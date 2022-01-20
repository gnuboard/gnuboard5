<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 전자명세서 1건의 상세정보 확인합니다.
     * - https://docs.popbill.com/statement/php/api#GetDetailInfo
     */

    include 'common.php';

    // 팝빌회원 사업자번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 명세서 코드 - 121(거래명세서), 122(청구서), 123(견적서) 124(발주서), 125(입금표), 126(영수증)
    $itemCode = '121';

    // 문서번호
    $mgtKey = '20210702-32';

    try {
        $result = $StatementService->GetDetailInfo($testCorpNum, $itemCode, $mgtKey);
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
                <legend>전자명세서 상세정보 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                        <li> itemCode(명세서 코드) : <?php echo $result->itemCode ?> </li>
                        <li> mgtKey(문서번호) : <?php echo $result->mgtKey ?> </li>
                        <li> invoiceNum(팝빌 승인번호) : <?php echo $result->invoiceNum ?> </li>
                        <li> formCode(맞춤양식 코드) : <?php echo $result->formCode ?> </li>
                        <li> writeDate(작성일자) : <?php echo $result->writeDate ?> </li>
                        <li> taxType(세금형태) : <?php echo $result->taxType  ?> </li>
                        <li> senderCorpNum(발신자 사업자번호) : <?php echo $result->senderCorpNum ?> </li>
                        <li> senderTaxRegID(발신자 종사업장번호) : <?php echo $result->senderTaxRegID ?> </li>
                        <li> senderCorpName(발신자 상호) : <?php echo $result->senderCEOName ?> </li>
                        <li> senderCEOName(발신자 대표자성명) : <?php echo $result->senderCEOName ?> </li>
                        <li> senderAddr(발신자 주소) : <?php echo $result->senderAddr ?> </li>
                        <li> senderBizClass(발신자 종목) : <?php echo $result->senderBizClass ?> </li>
                        <li> senderBizType(발신자 업태) : <?php echo $result->senderBizType ?> </li>
                        <li> senderContactName(발신자 담당자명) : <?php echo $result->senderContactName ?> </li>
                        <li> senderTEL(발신자 연락처) : <?php echo $result->senderTEL ?> </li>
                        <li> senderHP(발신자 휴대폰번호) : <?php echo $result->senderHP ?> </li>
                        <li> senderEmail(발신자 메일주소) : <?php echo $result->senderEmail ?> </li>
                        <li> receiverCorpNum(수신자 사업자번호) : <?php echo $result->receiverCorpNum ?> </li>
                        <li> receiverTaxRegID(수신자 종사업장번호) : <?php echo $result->receiverTaxRegID ?> </li>
                        <li> receiverCorpName(수신자 상호) : <?php echo $result->receiverCorpName ?> </li>
                        <li> receiverCEOName(수신자 대표자성명) : <?php echo $result->receiverCEOName ?> </li>
                        <li> receiverAddr(수신자 주소) : <?php echo $result->receiverAddr ?> </li>
                        <li> receiverBizClass(수신자 종목) : <?php echo $result->receiverBizClass ?> </li>
                        <li> receiverBizType(수신자 업태) : <?php echo $result->receiverBizType ?> </li>
                        <li> receiverContactName(수신자 담당자명) : <?php echo $result->receiverContactName ?> </li>
                        <li> receiverTEL(수신자 연락처) : <?php echo $result->receiverTEL ?> </li>
                        <li> receiverHP(수신자 휴대폰번호) : <?php echo $result->receiverHP ?> </li>
                        <li> receiverEmail(수신자 메일주소) : <?php echo $result->receiverEmail ?> </li>
                        <li> totalAmount(합계금액) : <?php echo $result->totalAmount ?> </li>
                        <li> supplyCostTotal(공급가액 합계) : <?php echo $result->supplyCostTotal ?> </li>
                        <li> taxTotal(세액 합계) : <?php echo $result->taxTotal ?> </li>
                        <li> purposeType(영수/청구) : <?php echo $result->purposeType ?> </li>
                        <li> serialNum(기재상 일련번호) : <?php echo $result->serialNum ?> </li>
                        <li> remark1(비고1) : <?php echo $result->remark1 ?> </li>
                        <li> remark2(비고2) : <?php echo $result->remark2 ?> </li>
                        <li> remark3(비고3) : <?php echo $result->remark3 ?> </li>
                        <li> businessLicenseYN(사업자등록증 첨부여부) : <?php echo $result->businessLicenseYN ?> </li>
                        <li> bankBookYN(통장사본 첨부여부) : <?php echo $result->bankBookYN ?> </li>
                        <li> smssendYN(알림문자 전송여부) : <?php echo $result->smssendYN ?> </li>
                        <li> autoacceptYN(발행시 자동승인 여부) : <?php echo $result->autoacceptYN ?> </li>
                    <?php
                            if ( !is_null($result->detailList) ) {
                                for ( $i = 0; $i < Count($result->detailList); $i++){
                    ?>
                                <fieldset class="fieldset2">
                                    <legend>detailList <?php echo $i+1 ?></legend>
                                        <ul>
                                            <li> serialNum(일련번호) : <?php echo $result->detailList[$i]->serialNum ?> </li>
                                            <li> purchaseDT(거래일자) : <?php echo $result->detailList[$i]->purchaseDT ?> </li>
                                            <li> itemName(품목명) : <?php echo $result->detailList[$i]->itemName ?> </li>
                                            <li> spec(규격) : <?php echo $result->detailList[$i]->spec ?> </li>
                                            <li> qty(수량) : <?php echo $result->detailList[$i]->qty ?> </li>
                                            <li> unitCost(단가) : <?php echo $result->detailList[$i]->unitCost ?> </li>
                                            <li> supplyCost(공급가액) : <?php echo $result->detailList[$i]->supplyCost ?> </li>
                                            <li> tax(세액) : <?php echo $result->detailList[$i]->tax ?> </li>
                                            <li> remark(비고) : <?php echo $result->detailList[$i]->remark ?> </li>
                                            <li> spare1(여분1) : <?php echo $result->detailList[$i]->spare1 ?> </li>
                                            <li> spare2(여분2) : <?php echo $result->detailList[$i]->spare2 ?> </li>
                                            <li> spare3(여분3) : <?php echo $result->detailList[$i]->spare3 ?> </li>
                                            <li> spare4(여분4) : <?php echo $result->detailList[$i]->spare4 ?> </li>
                                            <li> spare5(여분5) : <?php echo $result->detailList[$i]->spare5 ?> </li>
                                            <li> spare6(여분6) : <?php echo $result->detailList[$i]->spare6 ?> </li>
                                            <li> spare7(여분7) : <?php echo $result->detailList[$i]->spare7 ?> </li>
                                            <li> spare8(여분8) : <?php echo $result->detailList[$i]->spare8 ?> </li>
                                            <li> spare9(여분9) : <?php echo $result->detailList[$i]->spare9 ?> </li>
                                            <li> spare10(여분10) : <?php echo $result->detailList[$i]->spare10 ?> </li>
                                        </ul>
                                </fieldset>
                    <?php
                                }
                            }
                            if ( !is_null($result->propertyBag) ) {
                    ?>
                    <fieldset class="fieldset2">
                        <legend>propertyBag [추가속성 정보]</legend>
                        <ul>
                    <?php
                            foreach ($result->propertyBag as $key=>$data){
                    ?>
                            <li> <?php echo $key ?> : <?php echo $data ?> </li>
                    <?php
                            }
                    ?>
                        </ul>
                    <?php
                            }
                        }
                    ?>
                    </ul>
                </fieldset>
         </div>
    </body>
</html>

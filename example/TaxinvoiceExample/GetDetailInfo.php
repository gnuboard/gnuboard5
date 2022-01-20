<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 세금계산서 1건의 상세정보를 확인합니다.
     * - https://docs.popbill.com/taxinvoice/php/api#GetDetailInfo
     */

    include 'common.php';

    // 팝빌회원, 사업자번호
    $testCorpNum = '1234567890';

    // 발행유형, ENumMgtKeyType::SELL:매출, ENumMgtKeyType::BUY:매입, ENumMgtKeyType::TRUSTEE:위수탁
    $mgtKeyType = ENumMgtKeyType::SELL;

    // 세금계산서 문서번호
    $mgtKey = '20210701-001';

    try {
        $result = $TaxinvoiceService->GetDetailInfo($testCorpNum, $mgtKeyType, $mgtKey);
    } catch(PopbillException $pe) {
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
                            <li> writeDate (작성일자) : <?php echo $result->writeDate  ?> </li>
                            <li> chargeDirection (과금방향) : <?php echo $result->chargeDirection  ?> </li>
                            <li> issueType (발행형태) : <?php echo $result->issueType  ?> </li>
                            <li> taxType (과세형태) : <?php echo $result->taxType  ?> </li>
                            <li> supplyCostTotal (공급가액 합계) : <?php echo $result->supplyCostTotal  ?> </li>
                            <li> taxTotal (세액 합계) : <?php echo $result->taxTotal ?>  </li>
                            <li> totalAmount (합계금액) : <?php echo $result->totalAmount  ?> </li>
                            <li> ntsconfirmNum (국세청승인번호) : <?php echo $result->ntsconfirmNum  ?> </li>

                            <li> invoicerCorpNum (공급자 사업자번호) : <?php echo $result->invoicerCorpNum ?> </li>
                            <li> invoicerTaxRegID (공급자 종사업장 식별번호) : <?php echo $result->invoicerTaxRegID ?> </li>
                            <li> invoicerMgtKey (공급자 문서번호) : <?php echo $result->invoicerMgtKey  ?> </li>
                            <li> invoicerCorpName (공급자 상호) : <?php echo $result->invoicerCorpName  ?> </li>
                            <li> invoicerCEOName (공급자 대표자명) : <?php echo $result->invoicerCEOName  ?> </li>
                            <li> invoicerAddr (공급자 주소) : <?php echo $result->invoicerAddr  ?> </li>
                            <li> invoicerBizType (공급자 업태) : <?php echo $result->invoicerBizType  ?> </li>
                            <li> invoicerBizClass (공급자 종목) : <?php echo $result->invoicerBizClass  ?> </li>
                            <li> invoicerContactName (공급자 담당자명) : <?php echo $result->invoicerContactName  ?> </li>
                            <li> invoicerTEL (공급자 담당자 연락처) : <?php echo $result->invoicerTEL  ?> </li>
                            <li> invoicerHP (공급자 담당자 휴대폰) : <?php echo $result->invoicerHP  ?> </li>
                            <li> invoicerEmail (공급자 담당자 메일) : <?php echo $result->invoicerEmail  ?> </li>
                            <li> invoicerSMSSendYN (발행안내문자 전송여부) : <?php echo $result->invoicerSMSSendYN  ?> </li>

                            <li> invoiceeCorpNum (공급받는자 사업자번호) : <?php echo $result->invoiceeCorpNum  ?> </li>
                            <li> invoiceeTaxRegID (공급받는자 종사업장 식별번호) : <?php echo $result->invoiceeTaxRegID  ?> </li>
                            <li> invoiceeType (공급받는자 구분) : <?php echo $result->invoiceeType ?>  </li>
                            <li> invoiceeMgtKey (공급받는자 문서번호) : <?php echo $result->invoiceeMgtKey  ?> </li>
                            <li> invoiceeCorpName (공급받는자 상호) : <?php echo $result->invoiceeCorpName  ?> </li>
                            <li> invoiceeCEOName (공급받는자 대표자명) : <?php echo $result->invoiceeCEOName  ?> </li>
                            <li> invoiceeAddr (공급받는자 주소) : <?php echo $result->invoiceeAddr  ?> </li>
                            <li> invoiceeBizType (공급받는자 업태) : <?php echo $result->invoiceeBizType  ?> </li>
                            <li> invoiceeBizClass (공급받는자 종목) : <?php echo $result->invoiceeBizClass  ?> </li>
                            <li> invoiceeContactName1 (공급받는자 담당자명) : <?php echo $result->invoiceeContactName1  ?> </li>
                            <li> invoiceeDeptName1 (공급받는자 부서명) : <?php echo $result->invoiceeDeptName1  ?> </li>
                            <li> invoiceeTEL1 (공급받는자 담당자 연락처) : <?php echo $result->invoiceeTEL1  ?> </li>
                            <li> invoiceeHP1 (공급받는자 담당자 휴대폰) : <?php echo $result->invoiceeHP1  ?> </li>
                            <li> invoiceeEmail1 (공급받는자 담당자 메일) : <?php echo $result->invoiceeEmail1 ?> </li>
                            <li> invoiceeSMSSendYN (역발행안내문자 전송여부) : <?php echo $result->invoiceeSMSSendYN  ?> </li>
                            <li> closeDownState (공급받는자 휴폐업상태) : <?php echo $result->closeDownState ?> </li>
                            <li> closeDownStateDate (공급받는자 휴폐업일자) : <?php echo $result->closeDownStateDate ?> </li>

                            <li> purposeType (영수/청구) : <?php echo $result->purposeType  ?> </li>
                            <li> serialNum (일련번호) : <?php echo $result->serialNum ?>  </li>
                            <li> remark1 (비고1) : <?php echo $result->remark1 ?>  </li>
                            <li> remark2 (비고2) : <?php echo $result->remark2  ?> </li>
                            <li> remark3 (비고3) : <?php echo $result->remark3  ?> </li>
                            <li> kwon (권) : <?php echo $result->kwon  ?> </li>
                            <li> ho(호)  : <?php echo $result->ho  ?> </li>
                            <li> businessLicenseYN (사업자등록증 이미지 첨부여부) : <?php echo $result->businessLicenseYN  ?> </li>
                            <li> bankBookYN (통장사본이미지 첨부여부) : <?php echo $result->bankBookYN  ?> </li>

                            <li> cash (현금) : <?php echo $result->cash ?> </li>
                            <li> chkBill (수표) : <?php echo $result->chkBill ?> </li>
                            <li> credit (외상) : <?php echo $result->credit ?> </li>
                            <li> note (어음) : <?php echo $result->note ?> </li>

                        <?php
                            if ( isset($result->detailList) ) {
                                for ( $i = 0; $i < Count($result->detailList); $i++){
                            ?>
                                    <fieldset class="fieldset2">
                                        <legend> 상세항목(품목) 정보 [<?php echo $i+1 ?>] </legend>
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
                                } // end of for loop
                            } // end of if
                            if ( isset($result->addContactList) ) {
                                for ( $i = 0; $i < Count($result->addContactList); $i++){
                          ?>
                                    <fieldset class="fieldset2">
                                        <legend> 추가담당자 정보[<?php echo $i+1 ?>] </legend>
                                        <ul>
                                            <li> serialNum (일련번호) : <?php echo $result->addContactList[$i]->serialNum ?> </li>
                                            <li> email (담당자 이메일) : <?php echo $result->addContactList[$i]->email ?> </li>
                                            <li> contactName (담당자 성명) : <?php echo $result->addContactList[$i]->contactName ?> </li>
                                        </ul>
                                    </fieldset>
                          <?php
                                } // end of for loop
                            }  // end of if
                        }
                      ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

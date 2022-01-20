<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 현금영수증 1건의 상세정보를 확인합니다.
     * - https://docs.popbill.com/cashbill/php/api#GetDetailInfo
     */

    include 'common.php';

    // 팝빌회원 사업자번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 문서번호
    $mgtKey = '20211215-TEST100';

    try {
        $result = $CashbillService->GetDetailInfo($testCorpNum, $mgtKey);
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
                <legend>현금영수증 상세정보 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                            <li>mgtKey (현금영수증 문서번호) : <?php echo $result->mgtKey ?> </li>
                            <li>orgConfirmNum (원본 현금영수증 국세청승인번호) : <?php echo $result->orgConfirmNum ?> </li>
                            <li>orgTradeDate (원본 현금영수증 거래일자) : <?php echo $result->orgTradeDate ?> </li>
                            <li>tradeDate (거래일자) : <?php echo $result->tradeDate ?> </li>
                            <li>tradeType (문서형태) : <?php echo $result->tradeType ?> </li>
                            <li>tradeUsage (거래구분) : <?php echo $result->tradeUsage ?> </li>
                            <li>tradeOpt (거래유형) : <?php echo $result->tradeOpt ?> </li>
                            <li>taxationType (과세형태) : <?php echo $result->taxationType ?> </li>
                            <li>totalAmount (거래금액) : <?php echo $result->totalAmount ?> </li>
                            <li>supplyCost (공급가액) : <?php echo $result->supplyCost ?> </li>
                            <li>tax (부가세) : <?php echo $result->tax ?> </li>
                            <li>serviceFee (봉사료) : <?php echo $result->serviceFee ?> </li>
                            <li>franchiseCorpNum (가맹점 사업자번호) : <?php echo $result->franchiseCorpNum ?> </li>
                            <li>franchiseTaxRegID (가맹점 종사업장 식별번호) : <?php echo $result->franchiseTaxRegID ?> </li>
                            <li>franchiseCorpName (가맹점 상호) : <?php echo $result->franchiseCorpName ?> </li>
                            <li>franchiseCEOName (가맹점 대표자 성명) : <?php echo $result->franchiseCEOName ?> </li>
                            <li>franchiseAddr (가맹점 주소) : <?php echo $result->franchiseAddr ?> </li>
                            <li>franchiseTEL (가맹점 전화번호) : <?php echo $result->franchiseTEL ?> </li>
                            <li>identityNum (식별번호) : <?php echo $result->identityNum ?> </li>
                            <li>customerName (주문자명) : <?php echo $result->customerName ?> </li>
                            <li>itemName (주문상품명) : <?php echo $result->itemName ?> </li>
                            <li>orderNumber (주문번호) : <?php echo $result->orderNumber ?> </li>
                            <li>email (주문자 이메일) : <?php echo $result->email ?> </li>
                            <li>hp (주문자 휴대폰) : <?php echo $result->hp ?> </li>
                            <li>smssendYN (발행 안내문자 전송여부) : <?php echo $result->smssendYN ?> </li>
                            <li>cancelType (취소사유) : <?php echo $result->cancelType ?> </li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

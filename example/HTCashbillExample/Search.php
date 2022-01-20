<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * GetJobState(수집 상태 확인)를 통해 상태 정보 확인된 작업아이디를 활용하여 현금영수증 매입/매출 내역을 조회합니다.
     * - https://docs.popbill.com/htcashbill/php/api#Search
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 수집 요청(RequestJob) 호출시 반환받은 작업아이디
    $JobID = '018092923000000001';

    // 현금영수증 종류 배열, N-일반 현금영수증, C-취소 현금영수증
    $TradeType = array(
        'N',
        'C'
    );

    // 거래용도 배열, P-소득공제용, C-지출증빙용
    $TradeUsage = array(
        'P',
        'C'
    );

    // 페이지 번호
    $Page = 1;

    // 페이지당 목록개수
    $PerPage = 10;

    // 정렬방향, D-내림차순, A-오름차순
    $Order = "D";

    try {
        $response = $HTCashbillService->Search($testCorpNum, $JobID, $TradeType, $TradeUsage, $Page, $PerPage, $Order);
    } catch (PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
<body>
<div id="content">
    <p class="heading1">Response</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>수집 결과 조회</legend>
        <ul>
            <?php
            if (isset ($code)) {
                ?>
                <li>Response.code : <?php echo $code ?> </li>
                <li>Response.message : <?php echo $message ?></li>
                <?php
            } else {
                ?>
                <li>code (응답코드) : <?php echo $response->code ?></li>
                <li>message (응답메시지) : <?php echo $response->message ?></li>
                <li>total (총 검색결고 건수) : <?php echo $response->total ?></li>
                <li>perPage (페이지당 검색개수) : <?php echo $response->perPage ?></li>
                <li>pageNum (페이지 번호) : <?php echo $response->pageNum ?></li>
                <li>pageCount (페이지 개수) : <?php echo $response->pageCount ?></li>
                <?php
                for ($i = 0; $i < Count($response->list); $i++) {
                    ?>
                    <fieldset class="fieldset2">
                        <legend> 현금영수증 정보 [<?php echo $i + 1 ?>]</legend>
                        <ul>
                            <li>ntsconfirmNum (국세청승인번호) : <?php echo $response->list[$i]->ntsconfirmNum ?></li>
                            <li>tradeDate (거래일자) : <?php echo $response->list[$i]->tradeDate ?></li>
                            <li>tradeDT (거래일시) : <?php echo $response->list[$i]->tradeDT ?></li>
                            <li>tradeType (문서형태) : <?php echo $response->list[$i]->tradeType ?></li>
                            <li>tradeUsage (거래구분) : <?php echo $response->list[$i]->tradeUsage ?></li>
                            <li>totalAmount (거래금액) : <?php echo $response->list[$i]->totalAmount ?></li>
                            <li>supplyCost (공급가액) : <?php echo $response->list[$i]->supplyCost ?></li>
                            <li>tax (부가세) : <?php echo $response->list[$i]->tax ?></li>
                            <li>serviceFee (봉사료) : <?php echo $response->list[$i]->serviceFee ?></li>
                            <li>invoiceType (매입/매출) : <?php echo $response->list[$i]->invoiceType; ?></li>
                            <li>franchiseCorpNum (발행자 사업자번호) : <?php echo $response->list[$i]->franchiseCorpNum ?></li>
                            <li>franchiseCorpName (발행자 상호) : <?php echo $response->list[$i]->franchiseCorpName ?></li>
                            <li>franchiseCorpType (발행자 사업자유형) : <?php echo $response->list[$i]->franchiseCorpType ?></li>
                            <li>identityNum (거래처 식별번호) : <?php echo $response->list[$i]->identityNum ?></li>
                            <li>identityNumType (식별번호유형) : <?php echo $response->list[$i]->identityNumType ?></li>
                            <li>customerName (고객명) : <?php echo $response->list[$i]->customerName ?></li>
                            <li>cardOwnerName (카드소유자명) : <?php echo $response->list[$i]->cardOwnerName ?></li>
                            <li>deductionType (공제유형) : <?php echo $response->list[$i]->deductionType ?></li>
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

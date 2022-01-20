<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 검색조건에 해당하는 현금영수증을 조회합니다. (조회기간 단위 : 최대 6개월)
     * - https://docs.popbill.com/cashbill/php/api#Search
     */

    include 'common.php';

    // [필수] 팝빌회원 사업자번호
    $testCorpNum = '1234567890';

    // [필수] 조회일자 유형, R-등록일자, T-거래일자, I-발행일자
    $DType = 'R';

    // [필수] 시작일자
    $SDate = '20211201';

    // [필수] 종료일자
    $EDate = '20211220';

    // 문서상태코드, 2,3번째 자리 와일드카드 사용가능, 미기재시 전체조회
    $State = array(
        '1**',
        '2**',
        '3**',
        '4**'
    );

    // 문서형태, N-일반현금영수증, C-취소현금영수증
    $TradeType = array(
        'N',
        'C'
    );

    // 거래구분, P-소득공제, C-지출증빙
    $TradeUsage = array(
        'P',
        'C'
    );

    // 거래유형, N-일반, B-도서공연, T-대중교통
    $TradeOpt = array(
        'N',
        'B',
        'T'
    );

    // 과세형태, T-과세, N-비과세
    $TaxationType = array(
        'T',
        'N'
    );

    // 페이지번호, 기본값 1
    $Page = 1;

    // 페이지당 검색갯수, 기본값 500, 최대값 1000
    $PerPage = 30;

    // 정렬방향, D-내림차순, A-오름차순
    $Order = 'D';

    // 식별번호 조회, 미기재시 전체조회
    $QString = '';

    // 가맹점 종사업장 번호
    // └ 다수건 검색시 콤마(",")로 구분. 예) 1234,1000
    $FranchiseTaxRegID = "";

    try {
        $result = $CashbillService->Search( $testCorpNum, $DType, $SDate, $EDate, $State, $TradeType,
            $TradeUsage, $TaxationType, $Page, $PerPage, $Order, $QString, $TradeOpt, $FranchiseTaxRegID);
    }	catch(PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
    <body>
        <div id="content">
            <p class="heading1">Response</p>
            <br/>
            <fieldset class="fieldset1">
                <legend>현금영수증 목록조회</legend>
                <ul>
                   <?php
                        if( isset ( $code ) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
          ?>
                            <li>code (응답코드) : <?php echo $result->code ?> </li>
                            <li>total (총 검색결과 건수) : <?php echo $result->total ?> </li>
                            <li>pageNum (페이지 번호) : <?php echo $result->pageNum ?> </li>
                            <li>perPage (페이지당 목록개수) : <?php echo $result->perPage ?> </li>
                            <li>pageCount (페이지 개수) : <?php echo $result->pageCount ?> </li>
                            <li>message (응답메시지) : <?php echo $result->message ?> </li>
          <?php
                            for ($i = 0; $i < Count($result->list); $i++) {
                    ?>
                                <fieldset class="fieldset2">
                                    <legend> 현금영수증 상태/요약 정보[<?php echo $i+1?>]</legend>
                                    <ul>
                                        <li> itemKey (팝빌번호) : <?php echo $result->list[$i]->itemKey ?></li>
                                        <li> mgtKey (문서번호) : <?php echo $result->list[$i]->mgtKey ?></li>
                                        <li> tradeDate (거래일자) : <?php echo $result->list[$i]->tradeDate ?></li>
                                        <li> tradeType (문서형태) : <?php echo $result->list[$i]->tradeType ?></li>
                                        <li> tradeUsage (거래구분) : <?php echo $result->list[$i]->tradeUsage ?></li>
                                        <li> tradeOpt (거래유형) : <?php echo $result->list[$i]->tradeOpt ?></li>
                                        <li> taxationType (과세형태) : <?php echo $result->list[$i]->taxationType ?></li>
                                        <li> totalAmount (거래금액) : <?php echo $result->list[$i]->totalAmount ?></li>
                                        <li> issueDT (발행일시) : <?php echo $result->list[$i]->issueDT ?></li>
                                        <li> regDT (등록일시) : <?php echo $result->list[$i]->regDT ?></li>
                                        <li> stateMemo (상태메모) : <?php echo $result->list[$i]->stateMemo ?></li>
                                        <li> stateCode (상태코드) : <?php echo $result->list[$i]->stateCode ?></li>
                                        <li> stateDT (상태변경일시) : <?php echo $result->list[$i]->stateDT ?></li>
                                        <li> identityNum (식별번호) : <?php echo $result->list[$i]->identityNum ?></li>
                                        <li> itemName (주문상품명) : <?php echo $result->list[$i]->itemName ?></li>
                                        <li> customerName (주문자명) : <?php echo $result->list[$i]->customerName ?></li>
                                        <li> confirmNum (국세청승인번호) : <?php echo $result->list[$i]->confirmNum ?></li>
                                        <li> orgConfirmNum (원본 현금영수증 국세청승인번호) : <?php echo $result->list[$i]->orgConfirmNum ?></li>
                                        <li> orgTradeDate (원본 현금영수증 거래일자) : <?php echo $result->list[$i]->orgTradeDate ?></li>
                                        <li> ntssendDT (국세청 전송일시) : <?php echo $result->list[$i]->ntssendDT ?></li>
                                        <li> ntsresultDT (국세청 처리결과 수신일시) : <?php echo $result->list[$i]->ntsresultDT ?></li>
                                        <li> ntsresultCode (국세청 처리결과 상태코드) : <?php echo $result->list[$i]->ntsresultCode ?></li>
                                        <li> ntsresultMessage (국세청 처리결과 메시지) : <?php echo $result->list[$i]->ntsresultMessage ?></li>
                                        <li> printYN (인쇄여부) : <?php echo $result->list[$i]->printYN ?></li>
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

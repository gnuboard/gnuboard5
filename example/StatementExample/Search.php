<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 검색조건에 해당하는 전자명세서를 조회합니다. (조회기간 단위 : 최대 6개월)
     * - https://docs.popbill.com/statement/php/api#Search
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // [필수] 조회일자 유형, R-등록일자, W-작성일자, I-발행일자
    $DType = 'W';

    // [필수] 시작일자
    $SDate = '20210601';

    // [필수] 종료일자
    $EDate = '20210630';


    // 전송상태값 배열, 문서상태값 3자리 배열, 2,3번째 와일드카드 사용가능
    $State = array(
        '100',
        '2**',
        '3**'
    );

    // 명세서 코드배열 - 121(거래명세서), 122(청구서), 123(견적서) 124(발주서), 125(입금표), 126(영수증)
    $ItemCode = array(
        121,
        122,
        123,
        124,
        125,
        126
    );

    // 페이지 번호, 기본값 1
    $Page = 1;

    // 페이지당 검색갯수, 기본값(500), 최대값(1000)
    $PerPage = 20;

    // 정렬방향, D-내림차순, A-오름차순
    $Order = 'D';

    // 거래처 조회, 거래처 상호 또는 거래처 사업자등록번호 기재하여 조회, 미기재시 전체조회
    $QString = '';

    try {
        $result = $StatementService->Search($testCorpNum, $DType, $SDate, $EDate,
            $State, $ItemCode, $Page, $PerPage, $Order, $QString);
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
        <legend>전자명세서 목록조회</legend>
        <ul>
            <?php
            if ( isset($code) ) {
                ?>
                <li>Response.code : <?php echo $code ?> </li>
                <li>Response.message : <?php echo $message ?></li>
                <?php
            } else {
                ?>
                <li>code(응답 상태코드) : <?php echo $result->code ?> </li>
                <li>total(총 검색결과 건수) : <?php echo $result->total ?> </li>
                <li>pageNum(페이지 번호) : <?php echo $result->pageNum ?> </li>
                <li>perPage(페이지당 검색개수) : <?php echo $result->perPage ?> </li>
                <li>pageCount(페이지 개수) : <?php echo $result->pageCount ?> </li>
                <li>message(응답 메시지) : <?php echo $result->message ?> </li>
                <?php
                for ($i = 0; $i < Count($result->list); $i++) {
                    ?>
                    <fieldset class="fieldset2">
                        <legend> 전자명세서 요약정보[<?php echo $i+1?>]</legend>
                        <ul>
                            <li> itemKey(아이템키) : <?php echo $result->list[$i]->itemKey ?></li>
                            <li> itemCode(명세서 코드) : <?php echo $result->list[$i]->itemCode ?></li>
                            <li> stateCode(상태코드) : <?php echo $result->list[$i]->stateCode ?></li>
                            <li> taxType(세금형태) : <?php echo $result->list[$i]->taxType ?></li>
                            <li> purposeType(영수/청구) : <?php echo $result->list[$i]->purposeType ?></li>
                            <li> writeDate(작성일자) : <?php echo $result->list[$i]->writeDate ?></li>
                            <li> senderCorpName(발신자 상호) : <?php echo $result->list[$i]->senderCorpName ?></li>
                            <li> senderCorpNum(발신자 사업자번호) : <?php echo $result->list[$i]->senderCorpNum ?></li>
                            <li> senderPrintYN(발신자 인쇄여부) : <?php echo $result->list[$i]->senderPrintYN ?></li>
                            <li> receiverCorpName(수신자 상호) : <?php echo $result->list[$i]->receiverCorpName ?></li>
                            <li> receiverCorpNum(수신자 사업자번호) : <?php echo $result->list[$i]->receiverCorpNum ?></li>
                            <li> receiverPrintYN(수신자 인쇄여부) : <?php echo $result->list[$i]->receiverPrintYN ?></li>
                            <li> supplyCostTotal(공급가액 합계) : <?php echo $result->list[$i]->supplyCostTotal ?></li>
                            <li> taxTotal(세액 합계) : <?php echo $result->list[$i]->taxTotal ?></li>
                            <li> issueDT(발행일시) : <?php echo $result->list[$i]->issueDT ?></li>
                            <li> stateDT(상태 변경일시) : <?php echo $result->list[$i]->stateDT ?></li>
                            <li> openYN(메일 개봉 여부) : <?php echo $result->list[$i]->openYN ?></li>
                            <li> openDT(개봉 일시) : <?php echo $result->list[$i]->openDT ?></li>
                            <li> stateMemo(상태메모) : <?php echo $result->list[$i]->stateMemo ?></li>
                            <li> regDT(등록일시) : <?php echo $result->list[$i]->regDT ?></li>
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

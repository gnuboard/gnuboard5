<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 검색조건에 해당하는 팩스 전송내역 목록을 조회합니다. (조회기간 단위 : 최대 2개월)
     * - 팩스 접수일시로부터 2개월 이내 접수건만 조회할 수 있습니다.
     * - https://docs.popbill.com/fax/php/api#Search
     */

    include 'common.php';

    // 팝빌 회원 사업자 번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 검색시작일자
    $SDate = '20210701';

    // 검색종료일자
    $EDate = '20210710';

    // 전송상태값 배열, 1-대기, 2-성공, 3-실패, 4-취소
    $State = array(1, 2, 3, 4);

    // 예약전송 조회여부, true(예약전송건 검색)
    $ReserveYN = false;

    // 개인조회여부, true(개인조회), false(회사조회)
    $SenderOnly = false;

    // 페이지 번호, 기본값 1
    $Page = 1;

    // 페이지당 검색갯수, 기본값 500, 최대값 1000
    $PerPage = 500;

    // 정렬방향, D-내림차순, A-오름차순
    $Order = 'D';

    // 조회 검색어.
    // 팩스 전송시 입력한 발신자명 또는 수신자명 기재.
    // 조회 검색어를 포함한 발신자명 또는 수신자명을 검색합니다.
    $QString = '';

    try {
        $result = $FaxService->Search($testCorpNum, $SDate, $EDate, $State, $ReserveYN, $SenderOnly, $Page, $PerPage, $Order, '', $QString);
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
        <legend>팩스전송내역 조회</legend>
        <ul>
            <?php
            if (isset($code)) {
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
                        <legend> 팩스전송내역 조회 결과 [<?php echo $i + 1 ?>]</legend>
                        <ul>
                            <li> state (전송상태 코드) : <?php echo $result->list[$i]->state ?> </li>
                            <li> result (전송결과 코드) : <?php echo $result->list[$i]->result ?> </li>
                            <li> sendNum (발신번호) : <?php echo $result->list[$i]->sendNum ?> </li>
                            <li> senderName (발신자명) : <?php echo $result->list[$i]->senderName ?> </li>
                            <li> receiveNum (수신번호) : <?php echo $result->list[$i]->receiveNum ?> </li>
                            <li> receiveNumType (수신번호 유형) : <?php echo $result->list[$i]->receiveNumType ?> </li>
                            <li> receiveName (수신자명) : <?php echo $result->list[$i]->receiveName ?> </li>
                            <li> title (팩스제목) : <?php echo $result->list[$i]->title ?> </li>
                            <li> sendPageCnt (전체 페이지수) : <?php echo $result->list[$i]->sendPageCnt ?> </li>
                            <li> successPageCnt (성공 페이지수) : <?php echo $result->list[$i]->successPageCnt ?> </li>
                            <li> failPageCnt (실패 페이지수) : <?php echo $result->list[$i]->failPageCnt ?> </li>
                            <li> refundPageCnt (환불 페이지수) : <?php echo $result->list[$i]->refundPageCnt ?> </li>
                            <li> cancelPageCnt (취소 페이지수) : <?php echo $result->list[$i]->cancelPageCnt ?> </li>
                            <li> receiptDT (접수일시) : <?php echo $result->list[$i]->receiptDT ?> </li>
                            <li> reserveDT (예약일시) : <?php echo $result->list[$i]->reserveDT ?> </li>
                            <li> sendDT (전송일시) : <?php echo $result->list[$i]->sendDT ?> </li>
                            <li> resultDT (전송결과 수신일시) : <?php echo $result->list[$i]->resultDT ?> </li>
                            <li> fileNames (전송파일명 리스트) : <?php echo implode(', ', $result->list[$i]->fileNames) ?> </li>
                            <li> receiptNum (접수번호) : <?php echo $result->list[$i]->receiptNum ?> </li>
                            <li> requestNum (요청번호) : <?php echo $result->list[$i]->requestNum ?> </li>
                            <li> chargePageCnt (과금 페이지수) : <?php echo $result->list[$i]->chargePageCnt ?> </li>
                            <li> tiffFileSize (변환파일용랑) : <?php echo $result->list[$i]->tiffFileSize ?> </li>
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

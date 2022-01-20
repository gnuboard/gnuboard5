<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 검색조건에 해당하는 카카오톡 전송내역을 조회합니다. (조회기간 단위 : 최대 2개월)
     * - 카카오톡 접수일시로부터 6개월 이내 접수건만 조회할 수 있습니다.
     * - https://docs.popbill.com/kakao/php/api#Search
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // [필수] 시작일자, 날짜형식(yyyyMMdd)
    $SDate = '20210601';

    // [필수] 종료일자, 날짜형식(yyyyMMdd)
    $EDate = '20210630';

    // 전송상태값 배열, 0-대기, 1-전송중, 2-성공, 3-대체, 4-실패, 5-예약취소
    $State = array('0', '1', '2', '3', '4', '5');

    // 검색대상, ATS-알림톡, FTS-친구톡(텍스트), FMS-친구톡(이미지)
    $Item = array('ATS','FTS','FMS');

    // 예약여부, 공백-전체조회, 1-예약전송조회, 0-즉시전송조회
    $ReserveYN = '';

    // 개인조회여부, false-전체조회, true-개인조회
    $SenderYN = false;

    // 페이지번호
    $Page = 1;

    // 페이지 검색개수, 기본값 500, 최대값 1000
    $PerPage = 500;

    // 정렬방향, D-내림차순, A-오름차순
    $Order = 'D';

    // 조회 검색어.
    // 카카오톡 전송시 입력한 수신자명 기재.
    // 조회 검색어를 포함한 수신자명을 검색합니다.
    $QString = '';

    try {
        $result = $KakaoService->Search( $testCorpNum, $SDate, $EDate, $State, $Item, $ReserveYN, $SenderYN, $Page, $PerPage, $Order, '', $QString );
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
                <legend>카카오톡 전송내역 목록 조회 </legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        }else{
                    ?>
                            <li>code (응답코드) : <?php echo $result->code ?> </li>
                            <li>total (총 검색결과 건수) : <?php echo $result->total ?> </li>
                            <li>perPage (페이지당 검색개수) : <?php echo $result->perPage ?> </li>
                            <li>pageNum (페이지 번호) : <?php echo $result->pageNum ?> </li>
                            <li>pageCount (페이지 개수) : <?php echo $result->pageCount ?> </li>
                            <li>message (응답 메시지) : <?php echo $result->message ?> </li>
                    <?php
                        for ($i = 0; $i < Count($result->list); $i++) {
                    ?>
                            <fieldset class="fieldset2">
                                <legend> 카카오톡 전송내역 조회 결과 [<?php echo $i+1 ?>/<?php echo Count($result->list)?>]</legend>
                                <ul>
                                    <li> state (전송상태 코드) : <?php echo $result->list[$i]->state ?> </li>
                                    <li> sendDT (전송일시) : <?php echo $result->list[$i]->sendDT ?> </li>
                                    <li> result (전송결과 코드) : <?php echo $result->list[$i]->result ?> </li>
                                    <li> resultDT (전송결과 수신일시) : <?php echo $result->list[$i]->resultDT ?> </li>
                                    <li> contentType (카카오톡 유형) : <?php echo $result->list[$i]->contentType ?> </li>
                                    <li> receiveNum (수신번호) : <?php echo $result->list[$i]->receiveNum ?> </li>
                                    <li> receiveName (수신자명) : <?php echo $result->list[$i]->receiveName ?> </li>
                                    <li> content (알림톡/친구톡 내용) : <?php echo $result->list[$i]->content ?> </li>
                                    <li> altContentType (대체문자 전송타입) : <?php echo $result->list[$i]->altContentType ?> </li>
                                    <li> altSendDT (대체문자 전송일시) : <?php echo $result->list[$i]->altSendDT ?> </li>
                                    <li> altResult (대체문자 전송결과 코드) : <?php echo $result->list[$i]->altResult ?> </li>
                                    <li> altResultDT (대체문자 전송결과 수신일시) : <?php echo $result->list[$i]->altResultDT ?> </li>
                                    <li> altContent (대체문자 내용) : <?php echo $result->list[$i]->altContent ?> </li>
                                    <li> reserveDT (예약일시) : <?php echo $result->list[$i]->reserveDT ?> </li>
                                    <li> receiptNum (접수번호) : <?php echo $result->list[$i]->receiptNum ?> </li>
                                    <li> requestNum (요청번호) : <?php echo $result->list[$i]->requestNum ?> </li>
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

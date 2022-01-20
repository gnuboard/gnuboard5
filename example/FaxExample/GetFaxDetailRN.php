<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 파트너가 할당한 전송요청 번호를 통해 팩스 전송상태 및 결과를 확인합니다.
     * - https://docs.popbill.com/fax/php/api#GetFaxDetailRN
     */

    include 'common.php';

    // 팝빌 회원 사업자 번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 팩스전송 요청시 할당한 전송요청번호
    $requestNum = '20211215_TEST002';

    try {
        $result = $FaxService->GetFaxDetailRN($testCorpNum, $requestNum);
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
        <legend>팩스전송 내역 및 전송상태 확인</legend>
        <ul>
            <?php
            if (isset($code)) {
                ?>
                <li>Response.code : <?php echo $code ?> </li>
                <li>Response.message : <?php echo $message ?></li>
                <?php
            } else {
                for ($i = 0; $i < Count($result); $i++) {
                    ?>
                    <fieldset class="fieldset2">
                        <legend> 팩스전송내역 조회 결과 [<?php echo $i + 1 ?>/<?php echo Count($result) ?>]</legend>
                        <ul>
                            <li> state (전송상태 코드) : <?php echo $result[$i]->state ?> </li>
                            <li> result (전송결과 코드) : <?php echo $result[$i]->result ?> </li>
                            <li> sendNum (발신번호) : <?php echo $result[$i]->sendNum ?> </li>
                            <li> senderName (발신자명) : <?php echo $result[$i]->senderName ?> </li>
                            <li> receiveNum (수신번호) : <?php echo $result[$i]->receiveNum ?> </li>
                            <li> receiveNumType (수신번호 유형) : <?php echo $result[$i]->receiveNumType ?> </li>
                            <li> receiveName (수신자명) : <?php echo $result[$i]->receiveName ?> </li>
                            <li> title (팩스제목) : <?php echo $result[$i]->title ?> </li>
                            <li> sendPageCnt (전체 페이지수) : <?php echo $result[$i]->sendPageCnt ?> </li>
                            <li> successPageCnt (성공 페이지수) : <?php echo $result[$i]->successPageCnt ?> </li>
                            <li> failPageCnt (실패 페이지수) : <?php echo $result[$i]->failPageCnt ?> </li>
                            <li> refundPageCnt (환불 페이지수) : <?php echo $result[$i]->refundPageCnt ?> </li>
                            <li> cancelPageCnt (취소 페이지수) : <?php echo $result[$i]->cancelPageCnt ?> </li>
                            <li> receiptDT (접수일시) : <?php echo $result[$i]->receiptDT ?> </li>
                            <li> reserveDT (예약일시) : <?php echo $result[$i]->reserveDT ?> </li>
                            <li> sendDT (전송일시) : <?php echo $result[$i]->sendDT ?> </li>
                            <li> resultDT (전송결과 수신일시) : <?php echo $result[$i]->resultDT ?> </li>
                            <li> fileNames (전송파일명 리스트) : <?php echo implode(', ', $result[$i]->fileNames) ?> </li>
                            <li> receiptNum (접수번호) : <?php echo $result[$i]->receiptNum ?> </li>
                            <li> requestNum (요청번호) : <?php echo $result[$i]->requestNum ?> </li>
                            <li> chargePageCnt (과금 페이지수) : <?php echo $result[$i]->chargePageCnt ?> </li>
                            <li> tiffFileSize (변환파일용랑) : <?php echo $result[$i]->tiffFileSize ?>byte</li>
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

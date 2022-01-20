<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 문자전송에 대한 전송결과 요약정보를 확인합니다.
     * - https://docs.popbill.com/message/php/api#GetStates
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 문자전송 요청 시 발급받은 접수번호 배열(receiptNum)
    $ReceiptNumList = array();
    array_push($ReceiptNumList, '018041717000000018');

    try {
        $result = $MessagingService->GetStates($testCorpNum, $ReceiptNumList);
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
                <legend>전송내역 요약정보 확인 </legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                            for ($i = 0; $i < Count($result); $i++) {
                    ?>
                            <fieldset class="fieldset2">
                                <legend> 문자전송내역 조회 결과 [<?php echo $i+1 ?>/<?php echo Count($result)?>]</legend>
                                <ul>
                                    <li> rNum (접수번호) : <?php echo $result[$i]->rNum ?> </li>
                                    <li> sn (일련번호) : <?php echo $result[$i]->sn ?> </li>
                                    <li> stat (전송 상태코드) : <?php echo $result[$i]->stat ?> </li>
                                    <li> rlt (전송 결과코드) : <?php echo $result[$i]->rlt ?> </li>
                                    <li> sDT (전송일시) : <?php echo $result[$i]->sDT ?> </li>
                                    <li> rDT (전송결과 수신일시) : <?php echo $result[$i]->rDT ?> </li>
                                    <li> net (전송처리 이동통신사명) : <?php echo $result[$i]->net ?> </li>
                                    <li> srt (구 전송결과 코드) : <?php echo $result[$i]->srt ?> </li>
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

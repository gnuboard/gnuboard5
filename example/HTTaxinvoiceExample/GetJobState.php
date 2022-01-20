<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 함수 (RequestJob – 수집 요청)를 통해 반환 받은 작업 아이디의 상태를 확인합니다.
     * - https://docs.popbill.com/httaxinvoice/php/api#GetJobState
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 수집 요청시 반환받은 작업아이디
    $jobID = '021030710000000001';

    try {
        $result = $HTTaxinvoiceService->GetJobState($testCorpNum, $jobID);
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
                <legend>수집 상태 확인</legend>
                <ul>
                    <?php
                        if ( isset ( $code ) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                            <li>jobID (작업아이디) : <?php echo $result->jobID ?></li>
                            <li>jobState (수집상태) : <?php echo $result->jobState ?></li>
                            <li>queryType (수집유형) : <?php echo $result->queryType ?></li>
                            <li>queryDateType (일자유형) : <?php echo $result->queryDateType ?></li>
                            <li>queryStDate (시작일자) : <?php echo $result->queryStDate ?></li>
                            <li>queryEnDate (종료일자) : <?php echo $result->queryEnDate ?></li>
                            <li>errorCode (오류코드) : <?php echo $result->errorCode ?></li>
                            <li>errorReason (오류메시지) : <?php echo $result->errorReason ?></li>
                            <li>jobStartDT (작업 시작일시) : <?php echo $result->jobStartDT ?></li>
                            <li>jobEndDT (작업 종료일시) : <?php echo $result->jobEndDT ?></li>
                            <li>collectCount (수집개수) : <?php echo $result->collectCount ?></li>
                            <li>regDT (수집 요청일시) : <?php echo $result->regDT ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

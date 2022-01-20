<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 홈택스에 신고된 전자세금계산서 매입/매출 내역 수집을 팝빌에 요청합니다. (조회기간 단위 : 최대 3개월)
     * - 수집 요청후 반환받은 작업아이디(JobID)의 유효시간은 1시간 입니다.
     * - https://docs.popbill.com/httaxinvoice/php/api#RequestJob
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 전자세금계산서 유형, SELL-매출, BUY-매입, TRUSTEE-위수탁
    $TIKeyType = KeyType::SELL;

    // 수집일자유형, W-작성일자, I-발행일자, S-전송일자
    $DType = 'S';

    // 시작일자, 형식(yyyyMMdd)
    $SDate = '20210701';

    // 종료일자, 형식(yyyyMMdd)
    $EDate = '20210708';

    try {
        $jobID = $HTTaxinvoiceService->RequestJob($testCorpNum, $TIKeyType, $DType, $SDate, $EDate);
    }
    catch(PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
    <body>
        <div id="content">
            <p class="heading1">Response</p>
            <br/>
            <fieldset class="fieldset1">
                <legend>수집 요청</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                        <li>jobID(작업아이디) : <?php echo $jobID ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

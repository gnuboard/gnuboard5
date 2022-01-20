<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * "임시저장" 상태의 명세서에 1개의 파일을 첨부합니다. (최대 5개)
     * - https://docs.popbill.com/statement/php/api#AttachFile
     */

    include 'common.php';

    // 팝빌 회원 사업자번호, "-" 제외 10자리
    $testCorpNum = '1234567890';

    // 명세서 코드 - 121(거래명세서), 122(청구서), 123(견적서) 124(발주서), 125(입금표), 126(영수증)
    $itemCode= '121';

    // 문서번호
    $mgtKey = '20210708-001';

    // 첨부파일 경로, 해당 파일에 읽기 권한이 설정되어 있어야 합니다.
    $filepath = './uploadtest.pdf';

    try {
        $result = $StatementService->AttachFile($testCorpNum, $itemCode, $mgtKey, $filepath);
        $code = $result->code;
        $message = $result->message;
    }
    catch( PopbillException $pe ) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
    <body>
        <div id="content">
            <p class="heading1">Response</p>
            <br/>
            <fieldset class="fieldset1">
                <legend>전자명세서 첨부파일 등록</legend>
                <ul>
                    <li>Response.code : <?php echo $code ?></li>
                    <li>Response.message : <?php echo $message ?></li>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

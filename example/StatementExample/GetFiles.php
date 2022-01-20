<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 전자명세서에 첨부된 파일목록을 확인합니다.
     * - 응답항목 중 파일아이디(AttachedFile) 항목은 파일삭제(DeleteFile API) 호출시 이용할 수 있습니다.
     * - https://docs.popbill.com/statement/php/api#GetFiles
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 명세서 코드 - 121(거래명세서), 122(청구서), 123(견적서) 124(발주서), 125(입금표), 126(영수증)
    $itemCode = '121';

    // 문서번호
    $mgtKey = '20210703-001';

    try {
        $result = $StatementService->GetFiles($testCorpNum, $itemCode, $mgtKey);
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
                <legend>전자명세서 첨부파일 목록 확인 </legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {

                        for ($i = 0; $i < Count($result) ; $i++) {
                    ?>
                            <fieldset class ="fieldset2">
                                <legend> 첨부파일 [<?php echo $i+1 ?>] </legend>
                                <ul>
                                    <li> serialNum(첨부파일 일련번호) : <?php echo $result[$i]->serialNum ?></li>
                                    <li> attachedFile(파일아이디-첨부파일 삭제시 사용) : <?php echo $result[$i]->attachedFile ?></li>
                                    <li> displayName(첨부파일명) : <?php echo $result[$i]->displayName ?></li>
                                    <li> regDT(첨부일시) : <?php echo $result[$i]->regDT ?></li>
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

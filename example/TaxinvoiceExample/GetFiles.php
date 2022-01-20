<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * "임시저장" 상태의 세금계산서에 첨부된 1개의 파일을 삭제합니다.
     * - 파일을 식별하는 파일아이디는 첨부파일 목록(GetFiles API) 의 응답항목 중 파일아이디(AttachedFile) 값을 통해 확인할 수 있습니다.
     * - https://docs.popbill.com/taxinvoice/php/api#GetFiles
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 발행유형, ENumMgtKeyType::SELL:매출, ENumMgtKeyType::BUY:매입, ENumMgtKeyType::TRUSTEE:위수탁
    $mgtKeyType = ENumMgtKeyType::SELL;

    // 문서번호
    $mgtKey = '20210801-001';

    try {
        $result = $TaxinvoiceService->GetFiles($testCorpNum, $mgtKeyType, $mgtKey);
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
                <legend>세금계산서 첨부파일 목록 확인 </legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {

                        for ( $i = 0; $i < Count($result) ; $i++ ) {
                    ?>
                            <fieldset class ="fieldset2">
                                <legend> 첨부파일 [<?php echo $i+1 ?>] </legend>
                                <ul>
                                    <li> serialNum(순번) : <?php echo $result[$i]->serialNum; ?></li>
                                    <li> displayName(파일명) : <?php echo $result[$i]->displayName; ?></li>
                                    <li> attachedFile(파일아이디) : <?php echo $result[$i]->attachedFile; ?></li>
                                    <li> regDT(등록일시) : <?php echo $result[$i]->regDT; ?></li>
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

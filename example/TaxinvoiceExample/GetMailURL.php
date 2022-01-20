<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 안내메일과 관련된 전자세금계산서를 확인 할 수 있는 상세 페이지의 팝업 URL을 반환하며, 해당 URL은 메일 하단의 "전자세금계산서 보기" 버튼의 링크와 같습니다.
     * - 함수 호출로 반환 받은 URL에는 유효시간이 없습니다.
     * - https://docs.popbill.com/taxinvoice/php/api#GetMailURL
     */

    include 'common.php';

    // 팝빌 회원 사업자 번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 발행유형, ENumMgtKeyType::SELL:매출, ENumMgtKeyType::BUY:매입, ENumMgtKeyType::TRUSTEE:위수탁
    $mgtKeyType = ENumMgtKeyType::SELL;

    // 문서번호
    $mgtKey = '20210701-001';

    try {
        $url = $TaxinvoiceService->GetMailURL($testCorpNum, $mgtKeyType, $mgtKey);
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
                <legend>세금계산서 공급받는자용 메일 URL</legend>
                <ul>
                    <?php
                        if ( isset($url) ) {
                    ?>
                            <li>url : <?php echo $url ?></li>
                    <?php
                        } else {
                    ?>
                            <li>Response.code : <?php echo $code ?> </li>
                            <li>Response.message : <?php echo $message ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

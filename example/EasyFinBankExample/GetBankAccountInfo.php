<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /*
    * 팝빌에 등록된 계좌 정보를 확인합니다.
    * - https://docs.popbill.com/easyfinbank/php/api#GetBankAccountInfo
    */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 기관코드
    // 산업은행-0002 / 기업은행-0003 / 국민은행-0004 /수협은행-0007 / 농협은행-0011 / 우리은행-0020
    // SC은행-0023 / 대구은행-0031 / 부산은행-0032 / 광주은행-0034 / 제주은행-0035 / 전북은행-0037
    // 경남은행-0039 / 새마을금고-0045 / 신협은행-0048 / 우체국-0071 / KEB하나은행-0081 / 신한은행-0088 /씨티은행-0027
    $bankCode = '';

    // 계좌번호
    $accountNumber = '';

    try {
        $result = $EasyFinBankService->GetBankAccountInfo($testCorpNum, $bankCode, $accountNumber);
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
                <legend>계좌정보 확인</legend>
                <ul>
                    <?php
                        if ( isset ( $code ) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                        <li>bankCode (기관코드) : <?php echo $result->bankCode ?></li>
                        <li>accountNumber (계좌번호) : <?php echo $result->accountNumber ?></li>
                        <li>accountName (계좌 별칭) : <?php echo $result->accountName ?></li>
                        <li>accountType (계좌 유형) : <?php echo $result->accountType ?></li>
                        <li>state (계좌 상태) : <?php echo $result->state ?></li>
                        <li>regDT (등록일시) : <?php echo $result->regDT ?></li>
                        <li>memo (메모) : <?php echo $result->memo ?></li>

                        <li>contractDT (정액제 서비스 시작일시) : <?php echo $result->contractDT ?></li>
                        <li>useEndDate (정액제 서비스 종료일) : <?php echo $result->useEndDate ?></li>
                        <li>baseDate (자동연장 결제일) : <?php echo $result->baseDate ?></li>
                        <li>contractState (정액제 서비스 상태) : <?php echo $result->contractState ?></li>
                        <li>closeRequestYN (정액제 서비스 해지신청 여부) : <?php echo $result->closeRequestYN ?></li>
                        <li>useRestrictYN (정액제 서비스 사용제한 여부) : <?php echo $result->useRestrictYN ?></li>
                        <li>closeOnExpired (정액제 서비스 만료 시 해지 여부) : <?php echo $result->closeOnExpired ?></li>
                        <li>unPaidYN (미수금 보유 여부) : <?php echo $result->unPaidYN ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

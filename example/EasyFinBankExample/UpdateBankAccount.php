<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 팝빌에 등록된 계좌정보를 수정합니다.
     * - https://docs.popbill.com/easyfinbank/php/api#UpdateBankAccount
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-' 제외 10자리
    $testCorpNum = '1234567890';

    // [필수] 기관코드
    // 산업은행-0002 / 기업은행-0003 / 국민은행-0004 /수협은행-0007 / 농협은행-0011 / 우리은행-0020
    // SC은행-0023 / 대구은행-0031 / 부산은행-0032 / 광주은행-0034 / 제주은행-0035 / 전북은행-0037
    // 경남은행-0039 / 새마을금고-0045 / 신협은행-0048 / 우체국-0071 / KEB하나은행-0081 / 신한은행-0088 /씨티은행-0027
    $BankCode = '';

    // [필수]계좌번호
    $AccountNumber = '';

    // 계좌정보 클래스 생성
    $UpdateInfo = new UpdateEasyFinBankAccountForm();

    // [필수] 계좌비밀번호
    $UpdateInfo->AccountPWD = '';

    // 계좌 별칭
    $UpdateInfo->AccountName = '';

    // 인터넷뱅킹 아이디 (국민은행 필수)
    $UpdateInfo->BankID = '';

    // 조회전용 계정 아이디 (대구은행, 신협, 신한은행 필수)
    $UpdateInfo->FastID = '';

    // 조회전용 계정 비밀번호 (대구은행, 신협, 신한은행 필수
    $UpdateInfo->FastPWD = '';

    // 메모
    $UpdateInfo->Memo = '';

    try {
        $result = $EasyFinBankService->UpdateBankAccount($testCorpNum, $BankCode, $AccountNumber, $UpdateInfo);
        $code = $result->code;
        $message = $result->message;
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
                <legend>계좌정보 수정</legend>
                <ul>
                    <li>Response.code : <?php echo $code ?></li>
                    <li>Response.message : <?php echo $message ?></li>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

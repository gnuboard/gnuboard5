<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 계좌조회 서비스를 이용할 계좌를 팝빌에 등록합니다.
     * - https://docs.popbill.com/easyfinbank/php/api#RegistBankAccount
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-' 제외 10자리
    $testCorpNum = '1234567890';

    // 계좌정보 클래스 생성
    $BankAccountInfo = new EasyFinBankAccountForm();

    // [필수] 기관코드
    // 산업은행-0002 / 기업은행-0003 / 국민은행-0004 /수협은행-0007 / 농협은행-0011 / 우리은행-0020
    // SC은행-0023 / 대구은행-0031 / 부산은행-0032 / 광주은행-0034 / 제주은행-0035 / 전북은행-0037
    // 경남은행-0039 / 새마을금고-0045 / 신협은행-0048 / 우체국-0071 / KEB하나은행-0081 / 신한은행-0088 /씨티은행-0027
    $BankAccountInfo->BankCode = '';

    // [필수] 계좌번호 하이픈('-') 제외
    $BankAccountInfo->AccountNumber = '';

    // [필수] 계좌비밀번호
    $BankAccountInfo->AccountPWD = '';

    // [필수] 계좌유형, "법인" 또는 "개인" 입력
    $BankAccountInfo->AccountType = '';

    // [필수] 예금주 식별정보 (‘-‘ 제외)
    // 계좌유형이 “법인”인 경우 : 사업자번호(10자리)
    // 계좌유형이 “개인”인 경우 : 예금주 생년월일 (6자리-YYMMDD)
    $BankAccountInfo->IdentityNumber = '';

    // 계좌 별칭
    $BankAccountInfo->AccountName = '';

    // 인터넷뱅킹 아이디 (국민은행 필수)
    $BankAccountInfo->BankID = '';

    // 조회전용 계정 아이디 (대구은행, 신협, 신한은행 필수)
    $BankAccountInfo->FastID = '';

    // 조회전용 계정 비밀번호 (대구은행, 신협, 신한은행 필수
    $BankAccountInfo->FastPWD = '';

    // 결제기간(개월), 1~12 입력가능, 미기재시 기본값(1) 처리
    // - 파트너 과금방식의 경우 입력값에 관계없이 1개월 처리
    $BankAccountInfo->UsePeriod = '';

    // 메모
    $BankAccountInfo->Memo = '';

    try {
        $result = $EasyFinBankService->RegistBankAccount($testCorpNum, $BankAccountInfo);
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
                <legend>계좌 등록</legend>
                <ul>
                    <li>Response.code : <?php echo $code ?></li>
                    <li>Response.message : <?php echo $message ?></li>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

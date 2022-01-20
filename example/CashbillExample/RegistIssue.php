<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 작성된 현금영수증 데이터를 팝빌에 저장과 동시에 발행하여 "발행완료" 상태로 처리합니다.
     * - 현금영수증 국세청 전송 정책 : https://docs.popbill.com/cashbill/ntsSendPolicy?lang=php
     * - https://docs.popbill.com/cashbill/php/api#RegistIssue
     */

    include 'common.php';

    // 팝빌 회원 사업자번호, '-' 제외 10자리
    $testCorpNum = '1234567890';

    // 팝빌회원 아이디
    $testUserID = 'testkorea';

    // 문서번호, 최대 24자리, 영문, 숫자 '-', '_'를 조합하여 사업자별로 중복되지 않도록 구성
    $mgtKey = '20211215-TEST004';

    // 메모
    $memo = '현금영수증 즉시발행 메모';

    // 발행안내메일 제목
    // 미기재시 기본양식으로 전송
    $emailSubject = '';

    // 현금영수증 객체 생성
    $Cashbill = new Cashbill();

    // [필수] 현금영수증 문서번호,
    $Cashbill->mgtKey = $mgtKey;

    // [필수] 문서형태, (승인거래, 취소거래) 중 기재
    $Cashbill->tradeType = '승인거래';

    // [필수] 거래구분, (소득공제용, 지출증빙용) 중 기재
    $Cashbill->tradeUsage = '소득공제용';

    // [필수] 거래유형, (일반, 도서공연, 대중교통) 중 기재
    $Cashbill->tradeOpt = '일반';

    // [필수] 과세형태, (과세, 비과세) 중 기재
    $Cashbill->taxationType = '과세';

    // [필수] 거래금액, ','콤마 불가 숫자만 가능
    $Cashbill->totalAmount = '11000';

    // [필수] 공급가액, ','콤마 불가 숫자만 가능
    $Cashbill->supplyCost = '10000';

    // [필수] 부가세, ','콤마 불가 숫자만 가능
    $Cashbill->tax = '1000';

    // [필수] 봉사료, ','콤마 불가 숫자만 가능
    $Cashbill->serviceFee = '0';

    // [필수] 가맹점 사업자번호
    $Cashbill->franchiseCorpNum = $testCorpNum;

    // 가맹점 종사업장 식별번호
    $Cashbill->franchiseTaxRegID = "";

    // 가맹점 상호
    $Cashbill->franchiseCorpName = '발행자 상호';

    // 가맹점 대표자 성명
    $Cashbill->franchiseCEOName = '발행자 대표자명';

    // 가맹점 주소
    $Cashbill->franchiseAddr = '발행자 주소';

    // 가맹점 전화번호
    $Cashbill->franchiseTEL = '070-1234-1234';

    // [필수] 식별번호, 거래구분에 따라 작성
    // 소득공제용 - 주민등록/휴대폰/카드번호 기재가능
    // 지출증빙용 - 사업자번호/주민등록/휴대폰/카드번호 기재가능
    $Cashbill->identityNum = '0101112222';

    // 주문자명
    $Cashbill->customerName = '주식회사주문자명담당자';

    // 주문상품명
    $Cashbill->itemName = '상품명';

    // 주문주문번호
    $Cashbill->orderNumber = '주문번호';

    // 주문자 이메일
    // 팝빌 개발환경에서 테스트하는 경우에도 안내 메일이 전송되므로,
    // 실제 거래처의 메일주소가 기재되지 않도록 주의
    $Cashbill->email = 'test@test.com';

    // 주문자 휴대폰
    $Cashbill->hp = '010-111-222';

    // 발행시 알림문자 전송여부
    $Cashbill->smssendYN = false;

    try {
        $result = $CashbillService->RegistIssue($testCorpNum, $Cashbill, $memo, $testUserID, $emailSubject);
        $code = $result->code;
        $message = $result->message;
        $confirmNum = $result->confirmNum;
        $tradeDate = $result->tradeDate;
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
                <legend>현금영수증 즉시발행</legend>
                <ul>
                    <li>Response.code : <?php echo $code ?></li>
                    <li>Response.message : <?php echo $message ?></li>
                    <?php
                      if ( isset($confirmNum) ) {
                    ?>
                      <li>Response.confirmNum : <?php echo $confirmNum ?></li>
                      <li>Response.tradeDate : <?php echo $tradeDate ?></li>
                    <?php
                      }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

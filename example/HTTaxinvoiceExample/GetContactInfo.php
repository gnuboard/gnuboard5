<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 연동회원 사업자번호에 등록된 담당자(팝빌 로그인 계정) 정보를 확인합니다.
     * - https://docs.popbill.com/httaxinvoice/php/api#GetContactInfo
     */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 확인할 담당자 아이디
    $contactID = "linkhub111";

    try {
        $result = $HTTaxinvoiceService->GetContactInfo($testCorpNum, $contactID);
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
                <legend>담당자 정보 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                    ?>
                            <li>id(아이디) : <?php echo $result->id ; ?></li>
                            <li>personName(담당자 성명) : <?php echo $result->personName ; ?></li>
                            <li>email(담당자 이메일) : <?php echo $result->email ; ?></li>
                            <li>hp(담당자 휴대폰번호) : <?php echo $result->hp ; ?></li>
                            <li>fax(담당자 팩스번호) : <?php echo $result->fax ; ?></li>
                            <li>tel(담당자 연락처) : <?php echo $result->tel ; ?></li>
                            <li>regDT(등록일시) : <?php echo $result->regDT ; ?></li>
                            <li>searchRole(담당자 권한) : <?php echo $result->searchRole ; ?></li>
                            <li>mgrYN(관리자 여부) : <?php echo $result->mgrYN ; ?></li>
                            <li>state(상태) : <?php echo $result->state ; ?></li>
                    <?php
                        }
                    ?>
                </ul>
            </fieldset>
         </div>
    </body>
</html>

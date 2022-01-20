<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 팝빌에 등록한 연동회원의 카카오톡 발신번호 목록을 확인합니다.
     * - https://docs.popbill.com/kakao/php/api#GetSenderNumberList
     */

    include 'common.php';

    // 팝빌회원 사업자번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    try {
        $result = $KakaoService->GetSenderNumberList($testCorpNum);
    } catch (PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }
?>
<body>
<div id="content">
    <p class="heading1">Response</p>
    <br/>
    <fieldset class="fieldset1">
        <legend>발신번호 목록 확인</legend>

        <?php
        if (isset($result)) {
            for ($i = 0; $i < Count($result); $i++) {
         ?>
                <fieldset class="fieldset2">
                    <ul>
                        <li> 발신번호(number) : <?php echo $result[$i]->number ?></li>
                        <li> 대표번호 지정여부(representYN) : <?php echo $result[$i]->representYN ? 'true' : 'false' ?></li>
                        <li> 등록상태 (state) : <?php echo $result[$i]->state ?></li>
                        <li> 메모 (memo) : <?php echo $result[$i]->memo ?></li>
                    </ul>
                </fieldset>

        <?php
            }
        } else {
        ?>
            <li>Response.code : <?php echo $code ?> </li>
            <li>Response.message : <?php echo $message ?></li>
        <?php
        }
        ?>

    </fieldset>
</div>
</body>
</html>

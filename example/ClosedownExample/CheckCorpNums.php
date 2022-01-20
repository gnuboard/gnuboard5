<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="../Example.css" media="screen"/>
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 다수건의 사업자번호에 대한 휴폐업정보를 확인합니다. (최대 1,000건)
     * - https://docs.popbill.com/closedown/php/api#CheckCorpNums
     */

    include 'common.php';

    //팝빌회원 사업자번호
    $MemberCorpNum = "1234567890";

    // 조회할 사업자번호 배열, 최대 1000건
    $CorpNumList = array(
        "1234567890",
        "6798700433",
        "123-12-12312",
    );

    try {
        $result = $ClosedownService->checkCorpNums($MemberCorpNum, $CorpNumList);
    } catch (PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }

?>
<body>
<div id="content">
    <p class="heading1">Response</p>
    <br/>
    <?php
    if (isset($code)) {
        ?>

        <fieldset class="fieldset2">
            <ul>
                <li>Response.code : <?php echo $code ?> </li>
                <li>Response.message : <?php echo $message ?></li>
            </ul>
        </fieldset>
        <?php
    } else {
        ?>
        <p class="info">> state (휴폐업상태) : null-알수없음, 0-등록되지 않은 사업자번호, 1-사업중, 2-폐업, 3-휴업</p>
        <p class="info">> taxType (사업 유형) : null-알수없음, 10-일반과세자, 20-면세과세자, 30-간이과세자, 31-간이과세자(세금계산서 발급사업자), 40-비영리법인, 국가기관</p>
        <br/>
        <?php
        for ($i = 0; $i < Count($result); $i++) {
            ?>
            <fieldset class="fieldset2">
                <legend>휴폐업조회 결과 [ <?php echo $i + 1 ?> ]</legend>
                <ul>
                    <li>사업자번호(corpNum) : <?php echo $result[$i]->corpNum ?></li>
                    <li>사업자유형(taxType) : <?php echo $result[$i]->taxType ?></li>
                    <li>휴폐업상태(state) : <?php echo $result[$i]->state ?></li>
                    <li>휴폐업일자(stateDate) : <?php echo $result[$i]->stateDate ?></li>
                    <li>과세유형 전환일자(typeDate) : <?php echo $result[$i]->typeDate ?></li>
                    <li>국세청 확일일자(checkDate) : <?php echo $result[$i]->checkDate ?></li>
                </ul>
            </fieldset>
            <?php
        }
    }
    ?>
</div>
</body>
</html>

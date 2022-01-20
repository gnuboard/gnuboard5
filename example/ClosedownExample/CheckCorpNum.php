<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="../Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
     * 사업자번호 1건에 대한 휴폐업정보를 확인합니다.
     * - https://docs.popbill.com/closedown/php/api#CheckCorpNum
     */

    include 'common.php';

    if ( isset($_GET['CorpNum']) && $_GET['CorpNum'] != '' ) {

        // 팝빌회원 사업자번호
        $MemberCorpNum = "1234567890";

        // 조회 사업자번호
        $CheckCorpNum = $_GET['CorpNum'];

        try {
            $result = $ClosedownService->checkCorpNum($MemberCorpNum, $CheckCorpNum);
        }
        catch (PopbillException $pe) {
            $code = $pe->getCode();
            $message = $pe->getMessage();
        }
    }
?>
    <body>
        <div id="content">
            <p class="heading1">Response</p>
            <br/>
            <fieldset class="fieldset1">
                <legend>휴폐업조회 - 단건</legend>
                    <div class ="fieldset4">
                    <form method= "GET" id="corpnum_form" action="CheckCorpNum.php">
                        <input class= "txtCorpNum left" type="text" placeholder="사업자번호 기재" id="CorpNum" name="CorpNum" value ='<?php echo (isset($result->corpNum) ? $result->corpNum : "") ?>' tabindex=1/>
                        <p class="find_btn find_btn01 hand" onclick="search()" tabindex=2>조회</p>
                    </form>
                    </div>
            </fieldset>
            <?php
                if(isset($result)) {
            ?>
                <fieldset class="fieldset2">
                    <legend>휴폐업조회 - 단건</legend>
                    <ul>
                        <li>사업자번호(corpNum) : <?php echo $result->corpNum?></li>
                        <li>사업자유형(taxType) : <?php echo $result->taxType?></li>
                        <li>휴폐업상태(state) : <?php echo $result->state?></li>
                        <li>휴폐업일자(stateDate) : <?php echo $result->stateDate?></li>
                        <li>과세유형 전환일자(typeDate) : <?php echo $result->typeDate?></li>
                        <li>국세청 확일일자(checkDate) : <?php echo $result->checkDate?></li>
                    </ul>
                    <p class="info">> state (휴폐업상태) : null-알수없음, 0-등록되지 않은 사업자번호, 1-사업중, 2-폐업, 3-휴업</p>
                    <p class="info">> taxType (사업 유형) : null-알수없음, 10-일반과세자, 20-면세과세자, 30-간이과세자, 31-간이과세자(세금계산서 발급사업자), 40-비영리법인, 국가기관</p>
                    <br/>
                </fieldset>

            <?php
                } if(isset($code)) {
            ?>

                <fieldset class="fieldset2">
                    <legend>휴폐업조회 - 단건</legend>
                    <ul>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    </ul>
                </fieldset>
            <?php
                }
            ?>

         </div>

          <script type ="text/javascript">
         window.onload=function(){
             document.getElementById('CorpNum').focus();
         }

         function search(){
            document.getElementById('corpnum_form').submit();
         }

         </script>
    </body>
</html>

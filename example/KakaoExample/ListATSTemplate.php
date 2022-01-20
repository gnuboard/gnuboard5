<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
        <title>팝빌 SDK PHP 5.X Example.</title>
    </head>
<?php
    /**
    * 승인된 알림톡 템플릿 목록을 확인합니다.
    * - https://docs.popbill.com/kakao/php/api#ListATSTemplate
    */

    include 'common.php';

    // 팝빌회원 사업자번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    try {
        $result = $KakaoService->ListATSTemplate($testCorpNum);
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
                <legend>알림톡 템플릿 목록 확인</legend>
                <ul>
                    <?php
                        if ( isset($code) ) {
                    ?>
                        <li>Response.code : <?php echo $code ?> </li>
                        <li>Response.message : <?php echo $message ?></li>
                    <?php
                        } else {
                            for ($i = 0; $i < Count($result); $i++) {
                    ?>
                        <fieldset class="fieldset2">
                            <legend> 알림톡 템플릿 목록 [<?php echo $i + 1 ?>]</legend>
                            <ul>
                                <li>templateCode (템플릿 코드) : <?php echo $result[$i]->templateCode ?></li>
                                <li>templateName (템플릿 제목) : <?php echo $result[$i]->templateName ?></li>
                                <li>template (템플릿 내용) : <?php echo $result[$i]->template ?></li>
                                <li>ads (광고메시지 내용) : <?php echo $result[$i]->ads ?></li>
                                <li>appendix (부가메시지 내용) : <?php echo $result[$i]->appendix ?></li>
                                <li>plusFriendID (카카오톡채널 아이디) : <?php echo $result[$i]->plusFriendID ?></li>
                            </ul>
                            <?php
                            if (isset($result[$i]->btns)) {
                                for ($j = 0; $j < Count($result[$i]->btns); $j++) {
                                    ?>
                                    <legend> 버튼정보 [<?php echo $j + 1 ?>]</legend>
                                    <ul>
                                        <li>t (버튼유형) : <?php echo $result[$i]->btns[$j]->t ?></li>
                                        <li>n (버튼명) : <?php echo $result[$i]->btns[$j]->n ?></li>
                                        <li>u1 (버튼링크1) : <?php echo (isset($result[$i]->btns[$j]->u1)) ? $result[$i]->btns[$j]->u1 : ''; ?></li>
                                        <li>u2 (버튼링크2) : <?php echo (isset($result[$i]->btns[$j]->u2)) ? $result[$i]->btns[$j]->u2 : ''; ?></li>
                                    </ul>
                                    <?php
                                }
                            }
                            ?>
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

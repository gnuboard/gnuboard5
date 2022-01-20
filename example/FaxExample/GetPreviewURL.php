<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <link rel="stylesheet" type="text/css" href="/Example.css" media="screen"/>
    <title>팝빌 SDK PHP 5.X Example.</title>
</head>
<?php
    /**
     * 팩스 미리보기 팝업 URL을 반환하며, 팩스전송을 위한 TIF 포맷 변환 완료 후 호출 할 수 있습니다.
     * - 반환되는 URL은 보안 정책상 30초 동안 유효하며, 시간을 초과한 후에는 해당 URL을 통한 페이지 접근이 불가합니다.
     * - https://docs.popbill.com/fax/php/api#GetPreviewURL
     */

    include 'common.php';

    // 팝빌 회원 사업자 번호, "-"제외 10자리
    $testCorpNum = '1234567890';

    // 팩스전송 접수번호
    $ReceiptNum = '021102609383900001';

    // 팝빌 회원 아이디
    $testUserID = 'testkorea';

    try {
        $url = $FaxService->GetPreviewURL($testCorpNum,$ReceiptNum,$testUserID);
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
        <legend>팩스 미리보기 팝업 URL</legend>
        <ul>
          <?php
          if (isset($url)) {
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

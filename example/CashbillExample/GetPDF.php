<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="/Example.css" media="screen" />
		<title>팝빌 SDK PHP 5.X Example.</title>
	</head>
<?php
    /**
     * 현금영수증 PDF 파일을 byte 배열로 반환합니다.
     * - https://docs.popbill.com/cashbill/php/api#GetPDF
     */

    include 'common.php';

    // 팝빌 회원 사업자 번호, '-'제외 10자리
    $testCorpNum = '1234567890';

    // 문서번호
    $mgtKey = '20210801-001';

    // PDF 파일경로, PDF 파일을 저장할 폴더에 777 권한 필요.
    $pdfFilePath = './'.$mgtKey.'.pdf';

    try {
        $bytes = $CashbillService->GetPDF($testCorpNum, $mgtKey);
    }
    catch(PopbillException $pe) {
        $code = $pe->getCode();
        $message = $pe->getMessage();
    }

    if(file_put_contents( $pdfFilePath, $bytes )){
      $code = 1;
      $message = $pdfFilePath;
    };

?>
  <body>
    <div id="content">
      <p class="heading1">Response</p>
      <br/>
      <fieldset class="fieldset1">
        <legend>현금영수증 PDF 저장 </legend>
        <ul>
          <li>Response.code : <?php echo $code ?></li>
          <li>Response.message : <?php echo $message ?></li>
        </ul>
      </fieldset>
     </div>
  </body>
</html>

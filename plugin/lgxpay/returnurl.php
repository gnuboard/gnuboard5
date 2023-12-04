<?php
include_once('./_common.php');

/*
  payreq_crossplatform 에서 세션에 저장했던 파라미터 값이 유효한지 체크
  세션 유지 시간(로그인 유지시간)을 적당히 유지 하거나 세션을 사용하지 않는 경우 DB처리 하시기 바랍니다.
*/
if(!isset($_SESSION['lgd_certify'])){
    echo '세션이 만료 되었거나 유효하지 않은 요청 입니다.';
    return;
}

$payReqMap = $_SESSION['lgd_certify'];//결제 요청시, Session에 저장했던 파라미터 MAP
?>
<html>
<head>
	<script type="text/javascript">

		function setLGDResult() {
                
                <?php if ( G5_IS_MOBILE ) {  // 모바일이면 ?>
                
                var lgd_form = document.getElementById('LGD_RETURNINFO');
                
                lgd_form.action = "AuthOnlyRes.php";
                lgd_form.submit();

                <?php } else {  // PC 이면 ?>
                parent.payment_return();
                <?php } ?>
			try {
			} catch (e) {
				alert(e.message);
			}
		}

	</script>
</head>
<body onload="setLGDResult()">
<?php
  $LGD_RESPCODE = isset($_POST['LGD_RESPCODE']) ? clean_xss_tags($_POST['LGD_RESPCODE']) : '';
  $LGD_RESPMSG 	= isset($_POST['LGD_RESPMSG']) ? clean_xss_tags(iconv("EUC-KR", "UTF-8", $_POST['LGD_RESPMSG'])) : '';
  $LGD_AUTHONLYKEY		= "";	
  $LGD_PAYTYPE			= "";

  $payReqMap['LGD_RESPCODE'] = $LGD_RESPCODE;
  $payReqMap['LGD_RESPMSG']	 = $LGD_RESPMSG;

  if($LGD_RESPCODE == "0000"){
	  $payReqMap['LGD_AUTHONLYKEY'] = isset($_POST['LGD_AUTHONLYKEY']) ? clean_xss_tags($_POST['LGD_AUTHONLYKEY']) : '';
	  $payReqMap['LGD_PAYTYPE'] 	= isset($_POST['LGD_PAYTYPE']) ? clean_xss_tags($_POST['LGD_PAYTYPE']) : '';
  }
  else{
	  echo "LGD_RESPCODE:" . $LGD_RESPCODE . " ,LGD_RESPMSG:" . $LGD_RESPMSG; //인증 실패에 대한 처리 로직 추가
  }
?>
<form method="post" name="LGD_RETURNINFO" id="LGD_RETURNINFO">
<?php
	  foreach ($payReqMap as $key => $value) {
        $key = htmlspecialchars(strip_tags($key));
        $value = htmlspecialchars(strip_tags($value));
      echo "<input type='hidden' name='$key' id='$key' value='$value'>";
    }
?>
</form>
</body>
</html>
<?php
include_once('./_common.php');

/*
xpay_approval.php 에서 세션에 저장했던 파라미터 값이 유효한지 체크
세션 유지 시간(로그인 유지시간)을 적당히 유지 하거나 세션을 사용하지 않는 경우 DB처리 하시기 바랍니다.
*/

if(!isset($_SESSION['PAYREQ_MAP'])){
    alert_close('세션이 만료 되었거나 유효하지 않은 요청 입니다.');
}

$payReqMap = $_SESSION['PAYREQ_MAP']; //결제 요청시, Session에 저장했던 파라미터 MAP

$g5['title'] = 'LG 유플러스 eCredit서비스 결제';
$g5['body_script'] = 'onload="setLGDResult();"';
include_once(G5_PATH.'/head.sub.php');

$LGD_RESPCODE = $_REQUEST['LGD_RESPCODE'];
$LGD_RESPMSG  = $_REQUEST['LGD_RESPMSG'];
$LGD_PAYKEY   = '';

if($LGD_RESPCODE == '0000') {
    $LGD_PAYKEY                = $_REQUEST['LGD_PAYKEY'];
    $payReqMap['LGD_RESPCODE'] = $LGD_RESPCODE;
    $payReqMap['LGD_RESPMSG']  = $LGD_RESPMSG;
    $payReqMap['LGD_PAYKEY']   = $LGD_PAYKEY;
} else {
    alert_close('LGD_RESPCODE:' . $LGD_RESPCODE . ' ,LGD_RESPMSG:' . $LGD_RESPMSG); //인증 실패에 대한 처리 로직 추가
}
?>

<script type="text/javascript">
function setLGDResult() {
    var of = window.opener.document.forderform;

    of.res_cd.value     = "<?php echo $LGD_RESPCODE; ?>";
    of.LGD_PAYKEY.value = "<?php echo $LGD_PAYKEY; ?>";

    window.opener.forderform_check();
    window.close();
}
</script>

<?php
include_once(G5_PATH.'/tail.sub.php');
?>
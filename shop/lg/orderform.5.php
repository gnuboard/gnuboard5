<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>

<!--  xpay.js는 반드시 body 밑에 두시기 바랍니다. -->
<!--  UTF-8 인코딩 사용 시는 xpay.js 대신 xpay_utf-8.js 을  호출하시기 바랍니다.-->
<script language="javascript" src="<?php echo (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? 'https' : 'http'; ?>://xpay.uplus.co.kr<?php echo ($CST_PLATFORM == 'test') ? ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=='on') ? ':7443' : ':7080') : ''; ?>/xpay/js/xpay_ub_utf-8.js" type="text/javascript"></script>
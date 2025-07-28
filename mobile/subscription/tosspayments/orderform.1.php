<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

include_once(G5_SUBSCRIPTION_PATH.'/tosspayments/orderform.1.php');
?>

<form name="pay_form" method="POST">
<input type="hidden" name="gopaymethod" value="Card">
<input type="hidden" name="good_mny" value="">
</form>
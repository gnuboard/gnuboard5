<?php
include_once('./_common.php');

$g4['title'] = '장바구니';
include_once('./_head.php');
?>

<img src="<?php echo G4_SHOP_URL; ?>/img/top_cart.gif" border="0"><p>

<?php
$s_page = 'cart.php';
$s_uq_id = get_session('ss_uq_id');
include G4_SHOP_PATH.'/cartsub.inc.php';
?>
<br><br>

<?php
include_once('./_tail.php');
?>
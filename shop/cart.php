<?php
include_once('./_common.php');

if (G4_IS_MOBILE) {
    include_once(G4_MSHOP_PATH.'/cart.php');
    return;
}

$g4['title'] = '장바구니';
include_once('./_head.php');
?>

<div id="sod_bsk">

    <?php
    $s_page = 'cart.php';
    $s_uq_id = get_session('ss_uq_id');
    include G4_SHOP_PATH.'/cartsub.inc.php';
    ?>

</div>

<?php
include_once('./_tail.php');
?>
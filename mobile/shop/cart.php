<?php
include_once('./_common.php');

$g4['title'] = '장바구니';
include_once(G4_MSHOP_PATH.'/_head.php');
?>

<div id="sod_bsk">

    <?php
    $s_page = 'cart.php';
    $s_uq_id = get_session('ss_uq_id');
    include G4_MSHOP_PATH.'/cartsub.inc.php';
    ?>

</div>

<?php
include_once(G4_MSHOP_PATH.'/_tail.php');
?>
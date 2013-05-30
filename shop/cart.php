<?php
include_once('./_common.php');

// 회원일 경우 자신의 장바구니 상품 uq_id 값을 변경
if($is_member) {
    $tmp_uq_id = get_session('ss_uq_id');
    if(!$tmp_uq_id) {
        $tmp_uq_id = get_uniqid();
        set_session('ss_uq_id', $tmp_uq_id);
    }

    $ctime = date('Y-m-d H:i:s', (G4_SERVER_TIME - ($default['de_cart_keep_term'] * 86400)));
    $sql = " update {$g4['shop_cart_table']}
                set uq_id = '$tmp_uq_id'
                where mb_id = '{$member['mb_id']}'
                  and ct_status = '쇼핑'
                  and ct_time > '$ctime' ";
    sql_query($sql);
}

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
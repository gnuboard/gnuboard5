<?php
include_once(G5_LIB_PATH.'/kcp_rest.lib.php');
if (!defined('_GNUBOARD_')) exit;
if (!(kcp_rest_enabled())) return;
?>
<script>
// 인증창을 열기 전에 주문 상태를 저장하여 PC에서도 승인 잠금/복구를 적용한다.
function g5_kcp_rest_save(form) {
    var error = '결제 요청을 저장하지 못했습니다.';
    jQuery.ajax({
        type: 'POST', url: g5_url + '/shop/ajax.orderdatasave.php',
        data: jQuery(form).serialize(), cache: false, async: false,
        success: function(data, status, xhr) { error = data || g5_order_state_accept(xhr); }
    });
    if (error) { alert(error); return false; }
    return true;
}
</script>

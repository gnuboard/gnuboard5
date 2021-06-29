<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

?>
<script>
function global_m_hp_form_submit(pf){
    var global_m_hp_form = document.global_m_hp_form,
        od_settle_case = jQuery("input[name='od_settle_case']:checked").val();

    if( ! jQuery("form[name='sm_form']").length ){
        alert("해당 폼이 존재 하지 않는 결제오류입니다.");
        return false;
    }
    global_m_hp_form.good_name.value = "<?php echo $goods; ?>"; 
    global_m_hp_form.good_mny.value = document.sm_form.good_mny.value; 
    global_m_hp_form.good_info.value = "<?php echo $good_info; ?>";
    global_m_hp_form.settle_method.value = od_settle_case;

    global_m_hp_form.buyr_name.value = pf.od_name.value;
    global_m_hp_form.buyr_mail.value = pf.od_email.value;
    global_m_hp_form.buyr_tel1.value = pf.od_tel.value;
    global_m_hp_form.buyr_tel2.value = pf.od_hp.value;
    global_m_hp_form.rcvr_name.value = pf.od_b_name.value;
    global_m_hp_form.rcvr_tel1.value = pf.od_b_tel.value;
    global_m_hp_form.rcvr_tel2.value = pf.od_b_hp.value;
    global_m_hp_form.rcvr_mail.value = pf.od_email.value;
    global_m_hp_form.rcvr_zipx.value = pf.od_b_zip.value;
    global_m_hp_form.rcvr_add1.value = pf.od_b_addr1.value;
    global_m_hp_form.rcvr_add2.value = pf.od_b_addr2.value;

    // 주문 정보 임시저장
    var order_data = $(pf).serialize();
    var save_result = "";
    $.ajax({
        type: "POST",
        data: order_data,
        url: g5_url+"/shop/ajax.orderdatasave.php",
        cache: false,
        async: false,
        success: function(data) {
            save_result = data;
        }
    });

    if(save_result) {
        alert(save_result);
        return false;
    }

    global_m_hp_form.submit();

    return false;
}
</script>
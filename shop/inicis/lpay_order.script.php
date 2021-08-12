<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//삼성페이 또는 L.pay 또는 이니시스 카카오페이 사용시에만 해당함
if( ! ($default['de_inicis_lpay_use'] || $default['de_inicis_kakaopay_use']) || ('inicis' == $default['de_pg_service']) ){    //PG가 이니시스인 경우 아래 내용 사용 안함
    return;
}
?>
<script>
jQuery(function($){
    $( document ).ready(function() {
        var pf = document.forderform;

        $(pf).on("form_sumbit_order_samsungpay", inicis_pay_form_submit);
        
        function inicis_pay_form_submit(){
            var $form = $(this),
                pf = $form[0],
                inicis_pay_form = document.inicis_pay_form,
                inicis_settle_case = jQuery("input[name='od_settle_case']:checked").val();

            inicis_pay_form.gopaymethod.value = (inicis_settle_case === "inicis_kakaopay") ? "onlykakaopay" : "onlylpay";
            inicis_pay_form.acceptmethod.value = "cardonly";
            
            inicis_pay_form.price.value = inicis_pay_form.good_mny.value = pf.good_mny.value;
            inicis_pay_form.goodname.value = pf.od_goods_name.value;

            inicis_pay_form.buyername.value   = pf.od_name.value;
            inicis_pay_form.buyeremail.value  = pf.od_email.value;
            inicis_pay_form.buyertel.value    = pf.od_hp.value ? pf.od_hp.value : pf.od_tel.value;
            inicis_pay_form.recvname.value    = pf.od_b_name.value;
            inicis_pay_form.recvtel.value     = pf.od_b_hp.value ? pf.od_b_hp.value : pf.od_b_tel.value;
            inicis_pay_form.recvpostnum.value = pf.od_b_zip.value;
            inicis_pay_form.recvaddr.value    = pf.od_b_addr1.value + " " +pf.od_b_addr2.value;

            <?php if($default['de_tax_flag_use']) { ?>
                inicis_pay_form.comm_tax_mny.value = pf.comm_tax_mny.value;
                inicis_pay_form.comm_vat_mny.value = pf.comm_vat_mny.value;
                inicis_pay_form.comm_free_mny.value = pf.comm_free_mny.value;
                inicis_pay_form.tax.value = pf.comm_vat_mny.value;
                inicis_pay_form.taxfree.value = pf.comm_free_mny.value;
            <?php } ?>

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

            if(!make_signature(inicis_pay_form))
                return false;

            setTimeout(function(){
                paybtn(inicis_pay_form);
            }, 1);

            return false;
        }
    });
});
</script>
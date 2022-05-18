<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// 타 PG 사용시 NHN KCP 네이버페이 사용이 설정되어 있는지 체크, 그렇지 않다면 return;
if( !(function_exists('is_use_easypay') && is_use_easypay('global_nhnkcp')) ){
    return;
}
?>
<script>
jQuery(function($){
    $( document ).ready(function() {
        var pf = document.forderform;
        
        // NHN_KCP를 통한 네이버페이가 실행됨
        $(pf).on("form_sumbit_order_nhnkcp_naverpay", nhnkcp_naverpay_form_submit);

        function nhnkcp_naverpay_form_submit(){
            var $form = $(this),
                pf = $form[0],
                nhnkcp_pay_form = document.nhnkcp_pay_form,
                nhnkcp_settle_case = jQuery("input[name='od_settle_case']:checked").attr("data-pay"),
                od_settle_case = jQuery("input[name='od_settle_case']:checked").val();

            if( nhnkcp_settle_case == "naverpay" ){
                if(typeof nhnkcp_pay_form.naverpay_direct !== "undefined") nhnkcp_pay_form.naverpay_direct.value = "Y";
            }

            if( ! jQuery("form[name='sm_form']").length ){
                alert("해당 폼이 존재 하지 않는 결제오류입니다.");
                return false;
            }

            if (document.sm_form.good_mny.value < 1000) {
                <?php // 간편결제수단은 신용카드처럼 취급하며 금액은 1000원 이상이므로, 1000원 이상이 아니면 PG사에서 승인하지 않는다. ?>
                alert("간편결제는 1000원 이상 결제가 가능합니다.");
                return false;
            }

            nhnkcp_pay_form.good_mny.value = document.sm_form.good_mny.value; 
            nhnkcp_pay_form.good_info.value = "<?php echo $good_info; ?>";
            nhnkcp_pay_form.settle_method.value = od_settle_case;
            nhnkcp_pay_form.nhnkcp_pay_case.value = nhnkcp_settle_case;

            if(typeof pf.nhnkcp_pay_case !== "undefined") pf.nhnkcp_pay_case.value = nhnkcp_settle_case;

            nhnkcp_pay_form.buyr_name.value = pf.od_name.value;
            nhnkcp_pay_form.buyr_mail.value = pf.od_email.value;
            nhnkcp_pay_form.buyr_tel1.value = pf.od_tel.value;
            nhnkcp_pay_form.buyr_tel2.value = pf.od_hp.value;
            nhnkcp_pay_form.rcvr_name.value = pf.od_b_name.value;
            nhnkcp_pay_form.rcvr_tel1.value = pf.od_b_tel.value;
            nhnkcp_pay_form.rcvr_tel2.value = pf.od_b_hp.value;
            nhnkcp_pay_form.rcvr_mail.value = pf.od_email.value;
            nhnkcp_pay_form.rcvr_zipx.value = pf.od_b_zip.value;
            nhnkcp_pay_form.rcvr_add1.value = pf.od_b_addr1.value;
            nhnkcp_pay_form.rcvr_add2.value = pf.od_b_addr2.value;

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

            nhnkcp_pay_form.submit();

            return false;
        }
    });
});
</script>
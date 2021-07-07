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
        
        $(pf).on("form_sumbit_order_nhnkcp_naverpay", nhnkcp_naverpay_form_submit);
        
        function nhnkcp_naverpay_form_submit(){
            var $form = $(this),
                pf = $form[0],
                nhnkcp_pay_form = document.nhnkcp_pay_form,
                nhnkcp_settle_case = jQuery("input[name='od_settle_case']:checked").attr("data-pay"),
                od_settle_case = jQuery("input[name='od_settle_case']:checked").val();

            if (pf.good_mny.value < 1000) {
                <?php // 간편결제수단은 신용카드처럼 취급하며 금액은 1000원 이상이므로, 1000원 이상이 아니면 PG사에서 승인하지 않는다. ?>
                alert("간편결제는 1000원 이상 결제가 가능합니다.");
                return false;
            }

            if( nhnkcp_settle_case == "naverpay" ){
                if(typeof nhnkcp_pay_form.naverpay_direct !== "undefined") nhnkcp_pay_form.naverpay_direct.value = "Y";
            }
            
            nhnkcp_pay_form.pay_method.value = "100000000000";
            nhnkcp_pay_form.good_mny.value = pf.good_mny.value;
            nhnkcp_pay_form.good_name.value = pf.od_goods_name.value;
            nhnkcp_pay_form.good_info.value = "<?php echo $good_info; ?>";
            nhnkcp_pay_form.od_settle_case.value = od_settle_case;
            nhnkcp_pay_form.nhnkcp_pay_case.value = nhnkcp_settle_case;

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
            
            $("input,select,textarea", this).each(
                function(index){
                    var $clone_el = $(this).clone(),
                        clone_el_name = $clone_el.attr("name"),
                        clone_name_attr = "[name='"+clone_el_name+"']";
                    
                    if( $clone_el.prop("type") == "radio" || $clone_el.prop("type") == "checkbox" ){
                        if( $clone_el.attr("checked") != "checked" ){
                            clone_el_name = "";
                        }
                    }

                    if( clone_el_name && ! (/^(LGD_|CST_|it_price|cp_price|requestByJs|timestamp|signature|returnUrl|mKey|charset|payViewType|closeUrl|popupUrl|quotabase|tax|od_settle_case)/i.test(clone_el_name)) ){
                        if( $(nhnkcp_pay_form).find(clone_name_attr).length ){
                            $(nhnkcp_pay_form).find(clone_name_attr).val( $clone_el.val() );
                        } else {
                            $(nhnkcp_pay_form).append($clone_el);
                        }
                    }
                }
            );
            
            jsf__pay( nhnkcp_pay_form );

            return false;
        }

    });
});
</script>
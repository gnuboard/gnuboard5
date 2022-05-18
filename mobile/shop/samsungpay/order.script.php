<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//삼성페이 또는 L.pay 사용시에만 해당함
if( ! is_inicis_simple_pay() || ('inicis' == $default['de_pg_service']) ){    //PG가 이니시스인 경우 아래 내용 사용 안함
    return;
}
?>
<script>
jQuery(function($){
    $( document ).ready(function() {
        var pf = document.forderform;
        
        // 이후에 Lpay 가 추가됨
        $(pf).on("form_sumbit_order_samsungpay", samsungpay_form_submit);

        function samsungpay_form_submit(){
            var $form = $(this),
                pf = $form[0],
                samsungpayform = document.samsungpay_form;

            var paymethod = "";
            var width = 330;
            var height = 480;
            var xpos = (screen.width - width) / 2;
            var ypos = (screen.width - height) / 2;
            var position = "top=" + ypos + ",left=" + xpos;
            var features = position + ", width=320, height=440";
            var p_reserved = samsungpayform.DEF_RESERVED.value;
            samsungpayform.P_RESERVED.value = p_reserved;

            paymethod = "wcard";

            if( typeof settle_method != "undefined" && settle_method == "inicis_kakaopay" ){   // L.pay 로 결제하는 경우
                samsungpayform.P_RESERVED.value = samsungpayform.P_RESERVED.value.replace("&useescrow=Y", "")+"&d_kakaopay=Y";
            } else if( typeof settle_method != "undefined" && settle_method == "lpay" ){   // L.pay 로 결제하는 경우
                samsungpayform.P_RESERVED.value = samsungpayform.P_RESERVED.value.replace("&useescrow=Y", "")+"&d_lpay=Y";
            } else {    // 그 외에는 삼성페이로 인식
                samsungpayform.P_RESERVED.value = samsungpayform.P_RESERVED.value.replace("&useescrow=Y", "")+"&d_samsungpay=Y";
            }

            if( ! jQuery("form[name='sm_form']").length ){
                alert("해당 폼이 존재 하지 않는 결제오류입니다.");
                return false;
            }

            samsungpayform.P_AMT.value = samsungpayform.good_mny.value = document.sm_form.good_mny.value; 
            samsungpayform.P_UNAME.value = pf.od_name.value;
            samsungpayform.P_MOBILE.value = pf.od_hp.value;
            samsungpayform.P_EMAIL.value = pf.od_email.value;

            <?php if($default['de_tax_flag_use']) { ?>
            samsungpayform.P_TAX.value = pf.comm_vat_mny.value;
            samsungpayform.P_TAXFREE = pf.comm_free_mny.value;
            <?php } ?>

            samsungpayform.P_RETURN_URL.value = "<?php echo $return_url.$od_id; ?>";
            samsungpayform.action = "https://mobile.inicis.com/smart/" + paymethod + "/";

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
                return;
            }

            samsungpayform.submit();

            return false;
        }
    });
});
</script>
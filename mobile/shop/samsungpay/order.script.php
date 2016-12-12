<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

//삼성페이 사용시에만 해당함
if( ! $default['de_samsung_pay_use'] || ('inicis' == $default['de_pg_service']) ){    //PG가 이니시스인 경우 아래 내용 사용 안함
    return;
}
?>
<script>
jQuery(function($){
    $( document ).ready(function() {
        var pf = document.forderform;

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
            samsungpayform.P_RESERVED.value = samsungpayform.P_RESERVED.value.replace("&useescrow=Y", "")+"&d_samsungpay=Y";

            samsungpayform.P_AMT.value = samsungpayform.good_mny.value;
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
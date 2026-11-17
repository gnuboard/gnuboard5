<?php
if (!defined('_GNUBOARD_')) exit;

add_javascript('<script src="https://paypro.inicis.com/std/payment/js/INIPayPro_v2.js" charset="UTF-8"></script>', 10);

if (G5_IS_MOBILE) {
?>
<form name="sm_form" method="post" action="">
<input type="hidden" name="good_mny" value="<?php echo isset($tot_price) ? (int) $tot_price : 0; ?>">
</form>
<?php
}
?>
<script type="text/javascript">
function inicis_pro_email_check()
{
    var field = jQuery('input[name=od_email], input[name=pp_email]').first();
    if (!field.length)
        return true;

    var email = String(field.val() || '').replace(/^\s+|\s+$/g, '');
    if (email === '')
        return true;

    if (email.length <= 60 && /^[0-9A-Za-z._%+-]+@([0-9A-Za-z-]+\.)+[A-Za-z]{2,}$/.test(email)) {
        var tld = email.substring(email.lastIndexOf('.') + 1).toLowerCase();
        if (tld !== 'test' && tld !== 'example' && tld !== 'invalid' && tld !== 'localhost' && tld !== 'local')
            return true;
    }

    alert('이메일 주소가 올바르지 않습니다. 이메일 주소를 다시 확인해 주십시오.');
    field.focus();
    return false;
}

function inicis_pro_pay(oid, deviceType)
{
    var requestData = null;
    var errorMessage = '';

    if (!inicis_pro_email_check())
        return false;

    jQuery.ajax({
        type: 'POST',
        url: g5_url + '/shop/inicis/pro/request.php',
        data: {oid: oid, device_type: deviceType},
        dataType: 'json',
        cache: false,
        async: false,
        success: function(data) {
            if (data && data.success) {
                requestData = data.request;
            } else {
                errorMessage = data && data.message ? data.message : '결제 요청 정보를 생성하지 못했습니다.';
            }
        },
        error: function() {
            errorMessage = '결제 요청 정보를 확인하는 중 통신 오류가 발생했습니다.';
        }
    });

    if (!requestData) {
        alert(errorMessage);
        return false;
    }

    if (typeof INIPayPro === 'undefined' || typeof INIPayPro.requestPayment !== 'function') {
        alert('KG이니시스 결제창을 불러오지 못했습니다. 잠시 후 다시 시도해 주십시오.');
        return false;
    }

    INIPayPro.requestPayment(requestData);
    return false;
}
</script>

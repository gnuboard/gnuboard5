<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

if (!empty($inicis_pro_use)) {
    require(G5_SHOP_PATH.'/inicis/pro/orderform.1.php');
    return;
}

// 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'] || $default['de_easy_pay_use']) {
    add_javascript('<script language="javascript" type="text/javascript" src="'.$stdpay_js_url.'" charset="UTF-8"></script>', 10);
?>

<script language=javascript>
function make_signature(frm)
{
    // 데이터 암호화 처리
    var result = false;
    $.ajax({
        url: g5_url+"/shop/inicis/makesignature.php",
        type: "POST",
        data: {
            oid : frm.oid.value,
            price : frm.good_mny.value
        },
        dataType: "json",
        async: false,
        cache: false,
        error: function() {
            alert("결제 서명을 생성하지 못했습니다. 다시 시도해 주십시오.");
        },
        success: function(data) {
            if(data && data.error === "" && data.sign && data.verification && data.timestamp && data.mKey) {
                frm.timestamp.value = data.timestamp;
                frm.signature.value = data.sign;
                frm.verification.value = data.verification;
                result = true;
                frm.mKey.value = data.mKey;
            } else {
                alert((data && data.error) || "결제 서명을 생성하지 못했습니다.");
                result = false;
            }
        }
    });

    return result;
}

function paybtn(f) {
    INIStdPay.pay(f.id);
}
</script>
<?php }

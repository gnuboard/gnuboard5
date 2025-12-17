<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

add_javascript('<script language="javascript" type="text/javascript" src="'.$stdpay_js_url.'" charset="UTF-8"></script>', 10);
?>
<script language=javascript>
function make_signature(frm)
{
    // 데이터 암호화 처리
    var result = true;
    $.ajax({
        url: g5_url+"/subscription/inicis/makesignature.php",
        type: "POST",
        data: {
            price : frm.good_mny.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            if(data.error == "") {
                frm.timestamp.value = data.timestamp;
                frm.signature.value = data.sign;
                frm.verification.value = data.sign2;
                frm.mKey.value = data.mKey;
            } else {
                alert(data.error);
                result = false;
            }
        }
    });

    return result;
}

function paybtn(f) {
    console.log(f.id);
    INIStdPay.pay(f.id);
}
</script>
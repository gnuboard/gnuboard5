<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가
?>
<div id="display_pay_button" class="btn_confirm">
    <span id="show_req_btn"><input type="button" name="submitChecked" onClick="pay_approval();" value="결제등록" class="btn_submit"></span>
    <span id="show_pay_btn" style="display:none;"><input type="button" onClick="forderform_check();" value="주문하기" class="btn_submit"></span>
    <a href="<?php echo G5_SHOP_URL; ?>" class="btn_cancel">취소</a>
</div>


<script type="text/javascript">
function make_signature(frm)
{
    // 데이터 암호화 처리
    var result = true;
    $.ajax({
        url: g5_url+"/shop/nicepay/makesignature.php",
        type: "POST",
        data: {
            ediDate : frm.EdiDate.value,
            price : frm.good_mny.value
        },
        dataType: "json",
        async: false,
        cache: false,
        success: function(data) {
            if(data.error == "") {
                frm.EncryptData.value = data.EncryptData;
            } else {
                alert(data.error);
                result = false;
            }
        }
    });

    return result;
}
//결제창 최초 요청시 실행됩니다.
function paybtn(f){
    goPay(f);
}

//결제 최종 요청시 실행됩니다. <<'nicepaySubmit()' 이름 수정 불가능>>
// function nicepaySubmit(){
//     $("#forderform").submit();
// }
</script>
<?php
if (!defined("_GNUBOARD_")) exit; // 개별 페이지 접근 불가

// nicepay 전자결제를 사용할 때만 실행
if($default['de_iche_use'] || $default['de_vbank_use'] || $default['de_hp_use'] || $default['de_card_use'] || $default['de_easy_pay_use']) {
    add_javascript('<script src="https://web.nicepay.co.kr/v3/webstd/js/nicepay-2.0.js" type="text/javascript"></script>')
?>

<script type="text/javascript">
function make_signature(frm)
{
    // 데이터 암호화 처리
    // form에 존재하는 EncryptData에 적용되어야할 signature 값을 생성
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
function nicepaySubmit(){
    $("#forderform").submit();
}

//결제창 종료 함수 <<'nicepayClose()' 이름 수정 불가능>>
function nicepayClose(){
    alert("결제가 취소 되었습니다");
}
</script>
<?php }
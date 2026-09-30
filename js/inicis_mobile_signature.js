/* 모바일 결제창 호출 직전에 최종 금액과 주문번호의 서버 해시를 발급한다. */
function inicis_mobile_signature(form) {
    var result = false;
    form.P_TIMESTAMP.value = '';
    form.P_CHKFAKE.value = '';
    jQuery.ajax({
        url: g5_url + '/mobile/shop/inicis/makesignature.php',
        type: 'POST',
        dataType: 'json',
        async: false,
        cache: false,
        data: {oid: form.P_OID.value, price: form.P_AMT.value},
        success: function(data) {
            if (data && data.error === '' && data.timestamp && data.hash) {
                form.P_TIMESTAMP.value = data.timestamp;
                form.P_CHKFAKE.value = data.hash;
                result = true;
            } else {
                alert(data && data.error ? data.error : '결제 해시를 생성하지 못했습니다.');
            }
        },
        error: function() {
            alert('결제 해시를 생성하지 못했습니다. 다시 시도해 주십시오.');
        }
    });
    return result;
}

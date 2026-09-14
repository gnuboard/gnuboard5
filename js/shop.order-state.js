/* 주문 임시저장 응답의 토큰은 현재 탭의 결제 폼에만 전달한다. */
function g5_order_state_accept(xhr) {
    var token = xhr.getResponseHeader('X-G5-Order-State');
    if (!token || !/^[0-9]{1,20}\.[a-f0-9]{64}$/.test(token)) {
        return '결제 요청을 저장하지 못했습니다. 다시 시도해 주십시오.';
    }
    ['forderform', 'sm_form', 'nhnkcp_pay_form'].forEach(function(name) {
        var form = document.forms[name];
        if (!form) return;
        var input = form.elements.g5_order_state;
        if (!input) {
            input = document.createElement('input');
            input.type = 'hidden'; input.name = 'g5_order_state'; form.appendChild(input);
        }
        input.value = token;
    });
    return '';
}

function g5_order_state_url(url, form) {
    var input = form.elements.g5_order_state;
    if (!input || !/^[0-9]{1,20}\.[a-f0-9]{64}$/.test(input.value)) {
        throw new Error('결제 요청을 다시 저장해 주십시오.');
    }
    return url + (url.indexOf('?') < 0 ? '?' : '&') + 'g5_order_state=' + encodeURIComponent(input.value);
}

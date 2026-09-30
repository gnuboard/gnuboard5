const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const root = path.resolve(__dirname, '..');

for (const file of ['shop/inicis/orderform.1.php', 'shop/inicis/lpay_form.1.php', 'js/inicis_mobile_signature.js']) {
    const mobile = file.endsWith('.js');
    let source = fs.readFileSync(path.join(root, file), 'utf8');
    if (!mobile) source = source.slice(source.indexOf('function make_signature(')).split('</script>')[0];
    let response;
    let fail;
    let alerts = [];
    let payload;
    const jq = {ajax(options) {
        payload = options.data;
        if (fail) options.error(); else options.success(response);
    }};
    const context = {jQuery: jq, $: jq, g5_url: 'https://shop.example', alert: m => alerts.push(m)};
    vm.createContext(context);
    vm.runInContext(source, context);
    const form = {};
    for (const key of ['oid','P_TIMESTAMP','P_CHKFAKE','P_OID','P_AMT','good_mny','timestamp','signature','verification','mKey'])
        form[key] = {value: 'stale'};
    form.oid.value = form.P_OID.value = '202609300001';
    form.P_AMT.value = form.good_mny.value = '1200';
    const sign = mobile ? context.inicis_mobile_signature : context.make_signature;
    for (response of [null, {}, {error:'키 없음'}, {error:'',timestamp:'123'}]) {
        assert.equal(sign(form), false, file + ': 불완전 응답 차단');
    }
    fail = true;
    assert.equal(sign(form), false, file + ': 통신 실패 차단');
    fail = false;
    response = {error:'',timestamp:'1760000000000',hash:'mobile-hash',sign:'signature',verification:'verification',mKey:'mKey'};
    assert.equal(sign(form), true, file + ': 정상 진행');
    assert.equal(payload.price, '1200');
    assert.equal(payload.oid, '202609300001');
    if (mobile) {
        assert.equal(payload.oid, '202609300001');
        assert.equal(form.P_CHKFAKE.value, 'mobile-hash');
        fail = true;
        assert.equal(sign(form), false);
        assert.equal(form.P_CHKFAKE.value, '', '재시도 실패 시 이전 해시 제거');
    } else {
        assert.equal(form.verification.value, 'verification');
    }
    assert.ok(alerts.length >= 5);
}
console.log('이니시스 PC·간편결제·모바일 서명 JavaScript 회귀 검증 통과');

// 개별 서명 함수뿐 아니라 실제 간편결제 이벤트의 최종 PG 요청을 검증한다.
for (const mobile of [false, true]) {
    const file = mobile ? 'mobile/shop/samsungpay/order.script.php' : 'shop/inicis/lpay_order.script.php';
    let source = fs.readFileSync(path.join(root, file), 'utf8').split('<script>')[1].split('</script>')[0];
    source = source.replace(/<\?php[\s\S]*?\?>/g, '');
    for (const mode of ['success', 'save_error', 'network_error', 'missing_token', 'sign_error']) {
        let handler, sent = 0, signed = 0;
        const form = () => new Proxy({submit() { sent++; }}, {get(target, key) {
            if (!(key in target)) target[key] = {value: '1200'};
            return target[key];
        }});
        const pf = form(), pay = form(), sm = form();
        pay.DEF_RESERVED.value = 'centerCd=Y&amt_hash=Y';
        pay.acceptmethod.value = 'HPP(2):centerCd(Y):useescrow';
        const document = {forderform: pf, inicis_pay_form: pay, samsungpay_form: pay, sm_form: sm};
        const jq = arg => {
            if (typeof arg === 'function') return arg(jq);
            if (arg === document) return {ready: fn => fn()};
            if (typeof arg === 'string') return {length: 1, val: () => 'lpay'};
            return {0: arg, on(event, fn) { handler = fn; }, serialize: () => 'order-data'};
        };
        jq.ajax = opts => {
            if (mode === 'network_error') return;
            opts.success(mode === 'save_error' ? '저장 실패' : '', 'success', {});
        };
        const sign = () => { signed++; return mode !== 'sign_error'; };
        const context = {jQuery: jq, document, screen: {width: 1024, height:768}, g5_url: '', settle_method: 'lpay',
            g5_order_state_accept: () => mode === 'missing_token' ? '토큰 누락' : '',
            alert() {}, make_signature: sign, inicis_mobile_signature: sign,
            setTimeout: fn => fn(), paybtn: () => sent++};
        vm.createContext(context);
        vm.runInContext(source, context);
        assert.equal(handler.call(pf), false, file + ': 실패 시 기본 PG로 진행 금지 (' + mode + ')');
        assert.equal(sent, mode === 'success' ? 1 : 0, file + ': PG 전송 횟수 (' + mode + ')');
        if (mode === 'success') {
            assert.ok(mobile ? pay.P_RESERVED.value.includes('centerCd=Y') : pay.acceptmethod.value.includes('centerCd(Y)'), file + ': IDC 옵션 보존');
        }
        if (['save_error','network_error','missing_token'].includes(mode)) assert.equal(signed, 0, '저장 실패 시 서명 금지');
    }
}
console.log('실제 간편결제 이벤트의 IDC 옵션 및 실패 차단 검증 통과');

// 실행: node tests/pg_easypay.js (실제 PG SDK 대신 요청을 기록한다)
const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const path = require('node:path');
const root = path.dirname(__dirname);
const providers = ['TOSSPAY', 'NAVERPAY', 'SAMSUNGPAY', 'APPLEPAY', 'LPAY', 'KAKAOPAY', 'PINPAY', 'PAYCO', 'SSG'];
function read(file) { return fs.readFileSync(path.join(root, file), 'utf8'); }
async function main() {
    for (const [orderFile, sdkFile] of [
        ['shop/orderform.sub.php', 'shop/toss/orderform.1.php'],
        ['mobile/shop/orderform.sub.php', 'mobile/shop/toss/toss_approval.php'],
    ]) {
        const source = read(orderFile);
        const start = source.indexOf("        f.cardflowMode.value = 'DEFAULT';");
        const end = source.indexOf("        f.amountCurrency.value", start);
        const selectionCode = source.slice(start, end).replace(/<\?php[\s\S]*?\?>/g, JSON.stringify(providers));
        let sdk = read(sdkFile);
        sdk = sdk.slice(sdk.indexOf('async function launchCrossPlatform'), sdk.indexOf('await payment.requestPayment')) + 'await payment.requestPayment(paymentOptions);\n}';
        sdk = sdk.replace(/<\?php[\s\S]*?\?>/g, 'https://example.test');
        const requests = [];
        let selected = '';
        const context = vm.createContext({
            payment: { requestPayment: async options => requests.push(options) },
            $: () => ({ attr: () => selected }),
            alert: message => { throw new Error(message); },
        });
        vm.runInContext(sdk + '\nfunction select(f, settle_method) {\n' + selectionCode + '\n}', context);
        const form = {};
        for (const key of ['amountValue', 'method', 'taxFreeAmount', 'orderId', 'orderName', 'customerEmail', 'customerName', 'customerMobilePhone', 'cardflowMode', 'cardeasyPay', 'cardUseCardPoint', 'cardUseAppCardOnly', 'cardUseEscrow', 'escrowProducts']) {
            form[key] = { value: '' };
        }
        form.amountValue.value = '10000';
        form.taxFreeAmount.value = '2000';
        form.method.value = 'CARD';
        form.cardUseEscrow.value = 'false';
        for (const provider of providers) {
            selected = provider;
            context.select(form, '간편결제');
            await context.launchCrossPlatform(form);
            const direct = requests.at(-1);
            assert.equal(direct.card.easyPay, provider);
            assert.equal(direct.card.flowMode, 'DIRECT');
            assert.equal(direct.amount.value, 10000);
            assert.equal(direct.taxFreeAmount, 2000);
            context.select(form, '신용카드');
            await context.launchCrossPlatform(form);
            const card = requests.at(-1);
            assert.equal(card.card.flowMode, 'DEFAULT');
            assert.equal('easyPay' in card.card, false);
        }
        selected = 'invalid';
        assert.throws(() => context.select(form, '간편결제'), /다시 선택/);
        // NICEPAY 선택 후 카드 재선택 시 직접 호출/에스크로 상태도 복원된다.
        const niceStart = source.indexOf('        f.DirectShowOpt.value = "";');
        let niceCode = source.slice(niceStart).replace(/<\?php[\s\S]*?\?>/g, '');
        const niceContext = vm.createContext({ jQuery: () => ({ attr: () => selected }) });
        // 모바일은 이 블록에 구매자 정보 복사 등이 이어지므로 switch 끝까지만 실행한다.
        const switchStart = niceCode.indexOf('switch(settle_method)');
        const braceStart = niceCode.indexOf('{', switchStart);
        let depth = 1, braceEnd = braceStart + 1;
        for (; depth && braceEnd < niceCode.length; braceEnd++) {
            if (niceCode[braceEnd] === '{') depth++;
            if (niceCode[braceEnd] === '}') depth--;
        }
        niceCode = niceCode.slice(0, braceEnd);
        vm.runInContext('function select(f, settle_method) {\n' + niceCode + '\n}', niceContext);
        const niceForm = {};
        for (const key of ['DirectShowOpt', 'DirectEasyPay', 'NicepayReserved', 'EasyPayMethod', 'TransType', 'PayMethod']) niceForm[key] = { value: '' };
        for (const [provider, field, code] of [
            ['nice_naverpay', 'DirectEasyPay', 'E020'], ['nice_samsungpay', 'DirectEasyPay', 'E021'],
            ['nice_kakaopay', 'NicepayReserved', 'DirectKakao=Y'], ['nice_paycopay', 'NicepayReserved', 'DirectPayco=Y'],
            ['nice_skpay', 'NicepayReserved', 'DirectPay11=Y'], ['nice_ssgpay', 'DirectEasyPay', 'E007'], ['nice_lpay', 'DirectEasyPay', 'E018'],
            ...(orderFile.startsWith('mobile') ? [['nice_applepay', 'DirectEasyPay', 'E022']] : []),
        ]) {
            selected = provider;
            niceContext.select(niceForm, '간편결제');
            assert.equal(niceForm[field].value, code);
            assert.equal(niceForm.TransType.value, '0');
            niceContext.select(niceForm, '신용카드');
            for (const key of ['DirectShowOpt', 'DirectEasyPay', 'NicepayReserved', 'EasyPayMethod']) assert.equal(niceForm[key].value, '');
            assert.equal(niceForm.TransType.value, '1');
        }
    }
    console.log('PC·모바일 PG 요청/결제수단 전환 테스트 통과');
}
main().catch(error => { console.error(error); process.exitCode = 1; });

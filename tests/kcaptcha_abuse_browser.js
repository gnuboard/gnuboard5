// 실제 설치본 기본 PC·모바일 스킨의 오답 재시도와 정상 제출을 확인한다.
// G5_TEST_PLAYWRIGHT에 Playwright 모듈 경로를 지정한다.
// 시스템 Chrome을 쓰려면 G5_TEST_CHROME에 실행 파일 경로를 지정한다.
const { chromium } = require(process.env.G5_TEST_PLAYWRIGHT);
const fs = require('fs');
const assert = require('assert');
const base = process.argv[2];
const root = process.argv[3];
(async () => {
    const browser = await chromium.launch({headless: true, executablePath: process.env.G5_TEST_CHROME || undefined});
    try {
        for (const device of ['pc', 'mobile']) {
            const context = await browser.newContext(device === 'mobile' ? {
                viewport: {width: 390, height: 844}, isMobile: true, hasTouch: true,
                userAgent: 'Mozilla/5.0 (Linux; Android 14) AppleWebKit/537.36 Chrome/130.0.0.0 Mobile Safari/537.36'
            } : {});
            // 설치본 외부로 나가는 브라우저 요청은 허용하지 않는다.
            await context.route('**/*', route => route.request().url().startsWith(base + '/') ? route.continue() : route.abort());
            const page = await context.newPage();
            const dialogs = [];
            page.on('dialog', async dialog => {dialogs.push(dialog.message()); await dialog.accept();});
            const audioResponse = page.waitForResponse(r => r.url().includes('/kcaptcha_mp3.php'));
            await page.goto(base + '/bbs/password_lost.php?device=' + device);
            const audioUrl = (await (await audioResponse).text()).trim();
            assert(audioUrl.startsWith(base + '/data/cache/'));
            const audio = await (await context.request.get(audioUrl)).body();
            const refs = Array.from({length:10}, (_, n) => fs.readFileSync(root + '/plugin/kcaptcha/mp3/basic/' + n + '.mp3'));
            let answer = '';
            for (let i=0; i<audio.length; i+=refs[0].length) {
                const number = refs.findIndex(ref => ref.equals(audio.subarray(i,i+refs[0].length)));
                assert(number >= 0); answer += number;
            }
            assert.strictEqual(answer.length, 6);
            await page.locator('[name=mb_email]').fill('browser' + device + '@example.com');
            await page.locator('#captcha_key').fill(answer === '111111' ? '222222' : '111111');
            await page.locator('form[name=fpasswordlost] button[type=submit]').click();
            assert(dialogs.some(message => message.includes('자동등록방지')));
            await page.locator('#captcha_key').fill(answer);
            const submitted = page.waitForResponse(r => r.url().includes('/password_lost2.php'));
            await page.locator('form[name=fpasswordlost] button[type=submit]').click();
            assert((await (await submitted).text()).includes('안내 메일이 발송됩니다'));
            console.log(device + ' 오답 후 정상 제출 통과');
            await context.close();
        }
        const admin = await browser.newContext();
        await admin.route('**/*', route => route.request().url().startsWith(base + '/') ? route.continue() : route.abort());
        await admin.request.post(base + '/bbs/login_check.php', {form:{mb_id:'fixtureadmin',mb_password:'fixture-admin-password',url:base+'/adm/'},headers:{Referer:base+'/bbs/login.php'}});
        const settings = await admin.newPage();
        const adminDialogs = [];
        settings.on('dialog', async dialog => {adminDialogs.push(dialog.message()); await dialog.accept();});
        await settings.goto(base + '/adm/config_form.php');
        assert.strictEqual(await settings.locator('#password_lost_ip_burst').inputValue(),'10');
        await settings.locator('#password_lost_ip_burst').fill('12');
        await Promise.all([settings.waitForNavigation(),settings.locator('#fconfigform input[type=submit]').click()]);
        await settings.goto(base + '/adm/config_form.php');
        assert.strictEqual(await settings.locator('#password_lost_ip_burst').inputValue(),'12');
        console.log('최고관리자 요청 제한 정책 저장 통과');
        const invalidForm = await settings.locator('#fconfigform').evaluate(form => Object.fromEntries(new FormData(form)));
        const csrfKey = await settings.evaluate(() => window.g5_admin_csrf_token_key);
        const tokenResponse = await admin.request.post(base+'/adm/ajax.token.php',{form:{admin_csrf_token_key:csrfKey},headers:{Referer:base+'/adm/config_form.php'}});
        invalidForm.token = (await tokenResponse.json()).token;
        assert(invalidForm.token);
        invalidForm['password_lost_policy[ip][burst]']='0';
        const invalidResponse = await admin.request.post(base+'/adm/config_form_update.php',{form:invalidForm,headers:{Referer:base+'/adm/config_form.php'}});
        assert((await invalidResponse.text()).includes('1~10000'));
        await settings.goto(base + '/adm/config_form.php');
        assert.strictEqual(await settings.locator('#password_lost_ip_burst').inputValue(),'12');
        console.log('정책 범위 서버 검증 및 기존값 보존 통과');
        const legacyForm = await settings.locator('#fconfigform').evaluate(form => Object.fromEntries(new FormData(form)));
        for (const key of Object.keys(legacyForm)) if (key.startsWith('password_lost_policy')) delete legacyForm[key];
        const legacyToken = await admin.request.post(base+'/adm/ajax.token.php',{form:{admin_csrf_token_key:csrfKey},headers:{Referer:base+'/adm/config_form.php'}});
        legacyForm.token=(await legacyToken.json()).token;
        await admin.request.post(base+'/adm/config_form_update.php',{form:legacyForm,headers:{Referer:base+'/adm/config_form.php'}});
        await settings.goto(base + '/adm/config_form.php');
        assert.strictEqual(await settings.locator('#password_lost_ip_burst').inputValue(),'12');
        console.log('구형 관리자 폼 저장 시 기존 정책 보존 통과');
        await settings.locator('[name=password_lost_policy_reset]').check();
        await Promise.all([settings.waitForNavigation(),settings.locator('#fconfigform input[type=submit]').click()]);
        await settings.goto(base + '/adm/config_form.php');
        assert.strictEqual(await settings.locator('#password_lost_ip_burst').inputValue(),'10');
        assert.strictEqual(await settings.locator('#password_lost_recipient_interval').inputValue(),'300');
        console.log('최고관리자 요청 제한 기본값 복원 통과');
        if (process.env.G5_TEST_REPORT_DIR) await settings.locator('#anc_cf_mail').screenshot({path:process.env.G5_TEST_REPORT_DIR+'/password-lost-settings.png'});
        await admin.close();
    } finally { await browser.close(); }
})().catch(error => {console.error(error); process.exitCode = 1;});

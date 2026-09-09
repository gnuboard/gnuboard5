// 실행: node tests/admin_date_filter.js (playwright 필요, 실제 서버·DB 연결 없음)
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const { execFileSync } = require('node:child_process');
const path = require('node:path');
const assert = require('node:assert/strict');
(async () => {
    const results = JSON.parse(execFileSync('php', [path.join(__dirname, 'admin_date_filter.php'), '--json'], {encoding: 'utf8'}));
    const browser = await chromium.launch({headless: true, ...(process.env.CHROME_EXECUTABLE ? {executablePath: process.env.CHROME_EXECUTABLE} : {})});
    try {
        for (const [name, result] of Object.entries(results)) {
            const page = await browser.newPage();
            await page.route('**/*', route => route.abort());
            await page.setContent('<!doctype html><html><body>' + result.html + '</body></html>');
            assert.equal(await page.locator('[autofocus], [onfocus]').count(), 0, name);
            assert.equal(await page.evaluate(() => document.documentElement.dataset.g5xss), undefined, name);
            if (name.endsWith('-blocked')) {
                assert.equal(result.status, 400);
                assert.equal(await page.locator('input').count(), 0);
            } else {
                assert.equal(await page.locator('#fr_date').count(), 1);
                assert.equal(await page.locator('#to_date').count(), 1);
                if (name.endsWith('-valid')) {
                    assert.equal(await page.locator('#fr_date').inputValue(), '20240229');
                    assert.equal(await page.locator('#to_date').inputValue(), '20240301');
                }
                if (name.endsWith('-encoded-output')) {
                    assert.equal(await page.locator('#fr_date').inputValue(), '1" autofocus onfocus=document.documentElement.dataset.g5xss=1 x="');
                    await page.locator('#fr_date').focus();
                    await page.locator('#to_date').focus();
                    assert.equal(await page.evaluate(() => document.documentElement.dataset.g5xss), undefined);
                }
                for (const url of await page.locator('a[href*="page="]').evaluateAll(links => links.map(a => a.getAttribute('href')))) {
                    const query = new URL(url, 'https://example.invalid').searchParams;
                    assert.equal(query.has('amp;fr_date'), false);
                    if (name.endsWith('-initial')) assert.equal(query.has('fr_date') || query.has('to_date'), false);
                    if (name.endsWith('-valid')) {
                        assert.equal(query.get('fr_date'), '20240229');
                        assert.equal(query.get('to_date'), '20240301');
                    }
                }
            }
            await page.close();
        }
        console.log('보관함·판매순위 Chromium 속성 주입 차단·날짜 출력·링크 검증 통과');
    } finally {
        await browser.close();
    }
})().catch(error => {console.error(error); process.exitCode = 1;});

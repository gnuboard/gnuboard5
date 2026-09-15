// 실행: NODE_PATH=<Playwright 경로>/node_modules CHROME_PATH=<Chromium 실행 파일> node tests/shop_category_layout.js
const fs = require('fs');
const path = require('path');
const assert = require('assert');
const {execFileSync} = require('child_process');
const {chromium} = require('playwright');
const root = path.dirname(__dirname);
const read = file => fs.readFileSync(path.join(root, file), 'utf8');
const fixtures = JSON.parse(execFileSync('php', [path.join(__dirname, 'shop_category_navigation.php'), '--json'], {encoding:'utf8'}));
(async () => {
    const browser = await chromium.launch({headless:true, ...(process.env.CHROME_PATH ? {executablePath:process.env.CHROME_PATH} : {}), args:['--no-sandbox']});
    try {
        for (const skin of ['skin/shop/basic', 'theme/basic/skin/shop/basic']) {
            for (const view of ['list', 'item']) {
                const page = await browser.newPage({viewport:{width:1920, height:1080}});
                let baseline;
                for (const id of ['10', '101010', '10101010', '1010101010']) {
                    let html = fixtures[skin][id].replace(/<script>[\s\S]*?<\/script>/g, '');
                    if (view === 'list') html = html.replace('view_location', 'is_list is_right');
                    await page.setContent('<style>'+read(skin.startsWith('theme/') ? 'theme/basic/css/default_shop.css' : 'css/default_shop.css')+'\n'+read(skin+'/style.css')+'</style><div id="container"><div class="shop-content"><div id="wrapper_title">상품리스트</div><div class="sct_admin"><a>분류 관리</a></div><div id="sct">'+html+'<div id="sct_sortlst">상품 정렬</div></div></div></div>');
                    await page.addScriptTag({content:read('js/jquery-1.12.4.min.js')});
                    await page.addScriptTag({content:read('js/shop.category.navigation.js')});
                    await page.evaluate(() => new Promise(resolve => jQuery(() => {
                        jQuery('select.shop_hover_selectbox').shop_select_to_html();
                        resolve();
                    })));
                    const layout = await page.evaluate(() => {
                        const nav = document.getElementById('sct_location');
                        const rect = nav.getBoundingClientRect();
                        return {top:rect.top, height:rect.height, position:getComputedStyle(nav).position,
                            menuTop:document.getElementById('sct_ct_1').getBoundingClientRect().top,
                            sortTop:document.getElementById('sct_sortlst').getBoundingClientRect().top};
                    });
                    if (!baseline) baseline = layout;
                    assert.deepStrictEqual(layout, baseline, skin+' '+view+' '+id+' 단계 변경 시 레이아웃 유지');
                    assert.strictEqual(await page.locator('.shop_select_to_html').count(), id.length / 2);
                    assert.strictEqual(await page.locator('#sct_ct_1 ul').count(), 1);
                }
                await page.close();
            }
        }
        console.log('PC·basic 테마 목록/상세: 1·3·4·5단계 breadcrumb 위치·높이와 메뉴·정렬 영역 위치 동일');
    } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exitCode = 1; });

// Generate tests/Browser/.fixtures/staff-dashboard.html with KT_DASHBOARD_BROWSER_FIXTURE=1
// and StaffDashboardTest::test_needs_attention_matches_displayed_category_counts first.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');

const root = path.resolve(__dirname, '../..');
const dashboard = fs.readFileSync(path.join(__dirname, '.fixtures/staff-dashboard.html'), 'utf8');
const start = dashboard.indexOf('<div id="staffRecordAssistant"');
const script = dashboard.indexOf('staff-record-assistant.js', start);
const end = dashboard.indexOf('</script>', script) + '</script>'.length;
assert(start >= 0 && script > start && end > script, 'Rendered staff assistant fixture is missing');
const fragment = dashboard.slice(start, end);

(async () => {
    const server = http.createServer((request, response) => {
        const url = new URL(request.url, 'http://127.0.0.1');
        if (url.pathname === '/js/staff-record-assistant.js') {
            response.setHeader('Content-Type', 'text/javascript');
            return response.end(fs.readFileSync(path.join(root, 'public/js/staff-record-assistant.js')));
        }
        if (url.pathname === '/css/staff-record-assistant.css') {
            response.setHeader('Content-Type', 'text/css');
            return response.end(fs.readFileSync(path.join(root, 'public/css/staff-record-assistant.css')));
        }
        if (url.pathname === '/staff/assistant/patients') {
            response.setHeader('Content-Type', 'application/json');
            return response.end(JSON.stringify({patients:[{id:1,name:'Ana Santos',birthdate:'1990-01-01'}]}));
        }
        if (url.pathname === '/staff/assistant/ask') {
            let payload = '';
            request.on('data', chunk => { payload += chunk; });
            return request.on('end', () => {
                const question = JSON.parse(payload).question;
                response.setHeader('Content-Type', 'application/json');
                if (question.includes('fail')) {
                    response.statusCode = 503;
                    return response.end(JSON.stringify({message:'Records are temporarily unavailable.'}));
                }
                return response.end(JSON.stringify({message:question.includes('long answer') ? 'Recorded details. '.repeat(150) : 'Recorded balance: ₱600.00. Check the source record.',
                    links:[{label:'Patient balance source',url:`http://127.0.0.1:${server.address().port}/staff/patients/1`}]}));
            });
        }
        if (url.pathname === '/staff/patients/1') {
            response.setHeader('Content-Type', 'text/html');
            return response.end('<h1>Patient balance source</h1>');
        }
        const origin = `http://127.0.0.1:${server.address().port}`;
        const assistant = fragment.replaceAll('http://localhost', origin).replaceAll('http://127.0.0.1:8000', origin);
        response.setHeader('Content-Type', 'text/html');
        response.end('<!doctype html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="csrf-token" content="test"></head><body><button id="pageControl">Page control</button>' + assistant + '</body></html>');
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const origin = `http://127.0.0.1:${server.address().port}`;
    let browser;
    try {
        browser = await chromium.launch({channel:'msedge',headless:true});
        const page = await browser.newPage({viewport:{width:390,height:844},isMobile:true,hasTouch:true});
        const errors = [];
        page.on('pageerror', error => errors.push(error.message));
        await page.goto(origin);
        const launcher = page.getByRole('button', {name:'Open staff records assistant'});
        await launcher.click();
        const panel = page.getByRole('region', {name:'Staff Assistant'});
        await panel.waitFor();
        assert(await panel.isVisible(), 'Panel did not open');
        assert(!(await launcher.isVisible()), 'Launcher should hide while the panel is open');
        assert(await page.getByText('Read-only', {exact:true}).isVisible());
        assert(await page.locator('.record-assistant__empty').isVisible());
        assert(!(await page.locator('.record-assistant__dates').isVisible()));
        if (process.env.KT_ASSISTANT_SCREENSHOT) await page.screenshot({path:process.env.KT_ASSISTANT_SCREENSHOT});
        assert(await page.getByRole('textbox', {name:'Question'}).evaluate(el => el === document.activeElement), 'Typing focus is not in the question field');
        await page.getByRole('textbox', {name:'Question'}).fill('Which patients have outstanding balances?');
        await page.getByRole('button', {name:'Ask question'}).click();
        await page.getByRole('link', {name:'Patient balance source'}).waitFor();
        assert(!(await page.locator('.record-assistant__empty').isVisible()), 'Suggestions remained after chatting');
        await page.getByRole('link', {name:'Patient balance source'}).click();
        await page.waitForURL('**/staff/patients/1');
        await page.goBack();
        await launcher.click();
        await page.getByRole('button', {name:'Change patient'}).click();
        await page.getByRole('searchbox', {name:/Find a patient/}).fill('Ana');
        await page.getByRole('button', {name:/Ana Santos/}).click();
        assert.match(await page.locator('.record-assistant__selected').innerText(), /Patient #1/);
        await page.getByRole('button', {name:'All records'}).click();
        assert.match(await page.locator('.record-assistant__selected').innerText(), /All clinic records/);
        await page.getByRole('textbox', {name:'Question'}).fill('How many visits occurred in the selected date range?');
        assert(await page.locator('.record-assistant__dates').isVisible(), 'Date filters should appear for visit ranges');
        await page.getByRole('textbox', {name:'Question'}).fill('Please give a long answer');
        assert(!(await page.locator('.record-assistant__dates').isVisible()), 'Date filters should hide for other questions');
        await page.getByRole('button', {name:'Ask question'}).click();
        await page.getByText('Recorded details.', {exact:false}).waitFor();
        await page.getByRole('textbox', {name:'Question'}).fill('Please fail');
        await page.getByRole('button', {name:'Ask question'}).click();
        await page.locator('.record-assistant__response--error').waitFor();
        await page.setViewportSize({width:390,height:360});
        const boxes = await page.evaluate(() => {
            const p = document.querySelector('.record-assistant__panel').getBoundingClientRect();
            const close = document.querySelector('.record-assistant__close').getBoundingClientRect();
            const input = document.querySelector('.record-assistant__form input').getBoundingClientRect();
            const body = document.querySelector('.record-assistant__body');
            body.scrollTop = body.scrollHeight;
            return {panelTop:p.top,panelBottom:p.bottom,closeTop:close.top,inputBottom:input.bottom,
                bodyHeight:body.clientHeight,bodyScrolled:body.scrollTop};
        });
        assert(boxes.panelTop >= 0 && boxes.panelBottom <= 360, JSON.stringify(boxes));
        assert(boxes.closeTop >= 0 && boxes.inputBottom <= 360, JSON.stringify(boxes));
        assert(boxes.bodyHeight >= 75 && boxes.bodyScrolled > 0, JSON.stringify(boxes));
        await page.getByRole('button', {name:'Ask question'}).focus();
        await page.keyboard.press('Tab');
        assert(await page.getByRole('button', {name:'Close assistant'}).evaluate(el => el === document.activeElement), 'Tab did not wrap within the panel');
        await page.getByRole('button', {name:'Close assistant'}).click();
        assert(!(await panel.isVisible()), 'Panel did not close');
        await page.locator('#pageControl').click();
        await launcher.click();
        await page.keyboard.press('Escape');
        assert(!(await panel.isVisible()), 'Escape did not close panel');
        assert.deepEqual(errors, [], `Browser errors: ${errors.join('; ')}`);
        const desktop = await browser.newPage({viewport:{width:1280,height:800}});
        desktop.on('pageerror', error => errors.push(error.message));
        await desktop.goto(origin);
        await desktop.getByRole('button', {name:'Open staff records assistant'}).click();
        const desktopBoxes = await desktop.evaluate(() => {
            const panel = document.querySelector('.record-assistant__panel').getBoundingClientRect();
            const body = document.querySelector('.record-assistant__body').getBoundingClientRect();
            const form = document.querySelector('.record-assistant__form').getBoundingClientRect();
            return {panelLeft:panel.left,panelRight:panel.right,bodyHeight:body.height,formBottom:form.bottom,panelBottom:panel.bottom};
        });
        assert(desktopBoxes.panelLeft > 700 && desktopBoxes.panelRight <= 1280, JSON.stringify(desktopBoxes));
        assert(desktopBoxes.bodyHeight > 350 && Math.abs(desktopBoxes.formBottom - desktopBoxes.panelBottom) < 2, JSON.stringify(desktopBoxes));
        if (process.env.KT_ASSISTANT_SCREENSHOT_DESKTOP) await desktop.screenshot({path:process.env.KT_ASSISTANT_SCREENSHOT_DESKTOP});
        assert.deepEqual(errors, [], `Browser errors: ${errors.join('; ')}`);
        console.log('PASS: Edge mobile 390x844 and 390x360 plus desktop 1280x800; open/close, focus/typing, date reveal, long answer, error, source link, patient switching, scrolling, page control, Escape; no page errors.');
    } finally {
        if (browser) await browser.close();
        server.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });

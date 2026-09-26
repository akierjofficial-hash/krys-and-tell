// Run the PHP fixture test with KT_BROWSER_FIXTURES=1 first. Requires Playwright
// (PLAYWRIGHT_MODULE may point to an existing installation) and Microsoft Edge.
// Endpoints are mocked here; StaffRecordEntryTest exercises the real PHP writes.
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const http = require('node:http');

(async () => {
    const server = http.createServer((req, res) => {
        if (req.url === '/public/js/staff-record-entry.js') {
            res.setHeader('Content-Type', 'text/javascript');
            return res.end(fs.readFileSync(path.resolve(__dirname, '../../public/js/staff-record-entry.js')));
        }
        if (req.url.startsWith('/css/staff-app.css')) {
            res.setHeader('Content-Type', 'text/css');
            return res.end(fs.readFileSync(path.resolve(__dirname, '../../public/css/staff-app.css')));
        }
        const url = new URL(req.url, 'http://localhost');
        const mode = url.searchParams.get('mode') === 'visit' ? 'visit' : 'past';
        const fixture = url.searchParams.has('patient_id') ? mode : 'select';
        let html = fs.readFileSync(path.join(__dirname, '.fixtures', `${fixture}.html`), 'utf8');
        html = html.replaceAll('http://localhost', `http://127.0.0.1:${server.address().port}`);
        html = html.replace(/<script src="[^"]*staff-record-entry\.js[^\"]*" defer><\/script>/,
            '<script>window.recordEntryConfig.baseUrl = location.origin + "/staff/record-entry";</script><script src="/public/js/staff-record-entry.js" defer></script>');
        html = html.replace(/<link rel="stylesheet" href="[^"]*staff-app\.css[^"]*">/g,
            '<link rel="stylesheet" href="/css/staff-app.css?v=2">');
        res.setHeader('Content-Type', 'text/html');res.end(html);
    });
    await new Promise(resolve => server.listen(0, '127.0.0.1', resolve));
    const origin = `http://127.0.0.1:${server.address().port}`;
    let browser, page;
    try {
        browser = await chromium.launch({channel:'msedge',headless:true});
        page = await browser.newPage({viewport:{width:1100,height:780}});
        page.setDefaultTimeout(10000);
        const errors=[];page.on('pageerror',error=>errors.push(error.message));
        let draft=null, review=null, saved=null;
        await page.route('**/*',async route=>{
            const request=route.request(), url=new URL(request.url());
            if(url.origin!==origin)return route.abort();
            if(url.pathname.endsWith('/staff-record-entry.js'))return route.fulfill({contentType:'text/javascript',body:fs.readFileSync(path.resolve(__dirname,'../../public/js/staff-record-entry.js'),'utf8')});
            if(url.pathname.endsWith('/css/staff-app.css'))return route.fulfill({contentType:'text/css',body:fs.readFileSync(path.resolve(__dirname,'../../public/css/staff-app.css'),'utf8')});
            if(request.resourceType()==='script')return route.fulfill({contentType:'text/javascript',body:''});
            if(request.method()!=='GET'){
                const body=request.postDataJSON();
                if(url.pathname.endsWith('/review')) {review=body;return route.fulfill({json:{version:body.version+1,review_hash:'a'.repeat(64),warnings:[],payload:body.payload}});}
                if(url.pathname.endsWith('/save')){saved=body;return route.fulfill({json:{summary:{visits:review.payload.visits.length},redirect:`${origin}/done`}});}
                if(request.method()==='DELETE')return route.fulfill({json:{message:'Discarded'}});
                draft=body;return route.fulfill({json:{version:body.version+1,message:'Draft saved'}});
            }
            if(url.pathname==='/done')return route.fulfill({contentType:'text/html',body:'Records saved'});
            if(request.resourceType()==='document')return route.continue();
            return route.fulfill({body:'',contentType:'text/plain'});
        });
        const field=key=>page.locator(`[data-path="${key}"]`);
        await page.goto(`${origin}/staff/record-entry`,{waitUntil:'domcontentloaded'});
        await page.locator('#re-patient-search').fill('0917');
        const patientResult=page.locator('[data-patient-index="0"]');
        await patientResult.waitFor();
        assert.match(await patientResult.innerText(),/Patient, Paper/);
        assert.equal(await page.locator('#record-entry').getByRole('button',{name:'Continue',exact:true}).count(),0);
        await patientResult.click();
        await page.waitForURL('**/staff/record-entry?patient_id=1&mode=past');
        await field('visits.0.visit_date').fill('2020-01-01');
        await field('visits.0.doctor_id').selectOption({label:'Dr. History'});
        await page.locator('[data-search-service]').fill('Restor');
        await field('visits.0.procedures.0.service_id').selectOption({label:'Restoration'});
        assert.equal(await field('visits.0.procedures.0.price').inputValue(),'1800.00');
        await field('visits.0.procedures.0.price').fill('800.25');
        await field('visits.0.procedures.0.service_id').selectOption({label:'Restoration'});
        assert.equal(await field('visits.0.procedures.0.price').inputValue(),'800.25');
        await page.locator('[data-action="add-procedure"]').click();
        await field('visits.0.procedures.1.service_id').selectOption({label:'Oral Prophylaxis'});
        assert.equal(await field('visits.0.procedures.1.price').inputValue(),'1000.00');
        await page.locator('[data-action="add-payment"]').click();
        await field('visits.0.payments.0.procedure_index').selectOption('1');
        await field('visits.0.payments.0.amount').fill('1000');
        await field('visits.0.payments.0.amount').press('Enter');
        assert.equal(await page.locator(':focus').getAttribute('data-path'),'visits.0.payments.0.payment_date');
        await field('visits.0.arrangement').selectOption('installment');
        await field('visits.0.plan.procedure_index').selectOption('0');
        assert.equal(await field('visits.0.plan.total_cost').inputValue(),'800.25');
        await field('visits.0.plan.downpayment').fill('200.25');
        await field('visits.0.plan.months').fill('4');
        await page.locator('[data-action="duplicate"]').click();
        assert.equal(await page.locator('[data-totals]').count(),2);
        assert.equal(await page.locator('[data-path="visits.1.payments.0.amount"]').count(),0);
        const screenshotDirectory=path.resolve(__dirname,'../../docs/screenshots');
        fs.mkdirSync(screenshotDirectory,{recursive:true});
        await page.setViewportSize({width:1440,height:900});
        await page.evaluate(()=>window.scrollTo(0,0));
        const planBoxes=await page.locator('.re-plan-grid').first().locator(':scope > label').evaluateAll(labels=>labels.map(label=>{
            const control=label.querySelector('input,select,textarea');
            return {labelY:Math.round(label.getBoundingClientRect().y),controlY:Math.round(control.getBoundingClientRect().y)};
        }));
        assert.equal(planBoxes.length,8);
        assert.equal(new Set(planBoxes.slice(0,4).map(box=>box.labelY)).size,1);
        assert.equal(new Set(planBoxes.slice(0,4).map(box=>box.controlY)).size,1);
        assert.equal(new Set(planBoxes.slice(4).map(box=>box.labelY)).size,1);
        assert.equal(new Set(planBoxes.slice(4).map(box=>box.controlY)).size,1);
        assert(planBoxes[4].labelY>planBoxes[0].labelY);
        await page.screenshot({path:path.join(screenshotDirectory,'staff-past-records-desktop.png')});
        await page.locator('#re-save-draft').click();
        await page.waitForFunction(()=>document.getElementById('re-status').textContent==='Draft saved');
        assert.equal(draft.payload.visits.length,2);
        assert.equal(await page.locator('#re-drafts-count').textContent(),'1');
        await page.reload({waitUntil:'domcontentloaded'});
        assert(await page.locator('#re-drafts').isHidden());
        await page.locator('#re-drafts-toggle').click();
        await page.locator('[data-draft-continue]').click();
        assert.equal(await field('visits.0.procedures.0.price').inputValue(),'800.25');
        assert.equal(await page.locator('[data-totals]').count(),2);
        await field('visits.1.visit_date').fill('2020-02-01');
        await page.locator('[data-action="remove-procedure"][data-v="1"][data-j="1"]').click();
        await field('visits.1.arrangement').selectOption('installment');
        await field('visits.1.plan.total_cost').fill('1000');
        await field('visits.1.plan.downpayment').fill('200');
        await field('visits.1.plan.months').fill('4');
        await page.locator('[data-action="add-installment-payment"][data-v="1"]').click();
        await field('visits.1.plan.payments.0.amount').fill('100');
        await field('visits.1.plan.payments.0.payment_date').fill('2020-03-01');
        assert.match(await page.locator('[data-totals="1"]').innerText(),/700/);
        await page.setViewportSize({width:768,height:600});
        await page.evaluate(()=>window.scrollTo(0,0));
        const mobileSidebar=await page.locator('#staffSidebar').evaluate(element=>({
            open:element.classList.contains('open'),
            position:getComputedStyle(element).position,
            transform:getComputedStyle(element).transform,
        }));
        assert.equal(mobileSidebar.open,false);
        assert.equal(mobileSidebar.position,'fixed');
        assert.match(mobileSidebar.transform,/matrix\(1, 0, 0, 1, -/);
        await page.screenshot({path:path.join(screenshotDirectory,'staff-past-records-tablet.png')});
        await page.locator('#re-review-button').scrollIntoViewIfNeeded();
        assert(await page.locator('#re-review-button').isVisible());
        await page.locator('#re-review-button').click();
        await page.locator('#re-save-all').waitFor();
        assert.equal(review.payload.visits.length,2);
        assert.equal(review.payload.visits[0].procedures[0].price,'800.25');
        assert.equal(review.payload.visits[0].payments[0].procedure_index,'1');
        assert.equal(review.payload.visits[0].plan.procedure_index,'0');
        assert.equal(review.payload.visits[1].plan.payments[0].visit_id,null);
        await page.locator('#re-save-all').click();
        await page.waitForURL('**/done');
        assert.equal(saved.review_hash,'a'.repeat(64));
        await page.goto(`${origin}/staff/record-entry?patient_id=1&mode=visit`,{waitUntil:'domcontentloaded'});
        await field('visits.0.doctor_id').selectOption({label:'Dr. History'});
        await field('visits.0.procedures.0.service_id').selectOption({label:'Restoration'});
        await page.locator('[data-save-intent="payment"]').click();
        await field('visits.0.payments.0.amount').fill('100');
        await page.locator('[data-save-intent="payment"]').click();
        await page.getByRole('button',{name:'Save Visit & Record Payment',exact:true}).last().waitFor();
        await page.locator('#re-save-all').click();
        await page.waitForURL('**/done');
        assert.deepEqual(errors,[]);
        console.log('PASS: patient search/direct selection, compact draft recovery, aligned form grids, historical price, mixed-billing allocation, keyboard navigation, duplicate row, installment totals, tablet scroll, review/save, normal visit/payment actions; no browser errors.');
    } catch(error) {
        if(page) console.error('Editor error:',await page.locator('#re-errors').textContent().catch(()=>''));
        throw error;
    } finally {
        if(browser)await browser.close();
        await new Promise(resolve=>server.close(resolve));
    }
})().catch(error=>{console.error(error);process.exitCode=1;});

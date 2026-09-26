/* Patient-scoped draft editor. Clinical writes happen only after server review. */
(() => {
    'use strict';
    const c = window.recordEntryConfig;
    if (!c) return;
    const $ = id => document.getElementById(id);
    const esc = value => String(value ?? '').replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
    const money = n => Number(n || 0).toLocaleString('en-PH', {style:'currency', currency:'PHP'});
    const cents = n => Math.round(Number(n || 0) * 100);
    const sum = (rows, key) => rows.reduce((n, row) => n + cents(row[key]), 0);
    const methods = ['Cash', 'GCash', 'Card', 'Bank Transfer'];
    const option = (value, name, selected) => `<option value="${esc(value)}" ${String(value) === String(typeof selected === 'boolean' ? Number(selected) : selected) ? 'selected' : ''}>${esc(name)}</option>`;
    if (!c.patientId) {
        const input = $('re-patient-search'), results = $('re-patient-results');
        let matches = [], active = -1;
        const searchable = patient => `${patient.id} ${patient.first_name} ${patient.last_name} ${patient.last_name} ${patient.first_name} ${patient.birthdate || ''} ${patient.contact_number || ''}`.toLocaleLowerCase();
        function openPatient(patient) {
            const url = new URL(c.baseUrl);
            url.searchParams.set('patient_id', patient.id);
            url.searchParams.set('mode', c.mode);
            location.assign(url.toString());
        }
        function renderPatients() {
            const term = input.value.trim().toLocaleLowerCase();
            matches = c.patients.filter(patient => !term || searchable(patient).includes(term)).slice(0, 20);
            active = matches.length && active >= 0 ? Math.min(active, matches.length - 1) : -1;
            if (!matches.length) {
                results.innerHTML = '<div class="re-muted p-3">No matching patients found.</div>';
            } else {
                results.innerHTML = matches.map((patient, index) => `<button type="button" class="re-patient-result" id="re-patient-${index}" role="option" data-patient-index="${index}" aria-selected="${index === active}"><span><strong>${esc(patient.last_name)}, ${esc(patient.first_name)}</strong><small>Patient #${esc(patient.id)}${patient.contact_number ? ` · ${esc(patient.contact_number)}` : ''}</small></span><small>${esc(String(patient.birthdate || 'No birthdate').slice(0,10))}</small></button>`).join('');
            }
            results.hidden = false;
            input.setAttribute('aria-expanded', 'true');
            input.setAttribute('aria-activedescendant', active >= 0 ? `re-patient-${active}` : '');
        }
        function closePatients() {
            results.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); active = -1;
        }
        input.addEventListener('focus', renderPatients);
        input.addEventListener('input', () => {active = -1;renderPatients();});
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {closePatients();return;}
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (results.hidden) renderPatients();
                if (!matches.length) return;
                active = active < 0
                    ? (event.key === 'ArrowDown' ? 0 : matches.length - 1)
                    : Math.max(0, Math.min(matches.length - 1, active + (event.key === 'ArrowDown' ? 1 : -1)));
                renderPatients(); results.querySelector(`[data-patient-index="${active}"]`)?.scrollIntoView({block:'nearest'}); return;
            }
            if (event.key === 'Enter' && active >= 0 && matches[active]) {event.preventDefault();openPatient(matches[active]);}
        });
        results.addEventListener('click', event => {
            const button = event.target.closest('[data-patient-index]');
            if (button) openPatient(matches[Number(button.dataset.patientIndex)]);
        });
        document.addEventListener('click', event => {if (!event.target.closest('.re-patient-picker')) closePatients();});
        return;
    }
    const storageKey = `kt-record-entry:${c.userId}:${c.patientId}:${c.mode}`;
    const statusNode = $('re-status');
    new MutationObserver(() => {
        const badge = statusNode.closest('.re-state');
        const message = statusNode.textContent.toLowerCase();
        badge.classList.toggle('re-state-success', /saved|restored|reviewed/.test(message) && !/unsaved|waiting/.test(message));
        badge.classList.toggle('re-state-warning', /unsaved|waiting|recovered|enter the/.test(message));
    }).observe(statusNode, {childList:true, subtree:true, characterData:true});
    const uuid = () => crypto.randomUUID ? crypto.randomUUID() : '10000000-1000-4000-8000-100000000000'.replace(/[018]/g, n => (n ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> n / 4).toString(16));
    const procedure = () => ({service_id:'', tooth_number:'', surface:'', shade:'', price:'', notes:''});
    const receipt = date => ({amount:'', payment_date:date || c.today, method:'Cash', notes:'', procedure_index:''});
    const visit = () => ({visit_date:c.mode === 'visit' ? c.today : '', doctor_id:$('re-default-doctor').value, notes:'', arrangement:'ordinary', procedures:[procedure()], payments:[], plan:null});
    let state = {id:uuid(), version:0, payload:{default_doctor_id:'',visits:[visit()]}}, drafts = [];
    let dirty = false, reviewed = null, timer, queue = Promise.resolve(), locked = false, revision = 0;
    const cached = (() => {try {return JSON.parse(sessionStorage.getItem(storageKey));} catch {return null;}})();
    function localSave() {try {sessionStorage.setItem(storageKey, JSON.stringify(state));} catch { /* Server draft remains primary; unload warning stays enabled. */ }}
    function clearLocal() {try {sessionStorage.removeItem(storageKey);} catch {}}
    function errors(error) {
        const box = $('re-errors'); box.hidden = false; box.replaceChildren();
        const title = document.createElement('strong'); title.textContent = error.message || 'Unable to save. Your entry is still on this page.'; box.append(title);
        Object.entries(error.errors || {}).forEach(([path, messages]) => {
            const button = document.createElement('button'); button.type = 'button'; button.className = 'btn d-block re-error-link';
            button.textContent = `${path.replace(/visits\.(\d+)/, (_, n) => `Visit ${Number(n)+1}`).replaceAll('.', ' › ')}: ${[].concat(messages).join(' ')}`;
            button.onclick = () => {
                $('re-editor').hidden = false;
                const field = [...document.querySelectorAll('[data-path]')].find(el => el.dataset.path === path);
                if (field) { field.setAttribute('aria-invalid', 'true'); field.scrollIntoView({block:'center'}); field.focus(); }
            }; box.append(button);
        });
        box.scrollIntoView({block:'start'});
    }
    async function request(action, method, body, batchId=state.id) {
        const response = await fetch(`${c.baseUrl}/${batchId}/${action}`, {method, credentials:'same-origin', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':c.csrf}, body:JSON.stringify(body)});
        let json; try {json = await response.json();} catch {throw {message:'The server did not return a valid response. Check your connection and sign-in, then retry.'};}
        if (!response.ok) throw json;
        return json;
    }
    const body = () => ({patient_id:c.patientId, mode:c.mode, version:state.version, payload:structuredClone(state.payload)});
    function enqueue(work) {const next = queue.then(work); queue = next.catch(() => {}); return next;}
    async function saveDraft() {
        clearTimeout(timer);
        return enqueue(async () => {
            if (!dirty || locked) return;
            const sentRevision = revision;
            $('re-status').textContent = 'Saving draft…';
            const result = await request('draft', 'PUT', body());
            state.version = result.version;
            dirty = sentRevision !== revision;
            upsertDraft({...state, updated_at:new Date().toISOString()});
            localSave(); $('re-status').textContent = dirty ? 'New changes waiting to save' : 'Draft saved';
        });
    }
    function changed() {
        revision++; dirty = true; reviewed = null;
        $('re-review').hidden = true; $('re-editor').hidden = false;
        $('re-status').textContent = 'Unsaved changes'; localSave(); totals();
        clearTimeout(timer); timer = setTimeout(() => saveDraft().catch(errors), 900);
    }
    function at(path, value) {
        const parts = path.split('.'); let object = state.payload;
        parts.slice(0,-1).forEach(key => object = object[key]); object[parts.at(-1)] = value;
    }
    function input(label, path, value, type='text', extra='') {
        return `<label>${esc(label)}<input data-path="${path}" type="${type}" value="${esc(value)}" ${type === 'number' ? 'step="0.01" min="0"' : ''} ${extra}></label>`;
    }
    function select(label, path, options) {return `<label>${esc(label)}<select data-path="${path}">${options}</select></label>`;}
    function notes(label, path, value) {return `<label>${esc(label)}<textarea data-path="${path}" maxlength="2000">${esc(value)}</textarea></label>`;}
    function treatmentOptions(v, selected, emptyLabel) {
        return option('', emptyLabel, selected) + state.payload.visits[v].procedures.map((p,i) => {
            const service = c.services.find(s => String(s.id) === String(p.service_id));
            return option(i, `${service?.name || `Treatment ${i+1}`} — ${money(p.price)}`, selected);
        }).join('');
    }
    function paymentRow(p, path, v, j, installment=false) {
        let related = '';
        if (installment) {
            const selected = p.visit_id ? `existing:${p.visit_id}` : (p.visit_index !== undefined && p.visit_index !== null && p.visit_index !== '' ? `batch:${p.visit_index}` : '');
            related = `<label>Related treatment (optional)<select data-link="${path}">${option('', 'Collection only — no visit', selected)}${state.payload.visits.map((row,i) => option(`batch:${i}`, `Batch visit ${i+1}: ${row.visit_date || 'Date not entered'}`, selected)).join('')}${c.existingVisits.map(row => option(`existing:${row.id}`, `Existing #${row.id}: ${row.visit_date.slice(0,10)}`, selected)).join('')}</select></label>`;
        }
        const appliesTo = installment ? '' : select('Payment applies to', `${path}.procedure_index`, treatmentOptions(v, p.procedure_index, state.payload.visits[v].arrangement === 'installment' ? 'Choose non-installment treatment' : 'Entire visit / not allocated'));
        return `<div class="re-row"><div class="re-grid re-payment-grid">${installment ? input('Payment / month number', `${path}.month_number`, p.month_number, 'number', 'inputmode="numeric"') : ''}${appliesTo}${input('Amount received', `${path}.amount`, p.amount, 'number')}${input('Payment date', `${path}.payment_date`, p.payment_date, 'date')}${select('Payment method', `${path}.method`, methods.map(m => option(m,m,p.method)).join(''))}${notes('Receipt notes', `${path}.notes`, p.notes)}${related}</div><button type="button" class="btn btn-sm btn-outline-danger mt-2" data-action="remove-payment" data-v="${v}" data-j="${j}" data-installment="${installment}">Remove receipt</button></div>`;
    }
    function render() {
        const focusPath = document.activeElement?.dataset?.path;
        const batchCount = $('re-batch-count');
        if (batchCount) batchCount.textContent = `${state.payload.visits.length} ${state.payload.visits.length === 1 ? 'record' : 'records'} in batch`;
        $('re-visits').innerHTML = state.payload.visits.map((v,i) => {
            const base = `visits.${i}`;
            const procs = v.procedures.map((p,j) => {
                const path = `${base}.procedures.${j}`;
                return `<div class="re-row"><div class="re-grid re-procedure-grid"><label>Search treatments<input type="search" data-search-service="${path}" placeholder="Filter treatment list"></label>${select('Treatment / service', `${path}.service_id`, option('','Select treatment',p.service_id)+c.services.map(s => option(s.id,s.name,p.service_id)).join(''))}${input('Actual charge',`${path}.price`,p.price,'number')}${input('Tooth number(s)',`${path}.tooth_number`,p.tooth_number,'text','maxlength="50"')}${input('Surface',`${path}.surface`,p.surface,'text','maxlength="10"')}${input('Shade',`${path}.shade`,p.shade,'text','maxlength="10"')}${notes('Procedure notes',`${path}.notes`,p.notes)}</div><button type="button" class="btn btn-sm btn-outline-danger mt-2" data-action="remove-procedure" data-v="${i}" data-j="${j}">Remove procedure</button></div>`;
            }).join('');
            let billing = `<h4>${c.mode === 'visit' ? 'Payment received today (optional)' : 'Ordinary payments (optional)'}</h4><p class="re-muted">Add receipts for treatments paid outside an installment plan. In mixed billing, select the treatment each receipt pays for.</p>${v.payments.map((p,j) => paymentRow(p,`${base}.payments.${j}`,i,j)).join('')}<button type="button" class="btn btn-outline-secondary btn-sm" data-action="add-payment" data-v="${i}">+ Add ordinary receipt</button>`;
            if (v.arrangement === 'installment') {
                const p = v.plan, path = `${base}.plan`;
                billing += `<h4>Installment plan</h4><div class="re-grid re-plan-grid">${select('Financed treatment',`${path}.procedure_index`,treatmentOptions(i,p.procedure_index,'Choose financed treatment'))}${input('Total agreed cost',`${path}.total_cost`,p.total_cost,'number')}${input('Downpayment received (counted once)',`${path}.downpayment`,p.downpayment,'number')}${input('Plan start date',`${path}.start_date`,p.start_date,'date')}${input('Downpayment date',`${path}.downpayment_date`,p.downpayment_date,'date')}${select('Downpayment method',`${path}.downpayment_method`,methods.map(m => option(m,m,p.downpayment_method)).join(''))}${select('Contract type',`${path}.is_open_contract`,option(0,'Fixed term',p.is_open_contract)+option(1,'Open contract',p.is_open_contract))}${p.is_open_contract == 1 ? input('Suggested monthly amount (optional)',`${path}.open_monthly_payment`,p.open_monthly_payment,'number') : input('Number of months',`${path}.months`,p.months,'number','inputmode="numeric"')}</div><div class="re-muted mt-2" data-plan-suggestion="${i}"></div><h4>Previous installment payments</h4><p class="re-muted">Do not enter the downpayment again here. Month 0 is reserved for it. A receipt creates no treatment visit; link a real visit only when appropriate.</p>${p.payments.map((r,j) => paymentRow(r,`${path}.payments.${j}`,i,j,true)).join('')}<button type="button" class="btn btn-outline-secondary btn-sm" data-action="add-installment-payment" data-v="${i}">+ Add installment receipt</button>`;
            }
            return `<section class="re-card"><div class="d-flex justify-content-between flex-wrap"><h3>Visit ${i+1}</h3>${c.mode === 'past' ? `<div class="re-actions mt-0"><button type="button" class="btn btn-sm btn-outline-secondary" data-action="duplicate" data-v="${i}">Duplicate visit</button><button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-visit" data-v="${i}">Remove unsaved visit</button></div>` : ''}</div><div class="re-grid re-visit-grid">${input('Visit date',`${base}.visit_date`,v.visit_date,'date')}${select('Dentist',`${base}.doctor_id`,option('','Select dentist',v.doctor_id)+c.doctors.map(d => option(d.id,d.name+(d.is_active ? '' : ' (inactive)'),v.doctor_id)).join(''))}${notes('Visit notes',`${base}.notes`,v.notes)}</div><h4>Procedures</h4>${procs}<button type="button" class="btn btn-outline-secondary btn-sm" data-action="add-procedure" data-v="${i}">+ Add procedure</button><div class="re-grid re-arrangement-grid mt-3">${select('Payment arrangement',`${base}.arrangement`,option('ordinary','Ordinary payment / unpaid',v.arrangement)+option('installment','Installment or mixed billing',v.arrangement))}</div>${billing}<div class="re-totals" data-totals="${i}"></div></section>`;
        }).join('');
        totals();
        if (focusPath) document.querySelector(`[data-path="${focusPath}"]`)?.focus();
    }
    function figures(v) {
        const charge = sum(v.procedures,'price');
        const ordinaryPaid = sum(v.payments,'amount');
        if (v.arrangement !== 'installment') return {charge, agreed:charge, paid:ordinaryPaid, ordinaryPaid, planPaid:0, balance:charge-ordinaryPaid};
        const financedCharge = v.plan.procedure_index !== '' && v.plan.procedure_index != null ? cents(v.procedures[Number(v.plan.procedure_index)]?.price) : 0;
        const ordinaryCharge = charge-financedCharge;
        const planPaid = cents(v.plan.downpayment)+sum(v.plan.payments,'amount');
        const agreed = ordinaryCharge+cents(v.plan.total_cost);
        return {charge, agreed, paid:ordinaryPaid+planPaid, ordinaryPaid, planPaid, balance:(ordinaryCharge-ordinaryPaid)+(cents(v.plan.total_cost)-planPaid)};
    }
    function totals() {
        let charge=0, agreed=0, paid=0;
        state.payload.visits.forEach((v,i) => {
            const f = figures(v); charge+=f.charge; agreed+=f.agreed; paid+=f.paid;
            const target = document.querySelector(`[data-totals="${i}"]`);
            if (target) target.innerHTML = `<span>Treatment charge: ${money(f.charge/100)}</span>${v.arrangement === 'installment' ? `<span>Installment plan: ${money(v.plan.total_cost)}</span><span>Ordinary received: ${money(f.ordinaryPaid/100)}</span><span>Plan received: ${money(f.planPaid/100)}</span>` : `<span>Received: ${money(f.paid/100)}</span>`}<span>Combined balance: ${money(f.balance/100)}</span><span>${f.balance < 0 ? 'Overpayment — correct before saving' : f.balance === 0 ? 'Fully paid' : f.paid ? 'Partially paid' : 'Unpaid'}</span>`;
            const suggestion = document.querySelector(`[data-plan-suggestion="${i}"]`);
            if (suggestion) suggestion.textContent = v.plan.is_open_contract == 1 ? `Suggested monthly: ${money(v.plan.open_monthly_payment)}. No paid months will be generated.` : `Suggested monthly: ${money((cents(v.plan.total_cost)-cents(v.plan.downpayment))/100/Math.max(1,Number(v.plan.months)))}. No paid months will be generated.`;
        });
        $('re-grand-totals').textContent = `Charges ${money(charge/100)} · Agreed total ${money(agreed/100)} · Received ${money(paid/100)} · Balance ${money((agreed-paid)/100)}`;
    }
    $('re-visits').addEventListener('input', event => {
        const el = event.target;
        if (el.dataset.searchService) {
            const select = document.querySelector(`[data-path="${el.dataset.searchService}.service_id"]`);
            const term = el.value.toLowerCase(), selected = select.value;
            select.innerHTML = option('', 'Select treatment', selected) + c.services.filter(s => s.name.toLowerCase().includes(term) || String(s.id) === selected).map(s => option(s.id,s.name,selected)).join(''); return;
        }
        if (!el.dataset.path || el.tagName === 'SELECT') return;
        at(el.dataset.path, el.value); el.removeAttribute('aria-invalid'); changed();
    });
    $('re-visits').addEventListener('change', event => {
        const el = event.target;
        if (el.dataset.link) {
            const [kind,id] = el.value.split(':');
            at(`${el.dataset.link}.visit_id`, kind === 'existing' ? Number(id) : null);
            at(`${el.dataset.link}.visit_index`, kind === 'batch' ? Number(id) : null); changed(); return;
        }
        if (!el.dataset.path || el.tagName !== 'SELECT') return;
        const path = el.dataset.path, i = Number(path.split('.')[1]), v = state.payload.visits[i];
        if (path.endsWith('.arrangement')) {
            if (el.value === 'ordinary' && v.plan && (cents(v.plan.downpayment)>0 || v.plan.payments.some(p => cents(p.amount)>0))) {
                if (!confirm('Switch to ordinary payments and remove the unsaved installment plan?')) {el.value=v.arrangement;return;}
            }
            if (el.value === 'installment' && !v.plan) {
                const procedureIndex = v.procedures.length === 1 ? 0 : '';
                v.plan = {procedure_index:procedureIndex,total_cost:procedureIndex === 0 ? v.procedures[0].price : '', downpayment:'0', start_date:v.visit_date, downpayment_date:v.visit_date, downpayment_method:'Cash', is_open_contract:0, months:'', open_monthly_payment:'', payments:[]};
            }
            if (el.value === 'ordinary') v.plan = null;
        }
        at(path, el.value);
        if (path.endsWith('.plan.procedure_index') && el.value !== '' && (v.plan.total_cost === '' || v.plan.total_cost === null)) {
            v.plan.total_cost = v.procedures[Number(el.value)]?.price ?? '';
        }
        if (path.endsWith('.service_id')) {
            const p = v.procedures[Number(path.split('.')[3])];
            // Suggest only while the amount is blank. Changing treatment never overwrites an entered charge.
            if (p.price === '' || p.price === null) p.price = c.services.find(s => String(s.id) === el.value)?.base_price ?? '';
        }
        changed(); render();
    });
    $('re-visits').addEventListener('click', event => {
        const el = event.target.closest('[data-action]'); if (!el) return;
        const i=Number(el.dataset.v), j=Number(el.dataset.j), v=state.payload.visits[i];
        switch (el.dataset.action) {
            case 'add-procedure':v.procedures.push(procedure());break;
            case 'remove-procedure':
                v.procedures.splice(j,1);
                v.payments.forEach(p=>{if(p.procedure_index!==''&&p.procedure_index!=null){if(Number(p.procedure_index)===j)p.procedure_index='';else if(Number(p.procedure_index)>j)p.procedure_index--;}});
                if(v.plan?.procedure_index!==''&&v.plan?.procedure_index!=null){if(Number(v.plan.procedure_index)===j)v.plan.procedure_index='';else if(Number(v.plan.procedure_index)>j)v.plan.procedure_index--;}
                break;
            case 'add-payment':v.payments.push(receipt(v.visit_date));break;
            case 'add-installment-payment':v.plan.payments.push({...receipt(c.mode === 'visit' ? c.today : ''),month_number:Math.max(0,...v.plan.payments.map(p => Number(p.month_number)||0))+1,visit_id:null,visit_index:null});break;
            case 'remove-payment':(el.dataset.installment === 'true' ? v.plan.payments : v.payments).splice(j,1);break;
            case 'duplicate': {
                const copy=structuredClone(v); copy.payments=[]; copy.plan=null; copy.arrangement='ordinary'; state.payload.visits.push(copy); break;
            }
            case 'remove-visit':
                if (!confirm(`Remove unsaved visit ${i+1} and its receipts?`)) return;
                state.payload.visits.splice(i,1);
                state.payload.visits.forEach(row => (row.plan?.payments || []).forEach(p => {if(p.visit_index!==null && p.visit_index!==undefined){if(Number(p.visit_index)===i)p.visit_index=null;else if(Number(p.visit_index)>i)p.visit_index--;}}));break;
        }
        changed(); render();
    });
    $('re-editor').addEventListener('keydown', event => {
        if (event.key !== 'Enter' || !event.target.matches('input') || event.isComposing) return;
        event.preventDefault(); const fields = [...$('re-editor').querySelectorAll('input,select,textarea,button')]; fields[fields.indexOf(event.target)+1]?.focus();
    });
    if ($('re-add-visit')) $('re-add-visit').onclick = () => {state.payload.visits.push(visit());changed();render();};
    $('re-default-doctor').addEventListener('change', event => {
        state.payload.default_doctor_id=event.target.value;
        state.payload.visits.forEach(v=>{if(!v.doctor_id)v.doctor_id=event.target.value;});
        changed();render();
    });
    document.querySelectorAll('[data-save-intent]').forEach(button => button.onclick = () => {
        const intent = button.dataset.saveIntent, v = state.payload.visits[0];
        if (intent === 'visit' && (v.arrangement === 'installment' || v.payments.some(p => cents(p.amount) > 0))) {
            errors({message:'Save Visit alone requires an ordinary visit with no receipt amounts. Clear the unsaved payments or use the matching payment/plan action.'}); return;
        }
        if (intent === 'payment' && v.arrangement === 'installment') {
            errors({message:'This visit uses an installment plan. Use Save Visit & Create Installment Plan, or change the arrangement to ordinary payments.'}); return;
        }
        if (intent === 'payment' && !v.payments.some(p => cents(p.amount) > 0)) {
            if (!v.payments.length) v.payments.push(receipt(c.today));
            changed(); render();
            document.querySelector('[data-path="visits.0.payments.0.amount"]')?.focus();
            $('re-status').textContent='Enter the amount received, then use Save Visit & Record Payment again.'; return;
        }
        if (intent === 'plan' && v.arrangement !== 'installment') {
            const select=document.querySelector('[data-path="visits.0.arrangement"]');
            select.value='installment';select.dispatchEvent(new Event('change',{bubbles:true}));
            if(v.arrangement==='installment'){
                document.querySelector('[data-path="visits.0.plan.total_cost"]')?.focus();
                $('re-status').textContent='Complete the plan details, then use Save Visit & Create Installment Plan again.';
            }
            return;
        }
        $('re-review-button').click();
    });
    $('re-save-draft').onclick = () => {dirty=true;saveDraft().catch(errors);};
    function setLocked(value) {locked=value; ['re-editor','re-footer','re-review','re-drafts'].forEach(id => {$(id).querySelectorAll('input,select,textarea,button').forEach(el => el.disabled=value);});}
    $('re-review-button').onclick = async () => {
        clearTimeout(timer); $('re-errors').hidden=true;
        try {
            setLocked(true);
            await enqueue(async () => {
                reviewed = await request('review','POST',body()); state.version=reviewed.version;
                state.payload=reviewed.payload; dirty=false;upsertDraft({...state,updated_at:new Date().toISOString()});localSave();
            });
            $('re-editor').hidden=true; $('re-review').hidden=false;
            const summaries = state.payload.visits.map((v,i) => {
                const f=figures(v);
                const lines=v.procedures.map(p => `${c.services.find(s=>s.id==p.service_id)?.name || p.service_id} · tooth ${p.tooth_number || '—'} · ${money(p.price)}`).join('\n');
                const ordinaryReceipts=v.payments.map(p => {
                    const treatment=p.procedure_index!==''&&p.procedure_index!=null ? c.services.find(s=>s.id==v.procedures[Number(p.procedure_index)]?.service_id)?.name : 'Entire visit';
                    return `${p.payment_date} · ${p.method} · ${money(p.amount)} · For ${treatment}`;
                }).join('\n');
                const installmentReceipts=v.arrangement==='installment' ? v.plan.payments.map(p => `${p.payment_date} · Payment ${p.month_number} · ${p.method} · ${money(p.amount)}${p.visit_id ? ' · Visit #'+p.visit_id : p.visit_index != null ? ' · Batch visit '+(Number(p.visit_index)+1) : ' · Collection only'}`).join('\n') : '';
                const financed=v.arrangement==='installment' ? c.services.find(s=>s.id==v.procedures[Number(v.plan.procedure_index)]?.service_id)?.name : '';
                return `<div class="re-row"><strong>Visit ${i+1} · ${esc(v.visit_date)} · ${esc(c.doctors.find(d=>d.id==v.doctor_id)?.name)}</strong><div class="re-summary">${esc(lines)}</div><div class="re-summary mt-2">${ordinaryReceipts ? `Ordinary receipts:\n${esc(ordinaryReceipts)}\n` : ''}${v.arrangement==='installment' ? `Installment for ${esc(financed)} · Plan cost ${money(v.plan.total_cost)} · ${v.plan.is_open_contract == 1 ? 'Open contract' : v.plan.months+' months'}\nDownpayment ${money(v.plan.downpayment)} · ${esc(v.plan.downpayment_date || 'No downpayment')} · ${esc(v.plan.downpayment_method || '')}\n${installmentReceipts ? `Installment receipts:\n${esc(installmentReceipts)}` : 'No additional installment receipts'}` : (!ordinaryReceipts ? 'No receipts' : '')}</div><strong>Received ${money(f.paid/100)} · Combined balance ${money(f.balance/100)}</strong></div>`;
            }).join('');
            const warnings=reviewed.warnings.map(w=>`<li>${esc(w)}</li>`).join('');
            let saveLabel='Save All Records';
            if(c.mode==='visit') {const v=state.payload.visits[0];saveLabel=v.arrangement==='installment'?'Save Visit & Create Installment Plan':v.payments.length?'Save Visit & Record Payment':'Save Visit';}
            $('re-review').innerHTML=`<h3>Review Records</h3>${summaries}${warnings?`<div class="alert alert-warning"><strong>Possible duplicates</strong><ul>${warnings}</ul><label><input type="checkbox" id="re-ack"> I checked these matches and confirm these are separate records.</label></div>`:'<p>No possible duplicates found.</p>'}<div class="re-actions"><button class="btn btn-outline-secondary" type="button" id="re-back">Return to correct entry</button><button class="btn btn-primary" type="button" id="re-save-all">${saveLabel}</button></div>`;
            $('re-back').onclick=()=>{$('re-review').hidden=true;$('re-editor').hidden=false;render();};
            $('re-save-all').onclick=saveAll;
            $('re-status').textContent='Reviewed — no clinical records saved yet';$('re-review').scrollIntoView({block:'start'});
        } catch(error) {errors(error);} finally {setLocked(false);}
    };
    async function saveAll() {
        if (!reviewed) return;
        try {setLocked(true);const result=await request('save','POST',{review_hash:reviewed.review_hash,acknowledge_duplicates:!!$('re-ack')?.checked});dirty=false;clearLocal();location.href=result.redirect;}
        catch(error){errors(error);setLocked(false);}
    }
    $('re-discard').onclick=async()=>{
        if(!confirm('Discard this unfinished draft? Saved patient records will remain unchanged.'))return;
        clearTimeout(timer);
        try{setLocked(true);await enqueue(async()=>{if(state.version>0)await request('draft','DELETE',{});});clearLocal();dirty=false;location.href=`${c.baseUrl}?patient_id=${c.patientId}&mode=${c.mode}`;}catch(error){errors(error);setLocked(false);}
    };
    function resume(draft, fromCache=false) {
        clearTimeout(timer);
        state={id:draft.id,version:draft.version,payload:structuredClone(draft.payload)};
        reviewed=null;dirty=fromCache;revision++;
        $('re-default-doctor').value=state.payload.default_doctor_id || '';
        localSave();render();$('re-drafts').hidden=true;$('re-drafts-toggle').setAttribute('aria-expanded','false');
        $('re-status').textContent=fromCache?'Recovered unsaved changes — save draft to sync':'Draft restored';
    }
    function draftDate(value) {
        if (!value || value === 'This browser tab') return 'Unsaved changes in this browser tab';
        const date = new Date(value);
        return Number.isNaN(date.getTime()) ? String(value) : `Updated ${date.toLocaleString('en-PH',{dateStyle:'medium',timeStyle:'short'})}`;
    }
    function upsertDraft(draft) {
        const index=drafts.findIndex(item=>String(item.id)===String(draft.id));
        const snapshot={...draft,payload:structuredClone(draft.payload)};
        if(index>=0)drafts[index]=snapshot;else drafts.unshift(snapshot);
        renderDrafts();
    }
    function renderDrafts() {
        const toggle=$('re-drafts-toggle'), panel=$('re-drafts'), count=$('re-drafts-count');
        toggle.hidden=!drafts.length;count.textContent=String(drafts.length);
        if(!drafts.length){panel.hidden=true;toggle.setAttribute('aria-expanded','false');return;}
        panel.innerHTML=`<div class="d-flex justify-content-between align-items-center gap-2"><div><h3 class="mb-1">Saved drafts</h3><p class="re-muted mb-0">Drafts for this patient and your staff account.</p></div><button type="button" class="btn btn-sm btn-outline-secondary" data-draft-close aria-label="Close drafts"><i class="fa fa-xmark"></i></button></div><div>${drafts.map(d=>`<div class="re-draft-item"><div class="re-draft-meta"><strong>${esc(d.payload?.visits?.length || 0)} visit(s)</strong><span>${esc(draftDate(d.updated_at))}</span></div><div class="re-actions m-0"><button type="button" class="btn btn-sm btn-outline-primary" data-draft-continue="${esc(d.id)}">Continue</button><button type="button" class="btn btn-sm btn-outline-danger" data-draft-discard="${esc(d.id)}">Discard</button></div></div>`).join('')}</div><button type="button" class="btn btn-sm btn-outline-secondary mt-2" data-draft-new>Start a new batch</button>`;
    }
    drafts=[...c.drafts];
    if(cached?.payload?.visits && !drafts.some(d=>d.id===cached.id)) drafts.unshift({...cached,updated_at:'This browser tab',local:true});
    renderDrafts();
    $('re-drafts-toggle').addEventListener('click',()=>{const open=$('re-drafts').hidden;$('re-drafts').hidden=!open;$('re-drafts-toggle').setAttribute('aria-expanded',String(open));});
    $('re-drafts').addEventListener('click',event=>{
        if(event.target.closest('[data-draft-close]')){$('re-drafts').hidden=true;$('re-drafts-toggle').setAttribute('aria-expanded','false');return;}
        if(event.target.closest('[data-draft-new]')){
            if(dirty&&!confirm('Start a new batch? Save the current draft first if you want to keep these changes.'))return;
            clearTimeout(timer);clearLocal();dirty=false;reviewed=null;state={id:uuid(),version:0,payload:{default_doctor_id:'',visits:[visit()]}};
            $('re-default-doctor').value='';$('re-drafts').hidden=true;$('re-drafts-toggle').setAttribute('aria-expanded','false');$('re-status').textContent='New unsaved batch';render();return;
        }
        const continueButton=event.target.closest('[data-draft-continue]');
        if(continueButton){
            const draft=drafts.find(item=>String(item.id)===continueButton.dataset.draftContinue);if(!draft)return;
            if(dirty&&!confirm('Switch drafts? Save the current draft first if you want to keep it.'))return;
            const local=cached?.id===draft.id&&cached.version===draft.version?cached:null;
            enqueue(async()=>resume(local||draft,!!local||!!draft.local)).catch(errors);return;
        }
        const discardButton=event.target.closest('[data-draft-discard]');
        if(discardButton){
            const draft=drafts.find(item=>String(item.id)===discardButton.dataset.draftDiscard);if(!draft||!confirm('Discard this unfinished draft? Saved patient records remain unchanged.'))return;
            enqueue(async()=>{
                if(!draft.local&&Number(draft.version)>0)await request('draft','DELETE',{},draft.id);
                drafts=drafts.filter(item=>String(item.id)!==String(draft.id));
                if(String(state.id)===String(draft.id)){clearLocal();dirty=false;reviewed=null;state={id:uuid(),version:0,payload:{default_doctor_id:'',visits:[visit()]}};$('re-default-doctor').value='';$('re-status').textContent='New unsaved batch';render();}
                renderDrafts();
            }).catch(errors);
        }
    });
    document.addEventListener('keydown',event=>{if(event.key==='Escape'&&!$('re-drafts').hidden){$('re-drafts').hidden=true;$('re-drafts-toggle').setAttribute('aria-expanded','false');}});
    window.addEventListener('beforeunload',event=>{if(dirty){event.preventDefault();event.returnValue='';}});
    render();
})();

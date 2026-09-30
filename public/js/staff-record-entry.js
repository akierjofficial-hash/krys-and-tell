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
        let matches = [], active = -1, timer, controller, sequence = 0;
        function openPatient(patient) {
            const url = new URL(c.baseUrl);
            url.searchParams.set('patient_id', patient.id);
            url.searchParams.set('mode', c.mode);
            location.assign(url.toString());
        }
        function renderPatients() {
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
        async function searchPatients() {
            controller?.abort();
            controller = new AbortController();
            const ticket = ++sequence;
            results.hidden = false;
            results.innerHTML = '<div class="re-muted p-3" role="status">Searching patients…</div>';
            input.setAttribute('aria-expanded', 'true');
            try {
                const url = new URL(c.patientSearchUrl);
                url.searchParams.set('q', input.value.trim());
                const response = await fetch(url, {signal: controller.signal, headers: {'Accept':'application/json'}});
                if (!response.ok) throw new Error('Lookup unavailable');
                const data = await response.json();
                if (ticket !== sequence) return;
                matches = Array.isArray(data.patients) ? data.patients : [];
                renderPatients();
            } catch (error) {
                if (error.name !== 'AbortError' && ticket === sequence) {
                    results.innerHTML = '<div class="re-muted p-3" role="alert">Patient search is unavailable. Please retry.</div>';
                }
            }
        }
        function queueSearch(immediate = false) {
            clearTimeout(timer);
            controller?.abort();
            ++sequence;
            if (immediate) searchPatients();
            else timer = setTimeout(searchPatients, 220);
        }
        function closePatients() {
            clearTimeout(timer);
            controller?.abort();
            ++sequence;
            results.hidden = true; input.setAttribute('aria-expanded', 'false'); input.removeAttribute('aria-activedescendant'); active = -1;
        }
        input.addEventListener('focus', () => queueSearch(true));
        input.addEventListener('input', () => {active = -1;queueSearch();});
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') {closePatients();return;}
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                if (results.hidden) queueSearch(true);
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
        badge.classList.toggle('re-state-success', /saved|restored|reviewed/.test(message) && !/unsaved|not saved|no .* saved|waiting|failed|needs/.test(message));
        badge.classList.toggle('re-state-warning', /unsaved|not saved|waiting|recovered|enter the|failed|needs/.test(message));
    }).observe(statusNode, {childList:true, subtree:true, characterData:true});
    const uuid = () => crypto.randomUUID ? crypto.randomUUID() : '10000000-1000-4000-8000-100000000000'.replace(/[018]/g, n => (n ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> n / 4).toString(16));
    const procedure = () => ({service_id:'', tooth_number:'', surface:'', shade:'', price:'', notes:'', related_context:''});
    const receipt = date => ({amount:'', payment_date:date || c.today, method:'Cash', notes:'', procedure_index:''});
    const visit = () => ({visit_date:c.mode === 'visit' ? c.today : '', doctor_id:$('re-default-doctor').value, notes:'', arrangement:'ordinary', procedures:[procedure()], payments:[], plan:null});
    let state = {id:uuid(), version:0, payload:{default_doctor_id:'',visits:[visit()]}}, drafts = [];
    let dirty = false, reviewed = null, timer, queue = Promise.resolve(), locked = false, revision = 0;
    let fieldErrors = {};
    const cached = (() => {try {return JSON.parse(sessionStorage.getItem(storageKey));} catch {return null;}})();
    function localSave() {try {sessionStorage.setItem(storageKey, JSON.stringify(state));} catch { /* Server draft remains primary; unload warning stays enabled. */ }}
    function clearLocal() {try {sessionStorage.removeItem(storageKey);} catch {}}
    function errorLocation(path) {
        const parts = path.split('.');
        const names = {
            visit_date:'Visit date', doctor_id:'Dentist', service_id:'Treatment / service', price:'Actual charge',
            tooth_number:'Tooth number', surface:'Surface', shade:'Shade', notes:'Notes', related_context:'Related braces treatment',
            arrangement:'Payment arrangement', procedures:'Procedures', payments:'Receipts',
            amount:'Amount', payment_date:'Payment date', method:'Payment method',
            procedure_index:'Treatment paid', month_number:'Payment number', total_cost:'Agreed total',
            downpayment:'Initial payment', downpayment_date:'Initial payment date',
            downpayment_method:'Initial payment method', start_date:'Plan start date',
            months:'Number of months', open_monthly_payment:'Monthly amount',
            first_due_date:'First monthly due date', ended_at:'Treatment end date',
            acknowledge_unpaid:'Monthly-obligation review', duplicates:'Possible duplicates',
            default_doctor_id:'Default dentist', payload:'Record batch',
        };
        if (path === 'visits') return 'Visits';
        if (parts[0] !== 'visits') return names[parts.at(-1)] || path.replaceAll('_', ' ');
        if (!/^\d+$/.test(parts[1] || '')) return 'Visits';
        const labels = [`Visit ${Number(parts[1]) + 1}`];
        let index = 2;
        if (parts[index] === 'procedures' && /^\d+$/.test(parts[index + 1] || '')) {
            labels.push(`Procedure ${Number(parts[index + 1]) + 1}`); index += 2;
        } else if (parts[index] === 'payments' && /^\d+$/.test(parts[index + 1] || '')) {
            labels.push(`Ordinary receipt ${Number(parts[index + 1]) + 1}`); index += 2;
        } else if (parts[index] === 'plan') {
            labels.push('Installment plan'); index++;
            if (parts[index] === 'payments' && /^\d+$/.test(parts[index + 1] || '')) {
                labels.push(`Monthly receipt ${Number(parts[index + 1]) + 1}`); index += 2;
            }
        }
        const field = parts.slice(index).at(-1);
        if (field) labels.push(names[field] || field.replaceAll('_', ' '));
        return labels.join(' · ');
    }
    function errorSummary(error) {
        const count = Object.keys(error.errors || {}).length;
        if (count) return `Correct ${count} ${count === 1 ? 'field' : 'fields'} below. Your entry is still here and has not been saved as patient records.`;
        if ([401, 403, 419].includes(error.status)) return 'Your session expired or access changed. Sign in again, then reopen this patient record. Your unsaved entry remains in this browser tab.';
        if (error.status >= 500 || error.status === 0) return 'The clinic server could not complete this request. Your entry is still here. Check the connection and retry; if it happens again, contact the administrator.';
        return error.message || 'This action could not be completed. Your entry is still here.';
    }
    function errors(error, action = 'review') {
        fieldErrors = error.errors || {};
        statusNode.textContent = action === 'draft' ? 'Draft not saved — correct the issue below'
            : action === 'review' ? 'Review needs corrections — no records saved'
            : 'Action not completed — entry remains here';
        const box = $('re-errors'); box.hidden = false; box.replaceChildren();
        const title = document.createElement('strong'); title.textContent = errorSummary(error); box.append(title);
        Object.entries(error.errors || {}).forEach(([path, messages]) => {
            const button = document.createElement('button'); button.type = 'button'; button.className = 'btn d-block re-error-link';
            button.textContent = `${errorLocation(path)}: ${[].concat(messages).join(' ')}`;
            button.onclick = () => {
                $('re-editor').hidden = false;
                const fields = [...document.querySelectorAll('[data-path]')];
                const field = (path === 'default_doctor_id' ? $('re-default-doctor') : null)
                    || (path.endsWith('.service_id') ? document.querySelector(`[data-service-picker="${path.replace(/\.service_id$/, '')}"]`) : null)
                    || fields.find(el => el.dataset.path === path)
                    || (path.endsWith('.plan.payments') ? fields.find(el => el.dataset.path.startsWith(`${path}.`) && el.dataset.path.endsWith('.amount')) : null)
                    || (path.endsWith('.plan.payments') ? fields.find(el => el.dataset.path === path.replace(/\.payments$/, '.downpayment')) : null);
                if (field) {
                    field.closest('details')?.setAttribute('open', '');
                    field.setAttribute('aria-invalid', 'true'); field.scrollIntoView({block:'center'}); field.focus();
                }
            }; box.append(button);
        });
        showFieldErrors();
        box.scrollIntoView({block:'start'});
    }
    function showFieldErrors() {
        document.querySelectorAll('.re-field-error').forEach(node => node.remove());
        document.querySelectorAll('#re-visits [aria-invalid="true"], #re-default-doctor[aria-invalid="true"]').forEach(node => node.removeAttribute('aria-invalid'));
        Object.entries(fieldErrors).forEach(([path, messages]) => {
            const field = path === 'default_doctor_id' ? $('re-default-doctor')
                : path.endsWith('.service_id') ? document.querySelector(`[data-service-picker="${path.replace(/\.service_id$/, '')}"]`)
                : document.querySelector(`[data-path="${path}"]`);
            const target = field
                || (path.endsWith('.plan.payments') ? document.querySelector(`[data-path^="${path}."][data-path$=".amount"]`) || document.querySelector(`[data-path="${path.replace(/\.payments$/, '.downpayment')}"]`) : null)
                || (path.endsWith('.payments') ? document.querySelector(`[data-path^="${path}."][data-path$=".amount"]`) : null)
                || (path.endsWith('.procedures') ? document.querySelector(`[data-service-picker="${path}.0"]`) : null)
                || (path.endsWith('.plan.total_cost') ? document.querySelector(`[data-path="${path.replace(/\.plan\.total_cost$/, '.plan.procedure_index')}"]`) : null);
            if (!target) return;
            target.closest('details')?.setAttribute('open', '');
            target.setAttribute('aria-invalid', 'true');
            const message = document.createElement('small'); message.className = 're-field-error';
            message.textContent = [].concat(messages).join(' ');
            (target.closest('label') || target.parentElement).append(message);
        });
    }
    async function request(action, method, body, batchId=state.id) {
        let response;
        try {
            response = await fetch(`${c.baseUrl}/${batchId}/${action}`, {method, credentials:'same-origin', headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':c.csrf}, body:JSON.stringify(body)});
        } catch { throw {status:0}; }
        let json; try {json = await response.json();} catch {throw {status:response.status, message:'The server returned an unreadable response. Your entry is still here; retry, then contact the administrator if it continues.'};}
        if (!response.ok) throw {...json, status:response.status};
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
        fieldErrors = {};
        $('re-errors').hidden = true;
        document.querySelectorAll('.re-field-error').forEach(node => node.remove());
        document.querySelectorAll('#re-visits [aria-invalid="true"], #re-default-doctor[aria-invalid="true"]').forEach(node => node.removeAttribute('aria-invalid'));
        revision++; dirty = true; reviewed = null;
        $('re-review').hidden = true; $('re-editor').hidden = false;
        $('re-status').textContent = 'Unsaved changes'; localSave(); totals();
        syncRequiredIndicators();
        clearTimeout(timer); timer = setTimeout(() => saveDraft().catch(error => errors(error, 'draft')), 900);
    }
    function at(path, value) {
        const parts = path.split('.'); let object = state.payload;
        parts.slice(0,-1).forEach(key => object = object[key]); object[parts.at(-1)] = value;
    }
    function requiredForPath(path) {
        const match = /^visits\.(\d+)\.(.+)$/.exec(path);
        if (!match) return false;
        const visit = state.payload.visits[Number(match[1])];
        if (!visit) return false;
        const field = match[2];
        if (['visit_date', 'doctor_id', 'arrangement'].includes(field)) return true;
        const procedure = /^procedures\.(\d+)\.(service_id|price)$/.exec(field);
        if (procedure) return procedure[2] === 'service_id'
            || !(visit.plan?.is_unpriced_contract == 1 && Number(visit.plan.procedure_index) === Number(procedure[1]));
        const ordinary = /^payments\.(\d+)\.(amount|payment_date|method|procedure_index)$/.exec(field);
        if (ordinary) {
            const hasAmount = visit.payments[Number(ordinary[1])]?.amount !== '' && visit.payments[Number(ordinary[1])]?.amount != null;
            return hasAmount && (ordinary[2] !== 'procedure_index' || visit.arrangement === 'installment');
        }
        if (!field.startsWith('plan.') || !visit.plan) return false;
        const plan = visit.plan;
        const planField = field.slice(5);
        if (['procedure_index', 'start_date', 'downpayment', 'is_open_contract'].includes(planField)) return true;
        if (planField === 'total_cost') return plan.is_unpriced_contract != 1;
        if (['downpayment_date', 'downpayment_method'].includes(planField)) return cents(plan.downpayment) > 0;
        if (['open_monthly_payment', 'first_due_date', 'ended_at', 'acknowledge_unpaid'].includes(planField)) return plan.is_unpriced_contract == 1;
        if (planField === 'months') return plan.is_open_contract == 0;
        const monthly = /^payments\.(\d+)\.(month_number|amount|payment_date|method)$/.exec(planField);
        if (monthly) {
            const amount = plan.payments[Number(monthly[1])]?.amount;
            return amount !== '' && amount != null;
        }
        return false;
    }
    function syncRequiredIndicators() {
        const root = $('re-visits');
        root.querySelectorAll('[data-path]').forEach(field => {
            if (requiredForPath(field.dataset.path)) field.setAttribute('aria-required', 'true');
            else field.removeAttribute('aria-required');
        });
        window.KTRequiredFields?.refresh(root);
    }
    function input(label, path, value, type='text', extra='') {
        return `<label><span class="re-field-caption">${esc(label)}</span><input data-path="${path}" type="${type}" value="${esc(value)}" ${requiredForPath(path) ? 'aria-required="true"' : ''} ${type === 'number' ? 'step="0.01" min="0"' : ''} ${extra}></label>`;
    }
    function select(label, path, options) {return `<label><span class="re-field-caption">${esc(label)}</span><select data-path="${path}" ${requiredForPath(path) ? 'aria-required="true"' : ''}>${options}</select></label>`;}
    function notes(label, path, value) {return `<label><span class="re-field-caption">${esc(label)}</span><textarea data-path="${path}" maxlength="2000">${esc(value)}</textarea></label>`;}
    function serviceName(id) {
        if (id === '' || id === null || id === undefined) return '';
        return c.services.find(service => String(service.id) === String(id))?.name || `Treatment #${id} (unavailable — choose another)`;
    }
    function servicePicker(path, selectedId, visitIndex, procedureIndex) {
        const listId = `re-service-options-${visitIndex}-${procedureIndex}`;
        return `<div class="re-service-field"><label for="re-service-${visitIndex}-${procedureIndex}">Treatment / service</label><div class="re-service-picker"><input id="re-service-${visitIndex}-${procedureIndex}" type="search" role="combobox" aria-autocomplete="list" aria-required="true" aria-expanded="false" aria-controls="${listId}" autocomplete="off" data-service-picker="${path}" value="${esc(serviceName(selectedId))}" placeholder="Search or choose treatment"><input type="hidden" data-path="${path}.service_id" value="${esc(selectedId)}"><div id="${listId}" class="re-service-options" role="listbox" hidden></div></div></div>`;
    }
    function treatmentOptions(v, selected, emptyLabel, excludeRecement=false) {
        return option('', emptyLabel, selected) + state.payload.visits[v].procedures.map((p,i) => {
            if (excludeRecement && String(p.service_id) === String(c.recementServiceId)) return '';
            const service = c.services.find(s => String(s.id) === String(p.service_id));
            return option(i, `${service?.name || `Treatment ${i+1}`} — ${p.price === null || p.price === '' ? 'Charge not agreed' : money(p.price)}`, selected);
        }).join('');
    }
    function paymentRow(p, path, v, j, installment=false) {
        let related = '';
        if (installment) {
            const selected = p.visit_id ? `existing:${p.visit_id}` : (p.visit_index !== undefined && p.visit_index !== null && p.visit_index !== '' ? `batch:${p.visit_index}` : '');
            related = `<label>Related treatment (optional)<select data-link="${path}">${option('', 'Collection only — no visit', selected)}${state.payload.visits.map((row,i) => option(`batch:${i}`, `Batch visit ${i+1}: ${row.visit_date || 'Date not entered'}`, selected)).join('')}${c.existingVisits.map(row => option(`existing:${row.id}`, `Existing #${row.id}: ${row.visit_date.slice(0,10)}`, selected)).join('')}</select></label>`;
        }
        const appliesTo = installment ? '' : select('Payment applies to', `${path}.procedure_index`, treatmentOptions(v, p.procedure_index, state.payload.visits[v].arrangement === 'installment' ? 'Choose non-installment treatment' : 'Entire visit / not allocated'));
        const details = installment ? `<details class="re-receipt-extra" ${p.notes || p.visit_id || p.visit_index !== undefined && p.visit_index !== null && p.visit_index !== '' ? 'open' : ''}><summary>Optional receipt details</summary><div class="re-grid">${notes('Receipt notes', `${path}.notes`, p.notes)}${related}</div></details>` : '';
        return `<div class="re-row re-receipt-row"><div class="re-row-heading"><strong>${installment ? 'Monthly receipt' : 'Ordinary receipt'} ${j+1}</strong><button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-payment" data-v="${v}" data-j="${j}" data-installment="${installment}">Remove receipt</button></div><div class="re-grid re-payment-grid">${installment ? input('Month number', `${path}.month_number`, p.month_number, 'number', 'inputmode="numeric"') : ''}${appliesTo}${input('Amount received', `${path}.amount`, p.amount, 'number')}${input('Payment date', `${path}.payment_date`, p.payment_date, 'date')}${select('Payment method', `${path}.method`, methods.map(m => option(m,m,p.method)).join(''))}${installment ? '' : notes('Receipt notes', `${path}.notes`, p.notes)}</div>${details}</div>`;
    }
    function section(title, content, className = '') {
        return `<section class="re-section ${className}"><h4>${title}</h4>${content}</section>`;
    }
    function planFields(v, i, base, arrangementChoices) {
        const p = v.plan, path = `${base}.plan`, unknown = p.is_unpriced_contract == 1;
        const overview = `<div class="re-grid re-plan-overview">${select('Financed treatment',`${path}.procedure_index`,treatmentOptions(i,p.procedure_index,'Choose financed treatment',true))}${input('Plan start date',`${path}.start_date`,p.start_date,'date')}${unknown ? '<p class="re-unknown-total">Final total not agreed. The financed procedure charge stays blank.</p>' : input('Total agreed cost',`${path}.total_cost`,p.total_cost,'number')}</div>`;
        const initial = `<div class="re-grid re-plan-grid">${input('Initial payment received (counted once)',`${path}.downpayment`,p.downpayment,'number')}${input('Initial payment date',`${path}.downpayment_date`,p.downpayment_date,'date')}${select('Initial payment method',`${path}.downpayment_method`,methods.map(m => option(m,m,p.downpayment_method)).join(''))}${unknown ? `${input('Monthly amount',`${path}.open_monthly_payment`,p.open_monthly_payment,'number')}${input('First monthly due date',`${path}.first_due_date`,p.first_due_date,'date')}` : `${select('Contract term',`${path}.is_open_contract`,option(0,'Fixed term',p.is_open_contract)+option(1,'Open term, agreed total',p.is_open_contract))}${p.is_open_contract == 1 ? input('Suggested monthly amount (optional)',`${path}.open_monthly_payment`,p.open_monthly_payment,'number') : input('Number of months',`${path}.months`,p.months,'number','inputmode="numeric"')}`}</div>`;
        const end = unknown ? `<label class="re-ended-choice"><input type="checkbox" data-ended-toggle="${i}" ${p.ended_at ? 'checked' : ''}> Treatment has ended</label><div class="re-end-fields" ${p.ended_at ? '' : 'hidden'}><div class="re-grid">${input('Treatment end date',`${path}.ended_at`,p.ended_at,'date')}</div><label class="re-closure-review"><input type="checkbox" data-path="${path}.acknowledge_unpaid" value="1" ${p.acknowledge_unpaid == 1 ? 'checked' : ''}> I reviewed the monthly amounts due through the end date and any unpaid amount.</label></div>` : '';
        const paste = c.mode === 'past' && unknown ? `<details class="re-paste-panel"><summary>Paste multiple past receipts</summary><div class="re-paste-content"><p class="re-muted">One per line: YYYY-MM-DD, amount, method. Example: 2024-02-01, 2000, Cash. Do not include the initial payment again.</p><label for="re-monthly-import-${i}">Past monthly receipts</label><textarea id="re-monthly-import-${i}" class="form-control" data-monthly-import="${i}" rows="4" placeholder="2024-02-01, 2000, Cash"></textarea><button type="button" class="btn btn-outline-secondary btn-sm" data-action="import-monthly-receipts" data-v="${i}">Import pasted receipts</button></div></details>` : '';
        const receipts = `<div class="re-subsection"><h5>Past monthly payments</h5><p class="re-muted">Initial payment is counted above. Add only later monthly receipts here; a receipt does not create a treatment visit.</p>${p.payments.map((r,j) => paymentRow(r,`${path}.payments.${j}`,i,j,true)).join('')}<div class="re-payment-actions"><button type="button" class="btn btn-outline-primary btn-sm" data-action="add-installment-payment" data-v="${i}">+ Add monthly receipt</button>${paste}</div></div>`;
        const fields = document.createElement('div');
        fields.innerHTML = overview + initial;
        const overviewFields = [...fields.querySelector('.re-plan-overview').children];
        const paymentFields = [...fields.querySelector('.re-plan-grid').children];
        const agreement = `<div class="re-grid re-plan-agreement">${overviewFields[0].outerHTML}${arrangementChoices}${overviewFields[1].outerHTML}${unknown ? paymentFields.slice(3).map(el => el.outerHTML).join('') : overviewFields[2].outerHTML + paymentFields.slice(3).map(el => el.outerHTML).join('')}</div>${unknown ? overviewFields[2].outerHTML : ''}<p class="re-plan-notice" data-plan-charge-notice="${i}" hidden>This treatment says “Charge not agreed.” A fixed-total plan needs the agreed charge in Procedures and the total agreed cost here. If no final total was agreed, choose Open contract.</p>${end}<p class="re-muted re-plan-hint" data-plan-suggestion="${i}"></p>`;
        const initialPayment = `<p class="re-muted">Enter this payment once. Do not add it again as a monthly receipt.</p><div class="re-grid re-plan-initial">${paymentFields.slice(0,3).map(el => el.outerHTML).join('')}</div>`;
        return section('Installment plan', `<div class="re-plan-block"><h5>Agreement</h5>${agreement}</div><div class="re-plan-block"><h5>Initial payment</h5>${initialPayment}</div><div class="re-plan-block"><h5>Past payments</h5>${receipts}</div><div class="re-plan-summary" data-plan-summary="${i}" aria-live="polite"></div>`, 're-plan-section');
    }
    function render(focusServicePath = null) {
        const focusPath = document.activeElement?.dataset?.path;
        const pasted = [...document.querySelectorAll('[data-monthly-import]')].map(el => [el.dataset.monthlyImport, el.value, el.closest('details')?.open]);
        const expandedProcedures = new Set([...document.querySelectorAll('[data-procedure-details][open]')].map(el => el.dataset.procedureDetails));
        const batchCount = $('re-batch-count');
        if (batchCount) batchCount.textContent = `${state.payload.visits.length} ${state.payload.visits.length === 1 ? 'record' : 'records'} in batch`;
        $('re-visits').innerHTML = state.payload.visits.map((v,i) => {
            const base = `visits.${i}`;
            const procs = v.procedures.map((p,j) => {
                const path = `${base}.procedures.${j}`;
                const details = [p.tooth_number, p.surface, p.shade, p.notes].some(Boolean);
                const recementContext = String(p.service_id) === String(c.recementServiceId)
                    ? `<div class="re-recement-context">${select('Related braces visit or plan (optional)',`${path}.related_context`,option('', 'No linked braces record', p.related_context)+(c.recementContexts || []).map(row => option(row.value,row.label,p.related_context)).join(''))}<p class="re-muted">Context only. Recement is billed separately from the braces plan; the usual charge is ₱500 and you may adjust it above.</p></div>` : '';
                return `<div class="re-row re-procedure-row"><div class="re-row-heading"><strong>Procedure ${j+1}</strong><button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-procedure" data-v="${i}" data-j="${j}">Remove procedure</button></div><div class="re-grid re-procedure-main">${servicePicker(path,p.service_id,i,j)}${input(v.plan?.is_unpriced_contract == 1 && Number(v.plan.procedure_index) === j ? 'Actual charge (not agreed)' : 'Actual charge',`${path}.price`,p.price,'number')}</div>${recementContext}<details class="re-procedure-extra" data-procedure-details="${i}-${j}" ${details || expandedProcedures.has(`${i}-${j}`) ? 'open' : ''}><summary>More details: tooth, surface, shade & notes</summary><div class="re-grid re-procedure-extra-grid">${input('Tooth number(s)',`${path}.tooth_number`,p.tooth_number,'text','maxlength="50"')}${input('Surface',`${path}.surface`,p.surface,'text','maxlength="10"')}${input('Shade',`${path}.shade`,p.shade,'text','maxlength="10"')}${notes('Procedure notes',`${path}.notes`,p.notes)}</div></details></div>`;
            }).join('');
            const ordinaryReceipts = section('Ordinary payments', `<p class="re-muted">Use for treatments paid outside a plan. For mixed billing, choose the treatment each receipt pays for.</p>${v.payments.map((p,j) => paymentRow(p,`${base}.payments.${j}`,i,j)).join('')}<button type="button" class="btn btn-outline-primary btn-sm" data-action="add-payment" data-v="${i}">+ Add ordinary receipt</button>`, 're-ordinary-section');
            const arrangement = v.arrangement === 'ordinary' ? 'ordinary' : v.plan?.is_unpriced_contract == 1 ? 'open' : 'installment';
            const arrangementChoices = select('Billing type',`${base}.arrangement`,option('ordinary','Paid normally / unpaid',arrangement)+option('installment','Fixed-total installment',arrangement)+option('open','Open contract — monthly fee until treatment ends',arrangement));
            return `<section class="re-card re-visit-card" aria-label="Visit ${i+1}"><div class="re-visit-heading"><h3>Visit ${i+1}</h3>${c.mode === 'past' ? `<div class="re-actions"><button type="button" class="btn btn-sm btn-outline-secondary" data-action="duplicate" data-v="${i}">Duplicate visit</button><button type="button" class="btn btn-sm btn-outline-danger" data-action="remove-visit" data-v="${i}">Remove unsaved visit</button></div>` : ''}</div>${section('Visit details',`<div class="re-grid re-visit-grid">${input('Visit date',`${base}.visit_date`,v.visit_date,'date')}${select('Dentist',`${base}.doctor_id`,option('','Select dentist',v.doctor_id)+c.doctors.map(d => option(d.id,d.name+(d.is_active ? '' : ' (inactive)'),v.doctor_id)).join(''))}${notes('Visit notes',`${base}.notes`,v.notes)}</div>`)}${section('Procedures',`${procs}<button type="button" class="btn btn-outline-primary btn-sm" data-action="add-procedure" data-v="${i}">+ Add procedure</button>`)}${v.arrangement === 'installment' ? planFields(v,i,base,arrangementChoices) : section('Payment arrangement',`<div class="re-grid re-arrangement-grid">${arrangementChoices}</div><p class="re-muted">Choose how this visit is billed. Ordinary receipts may also be entered for other procedures.</p>`)}${ordinaryReceipts}${section('Review summary',`<div class="re-totals" data-totals="${i}" aria-live="polite"></div>`,'re-summary-section')}</section>`;
        }).join('');
        pasted.forEach(([i, value, open]) => {
            const field = document.querySelector(`[data-monthly-import="${i}"]`);
            if (field) { field.value = value; if (open) field.closest('details').open = true; }
        });
        showFieldErrors();
        syncRequiredIndicators();
        totals();
        if (focusServicePath) {
            const picker = document.querySelector(`[data-service-picker="${focusServicePath}"]`);
            picker?.focus();
            if (picker) closeServicePicker(picker);
        }
        else if (focusPath) document.querySelector(`[data-path="${focusPath}"]`)?.focus();
    }
    function figures(v) {
        const charge = sum(v.procedures,'price');
        const ordinaryPaid = sum(v.payments,'amount');
        if (v.arrangement !== 'installment') return {charge, agreed:charge, paid:ordinaryPaid, ordinaryPaid, planPaid:0, balance:charge-ordinaryPaid};
        const financedCharge = v.plan.procedure_index !== '' && v.plan.procedure_index != null ? cents(v.procedures[Number(v.plan.procedure_index)]?.price) : 0;
        const ordinaryCharge = charge-financedCharge;
        const planPaid = cents(v.plan.downpayment)+sum(v.plan.payments,'amount');
        const planCost = v.plan.is_unpriced_contract == 1 ? null : cents(v.plan.total_cost);
        const agreed = ordinaryCharge+planCost;
        return {charge, agreed:planCost === null ? null : agreed, paid:ordinaryPaid+planPaid, ordinaryPaid, ordinaryCharge, planPaid, planCost,
            ordinaryBalance:ordinaryCharge-ordinaryPaid, planBalance:planCost === null ? null : planCost-planPaid,
            balance:planCost === null ? null : (ordinaryCharge-ordinaryPaid)+(planCost-planPaid)};
    }
    function monthlyDue(v) {
        const plan = v.plan;
        if (!plan?.first_due_date || cents(plan.open_monthly_payment) <= 0) return null;
        const first = new Date(`${plan.first_due_date}T00:00:00Z`);
        if (Number.isNaN(first.getTime())) return null;
        const cutoff = plan.ended_at || c.today;
        let dueCount = 0;
        for (let month = 0; ; month++) {
            const firstOfMonth = new Date(Date.UTC(first.getUTCFullYear(), first.getUTCMonth() + month, 1));
            const lastDay = new Date(Date.UTC(firstOfMonth.getUTCFullYear(), firstOfMonth.getUTCMonth() + 1, 0)).getUTCDate();
            const due = new Date(Date.UTC(firstOfMonth.getUTCFullYear(), firstOfMonth.getUTCMonth(), Math.min(first.getUTCDate(), lastDay))).toISOString().slice(0, 10);
            if (due > cutoff) break;
            dueCount++;
        }
        const received = sum(plan.payments.filter(payment => payment.payment_date && payment.payment_date <= cutoff), 'amount');
        return Math.max(0, dueCount * cents(plan.open_monthly_payment) - received) / 100;
    }
    function totals() {
        let charge=0, agreed=0, paid=0, planOverpayments=0, unknownPlans=0, knownBalance=0;
        state.payload.visits.forEach((v,i) => {
            const f = figures(v); charge+=f.charge; agreed+=f.agreed; paid+=f.paid;
            const target = document.querySelector(`[data-totals="${i}"]`);
            const hasTotal = v.arrangement === 'installment' && v.plan.total_cost !== '' && v.plan.total_cost !== null && v.plan.total_cost !== undefined;
            const unknownTotal = v.arrangement === 'installment' && v.plan.is_unpriced_contract == 1;
            const fixedTotalMissing = v.arrangement === 'installment' && !unknownTotal && !hasTotal;
            const financed = v.arrangement === 'installment' ? v.procedures[Number(v.plan.procedure_index)] : null;
            const fixedChargeMissing = v.arrangement === 'installment' && !unknownTotal && v.plan.procedure_index !== '' && v.plan.procedure_index != null && financed && (financed.price === '' || financed.price === null);
            const fixedPlanIncomplete = fixedTotalMissing || fixedChargeMissing;
            if (!fixedPlanIncomplete && f.planBalance !== null && f.planBalance < 0) planOverpayments++;
            if (f.balance === null) { unknownPlans++; } else { knownBalance += f.balance; }
            const chargeNotice = document.querySelector(`[data-plan-charge-notice="${i}"]`);
            if (chargeNotice) chargeNotice.hidden = !fixedChargeMissing;
            const planSummary = document.querySelector(`[data-plan-summary="${i}"]`);
            if (planSummary) {
                const unknown = v.plan.is_unpriced_contract == 1;
                const summaryItem = (label, value) => `<div class="re-summary-item"><span>${label}</span><strong>${value}</strong></div>`;
                planSummary.innerHTML = summaryItem('Agreed total', unknown ? 'No final total agreed' : fixedChargeMissing ? 'Review treatment charge' : hasTotal ? money(f.planCost/100) : 'Not entered')
                    + summaryItem('Plan payments received', money(f.planPaid/100))
                    + summaryItem('Plan balance', unknown ? 'Balance not yet determined' : fixedChargeMissing ? 'Review treatment charge' : hasTotal ? money(f.planBalance/100) : 'Enter agreed total');
            }
            if (target) {
                const item = (label, value) => `<div class="re-summary-item"><span>${label}</span><strong>${value}</strong></div>`;
                const due = v.plan?.is_unpriced_contract == 1 ? monthlyDue(v) : null;
                target.innerHTML = item('Total collected', money(f.paid/100))
                    + (v.plan?.is_unpriced_contract == 1 ? item('Monthly dues so far', due === null ? 'Enter due date and monthly amount' : money(due)) : '')
                    + (v.arrangement === 'installment' ? item('Known other balance', money(f.ordinaryBalance/100)) : '')
                    + item(v.plan?.is_unpriced_contract == 1 ? 'Final contract balance' : 'Balance', fixedChargeMissing ? 'Review treatment charge' : fixedTotalMissing ? 'Enter agreed total' : f.balance === null ? 'Not determinable — no total agreed' : money(f.balance/100))
                    + (!fixedPlanIncomplete && f.planBalance !== null && f.planBalance < 0 ? `<div class="re-billing-warning" role="alert">Installment receipts exceed the agreed plan cost by ${money(-f.planBalance/100)}.</div>` : '');
            }
            const suggestion = document.querySelector(`[data-plan-suggestion="${i}"]`);
            if (suggestion) suggestion.textContent = v.plan.is_unpriced_contract == 1 ? 'Monthly dues stop at the treatment end date. Future months are not a final balance.' : fixedChargeMissing ? 'Enter the agreed treatment charge in Procedures before reviewing this fixed-total plan.' : fixedTotalMissing ? 'Enter the agreed total to see a suggested monthly amount.' : v.plan.is_open_contract == 1 ? `Suggested monthly: ${money(v.plan.open_monthly_payment)}. No paid months will be generated.` : `Suggested monthly: ${money((cents(v.plan.total_cost)-cents(v.plan.downpayment))/100/Math.max(1,Number(v.plan.months)))}. No paid months will be generated.`;
        });
        $('re-grand-totals').textContent = `${state.payload.visits.length} ${state.payload.visits.length === 1 ? 'visit' : 'visits'} in this batch${unknownPlans ? ` · ${unknownPlans} open contract${unknownPlans === 1 ? '' : 's'} with no agreed total` : ''}${planOverpayments ? ` · ${planOverpayments} installment overpayment${planOverpayments === 1 ? '' : 's'} to review` : ''}`;
    }
    function closeServicePicker(input, restore = false) {
        const list = document.getElementById(input.getAttribute('aria-controls'));
        if (list) list.hidden = true;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
        input.serviceMatches = [];
        input.serviceActive = -1;
        if (restore) {
            const id = state.payload.visits[Number(input.dataset.servicePicker.split('.')[1])]
                ?.procedures[Number(input.dataset.servicePicker.split('.')[3])]?.service_id;
            input.value = serviceName(id);
        }
    }
    function showServiceOptions(input) {
        const list = document.getElementById(input.getAttribute('aria-controls'));
        if (!list) return;
        const term = input.value.trim().toLocaleLowerCase();
        const matches = c.services.filter(service => service.name.toLocaleLowerCase().includes(term));
        input.serviceMatches = matches.slice(0, 15);
        input.serviceActive = -1;
        const path = `${input.dataset.servicePicker}.service_id`;
        const hidden = document.querySelector(`[data-path="${path}"]`);
        list.innerHTML = input.serviceMatches.length
            ? input.serviceMatches.map((service, index) => `<button type="button" role="option" id="${list.id}-option-${index}" data-service-id="${esc(service.id)}" aria-selected="${String(service.id) === String(hidden?.value)}">${esc(service.name)}</button>`).join('')
                + (matches.length > 15 ? '<div class="re-service-hint">More matches — keep typing</div>' : '')
            : '<div class="re-service-hint">No matching treatments</div>';
        list.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }
    function chooseService(input, id) {
        const service = c.services.find(item => String(item.id) === String(id));
        if (!service) return;
        const path = input.dataset.servicePicker;
        const parts = path.split('.');
        const visit = state.payload.visits[Number(parts[1])];
        const procedure = visit?.procedures[Number(parts[3])];
        if (!procedure) return;
        const previousServiceId = procedure.service_id;
        at(`${path}.service_id`, String(service.id));
        if (String(previousServiceId) !== String(service.id)) procedure.related_context = '';
        if (service.id == c.recementServiceId && String(previousServiceId) !== String(service.id)) {
            procedure.price = service.base_price ?? '500.00';
        } else if ((procedure.price === '' || procedure.price === null)
            && !(visit.plan?.is_unpriced_contract == 1 && Number(visit.plan.procedure_index) === Number(parts[3]))) {
            procedure.price = service.base_price ?? '';
        }
        changed();
        render(path);
    }
    $('re-visits').addEventListener('focusin', event => {
        if (!event.target.matches('[data-service-picker]')) return;
        document.querySelectorAll('[data-service-picker][aria-expanded="true"]').forEach(input => {
            if (input !== event.target) closeServicePicker(input, true);
        });
        showServiceOptions(event.target);
    });
    $('re-visits').addEventListener('keydown', event => {
        const input = event.target;
        if (!input.matches('[data-service-picker]')) return;
        if (event.key === 'Escape') { event.preventDefault(); event.stopPropagation(); closeServicePicker(input, true); return; }
        if (event.key === 'Tab') { closeServicePicker(input, true); return; }
        if (!['ArrowDown', 'ArrowUp', 'Enter'].includes(event.key)) return;
        event.preventDefault();
        event.stopPropagation();
        if (input.getAttribute('aria-expanded') !== 'true') showServiceOptions(input);
        const matches = input.serviceMatches || [];
        if (event.key === 'Enter') {
            const chosen = matches[input.serviceActive >= 0 ? input.serviceActive : (matches.length === 1 ? 0 : -1)];
            if (chosen) chooseService(input, chosen.id);
            return;
        }
        if (!matches.length) return;
        input.serviceActive = input.serviceActive < 0
            ? (event.key === 'ArrowDown' ? 0 : matches.length - 1)
            : Math.max(0, Math.min(matches.length - 1, input.serviceActive + (event.key === 'ArrowDown' ? 1 : -1)));
        const options = document.getElementById(input.getAttribute('aria-controls'))?.querySelectorAll('[role="option"]') || [];
        options.forEach((option, index) => option.classList.toggle('is-active', index === input.serviceActive));
        const active = options[input.serviceActive];
        if (active) { input.setAttribute('aria-activedescendant', active.id); active.scrollIntoView({block:'nearest'}); }
    });
    $('re-visits').addEventListener('click', event => {
        const option = event.target.closest('[data-service-id]');
        if (!option) return;
        const input = option.closest('.re-service-picker')?.querySelector('[data-service-picker]');
        if (input) chooseService(input, option.dataset.serviceId);
    });
    document.addEventListener('pointerdown', event => {
        if (event.target.closest('.re-service-picker')) return;
        document.querySelectorAll('[data-service-picker][aria-expanded="true"]').forEach(input => closeServicePicker(input, true));
    });
    $('re-visits').addEventListener('input', event => {
        const el = event.target;
        if (el.dataset.servicePicker) {
            const path = `${el.dataset.servicePicker}.service_id`;
            const selectedId = state.payload.visits[Number(el.dataset.servicePicker.split('.')[1])]
                ?.procedures[Number(el.dataset.servicePicker.split('.')[3])]?.service_id;
            const selectedName = serviceName(selectedId);
            if (el.value !== selectedName && selectedId !== '' && selectedId != null) {
                at(path, '');
                document.querySelector(`[data-path="${path}"]`).value = '';
                changed();
            }
            el.removeAttribute('aria-invalid');
            showServiceOptions(el);
            return;
        }
        if (!el.dataset.path || el.tagName === 'SELECT') return;
        at(el.dataset.path, el.type === 'checkbox' ? (el.checked ? 1 : 0) : el.value); el.removeAttribute('aria-invalid'); changed();
    });
    $('re-visits').addEventListener('change', event => {
        const el = event.target;
        if (el.dataset.endedToggle !== undefined) {
            const i = Number(el.dataset.endedToggle), plan = state.payload.visits[i]?.plan;
            if (!plan) return;
            if (!el.checked) { plan.ended_at = ''; plan.acknowledge_unpaid = 0; changed(); }
            const group = el.closest('.re-plan-section')?.querySelector('.re-end-fields');
            if (group) group.hidden = !el.checked;
            if (el.checked) group?.querySelector(`[data-path="visits.${i}.plan.ended_at"]`)?.focus();
            return;
        }
        if (el.dataset.link) {
            const [kind,id] = el.value.split(':');
            at(`${el.dataset.link}.visit_id`, kind === 'existing' ? Number(id) : null);
            at(`${el.dataset.link}.visit_index`, kind === 'batch' ? Number(id) : null); changed(); return;
        }
        if (!el.dataset.path || el.tagName !== 'SELECT') return;
        const path = el.dataset.path, i = Number(path.split('.')[1]), v = state.payload.visits[i];
        if (path.endsWith('.arrangement')) {
            const previous = v.arrangement === 'ordinary' ? 'ordinary' : v.plan?.is_unpriced_contract == 1 ? 'open' : 'installment';
            if (el.value === 'ordinary' && v.plan && (cents(v.plan.downpayment)>0 || v.plan.payments.some(p => cents(p.amount)>0))) {
                if (!confirm('Switch to ordinary payments and remove the unsaved installment plan?')) {el.value=previous;return;}
            }
            if (el.value !== 'ordinary' && !v.plan) {
                const procedureIndex = v.procedures.length === 1 ? 0 : '';
                v.plan = {procedure_index:procedureIndex,total_cost:procedureIndex === 0 ? v.procedures[0].price : '', downpayment:'0', start_date:v.visit_date, downpayment_date:v.visit_date, downpayment_method:'Cash', is_open_contract:0, is_unpriced_contract:0, first_due_date:'', ended_at:'', months:'', open_monthly_payment:'', payments:[]};
            }
            if (el.value === 'ordinary') v.plan = null;
            if (el.value === 'open' && previous !== 'open') {
                v.plan.is_unpriced_contract = 1; v.plan.is_open_contract = 1; v.plan.total_cost = null;
                const financed = v.procedures[Number(v.plan.procedure_index)];
                if (financed) financed.price = null;
            }
            if (el.value === 'installment' && previous === 'open') {
                v.plan.is_unpriced_contract = 0; v.plan.is_open_contract = 0;
                v.plan.ended_at = ''; v.plan.acknowledge_unpaid = 0;
            }
            v.arrangement = el.value === 'ordinary' ? 'ordinary' : 'installment';
            changed(); render(); return;
        }
        at(path, el.value);
        if (path.endsWith('.plan.is_unpriced_contract') && el.value === '1') {
            v.plan.is_open_contract = 1; v.plan.total_cost = null;
            const financed = v.procedures[Number(v.plan.procedure_index)];
            if (financed) financed.price = null;
        }
        if (path.endsWith('.plan.procedure_index') && v.plan?.is_unpriced_contract == 1) {
            if (el.value !== '' && v.procedures[Number(el.value)]) v.procedures[Number(el.value)].price = null;
        } else if (path.endsWith('.plan.procedure_index') && el.value !== '' && (v.plan.total_cost === '' || v.plan.total_cost === null)) {
            v.plan.total_cost = v.procedures[Number(el.value)]?.price ?? '';
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
            case 'import-monthly-receipts': {
                const source = document.querySelector(`[data-monthly-import="${i}"]`);
                const lines = (source?.value || '').trim().split(/\r?\n/).filter(Boolean);
                const rows = [];
                const methods = ['Cash', 'GCash', 'Card', 'Bank Transfer'];
                for (const [lineNo, line] of lines.entries()) {
                    const fields = line.split(/[,;\t]/).map(part => part.trim());
                    if (lineNo === 0 && /^date$/i.test(fields[0])) continue;
                    const amount = Number(fields[1]);
                    if (!/^\d{4}-\d{2}-\d{2}$/.test(fields[0]) || !Number.isFinite(amount) || amount <= 0 || !methods.includes(fields[2])) {
                        alert(`Line ${lineNo + 1}: use YYYY-MM-DD, amount, Cash/GCash/Card/Bank Transfer.`);
                        return;
                    }
                    rows.push({payment_date:fields[0], amount:amount.toFixed(2), method:fields[2]});
                }
                if (!rows.length || v.plan.payments.length + rows.length > 200) { alert('Paste 1 to 200 monthly receipts.'); return; }
                let next = Math.max(0, ...v.plan.payments.map(payment => Number(payment.month_number) || 0)) + 1;
                rows.forEach(row => v.plan.payments.push({...receipt(row.payment_date), ...row, month_number:next++, visit_id:null, visit_index:null}));
                source.value = '';
                break;
            }
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
    $('re-save-draft').onclick = () => {dirty=true;saveDraft().catch(error => errors(error, 'draft'));};
    function setLocked(value) {locked=value; ['re-editor','re-footer','re-review','re-drafts'].forEach(id => {$(id).querySelectorAll('input,select,textarea,button').forEach(el => el.disabled=value);});}
    let saving = false;
    function reviewReceipts(title, rows, details) {
        return `<div class="re-review-payment-group"><h5>${title} <span>${rows.length}</span></h5>${rows.length
            ? `<ul class="re-review-receipts">${rows.map((row, index) => `<li><span class="re-review-receipt-number">${index+1}</span><time datetime="${esc(row.payment_date)}">${esc(row.payment_date || 'Date missing')}</time><strong>${money(row.amount)}</strong><span>${esc(row.method || 'Method missing')}</span>${details ? `<small>${esc(details(row))}</small>` : ''}</li>`).join('')}</ul>`
            : '<p class="re-muted">No receipts in this group.</p>'}</div>`;
    }
    function reviewWarning(warning) {
        const activeIds = new Set(c.existingVisits.map(row => String(row.id)));
        const message = esc(warning)
            .replace(/resembles (Visit \d+, procedure \d+)/, (_, match) => {
                const number = match.match(/Visit (\d+)/)?.[1];
                return `<span>resembles <a href="#re-review-visit-${number}">${match}</a></span>`;
            })
            .replace(/#(\d+)/g, (match, id) => activeIds.has(id) && c.visitUrlTemplate
                ? `<a href="${esc(c.visitUrlTemplate.replace('__VISIT_ID__', id))}" target="_blank" rel="noopener" aria-label="Open existing visit ${id} in a new tab">${match}</a>` : match);
        return `<li>${message}</li>`;
    }
    function reviewVisit(v, i, warnings) {
        const f = figures(v), plan = v.arrangement === 'installment' ? v.plan : null;
        const unknown = plan?.is_unpriced_contract == 1;
        const doctor = c.doctors.find(row => String(row.id) === String(v.doctor_id))?.name || 'Dentist unavailable';
        const procedures = v.procedures.map((p, j) => {
            const name = c.services.find(row => String(row.id) === String(p.service_id))?.name || `Treatment #${p.service_id}`;
            const context = (c.recementContexts || []).find(row => row.value === p.related_context)?.label;
            const detail = [p.tooth_number && `Tooth ${p.tooth_number}`, p.surface && `Surface ${p.surface}`, p.shade && `Shade ${p.shade}`, p.notes, context && `Related to ${context}`].filter(Boolean).join(' · ');
            return `<li><span><strong>${esc(name)}</strong>${detail ? `<small>${esc(detail)}</small>` : ''}</span><span>${p.price === null || p.price === '' ? 'Charge not agreed' : money(p.price)}</span></li>`;
        }).join('');
        const arrangement = !plan ? 'Paid normally / unpaid' : unknown ? 'Open contract — monthly fee until treatment ends' : plan.is_open_contract == 1 ? 'Open term, agreed total' : 'Fixed-total installment';
        const financed = plan ? c.services.find(row => String(row.id) === String(v.procedures[Number(plan.procedure_index)]?.service_id))?.name || `Procedure ${Number(plan.procedure_index)+1}` : '';
        const initial = plan ? `<div class="re-review-payment-group"><h5>Initial payment <span>${cents(plan.downpayment)>0 ? 1 : 0}</span></h5>${cents(plan.downpayment)>0
            ? `<div class="re-review-initial"><time datetime="${esc(plan.downpayment_date)}">${esc(plan.downpayment_date || 'Date missing')}</time><strong>${money(plan.downpayment)}</strong><span>${esc(plan.downpayment_method || 'Method missing')}</span></div>`
            : '<p class="re-muted">No initial-payment receipt will be created.</p>'}</div>` : '';
        const ordinary = reviewReceipts('Ordinary receipts', v.payments, row => {
            const procedure = row.procedure_index !== '' && row.procedure_index != null ? v.procedures[Number(row.procedure_index)] : null;
            return procedure ? `For ${c.services.find(service => String(service.id) === String(procedure.service_id))?.name || 'selected treatment'}` : 'For entire visit';
        });
        const monthly = plan ? reviewReceipts('Monthly receipts', plan.payments, row => row.visit_id ? `Linked visit #${row.visit_id}` : row.visit_index != null ? `Batch visit ${Number(row.visit_index)+1}` : 'Collection only — no treatment visit') : '';
        const balance = unknown ? `<div class="re-review-unknown"><strong>No final total agreed</strong><span>Final balance cannot be calculated.</span></div>`
            : `<div class="re-review-figure"><span>${plan ? 'Agreed plan total' : 'Treatment charge'}</span><strong>${money(plan ? plan.total_cost : f.charge/100)}</strong></div><div class="re-review-figure"><span>Balance</span><strong>${money(f.balance/100)}</strong></div>`;
        return `<section class="re-card re-review-visit" id="re-review-visit-${i+1}" aria-labelledby="re-review-visit-title-${i+1}"><div class="re-review-visit-head"><div><span class="re-review-step">Visit ${i+1}</span><h3 id="re-review-visit-title-${i+1}">${esc(v.visit_date)}</h3><p>${esc(doctor)}</p></div>${warnings.length ? '<a class="re-review-duplicate-tag" href="#re-duplicate-warning">Possible duplicate — review below</a>' : ''}</div><div class="re-review-visit-body"><section><h4>Procedures</h4><ul class="re-review-procedures">${procedures}</ul>${v.notes ? `<p class="re-review-note"><strong>Visit notes:</strong> ${esc(v.notes)}</p>` : '<p class="re-muted">No visit notes recorded.</p>'}</section><section><h4>Payment arrangement</h4><p class="re-review-arrangement">${esc(arrangement)}${plan ? ` · ${esc(financed)}` : ''}</p>${plan && !unknown ? `<p class="re-muted">${plan.is_open_contract == 1 ? 'Open term with agreed total' : `${esc(plan.months)} monthly installments`}</p>` : ''}${plan ? initial + monthly : ''}${ordinary}</section></div><div class="re-review-visit-summary"><div class="re-review-figure"><span>Total collected</span><strong>${money(f.paid/100)}</strong></div>${unknown ? `<div class="re-review-figure"><span>Monthly amount</span><strong>${money(plan.open_monthly_payment)}</strong></div>` : ''}${plan ? `<div class="re-review-figure"><span>Known other balance</span><strong>${money(f.ordinaryBalance/100)}</strong></div>` : ''}${balance}</div></section>`;
    }
    function syncReviewSaveState() {
        const button = $('re-save-all');
        if (button) button.disabled = locked || saving || (!!reviewed?.warnings?.length && !$('re-ack')?.checked);
    }
    $('re-review-button').onclick = async () => {
        clearTimeout(timer); $('re-errors').hidden=true;
        $('re-status').textContent = 'Checking records for review…';
        try {
            setLocked(true);
            await enqueue(async () => {
                reviewed = await request('review','POST',body()); state.version=reviewed.version;
                state.payload=reviewed.payload; dirty=false;upsertDraft({...state,updated_at:new Date().toISOString()});localSave();
            });
            $('re-editor').hidden=true; $('re-footer').hidden=true; $('re-review').hidden=false;
            const patient = c.selectedPatient;
            const name = patient ? `${patient.last_name}, ${patient.first_name}` : `Patient #${c.patientId}`;
            const count = state.payload.visits.reduce((total, visit) => total + visit.payments.length
                + (visit.arrangement === 'installment' ? visit.plan.payments.length + (cents(visit.plan.downpayment)>0 ? 1 : 0) : 0), 0);
            const warnings = reviewed.warnings || [];
            const cards = state.payload.visits.map((visit, i) => reviewVisit(visit, i, warnings.filter(warning => warning.startsWith(`Visit ${i+1},`)))).join('');
            const duplicates = warnings.length ? `<section class="re-review-warning" id="re-duplicate-warning" aria-labelledby="re-duplicate-title"><h3 id="re-duplicate-title">Possible duplicate records</h3><p>These entries resemble records already in this batch or the patient’s history. Open each available matching visit and check the paper record. Correct a duplicate using Back to edit. If these are separate visits, confirm below before saving.</p><ul>${warnings.map(reviewWarning).join('')}</ul><label class="re-review-ack"><input type="checkbox" id="re-ack"> I checked the matches and confirm these are separate records.</label></section>`
                : '<p class="re-review-clear">No possible duplicate visits found in this review.</p>';
            const saveLabel = c.mode === 'past' ? 'Save all records' : 'Save visit records';
            $('re-review').innerHTML = `<div class="re-card re-review-header"><span class="re-review-step">Final checkpoint</span><h2 id="re-review-heading" tabindex="-1">Review Records</h2><div class="re-review-identity"><div><span>Patient</span><strong>${esc(name)}</strong></div><div><span>Patient ID</span><strong>P${String(c.patientId).padStart(5,'0')}</strong></div><div><span>Visits</span><strong>${state.payload.visits.length}</strong></div><div><span>Receipts to create</span><strong>${count}</strong></div></div><div class="re-review-unsaved" role="status"><strong>Not saved yet.</strong><span>Check the visits and payments below against the paper records before creating them.</span></div></div>${cards}${duplicates}<div id="re-save-error" class="alert alert-danger" role="alert" tabindex="-1" hidden></div><div class="re-review-actions"><p>Saving will create the ${state.payload.visits.length} clinical visit${state.payload.visits.length===1?'':'s'}, their procedures, ${count} receipt${count===1?'':'s'}${state.payload.visits.some(v=>v.arrangement==='installment')?', and the listed installment plans':''} for this patient.</p><div><button class="btn btn-outline-secondary" type="button" id="re-back">Back to edit</button><button class="btn btn-primary" type="button" id="re-save-all">${saveLabel}</button></div></div>`;
            $('re-back').onclick=()=>{$('re-review').hidden=true;$('re-footer').hidden=false;$('re-editor').hidden=false;render();$('re-editor').focus();};
            $('re-ack')?.addEventListener('change', syncReviewSaveState);
            $('re-save-all').onclick=saveAll;
            $('re-status').textContent='Reviewed — no clinical records saved yet';
            $('re-review-heading').focus();
        } catch(error) {reviewed = null; errors(error, 'review');} finally {setLocked(false);syncReviewSaveState();}
    };
    async function saveAll() {
        if (!reviewed || saving || (reviewed.warnings?.length && !$('re-ack')?.checked)) return;
        saving = true;
        const button = $('re-save-all'), failure = $('re-save-error');
        failure.hidden = true;
        const restoreButton = window.KTLoading?.button(button, 'Saving records…');
        $('re-review').setAttribute('aria-busy', 'true');
        $('re-status').textContent = 'Saving records…';
        try {
            setLocked(true);
            const result = await request('save','POST',{review_hash:reviewed.review_hash,acknowledge_duplicates:!!$('re-ack')?.checked});
            dirty=false;clearLocal();location.href=result.redirect;
        } catch(error) {
            if (Object.keys(error.errors || {}).length) errors(error, 'save');
            failure.textContent = errorSummary(error);
            failure.hidden = false;
            failure.focus();
            $('re-review').querySelector('.re-review-unsaved strong').textContent = 'Save not confirmed.';
            $('re-review').querySelector('.re-review-unsaved span').textContent = 'The result could not be confirmed. Check the patient record or retry this batch.';
            $('re-status').textContent = 'Save not confirmed — check the patient record or retry';
        } finally {
            saving = false;
            $('re-review').removeAttribute('aria-busy');
            restoreButton?.();
            button.textContent = c.mode === 'past' ? 'Save all records' : 'Save visit records';
            setLocked(false);syncReviewSaveState();
        }
    }
    $('re-discard').onclick=async()=>{
        if(!confirm('Discard this unfinished draft? Saved patient records will remain unchanged.'))return;
        clearTimeout(timer);
        try{setLocked(true);await enqueue(async()=>{if(state.version>0)await request('draft','DELETE',{});});clearLocal();dirty=false;location.href=`${c.baseUrl}?patient_id=${c.patientId}&mode=${c.mode}`;}catch(error){errors(error, 'discard');setLocked(false);}
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

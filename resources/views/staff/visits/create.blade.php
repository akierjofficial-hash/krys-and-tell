@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        --card-border: 1px solid rgba(15, 23, 42, .10);
        --soft: rgba(15, 23, 42, .06);
        --text: #0f172a;
        --muted: rgba(15, 23, 42, .62);
        --muted2: rgba(15, 23, 42, .45);
        --brand1: #0d6efd;
        --brand2: #1e90ff;
        --danger: #ef4444;
        --ok: #16a34a;
        --radius: 16px;
    }

    /* Layout */
    .form-max{ max-width: 1100px; }
    .page-wrap{ padding-bottom: 88px; } /* space for sticky bar */

    /* Header */
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin: 8px 0 16px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 28px;
        font-weight: 900;
        letter-spacing: -.4px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    /* Buttons */
    .btn-ghostx, .btn-primaryx, .btn-dangerx{
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 14px;
        text-decoration: none;
        transition: .15s ease;
        white-space: nowrap;
        user-select: none;
    }
    .btn-ghostx{
        border: 1px solid rgba(15, 23, 42, .14);
        color: rgba(15, 23, 42, .78);
        background: rgba(255,255,255,.86);
    }
    .btn-ghostx:hover{ transform: translateY(-1px); background: #fff; }
    .btn-primaryx{
        border: none;
        color: #fff;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        box-shadow: 0 12px 18px rgba(13, 110, 253, .18);
    }
    .btn-primaryx:hover{ transform: translateY(-1px); filter: brightness(1.02); }
    .btn-dangerx{
        border: 1px solid rgba(239, 68, 68, .22);
        color: #b91c1c;
        background: rgba(239, 68, 68, .10);
        padding: 8px 10px;
        border-radius: 12px;
        font-weight: 900;
    }
    .btn-dangerx:hover{ transform: translateY(-1px); }

    /* Card */
    .card-shell{
        background: rgba(255,255,255,.94);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
        backdrop-filter: blur(8px);
    }
    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid var(--soft);
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap: wrap;
        gap: 10px;
    }
    .hint{
        font-size: 13px;
        color: var(--muted);
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .pill{
        font-size: 12px;
        font-weight: 900;
        color: rgba(15, 23, 42, .70);
        background: rgba(15, 23, 42, .06);
        border: 1px solid rgba(15, 23, 42, .08);
        padding: 6px 10px;
        border-radius: 999px;
    }

    .card-bodyx{ padding: 18px; }

    /* Inputs */
    .form-labelx{
        font-weight: 900;
        font-size: 13px;
        color: rgba(15, 23, 42, .78);
        margin-bottom: 6px;
    }
    .helptext{
        font-size: 12px;
        color: var(--muted2);
        margin-top: 6px;
    }

    .inputx, .selectx, .textareax{
        width: 100%;
        border: 1px solid rgba(15, 23, 42, .12);
        padding: 11px 12px;
        border-radius: 12px;
        font-size: 14px;
        color: var(--text);
        background: rgba(255,255,255,.96);
        outline: none;
        transition: box-shadow .15s ease, border-color .15s ease;
    }
    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: rgba(13,110,253,.40);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
    }
    .invalidx{
        border-color: rgba(239, 68, 68, .55) !important;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, .12) !important;
    }

    /* Error box */
    .error-box{
        background: rgba(239, 68, 68, .10);
        border: 1px solid rgba(239, 68, 68, .22);
        color: #b91c1c;
        border-radius: 14px;
        padding: 14px 16px;
        margin: 0 0 14px;
    }

    /* Section header */
    .section-title{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        margin: 0 0 12px;
    }
    .section-title h5{
        margin: 0;
        font-weight: 900;
        letter-spacing: -.2px;
    }
    .divider{
        height: 1px;
        background: var(--soft);
        margin: 14px 0;
    }

    /* Procedures box */
    .proc-shell{
        border: 1px solid rgba(15, 23, 42, .10);
        background: rgba(15, 23, 42, .03);
        border-radius: 16px;
        padding: 14px;
    }
    .proc-grid{
        display:grid;
        grid-template-columns: 1.2fr .6fr .7fr .6fr 1fr auto;
        gap: 10px;
        align-items:end;
    }
    @media (max-width: 992px){
        .proc-grid{ grid-template-columns: 1fr 1fr; }
    }

    /* Table */
    .table-wrap{
        border: 1px solid rgba(15, 23, 42, .10);
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        margin-top: 12px;
    }
    table.proc-table{
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .proc-table thead th{
        background: rgba(15, 23, 42, .04);
        color: rgba(15, 23, 42, .70);
        font-size: 12px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 12px 12px;
        border-bottom: 1px solid var(--soft);
    }
    .proc-table tbody td{
        padding: 12px 12px;
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
        color: rgba(15, 23, 42, .86);
        font-size: 14px;
    }
    .proc-table tbody tr:hover td{
        background: rgba(13, 110, 253, .03);
    }
    .cell-chip{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(15, 23, 42, .10);
        background: rgba(15, 23, 42, .04);
        font-weight: 800;
        font-size: 13px;
        color: rgba(15, 23, 42, .80);
    }
    .muted-dash{ color: rgba(15, 23, 42, .40); font-weight: 800; }

    /* Sticky action bar */
    .sticky-actions{
        position: sticky;
        bottom: 0;
        z-index: 20;
        margin-top: 14px;
        padding: 12px 0 0;
    }
    .sticky-inner{
        border: 1px solid rgba(15, 23, 42, .10);
        background: rgba(255,255,255,.92);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        box-shadow: 0 16px 35px rgba(15, 23, 42, .10);
        padding: 12px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .mini-note{
        font-size: 13px;
        color: var(--muted);
        display:flex;
        align-items:center;
        gap: 8px;
    }
    .req-dot{
        width: 8px; height: 8px; border-radius: 999px;
        background: rgba(239,68,68,.85);
        box-shadow: 0 0 0 4px rgba(239,68,68,.12);
    }

    /* =========================
       ODONTOGRAM (IMAGE + MULTI SELECT)
       ========================= */
    .odonto-card{
        background: rgba(255,255,255,.92);
        border: 1px solid rgba(15, 23, 42, .10);
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(15,23,42,.06);
        overflow: hidden;
    }
    .odonto-top{
        padding: 14px 16px;
        border-bottom: 1px solid rgba(15,23,42,.06);
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: linear-gradient(180deg, rgba(248,250,252,.9), rgba(255,255,255,.9));
    }
    .odonto-title{
        display:flex;
        gap: 12px;
        align-items:flex-start;
    }
    .tooth-ic{
        width: 38px;
        height: 38px;
        display:grid;
        place-items:center;
        border-radius: 12px;
        background: rgba(13,110,253,.10);
        box-shadow: inset 0 0 0 1px rgba(13,110,253,.16);
    }
    .odonto-title .ttl{
        font-weight: 900;
        color:#0f172a;
        font-size: 14px;
    }
    .mutedx{ color: rgba(15,23,42,.55); font-weight: 800; }
    .odonto-title .sub{
        margin-top: 2px;
        font-size: 12px;
        color: rgba(15,23,42,.55);
    }
    .odonto-legend{
        display:flex;
        align-items:center;
        gap: 8px;
        flex-wrap: wrap;
        justify-content:flex-end;
    }
    .lg{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid rgba(15,23,42,.10);
        background: rgba(255,255,255,.85);
        font-size: 12px;
        font-weight: 900;
        color: rgba(15,23,42,.72);
    }
    .dot{ width: 8px; height: 8px; border-radius: 999px; background: currentColor; }
    .lg-has{ color:#0d6efd; }
    .lg-sel{ color:#7c3aed; }

    .btn-mini{
        padding: 9px 12px;
        border-radius: 12px;
        border: 1px solid rgba(15,23,42,.12);
        background: rgba(255,255,255,.9);
        font-weight: 900;
        font-size: 13px;
        color: rgba(15,23,42,.75);
        cursor:pointer;
        transition: .15s ease;
        display:inline-flex;
        align-items:center;
        gap: 8px;
    }
    .btn-mini:hover{
        background:#fff;
        border-color: rgba(13,110,253,.35);
        box-shadow: 0 10px 18px rgba(15,23,42,.06);
        transform: translateY(-1px);
    }

    .odonto-body{ padding: 14px 16px 16px 16px; }
    .odonto-bottom{
        padding: 10px 16px;
        border-top: 1px solid rgba(15,23,42,.06);
        background: rgba(248,250,252,.7);
    }
    .mini-hint{ font-size: 12px; color: rgba(15,23,42,.55); }


    /* Teeth grid */
    .teeth-grid{
        display:grid;
        grid-template-columns: repeat(16, minmax(0, 1fr));
        gap: 10px;
        align-items:end;
    }
    @media (max-width: 768px){
        .teeth-grid{ grid-template-columns: repeat(8, minmax(0, 1fr)); }
    }

    .tooth-btn{
        border: 0;
        padding: 0;
        background: transparent;
        cursor: pointer;
        width: 100%;
        display:flex;
        flex-direction:column;
        align-items:center;
        gap: 6px;
        user-select:none;
    }
    .tooth-icon{
        position: relative;
        width: 46px;
        height: 56px;
        border-radius: 14px;
        display:grid;
        place-items:center;
        transition: .15s ease;
    }
    .tooth-btn:hover .tooth-icon{
        transform: translateY(-1px);
        filter: drop-shadow(0 10px 18px rgba(15,23,42,.10));
    }
    .tooth-num{
        font-size: 13px;
        font-weight: 900;
        color: rgba(15,23,42,.78);
    }

    .tooth-svg{ width: 44px; height: 54px; }
    .tooth-fill{
        fill: #ffffff;
        stroke: rgba(15,23,42,.18);
        stroke-width: 1.2;
    }
    .tooth-shine{ fill: rgba(13,110,253,.08); }

    .tooth-btn.has-proc .tooth-fill{
        fill: rgba(13,110,253,.10);
        stroke: rgba(13,110,253,.45);
    }
    .tooth-btn.has-proc .tooth-num{ color:#0d6efd; }

    .tooth-btn.selected .tooth-fill{
        fill: rgba(124,58,237,.12);
        stroke: rgba(124,58,237,.55);
    }
    .tooth-btn.selected .tooth-num{ color:#5b21b6; }

    .badge-count{
        position:absolute;
        top: -6px;
        right: -6px;
        min-width: 20px;
        height: 20px;
        border-radius: 999px;
        background: #0d6efd;
        color:#fff;
        font-size: 11px;
        font-weight: 900;
        display:flex;
        align-items:center;
        justify-content:center;
        padding: 0 6px;
        border: 2px solid #fff;
        box-shadow: 0 10px 18px rgba(13,110,253,.25);
    }

    .tooth-btn.midgap{ margin-left: 10px; }
</style>

<div class="page-wrap">
    <div class="page-head form-max">
        <div>
            <h2 class="page-title">Add Visit</h2>
            <p class="subtitle">Record a new visit and add treatments per tooth.</p>
        </div>

        <a href="{{ route('staff.visits.index') }}" class="btn-ghostx">
            <i class="fa fa-arrow-left"></i> Back
        </a>
    </div>

    @if ($errors->any())
        <div class="error-box form-max">
            <strong>Please fix the following:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card-shell form-max">
        <div class="card-head">
            <div class="hint">
                <span class="pill">Step 1</span>
                Fill visit info, then add treatments below.
            </div>
            <div class="hint">
                <span class="pill">Tip</span>
                Select multiple teeth then press <strong>Add</strong>.
            </div>
        </div>

        <div class="card-bodyx">
            <form action="{{ route('staff.visits.store') }}" method="POST" id="visitForm">
                @csrf

                <div class="row g-3">
                    {{-- Patient --}}
                    <div class="col-md-6">
                        <label class="form-labelx">Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" class="selectx" required>
                            <option value="">-- Select Patient --</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}">
                                    {{ $patient->first_name }} {{ $patient->last_name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="helptext">Select the patient for this visit.</div>
                    </div>

                {{-- Assigned Dentist --}}
<div class="col-md-6">
    <label class="form-labelx">Assigned Dentist <span class="text-danger">*</span></label>
    <select name="doctor_id" class="selectx" required>
        <option value="">-- Choose Dentist --</option>

        @forelse($doctors as $doc)
            <option value="{{ $doc->id }}" @selected(old('doctor_id') == $doc->id)>
                {{ $doc->name }}{{ $doc->specialty ? ' — '.$doc->specialty : '' }}
            </option>
        @empty
            <option value="" disabled>No active doctors yet (add from Admin → Doctors)</option>
        @endforelse
    </select>

    <div class="helptext">
        This list is managed from Admin → Doctors. Only Active doctors appear here.
    </div>
</div>


                    {{-- Visit Date --}}
                    <div class="col-md-6">
                        <label class="form-labelx">Visit Date <span class="text-danger">*</span></label>
                        <input type="date" name="visit_date" class="inputx" required>
                        <div class="helptext">When the visit happened.</div>
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label class="form-labelx">Notes</label>
                        <textarea name="notes" rows="3" class="textareax" placeholder="Optional notes about the visit..."></textarea>
                    </div>

                    {{-- ODONTOGRAM --}}
                    <div class="col-12">
                        <div class="odonto-card">
                            <div class="odonto-top">
                                <div class="odonto-title">
                                    <span class="tooth-ic">🦷</span>
                                    <div>
                                        <div class="ttl">Tooth Chart <span class="mutedx">(Odontogram)</span></div>
                                        <div class="sub">Click multiple teeth to select. Click again to unselect.</div>
                                    </div>
                                </div>

                                <div class="odonto-legend">
                                    <span class="lg lg-has"><span class="dot"></span> Has procedure</span>
                                    <span class="lg lg-sel"><span class="dot"></span> Selected</span>

                                    <button type="button" class="btn-mini" id="clearToothBtn">
                                        <i class="fa fa-eraser"></i> Clear selection
                                    </button>
                                </div>
                            </div>

                            <div class="odonto-body">
                                <div class="teeth-grid" id="toothChart"></div>
                            </div>


                            <div class="odonto-bottom">
                                <span class="mini-hint">
                                    Selected tooth/teeth: <strong id="selectedToothText">None</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="divider"></div>

                        <div class="section-title">
                            <h5>Treatments / Procedures</h5>
                            <div class="hint">
                                <span class="pill">Step 2</span>
                                Add at least one procedure
                            </div>
                        </div>

                        <div class="proc-shell">
                            <div class="proc-grid">
                                <div>
                                    <label class="form-labelx">Service <span class="text-danger">*</span></label>
                                    <select id="serviceSelect" class="selectx">
                                        <option value="">-- Select Service --</option>
                                        @foreach($services as $service)
                                            <option value="{{ $service->id }}">{{ $service->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="form-labelx">Tooth #</label>
                                    <input type="text" id="toothInput" class="inputx" placeholder="11 (or select above)" autocomplete="off">
                                </div>

                                <div>
                                    <label class="form-labelx">Surface</label>
                                    <input type="text" id="surfaceInput" class="inputx" placeholder="O, OB">
                                </div>

                                <div>
                                    <label class="form-labelx">Shade</label>
                                    <input type="text" id="shadeInput" class="inputx" placeholder="A1">
                                </div>

                                <div>
                                    <label class="form-labelx">Notes</label>
                                    <input type="text" id="noteInput" class="inputx" placeholder="Optional">
                                </div>

                                <div class="d-grid">
                                    <button type="button" id="addProcedureBtn" class="btn-primaryx">
                                        <i class="fa fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>

                            <div class="helptext mt-2">
                                <span class="pill">Tip</span>
                                If you selected multiple teeth, clicking <strong>Add</strong> will create one row per tooth.
                            </div>
                        </div>

                        <div class="table-wrap">
                            <table class="proc-table">
                                <thead>
                                    <tr>
                                        <th style="width: 28%;">Service</th>
                                        <th style="width: 12%;">Tooth</th>
                                        <th style="width: 12%;">Surface</th>
                                        <th style="width: 12%;">Shade</th>
                                        <th>Notes</th>
                                        <th style="width: 70px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="proceduresTable">
                                    <tr id="emptyRow">
                                        <td colspan="6" style="padding: 18px; text-align:center; color: rgba(15,23,42,.55); font-weight:800;">
                                            No procedures yet. Add your first one above.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="helptext mt-2" id="procCountText">0 procedures added</div>
                    </div>

                    {{-- Sticky Actions --}}
                    <div class="col-12 sticky-actions">
                        <div class="sticky-inner">
                            <div class="mini-note">
                                <span class="req-dot"></span>
                                Required: Patient, Visit Date, and at least 1 Procedure
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn-primaryx" id="saveBtn">
                                    <i class="fa fa-check"></i> Save Visit
                                </button>
                                <a href="{{ route('staff.visits.index') }}" class="btn-ghostx">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                </div>{{-- row --}}
            </form>
        </div>
    </div>
</div>

<script>
(() => {
    let procedures = [];
    const selectedTeeth = new Set();

    const serviceSelect = document.getElementById('serviceSelect');
    const toothInput    = document.getElementById('toothInput');
    const surfaceInput  = document.getElementById('surfaceInput');
    const shadeInput    = document.getElementById('shadeInput');
    const noteInput     = document.getElementById('noteInput');

    const addBtn     = document.getElementById('addProcedureBtn');
    const tableBody  = document.getElementById('proceduresTable');
    const emptyRow   = document.getElementById('emptyRow');
    const countText  = document.getElementById('procCountText');
    const visitForm  = document.getElementById('visitForm');

    const selectedText = document.getElementById('selectedToothText');
    const clearToothBtn = document.getElementById('clearToothBtn');

    function setCount() {
        countText.textContent = `${procedures.length} procedure${procedures.length === 1 ? '' : 's'} added`;
    }

    function clearInvalid() {
        serviceSelect.classList.remove('invalidx');
    }

    function flashInvalid(el){
        el.classList.add('invalidx');
        setTimeout(() => el.classList.remove('invalidx'), 900);
    }

    function normalizeToothList(str){
        return String(str || '')
            .split(',')
            .map(s => s.trim())
            .filter(Boolean);
    }

    function renderProcedures() {
        tableBody.innerHTML = '';

        if (procedures.length === 0) {
            tableBody.appendChild(emptyRow);
            setCount();
            paintTeethFromProcedures();
            return;
        }

        procedures.forEach((p, i) => {
            const tr = document.createElement('tr');

            tr.innerHTML = `
                <td><span class="cell-chip"><i class="fa fa-stethoscope"></i> ${escapeHtml(p.service_name)}</span></td>
                <td>${p.tooth_number ? `<span class="cell-chip">${escapeHtml(p.tooth_number)}</span>` : `<span class="muted-dash">—</span>`}</td>
                <td>${p.surface ? `<span class="cell-chip">${escapeHtml(p.surface)}</span>` : `<span class="muted-dash">—</span>`}</td>
                <td>${p.shade ? `<span class="cell-chip">${escapeHtml(p.shade)}</span>` : `<span class="muted-dash">—</span>`}</td>
                <td>${p.notes ? escapeHtml(p.notes) : `<span class="muted-dash">—</span>`}</td>
                <td class="text-end">
                    <button type="button" class="btn-dangerx" data-remove="${i}" title="Remove">✕</button>
                </td>

                <input type="hidden" name="procedures[${i}][service_id]" value="${escapeAttr(p.service_id)}">
                <input type="hidden" name="procedures[${i}][tooth_number]" value="${escapeAttr(p.tooth_number)}">
                <input type="hidden" name="procedures[${i}][surface]" value="${escapeAttr(p.surface)}">
                <input type="hidden" name="procedures[${i}][shade]" value="${escapeAttr(p.shade)}">
                <input type="hidden" name="procedures[${i}][notes]" value="${escapeAttr(p.notes)}">
            `;

            tableBody.appendChild(tr);
        });

        setCount();
        paintTeethFromProcedures();
    }

    function addProcedure() {
        clearInvalid();

        const serviceId = serviceSelect.value;
        const serviceName = serviceSelect.options[serviceSelect.selectedIndex]?.text?.trim();

        if (!serviceId) {
            flashInvalid(serviceSelect);
            alert('Please select a service.');
            return;
        }

        // If user selected teeth, use those; otherwise parse the input
        const chosen = Array.from(selectedTeeth);
        const list = chosen.length ? chosen : normalizeToothList(toothInput.value);

        // If no tooth selected/typed, still allow a “general” procedure row (tooth empty)
        if (list.length === 0) list.push('');

        // One row per tooth
        list.forEach(tn => {
            procedures.push({
                service_id: serviceId,
                service_name: serviceName || 'Service',
                tooth_number: tn,
                surface: surfaceInput.value.trim(),
                shade: shadeInput.value.trim(),
                notes: noteInput.value.trim(),
            });
        });

        renderProcedures();

        // clear inputs + selection
        selectedTeeth.clear();
        syncSelectedUI();

        toothInput.value = '';
        surfaceInput.value = '';
        shadeInput.value = '';
        noteInput.value = '';
        toothInput.focus();
    }

    // Add via button
    addBtn.addEventListener('click', addProcedure);

    // Add via Enter key
    [serviceSelect, toothInput, surfaceInput, shadeInput, noteInput].forEach(el => {
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                addProcedure();
            }
        });
    });

    // Remove using event delegation
    tableBody.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-remove]');
        if (!btn) return;

        const index = Number(btn.getAttribute('data-remove'));
        procedures.splice(index, 1);
        renderProcedures();
    });

    // Enforce at least 1 procedure on submit
    visitForm.addEventListener('submit', (e) => {
        if (procedures.length === 0) {
            e.preventDefault();
            alert('Please add at least one procedure before saving.');
        }
    });

    /* =========================
       ODONTOGRAM (MULTI SELECT)
       ========================= */
    function toothSvg(){
        return `
            <svg class="tooth-svg" viewBox="0 0 64 80" aria-hidden="true">
                <path class="tooth-fill" d="M32 6c-13 0-22 7-22 20 0 10 6 14 6 24 0 8 2 24 10 24 4 0 5-7 6-12 1-6 2-10 6-10s5 4 6 10c1 5 2 12 6 12 8 0 10-16 10-24 0-10 6-14 6-24C54 13 45 6 32 6z"/>
                <path class="tooth-shine" d="M24 18c2-3 6-5 10-5 2 0 3 0 5 1-5 1-9 3-12 7-2 3-3 7-3 12-1-6-1-11 0-15z"/>
            </svg>
        `;
    }

    function buildToothChartIconsMulti(){
        const chart = document.getElementById('toothChart');
        if(!chart) return;
        chart.innerHTML = '';

        const upper = [18,17,16,15,14,13,12,11, 21,22,23,24,25,26,27,28];
        const lower = [48,47,46,45,44,43,42,41, 31,32,33,34,35,36,37,38];
        const all = [...upper, ...lower];

        all.forEach((num, idx) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'tooth-btn';
            btn.dataset.tooth = String(num);

            if (idx === 8 || idx === 24) btn.classList.add('midgap');

            btn.innerHTML = `
                <div class="tooth-icon">${toothSvg()}</div>
                <div class="tooth-num">${num}</div>
            `;

            btn.addEventListener('click', () => {
                const t = String(num);
                if (selectedTeeth.has(t)) selectedTeeth.delete(t);
                else selectedTeeth.add(t);
                syncSelectedUI();
            });

            chart.appendChild(btn);
        });

        clearToothBtn?.addEventListener('click', () => {
            selectedTeeth.clear();
            syncSelectedUI();
        });

        syncSelectedUI();
        paintTeethFromProcedures();
    }

    function syncSelectedUI(){
        const arr = Array.from(selectedTeeth);

        if (selectedText) selectedText.textContent = arr.length ? arr.join(', ') : 'None';
        if (toothInput) toothInput.value = arr.join(', ');

        document.querySelectorAll('.tooth-btn').forEach(btn => {
            btn.classList.toggle('selected', selectedTeeth.has(btn.dataset.tooth));
        });
    }

    function paintTeethFromProcedures(){
        const counts = {};
        procedures.forEach(p => {
            const tn = (p.tooth_number || '').toString().trim();
            if(!tn) return;
            counts[tn] = (counts[tn] || 0) + 1;
        });

        document.querySelectorAll('.tooth-btn').forEach(btn => {
            const tn = btn.dataset.tooth;
            const has = !!counts[tn];

            btn.classList.toggle('has-proc', has);

            const icon = btn.querySelector('.tooth-icon');
            if(!icon) return;

            const existing = icon.querySelector('.badge-count');
            if(existing) existing.remove();

            if(has){
                const b = document.createElement('div');
                b.className = 'badge-count';
                b.textContent = counts[tn];
                icon.appendChild(b);
            }
        });
    }

    // Helpers: basic escaping
    function escapeHtml(str) {
        return String(str ?? '').replace(/[&<>"']/g, s => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[s]));
    }
    function escapeAttr(str) {
        return escapeHtml(str).replace(/`/g, '&#96;');
    }

    // Init
    renderProcedures();
    buildToothChartIconsMulti();
})();
</script>

@endsection

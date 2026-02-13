@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Visits Edit (Dark mode compatible)
       - Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                           --kt-border, --kt-input-bg, --kt-input-border, --kt-shadow
       ========================================================== */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);
        --soft: rgba(148,163,184,.14);

        --text: var(--kt-text);
        --muted: var(--kt-muted);
        --muted2: rgba(148,163,184,.80);

        --brand1: #0d6efd;
        --brand2: #1e90ff;

        --danger: #f87171;
        --dangerBg: rgba(248,113,113,.16);
        --dangerBd: rgba(248,113,113,.25);

        --radius: 16px;

        --focus: rgba(96,165,250,.55);
        --focusRing: rgba(96,165,250,.18);
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
        --muted2: rgba(148,163,184,.72);
    }

    .form-max{ max-width: 1100px; }
    .page-wrap{ padding-bottom: 40px; }

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
        font-weight: 950;
        letter-spacing: -.4px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

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
        border: 1px solid var(--kt-border);
        color: var(--text);
        background: var(--kt-surface-2);
    }
    .btn-ghostx:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .btn-ghostx:hover{
        background: rgba(17,24,39,.75);
    }

    .btn-primaryx{
        border: none;
        color: #fff;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        box-shadow: 0 12px 18px rgba(13, 110, 253, .18);
    }
    .btn-primaryx:hover{ transform: translateY(-1px); filter: brightness(1.02); }

    .btn-dangerx{
        border: 1px solid var(--dangerBd);
        color: var(--danger);
        background: var(--dangerBg);
        padding: 8px 10px;
        border-radius: 12px;
        font-weight: 950;
    }
    .btn-dangerx:hover{ transform: translateY(-1px); }

    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
        backdrop-filter: blur(8px);
        color: var(--text);
    }
    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid var(--soft);
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap: wrap;
        gap: 10px;
        background: linear-gradient(180deg, rgba(148,163,184,.08), transparent);
    }
    html[data-theme="dark"] .card-head{
        background: linear-gradient(180deg, rgba(2,6,23,.45), rgba(17,24,39,0));
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
        font-weight: 950;
        color: var(--text);
        background: rgba(148,163,184,.12);
        border: 1px solid rgba(148,163,184,.18);
        padding: 6px 10px;
        border-radius: 999px;
    }
    html[data-theme="dark"] .pill{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.20);
    }

    .card-bodyx{ padding: 18px; }

    .divider{
        height: 1px;
        background: var(--soft);
        margin: 14px 0;
    }

    .form-labelx{
        font-weight: 950;
        font-size: 13px;
        color: var(--text);
        opacity: .9;
        margin-bottom: 6px;
    }
    .helptext{
        font-size: 12px;
        color: var(--muted2);
        margin-top: 6px;
    }

    .inputx, .selectx, .textareax{
        width: 100%;
        border: 1px solid var(--kt-input-border);
        padding: 11px 12px;
        border-radius: 12px;
        font-size: 14px;
        color: var(--text);
        background: var(--kt-input-bg);
        outline: none;
        transition: box-shadow .15s ease, border-color .15s ease, background .15s ease;
    }
    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: var(--focus);
        box-shadow: 0 0 0 4px var(--focusRing);
        background: var(--kt-surface);
    }
    .invalidx{
        border-color: rgba(248,113,113,.55) !important;
        box-shadow: 0 0 0 4px rgba(248,113,113,.16) !important;
    }

    .error-box{
        background: rgba(248,113,113,.14);
        border: 1px solid rgba(248,113,113,.25);
        color: #fecaca;
        border-radius: 14px;
        padding: 14px 16px;
        margin: 0 0 14px;
    }
    html[data-theme="light"] .error-box{
        color: #b91c1c;
        background: rgba(239,68,68,.10);
        border-color: rgba(239,68,68,.22);
    }

    /* Procedures box */
    .proc-shell{
        border: 1px solid var(--kt-border);
        background: rgba(148,163,184,.08);
        border-radius: 16px;
        padding: 14px;
    }
    html[data-theme="dark"] .proc-shell{
        background: rgba(2,6,23,.28);
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
        border: 1px solid var(--kt-border);
        border-radius: 14px;
        overflow: hidden;
        background: var(--kt-surface);
        margin-top: 12px;
    }
    table.proc-table{
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }
    .proc-table thead th{
        background: rgba(148,163,184,.12);
        color: var(--muted);
        font-size: 12px;
        font-weight: 950;
        text-transform: uppercase;
        letter-spacing: .06em;
        padding: 12px 12px;
        border-bottom: 1px solid var(--soft);
        white-space: nowrap;
    }
    html[data-theme="dark"] .proc-table thead th{
        background: rgba(2,6,23,.35);
    }

    .proc-table tbody td{
        padding: 12px 12px;
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
        color: var(--text);
        font-size: 14px;
    }
    .proc-table tbody tr:hover td{
        background: rgba(96,165,250,.08);
    }

    .cell-chip{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.10);
        font-weight: 900;
        font-size: 13px;
        color: var(--text);
    }
    html[data-theme="dark"] .cell-chip{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.20);
    }

    .muted-dash{ color: rgba(148,163,184,.65); font-weight: 900; }

    /* ========= ODONTOGRAM (image + multi select) ========= */
    .odonto-card{
        background: var(--kt-surface);
        border: 1px solid var(--kt-border);
        border-radius: 16px;
        box-shadow: var(--kt-shadow);
        overflow: hidden;
    }
    .odonto-top{
        padding: 14px 16px;
        border-bottom: 1px solid var(--soft);
        display:flex;
        align-items:flex-start;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        background: linear-gradient(180deg, rgba(148,163,184,.10), transparent);
    }
    html[data-theme="dark"] .odonto-top{
        background: linear-gradient(180deg, rgba(2,6,23,.45), rgba(17,24,39,0));
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
        background: rgba(96,165,250,.14);
        box-shadow: inset 0 0 0 1px rgba(96,165,250,.20);
    }
    .odonto-title .ttl{
        font-weight: 950;
        color: var(--text);
        font-size: 14px;
    }
    .mutedx{ color: var(--muted); font-weight: 900; }
    .odonto-title .sub{
        margin-top: 2px;
        font-size: 12px;
        color: var(--muted);
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
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.10);
        font-size: 12px;
        font-weight: 950;
        color: var(--text);
        white-space: nowrap;
    }
    html[data-theme="dark"] .lg{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.20);
    }
    .dot{ width: 8px; height: 8px; border-radius: 999px; background: currentColor; }
    .lg-has{ color:#60a5fa; }
    .lg-sel{ color:#a78bfa; }

    .btn-mini{
        padding: 9px 12px;
        border-radius: 12px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2);
        font-weight: 950;
        font-size: 13px;
        color: var(--text);
        cursor:pointer;
        transition: .15s ease;
        display:inline-flex;
        align-items:center;
        gap: 8px;
        white-space: nowrap;
    }
    .btn-mini:hover{
        background: rgba(148,163,184,.14);
        border-color: rgba(96,165,250,.35);
        box-shadow: 0 10px 18px rgba(15,23,42,.06);
        transform: translateY(-1px);
    }
    html[data-theme="dark"] .btn-mini:hover{
        background: rgba(17,24,39,.75);
        box-shadow: 0 10px 18px rgba(0,0,0,.25);
    }

    .odonto-body{ padding: 14px 16px 16px 16px; }
    .odonto-bottom{
        padding: 10px 16px;
        border-top: 1px solid var(--soft);
        background: rgba(148,163,184,.06);
        display:flex;
        justify-content:space-between;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
    }
    html[data-theme="dark"] .odonto-bottom{
        background: rgba(2,6,23,.22);
    }

    .mini-hint{ font-size: 12px; color: var(--muted); }

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
    html[data-theme="dark"] .tooth-btn:hover .tooth-icon{
        filter: drop-shadow(0 10px 18px rgba(0,0,0,.35));
    }

    .tooth-num{
        font-size: 13px;
        font-weight: 950;
        color: var(--muted);
    }

    .tooth-svg{ width: 44px; height: 54px; }
    .tooth-fill{
        fill: rgba(255,255,255,.92);
        stroke: rgba(148,163,184,.40);
        stroke-width: 1.2;
    }
    html[data-theme="dark"] .tooth-fill{
        fill: rgba(17,24,39,.85);
        stroke: rgba(148,163,184,.34);
    }
    .tooth-shine{ fill: rgba(96,165,250,.10); }

    .tooth-btn.has-proc .tooth-fill{
        fill: rgba(96,165,250,.14);
        stroke: rgba(96,165,250,.45);
    }
    .tooth-btn.has-proc .tooth-num{ color:#60a5fa; }

    .tooth-btn.selected .tooth-fill{
        fill: rgba(167,139,250,.16);
        stroke: rgba(167,139,250,.55);
    }
    .tooth-btn.selected .tooth-num{ color:#a78bfa; }

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
        font-weight: 950;
        display:flex;
        align-items:center;
        justify-content:center;
        padding: 0 6px;
        border: 2px solid rgba(255,255,255,.9);
        box-shadow: 0 10px 18px rgba(13,110,253,.25);
    }
    html[data-theme="dark"] .badge-count{
        border-color: rgba(17,24,39,.9);
        box-shadow: 0 10px 18px rgba(0,0,0,.35);
    }

    .tooth-btn.midgap{ margin-left: 10px; }
</style>

<div class="page-wrap">
    <div class="page-head form-max">
        <div>
            <h2 class="page-title">Edit Visit</h2>
            <p class="subtitle">Update visit details and procedures.</p>
        </div>

        <x-back-button
            fallback="{{ route('staff.visits.index') }}"
            class="btn-ghostx"
            label="Back"
        />
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
                <span class="pill">Edit</span>
                Update visit info, then add/remove procedures.
            </div>
            <div class="hint">
                <span class="pill">Tip</span>
                Select multiple teeth then press <strong>Add</strong>.
            </div>
        </div>

        <div class="card-bodyx">
            <form action="{{ route('staff.visits.update', $visit->id) }}" method="POST" id="visitForm">
                @csrf
    <input type="hidden" name="return" value="{{ old('return', request('return', session('kt.return_url', request()->fullUrl()))) }}">
                @method('PUT')

                <div class="row g-3">

                    {{-- Patient --}}
                    <div class="col-md-6">
                        <label class="form-labelx">Patient <span class="text-danger">*</span></label>
                        <select name="patient_id" class="selectx" required>
                            <option value="">-- Select Patient --</option>
                            @foreach($patients as $patient)
                                <option value="{{ $patient->id }}" {{ (int)$visit->patient_id === (int)$patient->id ? 'selected' : '' }}>
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
                                <option value="{{ $doc->id }}" @selected(old('doctor_id', $visit->doctor_id) == $doc->id)>
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
                        <input type="date" name="visit_date" class="inputx"
                               value="{{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('Y-m-d') : old('visit_date') }}"
                               required>
                        <div class="helptext">When the visit happened.</div>
                    </div>

                    {{-- Notes --}}
                    <div class="col-12">
                        <label class="form-labelx">Notes</label>
                        <textarea name="notes" rows="3" class="textareax" placeholder="Optional notes...">{{ old('notes', $visit->notes) }}</textarea>
                    </div>

                    {{-- ODONTOGRAM --}}
                    <div class="col-12">
                        <div class="divider"></div>

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
                                <span class="mini-hint">
                                    Rows: <strong id="procCountText">0</strong>
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Treatments / Procedures --}}
                    <div class="col-12">
                        <div class="divider"></div>

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
                                <tbody id="proceduresTable"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 d-flex gap-2 mt-2">
                        <button type="submit" class="btn-primaryx">
                            <i class="fa fa-check"></i> Update Visit
                        </button>

                        <a href="{{ route('staff.visits.index') }}" class="btn-ghostx">
                            Cancel
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
let procedures = @json($procedurePayload ?? []);
const selectedTeeth = new Set();

document.addEventListener('DOMContentLoaded', () => {
    window.toothInput   = document.getElementById('toothInput');
    window.surfaceInput = document.getElementById('surfaceInput');
    window.shadeInput   = document.getElementById('shadeInput');
    window.noteInput    = document.getElementById('noteInput');

    buildToothChartIconsMulti();
    renderProcedures();
});

/* ========= Procedures ========= */
document.getElementById('addProcedureBtn').addEventListener('click', () => {
    const serviceSelect = document.getElementById('serviceSelect');
    const serviceId = serviceSelect.value;
    const serviceName = serviceSelect.options[serviceSelect.selectedIndex]?.text?.trim();

    if (!serviceId) {
        alert('Select a service');
        return;
    }

    const chosen = Array.from(selectedTeeth);
    const list = chosen.length ? chosen : normalizeToothList(toothInput.value);

    // allow a general row even without tooth
    if (list.length === 0) list.push('');

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

    // clear selection + inputs
    selectedTeeth.clear();
    syncSelectedUI();

    toothInput.value = '';
    surfaceInput.value = '';
    shadeInput.value = '';
    noteInput.value = '';
    toothInput.focus();
});

function renderProcedures() {
    const table = document.getElementById('proceduresTable');
    table.innerHTML = '';

    procedures.forEach((p, i) => {
        table.innerHTML += `
            <tr>
                <td><span class="cell-chip"><i class="fa fa-stethoscope"></i> ${escapeHtml(p.service_name || '')}</span></td>
                <td>${p.tooth_number ? `<span class="cell-chip">${escapeHtml(p.tooth_number)}</span>` : `<span class="muted-dash">—</span>`}</td>
                <td>${p.surface ? `<span class="cell-chip">${escapeHtml(p.surface)}</span>` : `<span class="muted-dash">—</span>`}</td>
                <td>${p.shade ? `<span class="cell-chip">${escapeHtml(p.shade)}</span>` : `<span class="muted-dash">—</span>`}</td>
                <td>${p.notes ? escapeHtml(p.notes) : `<span class="muted-dash">—</span>`}</td>
                <td class="text-end">
                    <button type="button" class="btn-dangerx" onclick="removeProcedure(${i})">✕</button>
                </td>

                <input type="hidden" name="procedures[${i}][service_id]" value="${escapeAttr(p.service_id)}">
                <input type="hidden" name="procedures[${i}][tooth_number]" value="${escapeAttr(p.tooth_number ?? '')}">
                <input type="hidden" name="procedures[${i}][surface]" value="${escapeAttr(p.surface ?? '')}">
                <input type="hidden" name="procedures[${i}][shade]" value="${escapeAttr(p.shade ?? '')}">
                <input type="hidden" name="procedures[${i}][notes]" value="${escapeAttr(p.notes ?? '')}">
            </tr>
        `;
    });

    document.getElementById('procCountText').textContent = String(procedures.length);
    paintTeethFromProcedures();
}

function removeProcedure(i) {
    procedures.splice(i, 1);
    renderProcedures();
}

function normalizeToothList(str){
    return String(str || '')
        .split(',')
        .map(s => s.trim())
        .filter(Boolean);
}

/* ========= Odontogram multi-select ========= */
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
    chart.innerHTML = '';

    // match create: upper then lower
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

    document.getElementById('clearToothBtn').addEventListener('click', () => {
        selectedTeeth.clear();
        syncSelectedUI();
    });

    syncSelectedUI();
    paintTeethFromProcedures();
}

function syncSelectedUI(){
    const arr = Array.from(selectedTeeth);
    document.getElementById('selectedToothText').textContent = arr.length ? arr.join(', ') : 'None';
    if (window.toothInput) window.toothInput.value = arr.join(', ');

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

/* Escaping */
function escapeHtml(str) {
    return String(str ?? '').replace(/[&<>"']/g, s => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
}
function escapeAttr(str) {
    return escapeHtml(str).replace(/`/g, '&#96;');
}
</script>

@endsection

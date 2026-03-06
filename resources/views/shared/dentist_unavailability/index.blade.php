@extends($layout ?? 'layouts.staff')

@section('content')
@php
    $routePrefix = $routePrefix ?? 'staff.dentist-unavailability';
    $indexRoute = route($routePrefix . '.index');
    $storeRoute = route($routePrefix . '.store');
    $updateTemplate = route($routePrefix . '.update', ['doctorUnavailability' => '__ID__']);
    $today = now()->toDateString();
    $returnUrl = request()->fullUrl();
    $totalRecords = method_exists($items, 'total') ? (int) $items->total() : (int) $items->count();
    $selectedDoctorName = 'All dentists';
    if (!empty($selectedDoctorId)) {
        $match = $doctors->firstWhere('id', (int) $selectedDoctorId);
        if ($match) {
            $selectedDoctorName = $match->name ?? ('Dentist #' . $selectedDoctorId);
        }
    }
@endphp

<style>
    .du-page{ display:grid; gap:16px; }
    .du-head{
        border:1px solid rgba(148,163,184,.25);
        border-radius:22px;
        padding:18px 18px 16px;
        background:
            radial-gradient(620px 250px at 10% 0%, rgba(37,99,235,.14), transparent 62%),
            radial-gradient(560px 220px at 98% 18%, rgba(191,219,254,.28), transparent 62%),
            rgba(255,255,255,.86);
        box-shadow:0 18px 42px rgba(15,23,42,.10);
    }
    html[data-theme="dark"] .du-head{
        background:
            radial-gradient(620px 250px at 10% 0%, rgba(59,130,246,.18), transparent 62%),
            radial-gradient(560px 220px at 98% 18%, rgba(30,58,138,.42), transparent 62%),
            rgba(15,23,42,.88);
        box-shadow:0 22px 48px rgba(0,0,0,.32);
    }
    .du-kicker{
        text-transform:uppercase;
        letter-spacing:.13em;
        font-size:10px;
        font-weight:900;
        color:var(--kt-muted);
        margin-bottom:8px;
    }
    .du-title{
        margin:0;
        font-size:clamp(26px, 3.3vw, 38px);
        line-height:1.02;
        letter-spacing:-.035em;
        font-weight:950;
        color:var(--kt-text);
    }
    .du-sub{
        margin-top:9px;
        color:var(--kt-muted);
        font-weight:700;
        font-size:13px;
        max-width:900px;
    }
    .du-metrics{
        margin-top:12px;
        display:grid;
        grid-template-columns:repeat(3, minmax(0, 1fr));
        gap:10px;
    }
    .du-metric{
        border:1px solid rgba(148,163,184,.24);
        border-radius:14px;
        padding:10px 11px;
        background:rgba(255,255,255,.74);
    }
    html[data-theme="dark"] .du-metric{
        background:rgba(2,6,23,.40);
        border-color:rgba(148,163,184,.25);
    }
    .du-metric .k{
        font-size:10px;
        letter-spacing:.09em;
        text-transform:uppercase;
        font-weight:900;
        color:var(--kt-muted);
    }
    .du-metric .v{
        margin-top:4px;
        font-size:15px;
        line-height:1.2;
        font-weight:900;
        color:var(--kt-text);
    }
    .du-grid{
        display:grid;
        grid-template-columns:minmax(320px, 430px) minmax(0, 1fr);
        gap:14px;
        align-items:start;
    }
    .du-card{
        border:1px solid rgba(148,163,184,.24);
        border-radius:18px;
        background:rgba(255,255,255,.86);
        box-shadow:0 16px 40px rgba(15,23,42,.10);
        padding:15px;
    }
    html[data-theme="dark"] .du-card{
        background:rgba(15,23,42,.88);
        border-color:rgba(148,163,184,.24);
        box-shadow:0 20px 44px rgba(0,0,0,.32);
    }
    .du-card.sticky{ position:sticky; top:16px; }
    .du-form-chip{
        display:inline-flex;
        align-items:center;
        gap:7px;
        border-radius:999px;
        padding:6px 10px;
        border:1px solid rgba(59,130,246,.35);
        background:rgba(59,130,246,.12);
        color:#1d4ed8;
        font-size:10px;
        font-weight:900;
        text-transform:uppercase;
        letter-spacing:.09em;
    }
    html[data-theme="dark"] .du-form-chip{
        color:#bfdbfe;
        border-color:rgba(147,197,253,.40);
        background:rgba(30,58,138,.45);
    }
    .du-form-title{
        margin:10px 0 4px;
        font-size:21px;
        letter-spacing:-.02em;
        font-weight:900;
        color:var(--kt-text);
    }
    .du-form-help{
        margin-bottom:10px;
        font-size:12px;
        color:var(--kt-muted);
        font-weight:700;
    }
    .du-label{
        font-size:10px;
        text-transform:uppercase;
        letter-spacing:.09em;
        color:var(--kt-muted);
        font-weight:900;
        margin-bottom:6px;
    }
    .du-input{
        border-radius:12px !important;
        border:1px solid rgba(148,163,184,.34) !important;
        background:rgba(255,255,255,.95) !important;
        color:var(--kt-text) !important;
        font-weight:800 !important;
        padding:10px 11px !important;
        box-shadow:inset 0 1px 0 rgba(255,255,255,.75) !important;
    }
    html[data-theme="dark"] .du-input{
        background:rgba(15,23,42,.82) !important;
        border-color:rgba(148,163,184,.30) !important;
        box-shadow:none !important;
    }
    .du-input:focus{
        border-color:rgba(59,130,246,.58) !important;
        box-shadow:0 0 0 3px rgba(59,130,246,.18) !important;
    }
    .du-btn-primary{
        border-radius:12px;
        border:none;
        background:linear-gradient(135deg, #2563eb, #3b82f6);
        color:#fff;
        font-weight:900;
        padding:10px 14px;
        box-shadow:0 12px 28px rgba(37,99,235,.24);
    }
    .du-btn-primary:hover{ filter:brightness(.98); }
    .du-btn-ghost{
        border-radius:12px;
        border:1px solid rgba(148,163,184,.33);
        background:rgba(255,255,255,.72);
        color:var(--kt-text);
        font-weight:900;
        padding:10px 13px;
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        justify-content:center;
        gap:6px;
    }
    html[data-theme="dark"] .du-btn-ghost{ background:rgba(2,6,23,.35); }
    .du-btn-ghost:hover{
        background:rgba(59,130,246,.12);
        border-color:rgba(59,130,246,.34);
    }
    .du-list-top{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:10px;
    }
    .du-list-title{
        margin:0;
        font-size:13px;
        text-transform:uppercase;
        letter-spacing:.09em;
        color:var(--kt-muted);
        font-weight:900;
    }
    .du-list-sub{
        margin-top:5px;
        color:var(--kt-muted);
        font-size:12px;
        font-weight:700;
    }
    .du-filter{
        display:flex;
        align-items:center;
        gap:8px;
        flex-wrap:wrap;
        padding:10px;
        border:1px solid rgba(148,163,184,.25);
        border-radius:14px;
        background:rgba(148,163,184,.08);
    }
    html[data-theme="dark"] .du-filter{ background:rgba(2,6,23,.36); }
    .du-table-wrap{
        border:1px solid rgba(148,163,184,.24);
        border-radius:14px;
        overflow:auto;
        background:rgba(255,255,255,.76);
    }
    html[data-theme="dark"] .du-table-wrap{ background:rgba(2,6,23,.34); }
    .du-table{
        width:100%;
        border-collapse:collapse;
    }
    .du-table th,
    .du-table td{
        padding:11px 12px;
        border-bottom:1px solid rgba(148,163,184,.22);
        vertical-align:top;
    }
    .du-table th{
        font-size:10px;
        text-transform:uppercase;
        letter-spacing:.09em;
        color:var(--kt-muted);
        font-weight:900;
        background:rgba(148,163,184,.12);
    }
    .du-table tbody tr:hover{ background:rgba(59,130,246,.06); }
    .du-date-main,
    .du-doc-name{
        font-size:14px;
        font-weight:900;
        color:var(--kt-text);
        line-height:1.15;
    }
    .du-date-sub,
    .du-doc-sub{
        margin-top:2px;
        font-size:12px;
        font-weight:700;
        color:var(--kt-muted);
    }
    .du-reason{
        font-size:13px;
        line-height:1.45;
        color:var(--kt-text);
        font-weight:700;
        max-width:460px;
        word-break:break-word;
    }
    .du-actions{
        display:flex;
        justify-content:flex-end;
        gap:7px;
        flex-wrap:wrap;
    }
    .du-mini{
        border-radius:10px;
        border:1px solid rgba(148,163,184,.34);
        padding:7px 9px;
        font-size:12px;
        font-weight:900;
        background:rgba(255,255,255,.72);
        color:var(--kt-text);
        text-decoration:none;
        display:inline-flex;
        align-items:center;
        gap:6px;
    }
    html[data-theme="dark"] .du-mini{ background:rgba(2,6,23,.36); }
    .du-mini:hover{
        background:rgba(59,130,246,.12);
        border-color:rgba(59,130,246,.35);
    }
    .du-mini.danger:hover{
        background:rgba(239,68,68,.12);
        border-color:rgba(239,68,68,.35);
    }
    .du-empty{
        padding:26px 14px;
        text-align:center;
    }
    .du-empty .ico{
        width:42px;
        height:42px;
        border-radius:13px;
        border:1px solid rgba(148,163,184,.35);
        display:grid;
        place-items:center;
        margin:0 auto 10px;
        color:var(--kt-muted);
        background:rgba(148,163,184,.12);
    }
    .du-empty .t{
        color:var(--kt-text);
        font-size:15px;
        font-weight:900;
        letter-spacing:-.01em;
    }
    .du-empty .s{
        margin-top:4px;
        color:var(--kt-muted);
        font-size:12px;
        font-weight:700;
    }
    @media (max-width: 1120px){
        .du-grid{ grid-template-columns:1fr; }
        .du-card.sticky{ position:static; top:auto; }
    }
    @media (max-width: 860px){
        .du-metrics{ grid-template-columns:1fr; }
        .du-table{ min-width:620px; }
        .du-filter{
            display:grid;
            grid-template-columns:1fr 1fr;
            width:100%;
        }
        .du-filter > *:nth-child(3),
        .du-filter > *:nth-child(4){
            width:100%;
        }
    }
    @media (max-width: 560px){
        .du-filter{ grid-template-columns:1fr; }
    }
</style>

<div class="du-page">
    <div class="du-head">
        <div class="du-kicker">Calendar Control</div>
        <h1 class="du-title">{{ $pageTitle ?? 'Dentist Day-off' }}</h1>
        <div class="du-sub">{{ $pageSubtitle ?? 'Manage dentist unavailable dates.' }}</div>
        <div class="du-metrics">
            <div class="du-metric">
                <div class="k">Upcoming Records</div>
                <div class="v">{{ number_format($totalRecords) }}</div>
            </div>
            <div class="du-metric">
                <div class="k">Filtered Dentist</div>
                <div class="v">{{ $selectedDoctorName }}</div>
            </div>
            <div class="du-metric">
                <div class="k">From Date</div>
                <div class="v">{{ \Carbon\Carbon::parse($fromDate)->format('M d, Y') }}</div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success mb-0" style="border-radius:12px;">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger mb-0" style="border-radius:12px;">{{ session('error') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-0" style="border-radius:12px;">
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="du-grid">
        <div class="du-card sticky">
            <span class="du-form-chip" id="duFormChip">
                <i class="fa-solid fa-plus"></i> New Record
            </span>
            <h2 class="du-form-title" id="duFormTitle">Set Dentist Unavailable Date</h2>
            <div class="du-form-help">Use this for meetings, seminars, training, leave, or clinic events.</div>

            <form id="duForm"
                  method="POST"
                  action="{{ $storeRoute }}"
                  data-store-action="{{ $storeRoute }}"
                  data-update-template="{{ $updateTemplate }}">
                @csrf
                <input type="hidden" name="_method" id="duMethod">
                <input type="hidden" name="return" value="{{ $returnUrl }}">

                <div class="mb-3">
                    <label class="du-label">Dentist</label>
                    <select class="form-select du-input" name="doctor_id" id="duDoctor" required>
                        <option value="">Choose dentist...</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected((string) old('doctor_id', $selectedDoctorId) === (string) $doctor->id)>
                                {{ $doctor->name }}{{ !empty($doctor->specialty) ? ' - ' . $doctor->specialty : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label class="du-label">Unavailable Date</label>
                    <input type="date"
                           class="form-control du-input"
                           name="unavailable_date"
                           id="duDate"
                           value="{{ old('unavailable_date', $today) }}"
                           min="{{ $today }}"
                           required>
                </div>

                <div class="mb-3">
                    <label class="du-label">Reason (optional)</label>
                    <input type="text"
                           class="form-control du-input"
                           name="reason"
                           id="duReason"
                           maxlength="255"
                           value="{{ old('reason') }}"
                           placeholder="Meeting, seminar, leave, etc.">
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="submit" class="du-btn-primary">
                        <i class="fa-solid fa-floppy-disk me-1"></i>
                        <span id="duSubmitLabel">Save Date</span>
                    </button>
                    <button type="button" class="du-btn-ghost d-none" id="duCancelEdit">
                        Cancel Edit
                    </button>
                </div>
            </form>
        </div>

        <div class="du-card">
            <div class="du-list-top">
                <div>
                    <h3 class="du-list-title">Upcoming Unavailable Dates</h3>
                    <div class="du-list-sub">{{ number_format($totalRecords) }} record(s) found</div>
                </div>

                <form method="GET" action="{{ $indexRoute }}" class="du-filter">
                    <select class="form-select du-input" name="doctor_id" style="min-width:220px;">
                        <option value="">All dentists</option>
                        @foreach($doctors as $doctor)
                            <option value="{{ $doctor->id }}" @selected((string) $selectedDoctorId === (string) $doctor->id)>
                                {{ $doctor->name }}
                            </option>
                        @endforeach
                    </select>
                    <input type="date" class="form-control du-input" name="from_date" value="{{ $fromDate }}">
                    <button type="submit" class="du-btn-ghost">Filter</button>
                    <a href="{{ $indexRoute }}" class="du-btn-ghost">Reset</a>
                </form>
            </div>

            <div class="du-table-wrap">
                <table class="du-table">
                    <thead>
                        <tr>
                            <th style="width:170px;">Date</th>
                            <th style="min-width:240px;">Dentist</th>
                            <th>Reason</th>
                            <th class="text-end" style="width:170px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $rowDateIso = optional($item->unavailable_date)->format('Y-m-d');
                            @endphp
                            <tr>
                                <td>
                                    <div class="du-date-main">{{ optional($item->unavailable_date)->format('M d, Y') }}</div>
                                    <div class="du-date-sub">{{ optional($item->unavailable_date)->format('l') }}</div>
                                </td>
                                <td>
                                    <div class="du-doc-name">{{ $item->doctor->name ?? ('Dentist #' . $item->doctor_id) }}</div>
                                    <div class="du-doc-sub">{{ $item->doctor->specialty ?? 'No specialty set' }}</div>
                                </td>
                                <td>
                                    <div class="du-reason">{{ $item->reason ?: 'No reason provided.' }}</div>
                                </td>
                                <td>
                                    <div class="du-actions">
                                        <button type="button"
                                                class="du-mini"
                                                data-edit-dayoff
                                                data-id="{{ $item->id }}"
                                                data-doctor-id="{{ $item->doctor_id }}"
                                                data-date="{{ $rowDateIso }}"
                                                data-reason="{{ $item->reason ?? '' }}">
                                            <i class="fa-solid fa-pen"></i> Edit
                                        </button>

                                        <form method="POST" action="{{ route($routePrefix . '.destroy', ['doctorUnavailability' => $item->id]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <input type="hidden" name="return" value="{{ $returnUrl }}">
                                            <button type="submit"
                                                    class="du-mini danger"
                                                    onclick="return confirm('Remove this unavailable date?');">
                                                <i class="fa-solid fa-trash"></i> Remove
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="du-empty">
                                        <div class="ico"><i class="fa-solid fa-calendar-check"></i></div>
                                        <div class="t">No unavailable dates yet</div>
                                        <div class="s">Create one from the left panel to block a dentist for meetings or leave.</div>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $items->links() }}
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('duForm');
    if (!form) return;

    const chipEl = document.getElementById('duFormChip');
    const titleEl = document.getElementById('duFormTitle');
    const submitLabelEl = document.getElementById('duSubmitLabel');
    const methodEl = document.getElementById('duMethod');
    const doctorEl = document.getElementById('duDoctor');
    const dateEl = document.getElementById('duDate');
    const reasonEl = document.getElementById('duReason');
    const cancelEl = document.getElementById('duCancelEdit');

    const storeAction = form.dataset.storeAction || form.action;
    const updateTemplate = form.dataset.updateTemplate || '';
    const editingId = @json(session('editing_id'));
    const oldDoctorId = @json(old('doctor_id'));
    const oldDate = @json(old('unavailable_date'));
    const oldReason = @json(old('reason'));

    function setCreateMode() {
        form.action = storeAction;
        methodEl.value = '';
        if (chipEl) chipEl.innerHTML = '<i class="fa-solid fa-plus"></i> New Record';
        if (titleEl) titleEl.textContent = 'Set Dentist Unavailable Date';
        if (submitLabelEl) submitLabelEl.textContent = 'Save Date';
        cancelEl?.classList.add('d-none');
    }

    function setEditMode(payload) {
        if (!payload || !payload.id || !updateTemplate) return;

        form.action = updateTemplate.replace('__ID__', String(payload.id));
        methodEl.value = 'PUT';
        if (doctorEl) doctorEl.value = payload.doctorId || '';
        if (dateEl) dateEl.value = payload.date || '';
        if (reasonEl) reasonEl.value = payload.reason || '';

        if (chipEl) chipEl.innerHTML = '<i class="fa-solid fa-pen"></i> Editing';
        if (titleEl) titleEl.textContent = 'Edit Dentist Unavailable Date';
        if (submitLabelEl) submitLabelEl.textContent = 'Update Date';
        cancelEl?.classList.remove('d-none');
    }

    document.querySelectorAll('[data-edit-dayoff]').forEach((btn) => {
        btn.addEventListener('click', () => {
            setEditMode({
                id: btn.dataset.id || '',
                doctorId: btn.dataset.doctorId || '',
                date: btn.dataset.date || '',
                reason: btn.dataset.reason || ''
            });
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    });

    cancelEl?.addEventListener('click', () => {
        setCreateMode();
        if (doctorEl) doctorEl.value = '';
        if (dateEl) dateEl.value = @json($today);
        if (reasonEl) reasonEl.value = '';
    });

    if (editingId) {
        setEditMode({
            id: editingId,
            doctorId: oldDoctorId || '',
            date: oldDate || '',
            reason: oldReason || ''
        });
    } else {
        setCreateMode();
    }
})();
</script>
@endpush
@endsection

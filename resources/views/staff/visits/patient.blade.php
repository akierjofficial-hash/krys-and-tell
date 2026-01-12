@extends('layouts.staff')

@section('content')

<style>
    /* ==========================================================
       Visits > Patient (Dark mode compatible)
       - Uses layout tokens: --kt-text, --kt-muted, --kt-surface, --kt-surface-2,
                           --kt-border, --kt-shadow
       ========================================================== */
    :root{
        --card-shadow: var(--kt-shadow);
        --card-border: 1px solid var(--kt-border);
        --soft: rgba(148,163,184,.14);

        --text: var(--kt-text);
        --muted: var(--kt-muted);
        --muted2: rgba(148,163,184,.70);

        --brand1: #0d6efd;
        --brand2: #1e90ff;
        --radius: 16px;

        --focus: rgba(96,165,250,.55);
        --focusRing: rgba(96,165,250,.18);
    }
    html[data-theme="dark"]{
        --soft: rgba(148,163,184,.16);
        --muted2: rgba(148,163,184,.66);
    }

    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.4px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    .top-actions{
        display:flex;
        align-items:center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .btnx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid var(--kt-border);
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        background: var(--kt-surface-2);
        color: var(--text) !important;
    }
    .btnx:hover{
        transform: translateY(-1px);
        background: rgba(148,163,184,.14);
    }
    html[data-theme="dark"] .btnx:hover{
        background: rgba(17,24,39,.75);
    }

    .add-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        padding: 11px 14px;
        color: #fff !important;
        font-weight: 900;
        border-radius: 12px;
        font-size: 14px;
        text-decoration: none;
        box-shadow: 0 12px 18px rgba(13, 110, 253, .18);
        transition: .15s ease;
        white-space: nowrap;
    }
    .add-btn:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .24);
    }

    .card-shell{
        background: var(--kt-surface);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        backdrop-filter: blur(8px);
        min-width: 0;
    }

    .table-wrap{ padding: 8px 10px 10px 10px; }
    table{ width: 100%; border-collapse: separate; border-spacing: 0; }

    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: var(--muted);
        padding: 14px 14px;
        border-bottom: 1px solid var(--soft);
        background: rgba(148,163,184,.12);
        position: sticky;
        top: 0;
        z-index: 1;
        white-space: nowrap;
    }
    html[data-theme="dark"] thead th{
        background: rgba(2,6,23,.35);
    }

    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid var(--soft);
        vertical-align: middle;
    }
    tbody tr:hover{ background: rgba(96,165,250,.08); }
    .muted{ color: var(--muted); }

    .tags{ display:flex; flex-wrap: wrap; gap: 6px; }
    .tag{
        display:inline-flex;
        align-items:center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 850;
        background: rgba(148,163,184,.12);
        color: var(--text);
        border: 1px solid rgba(148,163,184,.18);
        white-space: nowrap;
    }
    html[data-theme="dark"] .tag{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.20);
    }

    .action-pills{
        display:flex;
        align-items:center;
        gap: 8px;
        justify-content:flex-end;
        flex-wrap: wrap;
    }
    .pill{
        display:inline-flex;
        align-items:center;
        gap: 6px;
        padding: 7px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 900;
        border: 1px solid transparent;
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
        background: transparent;
    }
    .pill i{ font-size: 12px; }

    .pill-edit{
        background: rgba(34, 197, 94, .14);
        color: #22c55e !important;
        border-color: rgba(34, 197, 94, .22);
    }
    .pill-edit:hover{ background: rgba(34,197,94,.20); }

    .pill-view{
        background: rgba(96,165,250,.14);
        color: #60a5fa !important;
        border-color: rgba(96,165,250,.22);
    }
    .pill-view:hover{ background: rgba(96,165,250,.20); }

    .pill-del{
        background: rgba(239, 68, 68, .14);
        color: #ef4444 !important;
        border-color: rgba(239, 68, 68, .22);
        cursor: pointer;
    }
    .pill-del:hover{ background: rgba(239,68,68,.20); }

    /* Pagination (bootstrap-5) */
    .pagination { margin: 0; gap: 6px; flex-wrap: wrap; }
    .pagination .page-link{
        border-radius: 10px;
        font-weight: 900;
        border: 1px solid var(--kt-border);
        color: var(--text);
        background: var(--kt-surface-2);
    }
    .pagination .page-item.active .page-link{
        background: rgba(96,165,250,.14);
        border-color: rgba(96,165,250,.25);
        color: #60a5fa;
    }
</style>

@php
    $patientLabel = trim(($patient->last_name ?? '').', '.($patient->first_name ?? ''));
    // If $visits is paginated, total() exists; otherwise fallback to count()
    $visitTotal = method_exists($visits, 'total') ? $visits->total() : $visits->count();
@endphp

<div class="page-head">
    <div>
        <h2 class="page-title">Visit Records</h2>
        <p class="subtitle">
            {{ $patientLabel }} — {{ $visitTotal }} total visit{{ $visitTotal === 1 ? '' : 's' }}
        </p>
    </div>

    <div class="top-actions">
        <x-back-button
            fallback="{{ route('staff.visits.index') }}"
            class="btnx"
            label="Back"
        />

        <a href="{{ route('staff.visits.create', ['patient_id' => $patient->id]) }}" class="add-btn">
            <i class="fa fa-plus"></i> Add Visit
        </a>
    </div>
</div>

<div class="card-shell">
    <div class="table-wrap table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Visit Date</th>
                    <th>Assigned Dentist</th>
                    <th>Reason / Notes</th>
                    <th>Treatments</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($visits as $visit)
                    @php
                        $dentistLabel = trim((string)($visit->dentist_name ?? '')) !== ''
                            ? $visit->dentist_name
                            : (optional($visit->doctor)->name ?: '—');
                    @endphp

                    <tr>
                        <td style="font-weight:900;">
                            {{ $visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('m/d/Y') : '—' }}
                        </td>

                        <td class="muted">{{ $dentistLabel }}</td>

                        <td class="muted">{{ $visit->notes ?? '—' }}</td>

                        <td>
                            @if($visit->procedures->count() > 0)
                                @php
                                    $chips = collect();

                                    foreach($visit->procedures->groupBy(fn($p) => $p->service?->name ?? '—') as $serviceName => $rows){
                                        $rows = $rows->values();

                                        $hasTooth = $rows->contains(fn($p) => !empty($p->tooth_number) || !empty($p->surface));

                                        $notes = $rows->pluck('notes')
                                            ->filter(fn($n) => trim((string)$n) !== '')
                                            ->map(fn($n) => trim((string)$n))
                                            ->unique()
                                            ->values();

                                        if(!$hasTooth && $notes->count()){
                                            foreach($notes as $n){
                                                $chips->push($serviceName.' — '.\Illuminate\Support\Str::limit($n, 28));
                                            }
                                        } else {
                                            $chips->push($serviceName.' ('.$rows->count().')');
                                        }
                                    }
                                @endphp

                                <div class="tags">
                                    @foreach($chips as $chip)
                                        <span class="tag">{{ $chip }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="muted">—</span>
                            @endif
                        </td>

                        <td class="text-end">
                            <div class="action-pills">
                                <a href="{{ route('staff.visits.edit', [$visit->id, 'return' => url()->full()]) }}" class="pill pill-edit">
                                    <i class="fa fa-pen"></i> Edit
                                </a>

                                <a href="{{ route('staff.visits.show', [$visit->id, 'return' => url()->full()]) }}" class="pill pill-view">
                                    <i class="fa fa-eye"></i> View
                                </a>

                                <form action="{{ route('staff.visits.destroy', $visit->id) }}"
                                      method="POST"
                                      style="display:inline;"
                                      onsubmit="return confirm('Delete this visit?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="pill pill-del">
                                        <i class="fa fa-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">No visits found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($visits, 'hasPages') && $visits->hasPages())
        <div class="px-3 pb-3">
            {{ $visits->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@endsection

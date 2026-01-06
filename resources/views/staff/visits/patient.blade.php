@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: 0 12px 30px rgba(15, 23, 42, .08);
        --card-border: 1px solid rgba(15, 23, 42, .10);
        --soft: rgba(15, 23, 42, .06);
        --text: #0f172a;
        --muted: rgba(15, 23, 42, .58);
        --brand1: #0d6efd;
        --brand2: #1e90ff;
        --radius: 16px;
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
        font-weight: 900;
        letter-spacing: -0.4px;
        margin: 0;
        color: var(--text);
    }
    .subtitle{
        margin: 6px 0 0 0;
        font-size: 13px;
        color: rgba(15, 23, 42, .58);
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
        font-weight: 800;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid transparent;
        transition: .15s ease;
        white-space: nowrap;
        cursor: pointer;
        user-select: none;
        background: rgba(15,23,42,.05);
        border-color: rgba(15,23,42,.10);
        color: rgba(15,23,42,.75) !important;
    }
    .btnx:hover{ background: rgba(15,23,42,.07); }

    .add-btn{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        background: linear-gradient(135deg, var(--brand1), var(--brand2));
        padding: 11px 14px;
        color: #fff !important;
        font-weight: 800;
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
        background: rgba(255,255,255,.94);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
    }
    .table-wrap{ padding: 8px 10px 10px 10px; }
    table{ width: 100%; border-collapse: separate; border-spacing: 0; }

    thead th{
        font-size: 12px;
        letter-spacing: .3px;
        text-transform: uppercase;
        color: rgba(15, 23, 42, .55);
        padding: 14px 14px;
        border-bottom: 1px solid rgba(15, 23, 42, .08);
        background: rgba(248, 250, 252, .9);
        position: sticky;
        top: 0;
        z-index: 1;
        white-space: nowrap;
    }
    tbody td{
        padding: 14px 14px;
        font-size: 14px;
        color: var(--text);
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        vertical-align: middle;
    }
    tbody tr:hover{ background: rgba(13,110,253,.06); }
    .muted{ color: var(--muted); }

    .tags{ display:flex; flex-wrap: wrap; gap: 6px; }
    .tag{
        display:inline-flex;
        align-items:center;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 700;
        background: rgba(15, 23, 42, .06);
        color: rgba(15, 23, 42, .75);
        border: 1px solid rgba(15, 23, 42, .08);
        white-space: nowrap;
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
        font-weight: 800;
        border: 1px solid transparent;
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
        background: transparent;
    }
    .pill i{ font-size: 12px; }

    .pill-edit{
        background: rgba(34, 197, 94, .12);
        color: #15803d !important;
        border-color: rgba(34, 197, 94, .22);
    }
    .pill-view{
        background: rgba(59, 130, 246, .12);
        color: #1d4ed8 !important;
        border-color: rgba(59, 130, 246, .22);
    }
    .pill-del{
        background: rgba(239, 68, 68, .12);
        color: #b91c1c !important;
        border-color: rgba(239, 68, 68, .22);
        cursor: pointer;
    }
</style>

@php
    $patientLabel = trim(($patient->last_name ?? '').', '.($patient->first_name ?? ''));
@endphp

<div class="page-head">
    <div>
        <h2 class="page-title">Visit Records</h2>
        <p class="subtitle">
            {{ $patientLabel }} — {{ $visits->count() }} total visit{{ $visits->count() === 1 ? '' : 's' }}
        </p>
    </div>

    <div class="top-actions">
        <a href="{{ route('staff.visits.index') }}" class="btnx">
            <i class="fa fa-arrow-left"></i> Back
        </a>

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
                        <td>
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
                                <a href="{{ route('staff.visits.edit', $visit->id) }}" class="pill pill-edit">
                                    <i class="fa fa-pen"></i> Edit
                                </a>

                                <a href="{{ route('staff.visits.show', $visit->id) }}" class="pill pill-view">
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
</div>

@endsection

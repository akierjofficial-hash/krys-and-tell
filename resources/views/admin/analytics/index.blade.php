@extends('layouts.admin')

@push('styles')
<style>
    /* ===== Page Header (Dashboard vibe) ===== */
    .a-wrap{
        padding: 12px 0 18px;
        border-radius: 18px;
    }

    .a-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap:16px;
        flex-wrap:wrap;
        margin: 6px 0 12px;
    }
    .a-title{
        margin:0;
        font-size: 28px;
        font-weight: 950;
        letter-spacing: -0.6px;
        line-height: 1.12;
        color: var(--text);
    }
    .a-sub{
        margin-top: 6px;
        font-size: 13px;
        color: var(--muted);
        font-weight: 800;
    }

    /* ===== Filters (glass card) ===== */
    .filter-card{
        padding: 14px 14px;
        border-radius: 20px;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,23,42,.10);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        backdrop-filter: blur(10px);
        position: relative;
        overflow:hidden;
        margin-bottom: 14px;
    }
    html[data-theme="dark"] .filter-card{
        background: rgba(17,24,39,.78);
        border-color: rgba(148,163,184,.18);
    }
    .filter-card::before{
        content:"";
        position:absolute;
        inset:-2px;
        background:
            radial-gradient(900px 240px at 18% 0%, rgba(37,99,235,.12), transparent 55%),
            radial-gradient(900px 240px at 82% 0%, rgba(124,58,237,.10), transparent 60%);
        pointer-events:none;
    }
    .filter-inner{
        position:relative;
        z-index:1;
        display:flex;
        align-items:end;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
    }
    .filter-left{
        display:flex;
        gap:10px;
        align-items:end;
        flex-wrap:wrap;
    }
    .lbl{
        font-size: 12px;
        color: var(--muted);
        font-weight: 900;
        margin-bottom: 6px;
    }

    .kt-select, .kt-date{
        padding: 10px 12px;
        border-radius: 14px;
        border: 1px solid rgba(15,23,42,.12);
        background: rgba(255,255,255,.75);
        color: var(--text);
        font-weight: 950;
        outline:none;
        box-shadow: 0 12px 20px rgba(15,23,42,.06);
        transition: .15s ease;
        min-width: 180px;
    }
    .kt-date{ min-width: 170px; }

    html[data-theme="dark"] .kt-select,
    html[data-theme="dark"] .kt-date{
        background: rgba(2,6,23,.35) !important;
        border-color: rgba(148,163,184,.18) !important;
        color: var(--text) !important;
    }

    .kt-select:focus, .kt-date:focus{
        border-color: rgba(37,99,235,.28);
        box-shadow: 0 0 0 4px rgba(37,99,235,.14);
        transform: translateY(-1px);
    }

    .kt-btn{
        padding: 10px 14px;
        border-radius: 14px;
        border: 1px solid rgba(37,99,235,.22);
        background: rgba(37,99,235,.10);
        color: var(--text);
        font-weight: 950;
        cursor:pointer;
        box-shadow: 0 12px 20px rgba(37,99,235,.10);
        transition: .15s ease;
        white-space: nowrap;
    }
    .kt-btn:hover{
        transform: translateY(-1px);
        background: rgba(37,99,235,.14);
        border-color: rgba(37,99,235,.28);
    }

    /* ===== Grid ===== */
    .a-grid{
        display:grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 14px;
    }
    @media (max-width: 980px){
        .a-grid{ grid-template-columns: 1fr; }
        .kt-select{ min-width: 220px; }
    }

    /* ===== KPI Card (Dashboard card vibe + hover) ===== */
    .a-card{
        padding: 14px 14px;
        border-radius: 20px;
        position:relative;
        overflow:hidden;
        transition: .18s ease;
        background: rgba(255,255,255,.86);
        border: 1px solid rgba(15,23,42,.10);
        box-shadow: 0 14px 36px rgba(15, 23, 42, .10);
        backdrop-filter: blur(10px);
    }
    html[data-theme="dark"] .a-card{
        background: rgba(17,24,39,.78);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 18px 48px rgba(0,0,0,.45);
    }
    .a-card::after{
        content:"";
        position:absolute;
        inset:-2px;
        background: radial-gradient(circle at top left, rgba(37,99,235,.12), transparent 55%);
        opacity: 0;
        transition: .18s ease;
        pointer-events:none;
    }
    .a-card:hover{
        transform: translateY(-2px);
        box-shadow: 0 22px 44px rgba(15,23,42,.14);
    }
    html[data-theme="dark"] .a-card:hover{
        box-shadow: 0 26px 60px rgba(0,0,0,.55);
    }
    .a-card:hover::after{ opacity: 1; }

    .a-head2{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
        margin-bottom: 10px;
        position:relative;
        z-index:1;
    }
    .a-title2{
        font-weight: 950;
        letter-spacing: -.2px;
        font-size: 14px;
        margin: 0;
        display:flex;
        gap:10px;
        align-items:center;
        color: var(--text);
    }

    .badge-delta{
        display:inline-flex;
        align-items:center;
        gap:6px;
        padding: 6px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 950;
        border: 1px solid rgba(148,163,184,.18);
        white-space: nowrap;
        box-shadow: 0 10px 18px rgba(15,23,42,.06);
    }
    .delta-up{ background: rgba(34,197,94,.12); color: #16a34a; }
    .delta-down{ background: rgba(239,68,68,.12); color: #dc2626; }
    .delta-flat{ background: rgba(148,163,184,.14); color: var(--text); }

    /* ===== Chart Box ===== */
    .a-chart{
        height: 180px;
        border-radius: 18px;
        border: 1px solid rgba(15,23,42,.10);
        padding: 12px;
        background: rgba(255,255,255,.70);
        overflow:hidden;
        position:relative;
        z-index:1;
        box-shadow: inset 0 0 0 1px rgba(255,255,255,.35);
        transition: .15s ease;
    }
    html[data-theme="dark"] .a-chart{
        background: rgba(2,6,23,.30);
        border-color: rgba(148,163,184,.18);
    }
    .a-card:hover .a-chart{
        transform: translateY(-1px);
        border-color: rgba(37,99,235,.22);
    }

    /* ===== Metric Boxes ===== */
    .a-metrics{
        display:grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
        position:relative;
        z-index:1;
    }
    .m-box{
        border-radius: 18px;
        border: 1px solid rgba(15,23,42,.10);
        padding: 10px 10px;
        background: rgba(255,255,255,.78);
        min-height: 66px;
        transition: .15s ease;
        box-shadow: 0 12px 20px rgba(15,23,42,.06);
    }
    html[data-theme="dark"] .m-box{
        background: rgba(2,6,23,.30);
        border-color: rgba(148,163,184,.18);
    }
    .m-box:hover{
        transform: translateY(-1px);
        border-color: rgba(37,99,235,.20);
        box-shadow: 0 18px 28px rgba(15,23,42,.10);
    }
    .m-label{ font-size: 11px; color: var(--muted); font-weight: 900; }
    .m-val{ font-size: 16px; font-weight: 950; margin-top: 2px; white-space:nowrap; color: var(--text); }

    .span-2{ grid-column: span 2; }
    @media (max-width: 980px){ .span-2{ grid-column: auto; } }

    /* little helper row for custom inputs */
    #customDates{ display:none; gap:10px; align-items:end; }
</style>
@endpush

@section('content')
@php
    $labelsPretty = collect($labels ?? [])->map(fn($d) => \Carbon\Carbon::parse($d)->format('M d'))->values();

    $rev = collect($revenueSeries ?? []);
    $ap  = collect($appointmentsSeries ?? []);
    $np  = collect($patientsSeries ?? []);

    $avgRevenue = $rev->count() ? ($rev->sum() / $rev->count()) : 0;
    $avgAppt    = $ap->count() ? ($ap->sum() / $ap->count()) : 0;
    $avgNewPat  = $np->count() ? ($np->sum() / $np->count()) : 0;

    $svcLabels = collect($topServices ?? [])->pluck('name')->values();
    $svcTotals = collect($topServices ?? [])->pluck('total')->values();
    $svcColors = collect($topServices ?? [])->map(fn($s) => $s->color ?: '#64748b')->values();

    $fmtDelta = function($pct){
        if ($pct === null) return ['—', 'delta-flat'];
        $v = (float)$pct;
        if (abs($v) < 0.01) return ['0%', 'delta-flat'];
        $sign = $v > 0 ? '+' : '';
        return [$sign . number_format($v, 1) . '%', $v > 0 ? 'delta-up' : 'delta-down'];
    };

    [$revDeltaText, $revDeltaClass] = $fmtDelta($revenueChangePct ?? null);
    [$apptDeltaText, $apptDeltaClass] = $fmtDelta($appointmentsChangePct ?? null);
    [$patDeltaText, $patDeltaClass] = $fmtDelta($patientsChangePct ?? null);
@endphp

<div class="a-wrap">

    <div class="a-head">
        <div>
            <h2 class="a-title">Analytics</h2>
            <div class="a-sub">{{ $periodLabel ?? 'Overview' }} (compared to previous period)</div>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.analytics.index') }}">
        <div class="filter-card">
            <div class="filter-inner">
                <div class="filter-left">
                    <div>
                        <div class="lbl">Range</div>
                        <select name="range" id="rangeSelect" class="kt-select">
                            <option value="7" @selected(($range ?? '30') === '7')>Last 7 days</option>
                            <option value="30" @selected(($range ?? '30') === '30')>Last 30 days</option>
                            <option value="month" @selected(($range ?? '30') === 'month')>This month</option>
                            <option value="custom" @selected(($range ?? '30') === 'custom')>Custom</option>
                        </select>
                    </div>

                    <div id="customDates">
                        <div>
                            <div class="lbl">Start</div>
                            <input type="date" name="start_date" class="kt-date" value="{{ $startDate ?? '' }}">
                        </div>
                        <div>
                            <div class="lbl">End</div>
                            <input type="date" name="end_date" class="kt-date" value="{{ $endDate ?? '' }}">
                        </div>
                    </div>

                    <div style="padding-top:22px;">
                        <button class="kt-btn" type="submit">
                            <i class="fa fa-filter me-2"></i>Apply
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="a-grid">

        {{-- Revenue --}}
        <div class="a-card">
            <div class="a-head2">
                <div class="a-title2">
                    Revenue
                    <span class="badge-delta {{ $revDeltaClass }}">{{ $revDeltaText }}</span>
                </div>
            </div>

            <div class="a-chart"><canvas id="chartRevenue"></canvas></div>

            <div class="a-metrics">
                <div class="m-box">
                    <div class="m-label">Total</div>
                    <div class="m-val">₱{{ number_format($kpiRevenue ?? 0, 2) }}</div>
                </div>
                <div class="m-box">
                    <div class="m-label">Avg / Day</div>
                    <div class="m-val">₱{{ number_format($avgRevenue, 2) }}</div>
                </div>
                <div class="m-box">
                    <div class="m-label">Days</div>
                    <div class="m-val">{{ number_format($rev->count()) }}</div>
                </div>
            </div>
        </div>

        {{-- Appointments --}}
        <div class="a-card">
            <div class="a-head2">
                <div class="a-title2">
                    Appointments
                    <span class="badge-delta {{ $apptDeltaClass }}">{{ $apptDeltaText }}</span>
                </div>
            </div>

            <div class="a-chart"><canvas id="chartAppointments"></canvas></div>

            <div class="a-metrics">
                <div class="m-box">
                    <div class="m-label">Total</div>
                    <div class="m-val">{{ number_format($kpiAppointments ?? 0) }}</div>
                </div>
                <div class="m-box">
                    <div class="m-label">Avg / Day</div>
                    <div class="m-val">{{ number_format($avgAppt, 1) }}</div>
                </div>
                <div class="m-box">
                    <div class="m-label">Days</div>
                    <div class="m-val">{{ number_format($ap->count()) }}</div>
                </div>
            </div>
        </div>

        {{-- New Patients --}}
        <div class="a-card">
            <div class="a-head2">
                <div class="a-title2">
                    New Patients
                    <span class="badge-delta {{ $patDeltaClass }}">{{ $patDeltaText }}</span>
                </div>
            </div>

            <div class="a-chart"><canvas id="chartPatients"></canvas></div>

            <div class="a-metrics">
                <div class="m-box">
                    <div class="m-label">Total</div>
                    <div class="m-val">{{ number_format($kpiNewPatients ?? 0) }}</div>
                </div>
                <div class="m-box">
                    <div class="m-label">Avg / Day</div>
                    <div class="m-val">{{ number_format($avgNewPat, 1) }}</div>
                </div>
                <div class="m-box">
                    <div class="m-label">Days</div>
                    <div class="m-val">{{ number_format($np->count()) }}</div>
                </div>
            </div>
        </div>

        {{-- Top Services --}}
        <div class="a-card">
            <div class="a-head2">
                <div class="a-title2">Top Services</div>
            </div>

            <div class="a-chart"><canvas id="chartTopServices"></canvas></div>

            <div class="a-metrics">
                <div class="m-box span-2">
                    <div class="m-label">Top Service</div>
                    <div class="m-val">{{ $kpiTopService ?? '—' }}</div>
                </div>
                <div class="m-box">
                    <div class="m-label">Items</div>
                    <div class="m-val">{{ number_format($svcLabels->count()) }}</div>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    const labels = @json($labelsPretty);
    const revenue = @json($revenueSeries);
    const appointments = @json($appointmentsSeries);
    const patients = @json($patientsSeries);

    const svcLabels = @json($svcLabels);
    const svcTotals = @json($svcTotals);
    const svcColors = @json($svcColors);

    function cssVar(name){ return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }

    let charts = [];

    function makeLine(id, data){
        const ctx = document.getElementById(id)?.getContext('2d');
        if (!ctx) return null;

        const muted = cssVar('--muted');

        return new Chart(ctx,{
            type:'line',
            data:{ labels, datasets:[{
                data,
                tension: 0.35,
                pointRadius: 0,
                borderWidth: 2,
                fill: true,
                backgroundColor: 'rgba(59,130,246,.12)',
                borderColor: 'rgba(59,130,246,.95)',
            }]},
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{ legend:{ display:false } },
                scales:{
                    x:{ grid:{ display:false }, ticks:{ color: muted, font:{ weight:900 }, maxRotation:0 } },
                    y:{ grid:{ color:'rgba(148,163,184,.14)' }, ticks:{ color: muted, font:{ weight:900 } } }
                }
            }
        });
    }

    function makeBars(id){
        const ctx = document.getElementById(id)?.getContext('2d');
        if (!ctx) return null;

        const muted = cssVar('--muted');

        return new Chart(ctx,{
            type:'bar',
            data:{
                labels: svcLabels,
                datasets:[{
                    data: svcTotals,
                    backgroundColor: svcColors,
                    borderRadius: 14,
                    borderSkipped: false
                }]
            },
            options:{
                responsive:true,
                maintainAspectRatio:false,
                plugins:{ legend:{ display:false } },
                scales:{
                    x:{ grid:{ display:false }, ticks:{ color: muted, font:{ weight:900 }, maxRotation:0 } },
                    y:{ grid:{ color:'rgba(148,163,184,.14)' }, ticks:{ color: muted, font:{ weight:900 } } }
                }
            }
        });
    }

    function renderAll(){
        charts.forEach(c => c && c.destroy());
        charts = [];

        charts.push(makeLine('chartRevenue', revenue));
        charts.push(makeLine('chartAppointments', appointments));
        charts.push(makeLine('chartPatients', patients));
        charts.push(makeBars('chartTopServices'));
    }

    renderAll();

    // re-render charts after theme toggle
    document.getElementById('themeToggle')?.addEventListener('click', () => setTimeout(renderAll, 80));

    // toggle custom date inputs
    const rangeSelect = document.getElementById('rangeSelect');
    const customDates = document.getElementById('customDates');
    function syncCustom(){
        const isCustom = rangeSelect?.value === 'custom';
        if (customDates) customDates.style.display = isCustom ? 'flex' : 'none';
    }
    rangeSelect?.addEventListener('change', syncCustom);
    syncCustom();
})();
</script>
@endpush

@endsection

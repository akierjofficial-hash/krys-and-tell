@extends('layouts.admin')

@push('styles')
<style>
    /* ============================
       Admin Dashboard — Clean Fit (No Quick Actions)
       ============================ */

    .dash-wrap{
        border-radius: 22px;
        padding: 14px;
        background:
            radial-gradient(1200px 520px at 10% -10%, rgba(21,90,193,.20), transparent 60%),
            radial-gradient(900px 480px at 92% 12%, rgba(124,58,237,.12), transparent 55%),
            radial-gradient(900px 520px at 45% 120%, rgba(34,197,94,.10), transparent 55%),
            linear-gradient(180deg, rgba(255,255,255,.20), transparent);
        border: 1px solid rgba(148,163,184,.18);
        box-shadow: 0 20px 60px rgba(15,23,42,.10);
    }
    html[data-theme="dark"] .dash-wrap{
        background:
            radial-gradient(1200px 520px at 10% -10%, rgba(96,165,250,.14), transparent 60%),
            radial-gradient(900px 480px at 92% 12%, rgba(167,139,250,.10), transparent 55%),
            radial-gradient(900px 520px at 45% 120%, rgba(34,197,94,.08), transparent 55%),
            linear-gradient(180deg, rgba(2,6,23,.35), transparent);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 22px 72px rgba(0,0,0,.55);
    }

    /* HERO */
    .hero{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 12px;
        flex-wrap: wrap;
        padding: 14px 16px;
        border-radius: 20px;
        background:
            radial-gradient(900px 360px at 0% 0%, rgba(21,90,193,.16), transparent 55%),
            radial-gradient(900px 360px at 100% 30%, rgba(124,58,237,.12), transparent 55%),
            rgba(255,255,255,.70);
        border: 1px solid rgba(148,163,184,.18);
        backdrop-filter: blur(12px);
        box-shadow: 0 14px 40px rgba(15,23,42,.10);
    }
    html[data-theme="dark"] .hero{
        background:
            radial-gradient(900px 360px at 0% 0%, rgba(96,165,250,.12), transparent 55%),
            radial-gradient(900px 360px at 100% 30%, rgba(167,139,250,.10), transparent 55%),
            rgba(2,6,23,.45);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 20px 70px rgba(0,0,0,.55);
    }

    .hero-left{ min-width:0; }
    .hero-title{
        margin:0;
        font-weight: 950;
        letter-spacing: -0.9px;
        font-size: 28px;
        line-height: 1.08;
    }
    .hero-sub{
        margin-top: 8px;
        color: var(--muted);
        font-weight: 850;
        font-size: 13px;
    }

    .hero-right{ display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .chip{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 9px 12px;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(255,255,255,.62);
        font-weight: 950;
        font-size: 12px;
        color: var(--text);
        white-space:nowrap;
        box-shadow: 0 10px 22px rgba(15,23,42,.08);
    }
    html[data-theme="dark"] .chip{
        background: rgba(2,6,23,.45);
        border-color: rgba(148,163,184,.18);
        box-shadow: 0 16px 40px rgba(0,0,0,.45);
    }
    .chip i{ color: var(--muted); }

    /* KPI GRID */
    .kpi-grid{
        display:grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-top: 14px;
    }
    @media (max-width: 1100px){ .kpi-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 640px){ .kpi-grid{ grid-template-columns: 1fr; } }

    .kpi{
        border-radius: 20px;
        padding: 14px 14px;
        position:relative;
        overflow:hidden;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(255,255,255,.70);
        box-shadow: 0 14px 40px rgba(15,23,42,.10);
        transition: .18s ease;
        display:flex;
        justify-content:space-between;
        gap: 10px;
    }
    html[data-theme="dark"] .kpi{
        background: rgba(2,6,23,.45);
        box-shadow: 0 18px 70px rgba(0,0,0,.55);
    }
    .kpi:hover{ transform: translateY(-2px); }

    .kpi::before{
        content:"";
        position:absolute;
        inset:-2px;
        background: radial-gradient(600px 220px at 15% 0%, rgba(21,90,193,.16), transparent 55%);
        pointer-events:none;
    }
    .kpi.violet::before{ background: radial-gradient(600px 220px at 15% 0%, rgba(124,58,237,.14), transparent 55%); }
    .kpi.amber::before{ background: radial-gradient(600px 220px at 15% 0%, rgba(245,158,11,.12), transparent 55%); }
    .kpi.green::before{ background: radial-gradient(600px 220px at 15% 0%, rgba(34,197,94,.12), transparent 55%); }

    .kpi-left{ position:relative; z-index:1; min-width:0; }
    .kpi-k{ font-size: 12px; color: var(--muted); font-weight: 950; letter-spacing: .2px; }
    .kpi-v{ font-size: 26px; font-weight: 950; letter-spacing: -.8px; margin-top: 6px; }
    .kpi-sub{ margin-top: 10px; font-size: 12px; color: var(--muted); font-weight: 900; display:flex; align-items:center; gap:8px; }

    .kpi-ico{
        position:relative;
        z-index:1;
        width: 48px;
        height: 48px;
        border-radius: 18px;
        display:grid;
        place-items:center;
        color:#fff;
        box-shadow: 0 14px 26px rgba(15,23,42,.18);
        overflow:hidden;
        flex:0 0 auto;
    }
    .kpi-ico::before{
        content:"";
        position:absolute;
        inset:-40%;
        background: radial-gradient(circle at 20% 20%, rgba(255,255,255,.35), transparent 60%);
        transform: rotate(15deg);
        opacity:.95;
        pointer-events:none;
    }
    .ico-blue{ background: linear-gradient(135deg, #155AC1, #60a5fa); }
    .ico-violet{ background: linear-gradient(135deg, #7c3aed, #a78bfa); }
    .ico-amber{ background: linear-gradient(135deg, #f59e0b, #fbbf24); }
    .ico-green{ background: linear-gradient(135deg, #22c55e, #10b981); }

    /* CHARTS */
    .charts{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-top: 14px;
    }
    @media (max-width: 980px){ .charts{ grid-template-columns: 1fr; } }

    .panel{
        border-radius: 20px;
        padding: 14px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(255,255,255,.70);
        box-shadow: 0 14px 40px rgba(15,23,42,.10);
    }
    html[data-theme="dark"] .panel{
        background: rgba(2,6,23,.45);
        box-shadow: 0 18px 70px rgba(0,0,0,.55);
    }
    .panel-h{
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        margin-bottom: 10px;
    }
    .panel-h h3{
        margin:0;
        font-weight: 950;
        letter-spacing: -.2px;
        font-size: 14px;
        display:flex;
        align-items:center;
        gap:10px;
    }
    .panel-h i{ color: var(--muted); }

    .chart-wrap{
        height: 250px;
        border-radius: 18px;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(148,163,184,.05);
        padding: 10px;
        overflow:hidden;
    }
    html[data-theme="dark"] .chart-wrap{
        background: rgba(2,6,23,.35);
        border-color: rgba(148,163,184,.18);
    }

    /* TABLE */
    .table-card{
        margin-top: 14px;
        border-radius: 20px;
        overflow:hidden;
        border: 1px solid rgba(148,163,184,.18);
        background: rgba(255,255,255,.70);
        box-shadow: 0 14px 40px rgba(15,23,42,.10);
    }
    html[data-theme="dark"] .table-card{
        background: rgba(2,6,23,.45);
        box-shadow: 0 18px 70px rgba(0,0,0,.55);
    }
    .table-h{
        padding: 14px 14px;
        border-bottom: 1px solid rgba(148,163,184,.18);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:10px;
    }
    .table-h h3{
        margin:0;
        font-weight: 950;
        letter-spacing: -.2px;
        font-size: 14px;
        display:flex;
        align-items:center;
        gap:10px;
    }
    .table-h i{ color: var(--muted); }

    table.kt{
        width:100%;
        border-collapse: separate;
        border-spacing: 0;
        min-width: 980px;
    }
    .kt th,.kt td{
        padding: 12px 12px;
        border-bottom: 1px solid rgba(148,163,184,.18);
        vertical-align: middle;
        white-space: nowrap;
        font-weight: 900;
        color: var(--text);
    }
    .kt th{
        font-size: 12px;
        color: var(--muted);
        background: rgba(148,163,184,.06);
        position: sticky;
        top: 0;
        z-index: 1;
    }
    html[data-theme="dark"] .kt th{ background: rgba(2,6,23,.55); }

    .kt tbody tr:hover{ background: rgba(21,90,193,.06); }

    .muted{ color: var(--muted); font-weight: 850; }

    /* Procedure pill w/ service color */
    .pill-proc{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,.18);
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }
    .dot{ width:8px; height:8px; border-radius:999px; }

    .status{
        display:inline-flex;
        align-items:center;
        gap:8px;
        padding: 6px 10px;
        border-radius: 999px;
        border: 1px solid rgba(148,163,184,.18);
        font-size: 12px;
        font-weight: 950;
        white-space: nowrap;
    }
    .s-up{ background: rgba(59,130,246,.12); color: #2563eb; }
    .s-done{ background: rgba(34,197,94,.14); color: #16a34a; }
    .s-cancel{ background: rgba(239,68,68,.14); color: #dc2626; }
</style>
@endpush

@section('content')
@php
    $userName = auth()->user()->name ?? 'Admin';
    $range = $rangeLabel ?? 'This Month';
    $todayLabel = now()->format('D, M d • g:i a');
@endphp

<div class="dash-wrap">

    {{-- HERO --}}
    <div class="hero">
        <div class="hero-left">
            <h2 class="hero-title">Welcome back, {{ $userName }} 👋</h2>
            <div class="hero-sub">Here’s your clinic overview for <strong>{{ $range }}</strong>.</div>
        </div>
        <div class="hero-right">
            <span class="chip"><i class="fa fa-clock"></i> {{ $todayLabel }}</span>
            <span class="chip"><i class="fa fa-chart-line"></i> Admin Dashboard</span>
        </div>
    </div>

    {{-- KPI --}}
    <div class="kpi-grid">
        <div class="kpi">
            <div class="kpi-left">
                <div class="kpi-k">Appointments</div>
                <div class="kpi-v">{{ $appointmentsThisMonth ?? 0 }}</div>
                <div class="kpi-sub"><i class="fa fa-calendar-check"></i> {{ $range }}</div>
            </div>
            <div class="kpi-ico ico-blue"><i class="fa fa-calendar-check"></i></div>
        </div>

        <div class="kpi violet">
            <div class="kpi-left">
                <div class="kpi-k">New Patients</div>
                <div class="kpi-v">{{ $newPatientsThisMonth ?? 0 }}</div>
                <div class="kpi-sub"><i class="fa fa-user-plus"></i> {{ $range }}</div>
            </div>
            <div class="kpi-ico ico-violet"><i class="fa fa-users"></i></div>
        </div>

        <div class="kpi amber">
            <div class="kpi-left">
                <div class="kpi-k">Total Income</div>
                <div class="kpi-v">₱{{ number_format((float)($totalIncomeThisMonth ?? 0), 0) }}</div>
                <div class="kpi-sub"><i class="fa fa-coins"></i> {{ $range }}</div>
            </div>
            <div class="kpi-ico ico-amber"><i class="fa fa-money-bill-wave"></i></div>
        </div>

        <div class="kpi green">
            <div class="kpi-left">
                <div class="kpi-k">Procedures</div>
                <div class="kpi-v">{{ $proceduresThisMonth ?? 0 }}</div>
                <div class="kpi-sub"><i class="fa fa-tooth"></i> {{ $range }}</div>
            </div>
            <div class="kpi-ico ico-green"><i class="fa fa-tooth"></i></div>
        </div>
    </div>

    {{-- CHARTS --}}
    <div class="charts">
        <div class="panel">
            <div class="panel-h">
                <h3><i class="fa fa-user-clock"></i> Patients by Age</h3>
                <span class="chip" style="box-shadow:none;"><i class="fa fa-calendar"></i> {{ $range }}</span>
            </div>
            <div class="chart-wrap">
                <canvas id="ageChart"></canvas>
            </div>
        </div>

        <div class="panel">
            <div class="panel-h">
                <h3><i class="fa fa-tooth"></i> Procedures Breakdown</h3>
                <span class="chip" style="box-shadow:none;"><i class="fa fa-list"></i> {{ $range }}</span>
            </div>
            <div class="chart-wrap">
                <canvas id="procChart"></canvas>
            </div>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="table-card">
        <div class="table-h">
            <h3><i class="fa fa-clock"></i> Nearest Appointments</h3>
            <span class="chip" style="box-shadow:none;"><i class="fa fa-bell"></i> Upcoming</span>
        </div>

        <div class="table-responsive" style="padding: 0 14px 14px;">
            <table class="kt">
                <thead>
                <tr>
                    <th>Patient Name</th>
                    <th>Assigned Doctor</th>
                    <th>Procedure</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                @forelse(($nearestAppointments ?? []) as $a)
                    @php
                        $statusRaw = strtolower((string)($a->status ?? 'upcoming'));
                        $statusClass = match(true){
                            str_contains($statusRaw,'complete') || str_contains($statusRaw,'done') => 's-done',
                            str_contains($statusRaw,'cancel') => 's-cancel',
                            default => 's-up'
                        };
                        $dotColor = match($statusClass){
                            's-done' => '#22c55e',
                            's-cancel' => '#ef4444',
                            default => '#3b82f6',
                        };

                        $patientName = trim(($a->patient->first_name ?? '') . ' ' . ($a->patient->last_name ?? '')) ?: 'Patient';
                        $doctorName  = $a->dentist_name ?? ($a->assigned_doctor ?? '—');
                        $procedure   = $a->service->name ?? '—';
                        $dateLabel   = $a->appointment_date ? \Carbon\Carbon::parse($a->appointment_date)->format('m.d.Y') : '—';
                        $timeLabel   = $a->appointment_time ? \Carbon\Carbon::parse($a->appointment_time)->format('g:i a') : '—';

                        // ✅ Service color (if you added services.color)
                        $procColor = $a->service->color ?? '#64748b';
                    @endphp
                    <tr>
                        <td style="font-weight:950;">{{ $patientName }}</td>
                        <td class="muted">{{ $doctorName }}</td>
                        <td>
                            <span class="pill-proc" style="background: color-mix(in srgb, {{ $procColor }} 14%, transparent);">
                                <span class="dot" style="background: {{ $procColor }};"></span>
                                {{ $procedure }}
                            </span>
                        </td>
                        <td class="muted">{{ $dateLabel }}</td>
                        <td class="muted">{{ $timeLabel }}</td>
                        <td>
                            <span class="status {{ $statusClass }}">
                                <span class="dot" style="background: {{ $dotColor }};"></span>
                                {{ ucfirst($statusRaw) }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="color:var(--muted);font-weight:950;padding:18px;">
                            No upcoming appointments.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function(){
    const ageData = {{ \Illuminate\Support\Js::from($patientsByAge ?? []) }};
    const ageLabels = Object.keys(ageData);
    const ageValues = Object.values(ageData);

    const procRows = {{ \Illuminate\Support\Js::from($proceduresByService ?? []) }};
    const procLabels = procRows.map(r => r.name);
    const procValues = procRows.map(r => r.total);

    let ageChart, procChart;

    function cssVar(name){ return getComputedStyle(document.documentElement).getPropertyValue(name).trim(); }
    function isDark(){ return document.documentElement.getAttribute('data-theme') === 'dark'; }

    function hashColor(str){
        let h = 0;
        for (let i=0;i<str.length;i++) h = str.charCodeAt(i) + ((h<<5) - h);
        const hue = Math.abs(h) % 360;
        const sat = isDark() ? 66 : 72;
        const light = isDark() ? 52 : 56;
        return `hsl(${hue} ${sat}% ${light}%)`;
    }

    function render(){
        ageChart?.destroy();
        procChart?.destroy();

        const muted = cssVar('--muted') || (isDark() ? 'rgba(248,250,252,.72)' : 'rgba(100,116,139,.85)');
        const grid  = isDark() ? 'rgba(148,163,184,.16)' : 'rgba(148,163,184,.14)';

        // Age chart (bar)
        ageChart = new Chart(document.getElementById('ageChart'), {
            type: 'bar',
            data: {
                labels: ageLabels,
                datasets: [{
                    data: ageValues,
                    borderWidth: 0,
                    borderRadius: 14,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display:false }, ticks: { color: muted, font: { weight: 900 } } },
                    y: { beginAtZero: true, grid: { color: grid }, ticks: { color: muted, font: { weight: 900 } } }
                }
            }
        });

        // Apply nicer bar fill after chart exists (keeps it theme-safe)
        const ctx = document.getElementById('ageChart').getContext('2d');
        const grad = ctx.createLinearGradient(0, 0, 0, 260);
        grad.addColorStop(0, 'rgba(59,130,246,.85)');
        grad.addColorStop(1, 'rgba(59,130,246,.08)');
        ageChart.data.datasets[0].backgroundColor = grad;
        ageChart.update();

        // Procedures chart (doughnut) – keep colors (service color if provided, else stable)
        const colors = procRows.map(r => r.color || hashColor(r.name || 'Service'));

        procChart = new Chart(document.getElementById('procChart'), {
            type: 'doughnut',
            data: {
                labels: procLabels,
                datasets: [{
                    data: procValues,
                    backgroundColor: colors,
                    borderColor: isDark() ? 'rgba(255,255,255,.10)' : 'rgba(255,255,255,.70)',
                    borderWidth: 1,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '62%',
                plugins: {
                    legend: { position: 'right', labels: { color: muted, font: { weight: 900 } } }
                }
            }
        });
    }

    render();
    document.getElementById('themeToggle')?.addEventListener('click', () => setTimeout(render, 140));
})();
</script>
@endpush

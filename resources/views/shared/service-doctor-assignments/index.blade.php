@extends($layout)

@section('title', 'Treatment Doctor Assignments')

@push('styles')
<style>
    .kt-sda-shell {
        display: grid;
        gap: 20px;
    }

    .kt-sda-hero,
    .kt-sda-summary,
    .kt-sda-card {
        border: 1px solid var(--border, rgba(15, 23, 42, .10));
        border-radius: 24px;
        background: var(--surface, rgba(255, 255, 255, .9));
        box-shadow: var(--card-shadow, 0 14px 36px rgba(15, 23, 42, .10));
    }

    .kt-sda-hero {
        padding: 24px;
    }

    .kt-sda-hero__top {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        flex-wrap: wrap;
    }

    .kt-sda-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: var(--muted, rgba(15, 23, 42, .58));
        margin-bottom: 10px;
    }

    .kt-sda-eyebrow::before {
        content: '';
        width: 26px;
        height: 1px;
        background: currentColor;
        opacity: .4;
    }

    .kt-sda-title {
        margin: 0;
        font-size: clamp(1.45rem, 1.9vw, 2rem);
        font-weight: 900;
        letter-spacing: -.03em;
        color: var(--text, #0f172a);
    }

    .kt-sda-body {
        margin: 10px 0 0;
        max-width: 760px;
        color: var(--muted, rgba(15, 23, 42, .58));
        line-height: 1.7;
    }

    .kt-sda-hero__actions {
        min-width: 280px;
        display: grid;
        gap: 12px;
    }

    .kt-sda-search {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .kt-sda-search .form-control,
    .kt-sda-search .btn,
    .kt-sda-toggle,
    .kt-sda-card .form-check-input,
    .kt-sda-card .btn {
        border-radius: 14px;
    }

    .kt-sda-guides {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 18px;
    }

    .kt-sda-summary {
        padding: 16px 18px;
    }

    .kt-sda-summary strong {
        display: block;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--muted, rgba(15, 23, 42, .58));
        margin-bottom: 6px;
    }

    .kt-sda-summary span {
        display: block;
        font-size: 14px;
        font-weight: 800;
        color: var(--text, #0f172a);
        line-height: 1.5;
    }

    .kt-sda-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .kt-sda-card {
        padding: 20px;
    }

    .kt-sda-card__head {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        align-items: flex-start;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .kt-sda-card__name {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 900;
        color: var(--text, #0f172a);
    }

    .kt-sda-card__desc {
        margin: 6px 0 0;
        color: var(--muted, rgba(15, 23, 42, .58));
        line-height: 1.6;
        font-size: 13px;
    }

    .kt-sda-badges {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: flex-end;
    }

    .kt-sda-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .kt-sda-badge--solid {
        background: rgba(37, 99, 235, .12);
        border: 1px solid rgba(37, 99, 235, .18);
        color: #1d4ed8;
    }

    .kt-sda-badge--ghost {
        background: rgba(148, 163, 184, .12);
        border: 1px solid rgba(148, 163, 184, .2);
        color: var(--muted, rgba(15, 23, 42, .58));
    }

    .kt-sda-badge--success {
        background: rgba(34, 197, 94, .12);
        border: 1px solid rgba(34, 197, 94, .18);
        color: #15803d;
    }

    .kt-sda-card__insight {
        padding: 14px 16px;
        border-radius: 16px;
        background: rgba(255, 255, 255, .55);
        border: 1px solid var(--border, rgba(15, 23, 42, .10));
        color: var(--muted, rgba(15, 23, 42, .58));
        line-height: 1.6;
        font-size: 13px;
        margin-bottom: 16px;
    }

    .kt-sda-card__section-title {
        margin: 0 0 10px;
        font-size: 12px;
        font-weight: 900;
        letter-spacing: .14em;
        text-transform: uppercase;
        color: var(--muted, rgba(15, 23, 42, .58));
    }

    .kt-sda-doctors {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .kt-sda-doctor {
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 14px;
        border-radius: 16px;
        border: 1px solid var(--border, rgba(15, 23, 42, .10));
        background: rgba(255, 255, 255, .55);
    }

    .kt-sda-doctor__meta {
        min-width: 0;
    }

    .kt-sda-doctor__name {
        font-weight: 800;
        color: var(--text, #0f172a);
        line-height: 1.4;
    }

    .kt-sda-doctor__sub {
        margin-top: 3px;
        font-size: 12px;
        color: var(--muted, rgba(15, 23, 42, .58));
    }

    .kt-sda-doctor__state {
        margin-top: 8px;
        display: inline-flex;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 10px;
        font-weight: 900;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .kt-sda-doctor__state--active {
        background: rgba(34, 197, 94, .12);
        color: #15803d;
    }

    .kt-sda-doctor__state--inactive {
        background: rgba(148, 163, 184, .16);
        color: var(--muted, rgba(15, 23, 42, .58));
    }

    .kt-sda-card__actions {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .kt-sda-card__hint {
        color: var(--muted, rgba(15, 23, 42, .58));
        font-size: 12px;
    }

    .kt-sda-card__hint strong {
        color: var(--text, #0f172a);
    }

    .kt-sda-empty {
        border: 1px dashed var(--border, rgba(15, 23, 42, .10));
        border-radius: 24px;
        background: rgba(255, 255, 255, .45);
        padding: 40px 24px;
        text-align: center;
        color: var(--muted, rgba(15, 23, 42, .58));
    }

    @media (max-width: 1199.98px) {
        .kt-sda-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767.98px) {
        .kt-sda-guides,
        .kt-sda-doctors {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

@section('content')
@php
    $servicesCount = $services->count();
    $restrictedCount = $services->where('restrict_to_assigned_doctors', true)->count();
    $singleDoctorCount = $services->filter(function ($service) {
        return (bool) ($service->restrict_to_assigned_doctors ?? false) && $service->assignedDoctors->count() === 1;
    })->count();
@endphp

<div class="container-fluid">
    <div class="kt-sda-shell">
        <section class="kt-sda-hero">
            <div class="kt-sda-hero__top">
                <div>
                    <div class="kt-sda-eyebrow">{{ $portalLabel }} Booking Rules</div>
                    <h1 class="kt-sda-title">Treatment Doctor Assignments</h1>
                    <p class="kt-sda-body">
                        Control which dentists can handle each treatment. Turn restriction on when a service should only be booked with selected doctors.
                        If only one doctor is assigned, public booking will auto-select that doctor for the patient.
                    </p>
                </div>

                <div class="kt-sda-hero__actions">
                    <form method="GET" action="{{ route($routePrefix . '.service_doctor_assignments.index') }}" class="kt-sda-search">
                        <input
                            type="search"
                            name="q"
                            value="{{ $search }}"
                            class="form-control"
                            placeholder="Search treatments"
                        >
                        <button type="submit" class="btn btn-primary">Search</button>
                        @if($search !== '')
                            <a href="{{ route($routePrefix . '.service_doctor_assignments.index') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </form>
                </div>
            </div>

            <div class="kt-sda-guides">
                <article class="kt-sda-summary">
                    <strong>Total Treatments</strong>
                    <span>{{ $servicesCount }} service{{ $servicesCount === 1 ? '' : 's' }}</span>
                </article>
                <article class="kt-sda-summary">
                    <strong>Restricted Treatments</strong>
                    <span>{{ $restrictedCount }} treatment{{ $restrictedCount === 1 ? '' : 's' }} now use assigned doctors only</span>
                </article>
                <article class="kt-sda-summary">
                    <strong>Auto-Assigned</strong>
                    <span>{{ $singleDoctorCount }} treatment{{ $singleDoctorCount === 1 ? '' : 's' }} will auto-pick one dentist on public booking</span>
                </article>
            </div>
        </section>

        @if(session('success'))
            <div class="alert alert-success mb-0">{{ session('success') }}</div>
        @endif

        @if($services->isEmpty())
            <section class="kt-sda-empty">
                <h2 class="h5 fw-bold mb-2">No treatments found</h2>
                <p class="mb-0">Try a different search or add treatments first before assigning doctors.</p>
            </section>
        @else
            <section class="kt-sda-grid">
                @foreach($services as $service)
                    @php
                        $assignedDoctorIds = $service->assignedDoctors->pluck('id')->map(fn ($id) => (int) $id)->all();
                        $assignedCount = count($assignedDoctorIds);
                        $isWalkIn = ($service->duration_minutes === null || $service->duration_minutes === '' || (is_numeric($service->duration_minutes) && (int) $service->duration_minutes > 0 && (int) $service->duration_minutes <= 5));
                        $showError = (string) $assignmentServiceId === (string) $service->id;
                    @endphp

                    <form method="POST"
                          action="{{ route($routePrefix . '.service_doctor_assignments.update', $service) }}"
                          class="kt-sda-card"
                          data-sda-card>
                        @csrf
                        @method('PUT')

                        <div class="kt-sda-card__head">
                            <div>
                                <h2 class="kt-sda-card__name">{{ $service->name }}</h2>
                                <p class="kt-sda-card__desc">
                                    {{ $service->description ?: 'No treatment description added yet.' }}
                                </p>
                            </div>

                            <div class="kt-sda-badges">
                                <span class="kt-sda-badge {{ $service->restrict_to_assigned_doctors ? 'kt-sda-badge--solid' : 'kt-sda-badge--ghost' }}">
                                    {{ $service->restrict_to_assigned_doctors ? 'Restricted' : 'Open to all active doctors' }}
                                </span>
                                <span class="kt-sda-badge {{ $assignedCount === 1 ? 'kt-sda-badge--success' : 'kt-sda-badge--ghost' }}">
                                    {{ $assignedCount }} assigned
                                </span>
                                <span class="kt-sda-badge kt-sda-badge--ghost">
                                    {{ $isWalkIn ? 'Walk-in' : 'Scheduled' }}
                                </span>
                            </div>
                        </div>

                        <div class="kt-sda-card__insight">
                            @if($service->restrict_to_assigned_doctors)
                                @if($assignedCount === 1)
                                    Public booking will automatically assign <strong>{{ $service->assignedDoctors->first()->name ?? 'the selected doctor' }}</strong> for this treatment.
                                @elseif($assignedCount > 1)
                                    Public booking will only show these assigned doctors for this treatment.
                                @else
                                    This treatment is restricted, but no doctor is assigned yet. Public booking will not offer a dentist until you assign one.
                                @endif
                            @else
                                This treatment still allows any active dentist in public booking. You can preselect doctors below and turn restriction on whenever you are ready.
                            @endif
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input
                                class="form-check-input kt-sda-toggle"
                                type="checkbox"
                                role="switch"
                                id="restrict_{{ $service->id }}"
                                name="restrict_to_assigned_doctors"
                                value="1"
                                @checked((bool) ($service->restrict_to_assigned_doctors ?? false))
                            >
                            <label class="form-check-label fw-semibold" for="restrict_{{ $service->id }}">
                                Use only assigned doctors for this treatment
                            </label>
                        </div>

                        @if($showError && $errors->has('doctor_ids'))
                            <div class="alert alert-danger py-2 mb-3">{{ $errors->first('doctor_ids') }}</div>
                        @endif

                        <div>
                            <h3 class="kt-sda-card__section-title">Assigned Doctors</h3>
                            <div class="kt-sda-doctors">
                                @foreach($doctors as $doctor)
                                    @php
                                        $isActive = (bool) ($doctor->is_active ?? true);
                                        $isChecked = in_array((int) $doctor->id, $assignedDoctorIds, true);
                                    @endphp
                                    <label class="kt-sda-doctor">
                                        <input
                                            class="form-check-input mt-1"
                                            type="checkbox"
                                            name="doctor_ids[]"
                                            value="{{ $doctor->id }}"
                                            @checked($isChecked)
                                        >
                                        <div class="kt-sda-doctor__meta">
                                            <div class="kt-sda-doctor__name">{{ $doctor->name }}</div>
                                            <div class="kt-sda-doctor__sub">
                                                {{ $doctor->specialty ?: 'General dentistry' }}
                                            </div>
                                            <span class="kt-sda-doctor__state {{ $isActive ? 'kt-sda-doctor__state--active' : 'kt-sda-doctor__state--inactive' }}">
                                                {{ $isActive ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="kt-sda-card__actions">
                            <div class="kt-sda-card__hint">
                                <strong>Tip:</strong> keep restriction off while you are still configuring a treatment, then turn it on once the correct doctors are selected.
                            </div>
                            <button type="submit" class="btn btn-primary px-4">Save Assignments</button>
                        </div>
                    </form>
                @endforeach
            </section>
        @endif
    </div>
</div>
@endsection

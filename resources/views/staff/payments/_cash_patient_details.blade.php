@php
    $total = (float) $payments->sum('amount');

    // Group by visit (so it’s clean)
    $byVisit = $payments->groupBy(fn($p) => $p->visit_id ?? 0);
@endphp

<div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
    <div>
        <div style="font-weight:950; color:var(--kt-text);">
            {{ trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: 'Patient' }}
        </div>
        <div class="muted" style="font-size:12px;">
            {{ $payments->count() }} payment(s) • Total: <span class="money" style="font-weight:950;">₱{{ number_format($total, 2) }}</span>
        </div>
    </div>

    <a href="{{ route('staff.patients.show', [$patient->id, 'tab' => 'tab-payments']) }}"
       class="pill pill-view"
       style="text-decoration:none;">
        <i class="fa fa-user"></i> <span>Open Patient</span>
    </a>
</div>

<div style="height:10px;"></div>

@if($payments->isEmpty())
    <div class="muted">No cash payments found.</div>
@else
    @foreach($byVisit as $visitId => $rows)
        @php
            $visit = $rows->first()?->visit;
            $visitDate = $visit?->visit_date
                ? \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y')
                : '—';

            $visitTotal = (float) $rows->sum('amount');

            $procLabels = collect();
            if ($visit && $visit->relationLoaded('procedures')) {
                $procLabels = $visit->procedures->map(function($p){
                    $name = $p->service?->name ?? '—';
                    $tooth = $p->tooth_number ? ('#'.$p->tooth_number) : null;
                    $surface = $p->surface ? ($p->surface) : null;
                    return trim($name.' '.trim(($tooth ?? '').' '.($surface ?? '')));
                })->filter()->values();
            }

            $shown = $procLabels->take(3);
            $more = max(0, $procLabels->count() - $shown->count());
        @endphp

        <div style="padding:12px 12px; border:1px solid var(--kt-border); border-radius:14px; background:var(--kt-surface); margin-bottom:12px;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
                <div>
                    <div style="font-weight:950; color:var(--kt-text);">
                        Visit #{{ $visitId ?: '—' }}
                        <span class="muted" style="font-weight:900;">• {{ $visitDate }}</span>
                    </div>

                    <div style="margin-top:6px;">
                        @if($shown->count())
                            <div class="tags">
                                @foreach($shown as $t)
                                    <span class="tag" title="{{ $t }}">{{ $t }}</span>
                                @endforeach
                                @if($more > 0)
                                    <span class="tag more">+{{ $more }} more</span>
                                @endif
                            </div>
                        @else
                            <span class="muted">No procedures</span>
                        @endif
                    </div>
                </div>

                <div class="muted" style="font-size:12px; text-align:right;">
                    Visit Total Paid<br>
                    <span class="money" style="font-weight:950; font-size:14px; color:var(--kt-text);">
                        ₱{{ number_format($visitTotal, 2) }}
                    </span>
                </div>
            </div>

            <div style="height:10px;"></div>

            <div style="overflow:auto; border-radius:12px; border:1px solid var(--soft);">
                <table style="width:100%; table-layout:auto; border-collapse:separate; border-spacing:0;">
                    <thead>
                        <tr>
                            <th style="padding:10px 12px; font-size:12px; color:var(--kt-muted); border-bottom:1px solid var(--soft); background:rgba(148,163,184,.10);">Date</th>
                            <th style="padding:10px 12px; font-size:12px; color:var(--kt-muted); border-bottom:1px solid var(--soft); background:rgba(148,163,184,.10);">Method</th>
                            <th style="padding:10px 12px; font-size:12px; color:var(--kt-muted); border-bottom:1px solid var(--soft); background:rgba(148,163,184,.10); text-align:right;">Amount</th>
                            <th style="padding:10px 12px; font-size:12px; color:var(--kt-muted); border-bottom:1px solid var(--soft); background:rgba(148,163,184,.10); text-align:right;">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($rows as $p)
                            @php
                                $d = $p->payment_date
                                    ? \Carbon\Carbon::parse($p->payment_date)->format('M d, Y')
                                    : '—';
                            @endphp

                            <tr>
                                <td style="padding:10px 12px; border-bottom:1px solid var(--soft); white-space:nowrap;">
                                    {{ $d }}
                                </td>
                                <td style="padding:10px 12px; border-bottom:1px solid var(--soft); white-space:nowrap;">
                                    <span class="muted">{{ $p->method ?? '—' }}</span>
                                </td>
                                <td style="padding:10px 12px; border-bottom:1px solid var(--soft); text-align:right; white-space:nowrap;">
                                    <span class="money" style="font-weight:950;">₱{{ number_format((float)$p->amount, 2) }}</span>
                                </td>
                                <td style="padding:10px 12px; border-bottom:1px solid var(--soft); text-align:right; white-space:nowrap;">
                                    <div class="action-pills" style="justify-content:flex-end;">
                                        <a class="pill pill-view" href="{{ route('staff.payments.show', $p) }}" title="View">
                                            <i class="fa fa-eye"></i> <span>View</span>
                                        </a>

                                        <a class="pill pill-edit" href="{{ route('staff.payments.edit', $p) }}" title="Edit">
                                            <i class="fa fa-pen"></i> <span>Edit</span>
                                        </a>

                                        <form action="{{ route('staff.payments.destroy', $p) }}" method="POST" style="display:inline;"
                                              onsubmit="return confirm('Delete this payment? This can’t be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="pill pill-del" type="submit" title="Delete">
                                                <i class="fa fa-trash"></i> <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @endforeach
@endif

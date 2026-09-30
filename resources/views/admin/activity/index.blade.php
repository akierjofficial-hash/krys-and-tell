@extends(request()->header('X-KT-Live-Search') === '1' ? 'layouts.live-search' : 'layouts.admin')
@section('title', 'Activity Log')
@section('content')
<x-admin.page-header title="Clinic activity log" description="Review administrative and operational changes across the clinic." />
<form class="cardx p-3 mb-3" method="GET" data-live-search data-live-extra="[data-live-extra]"><div class="row g-2">
 <div class="col-lg-3"><label class="form-label fw-bold">Search</label><input class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Event, reason, description"></div>
 <div class="col-lg-2"><label class="form-label fw-bold">Event</label><select class="form-select" name="event"><option value="">All events</option>@foreach($events as $event)<option value="{{ $event }}" @selected(($filters['event']??'')===$event)>{{ str($event)->headline() }}</option>@endforeach</select></div>
 <div class="col-lg-2"><label class="form-label fw-bold">Actor</label><select class="form-select" name="actor"><option value="">All actors</option>@foreach($actors as $actor)<option value="{{ $actor->id }}" @selected(($filters['actor']??null)==$actor->id)>{{ $actor->name }}</option>@endforeach</select></div>
 <div class="col-lg-2"><label class="form-label fw-bold">Target</label><select class="form-select" name="target_type"><option value="">All targets</option>@foreach($targetTypes as $target)<option value="{{ $target }}" @selected(($filters['target_type']??'')===$target)>{{ class_basename($target) }}</option>@endforeach</select></div>
 <div class="col-lg-2"><label class="form-label fw-bold">Result</label><select class="form-select" name="result"><option value="">Any result</option><option value="success" @selected(($filters['result']??'')==='success')>Success</option><option value="failure" @selected(($filters['result']??'')==='failure')>Failure</option></select></div>
 <div class="col-lg-2"><label class="form-label fw-bold">From</label><input type="date" class="form-control" name="from" value="{{ $filters['from'] ?? '' }}"></div>
 <div class="col-lg-2"><label class="form-label fw-bold">To</label><input type="date" class="form-control" name="to" value="{{ $filters['to'] ?? '' }}"></div>
 <div class="col-lg-1 d-flex align-items-end"><button class="btn btn-primary w-100" aria-label="Apply filters"><i class="fa fa-filter"></i></button></div>
 <div class="col-lg-2 d-flex align-items-end"><a class="btn btn-outline-secondary w-100" href="{{ route('admin.activity.index') }}">Reset filters</a></div>
 <div class="col-12"><label class="form-check-label"><input class="form-check-input me-2" type="checkbox" name="sensitive" value="1" @checked(request()->boolean('sensitive'))>Sensitive actions only</label></div>
</div></form>
<div class="cardx overflow-hidden" data-live-results><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>Time</th><th>Actor</th><th>Event</th><th>Target</th><th>Reason / details</th></tr></thead><tbody>
@forelse($logs as $log)<tr><td class="text-nowrap">{{ $log->created_at?->timezone(config('app.timezone'))->format('M j, Y g:i A') }}</td><td>{{ $log->user?->name ?: 'Deleted account' }}</td><td><span class="badge {{ $log->is_sensitive?'text-bg-warning':'text-bg-light' }}">{{ str($log->event)->headline() }}</span></td><td>{{ $log->target_type ? class_basename($log->target_type).' #'.$log->target_id : '—' }}</td><td><div class="fw-bold">{{ $log->description ?: '—' }}</div>@if($log->reason)<div class="small text-muted">Reason: {{ $log->reason }}</div>@endif</td></tr>@empty<tr><td colspan="5" class="text-center text-muted p-4">No activity matches these filters.</td></tr>@endforelse
</tbody></table></div></div><div class="mt-3" data-live-extra>{{ $logs->links() }}</div>
@endsection

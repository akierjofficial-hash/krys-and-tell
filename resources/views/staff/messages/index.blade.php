@extends('layouts.staff')

@section('content')
<div class="d-flex align-items-end justify-content-between flex-wrap gap-2 mb-3">
    <div>
        <h2 class="m-0" style="font-weight:900;">Messages</h2>
        <div class="text-muted">Contact form inbox</div>
    </div>

    <div class="d-flex gap-2 align-items-center">
        <span class="badge rounded-pill text-bg-primary">
            {{ $unreadCount }} unread
        </span>
    </div>
</div>

<div class="card p-0">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width:110px;">Status</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Message</th>
                    <th style="width:160px;">Received</th>
                    <th style="width:140px;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $m)
                    <tr class="{{ $m->read_at ? '' : 'fw-semibold' }}">
                        <td>
                            @if($m->read_at)
                                <span class="badge rounded-pill text-bg-light">Read</span>
                            @else
                                <span class="badge rounded-pill text-bg-warning">Unread</span>
                            @endif
                        </td>
                        <td>{{ $m->name }}</td>
                        <td>{{ $m->email }}</td>
                        <td style="max-width:420px;">
                            <div class="text-truncate" style="max-width:420px;">
                                {{ $m->message }}
                            </div>
                        </td>
                        <td class="text-muted">
                            {{ optional($m->created_at)->format('M d, Y h:i A') }}
                        </td>
                        <td>
                            <a class="btn btn-sm btn-primary"
                               href="{{ route('staff.messages.show', $m) }}">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No messages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $messages->links() }}
</div>
@endsection

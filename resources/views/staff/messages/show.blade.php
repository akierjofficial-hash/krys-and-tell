@extends('layouts.staff')

@section('content')

<style>
    :root{
        --card-shadow: var(--kt-shadow, 0 10px 25px rgba(15, 23, 42, .06));
        --card-border: 1px solid var(--kt-border, rgba(15, 23, 42, .10));
        --text: var(--kt-text, #0f172a);
        --muted: var(--kt-muted, rgba(15, 23, 42, .55));
        --soft: rgba(148, 163, 184, .14);
        --radius: 16px;
    }
    html[data-theme="dark"]{ --soft: rgba(148, 163, 184, .16); }

    .wrap{ max-width: 1060px; }

    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 12px;
        margin-bottom: 14px;
        flex-wrap: wrap;
    }
    .page-title{
        margin:0;
        font-size: 26px;
        font-weight: 950;
        letter-spacing: -.3px;
        color: var(--text);
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: var(--muted);
    }

    .chip{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 7px 10px;
        border-radius: 999px;
        font-weight: 950;
        font-size: 12px;
        border: 1px solid transparent;
        white-space: nowrap;
        line-height: 1;
    }
    .chip-unread{ background: rgba(245,158,11,.10); color:#f59e0b; border-color: rgba(245,158,11,.35); }
    .chip-read{ background: rgba(148,163,184,.10); color: rgba(148,163,184,.95); border-color: rgba(148,163,184,.22); }

    .btnx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 10px 12px;
        border-radius: 12px;
        font-weight: 950;
        font-size: 13px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2, rgba(148,163,184,.10));
        color: var(--text);
        text-decoration: none;
        transition: .12s ease;
        white-space: nowrap;
    }
    .btnx:hover{ transform: translateY(-1px); }

    .btnx-primary{
        border: none;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        color:#fff;
        box-shadow: 0 10px 18px rgba(13,110,253,.20);
    }
    .btnx-primary:hover{ color:#fff; box-shadow: 0 14px 24px rgba(13,110,253,.26); }

    .btnx-danger{
        border: 1px solid rgba(239,68,68,.35);
        background: rgba(239,68,68,.10);
        color:#ef4444;
    }

    .cardx{
        background: var(--kt-surface, #fff);
        border: var(--card-border);
        border-radius: var(--radius);
        box-shadow: var(--card-shadow);
        overflow: hidden;
        color: var(--text);
    }
    .cardx-head{
        padding: 14px 16px;
        border-bottom: 1px solid var(--soft);
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap: 10px;
        flex-wrap: wrap;
        background: linear-gradient(180deg, rgba(148,163,184,.08), transparent);
    }
    html[data-theme="dark"] .cardx-head{
        background: linear-gradient(180deg, rgba(2,6,23,.45), rgba(17,24,39,0));
    }
    .cardx-title{
        margin:0;
        font-weight: 950;
        font-size: 14px;
        display:flex;
        align-items:center;
        gap: 10px;
    }
    .cardx-body{ padding: 16px; }

    .grid{
        display:grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    @media (max-width: 768px){
        .grid{ grid-template-columns: 1fr; }
    }

    .info{
        padding: 12px;
        border-radius: 14px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2, rgba(148,163,184,.10));
    }
    .label{
        font-size: 11px;
        font-weight: 950;
        letter-spacing: .35px;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 6px;
    }
    .value{
        font-weight: 900;
        color: var(--text);
        word-break: break-word;
    }

    .msg{
        margin-top: 10px;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2, rgba(148,163,184,.10));
        white-space: pre-wrap;
        line-height: 1.5;
        font-weight: 650;
        color: var(--text);
    }

    .meta{
        margin-top: 14px;
        padding-top: 12px;
        border-top: 1px dashed var(--soft);
        color: var(--muted);
        font-size: 12px;
    }
    .meta code{
        font-size: 11px;
        padding: 2px 6px;
        border-radius: 8px;
        border: 1px solid var(--kt-border);
        background: var(--kt-surface-2, rgba(148,163,184,.10));
        color: var(--text);
        word-break: break-word;
    }
</style>

@php
    $isUnread = !$message->read_at;
@endphp

<div class="wrap">
    <div class="page-head">
        <div>
            <h2 class="page-title">Message</h2>
            <div class="subtitle">
                Received {{ optional($message->created_at)->format('M d, Y h:i A') }}
                @if($message->read_at) • Read {{ optional($message->read_at)->diffForHumans() }} @endif
                &nbsp;•&nbsp;
                @if($isUnread)
                    <span class="chip chip-unread">Unread</span>
                @else
                    <span class="chip chip-read">Read</span>
                @endif
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('staff.messages.index') }}" class="btnx">
                <i class="fa-solid fa-arrow-left"></i> Back
            </a>

            {{-- Quick reply (opens mail client) --}}
            <a class="btnx btnx-primary"
               href="mailto:{{ $message->email }}?subject={{ rawurlencode('Re: Message to Krys & Tell') }}">
                <i class="fa-solid fa-reply"></i> Reply
            </a>

            @if(!$message->read_at)
                <form method="POST" action="{{ route('staff.messages.read', $message) }}">
                    @csrf
                    <button class="btnx" type="submit">
                        <i class="fa-solid fa-check"></i> Mark as read
                    </button>
                </form>
            @endif

            {{-- ✅ Delete with your KT confirm modal --}}
            <form id="deleteMsgForm" method="POST" action="{{ route('staff.messages.destroy', $message) }}">
                @csrf
                @method('DELETE')

                <button type="button"
                        class="btnx btnx-danger"
                        data-confirm="Delete this message? This cannot be undone."
                        data-confirm-title="Delete message"
                        data-confirm-yes="Delete"
                        data-confirm-form="#deleteMsgForm">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </form>
        </div>
    </div>

    <div class="cardx">
        <div class="cardx-head">
            <div class="cardx-title">
                <i class="fa-solid fa-envelope-open-text"></i> Message Details
            </div>
        </div>

        <div class="cardx-body">
            <div class="grid">
                <div class="info">
                    <div class="label">Name</div>
                    <div class="value">{{ $message->name }}</div>
                </div>

                <div class="info">
                    <div class="label">Email</div>
                    <div class="value">{{ $message->email }}</div>
                </div>
            </div>

            <div class="info" style="margin-top:12px;">
                <div class="label">Message</div>
                <div class="msg">{{ $message->message }}</div>
            </div>

            <div class="meta">
                <div><strong>IP:</strong> <code>{{ $message->ip_address ?? '—' }}</code></div>
                <div style="margin-top:6px;">
                    <strong>UA:</strong> <code>{{ $message->user_agent ?? '—' }}</code>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

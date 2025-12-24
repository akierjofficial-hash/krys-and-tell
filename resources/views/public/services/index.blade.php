@extends('layouts.public')
@section('title', 'Services — Krys & Tell')

@section('content')
<section class="section section-soft">
    <div class="container">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
            <div>
                <h1 class="sec-title mb-2">Services</h1>
                <div class="sec-sub">These are the same services your staff manages in the system.</div>
            </div>
            <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/') }}">
                <i class="fa-solid fa-house me-1"></i> Back Home
            </a>
        </div>

        <div class="row g-3 mt-3">
            @forelse($services as $s)
                <div class="col-md-4">
                    <div class="card card-soft p-4 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-2">
                            <div class="fw-black" style="font-weight:950; font-size:1.05rem;">
                                {{ $s->name }}
                            </div>
                            @if(!empty($s->base_price))
                                <div class="pill" style="border-color:rgba(15,23,42,.12); background:#fff; color:var(--text);">
                                    ₱{{ number_format((float)$s->base_price, 0) }}
                                </div>
                            @endif
                        </div>

                        @if(!empty($s->description))
                            <div class="text-muted-2 mt-2">{{ $s->description }}</div>
                        @endif

                        <div class="d-flex gap-2 mt-4">
                            <a class="btn kt-btn kt-btn-outline" href="{{ route('public.services.show', $s->id) }}">
                                View
                            </a>
                            <a class="btn kt-btn kt-btn-primary text-white" href="{{ route('public.booking.create', $s->id) }}">
                                Book
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="card card-soft p-4">
                        No services yet. (Add services from Staff side.)
                    </div>
                </div>
            @endforelse
        </div>
    </div>
</section>
@endsection

@extends('layouts.public')
@section('title', 'My Profile')

@section('content')
<section class="section section-soft">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6">
                <div class="card-soft p-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="kt-avatar" style="width:56px;height:56px;font-size:1.2rem;">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div>
                            <div style="font-weight:950;font-size:1.25rem;line-height:1.1;">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-muted-2" style="font-weight:650;">
                                {{ auth()->user()->email }}
                            </div>
                        </div>
                    </div>

                    <hr style="border-color: rgba(17,17,17,.10);">

                    <div class="d-grid gap-2">
                        <a class="btn kt-btn kt-btn-primary text-white" href="{{ url('/services') }}">
                            <i class="fa-solid fa-calendar-check me-1"></i> Book an Appointment
                        </a>

                        @if(in_array(auth()->user()->role ?? '', ['admin','staff']))
                            <a class="btn kt-btn kt-btn-outline" href="{{ route('portal') }}">
                                <i class="fa-solid fa-gauge me-1"></i> Go to Portal
                            </a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="btn kt-btn kt-btn-outline" type="submit">
                                <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>
@endsection

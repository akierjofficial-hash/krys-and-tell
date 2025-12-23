@extends('layouts.app')

@section('content')

<style>
    :root{
        --card-shadow: 0 10px 25px rgba(15, 23, 42, .06);
        --card-border: 1px solid rgba(15, 23, 42, .08);
    }

    /* Header */
    .page-head{
        display:flex;
        align-items:flex-end;
        justify-content:space-between;
        gap: 14px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .page-title{
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.3px;
        margin: 0;
        color: #0f172a;
    }
    .subtitle{
        margin: 4px 0 0 0;
        font-size: 13px;
        color: rgba(15, 23, 42, .55);
    }

    .btn-ghostx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        text-decoration: none;
        border: 1px solid rgba(15, 23, 42, .12);
        color: rgba(15, 23, 42, .75);
        background: rgba(255,255,255,.85);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-ghostx:hover{
        background: rgba(15, 23, 42, .04);
        color: rgba(15, 23, 42, .85);
    }

    .btn-primaryx{
        display:inline-flex;
        align-items:center;
        gap: 8px;
        padding: 11px 14px;
        border-radius: 12px;
        font-weight: 900;
        font-size: 14px;
        border: none;
        color: #fff;
        text-decoration: none;
        background: linear-gradient(135deg, #0d6efd, #1e90ff);
        box-shadow: 0 10px 18px rgba(13, 110, 253, .20);
        transition: .15s ease;
        white-space: nowrap;
    }
    .btn-primaryx:hover{
        transform: translateY(-1px);
        box-shadow: 0 14px 24px rgba(13, 110, 253, .26);
        color:#fff;
    }

    /* Error box */
    .error-box{
        background: rgba(239, 68, 68, .10);
        border: 1px solid rgba(239, 68, 68, .22);
        color: #b91c1c;
        border-radius: 14px;
        padding: 14px 16px;
        margin-bottom: 14px;
    }
    .error-box .title{
        font-weight: 900;
        margin-bottom: 6px;
    }
    .error-box ul{
        margin: 0;
        padding-left: 18px;
        font-size: 13px;
    }

    /* Card */
    .card-shell{
        background: rgba(255,255,255,.92);
        border: var(--card-border);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        width: 100%;
    }
    .card-head{
        padding: 16px 18px;
        border-bottom: 1px solid rgba(15, 23, 42, .06);
        display:flex;
        align-items:center;
        justify-content:space-between;
        flex-wrap: wrap;
        gap: 10px;
    }
    .card-head .hint{
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }
    .card-bodyx{ padding: 18px; }

    /* Inputs */
    .form-labelx{
        font-weight: 900;
        font-size: 13px;
        color: rgba(15, 23, 42, .75);
        margin-bottom: 6px;
    }

    .inputx, .selectx, .textareax{
        width: 100%;
        border: 1px solid rgba(15, 23, 42, .12);
        padding: 11px 12px;
        border-radius: 12px;
        font-size: 14px;
        color: #0f172a;
        background: rgba(255,255,255,.95);
        outline: none;
        transition: .15s ease;
        box-shadow: 0 6px 16px rgba(15, 23, 42, .04);
    }

    .inputx:focus, .selectx:focus, .textareax:focus{
        border-color: rgba(13,110,253,.55);
        box-shadow: 0 0 0 4px rgba(13,110,253,.12);
        background: #fff;
    }

    .helper{
        margin-top: 6px;
        font-size: 12px;
        color: rgba(15, 23, 42, .55);
    }

    .form-max{ max-width: 1100px; }
</style>

<div class="page-head form-max">
    <div>
        <h2 class="page-title">Edit Payment</h2>
        <p class="subtitle">Update the details of this payment below.</p>
    </div>

    <a href="{{ route('payments.index') }}" class="btn-ghostx">
        <i class="fa fa-arrow-left"></i> Back to Payments
    </a>
</div>

@if ($errors->any())
    <div class="error-box form-max">
        <div class="title"><i class="fa fa-triangle-exclamation"></i> Please fix the following:</div>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card-shell form-max">
    <div class="card-head">
        <div class="hint">Payment ID: <strong>#{{ $payment->id }}</strong></div>
        <div class="hint">Edit carefully before saving</div>
    </div>

    <div class="card-bodyx">
        <form action="{{ route('payments.update', $payment) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">

                <!-- Visit Selection -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Select Visit <span class="text-danger">*</span></label>
                    <select name="visit_id" class="selectx" required>
                        @foreach($visits as $visit)
                            <option value="{{ $visit->id }}" {{ old('visit_id', $payment->visit_id) == $visit->id ? 'selected' : '' }}>
                                {{ $visit->patient->first_name }} {{ $visit->patient->last_name }}
                                - {{ \Carbon\Carbon::parse($visit->visit_date)->format('m/d/Y') }}
                            </option>
                        @endforeach
                    </select>
                    <div class="helper">Select the visit this payment belongs to.</div>
                </div>

                <!-- Amount -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Amount <span class="text-danger">*</span></label>
                    <input type="number" name="amount" value="{{ old('amount', $payment->amount) }}" class="inputx" required>
                </div>

                <!-- Method -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Method <span class="text-danger">*</span></label>
                    <select name="method" class="selectx" required>
                        @php $m = old('method', $payment->method); @endphp
                        <option value="Cash" {{ $m == 'Cash' ? 'selected' : '' }}>Cash</option>
                        <option value="GCash" {{ $m == 'GCash' ? 'selected' : '' }}>GCash</option>
                        <option value="Card" {{ $m == 'Card' ? 'selected' : '' }}>Card</option>
                        <option value="Bank Transfer" {{ $m == 'Bank Transfer' ? 'selected' : '' }}>Bank Transfer</option>
                    </select>
                </div>

                <!-- Payment Date -->
                <div class="col-12 col-md-6">
                    <label class="form-labelx">Payment Date <span class="text-danger">*</span></label>
                    <input type="date" name="payment_date" value="{{ old('payment_date', $payment->payment_date) }}" class="inputx" required>
                </div>

                <!-- Notes -->
                <div class="col-12">
                    <label class="form-labelx">Notes</label>
                    <textarea name="notes" rows="3" class="textareax">{{ old('notes', $payment->notes) }}</textarea>
                    <div class="helper">Optional: add internal notes about this payment.</div>
                </div>

                <div class="col-12 d-flex gap-2 flex-wrap pt-2">
                    <button type="submit" class="btn-primaryx">
                        <i class="fa fa-check"></i> Update Payment
                    </button>

                    <a href="{{ route('payments.index') }}" class="btn-ghostx">
                        <i class="fa fa-xmark"></i> Cancel
                    </a>
                </div>

            </div>
        </form>
    </div>
</div>

@endsection

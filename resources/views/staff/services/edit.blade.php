@extends('layouts.staff')

@section('content')

<style>
    .page-title {
        font-size: 26px;
        font-weight: 600;
        color: #1e40af;
        margin-bottom: 10px;
    }

    .page-subtitle {
        color: #6b7280;
        margin-bottom: 25px;
        font-size: 15px;
    }

    .form-card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        max-width: 700px;
        margin: auto;
    }

    .form-label {
        font-weight: 600;
        margin-bottom: 4px;
        color: #374151;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        border: 1px solid #d1d5db;
        padding: 10px;
        border-radius: 8px;
        font-size: 14px;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        border-color: #2563eb;
        outline: none;
        box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
    }

    .submit-btn {
        background: #2563eb;
        padding: 10px 18px;
        border-radius: 8px;
        color: white;
        font-weight: 600;
        transition: .2s;
    }

    .submit-btn:hover {
        background: #1d4ed8;
    }

    .cancel-link {
        margin-left: 10px;
        color: #6b7280;
        font-weight: 500;
        transition: .2s;
    }

    .cancel-link:hover {
        color: #1d4ed8;
    }
</style>

<h2 class="page-title">Edit Service</h2>
<p class="page-subtitle">Update the details of this service below.</p>

<div class="form-card">
    <form action="{{ route('staff.services.update', $service) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 gap-4">
            <div>
                <label class="form-label">Service Name</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $service->name) }}" required>
            </div>

            <div>
                <label class="form-label">Base Price</label>
                <input type="number" step="0.01" name="base_price" class="form-input" value="{{ old('base_price', $service->base_price) }}" required>
            </div>

            <div>
                <label class="form-label">Allow Custom Price?</label>
                <select name="allow_custom_price" class="form-select" required>
                    <option value="0" {{ old('allow_custom_price', $service->allow_custom_price) == 0 ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('allow_custom_price', $service->allow_custom_price) == 1 ? 'selected' : '' }}>Yes</option>
                </select>
            </div>

            <div>
                <label class="form-label">Description</label>
                <textarea name="description" rows="3" class="form-textarea">{{ old('description', $service->description) }}</textarea>
            </div>
        </div>

        <div class="mt-5">
            <button type="submit" class="submit-btn">Update Service</button>
            <a href="{{ route('satff.services.index') }}" class="cancel-link">Cancel</a>
        </div>
    </form>
</div>

@endsection

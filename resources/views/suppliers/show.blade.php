
@extends('layouts.app')

@section('title', 'Supplier Details')
@section('breadcrumb', 'Suppliers / Details')

@section('content')

<section class="page-heading">
    <div>
        <h1>{{ $supplier->company_name }}</h1>
        <div class="subtitle">Supplier profile and business information.</div>
    </div>

    <div>
        <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
            Back
        </a>

        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-primary">
            Edit Supplier
        </a>
    </div>
</section>

<div class="panel product-panel">
    <div class="panel-header">
        <div class="panel-title">Supplier Information</div>

        <span class="status-pill {{ $supplier->is_active ? 'status-active' : 'status-inactive' }}">
            {{ $supplier->is_active ? 'Active' : 'Inactive' }}
        </span>
    </div>

    <div class="form-grid">

        <div>
            <div class="secondary">Supplier Code</div>
            <strong>{{ $supplier->supplier_code }}</strong>
        </div>

        <div>
            <div class="secondary">Company Name</div>
            <strong>{{ $supplier->company_name }}</strong>
        </div>

        <div>
            <div class="secondary">Contact Person</div>
            {{ $supplier->contact_person ?: '—' }}
        </div>

        <div>
            <div class="secondary">Email</div>
            {{ $supplier->email ?: '—' }}
        </div>

        <div>
            <div class="secondary">Phone</div>
            {{ $supplier->phone ?: '—' }}
        </div>

        <div>
            <div class="secondary">Tax Number</div>
            {{ $supplier->tax_number ?: '—' }}
        </div>

        <div>
            <div class="secondary">Payment Terms</div>
            {{ [
                'cash' => 'Cash',
                'net_15' => 'Net 15 Days',
                'net_30' => 'Net 30 Days',
                'net_60' => 'Net 60 Days',
            ][$supplier->payment_terms] ?? $supplier->payment_terms }}
        </div>

        <div>
            <div class="secondary">Registered Date</div>
            {{ $supplier->created_at?->format('d M Y') }}
        </div>

        <div class="form-full">
            <div class="secondary">Business Address</div>
            {{ $supplier->address ?: '—' }}
        </div>

        <div class="form-full">
            <div class="secondary">Notes</div>
            {{ $supplier->notes ?: '—' }}
        </div>

    </div>
</div>

@endsection

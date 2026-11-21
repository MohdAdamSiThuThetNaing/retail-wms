
@extends('layouts.app')

@section('title', 'Customer Profile')

@section('content')
<div class="page-header">
    <div>
        <h1>Customer Profile</h1>
        <p>{{ $customer->customer_code }} — {{ $customer->name }}</p>
    </div>

    <div class="header-actions">
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            Back to Customers
        </a>

        @can('customers.manage')
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary">
                Edit Customer
            </a>
        @endcan
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-label">Customer Type</span>
        <strong>{{ ucfirst($customer->customer_type) }}</strong>
    </div>

    <div class="stat-card">
        <span class="stat-label">Credit Limit</span>
        <strong>{{ number_format((float) $customer->credit_limit, 2) }}</strong>
    </div>

    <div class="stat-card">
        <span class="stat-label">Payment Terms</span>
        <strong>
            {{ [
                'cash' => 'Cash',
                'net_15' => 'Net 15 Days',
                'net_30' => 'Net 30 Days',
                'net_60' => 'Net 60 Days',
            ][$customer->payment_terms] ?? $customer->payment_terms }}
        </strong>
    </div>

    <div class="stat-card">
        <span class="stat-label">Account Status</span>
        <strong>{{ $customer->is_active ? 'Active' : 'Inactive' }}</strong>
    </div>
</div>

<div class="content-card">
    <h2>Customer Information</h2>

    <div class="detail-grid">
        <div>
            <span class="detail-label">Customer Name</span>
            <strong>{{ $customer->name }}</strong>
        </div>

        <div>
            <span class="detail-label">Company Name</span>
            <strong>{{ $customer->company_name ?: '—' }}</strong>
        </div>

        <div>
            <span class="detail-label">Contact Person</span>
            <strong>{{ $customer->contact_person ?: '—' }}</strong>
        </div>

        <div>
            <span class="detail-label">Email</span>
            <strong>{{ $customer->email ?: '—' }}</strong>
        </div>

        <div>
            <span class="detail-label">Phone</span>
            <strong>{{ $customer->phone ?: '—' }}</strong>
        </div>

        <div>
            <span class="detail-label">Tax Number</span>
            <strong>{{ $customer->tax_number ?: '—' }}</strong>
        </div>

        <div class="full-width">
            <span class="detail-label">Address</span>
            <strong>{{ $customer->address ?: '—' }}</strong>
        </div>

        <div class="full-width">
            <span class="detail-label">Notes</span>
            <strong>{{ $customer->notes ?: '—' }}</strong>
        </div>

        <div>
            <span class="detail-label">Created</span>
            <strong>{{ $customer->created_at?->format('d M Y, H:i') }}</strong>
        </div>

        <div>
            <span class="detail-label">Last Updated</span>
            <strong>{{ $customer->updated_at?->format('d M Y, H:i') }}</strong>
        </div>
    </div>
</div>
@endsection


@extends('layouts.app')

@section('title', 'Edit Sales Order')

@section('content')
<div class="sales-order-management">
    <div class="page-header">
        <div>
            <h1>Edit Sales Order</h1>
            <p>Update draft order {{ $salesOrder->order_number }}.</p>
        </div>
    </div>

    <div class="content-card">
        <form
            method="POST"
            action="{{ route('sales-orders.update', $salesOrder) }}"
            id="sales-order-form"
        >
            @csrf
            @method('PUT')

            @include('sales-orders._form', [
                'submitLabel' => 'Update Sales Order'
            ])
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/sales-orders.js') }}" defer></script>
@endpush


@extends('layouts.app')

@section('title', 'Create Sales Order')

@section('content')
<div class="sales-order-management">
    <div class="page-header">
        <div>
            <h1>Create Sales Order</h1>
            <p>Create a new customer sales order.</p>
        </div>
    </div>

    <div class="content-card">
        <form method="POST" action="{{ route('sales-orders.store') }}" id="sales-order-form">
            @csrf

            @include('sales-orders._form', [
                'submitLabel' => 'Save Draft Order'
            ])
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/sales-orders.js') }}" defer></script>
@endpush

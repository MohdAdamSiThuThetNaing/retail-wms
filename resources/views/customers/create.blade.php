
@extends('layouts.app')

@section('title', 'Add Customer')

@section('content')
<div class="customer-management">

    <div class="page-header">
        <div>
            <h1>Add Customer</h1>
            <p>Create a new customer account.</p>
        </div>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            Back to Customers
        </a>
    </div>

    <div class="content-card customer-form-card">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf

            @include('customers._form', [
                'customer' => null,
                'submitLabel' => 'Create Customer',
            ])
        </form>
    </div>

</div>
@endsection

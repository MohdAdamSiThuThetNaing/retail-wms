
@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="page-header">
    <div>
        <h1>Edit Customer</h1>
        <p>Update customer account information.</p>
    </div>
</div>

<div class="content-card">
    <form method="POST" action="{{ route('customers.update', $customer) }}">
        @csrf
        @method('PUT')

        @include('customers._form', [
            'customer' => $customer,
            'submitLabel' => 'Update Customer',
        ])
    </form>
</div>
@endsection

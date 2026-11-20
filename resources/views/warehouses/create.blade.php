@extends('layouts.app')

@section('title', 'Add Warehouse')
@section('breadcrumb', 'Warehouses / Add')

@section('content')
<section class="page-heading">
    <div>
        <h1>Add Warehouse</h1>
        <div class="subtitle">Register a new warehouse facility.</div>
    </div>
</section>

<form method="POST" action="{{ route('warehouses.store') }}">
    @csrf
    @include('warehouses._form', [
        'warehouse' => null,
        'submitLabel' => 'Create Warehouse'
    ])
</form>
@endsection

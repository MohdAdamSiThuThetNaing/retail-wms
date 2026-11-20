@extends('layouts.app')

@section('title', 'Edit Warehouse')
@section('breadcrumb', 'Warehouses / Edit')

@section('content')
<section class="page-heading">
    <div>
        <h1>Edit Warehouse</h1>
        <div class="subtitle">Update {{ $warehouse->name }} information.</div>
    </div>

    <a href="{{ route('warehouses.show', $warehouse) }}" class="btn btn-secondary">
        View Warehouse
    </a>
</section>

<form method="POST" action="{{ route('warehouses.update', $warehouse) }}">
    @csrf
    @method('PUT')

    @include('warehouses._form', [
        'warehouse' => $warehouse,
        'submitLabel' => 'Save Changes'
    ])
</form>
@endsection

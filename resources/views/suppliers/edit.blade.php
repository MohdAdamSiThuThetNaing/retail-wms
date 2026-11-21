
@extends('layouts.app')

@section('title', 'Edit Supplier')
@section('breadcrumb', 'Suppliers / Edit Supplier')

@section('content')

<section class="page-heading">
    <div>
        <h1>Edit Supplier</h1>
        <div class="subtitle">
            Update {{ $supplier->company_name }} information.
        </div>
    </div>

    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
        Back to Suppliers
    </a>
</section>

<div class="panel product-panel">
    <div class="panel-header">
        <div class="panel-title">Supplier Information</div>
    </div>

    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf
        @method('PUT')

        @include('suppliers._form')

        <div class="form-actions">
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Update Supplier
            </button>
        </div>
    </form>
</div>

@endsection

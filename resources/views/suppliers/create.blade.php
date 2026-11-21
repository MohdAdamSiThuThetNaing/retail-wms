
@extends('layouts.app')

@section('title', 'Add Supplier')
@section('breadcrumb', 'Suppliers / Add Supplier')

@section('content')

<section class="page-heading">
    <div>
        <h1>Add Supplier</h1>
        <div class="subtitle">Register a new supplier in your business directory.</div>
    </div>

    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
        Back to Suppliers
    </a>
</section>

<div class="panel product-panel">
    <div class="panel-header">
        <div class="panel-title">Supplier Information</div>
    </div>

    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf

        @include('suppliers._form')

        <div class="form-actions">
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Save Supplier
            </button>
        </div>
    </form>
</div>

@endsection

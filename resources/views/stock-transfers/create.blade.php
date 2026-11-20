@extends('layouts.app')

@section('title', 'Stock Transfer')
@section('breadcrumb', 'Stock Transfer')

@section('content')

<div class="inventory-page">

    <section class="page-heading">
        <div>
            <h1>Stock Transfer</h1>
            <p>Move inventory from one warehouse to another.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
            Back to Inventory
        </a>
    </section>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following errors:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel inventory-panel">

        <div class="panel-header">
            <div>
                <h2>Transfer Details</h2>
                <p>Select the product and warehouses for this transfer.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('stock-transfers.store') }}">
            @csrf

            <div class="transfer-form-grid">

                <div class="form-group">
                    <label for="product_id">Product *</label>
                    <select id="product_id" name="product_id" required>
                        <option value="">Select Product</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                @selected(old('product_id') == $product->id)>
                                {{ $product->sku }} - {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('product_id')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="from_warehouse_id">From Warehouse *</label>
                    <select id="from_warehouse_id" name="from_warehouse_id" required>
                        <option value="">Select Source Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                @selected(old('from_warehouse_id') == $warehouse->id)>
                                {{ $warehouse->code }} - {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('from_warehouse_id')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="transfer-direction">
                    <span>↓</span>
                    <small>Transfer stock to</small>
                </div>

                <div class="form-group">
                    <label for="to_warehouse_id">To Warehouse *</label>
                    <select id="to_warehouse_id" name="to_warehouse_id" required>
                        <option value="">Select Destination Warehouse</option>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}"
                                @selected(old('to_warehouse_id') == $warehouse->id)>
                                {{ $warehouse->code }} - {{ $warehouse->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('to_warehouse_id')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="quantity">Transfer Quantity *</label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        min="1"
                        max="1000000"
                        value="{{ old('quantity') }}"
                        placeholder="Enter quantity"
                        required
                    >
                    @error('quantity')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="reference">Reference Number</label>
                    <input
                        type="text"
                        id="reference"
                        name="reference"
                        maxlength="100"
                        value="{{ old('reference') }}"
                        placeholder="e.g. TRF-2026-001"
                    >
                    @error('reference')
                        <small class="field-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>

            <div class="transfer-form-actions">
                <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                    Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    Transfer Stock
                </button>
            </div>

        </form>

    </div>

</div>

@endsection

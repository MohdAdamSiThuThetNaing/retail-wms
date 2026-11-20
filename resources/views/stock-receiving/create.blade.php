@extends('layouts.app')

@section('title', 'Receive Stock')
@section('breadcrumb', 'Stock Receiving')

@section('content')
<div class="inventory-page receiving-page">
    <div class="page-header">
        <div>
            <h1>Receive Stock</h1>
            <p>Record incoming products and add quantities to a warehouse.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Back to Inventory</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Please correct the following:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('stock-receiving.store') }}" class="receiving-card">
        @csrf

        <div class="receiving-card-header">
            <h2>Receiving Information</h2>
            <p>Select a warehouse and product, then enter the received quantity.</p>
        </div>

        <div class="receiving-form-grid">
            <div class="form-group">
                <label for="warehouse_id">Destination Warehouse *</label>
                <select id="warehouse_id" name="warehouse_id" required>
                    <option value="">Select a warehouse</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(old('warehouse_id') == $warehouse->id)>
                            {{ $warehouse->name }} ({{ $warehouse->code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="product_id">Product *</label>
                <select id="product_id" name="product_id" required>
                    <option value="">Select a product</option>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}" @selected(old('product_id') == $product->id)>
                            {{ $product->name }} — {{ $product->sku }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="quantity">Quantity Received *</label>
                <input id="quantity" type="number" name="quantity"
                       min="1" max="1000000" value="{{ old('quantity', 1) }}"
                       required placeholder="Enter quantity">
            </div>

            <div class="form-group">
                <label for="reference">Reference Number</label>
                <input id="reference" type="text" name="reference"
                       maxlength="100" value="{{ old('reference') }}"
                       placeholder="e.g. GRN-0001">
                <small>Optional goods receipt or supplier invoice number.</small>
            </div>
        </div>

        <div class="receiving-note">
            <strong>Stock update</strong>
            <p>Submitting this form increases the selected product's stock in the chosen warehouse and records the inventory movement.</p>
        </div>

        <div class="receiving-actions">
            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary"
                @disabled($warehouses->isEmpty() || $products->isEmpty())>
                Receive Stock
            </button>
        </div>
    </form>
</div>
@endsection

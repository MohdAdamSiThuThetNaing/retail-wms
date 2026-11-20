
@extends('layouts.app')

@section('title', 'Product Details')
@section('breadcrumb', 'Products / Details')

@section('content')
<section class="page-heading">
    <div>
        <h1>Product Details</h1>
        <div class="subtitle">Product information and warehouse stock.</div>
    </div>

    <a href="{{ route('products.edit', $product) }}" class="btn btn-primary">
        Edit Product
    </a>
</section>

<div class="panel">
    <div class="product-detail">
        @if($product->image_path)
            <img src="{{ asset('storage/'.$product->image_path) }}"
                 class="detail-image" alt="{{ $product->name }}">
        @else
            <div class="detail-placeholder">▤</div>
        @endif

        <div>
            <h2>{{ $product->name }}</h2>
            <p>{{ $product->description ?: 'No description provided.' }}</p>

            <div class="detail-grid">
                <div><span>SKU</span><strong>{{ $product->sku }}</strong></div>
                <div><span>Barcode</span><strong>{{ $product->barcode ?: '—' }}</strong></div>
                <div><span>Category</span><strong>{{ $product->category?->name ?? 'Uncategorized' }}</strong></div>
                <div><span>Brand</span><strong>{{ $product->brand ?: '—' }}</strong></div>
                <div><span>Cost Price</span><strong>{{ number_format((float) $product->cost_price, 2) }}</strong></div>
                <div><span>Selling Price</span><strong>{{ number_format((float) $product->unit_price, 2) }}</strong></div>
                <div><span>Unit</span><strong>{{ $product->unit }}</strong></div>
                <div><span>Status</span><strong>{{ $product->is_active ? 'Active' : 'Inactive' }}</strong></div>
            </div>
        </div>
    </div>
</div>

<div class="panel product-panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Stock by Warehouse</div>
            <div class="panel-subtitle">Current inventory balance for this product</div>
        </div>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Warehouse</th>
                    <th>Warehouse Code</th>
                    <th>Quantity</th>
                    <th>Reorder Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($product->stocks as $stock)
                    <tr>
                        <td>{{ $stock->warehouse?->name ?? '—' }}</td>
                        <td>{{ $stock->warehouse?->code ?? '—' }}</td>
                        <td>{{ number_format($stock->quantity) }} {{ $product->unit }}</td>
                        <td>{{ number_format($stock->reorder_level) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="empty">No stock recorded for this product.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

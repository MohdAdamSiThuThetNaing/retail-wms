
@extends('layouts.app')

@section('title', 'Products')
@section('breadcrumb', 'Products')

@section('content')
<section class="page-heading">
    <div>
        <h1>Product Management</h1>
        <div class="subtitle">Manage your product catalog, pricing and availability.</div>
    </div>

    <a href="{{ route('products.create') }}" class="btn btn-primary">
        + Add Product
    </a>
</section>

<section class="stats-grid product-stats">
    <article class="stat-card">
        <div class="stat-label">Total Products</div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Active Products</div>
        <div class="stat-value">{{ number_format($stats['active']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Inactive Products</div>
        <div class="stat-value">{{ number_format($stats['inactive']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Categories</div>
        <div class="stat-value">{{ $categories->count() }}</div>
    </article>
</section>

<div class="panel product-panel">
    <div class="panel-header">
        <div>
            <div class="panel-title">Product Catalog</div>
            <div class="panel-subtitle">Search and manage registered products</div>
        </div>

        <a href="{{ route('categories.index') }}" class="btn btn-secondary">
            Manage Categories
        </a>
    </div>

    <form method="GET" action="{{ route('products.index') }}" class="filter-bar">
        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search SKU, barcode, product or brand..."
            class="form-control search-input"
        >

        <select name="category_id" class="form-control">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category->id }}"
                    @selected(request('category_id') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>

        <select name="status" class="form-control">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>

        <button class="btn btn-primary" type="submit">Filter</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Reset</a>
    </form>

    <div class="table-wrap">
        <table class="product-table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>SKU / Barcode</th>
                    <th>Category</th>
                    <th>Cost Price</th>
                    <th>Selling Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
            @forelse($products as $product)
                <tr>
                    <td>
                        <div class="product-cell">
                            @if($product->image_path)
                                <img src="{{ asset('storage/'.$product->image_path) }}"
                                     alt="{{ $product->name }}"
                                     class="product-thumb">
                            @else
                                <div class="product-placeholder">▤</div>
                            @endif

                            <div>
                                <div class="product-name">{{ $product->name }}</div>
                                <div class="secondary">{{ $product->brand ?: 'No brand' }}</div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <strong>{{ $product->sku }}</strong>
                        <div class="secondary">{{ $product->barcode ?: 'No barcode' }}</div>
                    </td>

                    <td>{{ $product->category?->name ?? 'Uncategorized' }}</td>

                    <td>{{ number_format((float) $product->cost_price, 2) }}</td>

                    <td>{{ number_format((float) $product->unit_price, 2) }}</td>

                    <td>{{ number_format((int) ($product->stocks_sum_quantity ?? 0)) }}
                        {{ $product->unit }}
                    </td>

                    <td>
                        <span class="status-pill {{ $product->is_active ? 'status-active' : 'status-inactive' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>

                    <td>
                        <div class="action-links">
                            <a href="{{ route('products.show', $product) }}">View</a>
                            <a href="{{ route('products.edit', $product) }}">Edit</a>

                            <form method="POST"
                                  action="{{ route('products.destroy', $product) }}"
                                  onsubmit="return confirm('Delete this product? This cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-danger">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">
                        No products found. Add your first product to get started.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-wrap">
        {{ $products->links() }}
    </div>
</div>
@endsection

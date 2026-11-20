@extends('layouts.app')

@section('title', 'Inventory')
@section('breadcrumb', 'Inventory Management')

@section('content')

<div class="inventory-page">

    <section class="page-heading">
        <div>
            <h1>Inventory Management</h1>
            <p>Monitor stock levels across all warehouses.</p>
        </div>
    </section>

    {{-- Inventory statistics --}}
    <div class="inventory-stats">

        <div class="inventory-stat-card">
            <div class="inventory-stat-icon purple">📦</div>
            <div class="inventory-stat-info">
                <span>Total Products</span>
                <h2>{{ number_format($stats['products']) }}</h2>
            </div>
        </div>

        <div class="inventory-stat-card">
            <div class="inventory-stat-icon blue">🏬</div>
            <div class="inventory-stat-info">
                <span>Total Stock Quantity</span>
                <h2>{{ number_format($stats['total_stock']) }}</h2>
            </div>
        </div>

        <div class="inventory-stat-card">
            <div class="inventory-stat-icon orange">⚠️</div>
            <div class="inventory-stat-info">
                <span>Low Stock</span>
                <h2>{{ number_format($stats['low_stock']) }}</h2>
            </div>
        </div>

        <div class="inventory-stat-card">
            <div class="inventory-stat-icon green">❌</div>
            <div class="inventory-stat-info">
                <span>Out of Stock</span>
                <h2>{{ number_format($stats['out_of_stock']) }}</h2>
            </div>
        </div>

    </div>

    {{-- Inventory value --}}
    <div class="panel inventory-panel inventory-value-panel">
        <div>
            <span class="panel-label">Total Inventory Value</span>
            <h2>฿{{ number_format((float) $stats['stock_value'], 2) }}</h2>
            <small>Calculated using product cost prices</small>
        </div>
    </div>

    {{-- Stock overview --}}
    <div class="panel inventory-panel">

        <div class="panel-header">
            <div>
                <h2>Stock Overview</h2>
                <p>Current inventory by product and warehouse.</p>
            </div>
        </div>

        {{-- Search and filters --}}
        <form method="GET"
              action="{{ route('inventory.index') }}"
              class="inventory-filter-form">

            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Search product, SKU or barcode..."
            >

            <select name="warehouse_id">
                <option value="">All Warehouses</option>

                @foreach($warehouses as $warehouse)
                    <option
                        value="{{ $warehouse->id }}"
                        @selected((string) $warehouseId === (string) $warehouse->id)
                    >
                        {{ $warehouse->code }} - {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>

            <select name="status">
                <option value="">All Stock Status</option>
                <option value="healthy" @selected($status === 'healthy')>
                    Healthy
                </option>
                <option value="low" @selected($status === 'low')>
                    Low Stock
                </option>
                <option value="out" @selected($status === 'out')>
                    Out of Stock
                </option>
            </select>

            <button type="submit" class="btn btn-primary">
                Filter
            </button>

            <a href="{{ route('inventory.index') }}" class="btn btn-secondary">
                Reset
            </a>

        </form>

        {{-- Stock table --}}
        <div class="table-responsive">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Product</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Warehouse</th>
                        <th>Quantity</th>
                        <th>Reorder Level</th>
                        <th>Stock Value</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($stocks as $stock)

                        @php
                            $quantity = (int) $stock->quantity;
                            $reorder = (int) $stock->reorder_level;

                            $stockStatus = $quantity === 0
                                ? 'out'
                                : ($quantity <= $reorder ? 'low' : 'healthy');
                        @endphp

                        <tr>
                            <td>
                                <strong>{{ $stock->product->name }}</strong>
                            </td>

                            <td>{{ $stock->product->sku }}</td>

                            <td>
                                {{ $stock->product->category?->name ?? 'Uncategorized' }}
                            </td>

                            <td>
                                <strong>{{ $stock->warehouse->code }}</strong>
                                <small>{{ $stock->warehouse->name }}</small>
                            </td>

                            <td>
                                <strong>{{ number_format($quantity) }}</strong>
                                {{ $stock->product->unit }}
                            </td>

                            <td>{{ number_format($reorder) }}</td>

                            <td>
                                ฿{{ number_format(
                                    $quantity * (float) $stock->product->cost_price,
                                    2
                                ) }}
                            </td>

                            <td>
                                @if($stockStatus === 'out')
                                    <span class="status-badge status-danger">
                                        Out of Stock
                                    </span>
                                @elseif($stockStatus === 'low')
                                    <span class="status-badge status-warning">
                                        Low Stock
                                    </span>
                                @else
                                    <span class="status-badge status-success">
                                        Healthy
                                    </span>
                                @endif
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="8" class="empty-state">
                                No inventory records found.
                            </td>
                        </tr>

                    @endforelse

                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-wrapper">
            {{ $stocks->links() }}
        </div>

    </div>

</div>

@endsection
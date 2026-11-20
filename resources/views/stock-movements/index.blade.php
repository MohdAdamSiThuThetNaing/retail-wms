@extends('layouts.app')

@section('title', 'Stock Movements')
@section('breadcrumb', 'Stock Movements')

@section('content')

<div class="inventory-page">

    <section class="page-heading">
        <div>
            <h1>Stock Movements</h1>
            <p>Track stock receipts and transfers across warehouses.</p>
        </div>
    </section>

    {{-- Movement statistics --}}
    <div class="inventory-stats">

        <div class="inventory-stat-card">
            <div class="inventory-stat-icon purple">📋</div>
            <div class="inventory-stat-info">
                <span>Total Movements</span>
                <h2>{{ number_format($stats['total']) }}</h2>
            </div>
        </div>

        <div class="inventory-stat-card">
            <div class="inventory-stat-icon green">📥</div>
            <div class="inventory-stat-info">
                <span>Units Received</span>
                <h2>{{ number_format($stats['received']) }}</h2>
            </div>
        </div>

        <div class="inventory-stat-card">
            <div class="inventory-stat-icon orange">📤</div>
            <div class="inventory-stat-info">
                <span>Units Transferred Out</span>
                <h2>{{ number_format($stats['transferred_out']) }}</h2>
            </div>
        </div>

        <div class="inventory-stat-card">
            <div class="inventory-stat-icon blue">📦</div>
            <div class="inventory-stat-info">
                <span>Units Transferred In</span>
                <h2>{{ number_format($stats['transferred_in']) }}</h2>
            </div>
        </div>

    </div>

    {{-- Movement history --}}
    <div class="panel inventory-panel">

        <div class="panel-header">
            <div>
                <h2>Movement History</h2>
                <p>View and filter all inventory transactions.</p>
            </div>
        </div>

        <form method="GET"
              action="{{ route('stock-movements.index') }}"
              class="inventory-filter-form">

            <input
                type="text"
                name="search"
                value="{{ $filters['search'] ?? '' }}"
                placeholder="Search product, SKU or reference..."
            >

            <select name="product_id">
                <option value="">All Products</option>
                @foreach($products as $product)
                    <option
                        value="{{ $product->id }}"
                        @selected(($filters['product_id'] ?? '') == $product->id)
                    >
                        {{ $product->sku }} - {{ $product->name }}
                    </option>
                @endforeach
            </select>

            <select name="warehouse_id">
                <option value="">All Warehouses</option>
                @foreach($warehouses as $warehouse)
                    <option
                        value="{{ $warehouse->id }}"
                        @selected(($filters['warehouse_id'] ?? '') == $warehouse->id)
                    >
                        {{ $warehouse->code }} - {{ $warehouse->name }}
                    </option>
                @endforeach
            </select>

            <select name="type">
                <option value="">All Movement Types</option>
                <option value="receive" @selected(($filters['type'] ?? '') === 'receive')>
                    Stock Received
                </option>
                <option value="transfer_out" @selected(($filters['type'] ?? '') === 'transfer_out')>
                    Transfer Out
                </option>
                <option value="transfer_in" @selected(($filters['type'] ?? '') === 'transfer_in')>
                    Transfer In
                </option>
            </select>

            <input
                type="date"
                name="start_date"
                value="{{ $filters['start_date'] ?? '' }}"
            >

            <input
                type="date"
                name="end_date"
                value="{{ $filters['end_date'] ?? '' }}"
            >

            <button type="submit" class="btn btn-primary">
                Filter
            </button>

            <a href="{{ route('stock-movements.index') }}"
               class="btn btn-secondary">
                Reset
            </a>

        </form>

        <div class="table-responsive">
            <table class="data-table">

                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Product</th>
                        <th>Warehouse</th>
                        <th>Movement Type</th>
                        <th>Quantity</th>
                        <th>Reference</th>
                        <th>Performed By</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($movements as $movement)

                        <tr>
                            <td>
                                {{ $movement->created_at?->format('d M Y') ?? '—' }}
                                <small>
                                    {{ $movement->created_at?->format('h:i A') ?? '' }}
                                </small>
                            </td>

                            <td>
                                <strong>{{ $movement->product?->name ?? 'Deleted product' }}</strong>
                                <small>{{ $movement->product?->sku ?? '—' }}</small>
                            </td>

                            <td>
                                <strong>{{ $movement->warehouse?->code ?? '—' }}</strong>
                                <small>{{ $movement->warehouse?->name ?? '—' }}</small>
                            </td>

                            <td>
                                @if($movement->type === 'receive')
                                    <span class="status-badge status-success">
                                        Stock Received
                                    </span>
                                @elseif($movement->type === 'transfer_out')
                                    <span class="status-badge status-warning">
                                        Transfer Out
                                    </span>
                                @elseif($movement->type === 'transfer_in')
                                    <span class="status-badge status-success">
                                        Transfer In
                                    </span>
                                @else
                                    <span class="status-badge">
                                        {{ ucfirst(str_replace('_', ' ', $movement->type)) }}
                                    </span>
                                @endif
                            </td>

                            <td>
                                <strong>{{ number_format($movement->quantity) }}</strong>
                            </td>

                            <td>{{ $movement->reference ?: '—' }}</td>

                            <td>{{ $movement->creator?->name ?? 'System' }}</td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="7" class="empty-state">
                                No stock movements found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $movements->links() }}
        </div>

    </div>

</div>

@endsection

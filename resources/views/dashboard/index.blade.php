@extends('layouts.app')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')

<section class="page-heading">
    <div>
        <h1>Dashboard Overview</h1>
        <div class="subtitle">
            Monitor your retail operations and warehouse performance.
        </div>
    </div>

    <div class="date-label">
        {{ now()->format('D, d M Y') }}
    </div>
</section>

{{-- KPI Statistics --}}
<section class="stats-grid">

    <article class="stat-card">
        <div class="stat-top">
            <div class="stat-label">Total Products</div>
            <div class="stat-icon purple">▤</div>
        </div>
        <div class="stat-value">
            {{ number_format($stats['products']) }}
        </div>
        <div class="stat-foot">
            Products registered in the system
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-top">
            <div class="stat-label">Active Warehouses</div>
            <div class="stat-icon blue">▣</div>
        </div>
        <div class="stat-value">
            {{ number_format($stats['warehouses']) }}
        </div>
        <div class="stat-foot">
            Currently active warehouse locations
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-top">
            <div class="stat-label">Total Stock Units</div>
            <div class="stat-icon green">▥</div>
        </div>
        <div class="stat-value">
            {{ number_format($stats['total_stock']) }}
        </div>
        <div class="stat-foot">
            Combined quantity across warehouses
        </div>
    </article>

    <article class="stat-card">
        <div class="stat-top">
            <div class="stat-label">Low Stock Items</div>
            <div class="stat-icon orange">⚠</div>
        </div>
        <div class="stat-value">
            {{ number_format($stats['low_stock']) }}
        </div>
        <div class="stat-foot">
            Stock records at or below reorder level
        </div>
    </article>

</section>

{{-- Main Dashboard Grid --}}
<section class="section-grid">

    {{-- Recent Inventory Movements --}}
    <div class="panel">

        <div class="panel-header">
            <div>
                <div class="panel-title">
                    Recent Inventory Movements
                </div>
                <div class="panel-subtitle">
                    Latest stock activity recorded in the system
                </div>
            </div>

            <a href="#" class="view-all">
                View all →
            </a>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Movement</th>
                        <th>Warehouse</th>
                        <th>Quantity</th>
                        <th>Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recentMovements as $movement)

                        @php
                            $movementType = strtolower(
                                $movement->type ?? 'unknown'
                            );

                            $movementClass = match($movementType) {
                                'receive', 'in', 'inbound' => 'type-in',
                                'transfer' => 'type-transfer',
                                'issue', 'out', 'outbound' => 'type-out',
                                default => 'type-transfer',
                            };
                        @endphp

                        <tr>
                            <td>
                                <div class="product-name">
                                    {{ $movement->product?->name ?? 'Unknown product' }}
                                </div>
                                <div class="secondary">
                                    {{ $movement->product?->sku ?? '—' }}
                                </div>
                            </td>

                            <td>
                                <span class="type {{ $movementClass }}">
                                    {{ ucfirst($movementType) }}
                                </span>
                            </td>

                            <td>
                                {{ $movement->warehouse?->name ?? '—' }}
                            </td>

                            <td>
                                <strong>
                                    {{ number_format($movement->quantity ?? 0) }}
                                </strong>
                            </td>

                            <td>
                                {{ $movement->created_at?->format('d M Y, H:i') ?? '—' }}
                            </td>
                        </tr>

                    @empty

                        <tr>
                            <td colspan="5" class="empty">
                                No inventory movements recorded yet.
                            </td>
                        </tr>

                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right Dashboard Panels --}}
    <div class="side-panel">

        {{-- Inventory Summary --}}
        <div class="panel">

            <div class="panel-header">
                <div>
                    <div class="panel-title">Inventory Summary</div>
                    <div class="panel-subtitle">
                        Current inventory indicators
                    </div>
                </div>
            </div>

            <div class="stock-body">

                <div class="stock-metric">
                    <span class="stock-name">Registered products</span>
                    <span class="stock-number">
                        {{ number_format($stats['products']) }}
                    </span>
                </div>

                <div class="stock-metric">
                    <span class="stock-name">Active warehouses</span>
                    <span class="stock-number">
                        {{ number_format($stats['warehouses']) }}
                    </span>
                </div>

                <div class="stock-metric">
                    <span class="stock-name">Total stock units</span>
                    <span class="stock-number">
                        {{ number_format($stats['total_stock']) }}
                    </span>
                </div>

                <div class="stock-metric">
                    <span class="stock-name">Low stock records</span>
                    <span class="stock-number">
                        {{ number_format($stats['low_stock']) }}
                    </span>
                </div>

            </div>
        </div>

        {{-- Stock Alerts --}}
        <div class="panel">

            <div class="panel-header">
                <div>
                    <div class="panel-title">Stock Attention</div>
                    <div class="panel-subtitle">
                        Reorder monitoring
                    </div>
                </div>
            </div>

            <div class="alert-body">
                <div class="alert-box">
                    <div class="alert-symbol">⚠</div>

                    <div>
                        <div class="alert-title">
                            {{ $stats['low_stock'] > 0
                                ? 'Low stock requires attention'
                                : 'No low stock records' }}
                        </div>

                        <div class="alert-text">
                            {{ $stats['low_stock'] > 0
                                ? number_format($stats['low_stock']) . ' stock records are at or below their reorder level.'
                                : 'Your current stock records are above their configured reorder levels.' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Platform Information --}}
        <div class="panel">

            <div class="panel-header">
                <div>
                    <div class="panel-title">Platform Information</div>
                    <div class="panel-subtitle">
                        Application details
                    </div>
                </div>
            </div>

            <div class="system-body">

                <div class="system-row">
                    <span>Framework</span>
                    <strong class="system-value">Laravel 12</strong>
                </div>

                <div class="system-row">
                    <span>Application</span>
                    <strong>Retail WMS</strong>
                </div>

                <div class="system-row">
                    <span>Environment</span>
                    <strong>{{ app()->environment() }}</strong>
                </div>

            </div>
        </div>

    </div>
</section>

@endsection

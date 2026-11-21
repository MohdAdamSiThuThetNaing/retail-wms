
@extends('layouts.app')

@section('title', 'Purchase Orders')
@section('breadcrumb', 'Purchase Orders')

@section('content')

@php
    $statusLabels = [
        'draft' => 'Draft',
        'pending_approval' => 'Pending Approval',
        'approved' => 'Approved',
        'rejected' => 'Rejected',
        'partially_received' => 'Partially Received',
        'received' => 'Received',
        'cancelled' => 'Cancelled',
    ];

    $statusClasses = [
        'draft' => 'po-status-draft',
        'pending_approval' => 'po-status-pending',
        'approved' => 'po-status-approved',
        'rejected' => 'po-status-rejected',
        'partially_received' => 'po-status-partial',
        'received' => 'po-status-received',
        'cancelled' => 'po-status-cancelled',
    ];
@endphp

<section class="page-heading">
    <div>
        <h1>Purchase Order Management</h1>
        <div class="subtitle">
            Create, track and manage supplier purchase orders and goods receiving.
        </div>
    </div>

    @can('purchase-orders.create')
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
            + Create Purchase Order
        </a>
    @endcan
</section>

{{-- Purchase Order Statistics --}}
<section class="stats-grid po-stats">

    <article class="stat-card">
        <div class="stat-label">Total Orders</div>
        <div class="stat-value">{{ number_format($stats['total']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Draft</div>
        <div class="stat-value">{{ number_format($stats['draft']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Pending Approval</div>
        <div class="stat-value">{{ number_format($stats['pending_approval']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Approved</div>
        <div class="stat-value">{{ number_format($stats['approved']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Partially Received</div>
        <div class="stat-value">{{ number_format($stats['partially_received']) }}</div>
    </article>

    <article class="stat-card">
        <div class="stat-label">Fully Received</div>
        <div class="stat-value">{{ number_format($stats['received']) }}</div>
    </article>

</section>

{{-- Purchase Order Catalog --}}
<div class="panel po-panel">

    <div class="panel-header">
        <div>
            <div class="panel-title">Purchase Orders</div>
            <div class="panel-subtitle">
                Search and monitor purchase orders across your warehouses.
            </div>
        </div>
    </div>

    {{-- Search and Filters --}}
    <form method="GET"
          action="{{ route('purchase-orders.index') }}"
          class="filter-bar">

        <input
            type="search"
            name="q"
            value="{{ request('q') }}"
            placeholder="Search PO number or supplier..."
            class="form-control search-input"
        >

        <select name="status" class="form-control">
            <option value="">All statuses</option>

            @foreach($statusLabels as $value => $label)
                <option value="{{ $value }}"
                    @selected(request('status') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <button class="btn btn-primary" type="submit">
            Filter
        </button>

        <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">
            Reset
        </a>

    </form>

    {{-- Purchase Orders Table --}}
    <div class="table-wrap">
        <table class="product-table po-table">

            <thead>
                <tr>
                    <th>PO Number</th>
                    <th>Supplier</th>
                    <th>Warehouse</th>
                    <th>Order Date</th>
                    <th>Expected Delivery</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>

                @forelse($purchaseOrders as $purchaseOrder)

                    @php
                        $status = $purchaseOrder->status;
                        $statusLabel = $statusLabels[$status] ?? ucfirst(str_replace('_', ' ', $status));
                        $statusClass = $statusClasses[$status] ?? 'po-status-draft';
                    @endphp

                    <tr>

                        <td>
                            <strong>{{ $purchaseOrder->po_number }}</strong>
                            <div class="secondary">
                                {{ $purchaseOrder->currency }}
                            </div>
                        </td>

                        <td>
                            <strong>{{ $purchaseOrder->supplier->company_name ?? 'Unavailable' }}</strong>
                        </td>

                        <td>
                            {{ $purchaseOrder->warehouse->name ?? 'Unavailable' }}
                        </td>

                        <td>
                            {{ $purchaseOrder->order_date?->format('d M Y') ?? '—' }}
                        </td>

                        <td>
                            {{ $purchaseOrder->expected_delivery_date?->format('d M Y') ?? '—' }}
                        </td>

                        <td>
                            <strong>
                                {{ $purchaseOrder->currency }}
                                {{ number_format((float) $purchaseOrder->total_amount, 2) }}
                            </strong>
                        </td>

                        <td>
                            <span class="status-pill {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </td>

                        <td>
                            {{ $purchaseOrder->creator->name ?? '—' }}
                        </td>

                        <td>
                            <div class="action-links">

                                @can('purchase-orders.view')
                                    <a href="{{ route('purchase-orders.show', $purchaseOrder) }}">
                                        View
                                    </a>
                                @endcan

                                @if(in_array($purchaseOrder->status, ['draft', 'rejected'], true))
                                    @can('purchase-orders.create')
                                        <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}">
                                            Edit
                                        </a>
                                    @endcan
                                @endif

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="9" class="empty">
                            No purchase orders found.
                            @can('purchase-orders.create')
                                Create your first purchase order to get started.
                            @endcan
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>
    </div>

    {{-- Pagination --}}
    <div class="pagination-wrap">
        {{ $purchaseOrders->links() }}
    </div>

</div>

@endsection

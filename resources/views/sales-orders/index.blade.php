
@extends('layouts.app')

@section('title', 'Sales Order Management')

@section('content')
<div class="sales-order-management">
    <div class="page-header">
        <div>
            <h1>Sales Order Management</h1>
            <p>Manage customer orders, fulfillment and sales order status.</p>
        </div>

        @can('sales-orders.create')
            <a href="{{ route('sales-orders.create') }}" class="btn btn-primary">
                + Create Sales Order
            </a>
        @endcan
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-label">Total Orders</span>
            <strong>{{ number_format($stats['total']) }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">Draft</span>
            <strong>{{ number_format($stats['draft']) }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">Confirmed</span>
            <strong>{{ number_format($stats['confirmed']) }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">Processing</span>
            <strong>{{ number_format($stats['processing']) }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">Completed</span>
            <strong>{{ number_format($stats['completed']) }}</strong>
        </div>
        <div class="stat-card">
            <span class="stat-label">Cancelled</span>
            <strong>{{ number_format($stats['cancelled']) }}</strong>
        </div>
    </div>

    <div class="content-card">
        <form method="GET" action="{{ route('sales-orders.index') }}" class="filter-form">
            <input
                type="text"
                name="q"
                value="{{ request('q') }}"
                placeholder="Search order number or customer..."
            >

            <select name="status">
                <option value="">All Statuses</option>
                @foreach (['draft', 'confirmed', 'processing', 'completed', 'cancelled'] as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                        {{ ucfirst($status) }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">Filter</button>

            <a href="{{ route('sales-orders.index') }}" class="btn btn-secondary">
                Reset
            </a>
        </form>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer</th>
                        <th>Warehouse</th>
                        <th>Order Date</th>
                        <th>Delivery Date</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($salesOrders as $order)
                        <tr>
                            <td>
                                <strong>{{ $order->order_number }}</strong>
                            </td>
                            <td>
                                {{ $order->customer->name ?? '—' }}
                                @if ($order->customer?->company_name)
                                    <br>
                                    <small>{{ $order->customer->company_name }}</small>
                                @endif
                            </td>
                            <td>{{ $order->warehouse->name ?? '—' }}</td>
                            <td>{{ $order->order_date?->format('d M Y') ?? '—' }}</td>
                            <td>{{ $order->expected_delivery_date?->format('d M Y') ?? '—' }}</td>
                            <td>
                                <strong>
                                    {{ $order->currency }}
                                    {{ number_format((float) $order->total_amount, 2) }}
                                </strong>
                            </td>
                            <td>
                                <span class="status-badge status-{{ $order->status }}">
                                    {{ ucfirst($order->status) }}
                                </span>
                            </td>
                            <td class="action-cell">
                                <a href="{{ route('sales-orders.show', $order) }}">View</a>

                                @if ($order->status === 'draft')
                                    @can('sales-orders.create')
                                        <a href="{{ route('sales-orders.edit', $order) }}">Edit</a>

                                        <form
                                            method="POST"
                                            action="{{ route('sales-orders.destroy', $order) }}"
                                            onsubmit="return confirm('Delete this draft sales order?')"
                                        >
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="link-button danger">
                                                Delete
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="empty-state">
                                No sales orders found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pagination-wrapper">
            {{ $salesOrders->links() }}
        </div>
    </div>
</div>
@endsection
